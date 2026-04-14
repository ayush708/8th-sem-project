<?php
session_start();
include('config/constants.php');

class CartOperations {
    public function updateQuantity($itemId, $quantity) {
        if (isset($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $key => $item) {
                if ($item['id'] == $itemId) {
                    if ($quantity > 0) {
                        $_SESSION['cart'][$key]['quantity'] = $quantity;
                        return true;
                    }
                    return false;
                }
            }
        }
        return false;
    }
}

// Check if the quantity and item_id are set
if (isset($_POST['quantity']) && isset($_POST['item_id'])) {
    $itemId = $_POST['item_id'];
    $quantity = intval($_POST['quantity']);
    
    $cartOps = new CartOperations();
    $cartOps->updateQuantity($itemId, $quantity);
    
    // Redirect back to the cart page
    header('Location: cart.php');
    exit();
}
?>
