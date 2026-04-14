<?php 
include('partials-front/menu.php');
include('config/constants.php');

class OrderProcessorManager extends BaseManager {
    public function __construct($db = null) {
        parent::__construct($db);
    }
    
    public function incrementItemViews($itemId) {
        $sql = "UPDATE tbl_items SET total_views = total_views + 1 WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $this->db->bind($stmt, "i", $itemId);
        return $this->db->execute($stmt);
    }
    
    public function getItemDetails($itemId) {
        $sql = "SELECT * FROM tbl_items WHERE id=?";
        $stmt = $this->db->prepare($sql);
        $this->db->bind($stmt, "i", $itemId);
        $this->db->execute($stmt);
        $res = $this->db->getResult($stmt);
        
        if ($res && $this->db->numRows($res) == 1) {
            return $this->db->fetchAssoc($res);
        }
        return null;
    }
    
    public function getUserDetails($userId) {
        $sql = "SELECT * FROM tbl_users WHERE user_id=?";
        $stmt = $this->db->prepare($sql);
        $this->db->bind($stmt, "i", $userId);
        $this->db->execute($stmt);
        $res = $this->db->getResult($stmt);
        
        if ($res && $this->db->numRows($res) == 1) {
            return $this->db->fetchAssoc($res);
        }
        return null;
    }
    
    public function createOrder($itemTitle, $price, $quantity, $total, $fullName, $phone, $email, $address, $userId, $paymentMethod) {
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
            $paymentMethod
        );
        
        return $this->db->execute($stmt);
    }
}

// Check if user is logged in
if (!isset($_SESSION["user_logged_in"]) || $_SESSION["user_logged_in"] !== true) {
    header("location: login.php");
    exit();
}

$orderProcessor = new OrderProcessorManager();

// Check if item_id is passed in the URL
if (isset($_GET['item_id'])) {
    $itemId = $_GET['item_id'];
    $orderProcessor->incrementItemViews($itemId);
} else {
    header('location:' . SITEURL);
    exit();
}

// Check whether item_id is set or not
if (isset($_GET['item_id'])) {
    $itemId = $_GET['item_id'];
    $item = $orderProcessor->getItemDetails($itemId);
    
    if ($item) {
        $title = $item['title'];
        $price = $item['price'];
        $imageName = $item['image_name'];
        $availableQty = $item['quantity'];
    } else {
        header('location:' . SITEURL);
        exit();
    }
} else {
    header('location:' . SITEURL);
    exit();
}

// Initialize error array
$err = [];

// Fetch user details from database
$userId = $_SESSION['user_id'];
$user = $orderProcessor->getUserDetails($userId);

if ($user) {
    $userFullName = $user['full_name'];
    $userPhone = $user['phone'];
    $userEmail = $user['email'];
    $userAddress = $user['address'];
} else {
    header('location: login.php');
    exit();
}

// Initialize error array
$err = [];

// Check whether submit button is clicked or not
if (isset($_POST['submit'])) {
    // Get all the details from the form
    $itemTitle = $title;
    $itemPrice = $price;
    $itemQty = isset($_POST['qty']) ? intval($_POST['qty']) : 0;
    $itemTotal = $itemPrice * $itemQty;
    $customerName = $userFullName;
    $customerPhone = $userPhone;
    $customerEmail = $userEmail;
    $customerAddress = isset($_POST['address']) ? htmlspecialchars($_POST['address']) : '';
    $paymentOption = isset($_POST['payment_option']) ? htmlspecialchars($_POST['payment_option']) : '';

    // Validate quantity
    if (empty($itemQty) || !is_numeric($itemQty) || $itemQty <= 0) {
        $err['quantity'] = "Quantity must be greater than zero.";
    } elseif ($itemQty > $availableQty) {
        $err['quantity'] = "Only $availableQty items available in stock.";
    }

    // Validate address
    if (empty($customerAddress)) {
        $err['customer_address'] = "Address is required";
    }

    // Save the order in the database
    if (empty($err)) {
        if ($orderProcessor->createOrder($itemTitle, $itemPrice, $itemQty, $itemTotal, $customerName, $customerPhone, $customerEmail, $customerAddress, $userId, $paymentOption)) {
            $lastOrderId = Database::getInstance()->lastInsertId();
            $_SESSION['order_id'] = $lastOrderId;
            
            // Redirect based on payment method
            if ($paymentOption == "Online Payment") {
                header("Location: checkout.php?amount=$itemTotal&item=$itemTitle&order_id=$lastOrderId");
                exit();
            } else {
                $_SESSION['order'] = "<div class='success text-center'>Order Placed Successfully</div>";
                header('location:' . SITEURL);
                exit();
            }
        } else {
            $_SESSION['order'] = "<div class='error text-center'>Failed to Place Order</div>";
            header('location:' . SITEURL);
            exit();
        }
    }
}
?>
?>

