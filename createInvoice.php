

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Invoice</title>
    <link rel="stylesheet" href="css/card.css">
    <style>
        .large-checkbox {
            transform: scale(1.8); /* Increase the size of the checkbox */
            margin-right: 10px; /* Optional: Add some space to the right of the checkbox */
            
        }
        .inline-label {
            display: inline-block;
            margin-right: 10px;
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <script>

        function resetInvoiceNo() {
            localStorage.removeItem('lastInvoiceNo');
            alert("Invoice number has been reset.");
            document.getElementById('invoiceNo').value = generateInvoiceNo(); // Update the invoice number after reset
        }

    </script>
    
</head>
<body>
    <nav class="nav">
        <div class="nav-container">
            <div class="nav-logo">
                <span class="logo-text">PEKAR</span>
                <span class="logo-subtext">Industrial & Construction</span>
            </div>
            <ul class="nav-menu">
                <li><a href="home.html" class="nav-link active">Home</a></li>
                <li><a href="viewCard.php" class="nav-link">Job cards</a></li>
                <li><a href="viewNote.php" class="nav-link">Delivery notes</a></li>
                <li><a href="viewInvoice.php" class="nav-link">Invoices</a></li>
            </ul>
        </div>
    </nav>

    <?php
require_once("db_connect.php");

if (isset($_POST['create_invoice'])) {
    ob_start();

    $invoice_no = mysqli_real_escape_string($conn, $_POST['invoiceNo']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $lpo = mysqli_real_escape_string($conn, $_POST['lpo']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $delivery_no = mysqli_real_escape_string($conn, $_POST['deliveryNo']);
    $tel = mysqli_real_escape_string($conn, $_POST['tel']);
    $dated = mysqli_real_escape_string($conn, $_POST['dated']);
    $items = $_POST['items'];
    $descriptions = $_POST['descriptions'];
    $quantities = $_POST['quantities'];
    $unit_prices = $_POST['unit_prices'];
    $vatables = isset($_POST['vatables']) ? $_POST['vatables'] : [];

    $total = 0;
    $totalVAT = 0;

    // Step 1: Ensure the invoice exists before adding items
    $stmt = $conn->prepare("INSERT INTO invoice (invoice_no, name, address, lpo_no, contact, delivery_no, tel, date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        echo "Error preparing statement for invoice: " . $conn->error;
        exit();
    }
    $stmt->bind_param("ssssssss", $invoice_no, $name, $address, $lpo, $contact, $delivery_no, $tel, $dated);
    if (!$stmt->execute()) {
        echo "Error executing invoice statement: " . $stmt->error;
        exit();
    }
    $stmt->close();

    // Step 2: Insert into invoice_item
    $stmt = $conn->prepare("INSERT INTO invoice_item (invoice_no, item_code, description, quantity, unit_price, total_cost, vatable) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        echo "Error preparing statement for invoice items: " . $conn->error;
        exit();
    }

    foreach ($items as $index => $item) {
        $item = mysqli_real_escape_string($conn, $item);
        $description = mysqli_real_escape_string($conn, $descriptions[$index]);
        $quantity = mysqli_real_escape_string($conn, $quantities[$index]);
        $unit_price = mysqli_real_escape_string($conn, $unit_prices[$index]);
        $row = $_POST['item_row'][$index];
        $vatable = isset($_POST['vatables'][$row]) ? 1 : 0;

        $total_cost = $quantity * $unit_price;
        $vat = $vatable ? $total_cost * 0.16 : 0;

        $total += $total_cost;
        $totalVAT += $vat;

        $stmt->bind_param("sssiddi", $invoice_no, $item, $description, $quantity, $unit_price, $total_cost, $vatable);

        if (!$stmt->execute()) {
            echo "Error executing statement for items: " . $stmt->error;
            exit();
        }
    }
    $stmt->close();

    // Step 3: Update total, VAT, and grand total in the invoice table
    $grand_total = $total + $totalVAT;
    $stmt = $conn->prepare("UPDATE invoice SET total = ?, vat = ?, grand_total = ? WHERE invoice_no = ?");
    if (!$stmt) {
        echo "Error preparing statement for invoice update: " . $conn->error;
        exit();
    }

    $stmt->bind_param("ddds", $total, $totalVAT, $grand_total, $invoice_no);

    if (!$stmt->execute()) {
        echo "Error executing update statement: " . $stmt->error;
    }
    $stmt->close();

    echo "Invoice saved successfully!";
    header("Location: viewinvoice.php");
    ob_end_flush();
    exit();
}

$conn->close();
?>
    <div class="cont">
        <!--<img src="images/image.png" width="1255" height="150" class="d-inline-block align-top" alt="Logo">-->
    </div>
    <form id="invoiceForm" method="POST" action="<?php print htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <h2 style="text-align: center;">INVOICE</h2>
        <input type="hidden" id="invoiceNo" name="invoiceNo" value="">
        

        <label for="name">NAME:</label>
        <input type="text" id="name" name="name"required><br><br>

        <label for="address">ADDRESS:</label>
        <input type="text" id="address" name="address"required><br><br>

        <label for="lpo">LPO NO:</label>
        <input type="text" id="lpo" name="lpo" required><br><br>

        <label for="contact">CONTACT:</label>
        <input type="text" id="contact" name="contact" required><br><br>

        <label for="deliveryNo">DELIVERY NO/JOB CARD NO:</label>
        <input type="text" id="deliveryNo" name="deliveryNo" required><br><br>

        <label for="tel">TEL:</label>
        <input type="text" id="tel" name="tel" required><br><br>

        <label for="dated">DATE:</label>
        <input type="date" id="dated" name="dated" required><br><br>

        <h3>ITEMS</h3>
        <div id="itemsContainer">
            <div class="item-description">
                <input type="hidden" name="item_row[]" value="0">
                <label for="item1">ITEM CODE:</label>
                <input type="text" id="item1" name="items[]" class="item-code" required>
                <label for="description1">DESCRIPTION:</label>
                <textarea id="description1" name="descriptions[]" class="item-description" required></textarea>
                <label for="quantity1">QTY:</label>
                <input type="text" id="quantity1" name="quantities[]" class="item-quantity" required>
                <label for="unit1">UNIT PRICE:</label>
                <input type="text" id="unit1" name="unit_prices[]"class="item-unit" required>
                <label for="vatable1" class="inline-label">VATABLE:</label>
                <input type="checkbox" id="vatable1" name="vatables[0]"class="item-vatable large-checkbox">
            </div>
        </div>
        <button type="button" onclick="addItem()">+ Add Item</button><br><br>

        <button type="button" onclick="generatePDF()"style="background: #ed8936;">📄 Download as PDF</button>

        <button type="button" onclick="resetInvoiceNo()"style="background: #f56565;">🔄 Reset Invoice Number</button>

        <input type="submit" name="create_invoice" value="Save invoice" onclick="setInvoiceNoBeforeSubmit(event)">
    </form>
    <script>
        // Function to generate invoice number
        function generateInvoiceNo() {
            let lastInvoiceNo = localStorage.getItem('lastInvoiceNo');
            if (!lastInvoiceNo) {
                lastInvoiceNo = 200;
            }
            const newInvoiceNo = parseInt(lastInvoiceNo) + 1;
            localStorage.setItem('lastInvoiceNo', newInvoiceNo);
            return `00${newInvoiceNo}`;
        }

        // Set invoice number before form submit
        function setInvoiceNoBeforeSubmit(event) {
            const invoiceNoField = document.getElementById('invoiceNo');
            if (invoiceNoField.value === '') {
                invoiceNoField.value = generateInvoiceNo();
            }
            // Allow form to submit
        }

        let itemCount = 1;
        function addItem() {
            itemCount++;
            const container = document.getElementById('itemsContainer');
            const newItem = document.createElement('div');
            newItem.classList.add('item-description');
            newItem.innerHTML = `
            <input type="hidden" name="item_row[]" value="${itemCount-1}">
            <label for="item${itemCount}">ITEM CODE:</label>
            <input type="text" id="item${itemCount}" name="items[]" class="item-code" required>
            <label for="description${itemCount}">DESCRIPTION:</label>
            <textarea id="description${itemCount}" name="descriptions[]" class="item-description" required></textarea>
            <label for="quantity${itemCount}">QTY:</label>
            <input type="text" id="quantity${itemCount}" name="quantities[]" class="item-quantity" required>
            <label for="unit${itemCount}">UNIT PRICE:</label>
            <input type="text" id="unit${itemCount}" name="unit_prices[]" class="item-unit" required>
            <label for="vatable${itemCount}" class="inline-label">VATABLE:</label>
            <input type="checkbox" id="vatable${itemCount}" name="vatables[${itemCount-1}]" class="item-vatable large-checkbox">
            `;
            container.appendChild(newItem);
        }

        function calculateTotals() {
            let total = 0;
            let totalVAT = 0;
            const items = document.querySelectorAll('.ite');
            items.forEach((item, index) => {
                const unitPrice = parseFloat(item.querySelector('.item-unit').value) || 0;
                const quantity = parseFloat(item.querySelector('.item-quantity').value) || 0;
                const totalCost = unitPrice * quantity;

                const isVatable = item.querySelector('.item-vatable').checked;
                let vat = 0;
                if (isVatable) {
                    vat = totalCost * 0.16;
                    totalVAT += vat;
                }

                total += totalCost;
            });

            const grandTotal = total + totalVAT;

            return { total, totalVAT, grandTotal };
        }

        async function generatePDF() {
            // Generate the invoice number
    function generateInvoiceNo() {
        let lastInvoiceNo = localStorage.getItem('lastInvoiceNo');
        if (!lastInvoiceNo) {
            lastInvoiceNo = 200;
        }
        const newInvoiceNo = parseInt(lastInvoiceNo) + 1;
        localStorage.setItem('lastInvoiceNo', newInvoiceNo);
        return `00${newInvoiceNo}`;
    }

    const invoiceNo = generateInvoiceNo(); // Call the function to generate the invoice number
            const { total, totalVAT, grandTotal } = calculateTotals();

            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            // Getting input values
            const name = document.getElementById('name').value;
            
            const address = document.getElementById('address').value;
            const lpo = document.getElementById('lpo').value;
            const contact = document.getElementById('contact').value;
            const deliveryNo = document.getElementById('deliveryNo').value;
            const tel = document.getElementById('tel').value;
            const dated = document.getElementById('dated').value;
            
            // Ensure jsPDF is properly loaded
            if (!jsPDF) {
                alert("jsPDF failed to load. Please check your internet connection.");
                return;
            }

            
                const img = new Image();
                img.src = 'image.png'; // Replace with your image path
                doc.addImage(img, 'PNG', 10, 10, 50, 35);

                // Add two small paragraphs on the top right corner
                doc.setFont("helvetica", "normal");
                doc.setFontSize(10);
                doc.setTextColor(128, 128, 128);
                doc.text("Plumbing works, Mechanical & Electrical plant installations HVAC, Infra-Red thermography and other maintenance solutions and General Contractors", 70, 15, { maxWidth: 140 });
                doc.text("Location: Kasarani Mwiki Road", 70, 25);
                doc.text("P.O BOX 4384-00200 City Square Nairobi", 70, 29.5);
                doc.text("Email: pekar.industrial@gmail.com", 70, 33.5);
                doc.text("Cell Phone: 0722301274/0721301274", 70, 38);
                doc.text("PIN Number: P051398673W", 70, 42);
            
                // Adding content to PDF
                doc.setFont("helvetica", "bold");
                doc.setFontSize(18);
                doc.setTextColor(0, 0, 0);
                doc.setLineWidth(0.7);
                doc.rect(15, 50, 180, 10);
                doc.text("REF: INVOICE", 81, 57);
                doc.setFont("helvetica", "normal");

                // Adding form details
                doc.setFontSize(11);
                doc.setLineWidth(0.2);
                doc.setTextColor(50, 50, 50);

            doc.text(`Name:`, 20, 66);
            doc.setTextColor(0, 0, 0);
            doc.text(`${name}`, 36, 66);
            doc.text(`_____________________`, 35, 66.5);
        
            doc.setTextColor(50, 50, 50);
            doc.text(`Invoice Number:`, 103, 66);
            doc.setTextColor(0, 0, 0);
            doc.text(`${invoiceNo}`, 137, 66);
            doc.text(`___________________`, 136, 66.5);

            doc.setTextColor(50, 50, 50);
            doc.text(`Address:`, 20, 73);
            doc.setTextColor(0, 0, 0);
            doc.text(`${address}`, 40, 73);
            doc.text(`____________________`, 39, 73.5);

            doc.setTextColor(50, 50, 50);
            doc.text(`LPO Number:`, 103, 73);
            doc.setTextColor(0, 0, 0);
            doc.text(`${lpo}`, 133, 73);
            doc.text(`_____________________`, 132, 73.5);

            doc.setTextColor(50, 50, 50);
            doc.text(`Contact:`, 20, 80);
            doc.setTextColor(0, 0, 0);
            doc.text(`${contact}`, 39, 80);
            doc.text(`____________________`, 38, 80.5);

            doc.setTextColor(50, 50, 50);
            doc.text(`Delivery no/Job Card no:`, 103, 80);
            doc.setTextColor(0, 0, 0);
            doc.text(`${deliveryNo}`, 150, 80);
            doc.text(`_____________`, 149, 80.5);

            doc.setTextColor(50, 50, 50);
            doc.text(`Tel:`, 20, 87);
            doc.setTextColor(0, 0, 0);
            doc.text(`${tel}`, 33, 87);
            doc.text(`____________________`, 32, 87.5);

            doc.setTextColor(50, 50, 50);
            doc.text(`Date:`, 103, 87);
            doc.setTextColor(0, 0, 0);
            doc.text(`${dated}`, 118, 87);
            doc.text(`____________________`, 117, 87.5);

            const addTableHeader =(doc,yPos) =>{
            doc.setTextColor(128, 128, 128);
            doc.text(`ITEM CODE`, 12, yPos, { maxWidth: 21 });
            doc.text(`DESCRIPTION`, 31, yPos);
            doc.text(`QTY`, 116, yPos);
            doc.text(`UNIT PRICE`, 131, yPos, { maxWidth: 20 });
            doc.text(`VAT`, 155, yPos, { maxWidth: 20 });
            doc.text(`TOTAL COST`, 179, yPos, { maxWidth: 20 });
            doc.setTextColor(0, 0, 0);
            };

            let yPos = 97;
            addTableHeader(doc, yPos);
            yPos += 5;

            // Adding items
            //let yPos = 102;
            const items = document.querySelectorAll('.ite');
            items.forEach((item, index) => {

                if (yPos > 270) {
                    doc.addPage();
                    yPos = 20;
                    addTableHeader(doc, yPos);
                    yPos += 5;
                }
                const itemCode = item.querySelector('.item-code').value;
                const description = item.querySelector('.item-description').value;
                const quantity = item.querySelector('.item-quantity').value;
                const unitPrice = parseFloat(item.querySelector('.item-unit').value).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                const totalCost = parseFloat(item.querySelector('.item-unit').value) * parseFloat(quantity);
                const isVatable = item.querySelector('.item-vatable').checked;
                const vat = isVatable ? totalCost * 0.16 : 0;
                const totalCostWithVAT = (totalCost).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

                const descriptionLines = doc.splitTextToSize(description, 85);
                const lineHeight = 4.5;
                const itemHeight = descriptionLines.length * lineHeight;

                doc.text(`${itemCode}`, 13, yPos + lineHeight);
                doc.text(descriptionLines, 26, yPos + lineHeight, { maxWidth: 85 });
                doc.text(`${quantity}`, 118, yPos + lineHeight);
                doc.text(`${unitPrice}`, 129, yPos + lineHeight);
                doc.text(`${vat.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`, 152.5, yPos + lineHeight);
                doc.text(`${totalCostWithVAT}`, 177, yPos + lineHeight);

                yPos += itemHeight + 1; // Adding some space between items
            });

            // Adding totals

            if (yPos > 270) {
                doc.addPage();
                yPos = 20;
            }
            yPos += 3;
            doc.setTextColor(128, 128, 128);
            doc.setFont("helvetica", "bold");
            doc.text(`TOTAL:`, 56, yPos + 6.5);
            doc.setTextColor(0, 0, 0);
            doc.text(`${total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`, 87, yPos + 6.5);

            yPos += 8;
            doc.setTextColor(128, 128, 128);
            doc.text(`TOTAL VAT:`, 56, yPos + 6.5);
            doc.setTextColor(0, 0, 0);
            doc.text(`${totalVAT.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`, 87, yPos + 6.5);

            yPos += 8;
            doc.setTextColor(128, 128, 128);
            doc.text(`GRAND TOTAL:`, 56, yPos + 6.5, { maxWidth: 30 });
            doc.setTextColor(0, 0, 0);
            doc.text(`${grandTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`, 87, yPos + 6.5);

            if (yPos > 270) {
                doc.addPage();
                yPos = 20;
            }
            yPos += 13;
            doc.setFont("helvetica", "normal");
            doc.text(`Signed:`, 20, yPos + 7); 
            img.src = 'final_signature.jpg'; // Replace with your image path
            doc.addImage(img, 'JPG', 40, yPos - 5, 25, 25);
            
            if (yPos > 270) {
                doc.addPage();
                yPos = 20;
            }
            yPos += 25;
            doc.text(`______________________________________________`, 20, yPos);
            doc.text(`FOR: PEKAR INDUSTRIAL AND CONSTRUCTION LTD`, 20, yPos - 1);

            if (yPos > 270) {
                doc.addPage();
                yPos = 20;
            }
            yPos += 3.5;
            doc.rect(20, yPos, 180, 15);
            doc.text(`NOTE: Cheque to be drawn to Pekar Industrial and Construction Limited`, 45, yPos + 5);
            doc.text(`BANK: Consolidated Bank of Kenya A/C No.10011301000125, Branch:Koinange Street`, 34, yPos + 11);

            // Saving the PDF
            doc.save("invoice.pdf");
        }
    </script>
    <footer>
        <div class="footer-container">
            <!--<div class="footer-section">
                <h3>Pekar Industrial & Construction LTD</h3>
                <ul>
                    <li><h4>Location: Kasarani Mwiki Road</h4></li>
                    <li><h4>P.O Box 4384-00200 City Square Nairobi</h4></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Contact Info</h3>
                <ul>
                    <li><h4>Email: pekar.industrial@gmail.com</h4></li>
                    <li><h4>Cell Phone: 0721301274/0722301274</h4></li>
                </ul>
            </div>-->
            <div class="footer-bottom">
                © 2025 Pekar Industrial & Construction LTD | All rights reserved.
            </div>
        </div>
    </footer>
</body>
</html>