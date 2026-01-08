<?php
require_once("db_connect.php");

$jobNo = $_GET['jobNo'] ?? null;
if (!$jobNo) {
    die("No job number specified.");
}

// Fetch card data
$card = $conn->query("SELECT * FROM card WHERE jobNo='$jobNo'")->fetch_assoc();
$items = $conn->query("SELECT * FROM card_item WHERE jobNo='$jobNo'");
$spares = $conn->query("SELECT * FROM card_spare WHERE jobNo='$jobNo'");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Update card
    $date = $_POST['date'];
    $customerName = $_POST['customerName'];
    $technicianName = $_POST['technicianName'];
    $lpo = $_POST['lpo'];
    $dateStarted = $_POST['dateStarted'];
    $dateFinished = $_POST['dateFinished'];

    $conn->query("UPDATE card SET date='$date', customer_name='$customerName', technician_name='$technicianName', lpo_no='$lpo', date_started='$dateStarted', date_finished='$dateFinished' WHERE jobNo='$jobNo'");

    // Update items: delete old, insert new
    $conn->query("DELETE FROM card_item WHERE jobNo='$jobNo'");
    foreach ($_POST['machineSerialNumbers'] as $i => $serial) {
        $desc = $_POST['jobDescriptions'][$i];
        $conn->query("INSERT INTO card_item (jobNo, machine_serial_number, job_description) VALUES ('$jobNo', '$serial', '$desc')");
    }

    // Update spares: delete old, insert new
    $conn->query("DELETE FROM card_spare WHERE jobNo='$jobNo'");
    foreach ($_POST['spareParts'] as $i => $spare) {
        $qty = $_POST['quantities'][$i];
        $unit = $_POST['unitCosts'][$i];
        $total = $qty * $unit;
        $conn->query("INSERT INTO card_spare (jobNo, spare_part, quantity, unit_cost, total) VALUES ('$jobNo', '$spare', '$qty', '$unit', '$total')");
    }

    header("Location: viewCard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Job Card</title>
    <link rel="stylesheet" href="css/card.css">
</head>
<body>
    <h2>Edit Job Card</h2>
    <form method="POST">
        <label>Date:</label>
        <input type="date" name="date" value="<?php echo htmlspecialchars($card['date']); ?>" required><br>
        <label>Customer:</label>
        <input type="text" name="customerName" value="<?php echo htmlspecialchars($card['customer_name']); ?>" required><br>
        <label>Technician Name:</label>
        <input type="text" name="technicianName" value="<?php echo htmlspecialchars($card['technician_name']); ?>" required><br>
        <label>LPO/REF:</label>
        <input type="text" name="lpo" value="<?php echo htmlspecialchars($card['lpo_no']); ?>" required><br>
        <label>Time Job Started:</label>
        <input type="date" name="dateStarted" value="<?php echo htmlspecialchars($card['date_started']); ?>" required><br>
        <label>Time Job Finished:</label>
        <input type="date" name="dateFinished" value="<?php echo htmlspecialchars($card['date_finished']); ?>" required><br>
        <h3>Item Description</h3>
        <div id="itemDescriptionContainer">
        <?php $i = 0; while($row = $items->fetch_assoc()): ?>
            <div class="item-description">
                <label>Item/Machine Serial Number:</label>
                <input type="text" name="machineSerialNumbers[]" value="<?php echo htmlspecialchars($row['machine_serial_number']); ?>" required>
                <label>Job Description/Instruction:</label>
                <textarea name="jobDescriptions[]" required><?php echo htmlspecialchars($row['job_description']); ?></textarea>
            </div>
        <?php $i++; endwhile; ?>
        </div>
        <button type="button" onclick="addItem()">+ Add Item/Machine</button>
        <h3>Spare Parts Used</h3>
        <div id="sparePartsContainer">
        <?php $i = 0; while($row = $spares->fetch_assoc()): ?>
            <div class="spare-part">
                <label>Spares Used:</label>
                <input type="text" name="spareParts[]" value="<?php echo htmlspecialchars($row['spare_part']); ?>" required>
                <label>Quantity:</label>
                <input type="text" name="quantities[]" value="<?php echo htmlspecialchars($row['quantity']); ?>" required>
                <label>Unit Cost:</label>
                <input type="text" name="unitCosts[]" value="<?php echo htmlspecialchars($row['unit_cost']); ?>" required>
            </div>
        <?php $i++; endwhile; ?>
        </div>
        <button type="button" onclick="addSparePart()">+ Add Spare Part</button>
        <br><br>
        <input type="submit" value="Save Changes">
    </form>
    <script>
        function addItem() {
            const container = document.getElementById('itemDescriptionContainer');
            const newItem = document.createElement('div');
            newItem.classList.add('item-description');
            newItem.innerHTML = `
                <label>Item/Machine Serial Number:</label>
                <input type="text" name="machineSerialNumbers[]" required>
                <label>Job Description/Instruction:</label>
                <textarea name="jobDescriptions[]" required></textarea>
            `;
            container.appendChild(newItem);
        }
        function addSparePart() {
            const container = document.getElementById('sparePartsContainer');
            const newSparePart = document.createElement('div');
            newSparePart.classList.add('spare-part');
            newSparePart.innerHTML = `
                <label>Spares Used:</label>
                <input type="text" name="spareParts[]" required>
                <label>Quantity:</label>
                <input type="text" name="quantities[]" required>
                <label>Unit Cost:</label>
                <input type="text" name="unitCosts[]" required>
            `;
            container.appendChild(newSparePart);
        }
    </script>
</body>
</html>
