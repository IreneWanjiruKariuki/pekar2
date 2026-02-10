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

if (!empty($_POST['removed_items'])) {
    $removed = json_decode($_POST['removed_items'], true);
    if (is_array($removed) && count($removed) > 0) {
        $in = str_repeat('?,', count($removed) - 1) . '?';
        $types = str_repeat('s', count($removed));
        $params = $removed;
        $params[] = $invoice_no;
        $types .= 's';
        $sql = "DELETE FROM invoice_item WHERE item_code IN ($in) AND invoice_no=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $stmt->close();
    }
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
        foreach ($_POST['items'] as $index => $item_code) {
            $description = $_POST['descriptions'][$index];
            $quantity = $_POST['quantities'][$index];
            $unit_price = $_POST['unit_prices'][$index];
            $row = $_POST['item_row'][$index];
            $vatable = isset($_POST['vatables'][$row]) ? 1 : 0;
            //$item_id = $_POST['item_ids'][$index];

            $total_cost = $quantity * $unit_price;
            $stmt = $conn->prepare("UPDATE invoice_item SET description=?, quantity=?, unit_price=?, total_cost=?, vatable=? WHERE invoice_no=? AND item_code=?");
            $stmt->bind_param("siddsis", $description, $quantity, $unit_price, $total_cost, $vatable, $invoice_no, $item_code);
            $stmt->execute();
            $stmt->close();
        } 
        // Recalculate totals
        $stmt = $conn->prepare("SELECT SUM(total_cost) as total, SUM(CASE WHEN vatable=1 THEN total_cost*0.16 ELSE 0 END) as vat FROM invoice_item WHERE invoice_no=?");
        $stmt->bind_param("s", $invoice_no);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $total = $row['total'] ?? 0;
        $vat = $row['vat'] ?? 0;
        $grand_total = $total + $vat;
        $stmt->close();

        // Update invoice table
        $stmt = $conn->prepare("UPDATE invoice SET total=?, vat=?, grand_total=? WHERE invoice_no=?");
        $stmt->bind_param("ddds", $total, $vat, $grand_total, $invoice_no);
        $stmt->execute();
        $stmt->close();
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
        
        .large-checkbox {
            transform: scale(1.8); /* Increase the size of the checkbox */
            margin-right: 10px; /* Optional: Add some space to the right of the checkbox */
            
        }
        .inline-label {
            display: inline-block;
            margin-right: 10px;
        }

        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    </style>
</head>
<body>
    <nav class="nav">
        <!-- ...existing nav code... -->
    </nav>
    <div class="container" id="editInvoiceForm">
        <h2>Edit Invoice</h2>
        <?php if ($invoice): ?>
        <form method="POST">
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
            <?php foreach ($items as $index => $item): ?>
                    <div class="item-description ite">
                        <input type="hidden" name="item_row[]" value="<?= $index ?>">
                        <!--<input type="hidden" name="item_ids[]" value="<?= $item['id'] ?>"> -->
                    <label>Item Code:</label>
                    <input type="text" name="items[]" class="item-code" value="<?= htmlspecialchars($item['item_code']) ?>" required>
                    <label>Description:</label>
                    <textarea name="descriptions[]" class="item-description" required><?= htmlspecialchars($item['description']) ?></textarea>
                    <label>Qty:</label>
                    <input type="text" name="quantities[]" class="item-quantity" value="<?= htmlspecialchars($item['quantity']) ?>" required>
                    <label>Unit Price:</label>
                    <input type="text" step="0.01" name="unit_prices[]" class="item-unit" value="<?= htmlspecialchars($item['unit_price']) ?>" required>
                    <label class="inline-label">VATABLE:</label>
                    <input type="checkbox" name="vatables[<?= $index ?>]" class="item-vatable large-checkbox" <?= ($item['vatable']) ? 'checked' : '' ?>><br>
                    <button type="button" class="remove-btn" onclick="removeItem(this, '<?= htmlspecialchars($item['item_code']) ?>')">Remove</button>
                </div>
            <?php endforeach; ?>
            <input type="hidden" name="removed_items" id="removed_items" value="">
            <button type="button" onclick="addItem()">+ Add Item</button>
            <input type="submit" name="update_invoice" value="Save changes">
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
let removedItemCodes = [];
function removeItem(btn, itemCode) {
    btn.parentElement.remove();
    if (itemCode) {
        removedItemCodes.push(itemCode);
        document.getElementById('removed_items').value = JSON.stringify(removedItemCodes);
    }
}
document.getElementById('editInvoiceForm').addEventListener('submit', function() {
    document.getElementById('removed_items').value = JSON.stringify(removedItemCodes);
});

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