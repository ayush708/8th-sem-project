<?php include('partials/menu.php'); 

class AdminManager extends BaseManager {
    public function __construct($db = null) {
        parent::__construct($db);
    }
    
    public function validateFullName($full_name) {
        if (empty($full_name)) {
            return "Enter Full Name";
        } elseif (!preg_match("/^[A-Za-z\s]+$/", $full_name)) {
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
    
    public function validatePassword($password) {
        if (empty($password)) {
            return "Enter Password";
        }
        return null;
    }
    
    public function addAdmin($full_name, $username, $password) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO tbl_admin (full_name, username, password) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $this->db->bind($stmt, "sss", $full_name, $username, $hashedPassword);
        return $this->db->execute($stmt);
    }
}

// Initialize error array
$err = [];
$adminManager = new AdminManager();

// Check if form is submitted
if(isset($_POST['submit'])) {
    // Validate and sanitize inputs
    $full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Validate inputs
    if ($nameError = $adminManager->validateFullName($full_name)) {
        $err['full_name'] = $nameError;
    }
    if ($usernameError = $adminManager->validateUsername($username)) {
        $err['username'] = $usernameError;
    }
    if ($passwordError = $adminManager->validatePassword($password)) {
        $err['password'] = $passwordError;
    }

    // Check if there are no errors
    if(empty($err)) {
        if ($adminManager->addAdmin($full_name, $username, $password)) {
            $_SESSION['add'] = "Admin Added Successfully";
            header("location:".SITEURL.'admin/manage-admin.php');
            exit;
        } else {
            $_SESSION['add'] = "Failed to add Admin";
            header("location:".SITEURL.'admin/add-admin.php');
            exit;
        }
    }
}
?>

<div class="main">
    <div class="wrapper">
        <h1>Add Admin</h1>
        <br>

        <?php
        if(isset($_SESSION['add'])) {
            echo $_SESSION['add'];
            unset($_SESSION['add']);
        }
        ?>

<form action="" method="POST" style="max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f7f7f7; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);">
    <div style="margin-bottom: 15px;">
        <label for="full_name" style="display: block; font-weight: bold; margin-bottom: 5px;">Full Name:</label>
        <input type="text" name="full_name" id="full_name" placeholder="Enter your name" value="<?php if(isset($full_name)) echo htmlspecialchars($full_name); ?>" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
        <span class="error" style="color: red; font-size: 0.9em;"><?php if(isset($err['full_name'])) echo $err['full_name']; ?></span>
    </div>

    <div style="margin-bottom: 15px;">
        <label for="username" style="display: block; font-weight: bold; margin-bottom: 5px;">Username:</label>
        <input type="text" name="username" id="username" placeholder="Your Username" value="<?php if(isset($username)) echo htmlspecialchars($username); ?>" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
        <span class="error" style="color: red; font-size: 0.9em;"><?php if(isset($err['username'])) echo $err['username']; ?></span>
    </div>

    <div style="margin-bottom: 15px;">
        <label for="password" style="display: block; font-weight: bold; margin-bottom: 5px;">Password:</label>
        <input type="password" name="password" id="password" placeholder="Your Password" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
        <span class="error" style="color: red; font-size: 0.9em;"><?php if(isset($err['password'])) echo $err['password']; ?></span>
    </div>

    <div style="text-align: center;">
        <input type="submit" name="submit" value="Add Admin" class="btn-secondary" style="width: 100%; padding: 12px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 1.1em;">
    </div>
</form>

    </div>
</div>

<?php include('partials/footer.php'); ?>
