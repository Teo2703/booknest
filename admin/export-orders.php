<?php
include '../app.php';
requireAdmin();

$search = isset($_GET['search']) ? $_GET['search'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="orders.csv"');

$output = fopen('php://output', 'w');

fputcsv($output, ['Order ID', 'Customer', 'Date', 'Total', 'Status']);

$sql = "
    SELECT orders.order_id, users.name, orders.order_date, orders.total_amount, orders.status
    FROM orders
    LEFT JOIN users ON orders.user_id = users.user_id
    WHERE 1
";

$params = [];
$types = "";

/* SEARCH FILTER */
if ($search != '') {
    $sql .= " AND (orders.order_id LIKE ? OR users.name LIKE ?)";
    $keyword = "%$search%";
    $params[] = $keyword;
    $params[] = $keyword;
    $types .= "ss";
}

/* STATUS FILTER */
if ($status != '' && $status != 'All Status') {
    $sql .= " AND orders.status = ?";
    $params[] = $status;
    $types .= "s";
}

$sql .= " ORDER BY orders.order_date DESC";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

/* OUTPUT CSV */
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