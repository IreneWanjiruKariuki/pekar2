
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Delivery Note</title>
    <link rel="stylesheet" href="css/card.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script>

        function resetDeliveryNo() {
            localStorage.removeItem('lastDeliveryNo');
            alert("Delivery number has been reset.");
            document.getElementById('invoiceNo').value = generateDeliveryNo(); // Update the delivery number after reset
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

if (isset($_POST['create_note'])) {
    ob_start();

    $deliver_to = mysqli_real_escape_string($conn, $_POST['deliverTo']);
    $delivery_no = mysqli_real_escape_string($conn, $_POST['deliveryNo']);
    $lpo_no = mysqli_real_escape_string($conn, $_POST['lpo']);
    $dated = mysqli_real_escape_string($conn, $_POST['dated']);
    $delivery_date = mysqli_real_escape_string($conn, $_POST['deliveryDate']);
    $delivered_by = mysqli_real_escape_string($conn, $_POST['deliveredBy']);
    $items = $_POST['items'];
    $descriptions = $_POST['descriptions'];
    $units = $_POST['units'];
    $quantities = $_POST['quantities'];

    // Step 1: Insert the main delivery note details into the note table
    $stmt = $conn->prepare("INSERT INTO note (deliver_to, delivery_no, lpo_no, dated, delivery_date, delivered_by) VALUES (?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        echo "Error preparing statement for note: " . $conn->error;
        exit();
    }
    $stmt->bind_param("ssssss", $deliver_to, $delivery_no, $lpo_no, $dated, $delivery_date, $delivered_by);
    if (!$stmt->execute()) {
        echo "Error executing note statement: " . $stmt->error;
        exit();
    }
    $stmt->close();

    // Step 2: Insert each item associated with the delivery note into the note_item table
    $stmt = $conn->prepare("INSERT INTO note_item (delivery_no, item, description, unit, quantity) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
        echo "Error preparing statement for note items: " . $conn->error;
        exit();
    }

    for ($i = 0; $i < count($items); $i++) {
        $item = mysqli_real_escape_string($conn, $items[$i]);
        $description = mysqli_real_escape_string($conn, $descriptions[$i]);
        $unit = mysqli_real_escape_string($conn, $units[$i]);
        $quantity = mysqli_real_escape_string($conn, $quantities[$i]);

        $stmt->bind_param("ssssd", $delivery_no, $item, $description, $unit, $quantity);
        if (!$stmt->execute()) {
            echo "Error executing statement for items: " . $stmt->error;
            exit();
        }
    }
    $stmt->close();

    echo "Delivery Note saved successfully!";
    header("Location: viewnote.php");
    ob_end_flush();
    exit();
}

$conn->close();
?>

    <div class="cont">
        <!--<img src="images/image.png" width="1255" height="150" class="d-inline-block align-top" alt="Logo">-->
    </div>
    <form id="deliveryNoteForm" method="POST" action="<?php print htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <h2 style="text-align: center;">DELIVERY NOTE</h2>
        

        <label for="deliverTo">DELIVER TO:</label>
        <input type="text" id="deliverTo" name="deliverTo" required><br><br>

        <label for="lpo">LPO/INVOICE NO:</label>
        <input type="text" id="lpo" name="lpo" required><br><br>

        <label for="dated">DATED:</label>
        <input type="date" id="dated" name="dated" required><br><br>

        <label for="deliveryDate">DELIVERY DATE:</label>
        <input type="date" id="deliveryDate" name="deliveryDate" required><br><br>

        <label for="deliveredBy">DELIVERED BY:</label>
        <input type="text" id="deliveredBy" name="deliveredBy" required><br><br>

        <h3>ITEMS</h3>
        <div id="itemsContainer">
            <div class="item-description">
                <label for="item1">ITEM:</label>
                <input type="text" id="item1" name="items[]" class="item-name" required>
                <label for="description1">DESCRIPTION:</label>
                <textarea id="description1" name="descriptions[]" class="item-description" required></textarea>
                <label for="unit1">UNIT:</label>
                <input type="text" id="unit1" name="units[]" class="item-unit" required>
                <label for="quantity1">QTY:</label>
                <input type="text" id="quantity1" name="quantities[]" class="item-quantity" required>
            </div>
        </div>
        <button type="button" onclick="addItem()">+ Add Item</button><br><br>

        <button type="button" onclick="generatePDF()"style="background: #ed8936;">📄 Download as PDF</button>

        <button type="button" onclick="resetDeliveryNo()"style="background: #f56565;">🔄 Reset Delivery Number</button>

        <input type="submit" name="create_note" value="Save delivery note">
    </form>
    <script>
        let itemCount = 1;

        function addItem() {
            itemCount++;
            const container = document.getElementById('itemsContainer');
            const newItem = document.createElement('div');
            newItem.classList.add('item-description');
            newItem.innerHTML = `
            <label for="item${itemCount}">ITEM:</label>
            <input type="text" id="item${itemCount}" name="items[]" class="item-name" required>
            <label for="description${itemCount}">DESCRIPTION:</label>
            <textarea id="description${itemCount}" name="descriptions[]" class="item-description" required></textarea>
            <label for="unit${itemCount}">UNIT:</label>
            <input type="text" id="unit${itemCount}" name="units[]" class="item-unit" required>
            <label for="quantity${itemCount}">QTY:</label>
            <input type="text" id="quantity${itemCount}" name="quantities[]" class="item-quantity" required>
            `;
            container.appendChild(newItem);
            newItem.style.animation = 'fadeIn 0.4s ease-out';
        }
        async function generatePDF() {
            function generateDeliveryNo() {
            let lastDeliveryNo = localStorage.getItem('lastDeliveryNo');
            if (!lastDeliveryNo) {
                lastDeliveryNo = 10;
            }
            const newDeliveryNo = parseInt(lastDeliveryNo) + 1;
            localStorage.setItem('lastDeliveryNo', newDeliveryNo);
            return `0${newDeliveryNo}`;
        }
        const deliveryNo = generateDeliveryNo(); // Call the function to generate the invoice number
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            // Getting input values
            const deliverTo = document.getElementById('deliverTo').value;
            
            const lpo = document.getElementById('lpo').value;
            const dated = document.getElementById('dated').value;
            const deliveryDate = document.getElementById('deliveryDate').value;
            const deliveredBy = document.getElementById('deliveredBy').value;

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
            doc.setFontSize(10);  // Set font size to 20
            doc.setTextColor(128, 128, 128);
            doc.text("Plumbing works, Mechanical & Electrical plant installations HVAC, Infra-Red thermography and other maintenance solutions and General Contractors", 70, 15, { maxWidth: 140 });
            doc.text("Location: Kasarani Mwiki Road", 70, 25);
            doc.text("P.O BOX 4384-00200 City Square Nairobi", 70, 31);
            doc.text("Email: pekar.industrial@gmail.com", 70, 37);
            doc.text("Cell Phone: 0722301274/0721301274", 70, 43);
            // Adding content to PDF
            doc.setFont("helvetica", "bold");
            doc.setFontSize(18);
            doc.setTextColor(0, 0, 0);
            doc.setLineWidth(0.7);
            doc.rect(15, 50, 180, 10);
            doc.text("DELIVERY NOTE", 73, 57);
            doc.setFont("helvetica", "normal");

            // Adding form details
            doc.setFontSize(11);
            doc.setLineWidth(0.2);
            doc.rect(20, 63, 90, 7);
            doc.setTextColor(50, 50, 50);
            doc.text(`DELIVER TO:`, 22, 67.5);
            doc.setTextColor(0, 0, 0);
            doc.text(`${deliverTo}`, 54, 67.5);

            doc.rect(115, 63, 80, 7);
            doc.setTextColor(50, 50, 50);
            doc.text(`DELIVERY NO:`, 117, 67.5);
            doc.setTextColor(0, 0, 0);
            doc.text(`${deliveryNo}`, 153, 67.5);

            doc.rect(20, 73, 85, 8);
            doc.rect(65, 73, 40, 8);
            doc.setTextColor(50, 50, 50);
            doc.text(`LPO/INVOICE NO:`, 22, 77.5);
            doc.setTextColor(0, 0, 0);
            doc.text(`${lpo}`, 67, 77.5);

            doc.rect(105, 73, 88, 8);
            doc.rect(146, 73, 47, 8);
            doc.setTextColor(50, 50, 50);
            doc.text(`DATED:`, 107, 77.5);
            doc.setTextColor(0, 0, 0);
            doc.text(`${dated}`, 148, 77.5);

            doc.rect(20, 81, 85, 10);
            doc.rect(65, 81, 40, 10);
            doc.setTextColor(50, 50, 50);
            doc.text(`DELIVERY DATE:`, 22, 85.5);
            doc.setTextColor(0, 0, 0);
            doc.text(`${deliveryDate}`, 67, 85.5);

            doc.rect(105, 81, 88, 10);
            doc.rect(146, 81, 47, 10);
            doc.setTextColor(50, 50, 50);
            doc.text(`DELIVERED BY:`, 107, 85.5);
            doc.text(`(Name & Signature)`, 107, 89.5);
            doc.setTextColor(0, 0, 0);
            doc.text(`${deliveredBy}`, 148, 85.5);

            let yPos=99;
            const itemsHeader = (doc, yPos) => { 
            doc.setTextColor(128, 128, 128);
            doc.setFont("helvetica", "bold");
            doc.text(`ITEM`, 20, yPos);
            doc.text(`DESCRIPTION`, 40, yPos);
            doc.text(`UNITS`, 153, yPos);
            doc.text(`QTY`, 177, yPos);
            doc.setTextColor(0, 0, 0);
            doc.setFont("helvetica", "normal");

            };
            itemsHeader(doc, 99);
            // Adding items
            yPos += 1;
            const items = document.querySelectorAll('.ite');
            items.forEach((item, index) => {
                const itemName = item.querySelector('.item-name').value;
                const description = item.querySelector('.item-description').value;
                const unit = item.querySelector('.item-unit').value;
                const quantity = item.querySelector('.item-quantity').value;

                const descriptionLines = doc.splitTextToSize(description, 100);
                const lineHeight = 4.5;
                const itemHeight = descriptionLines.length * lineHeight;

                if (yPos + itemHeight> 270) {
                    doc.addPage();
                    yPos = 20;
                    itemsHeader(doc, yPos);
                    yPos += 5;
                }
                
                doc.text(`${itemName}`, 20, yPos+lineHeight);
                doc.text(` ${description}`, 40, yPos+lineHeight, { maxWidth: 100 });
                doc.text(`${unit}`, 154, yPos+lineHeight);
                doc.text(`${quantity}`, 177, yPos+lineHeight);
                yPos += itemHeight + 2; // Adding some space between items
            });

            yPos+=8;
            if (yPos > 270) {
                doc.addPage();
                yPos = 20;
            }
            doc.setFont("helvetica", "bold");
            doc.rect(20, yPos, 173, 30);
            doc.text(`Received in good condition by client representative:`, 22, yPos+6.5);
            doc.text(`................................................................................`, 22, yPos+15.5);
            doc.text(`Name, Signature and Stamp`, 22, yPos+23.5);

            // Saving the PDF
            doc.save("delivery_note.pdf");
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