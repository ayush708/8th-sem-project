<?php include('partials/menu.php'); 

class OrderListManager extends BaseManager {
    public function __construct($db = null) {
        parent::__construct($db);
    }
    
    public function getAllOrders() {
        $sql = "SELECT * FROM tbl_order ORDER BY id DESC";
        $res = $this->db->query($sql);
        return $res ? $this->db->fetchAll($res) : [];
    }
}

$orderListManager = new OrderListManager();
$orders = $orderListManager->getAllOrders();
?>

<div class="main" style="padding: 20px; background-color: #f8f9fa;">
    <div class="wrapper" style="max-width: 1200px; margin: 0 auto;">
        <h1 style="font-size: 2.5em; color: #343a40; text-align: center; margin-bottom: 30px;">Order</h1>

        <br>

        <?php
        if (isset($_SESSION['update'])) {
            echo "<div style='font-size: 1.2em; color: #28a745; text-align: center; margin-bottom: 20px;'>" . $_SESSION['update'] . "</div>";
            unset($_SESSION['update']);
        }
        ?>

        <br>

        <table class="tbl-full" style="width: 100%; border-collapse: collapse; margin-top: 20px; background-color: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);">
            <thead style="background-color: #007bff; color: #ffffff;">
                <tr>
                    <th style="padding: 15px; text-align: left;">S.N.</th>
                    <th style="padding: 15px; text-align: left;">Item</th>
                    <th style="padding: 15px; text-align: left;">Price</th>
                    <th style="padding: 15px; text-align: left;">Qty.</th>
                    <th style="padding: 15px; text-align: left;">Total</th>
                    <th style="padding: 15px; text-align: left;">Order Date</th>
                    <th style="padding: 15px; text-align: left;">Status</th>
                    <th style="padding: 15px; text-align: left;">Customer Name</th>
                    <th style="padding: 15px; text-align: left;">Contact</th>
                    <th style="padding: 15px; text-align: left;">Email</th>
                    <th style="padding: 15px; text-align: left;">Address</th>
                    <th style="padding: 15px; text-align: left;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sn = 1;
                if (count($orders) > 0) {
                    foreach ($orders as $order) {
                        $id = $order['id'];
                        $item = $order['item'];
                        $price = $order['price'];
                        $qty = $order['qty'];
                        $total = $order['total'];
                        $order_date = $order['order_date'];
                        $status = $order['status'];
                        $customer_name = $order['customer_name'];
                        $customer_contact = $order['customer_contact'];
                        $customer_email = $order['customer_email'];
                        $customer_address = $order['customer_address'];
                ?>
                        <tr style="background-color: #f8f9fa;">
                            <td style="padding: 15px;"><?php echo $sn++; ?></td>
                            <td style="padding: 15px;"><?php echo htmlspecialchars($item); ?></td>
                            <td style="padding: 15px;">Rs.<?php echo htmlspecialchars($price); ?></td>
                            <td style="padding: 15px;"><?php echo htmlspecialchars($qty); ?></td>
                            <td style="padding: 15px;">Rs.<?php echo htmlspecialchars($total); ?></td>
                            <td style="padding: 15px;"><?php echo htmlspecialchars($order_date); ?></td>
                            <td style="padding: 15px;"><?php echo htmlspecialchars($status); ?></td>
                            <td style="padding: 15px;"><?php echo htmlspecialchars($customer_name); ?></td>
                            <td style="padding: 15px;"><?php echo htmlspecialchars($customer_contact); ?></td>
                            <td style="padding: 15px;"><?php echo htmlspecialchars($customer_email); ?></td>
                            <td style="padding: 15px;"><?php echo htmlspecialchars($customer_address); ?></td>
                            <td style="padding: 15px; text-align: center;">
                                <a href="<?php echo SITEURL; ?>admin/update-order.php?id=<?php echo $id; ?>" style="display: inline-block; background-color: #007bff; color: #ffffff; padding: 10px 15px; border-radius: 5px; text-decoration: none;">Update</a>
                            </td>
                        </tr>
                <?php
                    }
                } else {
                    echo "<tr><td colspan='12' style='padding: 20px; text-align: center; color: #6c757d;'>Order Not Available</td></tr>";
                }
                ?>
            </tbody>
        </table>

    </div>
</div>

<style>
    @media (max-width: 768px) {
        .tbl-full { display: block; overflow-x: auto; }
        .tbl-full thead { display: none; }
        .tbl-full tr { display: block; margin-bottom: 15px; }
        .tbl-full td { display: block; text-align: right; padding-left: 50%; position: relative; }
        .tbl-full td:last-child { border-bottom: none; }
        .tbl-full td a { display: block; width: 100%; text-align: center; margin-top: 10px; }
    }
</style>

<?php include('partials/footer.php'); ?>
