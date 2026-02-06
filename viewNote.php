<?php
require_once("db_connect.php");

if(isset($_GET["DelId"])){
    $DelId = $_GET["DelId"];
    // Delete related items
    $del_items = "DELETE FROM note_item WHERE delivery_no='$DelId'";
    $conn->query($del_items);
    // Delete main note
    $del_note = "DELETE FROM note WHERE delivery_no='$DelId' LIMIT 1";
    if ($conn->query($del_note) === TRUE) {
        header("Location:viewNote.php");
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
    <title>View Delivery Notes</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: #333;
            min-height: 100vh;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            font-size: 32px;
            font-weight: 600;
            margin-bottom: 32px;
            color: #000;
            text-align: center;
            margin-top: 2rem;
        }
        .table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        thead {
            background-color: #f3f4f6;
            border-bottom: 1px solid #e5e7eb;
        }
        th {
            padding: 16px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            letter-spacing: 0.5px;
            color: #374151;
            text-transform: uppercase;
        }
        tbody tr {
            border-bottom: 1px solid #e5e7eb;
            transition: background-color 0.15s ease;
        }
        tbody tr:hover {
            background-color: #f9fafb;
        }
        td {
            padding: 16px;
            color: #1f2937;
            font-size: 14px;
        }
        .actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        button {
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 500;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        button:active {
            transform: scale(0.98);
        }
        button:nth-child(1) { background-color: #a7b0c0ff; color: grey; }
        button:nth-child(1):hover { background-color: #b3c0e0ff; }
        button:nth-child(2) { background-color: #63dd8aff; color: grey; }
        button:nth-child(2):hover { background-color:#b3c0e0ff; }
        button:nth-child(3) { background-color: #e34343ff; color: white; }
        button:nth-child(3):hover { background-color: #c03e3eff; }
        .empty-state {
            text-align: center;
            color: #9ca3af;
            font-style: italic;
            padding: 32px !important;
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
    </style>
</head>
<body>
    <nav class="nav">
        <div class="nav-container">
            <div class="nav-logo">
                <span class="logo-text">PEKAR</span>
                <span class="logo-subtext">Industrial & Construction</span>
            </div>
            <ul class="nav-menu">
                <li><a href="home.html" class="nav-link">Home</a></li>
                <li><a href="viewCard.php" class="nav-link">Job cards</a></li>
                <li><a href="viewNote.php" class="nav-link active">Delivery notes</a></li>
                <li><a href="viewInvoice.php" class="nav-link">Invoices</a></li>
            </ul>
        </div>
    </nav>
    <div class="container">
        <h1 style="margin-top: 7rem;">DELIVERY NOTES</h1>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>DELIVERY NO</th>
                        <th>DELIVER TO</th>
                        <th>LPO NO</th>
                        <th>DELIVERY DATE</th>
                        <th>ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT deliver_to, delivery_no, lpo_no, delivery_date FROM note ORDER BY delivery_no DESC";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row["delivery_no"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["deliver_to"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["lpo_no"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["delivery_date"]) . "</td>";
                            echo "<td><div class='actions'>";
                            echo "<button onclick=\"window.location.href='editNote.php?delivery_no=" . urlencode($row["delivery_no"]) . "'\">Edit</button>";
                            echo "<button onclick=\"generatePDF('" . addslashes($row['delivery_no']) . "')\">Download</button>";
                            echo "<button onclick=\"if(confirm('Are you sure you want to delete this delivery note?')) window.location.href='viewNote.php?DelId=" . urlencode($row["delivery_no"]) . "'\">Delete</button>";
                            echo "</div></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7' class='empty-state'>No delivery notes found</td></tr>";
                    }
                    $conn->close();
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>
        async function generatePDF(deliveryNo) {
            const { jsPDF } = window.jspdf;
            // Fetch all note data from the server
            const response = await fetch(`getNoteData.php?delivery_no=${encodeURIComponent(deliveryNo)}`);
            const data = await response.json();
            if (!data.note) {
                alert('Could not fetch delivery note data.');
                return;
            }
            const note = data.note;
            const items = data.items;

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
            doc.text('DELIVERY NOTE', 73, 57);
            doc.setFont('helvetica', 'normal');

            // Adding form details
            doc.setFontSize(11);
            doc.setLineWidth(0.2);
            doc.rect(20, 63, 90, 7);
            doc.setTextColor(50, 50, 50);
            doc.text(`DELIVER TO:`, 22, 67.5);
            doc.setTextColor(0, 0, 0);
            doc.text(`${note.deliver_to}`, 54, 67.5);

            doc.rect(115, 63, 80, 7);
            doc.setTextColor(50, 50, 50);
            doc.text(`DELIVERY NO:`, 117, 67.5);
            doc.setTextColor(0, 0, 0);
            doc.text(`${note.delivery_no}`, 153, 67.5);

            doc.rect(20, 73, 85, 8);
            doc.rect(65, 73, 40, 8);
            doc.setTextColor(50, 50, 50);
            doc.text(`LPO/INVOICE NO:`, 22, 77.5);
            doc.setTextColor(0, 0, 0);
            doc.text(`${note.lpo_no}`, 67, 77.5);

            doc.rect(105, 73, 88, 8);
            doc.rect(146, 73, 47, 8);
            doc.setTextColor(50, 50, 50);
            doc.text(`DATED:`, 107, 77.5);
            doc.setTextColor(0, 0, 0);
            doc.text(`${note.dated}`, 148, 77.5);

            doc.rect(20, 81, 85, 10);
            doc.rect(65, 81, 40, 10);
            doc.setTextColor(50, 50, 50);
            doc.text(`DELIVERY DATE:`, 22, 85.5);
            doc.setTextColor(0, 0, 0);
            doc.text(`${note.delivery_date}`, 67, 85.5);

            doc.rect(105, 81, 88, 10);
            doc.rect(146, 81, 47, 10);
            doc.setTextColor(50, 50, 50);
            doc.text(`DELIVERED BY:`, 107, 85.5);
            doc.text(`(Name & Signature)`, 107, 89.5);
            doc.setTextColor(0, 0, 0);
            doc.text(`${note.delivered_by}`, 148, 85.5);

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
            yPos += 1;
            items.forEach((item, index) => {
                const descriptionLines = doc.splitTextToSize(item.description, 100);
                const lineHeight = 4.5;
                const itemHeight = descriptionLines.length * lineHeight;
                if (yPos + itemHeight> 270) {
                    doc.addPage();
                    yPos = 20;
                    itemsHeader(doc, yPos);
                    yPos += 5;
                }
                doc.text(`${item.item}`, 20, yPos+lineHeight);
                doc.text(` ${item.description}`, 40, yPos+lineHeight, { maxWidth: 100 });
                doc.text(`${item.unit}`, 154, yPos+lineHeight);
                doc.text(`${item.quantity}`, 177, yPos+lineHeight);
                yPos += itemHeight + 2;
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
            doc.save(`DeliveryNote_${note.delivery_no || 'Unknown'}.pdf`);
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
                © 2026 Pekar Industrial & Construction LTD | All rights reserved.
            </div>
        </div>
    </footer>
</body>
</html>