<!-- item SEARCH Section Starts Here -->
<section class="search" style="padding: 20px; background-color: #f8f9fa;">
    <div class="container" style="max-width: 900px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);">
        
        <h2 class="text-center" style="color: #343a40; margin-bottom: 20px; font-size: 1.5em;">Fill this form to confirm your order.</h2>

        <?php if (!empty($err)): ?>
            <div class="error text-center" style="color: red; margin-bottom: 20px;">
                <?php foreach ($err as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?> 

        <form action="" method="POST" class="order" style="display: flex; flex-direction: column; gap: 20px;">
            <div class="box" style="display: flex; flex-direction: column; gap: 20px;">
                <div>
                    <h2 class="order-label" style="font-size: 1.5em; color: #343a40; margin-bottom: 10px;">Selected item</h2>
                    <h3 class="order-label" style="font-size: 1.2em; color: #495057; margin-bottom: 10px;"><?php echo $title; ?></h3>
                    <input type="hidden" name="item" value="<?php echo $title; ?>">
                    <input type="hidden" name="price" value="<?php echo $price; ?>">
                    <p class="item-price order-label" style="font-size: 1.2em; color: #28a745; margin-bottom: 10px;">Rs.<?php echo $price; ?></p>
                    <div class="order-label" style="font-size: 1.2em; color: #343a40; margin-bottom: 10px;">Quantity</div>
                    <input type="number" name="qty" class="input-responsive" value="1" min="1" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ced4da;">
                </div>
                
                <div>
                    <h2 class="order-label" style="font-size: 1.5em; color: #343a40; margin-bottom: 10px;">Delivery Details</h2>
                    <div class="order-label" style="font-size: 1.2em; color: #343a40; margin-bottom: 10px;">Full Name</div>
                    <input type="text" name="full_name" value="<?php echo $userFullName; ?>" readonly class="input-responsive" disabled style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ced4da;">
                    
                    <div class="order-label" style="font-size: 1.2em; color: #343a40; margin-bottom: 10px;">Phone Number</div>
                    <input type="tel" name="contact" value="<?php echo $userPhone; ?>" readonly class="input-responsive" disabled style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ced4da;">
                    
                    <div class="order-label" style="font-size: 1.2em; color: #343a40; margin-bottom: 10px;">Email</div>
                    <input type="text" name="email" value="<?php echo $userEmail; ?>" readonly class="input-responsive" disabled style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ced4da;">
                    
                    <div class="order-label" style="font-size: 1.2em; color: #343a40; margin-bottom: 10px;">Address</div>
                    <textarea name="address" rows="4" placeholder="Street, City" class="input-responsive" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ced4da;"></textarea>
                    
                    <div class="order-label" style="font-size: 1.2em; color: #343a40; margin-bottom: 10px;">Payment Method</div>
                    <select name="payment_option" class="input-responsive" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ced4da;">
                        <option value="Cash on Delivery">Cash on Delivery</option>
                        <option value="Online Payment">Online Payment</option>
                    </select>
                </div>
            </div>

            <input type="submit" name="submit" value="Confirm Order" class="btn btn-primary" style="background-color: #007bff; color: #fff; padding: 10px; border: none; border-radius: 5px; cursor: pointer; width: 100%;">
        </form>
    </div>
</section>
<!-- item SEARCH Section Ends Here -->

<?php include('partials-front/footer.php'); ?>