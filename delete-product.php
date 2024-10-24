<?php
// Start the session
session_start();

// Include database connection file
include('db_connection.php');

// Fetch product ID from the query parameter
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Check if the ID is valid
if ($id > 0) {
    // Prepare and execute the delete query
    $sql = "DELETE FROM tb_inventory WHERE product_id = $id";
    
    if ($conn->query($sql) === TRUE) {
        // Redirect to inventory page with success message
        $_SESSION['message'] = "Product deleted successfully.";
    } else {
        // Redirect to inventory page with error message
        $_SESSION['message'] = "Error: " . $conn->error;
    }
} else {
    // Redirect to inventory page with invalid ID message
    $_SESSION['message'] = "Invalid product ID.";
}

// Close the database connection
$conn->close();

// Redirect to the inventory page
header("Location: inventory.php");
exit();
?>
