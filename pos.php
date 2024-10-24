<?php
// Start session and include database connection
session_start();
include('db_connection.php');

// Check if the user is logged in and is an admin
$isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';

// Initialize search query
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Prepare the SQL query to fetch products, applying search filter if provided
$query = "SELECT * FROM tb_inventory WHERE quantity > 0";
if ($search) {
    $search = $conn->real_escape_string($search); // Prevent SQL injection
    $query .= " AND name LIKE '%$search%'";
}

// Get all products for the POS system
$products = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS - GSL25 Inventory Management System</title>
    <link rel="icon" href="img/GSL25_transparent 2.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
    <style>
        body {
            background-color: #1a202c; /* Dark background for consistency */
            color: white;
            font-family: 'Arial', sans-serif;
            padding: 2rem 0;
        }

        h1, h2 {
            color: #fff;
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .product-list-container {
            background-color: #2d3748;
            padding: 20px;
            border-radius: 8px;
        }

        .product-list li {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #4a5568;
        }

        .product-list li:last-child {
            border-bottom: none;
        }

        .searchInput {
            width: 100%;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 10px;
            background-color: #fff;
            color: #000;
        }

        .searchButton {
            display: inline-block;
            width: 100%;
            background-color: #3182ce;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 6px;
            margin-bottom: 20px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .searchButton:hover {
            background-color: #2b6cb0;
        }

        .cart-summary {
            background-color: #2d3748;
            padding: 20px;
            border-radius: 8px;
            margin-top: 1.5rem;
            width: 100%;
        }

        .cart-summary table {
            width: 100%;
            border-collapse: collapse;
        }

        .cart-summary th, .cart-summary td {
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid #4a5568;
        }

        .cart-summary th {
            background-color: #4a5568;
        }

        button {
            background-color: #3182ce;
            color: white;
            padding: 8px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #2b6cb0;
        }

        .grid {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 2rem;
        }

        .cart-summary-container {
            flex: 1;
            position: sticky;
            top: 0;
            right: 0;
        }

        .cart-summary input {
            width: 60px;
            padding: 5px;
            border-radius: 4px;
            text-align: right;
        }

        .cart-summary button {
            float: right;
        }

        .product-image {
            width: 50px; /* Set a uniform size for product images */
            height: 50px;
            object-fit: cover; /* Maintain aspect ratio */
            margin-right: 10px; /* Space between image and text */
        }

        /* Modal styles */
        .modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgb(0,0,0); /* Fallback color */
            background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
            padding-top: 60px; /* Location of the box */
        }

        .modal-content {
            background-color: #2d3748;
            margin: 5% auto; /* 15% from the top and centered */
            padding: 20px;
            border: 1px solid #888;
            width: 80%; /* Could be more or less, depending on screen size */
            border-radius: 8px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: white;
            text-decoration: none;
            cursor: pointer;
        }

    </style>
</head>
<body>
    <div class="container mx-auto py-12 px-6">
        <div class="mb-4">
            <button onclick="window.location.href='inventory.php'" class="bg-gray-700 hover:bg-gray-600 text-white py-2 px-4 rounded">
                <i class="fas fa-arrow-left"></i> Back
            </button>
        </div>

        <h1 class="text-4xl font-bold text-center">Point of Sale (POS)</h1>
        <div class="grid">
            <div>
                <h2 class="text-2xl">Product List</h2>
                <form method="GET" class="mb-4">
                    <input type="text" name="search" class="w-full px-4 py-2 text-black rounded-lg" placeholder="Search product..." value="<?php echo htmlspecialchars($search); ?>" />
                    <button type="submit" class="searchButton mt-2 w-full">Search</button>
                </form>
                <ul class="product-list">
                    <?php while ($row = $products->fetch_assoc()): ?>
                        <li class="mb-4 flex items-center">
                            <img src="<?php echo htmlspecialchars($row['image_url']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" class="product-image rounded" /> <!-- Product Image -->
                            <div class="flex-grow">
                                <span><?php echo htmlspecialchars($row['name']); ?> (Size: <?php echo htmlspecialchars($row['size']); ?>)</span>
                                <span class="float-right">₱<?php echo htmlspecialchars($row['price']); ?></span>
                            </div>
                            <button class="mt-2" onclick="openModal(<?php echo $row['product_id']; ?>, '<?php echo htmlspecialchars($row['name']); ?>', <?php echo $row['price']; ?>, '<?php echo htmlspecialchars($row['size']); ?>', '<?php echo htmlspecialchars($row['image_url']); ?>')">
                                Add
                            </button>
                            <?php if ($isAdmin): ?>
                                <span class="ml-4 text-sm text-gray-400">Stock: <?php echo htmlspecialchars($row['quantity']); ?></span>
                            <?php endif; ?>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>

            <div class="cart-summary-container">
                <h2 class="text-2xl">Cart Summary</h2>
                <div class="cart-summary">
                    <table>
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Size</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Total</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="cartItems"></tbody>
                    </table>
                    <div class="text-right">
                        <strong>Subtotal:</strong> ₱<span id="subtotal">0.00</span>
                    </div>
                    <div class="text-right">
                        <strong>Discount:</strong> <input type="number" id="discount" class="px-2 py-1 w-20 text-black" value="0" />%
                    </div>
                    <div class="text-right">
                        <strong>Total:</strong> ₱<span id="total">0.00</span>
                    </div>
                    <button class="mt-4" onclick="processOrder()">Checkout</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="productModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 class="text-2xl font-bold mb-4" id="modalProductName"></h2>
            <img id="modalProductImage" class="product-image" alt="Product Image" />
            <p id="modalProductSize"></p>
            <p class="text-xl">Price: ₱<span id="modalProductPrice"></span></p>
            <label for="modalProductQuantity" class="mt-2">Quantity:</label>
            <input type="number" id="modalProductQuantity" value="1" class="w-full px-2 py-1 text-black rounded" min="1" />
            <button class="mt-4" id="addToCartButton">Add to Cart</button>
        </div>
    </div>

    <script>
        const cart = [];
        let subtotal = 0;

        function openModal(id, name, price, size, image) {
            document.getElementById('modalProductName').innerText = name;
            document.getElementById('modalProductPrice').innerText = price.toFixed(2);
            document.getElementById('modalProductSize').innerText = `Size: ${size}`;
            document.getElementById('modalProductImage').src = image;
            document.getElementById('productModal').style.display = 'block';

            // Add event listener for the add to cart button
            const addToCartButton = document.getElementById('addToCartButton');
            addToCartButton.onclick = () => {
                const quantity = parseInt(document.getElementById('modalProductQuantity').value);
                if (quantity > 0) {
                    addToCart(id, name, price, size, quantity);
                    closeModal(); // Close the modal after adding
                }
            };
        }

        function closeModal() {
            document.getElementById('productModal').style.display = 'none';
        }

        function addToCart(id, name, price, size, quantity) {
            const cartItem = { id, name, price, size, quantity };
            cart.push(cartItem);
            updateCartSummary();
        }

        function updateCartSummary() {
            const cartItemsContainer = document.getElementById('cartItems');
            cartItemsContainer.innerHTML = ''; // Clear previous items

            subtotal = 0;
            cart.forEach(item => {
                const totalPrice = item.price * item.quantity;
                subtotal += totalPrice;

                cartItemsContainer.innerHTML += `
                    <tr>
                        <td>${item.name}</td>
                        <td>${item.size}</td>
                        <td>${item.quantity}</td>
                        <td>₱${item.price.toFixed(2)}</td>
                        <td>₱${totalPrice.toFixed(2)}</td>
                        <td><button onclick="removeFromCart(${item.id})">Remove</button></td>
                    </tr>
                `;
            });

            // Update subtotal and total with discount
            document.getElementById('subtotal').innerText = subtotal.toFixed(2);
            const discount = parseFloat(document.getElementById('discount').value) || 0;
            const total = subtotal - (subtotal * (discount / 100));
            document.getElementById('total').innerText = total.toFixed(2);
        }

        function removeFromCart(id) {
            const itemIndex = cart.findIndex(item => item.id === id);
            if (itemIndex > -1) {
                cart.splice(itemIndex, 1);
                updateCartSummary();
            }
        }

        function processOrder() {
            if (cart.length === 0) {
                alert('Your cart is empty!');
                return;
            }

            // You can implement the order processing logic here
            alert('Order processed successfully!');
        }
    </script>
</body>
</html>
