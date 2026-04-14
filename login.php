<?php
include('config/constants.php');
include('partials-front/menu.php');

class UserAuthManager extends BaseManager {
    public function __construct($db = null) {
        parent::__construct($db);
    }
    
    public function authenticate($username, $password) {
        $sql = "SELECT * FROM tbl_users WHERE username=?";
        $stmt = $this->db->prepare($sql);
        $this->db->bind($stmt, "s", $username);
        $this->db->execute($stmt);
        $res = $this->db->getResult($stmt);
        
        if ($res && $this->db->numRows($res) > 0) {
            $row = $this->db->fetchAssoc($res);
            if (password_verify($password, $row['password'])) {
                return $row;
            }
        }
        return false;
    }
    
    public function setSessionData($user) {
        $_SESSION['login'] = "Login Successful";
        $_SESSION['user'] = $user['username'];
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['user_logged_in'] = true;
    }
}

// Check login status
if (isset($_SESSION["user_logged_in"]) && $_SESSION["user_logged_in"] === true) {
    header("location: user-dashboard.php");
    exit();
}

// Initialize error array
$err = [];
$user = new UserAuthManager();
$message = '';

// Check whether the submit button is clicked or not
if(isset($_POST['submit'])) {
    // Get data from login form and check if empty
    if(isset($_POST['username']) && !empty(trim($_POST['username']))) {
        $username = trim($_POST['username']);
    } else {
        $err['username'] = "Enter username";
    }

    if(isset($_POST['password']) && !empty($_POST['password'])) {
        $password = $_POST['password'];
    } else {
        $err['password'] = "Enter password";
    }

    if(empty($err)) {
        $authUser = $user->authenticate($username, $password);
        if ($authUser) {
            $user->setSessionData($authUser);
            header('Location: ' . SITEURL . 'user-dashboard.php');
            exit;
        } else {
            $message = "<span class='error text-center'>Username or Password didn't Match</span>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="main">
        <div class="wrapper">
            <h1 class="text-center">Login</h1>
            <br><br>

            <!-- Error message display -->
            <?php 
            if(isset($_SESSION['login'])) {
                echo $_SESSION['login'];
                unset($_SESSION['login']);
            }
            ?>
            <br><br>

            <!-- Login form starts here -->
            <form action="" method="POST" onsubmit="return validateLoginForm()" style="width: 40%; margin: 100px auto; padding: 30px; border: 2px solid #2196F3; border-radius: 10px; background-color: #ffffff; box-shadow: 0 2px 5px rgba(0,0,0,0.3);">
                <h2 style="text-align: center; color: #2196F3; margin-bottom: 20px;">User Login</h2>
                
                <div style="margin-bottom: 15px;">
                    <label for="username" style="display: inline-block; width: 100px; font-weight: bold;">Username:</label>
                    <input type="text" name="username" id="username" placeholder="Enter Username" value="<?php if(isset($username)) echo $username; ?>" style="width: calc(100% - 120px); padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
                    <br>
                    <span class="error" style="color: red; font-size: 0.9em;">
                        <?php if(isset($err['username'])) echo $err['username']; ?>
                    </span>
                </div>

                <div style="margin-bottom: 20px;">
                    <label for="password" style="display: inline-block; width: 100px; font-weight: bold;">Password:</label>
                    <input type="password" name="password" id="password" placeholder="Enter Password" style="width: calc(100% - 120px); padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
                    <br>
                    <span class="error" style="color: red; font-size: 0.9em;">
                        <?php if(isset($err['password'])) echo $err['password']; ?>
                    </span>
                </div>

                <div style="text-align: center; margin-bottom: 15px;">
                    <input type="submit" name="submit" value="Login" style="width: 100%; padding: 12px; background-color: #2196F3; color: white; border: none; border-radius: 5px; font-size: 16px; cursor: pointer;">
                </div>

                <div style="text-align: center;">
                    <span style="font-size: 0.9em;">Don't have an account? 
                        <a href="register.php" style="color: #2196F3; text-decoration: none; font-weight: bold;">Sign Up</a>
                    </span>
                    <br><br>
                    <span style="font-size: 0.9em;">
                        <a href="forgot-password.php" style="color: #2196F3; text-decoration: none; font-weight: bold;">Forgot Password?</a>
                    </span>
                </div>
            </form>

            <!-- Login form ends here -->
        </div>
    </div>

    <?php include('partials-front/footer.php'); ?>
</body>
</html>
