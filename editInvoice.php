<?php
require_once("db_connect.php");

// Get invoice number from query string
$invoice_no = isset($_GET['invoice_no']) ? $_GET['invoice_no'] : '';

// Fetch invoice data
$invoice = null;
$items = [];
if ($invoice_no) {
    $stmt = $conn->prepare("SELECT * FROM invoice WHERE invoice_no = ?");
    $stmt->bind_param("s", $invoice_no);
    $stmt->execute();
    $result = $stmt->get_result();
    $invoice = $result->fetch_assoc();
    $stmt->close();

    $stmt = $conn->prepare("SELECT * FROM invoice_item WHERE invoice_no = ?");
    $stmt->bind_param("s", $invoice_no);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_invoice'])) {
    $name = $_POST['name'];
    $address = $_POST['address'];
    $lpo = $_POST['lpo'];
    $contact = $_POST['contact'];
    $delivery_no = $_POST['deliveryNo'];
    $tel = $_POST['tel'];
    $dated = $_POST['dated'];

    $stmt = $conn->prepare("UPDATE invoice SET name=?, address=?, lpo_no=?, contact=?, delivery_no=?, tel=?, date=? WHERE invoice_no=?");
    $stmt->bind_param("ssssssss", $name, $address, $lpo, $contact, $delivery_no, $tel, $dated, $invoice_no);
    $stmt->execute();
    $stmt->close();

    // Update items
    if (isset($_POST['items'])) {
        foreach ($_POST['items'] as $idx => $item_code) {
            $description = $_POST['descriptions'][$idx];
            $quantity = $_POST['quantities'][$idx];
            $unit_price = $_POST['unit_prices'][$idx];
            $vatable = isset($_POST['vatables'][$idx]) ? 1 : 0;
            //$item_id = $_POST['item_ids'][$idx];

            $total_cost = $quantity * $unit_price;
            $stmt = $conn->prepare("UPDATE invoice_item SET item_code=?, description=?, quantity=?, unit_price=?, total_cost=?, vatable=? WHERE id=?");
            $stmt->bind_param("ssiddii", $item_code, $description, $quantity, $unit_price, $total_cost, $vatable, $item_id);
            $stmt->execute();
            $stmt->close();
        }
    }
    echo "<script>alert('Invoice updated successfully!');window.location='viewInvoice.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Invoice</title>
    <link rel="stylesheet" href="css/card.css">
    <style>
        .container { max-width: 800px; margin: 2rem auto; background: #fff; padding: 1rem; border-radius: 8px; }
        .remove-btn { background: #e34343ff; margin-left: 1rem; margin-top: 1.5rem; }
    </style>
</head>
<body>
    <nav class="nav">
        <!-- ...existing nav code... -->
    </nav>
    <div class="container">
        <h2>Edit Invoice</h2>
        <?php if ($invoice): ?>
        <form method="POST">
            <label>Invoice No: <b><?= htmlspecialchars($invoice['invoice_no']) ?></b></label><br>
            <label>Name:</label>
            <input type="text" name="name" value="<?= htmlspecialchars($invoice['name']) ?>" required><br>
            <label>Address:</label>
            <input type="text" name="address" value="<?= htmlspecialchars($invoice['address']) ?>" required><br>
            <label>LPO No:</label>
            <input type="text" name="lpo" value="<?= htmlspecialchars($invoice['lpo_no']) ?>" required><br>
            <label>Contact:</label>
            <input type="text" name="contact" value="<?= htmlspecialchars($invoice['contact']) ?>" required><br>
            <label>Delivery No/Job Card No:</label>
            <input type="text" name="deliveryNo" value="<?= htmlspecialchars($invoice['delivery_no']) ?>" required><br>
            <label>Tel:</label>
            <input type="text" name="tel" value="<?= htmlspecialchars($invoice['tel']) ?>" required><br>
            <label>Date:</label>
            <input type="date" name="dated" value="<?= htmlspecialchars($invoice['date']) ?>" required><br>
            <h3>Items</h3>
            <?php foreach ($items as $idx => $item): ?>
                <div class="item-description ite">
                    <!--<input type="hidden" name="item_ids[]" value="<?= $item['id'] ?>">-->
                    <label>Item Code:</label>
                    <input type="text" name="items[]" class="item-code" value="<?= htmlspecialchars($item['item_code']) ?>" required>
                    <label>Description:</label>
                    <textarea name="descriptions[]" class="item-description" required><?= htmlspecialchars($item['description']) ?></textarea>
                    <label>Qty:</label>
                    <input type="text" name="quantities[]" class="item-quantity" value="<?= htmlspecialchars($item['quantity']) ?>" required>
                    <label>Unit Price:</label>
                    <input type="text" step="0.01" name="unit_prices[]" class="item-unit" value="<?= htmlspecialchars($item['unit_price']) ?>" required>
                    <label class="inline-label">VATABLE:</label>
                    <input type="checkbox" name="vatables[]" class="item-vatable large-checkbox" <?= ($item['vatable']) ? 'checked' : '' ?>>
                    <button type="button" class="remove-btn" onclick="this.parentElement.remove()">Remove</button>
                </div>
            <?php endforeach; ?>
            <button type="button" onclick="addItem()">+ Add Item</button>
            <input type="submit" name="update_invoice" value="Update Invoice">
        </form>
        <?php else: ?>
            <p>No invoice selected or invoice not found.</p>
        <?php endif; ?>
    </div>
<script>

function calculateTotals() {
    let total = 0;
    let totalVAT = 0;
    const items = document.querySelectorAll('.ite');
    items.forEach((item, index) => {
        const unitPrice = parseFloat(item.querySelector('.item-unit').value) || 0;
        const quantity = parseFloat(item.querySelector('.item-quantity').value) || 0;
        const totalCost = unitPrice * quantity;
        const isVatable = item.querySelector('.item-vatable') ? item.querySelector('.item-vatable').checked : false;
        let vat = isVatable ? totalCost * 0.16 : 0;
        totalVAT += vat;
        total += totalCost;
    });
    const grandTotal = total + totalVAT;
    return { total, totalVAT, grandTotal };
}

function showTotals() {
    const { total, totalVAT, grandTotal } = calculateTotals();
    document.getElementById('totalsDisplay').innerHTML =
        `<b>Total:</b> ${total.toLocaleString('en-US', { minimumFractionDigits: 2 })} <br>` +
        `<b>Total VAT:</b> ${totalVAT.toLocaleString('en-US', { minimumFractionDigits: 2 })} <br>` +
        `<b>Grand Total:</b> ${grandTotal.toLocaleString('en-US', { minimumFractionDigits: 2 })}`;
}
</script>
</body>
<script>
function addItem() {
    const container = document.querySelector('form');
    const addBtn = document.querySelector('button[onclick="addItem()"]');
    const div = document.createElement('div');
    div.className = 'item-description ite';
    div.innerHTML = `
        <label>Item Code:</label>
        <input type="text" name="items[]" class="item-code" required>
        <label>Description:</label>
        <textarea name="descriptions[]" class="item-description" required></textarea>
        <label>Qty:</label>
        <input type="text" name="quantities[]" class="item-quantity" required>
        <label>Unit Price:</label>
        <input type="text" step="0.01" name="unit_prices[]" class="item-unit" required>
        <label class="inline-label">VATABLE:</label>
        <input type="checkbox" name="vatables[]" class="item-vatable large-checkbox">
        <button type="button" class="remove-btn" onclick="this.parentElement.remove()">Remove</button>
    `;
    container.insertBefore(div, addBtn);
}
</script>
</html>