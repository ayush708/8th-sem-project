<?php include('partials/menu.php'); 

class AdminUpdateManager extends BaseManager {
    public function __construct($db = null) {
        parent::__construct($db);
    }
    
    public function getAdminById($id) {
        $sql = "SELECT * FROM tbl_admin WHERE id=?";
        $stmt = $this->db->prepare($sql);
        $this->db->bind($stmt, "i", $id);
        $this->db->execute($stmt);
        $res = $this->db->getResult($stmt);
        
        if ($this->db->numRows($res) == 1) {
            return $this->db->fetchAssoc($res);
        }
        return null;
    }
    
    public function validateFullName($full_name) {
        if (empty($full_name)) {
            return "Enter Full Name";
        } elseif (!preg_match("/^[a-zA-Z\s]+$/", $full_name)) {
            return "Full Name must only contain letters and spaces";
        }
        return null;
    }
    
    public function validateUsername($username) {
        if (empty($username)) {
            return "Enter Username";
        } elseif (!preg_match("/^[a-zA-Z0-9]{4,29}$/", $username)) {
            return "Username must be alphanumeric and between 4 to 29 characters";
        }
        return null;
    }
    
    public function updateAdmin($id, $full_name, $username) {
        $sql = "UPDATE tbl_admin SET full_name=?, username=? WHERE id=?";
        $stmt = $this->db->prepare($sql);
        $this->db->bind($stmt, "ssi", $full_name, $username, $id);
        return $this->db->execute($stmt);
    }
}

// Initialize error array
$err = [];
$adminUpdateManager = new AdminUpdateManager();

// Check if ID is passed via GET
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $admin = $adminUpdateManager->getAdminById($id);
    
    if (!$admin) {
        header('location:'.SITEURL.'admin/manage-admin.php');
        exit;
    }
    
    $full_name = $admin['full_name'];
    $username = $admin['username'];
} else {
    header('location:'.SITEURL.'admin/manage-admin.php');
    exit;
}

// Check if form is submitted
if (isset($_POST['submit'])) {
    $id = $_POST['id'];
    $full_name = $_POST['full_name'];
    $username = $_POST['username'];

    // Validate inputs
    if ($nameError = $adminUpdateManager->validateFullName($full_name)) {
        $err['full_name'] = $nameError;
    }
    if ($usernameError = $adminUpdateManager->validateUsername($username)) {
        $err['username'] = $usernameError;
    }

    // If no errors, proceed with update
    if (empty($err)) {
        if ($adminUpdateManager->updateAdmin($id, $full_name, $username)) {
            $_SESSION['update'] = "Admin Updated Successfully";
            header('location:'.SITEURL.'admin/manage-admin.php');
            exit;
        } else {
            $_SESSION['update'] = "Failed to update Admin";
            header('location:'.SITEURL.'admin/manage-admin.php');
            exit;
        }
    }
}
?>

<div class="main-content">
    <div class="wrapper">
        <h1>Update Admin</h1>
        <br><br>

        
        <form action="" method="POST" style="max-width: 500px; margin: auto; background: #f7f7f7; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);">
    <div style="margin-bottom: 15px;">
        <label for="full_name" style="display: block; font-weight: bold; margin-bottom: 5px;">Full Name:</label>
        <input type="text" name="full_name" id="full_name" value="<?php echo htmlspecialchars($full_name); ?>" placeholder="Enter your name" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
        <span class="error" style="color: red; font-size: 0.9em;"><?php if(isset($err['full_name'])) echo $err['full_name']; ?></span>
    </div>

    <div style="margin-bottom: 15px;">
        <label for="username" style="display: block; font-weight: bold; margin-bottom: 5px;">Username:</label>
        <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($username); ?>" placeholder="Enter your username" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
        <span class="error" style="color: red; font-size: 0.9em;"><?php if(isset($err['username'])) echo $err['username']; ?></span>
    </div>

    <div style="text-align: center;">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <input type="submit" name="submit" value="Update Admin" class="btn-secondary" style="width: 100%; padding: 10px; background-color: #2196F3; color: white; border: none; border-radius: 5px; cursor: pointer;">
    </div>
</form>

    </div>
</div>


<?php include('partials/footer.php'); ?>
