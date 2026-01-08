<?php
    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'pekar2');

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    if(isset($_GET["DelId"])){
        $DelId=$_GET["DelId"]; 
    
        // Delete related rows from card_item
        $del_items = "DELETE FROM card_item WHERE jobNo='$DelId'";
        $conn->query($del_items);

        // Delete related rows from card_spare
        $del_spares = "DELETE FROM card_spare WHERE jobNo='$DelId'";
        $conn->query($del_spares);
        
        // sql to delete a record
        $del_card = "DELETE FROM card WHERE jobNo='$DelId' LIMIT 1";
    
        if ($conn->query($del_card) === TRUE) {
            header("Location:viewCard.php");
            exit();
        } else {
            echo "Error deleting record: " . $conn->error;
        }
    }
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Job Cards</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: #333;
            min-height: 100vh;
        }

        /* Navigation Styling */
        nav {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            flex-wrap: wrap;
            gap: 1.5rem;
        }

        nav a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        nav a:hover {
            background-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .search-container {
            display: flex;
            gap: 0.5rem;
        }

        .search-container input {
            padding: 0.6rem 1rem;
            border: none;
            border-radius: 4px;
            width: 200px;
            font-size: 0.95rem;
        }

        .search-container button {
            padding: 0.6rem 1rem;
            background-color: #ff6b6b;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .search-container button:hover {
            background-color: #ff5252;
            transform: scale(1.05);
        }

        /* Header Container */
        .cont {
            text-align: center;
            padding: 2rem 1rem;
            background-color: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .cont img {
            max-width: 100%;
            height: auto;
            max-height: 150px;
        }

        /* Main Content */
        h1 {
            text-align: center;
            color: #1e3c72;
            margin: 2rem 1rem 1.5rem;
            font-size: 2rem;
            font-weight: 700;
        }

        /* Table Styling */
        .table-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem 2rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        }

        table thead {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
        }

        table th {
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            font-size: 0.9rem;
        }

        table td {
            padding: 1rem;
            border-bottom: 1px solid #e0e0e0;
        }

        table tbody tr {
            transition: all 0.3s ease;
        }

        table tbody tr:nth-child(odd) {
            background-color: #f8f9fa;
        }

        table tbody tr:hover {
            background-color: #e3f2fd;
            box-shadow: inset 0 0 10px rgba(30, 60, 114, 0.1);
        }

        /* Button Styling */
        button {
            padding: 0.6rem 1rem;
            margin: 0.25rem;
            border: none;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        button:first-of-type {
            background-color: #4CAF50;
            color: white;
        }

        button:first-of-type:hover {
            background-color: #45a049;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(76, 175, 80, 0.3);
        }

        button:nth-of-type(2) {
            background-color: #2196F3;
            color: white;
        }

        button:nth-of-type(2):hover {
            background-color: #0b7dda;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(33, 150, 243, 0.3);
        }

        button:nth-of-type(3) {
            background-color: #f44336;
            color: white;
        }

        button:nth-of-type(3):hover {
            background-color: #da190b;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(244, 67, 54, 0.3);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #999;
            font-size: 1.1rem;
        }

        /* Footer Styling */
        footer {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 2rem;
            margin-top: 3rem;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .footer-section h3 {
            font-size: 1.2rem;
            margin-bottom: 1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .footer-section ul {
            list-style: none;
        }

        .footer-section h4 {
            font-size: 0.95rem;
            font-weight: 400;
            margin: 0.5rem 0;
            opacity: 0.9;
        }

        .footer-bottom {
            grid-column: 1 / -1;
            text-align: center;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            font-size: 0.9rem;
            opacity: 0.8;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            nav {
                flex-direction: column;
                gap: 1rem;
            }

            .search-container input {
                width: 100%;
            }

            table {
                font-size: 0.9rem;
            }

            table th, table td {
                padding: 0.75rem 0.5rem;
            }

            button {
                padding: 0.5rem 0.75rem;
                font-size: 0.8rem;
                margin: 0.2rem;
            }

            h1 {
                font-size: 1.5rem;
            }
        }
    </style>
    
</head>
<body>
    <nav>
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <a href="./">HOME</a>
            <a href="viewcard.php">VIEW JOB CARDS</a>
            <a href="viewnote.php">VIEW DELIVERY NOTES</a>
            <a href="viewinvoice.php">VIEW INVOICES</a>
        </div>
        <div class="search-container">
            <input type="text" placeholder="Search..." id="search">
            <button type="submit">🔍</button>
        </div>
    </nav>

    <div class="cont">
        <img src="images/image.png" alt="Pekar Industrial & Construction Logo">
    </div>

    

    <h1>Job Cards</h1>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>JOB NO</th>
                    <th>CUSTOMER NAME</th>
                    <th>LPO NO</th>
                    <th>DATE</th>
                    <th>ACTION</th>
                </tr>
            </thead>
            <tbody>
                <?php
                
                // Fetch job cards with only the required columns
                $sql="SELECT jobNo, customer_name, lpo_no, date FROM card ORDER BY jobNo DESC";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row["jobNo"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["customer_name"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["lpo_no"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["date"]) . "</td>";
                        echo "<td>";
                        echo "<button onclick=\"window.location.href='editcard.php?jobNo=" . urlencode($row["jobNo"]) . "'\">Edit</button>";
                        echo "<button onclick=\"generatePDF('" . addslashes($row['jobNo']) . "', '" . addslashes($row['customer_name']) . "', '" . addslashes($row['lpo_no']) . "', '" . addslashes($row['date']) . "')\">Download</button>";
                        echo "<button onclick=\"if(confirm('Are you sure you want to delete this job card?')) window.location.href='viewcard.php?DelId=" . urlencode($row["jobNo"]) . "'\">Delete</button>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' class='empty-state'>No job cards found</td></tr>";
                }

                $conn->close();
                ?>
            </tbody>
        </table>
    </div>

    <script>
        async function generatePDF(jobNo) {
            const { jsPDF } = window.jspdf;
            // Fetch all card data from the server
            const response = await fetch(`getCardData.php?jobNo=${encodeURIComponent(jobNo)}`);
            const data = await response.json();
            if (!data.card) {
                alert('Could not fetch job card data.');
                return;
            }
            const card = data.card;
            const items = data.items;
            const spares = data.spares;

            const doc = new jsPDF();

            // Add Image at the top left corner
            const img = new Image();
            img.src = 'image.png'; // Replace with your image path
            doc.addImage(img, 'PNG', 10, 10, 50, 35);
            doc.setFont("helvetica", "normal");
            doc.setFontSize(10);
            doc.setTextColor(128, 128, 128);
            doc.text("Plumbing works, Mechanical & Electrical plant installations HVAC, Infra-Red thermography and other maintenance solutions and General Contractors", 70, 15, { maxWidth: 140 });
            doc.text("Location: Kasarani Mwiki Road", 70, 25);
            doc.text("P.O BOX 4384-00200 City Square Nairobi", 70, 31);
            doc.text("Email: pekar.industrial@gmail.com", 70, 37);
            doc.text("Cell Phone: 0722301274/0721301274", 70, 43);

            // Header
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(18);
            doc.setTextColor(0, 0, 0);
            doc.setLineWidth(0.7);
            doc.rect(15, 50, 180, 10);
            doc.text('JOB CARD / EQUIPMENT HANDOVER', 55, 57);
            doc.setFont('helvetica', 'normal');

            // Adding form details
            doc.setFontSize(11);
            doc.setLineWidth(0.1);

            // Adding Date and Job Number
            doc.setTextColor(50, 50, 50);
            doc.text(`DATE:`, 20, 65);
            doc.setTextColor(0, 0, 0);
            doc.text(`${card.date || ''}`, 35, 65);
            doc.setTextColor(50, 50, 50);
            doc.text(`JOB NO:`, 140, 65);
            doc.setTextColor(0, 0, 0);
            doc.text(`${card.jobNo || ''}`, 160, 65);

            // Customer and Technician Info
            doc.rect(20, 69, 180, 7);
            doc.setTextColor(50, 50, 50);
            doc.text(`CUSTOMER:`, 22, 73.5);
            doc.setTextColor(0, 0, 0);
            doc.text(`${card.customer_name || ''}`, 50, 73.5);

            doc.rect(20, 76, 90, 8);
            doc.rect(20, 76, 40, 8);
            doc.setTextColor(50, 50, 50);
            doc.text("TECHNICIAN :", 22, 80.5, { maxWidth: 40 });
            doc.setTextColor(0, 0, 0);
            doc.text(`${card.technician_name || ''}`, 62, 80.5);
            
            doc.rect(110, 76, 90, 8);
            doc.rect(110, 76, 40, 8);
            doc.setTextColor(50, 50, 50);
            doc.text("LPO/REF:", 112, 80.5, { maxWidth: 50 });
            doc.setTextColor(0, 0, 0);
            doc.text(`${card.lpo_no || ''}`, 152, 80.5);
            
            doc.rect(20, 84, 90, 8);
            doc.rect(20, 84, 40, 8);
            doc.setTextColor(50, 50, 50);
            doc.text("JOB STARTED:", 22, 88.5, { maxWidth: 40 });
            doc.setTextColor(0, 0, 0);
            doc.text(`${card.date_started || ''}`, 62, 88.5);
            
            doc.rect(110, 84, 90, 8);
            doc.rect(110, 84, 40, 8);
            doc.setTextColor(50, 50, 50);
            doc.text("JOB FINISHED:", 112, 88.5, { maxWidth: 40 });
            doc.setTextColor(0, 0, 0);
            doc.text(`${card.date_finished || ''}`, 152, 88.5);

            // Item Descriptions
            doc.setFont('helvetica', 'bold');
            doc.setTextColor(128, 128, 128);

            const itemHeader = (doc,yPos) =>{
            doc.text("ITEM/MACHINE SERIAL NO", 22, yPos, { maxWidth: 70 });
            doc.text("JOB DESCRIPTION/INSTRUCTION", 85, yPos);
            };

            let yPos = 100;
            itemHeader(doc, yPos);
            yPos += 5;

            doc.setFont('helvetica', 'normal');
            doc.setTextColor(0, 0, 0);

            items.forEach(item => {
                if (yPos > 270) {
                    doc.addPage();
                    yPos = 20;
                    itemHeader(doc, yPos);
                    yPos += 5;
                }

                const jobDescription = item.job_description || '';
                const jobDescriptionLines = doc.splitTextToSize(jobDescription, 110);
                const lineHeight = 4.5;
                const itemHeight = jobDescriptionLines.length * lineHeight;
                doc.text(item.machine_serial_number || '', 22, yPos+ lineHeight);
                doc.text(`${jobDescription}`, 85, yPos+ lineHeight, { maxWidth: 110 });
                yPos += itemHeight + 1;
            });

        
            // Spare Parts
            const sparesHeader = (doc, yPos) =>{
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

            spares.forEach(spare => {
                if (yPos > 270) {
                    doc.addPage();
                    yPos = 20;
                    sparesHeader(doc, yPos);
                    yPos += 5;
                }
                doc.text(spare.spare_part || '', 20, yPos+7);
                doc.text(String(spare.quantity || ''), 105, yPos+7);
                doc.text(String(spare.unit_cost || ''), 130, yPos+7);
                doc.text(String(spare.total || ''), 165, yPos+7);
                yPos += 6.5;
            });

            if (yPos > 270) {
                doc.addPage();
                yPos = 20;
            }

            doc.setFont('helvetica', 'bold');
            doc.text("Installation, testing, and commissioning done and system left in good working condition.", 20, yPos+10, { maxWidth: 180 });
            if (yPos > 270) {
                doc.addPage();
                yPos = 20;
            }
            doc.setFont('helvetica', 'normal');
            doc.text("Technician's Name and Signature: ..........................................................", 20, yPos+17.5);
            if (yPos > 270) {
                doc.addPage();
                yPos = 20;
            }
            doc.setFont('helvetica', 'bold');
            doc.text("Confirmed by client representative.", 20, yPos+25, { maxWidth: 180 });
            if (yPos > 270) {
                doc.addPage();
                yPos = 20;
            }
            doc.setFont('helvetica', 'normal');
            doc.text("Client's Name and Signature: ...............................................................", 20, yPos+32.5);

            doc.save(`JobCard_${card.jobNo || 'Unknown'}.pdf`);
        }
    </script>

    <footer>
        <div class="footer-container">
            <div class="footer-section">
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
            </div>
            <div class="footer-bottom">
                © 2025 Pekar Industrial & Construction LTD | All rights reserved.
            </div>
        </div>
    </footer>
</body>
</html>