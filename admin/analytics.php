<?php
include '../app.php';
requireAdmin();

/* ======================
DATE FILTER
====================== */
$from = $_GET['from'] ?? '';
$to   = $_GET['to'] ?? '';
$range = isset($_GET['range']) ? (int)$_GET['range'] : 7;

$dateFilter = "";

if (!empty($from) && !empty($to)) {
    $dateFilter = " AND DATE(order_date) BETWEEN '$from' AND '$to'";
} else {
    $dateFilter = " AND order_date >= DATE_SUB(CURDATE(), INTERVAL $range DAY)";
}

/* ======================
SALES DATA
====================== */
$salesQuery = $conn->query("
    SELECT DATE(order_date) as date, SUM(total_amount) as total
    FROM orders
    WHERE status = 'Completed'
    $dateFilter
    GROUP BY DATE(order_date)
    ORDER BY date ASC
");

$dates = [];
$totals = [];

while ($row = $salesQuery->fetch_assoc()) {
    $dates[] = $row['date'];
    $totals[] = (float)$row['total'];
}

/* ======================
ANALYTICS
====================== */
$bestDay = "No Data";
$worstDay = "No Data";
$maxSales = 0;
$minSales = 0;
$avgSales = 0;
$trend = "none";

if (!empty($totals)) {
    $maxSales = max($totals);
    $minSales = min($totals);

    $bestDay = date("d M", strtotime($dates[array_search($maxSales, $totals)]));
    $worstDay = date("d M", strtotime($dates[array_search($minSales, $totals)]));

    $avgSales = array_sum($totals) / count($totals);

    if (count($totals) >= 2) {
        if (end($totals) > $totals[0]) $trend = "up";
        elseif (end($totals) < $totals[0]) $trend = "down";
        else $trend = "stable";
    }
}

/* ======================
ORDERS COUNT
====================== */
$orderQuery = $conn->query("
    SELECT DATE(order_date) as date, COUNT(*) as total_orders
    FROM orders
    WHERE 1 $dateFilter
    GROUP BY DATE(order_date)
");

$orderDates = [];
$orderCounts = [];

while ($row = $orderQuery->fetch_assoc()) {
    $orderDates[] = $row['date'];
    $orderCounts[] = (int)$row['total_orders'];
}

/* ======================
SUMMARY
====================== */
$totalRevenue = array_sum($totals);
$totalOrdersCount = array_sum($orderCounts);
$profit = $totalRevenue * 0.3;

/* ======================
TOP BOOKS 🔥
====================== */
$topBooks = $conn->query("
    SELECT books.title, SUM(order_items.quantity) as total_sold
    FROM order_items
    LEFT JOIN books ON order_items.book_id = books.book_id
    GROUP BY books.title
    ORDER BY total_sold DESC
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Analytics | BookNest</title>
<link rel="stylesheet" href="../css/style.css?v=999">
</head>

<body>

<!-- Top Bar -->
<div class="topbar">
    <div class="container">
        <span>Mini Online Bookstore</span>
        <span>Sales Analytics</span>
    </div>
</div>

<!-- Navbar -->
<header class="navbar">
    <div class="container nav-inner">
        <a class="brand" href="admin-dashboard.php">
            Book<span>Nest</span>
        </a>
    </div>
</header>

<!-- Title -->
<section class="page-title">
    <div class="container">
        <h1>Sales Analytics</h1>
        <p>Track sales performance and order activity</p>
    </div>
</section>

<main class="section">
<div class="container admin-layout">

            <aside class="sidebar">
                <a href="admin-dashboard.php">Dashboard</a>
                <a href="manage-books.php">Manage Books</a>
                <a href="manage-orders.php">Manage Orders</a>
                <a class="active" href="analytics.php">Analytics</a>
                <a href="../auth/logout.php">Logout</a>
            </aside>

<section>

<!-- FILTER -->
<form method="GET" class="filters">
    <input type="date" name="from" value="<?php echo $from; ?>">
    <input type="date" name="to" value="<?php echo $to; ?>">

    <button class="btn secondary">Apply</button>

    <a href="export-sales.php?from=<?php echo $from; ?>&to=<?php echo $to; ?>" 
    class="btn filterexport-btn">
    ⬇ Export CSV
    </a>
</form>

<!-- SUMMARY -->
<div class="stat-grid">
    <div class="stat">💰 Revenue<br><strong>RM<?php echo number_format($totalRevenue,2); ?></strong></div>
    <div class="stat">📦 Orders<br><strong><?php echo $totalOrdersCount; ?></strong></div>
    <div class="stat">💸 Profit<br><strong>RM<?php echo number_format($profit,2); ?></strong></div>
</div>

<!-- INSIGHTS -->
<div class="insight-card">
    <h3>🧠 Insights</h3>
    <p>🔥 Best Day: <?php echo $bestDay; ?> (RM<?php echo number_format($maxSales,2); ?>)</p>
    <p>📉 Worst Day: <?php echo $worstDay; ?> (RM<?php echo number_format($minSales,2); ?>)</p>
    <p>📊 Avg Sales: RM<?php echo number_format($avgSales,2); ?></p>
    <p>📈 Trend:
        <?php
        if ($trend=="up") echo "📈 Increasing";
        elseif ($trend=="down") echo "📉 Decreasing";
        else echo "➡ Stable";
        ?>
    </p>
</div>

<!-- CHART -->
<div class="chart-card">
    <h2>Sales Trend</h2>
    <canvas id="salesChart"></canvas>
</div>

<!-- TOP BOOKS -->
<div class="chart-card">
    <h2>🔥 Top Selling Books</h2>
    <?php while($b = $topBooks->fetch_assoc()): ?>
        <div style="display:flex; justify-content:space-between;">
            <span><?php echo $b['title']; ?></span>
            <strong><?php echo $b['total_sold']; ?></strong>
        </div>
    <?php endwhile; ?>
</div>

</section>
</div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
new Chart(document.getElementById('salesChart'), {
    type: 'line',
    data: {
        labels: <?php echo json_encode($dates); ?>,
        datasets: [{
            label: 'Sales',
            data: <?php echo json_encode($totals); ?>,
            borderColor: '#6b4f3b',
            fill: true
        }]
    }
});
</script>

</body>
</html>