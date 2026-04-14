<?php
session_start();
include('config/constants.php');

class CartOperations {
    public function removeItem($itemId) {
        if (isset($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $key => $item) {
                if ($item['id'] == $itemId) {
                    unset($_SESSION['cart'][$key]);
                    return true;
                }
            }
        }
        return false;
    }
}

// Check if item_id is set
if (isset($_GET['item_id'])) {
    $itemId = $_GET['item_id'];
    $cartOps = new CartOperations();
    
    if ($cartOps->removeItem($itemId)) {
        $_SESSION['order'] = "Item removed from cart successfully.";
    }
    
    // Redirect to cart page
    header('Location: cart.php');
    exit();
} else {
    // Redirect to cart page if item_id is not set
    header('Location: cart.php');
    exit();
}
?>
