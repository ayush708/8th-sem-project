<?php include('partials/menu.php'); 

class UserListManager extends BaseManager {
    public function __construct($db = null) {
        parent::__construct($db);
    }
    
    public function getAllUsers() {
        $sql = "SELECT * FROM tbl_users";
        $res = $this->db->query($sql);
        return $res ? $this->db->fetchAll($res) : [];
    }
}

$userListManager = new UserListManager();
$users = $userListManager->getAllUsers();
?>

<div class="main">
    <div class="wrapper">
        <h1>Manage Users</h1>

        <?php
            if(isset($_SESSION['delete'])) {
                echo $_SESSION['delete'];
                unset($_SESSION['delete']);
            }
        ?>

        <br><br>

        <table class="tbl-full">
            <tr>
                <th>S.N.</th>
                <th>Username</th>
                <th>Phone Number</th>
                <th>Actions</th>
            </tr>

            <?php
                $sn = 1;
                if (count($users) > 0) {
                    foreach ($users as $user) {
                        $username = $user['username'];
                        $phone = $user['phone'];
            ?>

                            <tr>
                                <td><?php echo $sn++; ?></td>
                                <td><?php echo htmlspecialchars($username); ?></td>
                                <td><?php echo htmlspecialchars($phone); ?></td>
                                <td>
                                    <a href="<?php echo SITEURL; ?>admin/delete-user.php?username=<?php echo urlencode($username); ?>" class="btn-secondary1">Delete</a>
                                </td>
                            </tr>

            <?php
                    }
                } else {
                    echo "<tr><td colspan='4'>No Users Added Yet</td></tr>";
                }
            ?>

        </table>
    </div>
</div>

<?php include('partials/footer.php'); ?>
