<?php
require_once("db_connect.php");

$delivery_no = isset($_GET['delivery_no']) ? $_GET['delivery_no'] : '';
$note = null;
$items = [];

if ($delivery_no) {
	// Fetch note details
	$stmt = $conn->prepare("SELECT * FROM note WHERE delivery_no = ?");
	$stmt->bind_param("s", $delivery_no);
	$stmt->execute();
	$result = $stmt->get_result();
	$note = $result->fetch_assoc();
	$stmt->close();

	// Fetch note items
	$stmt = $conn->prepare("SELECT * FROM note_item WHERE delivery_no = ?");
	$stmt->bind_param("s", $delivery_no);
	$stmt->execute();
	$result = $stmt->get_result();
	while ($row = $result->fetch_assoc()) {
		$items[] = $row;
	}
	$stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$deliver_to = $_POST['deliverTo'];
	$lpo_no = $_POST['lpo'];
	$dated = $_POST['dated'];
	$delivery_date = $_POST['deliveryDate'];
	$delivered_by = $_POST['deliveredBy'];
	$itemsArr = $_POST['items'];
	$descriptions = $_POST['descriptions'];
	$units = $_POST['units'];
	$quantities = $_POST['quantities'];

	// Update main note
	$stmt = $conn->prepare("UPDATE note SET deliver_to=?, lpo_no=?, dated=?, delivery_date=?, delivered_by=? WHERE delivery_no=?");
	$stmt->bind_param("ssssss", $deliver_to, $lpo_no, $dated, $delivery_date, $delivered_by, $delivery_no);
	$stmt->execute();
	$stmt->close();

	// Delete old items
	$stmt = $conn->prepare("DELETE FROM note_item WHERE delivery_no=?");
	$stmt->bind_param("s", $delivery_no);
	$stmt->execute();
	$stmt->close();

	// Insert new items
	$stmt = $conn->prepare("INSERT INTO note_item (delivery_no, item, description, unit, quantity) VALUES (?, ?, ?, ?, ?)");
	for ($i = 0; $i < count($itemsArr); $i++) {
		$item = $itemsArr[$i];
		$description = $descriptions[$i];
		$unit = $units[$i];
		$quantity = $quantities[$i];
		$stmt->bind_param("ssssd", $delivery_no, $item, $description, $unit, $quantity);
		$stmt->execute();
	}
	$stmt->close();

	header("Location: viewNote.php");
	exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Edit Delivery Note</title>
	<link rel="stylesheet" href="css/card.css">
	<style>
		body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
		.container { max-width: 800px; margin: 2rem auto; background: #fff; padding: 1rem; border-radius: 8px; }
		label { display: block; margin-top: 1rem; }
		input, textarea { width: 100%; padding: 0.5rem; margin-top: 0.25rem; }
		.item-row { border-bottom: 1px solid #eee; margin-bottom: 1rem; padding-bottom: 1rem; }
		.actions { margin-top: 1.5rem; }
		button { padding: 0.5rem 1.5rem; border: none; border-radius: 4px; background: #2a5298; color: #fff; font-weight: 600; cursor: pointer; }
		.remove-btn { background: #e34343ff; margin-left: 1rem; margin-top: 1.5rem; }
	</style>
	<script>
		function addItemRow() {
			const container = document.getElementById('itemsContainer');
			const div = document.createElement('div');
			div.className = 'item-description';
			div.innerHTML = `
				<label>ITEM: <input type="text" name="items[]" required></label>
				<label>DESCRIPTION: <textarea name="descriptions[]" required></textarea></label>
				<label>UNIT: <input type="text" name="units[]" required></label>
				<label>QTY: <input type="number" name="quantities[]" required></label>
				<button type="button" class="remove-btn" onclick="this.parentElement.remove()">Remove</button>
			`;
			container.appendChild(div);
		}
	</script>
</head>
<body>
	<div class="container">
		<h2>Edit Delivery Note</h2>
		<?php if ($note): ?>
		<form method="POST">
			<label>DELIVER TO:
				<input type="text" name="deliverTo" value="<?php echo htmlspecialchars($note['deliver_to']); ?>" required>
			</label>
			<label>LPO/INVOICE NO:
				<input type="text" name="lpo" value="<?php echo htmlspecialchars($note['lpo_no']); ?>" required>
			</label>
			<label>DATED:
				<input type="date" name="dated" value="<?php echo htmlspecialchars($note['dated']); ?>" required>
			</label>
			<label>DELIVERY DATE:
				<input type="date" name="deliveryDate" value="<?php echo htmlspecialchars($note['delivery_date']); ?>" required>
			</label>
			<label>DELIVERED BY:
				<input type="text" name="deliveredBy" value="<?php echo htmlspecialchars($note['delivered_by']); ?>" required>
			</label>
			<h3>ITEMS</h3>
			<div id="itemsContainer">
				<?php foreach ($items as $item): ?>
				<div class="item-description">
					<label>ITEM:
						<input type="text" name="items[]" value="<?php echo htmlspecialchars($item['item']); ?>" required>
					</label>
					<label>DESCRIPTION:
						<textarea name="descriptions[]" required><?php echo htmlspecialchars($item['description']); ?></textarea>
					</label>
					<label>UNIT:
						<input type="text" name="units[]" value="<?php echo htmlspecialchars($item['unit']); ?>" required>
					</label>
					<label>QTY:
						<input type="number" name="quantities[]" value="<?php echo htmlspecialchars($item['quantity']); ?>" required>
					</label>
					<button type="button" class="remove-btn" onclick="this.parentElement.remove()">Remove</button>
				</div>
				<?php endforeach; ?>
			</div>
			<button type="button" onclick="addItemRow()">+ Add Item</button>
			<div class="actions">
                <input type="submit" value="Save Changes">
			</div>
		</form>
		<?php else: ?>
			<p>Delivery note not found.</p>
		<?php endif; ?>
	</div>
</body>
</html>
