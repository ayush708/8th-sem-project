<?php 
include('partials/menu.php');

class DashboardStatsManager extends BaseManager {
    public function __construct($db = null) {
        parent::__construct($db);
    }
    
    public function getCategoryCount() {
        $sql = "SELECT COUNT(*) as count FROM tbl_category";
        $res = $this->db->query($sql);
        if ($res) {
            $row = $this->db->fetchAssoc($res);
            return $row['count'];
        }
        return 0;
    }
    
    public function getItemCount() {
        $sql = "SELECT COUNT(*) as count FROM tbl_items";
        $res = $this->db->query($sql);
        if ($res) {
            $row = $this->db->fetchAssoc($res);
            return $row['count'];
        }
        return 0;
    }
    
    public function getOrderCount() {
        $sql = "SELECT COUNT(*) as count FROM tbl_order";
        $res = $this->db->query($sql);
        if ($res) {
            $row = $this->db->fetchAssoc($res);
            return $row['count'];
        }
        return 0;
    }
    
    public function getUserCount() {
        $sql = "SELECT COUNT(*) as count FROM tbl_users";
        $res = $this->db->query($sql);
        if ($res) {
            $row = $this->db->fetchAssoc($res);
            return $row['count'];
        }
        return 0;
    }
    
    public function getAdminCount() {
        $sql = "SELECT COUNT(*) as count FROM tbl_admin";
        $res = $this->db->query($sql);
        if ($res) {
            $row = $this->db->fetchAssoc($res);
            return $row['count'];
        }
        return 0;
    }
}

$dashboardStatsManager = new DashboardStatsManager();
$categoryCount = $dashboardStatsManager->getCategoryCount();
$itemCount = $dashboardStatsManager->getItemCount();
$orderCount = $dashboardStatsManager->getOrderCount();
$userCount = $dashboardStatsManager->getUserCount();
$adminCount = $dashboardStatsManager->getAdminCount();
?>

<!-- Add Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Main content section starts here -->
<div class="main">
    <div class="wrapper">
        <h1 class="dashboard-title">Dashboard Overview</h1>
        <br>
        <?php 
            if(isset($_SESSION['login'])) {
                echo "<div class='alert-success'><i class='fas fa-check-circle'></i> " . $_SESSION['login'] . "</div>";
                unset($_SESSION['login']);
            }
        ?>
        <br>
        <div class="dashboard-container">
            <div class="dashboard-card category-card">
                <div class="card-icon">
                    <i class="fas fa-folder-open"></i>
                </div>
                <h1><?php echo $categoryCount; ?></h1>
                <p>Categories</p>
            </div>

            <div class="dashboard-card items-card">
                <div class="card-icon">
                    <i class="fas fa-box-open"></i>
                </div>
                <h1><?php echo $itemCount; ?></h1>
                <p>Items</p>
            </div>

            <div class="dashboard-card orders-card">
                <div class="card-icon">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <h1><?php echo $orderCount; ?></h1>
                <p>Total Orders</p>
            </div>

            <div class="dashboard-card users-card">
                <div class="card-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h1><?php echo $userCount; ?></h1>
                <p>Total Users</p>
            </div>

            <div class="dashboard-card admin-card">
                <div class="card-icon">
                    <i class="fas fa-user-shield"></i>
                </div>
                <h1><?php echo $adminCount; ?></h1>
                <p>Total Admins</p>
            </div>
        </div>
    </div>
</div>

<!-- Include styles and scripts -->
<style>
    :root {
        --primary-color: #2c3e50;
        --secondary-color: #3498db;
        --success-color: #27ae60;
        --warning-color: #f1c40f;
        --danger-color: #e74c3c;
    }

    .dashboard-title {
        text-align: center;
        font-size: 2.2rem;
        margin-bottom: 30px;
        color: var(--primary-color);
        position: relative;
        padding-bottom: 10px;
    }

    .dashboard-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 100px;
        height: 3px;
        background: var(--secondary-color);
    }

    .dashboard-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 25px;
        padding: 20px 0;
    }

    .dashboard-card {
        background: #ffffff;
        padding: 30px 25px;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
    }

    .card-icon {
        width: 60px;
        height: 60px;
        background: var(--secondary-color);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        color: white;
        font-size: 1.8rem;
    }

    .dashboard-card h1 {
        font-size: 2.4rem;
        margin: 10px 0;
        color: var(--primary-color);
    }

    .dashboard-card p {
        font-size: 1.1rem;
        color: #666;
        margin: 0;
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
        padding: 15px 25px;
        border-radius: 8px;
        border-left: 4px solid #28a745;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* Category-specific colors */
    .category-card .card-icon { background: #3498db; }
    .items-card .card-icon { background: #2ecc71; }
    .orders-card .card-icon { background: #e67e22; }
    .revenue-card .card-icon { background: #9b59b6; }

    @media (max-width: 768px) {
        .dashboard-container {
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .dashboard-card {
            padding: 20px 15px;
        }
    }

    @media (max-width: 480px) {
        .dashboard-container {
            grid-template-columns: 1fr;
        }
        
        .dashboard-title {
            font-size: 1.8rem;
        }
    }
</style>

<?php include('partials/footer.php'); ?>