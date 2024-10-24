<?php
// Start the session
session_start();

// Include database connection file
include('db_connection.php');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_name = $_POST['customer_name'];
    $product_name = $_POST['product_name'];
    $size = $_POST['size'];
    $quantity = $_POST['quantity'];
    $order_date = $_POST['order_date'];
    $status = $_POST['status'];

    // Insert order into the database
    $sql = "INSERT INTO tb_orders (customer_name, product_name, size, quantity, order_date, status) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssiss', $customer_name, $product_name, $size, $quantity, $order_date, $status);

    if ($stmt->execute()) {
        // Redirect to orders.php after successful insertion
        header('Location: orders.php');
        exit;
    } else {
        echo "<script>alert('Error adding order.'); window.location.href = 'add-order.php';</script>";
    }
}

// Fetch products for the dropdown
$product_sql = "SELECT DISTINCT name FROM tb_inventory";
$product_result = $conn->query($product_sql);

// Fetch sizes for products
$size_sql = "SELECT name, size FROM tb_inventory";
$size_result = $conn->query($size_sql);

// Prepare sizes data for JavaScript
$product_sizes = [];
while ($row = $size_result->fetch_assoc()) {
    $product_name = $row['name'];
    $size = $row['size'];
    if (!isset($product_sizes[$product_name])) {
        $product_sizes[$product_name] = [];
    }
    $product_sizes[$product_name][] = $size;
}
$product_sizes_json = json_encode($product_sizes);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Order - GSL25 Inventory Management System</title>
    <link rel="icon" href="img/GSL25_transparent 2.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
    <style>
body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(to right, #000000, gray, #ffffff);
    margin: 0;
    padding: 0;
    color: #333;
    transition: background-color 0.3s, color 0.3s;
}

body.dark-mode {
    background: linear-gradient(to right, #2c3e50, #34495e, #2c3e50);
    color: #ecf0f1;
}

.container {
    max-width: 800px;
    margin: 20px auto;
    padding: 20px;
    background: #ffffff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    transition: background 0.3s;
}

.container.dark-mode {
    background: #34495e;
}

h1 {
    font-size: 28px;
    margin-bottom: 20px;
    color: #007bff;
}

form label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

form input[type="text"],
form input[type="number"],
form input[type="date"],
form select {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ced4da;
    border-radius: 4px;
    color: #000;
    background-color: #ffffff;
}

body.dark-mode form input[type="text"],
body.dark-mode form input[type="number"],
body.dark-mode form input[type="date"],
body.dark-mode form select {
    color: #ffffff;
    background-color: #34495e;
}

button {
    background-color: #007bff;
    color: #ffffff;
    border: none;
    padding: 10px 15px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    display: inline-block;
    margin-right: 10px;
}

button:hover {
    background-color: #0056b3;
}

.button-container {
    display: flex;
    justify-content: space-between;
    margin-top: 20px;
}

.button-container .backButton {
    background-color: #007bff;
    color: #ffffff;
    border: none;
    padding: 10px 15px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    text-align: center;
    display: inline-block;
    margin-right: 10px;
    text-decoration: none;
}

.button-container .backButton:hover {
    background-color: #0056b3;
}

.dark-mode-toggle {
    position: fixed;
    top: 20px;
    right: 20px;
    background-color: #3498db;
    color: #ffffff;
    border: none;
    padding: 10px 15px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    font-weight: bold;
    z-index: 1000;
}

.dark-mode-toggle:hover {
    background-color: #2980b9;
}

    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-2xl font-bold mb-4">Add New Order</h1>

        <form action="add-order.php" method="POST">
            <label for="customer_name">Customer Name:</label>
            <input type="text" id="customer_name" name="customer_name" required>
            
            <label for="product_name">Product Name:</label>
            <select id="product_name" name="product_name" required>
                <?php while ($product_row = $product_result->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($product_row['name']); ?>">
                        <?php echo htmlspecialchars($product_row['name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            
            <label for="size">Size:</label>
            <select id="size" name="size" required>
                <!-- Size options will be populated dynamically -->
            </select>
            
            <label for="quantity">Quantity:</label>
            <input type="number" id="quantity" name="quantity" required>
            
            <label for="order_date">Order Date:</label>
            <input type="date" id="order_date" name="order_date" required>
            
            <div class="button-container">
                <button type="submit"><i class="fa fa-save"></i> Save Order</button>
                <a href="orders.php" class="backButton"><i class="fa fa-arrow-left"></i> Back to Orders</a>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const productSelect = document.getElementById('product_name');
            const sizeSelect = document.getElementById('size');
            
            const productSizes = <?php echo $product_sizes_json; ?>;

            productSelect.addEventListener('change', function() {
                const selectedProduct = productSelect.value;
                const sizes = productSizes[selectedProduct] || [];

                sizeSelect.innerHTML = '';

                sizes.forEach(size => {
                    const option = document.createElement('option');
                    option.value = size;
                    option.textContent = size;
                    sizeSelect.appendChild(option);
                });
            });

            productSelect.dispatchEvent(new Event('change'));

            if (localStorage.getItem('darkMode') === 'enabled') {
                document.body.classList.add('dark-mode');
                document.querySelector('.container').classList.add('dark-mode');
            }
        });

        function toggleDarkMode() {
            const body = document.body;
            const container = document.querySelector('.container');
            body.classList.toggle('dark-mode');
            container.classList.toggle('dark-mode');

            if (body.classList.contains('dark-mode')) {
                localStorage.setItem('darkMode', 'enabled');
            } else {
                localStorage.setItem('darkMode', 'disabled');
            }
        }
    </script>
</body>
</html>
