<?php
include '../app.php';
requireAdmin();

/* ======================
GET FILTER
====================== */
$from = $_GET['from'] ?? '';
$to   = $_GET['to'] ?? '';

/* ======================
CSV HEADER
====================== */
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="sales_report.csv"');

$output = fopen('php://output', 'w');

/* HEADER ROW */
fputcsv($output, [
    'Date',
    'Total Sales (RM)',
    'Total Orders'
]);

/* ======================
BASE QUERY
====================== */
$sql = "
    SELECT 
        DATE(order_date) as date,
        SUM(total_amount) as total_sales,
        COUNT(*) as total_orders
    FROM orders
    WHERE status = 'Completed'
";

$params = [];
$types = "";

/* ======================
DATE FILTER
====================== */
if (!empty($from) && !empty($to)) {
    $sql .= " AND DATE(order_date) BETWEEN ? AND ?";
    $params[] = $from;
    $params[] = $to;
    $types .= "ss";
}

$sql .= " GROUP BY DATE(order_date) ORDER BY date ASC";

/* ======================
EXECUTE
====================== */
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("SQL Error: " . $conn->error);
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

/* ======================
NO DATA
====================== */
if ($result->num_rows == 0) {
    fputcsv($output, ['No data found']);
    fclose($output);
    exit;
}

/* ======================
OUTPUT DATA
====================== */
$totalRevenue = 0;
$totalOrders = 0;

while ($row = $result->fetch_assoc()) {

    $totalRevenue += $row['total_sales'];
    $totalOrders += $row['total_orders'];

    fputcsv($output, [
        $row['date'],
        number_format($row['total_sales'], 2),
        $row['total_orders']
    ]);
}

/* ======================
SUMMARY ROW 🔥
====================== */
fputcsv($output, []); // empty line
fputcsv($output, ['TOTAL', number_format($totalRevenue,2), $totalOrders]);

fclose($output);
exit;
?>