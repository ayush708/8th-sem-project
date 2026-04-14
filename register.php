<?php
include('config/constants.php');
include('partials-front/menu.php');

// User Registration Class
class UserRegistrationManager extends BaseManager {
    public function __construct($db = null) {
        parent::__construct($db);
    }
    
    public function validateFullName($fullName) {
        if(empty($fullName)) {
            return "Enter Full Name";
        } elseif(!preg_match("/^[A-Za-z\s]+$/", $fullName)) {
            return "Full Name must only contain letters and spaces";
        }
        return null;
    }
    
    public function validateUsername($username) {
        if(empty($username)) {
            return "Enter Username";
        } elseif(!preg_match("/^[a-zA-Z0-9]{4,29}$/", $username)) {
            return "Username must be alphanumeric and between 4 to 29 characters";
        }
        
        $sql = "SELECT * FROM tbl_users WHERE username=?";
        $stmt = $this->db->prepare($sql);
        $this->db->bind($stmt, "s", $username);
        $this->db->execute($stmt);
        $result = $this->db->getResult($stmt);
        
        if($this->db->numRows($result) > 0) {
            return "Username already exists";
        }
        return null;
    }
    
    public function validatePhone($phone) {
        if(empty($phone)) {
            return "Contact is required";
        } elseif(!preg_match("/^9[0-9]{9}$/", $phone)) {
            return "Invalid phone number, must start with 9 and be ten characters long";
        }
        
        $sql = "SELECT * FROM tbl_users WHERE phone=?";
        $stmt = $this->db->prepare($sql);
        $this->db->bind($stmt, "s", $phone);
        $this->db->execute($stmt);
        $result = $this->db->getResult($stmt);
        
        if($this->db->numRows($result) > 0) {
            return "Phone number already exists";
        }
        return null;
    }
    
    public function validateEmail($email) {
        if(empty($email)){
            return "Email is required";
        } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "Enter a valid email";
        }
        
        $sql = "SELECT * FROM tbl_users WHERE email=?";
        $stmt = $this->db->prepare($sql);
        $this->db->bind($stmt, "s", $email);
        $this->db->execute($stmt);
        $result = $this->db->getResult($stmt);
        
        if($this->db->numRows($result) > 0) {
            return "Email already exists";
        }
        return null;
    }
    
    public function validateAddress($address) {
        if(empty($address)){
            return "Address is required";
        }
        return null;
    }
    
    public function validatePassword($password) {
        if (empty($password)) {
            return "Password is required";
        } elseif (strlen($password) < 8) {
            return "Password must be at least 8 characters long";
        } elseif (!preg_match("/[A-Z]/", $password)) {
            return "Password must contain at least one uppercase letter";
        } elseif (!preg_match("/[a-z]/", $password)) {
            return "Password must contain at least one lowercase letter";
        } elseif (!preg_match("/[0-9]/", $password)) {
            return "Password must contain at least one number";
        } elseif (!preg_match("/[\W]/", $password)) {
            return "Password must contain at least one special character";
        }
        return null;
    }
    
    public function register($full_name, $username, $phone, $email, $address, $password) {
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO tbl_users (full_name, username, phone, email, address, password) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $this->db->bind($stmt, "ssssss", $full_name, $username, $phone, $email, $address, $password_hashed);
        
        return $this->db->execute($stmt);
    }
}

// Check login status
if (isset($_SESSION["user_logged_in"]) && $_SESSION["user_logged_in"] === true) {
    header("location: user-dashboard.php");
    exit();
}

// Initialize variables
$full_name = '';
$username = '';
$phone = '';
$email = '';
$address = '';
$err = [];

// Instantiate registration class
$userReg = new UserRegistrationManager();

