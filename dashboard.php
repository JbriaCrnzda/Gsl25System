<?php
// Start the session
session_start();

// Include database connection file
include('db_connection.php');

// Check if the user is logged in, if not then redirect to the login page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

// Query to get total number of products
$total_stock_sql = "SELECT SUM(quantity) AS total_stock FROM tb_inventory";
$total_stock_result = $conn->query($total_stock_sql);
$total_stock = $total_stock_result->fetch_assoc()['total_stock'];

// Query to get total number of orders
$total_orders_sql = "SELECT COUNT(*) AS total_orders FROM tb_orders";
$total_orders_result = $conn->query($total_orders_sql);
$total_orders = $total_orders_result->fetch_assoc()['total_orders'];

// Query to get total number of categories
$total_categories_sql = "SELECT COUNT(DISTINCT category) AS total_categories FROM tb_inventory";
$total_categories_result = $conn->query($total_categories_sql);
$total_categories = $total_categories_result->fetch_assoc()['total_categories'];

// Query to get number of low stock items (less than 30 quantity)
$low_stock_sql = "SELECT COUNT(*) AS low_stock_items FROM tb_inventory WHERE quantity < 15";
$low_stock_result = $conn->query($low_stock_sql);
$low_stock_items = $low_stock_result->fetch_assoc()['low_stock_items'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - GSL25 Inventory Management System</title>
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

.toggleButton {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    outline: none;
    transition: color 0.3s ease;
}

.toggleButton:hover {
    color: yellow;
}

/* Icon colors */
#darkModeIcon.fa-moon {
    color: #2c3e50; /* Color for the moon icon */
}

#darkModeIcon.fa-sun {
    color: #F1C40F; /* Color for the sun icon (yellow) */
}


.dashboardSections {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-top: 30px;
}

.dashboardSections {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-top: 30px;
}

.dark-mode .dashboardSections {
    background: #2c3e50; 
}

.dashboardSection {
    background: lightblue;
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    display: flex;
    justify-content: center;
    align-items: center;
    transition: background 0.3s ease, box-shadow 0.3s ease;
}

.dark-mode .dashboardSection {
    background: #34495e;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2); 
}

.dark-mode .statCard {
    background: #2980b9;
}

.dark-mode .statCard h3,
.dark-mode .statCard p {
    color: #ffffff; 
}


.statCard {
    background: #3498db;
    padding: 25px;
    border-radius: 12px;
    text-align: center;
    color: #ffffff;
    flex: 1;
    position: relative;
    min-width: 200px;
    margin: 20px;
}

.statCard p {
    font-size: 3rem;
    margin: 0;
    font-weight: bold;
    position: relative;
}

.dark-mode .statCard {
    background: #2980b9;
}

.statCard {
    background: #3498db; 
    padding: 25px;
    border-radius: 12px;
    text-align: center;
    color: #ffffff;
    flex: 1;
    position: relative;
    min-width: 200px;
    margin: 20px;
}

.statCard::before {
    content: "";
    position: absolute;
    top: -20px;
    right: -20px;
    width: 50px;
    height: 50px;
    background: lightblue; 
    border-radius: 50%;
    z-index: 1;
}

/* Dark mode styles */
.dark-mode .statCard {
    background: #2980b9;
    content: "";
}

.dark-mode .statCard::before {
    background: #2c3e50; 
}

.dark-mode .statCard h3,
.dark-mode .statCard p {
    color: #ffffff; 
}


.dark-mode .statCard h3,
.dark-mode .statCard p {
    color: #ffffff;
}

.recentActivities,
.quickActions {
    background: lightblue;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    transition: background 0.3s ease, color 0.3s ease;
}

.dark-mode .recentActivities,
.dark-mode .quickActions {
    background: #34495e;
    color: #ecf0f1;
}

.quickActions h2 {
    font-size: 1.5rem;
    margin-bottom: 2rem;
    text-align: center;
}

.quickActions .buttonGroup {
    display: flex;
    justify-content: space-around;
    margin-top: 1.5rem;
}

