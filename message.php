<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class PaymentMessagePageRenderer {
    public function getTransactionMessage() {
        if (isset($_SESSION['transaction_msg'])) {
            $message = $_SESSION['transaction_msg'];
            unset($_SESSION['transaction_msg']);
            return $message;
        }
        return '';
    }

    public function render() {
        $transactionMessage = $this->getTransactionMessage();
        ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment successful</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<body>
    <?php if (!empty($transactionMessage)) {
        echo $transactionMessage;
    } ?>

    <div class="mt-5 d-flex justify-content-center">
        <div class="mb-3">
            <img src="payment-success.jpg" class="img-flud" alt="">
            <div class="card">
                <div class="card-body text-white bg-success">
                    <h5 class="card-title">Dear Customer,</h5>
                    <p class="card-text">
                        Your payment has been successfully processed. Thank you for shopping with us.
                    </p>
                </div>
                <div class="card-footer">
                    <a href="checkout.php" class="btn btn-primary">Back to Checkout</a>
                </div>
            </div>

        </div>
    </div>

</body>

</html>
<?php
    }
}

$paymentMessagePageRenderer = new PaymentMessagePageRenderer();
$paymentMessagePageRenderer->render();
?>