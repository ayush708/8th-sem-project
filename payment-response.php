<?php
session_start();
include('config/constants.php');

interface PaymentGatewayHandlerInterface {
    public function verifyPayment($orderId, $amount);
    public function updatePaymentStatus($orderId, $refId);
}

class PaymentHandler extends BaseManager implements PaymentGatewayHandlerInterface {
    private $successRedirectUrl = 'https://test-pay.khalti.com/wallet?pidx=hmFHtX2tuW9K3KUy46VPdc';

    public function __construct($db = null) {
        parent::__construct($db);
    }

    public function setSuccessRedirectUrl($url) {
        $this->successRedirectUrl = $url;
        return $this;
    }

    public function sanitize($value) {
        return parent::sanitize($value);
    }

    public function getSuccessRedirectUrl() {
        return $this->successRedirectUrl;
    }
    
    public function verifyPayment($orderId, $amount) {
        $sql = "SELECT * FROM tbl_order WHERE id=? AND total=?";
        $stmt = $this->db->prepare($sql);
        $this->db->bind($stmt, "id", $orderId, $amount);
        $this->db->execute($stmt);
        $res = $this->db->getResult($stmt);
        
        return $res && $this->db->numRows($res) == 1 ? $this->db->fetchAssoc($res) : null;
    }
    
    public function updatePaymentStatus($orderId, $refId) {
        $sql = "UPDATE tbl_order SET status='Paid', payment_ref=? WHERE id=?";
        $stmt = $this->db->prepare($sql);
        $this->db->bind($stmt, 'si', $refId, $orderId);
        return $this->db->execute($stmt);
    }
}

// Payment response handler
if (isset($_GET['amt']) && isset($_GET['refId']) && isset($_GET['oid'])) {
    $paymentHandler = (new PaymentHandler())
        ->setSuccessRedirectUrl('https://test-pay.khalti.com/wallet?pidx=hmFHtX2tuW9K3KUy46VPdc');

    $amount = $paymentHandler->sanitize($_GET['amt']);
    $refId = $paymentHandler->sanitize($_GET['refId']);
    $order_id = (int)$paymentHandler->sanitize($_GET['oid']);

    $order = $paymentHandler->verifyPayment($order_id, $amount);

    if ($order) {
        $status = $order['status'];

        if ($status == 'Ordered') {
            // Update order status to "Paid"
            if ($paymentHandler->updatePaymentStatus($order_id, $refId)) {
                $_SESSION['order'] = "<div class='success text-center'>Payment Successful. Order ID: $order_id</div>";
                $khalti_url = $paymentHandler->getSuccessRedirectUrl();
                header("Location: $khalti_url");
                exit();
            } else {
                $_SESSION['order'] = "<div class='error text-center'>Failed to update payment status.</div>";
                header('location:' . SITEURL . 'index.php');
                exit();
            }
        } else {
            $_SESSION['order'] = "<div class='error text-center'>Order is already processed or canceled.</div>";
            header('location:' . SITEURL . 'index.php');
            exit();
        }
    } else {
        $_SESSION['order'] = "<div class='error text-center'>Invalid payment or order not found.</div>";
        header('location:' . SITEURL . 'index.php');
        exit();
    }
} else {
    $_SESSION['order'] = "<div class='error text-center'>Invalid payment request.</div>";
    header('location:' . SITEURL . 'index.php');
    exit();
}
?>