// Check if form is submitted
if(isset($_POST['submit'])) {
    // Validate and sanitize inputs
    $full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    // Validate all fields
    if($error = $userReg->validateFullName($full_name)) $err['full_name'] = $error;
    if($error = $userReg->validateUsername($username)) $err['username'] = $error;
    if($error = $userReg->validatePhone($phone)) $err['phone'] = $error;
    if($error = $userReg->validateEmail($email)) $err['email'] = $error;
    if($error = $userReg->validateAddress($address)) $err['address'] = $error;
    if($error = $userReg->validatePassword($password)) $err['password'] = $error;

    // Check if there are no errors
    if(empty($err)) {
        if($userReg->register($full_name, $username, $phone, $email, $address, $password)) {
            $_SESSION['add'] = "Registration Successful";
            header("location:".SITEURL.'login.php');
            exit;
        } else {
            $_SESSION['add'] = "Failed to register";
            header("location:".SITEURL.'register.php');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <div class="main">
        <div class="wrapper">

            <?php
            if(isset($_SESSION['add'])) {
                echo $_SESSION['add'];
                unset($_SESSION['add']);
            }
            ?>

<form action="" method="POST" onsubmit="return validateForm()">
    <fieldset style="width: 50%; margin: 0 auto; padding: 20px; border: 2px solid #2196F3; border-radius: 10px; background-color: #f2f2f2;">
        <legend style="font-size: 1.5em; color: #2196F3; text-align: center;">Register User</legend>
        <table class="tbl-30" style="width: 100%; margin-top: 10px;">
            <tr>
                <td colspan="2">
                    <span class="error" style="color: red; font-size: 0.9em;"><?php if(isset($err['full_name'])) echo $err['full_name']; ?></span>
                </td>
            </tr>
            <tr>
                <td style="padding: 8px;">Full Name:</td>
                <td style="padding: 8px;">
                    <input type="text" name="full_name" placeholder="Enter your name" value="<?php if(isset($full_name)) echo $full_name; ?>" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="error" style="color: red; font-size: 0.9em;"><?php if(isset($err['username'])) echo $err['username']; ?></span>
                </td>
            </tr>
            <tr>
                <td style="padding: 8px;">Username:</td>
                <td style="padding: 8px;">
                    <input type="text" name="username" placeholder="Your Username" value="<?php if(isset($username)) echo $username; ?>" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="error" style="color: red; font-size: 0.9em;"><?php if(isset($err['phone'])) echo $err['phone']; ?></span>
                </td>
            </tr>
            <tr>
                <td style="padding: 8px;">Phone:</td>
                <td style="padding: 8px;">
                    <input type="text" name="phone" placeholder="Your Phone" value="<?php if(isset($phone)) echo $phone; ?>" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="error" style="color: red; font-size: 0.9em;"><?php if(isset($err['email'])) echo $err['email']; ?></span>
                </td>
            </tr>
            <tr>
                <td style="padding: 8px;">Email:</td>
                <td style="padding: 8px;">
                    <input type="text" name="email" placeholder="Your Email" value="<?php if(isset($email)) echo $email; ?>" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="error" style="color: red; font-size: 0.9em;"><?php if(isset($err['address'])) echo $err['address']; ?></span>
                </td>
            </tr>
            <tr>
                <td style="padding: 8px;">Address:</td>
                <td style="padding: 8px;">
                    <input type="text" name="address" placeholder="Your Address" value="<?php if(isset($address)) echo $address; ?>" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="error" style="color: red; font-size: 0.9em;"><?php if(isset($err['password'])) echo $err['password']; ?></span>
                </td>
            </tr>
            <tr>
                <td style="padding: 8px;">Password:</td>
                <td style="padding: 8px;">
                    <input type="password" name="password" placeholder="Your Password" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
                </td>
            </tr>
            <tr>
                <td colspan="2" style="padding-top: 10px;">
                    <input type="submit" name="submit" value="Register" class="btn-secondary" style="width: 100%; padding: 10px; background-color: #2196F3; color: white; border: none; border-radius: 5px; cursor: pointer;">
                </td>
            </tr>
        </table>
    </fieldset>
</form>

<script>
    function validateForm() {
        let fullName = document.forms[0]["full_name"].value;
        let username = document.forms[0]["username"].value;
        let phone = document.forms[0]["phone"].value;
        let email = document.forms[0]["email"].value;
        let address = document.forms[0]["address"].value;
        let password = document.forms[0]["password"].value;
        
        if (fullName == "" || username == "" || phone == "" || email == "" || address == "" || password == "") {
            alert("All fields must be filled out");
            return false;
        }
        return true;
    }
</script>

        </div>
    </div>

    <?php include('partials-front/footer.php'); ?>
</body>
</html>
