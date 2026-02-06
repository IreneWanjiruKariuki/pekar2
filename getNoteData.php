<?php
header('Content-Type: application/json');
require_once("db_connect.php");

$delivery_no = isset($_GET['delivery_no']) ? $_GET['delivery_no'] : '';
$response = ["note" => null, "items" => []];

if ($delivery_no) {
	// Fetch note details
	$stmt = $conn->prepare("SELECT * FROM note WHERE delivery_no = ? LIMIT 1");
	$stmt->bind_param("s", $delivery_no);
	$stmt->execute();
	$result = $stmt->get_result();
	if ($row = $result->fetch_assoc()) {
		$response["note"] = $row;
	}
	$stmt->close();

	// Fetch note items
	$stmt = $conn->prepare("SELECT item, description, unit, quantity FROM note_item WHERE delivery_no = ?");
	$stmt->bind_param("s", $delivery_no);
	$stmt->execute();
	$result = $stmt->get_result();
	while ($row = $result->fetch_assoc()) {
		$response["items"][] = $row;
	}
	$stmt->close();
}

$conn->close();
echo json_encode($response);
?>
