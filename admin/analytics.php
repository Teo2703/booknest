<?php
include '../app.php';
requireAdmin();

/* ======================
   FILTER (7 / 30 DAYS)
====================== */
$range = isset($_GET['range']) ? (int)$_GET['range'] : 7;

/* ======================
   SALES DATA (LINE CHART)
====================== */
$salesQuery = $conn->query("
    SELECT DATE(order_date) as date, SUM(total_amount) as total
    FROM orders
    WHERE status = 'Completed'
    AND order_date >= DATE_SUB(CURDATE(), INTERVAL $range DAY)
    GROUP BY DATE(order_date)
    ORDER BY date ASC
");

$dates = [];
$totals = [];

while ($row = $salesQuery->fetch_assoc()) {
    $dates[] = $row['date'];
    $totals[] = (float)$row['total'];
}

if (!empty($totals)) {

    $maxSales = max($totals);
    $minSales = min($totals);

    $bestDay = $dates[array_search($maxSales, $totals)];
    $worstDay = $dates[array_search($minSales, $totals)];

    $avgSales = array_sum($totals) / count($totals);

    $trend = "Stable";
    if (count($totals) >= 2) {
        if (end($totals) > $totals[0]) {
            $trend = "Increasing 📈";
        } elseif (end($totals) < $totals[0]) {
            $trend = "Decreasing 📉";
        }
    }
}

/* ======================
   ORDER COUNT (BAR CHART)
====================== */
$orderQuery = $conn->query("
    SELECT DATE(order_date) as date, COUNT(*) as total_orders
    FROM orders
    WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL $range DAY)
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Analytics | BookNest</title>
<link rel="stylesheet" href="../css/style.css">
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

<!-- Main -->
<main class="section">
<div class="container admin-layout">

<!-- Sidebar -->
<aside class="sidebar">
    <a href="admin-dashboard.php">Dashboard</a>
    <a href="manage-books.php">Manage Books</a>
    <a href="manage-orders.php">Manage Orders</a>
    <a class="active" href="analytics.php">Analytics</a>
    <a href="../index.php">Logout</a>
</aside>

<!-- Content -->
<section>

<!-- FILTER -->
<form method="GET" class="filters" style="margin-bottom:1rem;">
    <select name="range" onchange="this.form.submit()">
        <option value="7" <?php if($range==7) echo "selected"; ?>>Last 7 Days</option>
        <option value="30" <?php if($range==30) echo "selected"; ?>>Last 30 Days</option>
    </select>
</form>

<!-- SUMMARY CARDS -->
<div class="stat-grid">
    <div class="stat">
        💰 Total Revenue<br>
        <strong>RM<?php echo number_format($totalRevenue,2); ?></strong>
    </div>

    <div class="stat">
        📦 Total Orders<br>
        <strong><?php echo $totalOrdersCount; ?></strong>
    </div>
</div>

<div class="insight-card">
    <h3>🧠 Sales Insights</h3>

    <p>🔥 Best Day: <?php echo date("d M", strtotime($bestDay)); ?> 
    (RM<?php echo number_format($maxSales,2); ?>)</p>

    <p>📉 Lowest Day: <?php echo date("d M", strtotime($worstDay)); ?> 
    (RM<?php echo number_format($minSales,2); ?>)</p>

    <p>📊 Average Sales: RM<?php echo number_format($avgSales,2); ?></p>

    <p>📈 Trend: <?php echo $trend; ?></p>
</div>

<!-- SALES CHART -->
<div class="chart-card">
    <h2>Sales Trend</h2>
    <canvas id="salesChart"></canvas>
</div>

<!-- ORDERS CHART -->
<div class="chart-card">
    <h2>Orders Count</h2>
    <canvas id="orderChart"></canvas>
</div>

</section>
</div>
</main>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// SALES CHART
const salesCtx = document.getElementById('salesChart').getContext('2d');
new Chart(salesCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($dates); ?>,
        datasets: [{
            label: 'Sales (RM)',
            data: <?php echo json_encode($totals); ?>,
            borderColor: '#6b4f3b',
            backgroundColor: 'rgba(107,79,59,0.1)',
            fill: true,
            tension: 0.4
        }]
    }
});

// ORDER CHART
const orderCtx = document.getElementById('orderChart').getContext('2d');
new Chart(orderCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($orderDates); ?>,
        datasets: [{
            label: 'Orders',
            data: <?php echo json_encode($orderCounts); ?>,
            backgroundColor: '#dfeeff'
        }]
    }
});
</script>

</body>
</html>