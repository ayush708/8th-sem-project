<?php
include('config/constants.php');

interface CancellableOrderInterface {
    public function checkOrderExists($orderId);
    public function cancelOrder($orderId);
}

class OrderCanceller extends BaseManager implements CancellableOrderInterface {
    private $orderId;

    public function __construct($db = null) {
        parent::__construct($db);
    }

    public function setOrderId($orderId) {
        $this->orderId = (int)$orderId;
        return $this;
    }

    public function getOrderId() {
        return $this->orderId;
    }
    
    public function checkOrderExists($orderId) {
        $sql = "SELECT * FROM tbl_order WHERE id = ? AND status NOT IN ('Delivered', 'On Delivery')";
        $stmt = $this->db->prepare($sql);
        $this->db->bind($stmt, "i", $orderId);
        $this->db->execute($stmt);
        $res = $this->db->getResult($stmt);
        
        return $this->db->numRows($res) > 0;
    }
    
    public function cancelOrder($orderId) {
        $sql = "DELETE FROM tbl_order WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $this->db->bind($stmt, "i", $orderId);
        return $this->db->execute($stmt);
    }
}

// Get the order ID from the POST request
$orderId = isset($_POST['order_id']) ? $_POST['order_id'] : null;

if ($orderId) {
    $canceller = (new OrderCanceller())->setOrderId($orderId);
    $orderId = $canceller->getOrderId();
    
    if ($canceller->checkOrderExists($orderId)) {
        if ($canceller->cancelOrder($orderId)) {
            echo "Order cancelled successfully.";
        } else {
            echo "Failed to cancel order.";
        }
    } else {
        echo "Order not found or already delivered.";
    }
} else {
    echo "Order ID is missing.";
}
?>