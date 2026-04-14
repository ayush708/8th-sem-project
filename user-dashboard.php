<?php
include('config/constants.php');
include('partials-front/menu.php');

class UserDashboardManager extends BaseManager {
    public function __construct($db = null) {
        parent::__construct($db);
    }

    public function getLoginMessage() {
        if (isset($_SESSION['login'])) {
            $message = $_SESSION['login'];
            unset($_SESSION['login']);
            return $message;
        }
        return '';
    }

    public function getOrderCountByUserId($userId) {
        $sql = "SELECT COUNT(*) AS total_orders FROM tbl_order WHERE uid = ?";
        $stmt = $this->db->prepare($sql);
        $this->db->bind($stmt, "i", $userId);
        $this->db->execute($stmt);
        $res = $this->db->getResult($stmt);
        if ($res && $this->db->numRows($res) > 0) {
            $row = $this->db->fetchAssoc($res);
            return (int)$row['total_orders'];
        }
        return 0;
    }

    public function getOrdersByUserId($userId) {
        $sql = "SELECT * FROM tbl_order WHERE uid = ? ORDER BY order_date DESC";
        $stmt = $this->db->prepare($sql);
        $this->db->bind($stmt, "i", $userId);
        $this->db->execute($stmt);
        $res = $this->db->getResult($stmt);
        return $res ? $this->db->fetchAll($res) : [];
    }
}

$dashboardManager = new UserDashboardManager();
$userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$loginMessage = $dashboardManager->getLoginMessage();
$orderCount = $dashboardManager->getOrderCountByUserId($userId);
$orders = $dashboardManager->getOrdersByUserId($userId);
?>
<!--main content section starts here-->
<div class="main">
    <div class="wrapper">
        <div class="dashboard-header" style="
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 30px;
            padding: 0 10px;
            box-sizing: border-box;
        ">
            <h1 style="font-size: 2.5em; color: #343a40; margin: 0;">Dashboard</h1>
            <div class="profile-container" style="
                position: relative;
                display: flex;
                align-items: center;
            ">
                <button class="profile-button" onclick="window.location.href='profile.php';" style="
                    background-color: #007bff;
                    border: none;
                    border-radius: 50%;
                    width: 50px;
                    height: 50px;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    color: white;
                    font-size: 24px;
                    cursor: pointer;
                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
                ">
                    <span style="font-size: 24px;">&#128100;</span> <!-- Profile emoji -->
                </button>
                <div style="margin-left: 20px; display: flex; align-items: center;">
                    <a href="profile.php" style="
                        font-size: 1em;
                        color: #007bff;
                        text-decoration: none;
                        font-weight: bold;
                        transition: color 0.3s;
                        margin-right: 20px;
                    " onmouseover="this.style.color='#0056b3'" onmouseout="this.style.color='#007bff'">Edit Profile</a>
                    <a href="change_password.php" style="
                        display: flex;
                        align-items: center;
                        color: #007bff;
                        text-decoration: none;
                        font-weight: bold;
                        transition: color 0.3s;
                    " onmouseover="this.style.color='#0056b3'" onmouseout="this.style.color='#007bff'">
                        <span style="font-size: 20px; margin-right: 5px;">&#128273;</span> <!-- Key emoji -->
                        Change Password
                    </a>
                </div>
            </div>
        </div>

        <!-- Notification for successful login -->
        <?php if (!empty($loginMessage)) {
            echo "<div style='font-size: 1.2em; color: #28a745; text-align: center; margin-bottom: 20px;'>{$loginMessage}</div>";
        } ?>
        <br>

        <!-- Dashboard content -->
        <div class="col-4 text-center" style="
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 150px;
        ">
            <div>
                <h1 style="font-size: 3em; color: #007bff; margin: 0;"><?php echo $orderCount; ?></h1>
                <p style="font-size: 1.2em; color: #495057; margin: 0;">Total Orders</p>
            </div>
        </div>

        <div class="clearfix"></div>

        <table class="tbl-full" style="width: 100%; border-collapse: collapse; margin-top: 20px;">
            <tr style="background-color: #007bff; color: #ffffff;">
                <th style="padding: 15px; text-align: left;">SN</th>
                <th style="padding: 15px; text-align: left;">Item</th>
                <th style="padding: 15px; text-align: left;">Price</th>
                <th style="padding: 15px; text-align: left;">Quantity</th>
                <th style="padding: 15px; text-align: left;">Total</th>
                <th style="padding: 15px; text-align: left;">Order Date</th>
                <th style="padding: 15px; text-align: left;">Status</th>
                <th style="padding: 15px; text-align: left;">Action</th>
            </tr>
            <?php 
            if (!empty($orders)) {
                $sn = 1;
                foreach ($orders as $row) {
                    $order_id = $row['id']; // Assuming `id` is the primary key for orders
                    $item = $row['item'];
                    $price = $row['price'];
                    $qty = $row['qty'];
                    $total = $row['total'];
                    $order_date = $row['order_date'];
                    $status = $row['status'];
                    ?>
                    <tr style="background-color: #f8f9fa;">
                        <td style="padding: 15px;"><?php echo $sn++; ?></td>
                        <td style="padding: 15px;"><?php echo $item; ?></td>
                        <td style="padding: 15px;">Rs.<?php echo $price; ?></td>
                        <td style="padding: 15px;"><?php echo $qty; ?></td>
                        <td style="padding: 15px;">Rs.<?php echo $total; ?></td>
                        <td style="padding: 15px;"><?php echo $order_date; ?></td>
                        <td style="padding: 15px;"><?php echo $status; ?></td>
                        <td style="padding: 15px;">
                            <!-- Cancel Button with data-id attribute -->
                            <button class="cancel-btn" data-id="<?php echo $order_id; ?>" style="background-color: #dc3545; color: #ffffff; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">Cancel</button>
                        </td>
                    </tr>
                <?php
                }
            } else {
                ?>
                <tr>
                    <td colspan="8" style="padding: 20px; text-align: center; color: #6c757d;">No orders found.</td>
                </tr>
            <?php
            }
            ?>
        </table>

    </div>
</div>
<!--main content section ends here-->
<em style="display: block; text-align: center; margin-top: 30px; color: #6c757d;"><strong>Note: For order cancellation, Contact Admin 9808224685/9840499983</strong></em>
<?php include('partials-front/footer.php'); ?>

<!-- JavaScript code for canceling orders -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Attach event listeners to cancel buttons
    document.querySelectorAll('.cancel-btn').forEach(button => {
        button.addEventListener('click', function() {
            const orderId = this.getAttribute('data-id');
            
            if (confirm('Are you sure you want to cancel this order?')) {
                fetch('cancel_order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        'order_id': orderId
                    })
                })
                .then(response => response.text())
                .then(result => {
                    if (result.includes("cancelled successfully")) {
                        alert('Order has been cancelled.');
                        location.reload(); // Reload the page to reflect changes
                    } else {
                        alert('Failed to cancel order: ' + result);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            }
        });
    });
});
</script>

<style>
/* Responsive design for profile button */
@media (max-width: 768px) {
    .dashboard-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .profile-button {
        width: 40px;
        height: 40px;
        font-size: 20px;
    }

    .profile-container a {
        font-size: 0.9em;
    }
}

@media (max-width: 480px) {
    .dashboard-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .profile-button {
        width: 35px;
        height: 35px;
        font-size: 18px;
    }

    .profile-container a {
        font-size: 0.8em;
    }

    h1 {
        font-size: 1.8em;
    }
}
</style>