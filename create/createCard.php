<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Job Card - Pekar Industrial</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <link rel="stylesheet" href="card.css">
    <script>
        function resetJobNumber() {
            localStorage.removeItem('lastJobNumber');
            alert("Job number has been reset successfully!");
            location.reload();
        }

        function toggleNavbar() {
            const nav = document.querySelector('nav');
            nav.classList.toggle('responsive');
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
                <li><a href="#" class="nav-link active">Home</a></li>
                <li><a href="viewCard.html" class="nav-link">Job cards</a></li>
                <li><a href="viewNote.html" class="nav-link">Delivery notes</a></li>
                <li><a href="viewInvoice.html" class="nav-link">Invoices</a></li>
            </ul>
        </div>
    </nav>

    <?php
    require_once("db_connect.php");

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        ob_start();

        $jobNumber = mysqli_real_escape_string($conn, $_POST['jobNumber']);
        $date = mysqli_real_escape_string($conn, $_POST['date']);
        $customerName = mysqli_real_escape_string($conn, $_POST['customerName']);
        $technicianName = mysqli_real_escape_string($conn, $_POST['technicianName']);
        $lpo = mysqli_real_escape_string($conn, $_POST['lpo']);
        $dateStarted = mysqli_real_escape_string($conn, $_POST['dateStarted']);
        $dateFinished = mysqli_real_escape_string($conn, $_POST['dateFinished']);
        $machineSerialNumbers = $_POST['machineSerialNumbers'];
        $jobDescriptions = $_POST['jobDescriptions'];
        $spareParts = $_POST['spareParts'];
        $quantities = $_POST['quantities'];
        $unitCosts = $_POST['unitCosts'];

        $stmt = $conn->prepare("INSERT INTO card (jobNo, date, customer_name, technician_name, date_started, date_finished, lpo_no) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            echo "<div class='success-message' style='background: #f56565;'>Error preparing statement for card: " . $conn->error . "</div>";
            exit();
        }
        $stmt->bind_param("sssssss", $jobNumber, $date, $customerName, $technicianName, $dateStarted, $dateFinished, $lpo);
        if (!$stmt->execute()) {
            echo "<div class='success-message' style='background: #f56565;'>Error executing card statement: " . $stmt->error . "</div>";
            exit();
        }
        $stmt->close();

        $stmt = $conn->prepare("INSERT INTO card_item (jobNo, machine_serial_number, job_description) VALUES (?, ?, ?)");
        if (!$stmt) {
            echo "<div class='success-message' style='background: #f56565;'>Error preparing statement for card items: " . $conn->error . "</div>";
            exit();
        }

        for ($i = 0; $i < count($machineSerialNumbers); $i++) {
            $machineSerialNumber = mysqli_real_escape_string($conn, $machineSerialNumbers[$i]);
            $jobDescription = mysqli_real_escape_string($conn, $jobDescriptions[$i]);

            $stmt->bind_param("sss", $jobNumber, $machineSerialNumber, $jobDescription);
            if (!$stmt->execute()) {
                echo "<div class='success-message' style='background: #f56565;'>Error executing statement for card items: " . $stmt->error . "</div>";
                exit();
            }
        }
        $stmt->close();

        $stmt = $conn->prepare("INSERT INTO card_spare (jobNo, spare_part, quantity, unit_cost, total) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            echo "<div class='success-message' style='background: #f56565;'>Error preparing statement for card spares: " . $conn->error . "</div>";
            exit();
        }

        for ($i = 0; $i < count($spareParts); $i++) {
            $sparePart = mysqli_real_escape_string($conn, $spareParts[$i]);
            $quantity = mysqli_real_escape_string($conn, $quantities[$i]);
            $unitCost = mysqli_real_escape_string($conn, $unitCosts[$i]);
            $total = $quantity * $unitCost;

            $stmt->bind_param("ssidd", $jobNumber, $sparePart, $quantity, $unitCost, $total);
            if (!$stmt->execute()) {
                echo "<div class='success-message' style='background: #f56565;'>Error executing statement for card spares: " . $stmt->error . "</div>";
                exit();
            }
        }
        $stmt->close();

        echo "<div class='success-message'>Job Card saved successfully!</div>";
        header("Location: viewcard.php");
        ob_end_flush();
        exit();
    }

    $conn->close();
    ?>

    <div class="cont">
        <img src="images/image.png" alt="Pekar Industrial Logo">
    </div>

    <form id="jobCardForm" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <h2>JOB CARD / EQUIPMENT HANDOVER</h2>

        <label for="date">Date:</label>
        <input type="date" id="date" name="date" required>

        <label for="customerName">Customer:</label>
        <input type="text" id="customerName" name="customerName" required>

        <label for="technicianName">Technician Name:</label>
        <input type="text" id="technicianName" name="technicianName" required>

        <label for="lpo">LPO/REF:</label>
        <input type="text" id="lpo" name="lpo" required>

        <label for="dateStarted">Time Job Started:</label>
        <input type="date" id="dateStarted" name="dateStarted" required>

        <label for="dateFinished">Time Job Finished:</label>
        <input type="date" id="dateFinished" name="dateFinished" required>
        
        <h3>Item Description</h3>
        <div id="itemDescriptionContainer">
            <div class="item-description">
                <label for="machineSerialNumber1">Item/Machine Serial Number:</label>
                <input type="text" id="machineSerialNumber1" name="machineSerialNumbers[]" class="machine-serial-number" required>
                <label for="jobDescription1">Job Description/Instruction:</label>
                <textarea id="jobDescription1" name="jobDescriptions[]" class="job-description" required></textarea>
            </div>
        </div>
        <button type="button" onclick="addItem()">+ Add Item/Machine</button>

        <h3>Spare Parts Used</h3>
        <div id="sparePartsContainer">
            <div class="spare-part">
                <label for="sparePart1">Spares Used:</label>
                <input type="text" id="sparePart1" name="spareParts[]" class="spare-part-name" required>
                <label for="quantity1">Quantity:</label>
                <input type="text" id="quantity1" name="quantities[]" class="spare-part-quantity" required>
                <label for="unitCost1">Unit Cost:</label>
                <input type="text" id="unitCost1" name="unitCosts[]" class="spare-part-unit-cost" required>
            </div>
        </div>
        <button type="button" onclick="addSparePart()">+ Add Spare Part</button>

        <div style="margin-top: 2rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <button type="button" onclick="generatePDF()" style="background: #ed8936;">📄 Download as PDF</button>
            <button type="button" onclick="resetJobNumber()" style="background: #f56565;">🔄 Reset Job Number</button>
        </div>

        <input type="submit" value="💾 Save Job Card">
    </form>

    <script>
        let sparePartCount = 1;
        let itemCount = 1;

        function addItem() {
            itemCount++;
            const container = document.getElementById('itemDescriptionContainer');
            const newItem = document.createElement('div');
            newItem.classList.add('item-description');
            newItem.innerHTML = `
                <label for="machineSerialNumber${itemCount}">Item/Machine Serial Number:</label>
                <input type="text" id="machineSerialNumber${itemCount}" name="machineSerialNumbers[]" class="machine-serial-number" required>
                <label for="jobDescription${itemCount}">Job Description/Instruction:</label>
                <textarea id="jobDescription${itemCount}" name="jobDescriptions[]" class="job-description" required></textarea>
            `;
            container.appendChild(newItem);
            newItem.style.animation = 'fadeIn 0.4s ease-out';
        }

        function calculateTotals() {
            let total = 0;
            const items = document.querySelectorAll('.spare-part');
            items.forEach((sparePart) => {
                const unitPrice = parseFloat(sparePart.querySelector('.spare-part-unit-cost').value) || 0;
                const quantity = parseFloat(sparePart.querySelector('.spare-part-quantity').value) || 0;
                const totalCost = unitPrice * quantity;
                total += totalCost;
            });
            return { total };
        }

        function addSparePart() { 
            sparePartCount++;
            const container = document.getElementById('sparePartsContainer');
            const newSparePart = document.createElement('div');
            newSparePart.classList.add('spare-part');
            newSparePart.innerHTML = `
                <label for="sparePart${sparePartCount}">Spares Used:</label>
                <input type="text" id="sparePart${sparePartCount}" name="spareParts[]" class="spare-part-name" required>
                <label for="quantity${sparePartCount}">Quantity:</label>
                <input type="text" id="quantity${sparePartCount}" name="quantities[]" class="spare-part-quantity" required>
                <label for="unitCost${sparePartCount}">Unit Cost:</label>
                <input type="text" id="unitCost${sparePartCount}" name="unitCosts[]" class="spare-part-unit-cost" required>
            `;
            container.appendChild(newSparePart);
            newSparePart.style.animation = 'fadeIn 0.4s ease-out';
        }

        async function generatePDF() {
            function generateJobNumber() {
                let lastJobNumber = localStorage.getItem('lastJobNumber');
                if (!lastJobNumber) {
                    lastJobNumber = 23;
                }
                const newJobNumber = parseInt(lastJobNumber) + 1;
                localStorage.setItem('lastJobNumber', newJobNumber);
                return `0${newJobNumber}`;
            }
            const jobNumber = generateJobNumber();
            const { total } = calculateTotals();

            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            const date = document.getElementById('date').value;
            const customerName = document.getElementById('customerName').value;
            const technicianName = document.getElementById('technicianName').value;
            const lpo = document.getElementById('lpo').value;
            const dateStarted = document.getElementById('dateStarted').value;
            const dateFinished = document.getElementById('dateFinished').value;

            if (!jsPDF) {
                alert("jsPDF failed to load. Please check your internet connection.");
                return;
            }

            const img = new Image();
            img.src = 'images/image.png';
            doc.addImage(img, 'PNG', 10, 10, 50, 35);
            doc.setFont("helvetica", "normal");
            doc.setFontSize(10);
            doc.setTextColor(128, 128, 128);
            doc.text("Plumbing works, Mechanical & Electrical plant installations HVAC, Infra-Red thermography and other maintenance solutions and General Contractors", 70, 15, { maxWidth: 140 });
            doc.text("Location: Kasarani Mwiki Road", 70, 25);
            doc.text("P.O BOX 4384-00200 City Square Nairobi", 70, 31);
            doc.text("Email: pekar.industrial@gmail.com", 70, 37);
            doc.text("Cell Phone: 0722301274/0721301274", 70, 43);
            
            doc.setFont("helvetica", "bold");
            doc.setFontSize(18);
            doc.setTextColor(0, 0, 0);
            doc.setLineWidth(0.7);
            doc.rect(15, 50, 180, 10);
            doc.text("JOB CARD/EQUIPMENT HANDOVER", 55, 57);
            doc.setFont("helvetica", "normal");

            doc.setFontSize(11);
            doc.setLineWidth(0.1);
    
            doc.setTextColor(50, 50, 50);
            doc.text(`DATE:`, 20, 65);
            doc.setTextColor(0, 0, 0);
            doc.text(`${date}`, 35, 65);
            doc.setTextColor(50, 50, 50);
            doc.text(`JOB NO:`, 140, 65);
            doc.setTextColor(0, 0, 0);
            doc.text(`${jobNumber}`, 160, 65);
    
            doc.rect(20, 69, 180, 7);
            doc.setTextColor(50, 50, 50);
            doc.text(`CUSTOMER:`, 22, 73.5);
            doc.setTextColor(0, 0, 0);
            doc.text(`${customerName}`, 50, 73.5);
    
            doc.rect(20, 76, 90, 8);
            doc.rect(20, 76, 40, 8);
            doc.setTextColor(50, 50, 50);
            doc.text("TECHNICIAN :", 22, 80.5, { maxWidth: 40 });
            doc.setTextColor(0, 0, 0);
            doc.text(`${technicianName}`, 62, 80.5);
    
            doc.rect(110, 76, 90, 8);
            doc.rect(110, 76, 40, 8);
            doc.setTextColor(50, 50, 50);
            doc.text("LPO/REF:", 112, 80.5, { maxWidth: 50 });
            doc.setTextColor(0, 0, 0);
            doc.text(`${lpo}`, 152, 80.5);
    
            doc.rect(20, 84, 90, 8);
            doc.rect(20, 84, 40, 8);
            doc.setTextColor(50, 50, 50);
            doc.text("JOB STARTED:", 22, 88.5, { maxWidth: 40 });
            doc.setTextColor(0, 0, 0);
            doc.text(`${dateStarted}`, 62, 88.5);
    
            doc.rect(110, 84, 90, 8);
            doc.rect(110, 84, 40, 8);
            doc.setTextColor(50, 50, 50);
            doc.text("JOB FINISHED:", 112, 88.5, { maxWidth: 40 });
            doc.setTextColor(0, 0, 0);
            doc.text(`${dateFinished}`, 152, 88.5);
    
            doc.setFont("helvetica", "bold");
            doc.setTextColor(128, 128, 128);

            const itemHeader = (doc, yPos) => {
                doc.text("ITEM/MACHINE SERIAL NO", 22, yPos, { maxWidth: 70 });
                doc.text("JOB DESCRIPTION/INSTRUCTION", 85, yPos);
            };

            let yPos = 100;
            itemHeader(doc, yPos);
            yPos += 5;
    
            doc.setFont("helvetica", "normal");
            doc.setTextColor(0, 0, 0);
            const itemDescriptions = document.querySelectorAll('.item-description');
            itemDescriptions.forEach((item) => {
                if (yPos > 270) {
                    doc.addPage();
                    yPos = 20;
                    itemHeader(doc, yPos);
                    yPos += 5;
                }
                const machineSerialNumber = item.querySelector(`.machine-serial-number`).value;
                const jobDescription = item.querySelector(`.job-description`).value;
                const jobDescriptionLines = doc.splitTextToSize(jobDescription, 110);
                const lineHeight = 4.5;
                const itemHeight = jobDescriptionLines.length * lineHeight;
                doc.text(`${machineSerialNumber}`, 22, yPos + lineHeight);
                doc.text(`${jobDescription}`, 85, yPos + lineHeight, { maxWidth: 110 });
                yPos += itemHeight + 1;
            });
            
            yPos += 2;

            const sparesHeader = (doc, yPos) => {
                doc.setTextColor(128, 128, 128);
                doc.setFont("helvetica", "bold");
                doc.text("SPARES USED", 20, yPos + 7);
                doc.text("QTY", 104, yPos + 7);
                doc.text("UNIT COST", 130, yPos + 7);
                doc.text("TOTAL", 165, yPos + 7);
                doc.setTextColor(0, 0, 0);
                doc.setFont("helvetica", "normal");
            };
            
            sparesHeader(doc, yPos);
            yPos += 6.5;
            const spareParts = document.querySelectorAll('.spare-part');
            spareParts.forEach((part) => {
                if (yPos > 270) {
                    doc.addPage();
                    yPos = 20;
                    sparesHeader(doc, yPos);
                    yPos += 5;
                }
                const sparePartName = part.querySelector('.spare-part-name').value;
                const quantity = part.querySelector('.spare-part-quantity').value;
                const unitCost = part.querySelector('.spare-part-unit-cost').value;
                const totalCost = (parseFloat(unitCost) * parseFloat(quantity)).toFixed(2);
                
                doc.text(sparePartName, 20, yPos + 7);
                doc.text(quantity, 105, yPos + 7);
                doc.text(unitCost, 130, yPos + 7);
                doc.text(totalCost, 165, yPos + 7);

                yPos += 6.5;
            });

            if (yPos > 270) {
                doc.addPage();
                yPos = 20;
            }
            doc.setFont("helvetica", "bold");
            doc.text("Installation, testing, and commissioning done and system left in good working condition.", 20, yPos + 10, { maxWidth: 180 });
            if (yPos > 270) {
                doc.addPage();
                yPos = 20;
            }
            doc.setFont("helvetica", "normal");
            doc.text("Technician's Name and Signature: ..........................................................", 20, yPos + 17.5);

            if (yPos > 270) {
                doc.addPage();
                yPos = 20;
            }
            doc.setFont("helvetica", "bold");
            doc.text("Confirmed by client representative.", 20, yPos + 25, { maxWidth: 180 });
            if (yPos > 270) {
                doc.addPage();
                yPos = 20;
            }
            doc.setFont("helvetica", "normal");
            doc.text("Client's Name and Signature: ...............................................................", 20, yPos + 32.5);

            doc.save("job_card.pdf");
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