.quickActions .buttonGroup a {
    background: #3498db;
    padding: 16px 28px;
    border-radius: 12px;
    color: #ffffff;
    font-size: 1rem;
    text-decoration: none;
    text-align: center;
    transition: background 0.3s ease;
}

.dark-mode .quickActions .buttonGroup a {
    background: #2980b9;
}

.quickActions .buttonGroup a:hover {
    background: #2985b3;
}
.quickActions {
    position: relative; /* Make it relative to position the circles inside it */
    background: lightblue;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    transition: background 0.3s ease, color 0.3s ease;
}

.quickActions::before,
.quickActions::after {
    content: "";
    position: absolute;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: #ffff; /* Light blue color for the circles */
    z-index: 1;
}

.quickActions::before {
    top: -25px; /* Position above the quickActions section */
    left: 10px; /* Adjust to place the circle on the left side */
}

.quickActions::after {
    top: -25px; /* Position above the quickActions section */
    right: 10px; /* Adjust to place the circle on the right side */
}

/* Dark mode styles */
.dark-mode .quickActions {
    background: #34495e;
}

.dark-mode .quickActions::before,
.dark-mode .quickActions::after {
    background: #2c3e50; /* Darker background color for dark mode */
}


    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebarHeader">
            <h2>GSL25 Inventory</h2>
        </div>
        <div class="sidebarNav">
            <ul>
            <li><a href="dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                <li><a href="inventory.php"><i class="fa fa-box"></i> Inventory</a></li>
                <li><a href="orders.php"><i class="fa fa-receipt"></i> Orders</a></li>
                <li><a href="reports.php"><i class="fa fa-chart-line"></i> Reports</a></li>
                <li><a href="settings.php"><i class="fa fa-cog"></i> Settings</a></li>    
            </ul>
        </div>
    </div>
    <div class="mainContent">
        <div class="mainHeader">
            <h1>Dashboard</h1>
            <button class="toggleButton" id="darkModeToggle" onclick="toggleDarkMode()">
                <i id="darkModeIcon" class="fa-solid fa-moon"></i>
            </button>
        </div>
        <div class="quickActions">
            <h2>Quick Actions</h2>
            <div class="buttonGroup">
                <a href="add-product.php">Add Product</a>
                <a href="add-order.php">Orders</a>
                <a href="reports.php">Report</a>
            </div>
        </div>
        <div class="dashboardSections">
            <div class="dashboardSection">
                <div class="statCard">
                    <h3>Total Stock</h3>
                    <p><?php echo $total_stock; ?></p>
                </div>
            </div>
            <div class="dashboardSection">
                <div class="statCard">
                    <h3>Total Orders</h3>
                    <p><?php echo $total_orders; ?></p>
                </div>
            </div>
            <div class="dashboardSection">
                <div class="statCard">
                    <h3>Category</h3>
                    <p><?php echo $total_categories; ?></p>
                </div>
            </div>
            <div class="dashboardSection">
                <div class="statCard">
                    <h3>Low Stock Items</h3>
                    <p><?php echo $low_stock_items; ?></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleDarkMode() {
    const body = document.body;
    body.classList.toggle("dark-mode");
    const icon = document.getElementById("darkModeIcon");
    if (body.classList.contains("dark-mode")) {
        icon.classList.remove("fa-moon");
        icon.classList.add("fa-sun");
        localStorage.setItem("darkMode", "enabled");
    } else {
        icon.classList.remove("fa-sun");
        icon.classList.add("fa-moon");
        localStorage.setItem("darkMode", "disabled");
    }
}

// Check the user's preference on page load
document.addEventListener("DOMContentLoaded", function () {
    if (localStorage.getItem("darkMode") === "enabled") {
        document.body.classList.add("dark-mode");
        document.getElementById("darkModeIcon").classList.remove("fa-moon");
        document.getElementById("darkModeIcon").classList.add("fa-sun");
    }
});

    </script>
</body>
</html>