<?php
// Include your database connection file here
include('db_connection.php');

$error_message = '';
$success_message = '';
$product = null;

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Fetch the product data from the database
    $stmt = $conn->prepare('SELECT * FROM tb_inventory WHERE product_id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        $error_message = 'Product not found.';
    }

    // If form is submitted, update the product in the database
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['name'];
        $category = $_POST['category'];
        $quantity = $_POST['quantity'];

        // Validation
        if (empty($name) || empty($category) || !is_numeric($quantity) || $quantity < 0) {
            $error_message = 'Please provide valid input.';
        } else {
            $update_stmt = $conn->prepare('UPDATE tb_inventory SET name = ?, category = ?, quantity = ? WHERE product_id = ?');
            $update_stmt->bind_param('ssii', $name, $category, $quantity, $id);

            if ($update_stmt->execute()) {
                $success_message = 'Product updated successfully.';
                // Refresh the product data
                $product['name'] = $name;
                $product['category'] = $category;
                $product['quantity'] = $quantity;
            } else {
                $error_message = 'Failed to update product. Please try again.';
            }
        }
    }
} else {
    $error_message = 'No product selected.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - GSL25 Inventory Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #000000, gray, #ffffff); /* Horizontal black and white gradient */
            margin: 0;
            padding: 0;
            color: #333; /* General text color */
        }
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: #ffffff; /* Container background */
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .message {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
        }
        form label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        form input[type="text"],
        form input[type="number"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ced4da;
            border-radius: 4px;
        }
        form input[type="submit"] {
            background-color: #007bff;
            color: #ffffff;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        form input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .back-button {
            display: inline-block;
            margin-top: 20px;
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }
        .back-button:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-2xl font-bold mb-4">Edit Product</h1>

        <!-- Display error or success message if available -->
        <?php if ($error_message): ?>
            <p class="message error"><?php echo htmlspecialchars($error_message); ?></p>
        <?php elseif ($success_message): ?>
            <p class="message success"><?php echo htmlspecialchars($success_message); ?></p>
        <?php endif; ?>

        <!-- Show form only if product data is available -->
        <?php if ($product): ?>
            <form action="edit-product.php?id=<?php echo urlencode($id); ?>" method="post">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                
                <label for="category">Category:</label>
                <input type="text" id="category" name="category" value="<?php echo htmlspecialchars($product['category']); ?>" required>
                
                <label for="quantity">Quantity:</label>
                <input type="number" id="quantity" name="quantity" value="<?php echo htmlspecialchars($product['quantity']); ?>" required>
                
                <input type="submit" name="submit" value="Update Product">
            </form>
        <?php endif; ?>

        <a href="inventory.php" class="back-button">Back to Inventory</a>
    </div>
    <script>
        // JavaScript for dark mode toggle
        function toggleDarkMode() {
            document.body.classList.toggle('dark-mode');
        }

        // Set dark mode based on localStorage
        if (localStorage.getItem('darkMode') === 'enabled') {
            document.body.classList.add('dark-mode');
        }

        // Save dark mode preference to localStorage
        document.querySelector('.toggleButton').addEventListener('click', function() {
            if (document.body.classList.contains('dark-mode')) {
                localStorage.setItem('darkMode', 'enabled');
            } else {
                localStorage.setItem('darkMode', 'disabled');
            }
        });
    </script>
</body>
</html>
