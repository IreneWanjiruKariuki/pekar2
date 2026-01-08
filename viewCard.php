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
        async function generatePDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            // Save the PDF
            doc.save(`JobCard_${jobNo}.pdf`);
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