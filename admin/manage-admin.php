<?php include('partials/menu.php'); 

class AdminListManager extends BaseManager {
    public function __construct($db = null) {
        parent::__construct($db);
    }
    
    public function getAllAdmins() {
        $sql = "SELECT * FROM tbl_admin";
        $res = $this->db->query($sql);
        return $res ? $this->db->fetchAll($res) : [];
    }
}

$adminListManager = new AdminListManager();
$admins = $adminListManager->getAllAdmins();
?>
<!--main content section starts here-->
<div class="main">
    <div class="wrapper">
        <h1>Manage Admin</h1>
        <br>
        <br>

        <?php
            if(isset($_SESSION['add'])) {
                echo $_SESSION['add'];
                unset($_SESSION['add']);
            }

            if(isset($_SESSION['delete'])) {
                echo $_SESSION['delete'];
                unset($_SESSION['delete']);
            }

            if(isset($_SESSION['update'])) {
                echo $_SESSION['update'];
                unset($_SESSION['update']);
            }

            if(isset($_SESSION['user-not-found'])) {
                echo $_SESSION['user-not-found'];
                unset($_SESSION['user-not-found']);
            }

            if(isset($_SESSION['password-not-match'])) {
                echo $_SESSION['password-not-match'];
                unset($_SESSION['password-not-match']);
            }

            if(isset($_SESSION['change-password'])) {
                echo $_SESSION['change-password'];
                unset($_SESSION['change-password']);
            }
        ?>
        <br>
        <br>
        <!-- button to add admin -->
        <a href="add-admin.php" class="btn-primary">Add Admin</a>
        <br>
        <br>
        <table class="tbl-full">
            <tr>
                <th>S.N.</th>
                <th>Full Name</th>
                <th>Username</th>
                <th>Actions</th>
            </tr>

            <?php
                $sn = 1;
                if (count($admins) > 0) {
                    foreach ($admins as $admin) {
                        $id = $admin['id'];
                        $full_name = $admin['full_name'];
                        $username = $admin['username'];
            ?>
            <tr>
                <td><?php echo $sn++; ?></td>
                <td><?php echo htmlspecialchars($full_name); ?></td>
                <td><?php echo htmlspecialchars($username); ?></td>
                <td>
                    <a href="<?php echo SITEURL; ?>admin/update-password.php?id=<?php echo $id;?>" class="btn-primary">Change Password</a>
                    <a href="<?php echo SITEURL; ?>admin/update-admin.php?id=<?php echo $id;?>" class="btn-secondary">Update Admin</a>
                </td>
            </tr>
            <?php
                    }
                }
            ?>
        </table>
    </div>
</div>
<!--main content section ends here-->
<?php include('partials/footer.php') ?>
