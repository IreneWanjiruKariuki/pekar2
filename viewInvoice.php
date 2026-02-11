<?php
require_once("db_connect.php");

if(isset($_GET["DelId"])){
	$DelId = $_GET["DelId"];
	// Delete related items
	$del_items = "DELETE FROM invoice_item WHERE invoice_no='$DelId'";
	$conn->query($del_items);
	// Delete main invoice
	$del_invoice = "DELETE FROM invoice WHERE invoice_no='$DelId' LIMIT 1";
	if ($conn->query($del_invoice) === TRUE) {
		header("Location:viewInvoice.php");
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
	<title>View Invoices</title>
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
				<li><a href="viewNote.php" class="nav-link">Delivery notes</a></li>
				<li><a href="viewInvoice.php" class="nav-link active">Invoices</a></li>
			</ul>
		</div>
	</nav>
	<div class="container">
		<h1 style="margin-top: 7rem;">INVOICES</h1>
		<div class="table-container">
			<table>
				<thead>
					<tr>
						<th>INVOICE NO</th>
						<th>CUSTOMER</th>
						<th>LPO NO</th>
						<th>TOTAL</th>
						<th>VAT</th>
						<th>GRAND TOTAL</th>
						<th>ACTION</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$sql = "SELECT invoice_no, name, lpo_no, total, vat, grand_total FROM invoice ORDER BY invoice_no DESC";
					$result = $conn->query($sql);
					if ($result->num_rows > 0) {
						while ($row = $result->fetch_assoc()) {
							echo "<tr>";
							echo "<td>" . htmlspecialchars($row["invoice_no"]) . "</td>";
							echo "<td>" . htmlspecialchars($row["name"]) . "</td>";
							echo "<td>" . htmlspecialchars($row["lpo_no"]) . "</td>";
							echo "<td>" . htmlspecialchars($row["total"]) . "</td>";
							echo "<td>" . htmlspecialchars($row["vat"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["grand_total"]) . "</td>";
							echo "<td><div class='actions'>";
							echo "<button onclick=\"window.location.href='editInvoice.php?invoice_no=" . urlencode($row["invoice_no"]) . "'\">Edit</button>";
							echo "<button onclick=\"generatePDF('" . addslashes($row['invoice_no']) . "')\">Download</button>";
							echo "<button onclick=\"if(confirm('Are you sure you want to delete this invoice?')) window.location.href='viewInvoice.php?DelId=" . urlencode($row["invoice_no"]) . "'\">Delete</button>";
							echo "</div></td>";
							echo "</tr>";
						}
					} else {
						echo "<tr><td colspan='6' class='empty-state'>No invoices found</td></tr>";
					}
					$conn->close();
					?>
				</tbody>
			</table>
		</div>
	</div>
	<script>
		   async function generatePDF(invoiceNo) {
			   const { jsPDF } = window.jspdf;
			   // Fetch all invoice data from the server
			   const response = await fetch(`getInvoiceData.php?invoice_no=${encodeURIComponent(invoiceNo)}`);
			   const data = await response.json();
			   if (!data.invoice) {
				   alert('Could not fetch invoice data.');
				   return;
			   }
			   const invoice = data.invoice;
			   const items = data.items;

			   const doc = new jsPDF();
			   // Add Image at the top left corner
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
			   doc.text(`${invoice.name}`, 36, 66);
			   doc.text(`_____________________`, 35, 66.5);

			   doc.setTextColor(50, 50, 50);
			   doc.text(`Invoice Number:`, 103, 66);
			   doc.setTextColor(0, 0, 0);
			   doc.text(`${invoice.invoice_no}`, 137, 66);
			   doc.text(`___________________`, 136, 66.5);

			   doc.setTextColor(50, 50, 50);
			   doc.text(`Address:`, 20, 73);
			   doc.setTextColor(0, 0, 0);
			   doc.text(`${invoice.address || ''}`, 40, 73);
			   doc.text(`____________________`, 39, 73.5);

			   doc.setTextColor(50, 50, 50);
			   doc.text(`LPO Number:`, 103, 73);
			   doc.setTextColor(0, 0, 0);
			   doc.text(`${invoice.lpo_no}`, 133, 73);
			   doc.text(`_____________________`, 132, 73.5);

			   doc.setTextColor(50, 50, 50);
			   doc.text(`Contact:`, 20, 80);
			   doc.setTextColor(0, 0, 0);
			   doc.text(`${invoice.contact || ''}`, 39, 80);
			   doc.text(`____________________`, 38, 80.5);

			   doc.setTextColor(50, 50, 50);
			   doc.text(`Delivery no/Job Card no:`, 103, 80);
			   doc.setTextColor(0, 0, 0);
			   doc.text(`${invoice.delivery_no || ''}`, 150, 80);
			   doc.text(`_____________`, 149, 80.5);

			   doc.setTextColor(50, 50, 50);
			   doc.text(`Tel:`, 20, 87);
			   doc.setTextColor(0, 0, 0);
			   doc.text(`${invoice.tel || ''}`, 33, 87);
			   doc.text(`____________________`, 32, 87.5);

			   doc.setTextColor(50, 50, 50);
			   doc.text(`Date:`, 103, 87);
			   doc.setTextColor(0, 0, 0);
			   doc.text(`${invoice.date || ''}`, 118, 87);
			   doc.text(`____________________`, 117, 87.5);

			   // Table header
			   const addTableHeader = (doc, yPos) => {
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
			   items.forEach((item, index) => {
				   if (yPos > 270) {
					   doc.addPage();
					   yPos = 20;
					   addTableHeader(doc, yPos);
					   yPos += 5;
				   }
				   const itemCode = item.item_code || '';
				   const description = item.description || '';
				   const quantity = item.quantity || '';
				   const unitPrice = parseFloat(item.unit_price).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
				   const totalCost = parseFloat(item.unit_price) * parseFloat(quantity);
				   const isVatable = item.vatable == 1;
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
			   let total = parseFloat(invoice.total) || 0;
			   let totalVAT = parseFloat(invoice.vat) || 0;
			   let grandTotal = parseFloat(invoice.grand_total) || 0;

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
			   doc.save(`Invoice_${invoice.invoice_no || 'Unknown'}.pdf`);
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
