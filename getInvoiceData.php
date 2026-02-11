<?php
require_once("db_connect.php");
header('Content-Type: application/json');

if (!isset($_GET['invoice_no'])) {
    echo json_encode(['error' => 'Missing invoice_no parameter.']);
    exit;
}

$invoice_no = $conn->real_escape_string($_GET['invoice_no']);

// Fetch invoice main data
$invoice_sql = "SELECT * FROM invoice WHERE invoice_no = '$invoice_no' LIMIT 1";
$invoice_result = $conn->query($invoice_sql);

if ($invoice_result && $invoice_result->num_rows > 0) {
    $invoice = $invoice_result->fetch_assoc();
} else {
    echo json_encode(['error' => 'Invoice not found.']);
    exit;
}

// Fetch invoice items
$items_sql = "SELECT * FROM invoice_item WHERE invoice_no = '$invoice_no'";
$items_result = $conn->query($items_sql);
$items = [];
if ($items_result) {
    while ($item = $items_result->fetch_assoc()) {
        $items[] = $item;
    }
}

// Output as JSON
$response = [
    'invoice' => $invoice,
    'items' => $items
];
echo json_encode($response);
$conn->close();
