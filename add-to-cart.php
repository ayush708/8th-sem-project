<?php
// Start the session
session_start();

// Include database connection
include('config/constants.php');

// Cart Management Class
class CartManager extends BaseManager {
    public function __construct($db = null) {
        parent::__construct($db);
    }
    
    public function getItemById($itemId) {
        $sql = "SELECT * FROM tbl_items WHERE id=?";
        $stmt = $this->db->prepare($sql);
        $this->db->bind($stmt, "i", $itemId);
        $this->db->execute($stmt);
        $res = $this->db->getResult($stmt);
        
        if ($res && $this->db->numRows($res) > 0) {
            return $this->db->fetchAssoc($res);
        }
        return null;
    }
    
    public function addToCart($itemId, $title, $price, $imageName) {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = array();
        }
        
        $itemArrayId = array_column($_SESSION['cart'], 'id');
        
        if (!in_array($itemId, $itemArrayId)) {
            $itemArray = array(
                'id' => $itemId,
                'title' => $title,
                'price' => $price,
                'image_name' => $imageName,
                'quantity' => 1
            );
            $_SESSION['cart'][] = $itemArray;
            return "added";
        } else {
            foreach ($_SESSION['cart'] as &$item) {
                if ($item['id'] == $itemId) {
                    $item['quantity'] += 1;
                    return "updated";
                }
            }
        }
    }
}

// Check if item_id is set in the URL
if (isset($_GET['item_id'])) {
    $itemId = $_GET['item_id'];
    $cartManager = new CartManager();
    
    // Fetch item details from the database
    $item = $cartManager->getItemById($itemId);
    
    // If item found
    if ($item) {
        $result = $cartManager->addToCart($item['id'], $item['title'], $item['price'], $item['image_name']);
        
        if ($result === "added") {
            $_SESSION['order'] = "Item added to cart successfully.";
        } elseif ($result === "updated") {
            $_SESSION['order'] = "Item quantity updated in cart.";
        }
    }
    
    // Redirect to the previous page or cart page
    header('Location: index.php');
    exit();
} else {
    header('Location: index.php');
    exit();
}
?>
