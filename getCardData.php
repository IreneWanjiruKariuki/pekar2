<?php
header('Content-Type: application/json');
require_once("db_connect.php");

$jobNo = $_GET['jobNo'] ?? null;
if (!$jobNo) {
    echo json_encode(["error" => "No job number specified."]);
    exit();
}

// Fetch card data
$card = $conn->query("SELECT * FROM card WHERE jobNo='$jobNo'")->fetch_assoc();
$items = [];
$spares = [];

$itemResult = $conn->query("SELECT * FROM card_item WHERE jobNo='$jobNo'");
while ($row = $itemResult->fetch_assoc()) {
    $items[] = $row;
}

$spareResult = $conn->query("SELECT * FROM card_spare WHERE jobNo='$jobNo'");
while ($row = $spareResult->fetch_assoc()) {
    $spares[] = $row;
}

$data = [
    "card" => $card,
    "items" => $items,
    "spares" => $spares
];

echo json_encode($data);
?>
