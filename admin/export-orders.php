<?php
include '../app.php';
requireAdmin();

/* ======================
GET FILTER VALUES
====================== */
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$from = $_GET['from_date'] ?? '';
$to   = $_GET['to_date'] ?? '';

/* FIX: All Status */
if ($status === 'All Status') {
    $status = '';
}

/* ======================
CSV HEADER
====================== */
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="orders.csv"');

$output = fopen('php://output', 'w');

/* HEADER ROW */
fputcsv($output, ['Order ID', 'Customer', 'Date', 'Total', 'Status']);

/* ======================
BASE QUERY
====================== */
$sql = "
    SELECT orders.order_id, users.name, orders.order_date, orders.total_amount, orders.status
    FROM orders
    LEFT JOIN users ON orders.user_id = users.user_id
    WHERE 1
";

$params = [];
$types = "";

/* SEARCH */
if (!empty($search)) {
    $sql .= " AND (orders.order_id LIKE ? OR users.name LIKE ?)";
    $keyword = "%$search%";
    $params[] = $keyword;
    $params[] = $keyword;
    $types .= "ss";
}

/* STATUS */
if (!empty($status)) {
    $sql .= " AND orders.status = ?";
    $params[] = $status;
    $types .= "s";
}

/* DATE FILTER 🔥 */
if (!empty($from) && !empty($to)) {
    $sql .= " AND DATE(orders.order_date) BETWEEN ? AND ?";
    $params[] = $from;
    $params[] = $to;
    $types .= "ss";
}

$sql .= " ORDER BY orders.order_date DESC";

/* EXECUTE */
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("SQL Error: " . $conn->error);
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

/* DEBUG (REMOVE LATER) */
if ($result->num_rows == 0) {
    fputcsv($output, ['No data found']);
    fclose($output);
    exit;
}

/* OUTPUT */
while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        'BN' . str_pad($row['order_id'], 4, '0', STR_PAD_LEFT),
        $row['name'],
        $row['order_date'],
        $row['total_amount'],
        $row['status']
    ]);
}

fclose($output);
exit;
?>