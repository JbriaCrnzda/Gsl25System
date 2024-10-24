<?php
// Start the session
session_start();

// Include database connection file
include('db_connection.php');

// Check if the user is logged in; if not, redirect to the login page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

// Initialize variables
$selected_date = '';

// Check if a date filter has been applied
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['selected_date'])) {
    $selected_date = $_POST['selected_date'];
}

// Fetch orders from the database based on the selected date   
if ($selected_date) {
    $sql = "SELECT o.*, i.size FROM tb_orders o JOIN tb_inventory i ON o.product_name = i.name WHERE o.order_date = ? ORDER BY o.order_id ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $selected_date);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = "SELECT o.*, i.size FROM tb_orders o JOIN tb_inventory i ON o.product_name = i.name ORDER BY o.order_id ASC";
    $result = $conn->query($sql);
}

// Calculate the total number of orders
$total_orders = 0;
if ($result) {
    $total_orders = $result->num_rows;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - GSL25 Inventory Management System</title>
    <link rel="icon" href="img/GSL25_transparent 2.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
    <style>
        /* Body and general styling */
        body {
            font-family: 'Poppins', sans-serif;
            display: flex;
            margin: 0;
            color: #2c3e50;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .dark-mode {
            background-color: #2c3e50;
            color: #ecf0f1;
        }

        .sidebar {
            width: 260px;
            background: linear-gradient(145deg, #34495e, #2c3e50);
            color: #ecf0f1;
            padding: 30px 20px;
            height: 100vh;
            position: fixed;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            transition: background 0.3s ease;
        }

        .sidebarHeader h2 {
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .sidebarNav ul {
            list-style: none;
            padding: 0;
        }

        .sidebarNav ul li {
            margin: 1.2rem 0;
        }

        .sidebarNav ul li a {
            text-decoration: none;
            color: #ecf0f1;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            padding: 0.8rem 1rem;
            border-radius: 8px;
            transition: background 0.3s ease;
        }

        .sidebarNav ul li a:hover {
            background-color: #2980b9;
        }

        .sidebarNav ul li a i {
            margin-right: 15px;
        }

        .mainContent {
            margin-left: 280px;
            padding: 30px;
            width: calc(100% - 280px);
            min-height: 100vh;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .mainHeader {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .mainHeader h1 {
            font-size: 2.5rem;
            margin-bottom: 2rem;
            text-align: center;
        }

        .headerActions .button i {
            margin-right: 8px;
        }

        .ordersTable {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .ordersTable th, .ordersTable td {
            padding: 15px;
            border: 1px solid #ddd;
            text-align: center;
        }

        .ordersTable th {
            background-color: #3498db;
            color: #ffffff;
        }

        .ordersTable tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .dark-mode .ordersTable th {
            background-color: #2980b9;
        }

        .dark-mode .ordersTable tr:nth-child(even) {
            background-color: #34495e;
        }

        .button {
            background-color: #ffffff; 
            color: #c0392b; 
            padding: 5px 10px; 
            border-radius: 8px;
            font-size: 1rem;
            text-align: center;
            text-decoration: none;
            border: 1px solid #3498db; 
            transition: background-color 0.3s ease, color 0.3s ease;
            display: inline-block; 
            cursor: pointer; 
        }

        .button:hover {
            background-color: #3498db;
            color: #ffffff; 
        }

        .totalOrders {
            margin-top: 30px;
            font-size: 1.2rem;
            text-align: center;
        }

        @media (max-width: 768px) {
            .mainContent {
                margin-left: 0;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebarHeader">
            <h2>GSL25 Dashboard</h2>
        </div>
        <nav class="sidebarNav">
            <ul>
                <li><a href="dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                <li><a href="inventory.php"><i class="fa fa-box"></i> Inventory</a></li>
                <li><a href="orders.php"><i class="fa fa-receipt"></i> Orders</a></li>
                <li><a href="reports.php"><i class="fa fa-chart-line"></i> Reports</a></li>
                <li><a href="settings.php"><i class="fa fa-cog"></i> Settings</a></li>
            </ul>
        </nav>
    </aside>

    <!-- Main Content -->
    <div class="mainContent">
        <header class="mainHeader">
            <h1>Order Management</h1>
            <div class="headerActions">
                <a href="add-order.php" class="button"><i class="fa fa-plus"></i> Add New Order</a>
                <a href="recently-deleted.php" class="button"><i class="fa fa-undo"></i> Recently Deleted</a>
            </div>
        </header>

        <!-- Orders Table -->
        <section class="ordersSection">
            <table class="ordersTable">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer Name</th>
                        <th>Product Name</th>
                        <th>Size</th>
                        <th>Quantity</th>
                        <th>Order Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Display the fetched orders
                    if (isset($result) && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) { 
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['order_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['size']); ?></td>
                            <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                            <td><?php echo htmlspecialchars($row['order_date']); ?></td>
                            <td>
                                <a href="edit-order.php?id=<?php echo htmlspecialchars($row['order_id']); ?>" class="button"><i class="fa fa-edit"></i></a>
                                <a href="delete-order.php?id=<?php echo htmlspecialchars($row['order_id']); ?>" class="button" onclick="return confirm('Are you sure you want to delete this order?');"><i class="fa fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php
                        }
                    } else {
                        echo '<tr><td colspan="7">No orders found.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
            <p>Total Orders: <?php echo $total_orders; ?></p>
        </section>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (localStorage.getItem('darkMode') === 'enabled') {
                document.body.classList.add('dark-mode');
            }
        });

        function toggleDarkMode() {
            document.body.classList.toggle('dark-mode');
            if (document.body.classList.contains('dark-mode')) {
                localStorage.setItem('darkMode', 'enabled');
            } else {
                localStorage.setItem('darkMode', 'disabled');
            }
        }
    </script>
</body>
</html>
