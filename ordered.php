<?php
session_start();
include('config/constants.php'); 
include('partials-front/menu.php');

class OrderPlacementManager extends BaseManager {
    public function __construct($db = null) {
        parent::__construct($db);
    }
    
    public function createOrder($itemTitle, $price, $quantity, $total, $fullName, $phone, $email, $address, $userId, $paymentOption) {
        $sql = "INSERT INTO tbl_order (item, price, qty, total, order_date, status, customer_name, customer_contact, customer_email, customer_address, uid, payment_option)
                VALUES (?, ?, ?, ?, NOW(), 'Ordered', ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $this->db->bind($stmt, 'sdidsissis', 
            $itemTitle, 
            $price, 
            $quantity, 
            $total, 
            $fullName, 
            $phone, 
            $email, 
            $address, 
            $userId, 
            $paymentOption
        );
        
        return $this->db->execute($stmt);
    }
    
    public function processCartOrder($cartItems, $fullName, $phone, $email, $address, $paymentMethod) {
        $totalPrice = 0;
        
        foreach ($cartItems as $item) {
            if (is_array($item) && isset($item['title'], $item['price'], $item['quantity'])) {
                $itemTotal = $item['price'] * $item['quantity'];
                $totalPrice += $itemTotal;
                
                if (isset($_SESSION['user_id'])) {
                    $result = $this->createOrder(
                        $item['title'],
                        $item['price'],
                        $item['quantity'],
                        $itemTotal,
                        $fullName,
                        $phone,
                        $email,
                        $address,
                        $_SESSION['user_id'],
                        $paymentMethod
                    );
                    
                    if (!$result) {
                        return false;
                    }
                } else {
                    return false;
                }
            }
        }
        
        return true;
    }
}

// Check if user is logged in
if (!isset($_SESSION["user_logged_in"]) || $_SESSION["user_logged_in"] !== true) {
    header("location: login.php");
    exit();
}

// Fetch cart details from session
$cartItems = isset($_SESSION['cart']) ? $_SESSION['cart'] : array();
$totalPrice = 0;

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Process the order
    if (count($cartItems) > 0) {
        // Get delivery details from the form
        $fullName = htmlspecialchars($_POST['full_name']);
        $phone = htmlspecialchars($_POST['phone']);
        $email = htmlspecialchars($_POST['email']);
        $address = htmlspecialchars($_POST['address']);
        $paymentMethod = htmlspecialchars($_POST['payment_method']);
        
        $order = new OrderPlacementManager();
        
        if ($order->processCartOrder($cartItems, $fullName, $phone, $email, $address, $paymentMethod)) {
            // Clear the cart
            unset($_SESSION['cart']);
            
            // Redirect based on payment option
            if ($paymentMethod === 'Online Payment') {
                header('location: payment-request.php'); 
            } else {
                $_SESSION['order'] = "<div class='success text-center'>Order Placed Successfully</div>";
                header('location: index.php'); 
            }
            exit();
        } else {
            echo "<div class='error text-red-600'>Failed to place order. Please try again.</div>";
        }
    } else {
        header('location: index.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <title>Order Confirmation</title>
</head>
<body>

<section class="order-confirmation py-8">
    <div class="container mx-auto">
        <h2 class="text-center text-3xl font-bold mb-6">Confirm Your Order</h2>

        <?php if (count($cart_items) > 0): ?>
            <table class="min-w-full bg-white border border-gray-200"> 
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-2 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-600">Item</th>
                        <th class="py-2 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-600">Price</th>
                        <th class="py-2 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-600">Quantity</th>
                        <th class="py-2 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-600">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                        <?php 
                        $item_total = $item['price'] * $item['quantity']; 
                        $total_price += $item_total;
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-4 border-b border-gray-200"><?= htmlspecialchars($item['title']) ?></td>
                            <td class="py-3 px-4 border-b border-gray-200">Rs. <?= htmlspecialchars($item['price']) ?></td>
                            <td class="py-3 px-4 border-b border-gray-200"><?= htmlspecialchars($item['quantity']) ?></td>
                            <td class="py-3 px-4 border-b border-gray-200">Rs. <?= htmlspecialchars($item_total) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="font-bold">
                        <td colspan="3" class="py-3 px-4 border-b border-gray-200">Total</td>
                        <td class="py-3 px-4 border-b border-gray-200">Rs. <?= htmlspecialchars($total_price) ?></td>
                    </tr>
                </tbody>
            </table>

            <h3 class="text-xl font-semibold mt-8">Delivery Details</h3>
            <form action="" method="POST" class="mt-4">
                <div class="mb-4">
                    <label for="full_name" class="block text-gray-700 font-bold">Full Name</label>
                    <input type="text" name="full_name" id="full_name" required class="w-full p-2 border rounded" placeholder="Enter your full name">
                </div>
                <div class="mb-4">
                    <label for="phone" class="block text-gray-700 font-bold">Phone Number</label>
                    <input type="text" name="phone" id="phone" required class="w-full p-2 border rounded" placeholder="Enter your phone number">
                </div>
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 font-bold">Email</label>
                    <input type="email" name="email" id="email" required class="w-full p-2 border rounded" placeholder="Enter your email">
                </div>
                <div class="mb-4">
                    <label for="address" class="block text-gray-700 font-bold">Address</label>
                    <textarea name="address" id="address" required class="w-full p-2 border rounded" placeholder="Enter your delivery address"></textarea>
                </div>

                <div class="mb-4">
                    <label for="payment_method" class="block text-gray-700 font-bold">Payment Method</label>
                    <select name="payment_method" id="payment_method" required class="w-full p-2 border rounded">
                        <option value="COD">Cash on Delivery</option>
                        <option value="Online Payment">Online Payment</option>
                    </select>
                </div>

                <input type="hidden" name="total_price" value="<?= htmlspecialchars($total_price) ?>">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Confirm Order</button>
            </form>

        <?php else: ?>
            <div class="error text-red-600 text-lg font-bold text-center mt-4">Your cart is empty.</div>
        <?php endif; ?>

    </div>
</section>

<?php include('partials-front/footer.php'); ?>

</body>
</html>
