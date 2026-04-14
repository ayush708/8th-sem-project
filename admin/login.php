<?php
include('../config/constants.php');

class AdminLoginManager extends BaseManager {
    public function __construct($db = null) {
        parent::__construct($db);
    }
    
    public function authenticate($username, $password) {
        $sql = "SELECT * FROM tbl_admin WHERE username=?";
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
    
    public function setSession($admin) {
        $_SESSION['login'] = "Login Successful";
        $_SESSION['user'] = $admin['username'];
        $_SESSION['admin_id'] = $admin['admin_id'] ?? $admin['id'];
    }
}

$err = [];
$adminLoginManager = new AdminLoginManager();
$message = '';

if (isset($_POST['submit'])) {
    if (isset($_POST['username']) && !empty(trim($_POST['username']))) {
        $username = trim($_POST['username']);
    } else {
        $err['username'] = "Enter username";
    }

    if (isset($_POST['password']) && !empty($_POST['password'])) {
        $password = $_POST['password'];
    } else {
        $err['password'] = "Enter password";
    }

    if (empty($err)) {
        $admin = $adminLoginManager->authenticate($username, $password);
        if ($admin) {
            $adminLoginManager->setSession($admin);
            header('Location: ' . SITEURL . 'admin/');
            exit;
        } else {
            $message = "<span class='error'>Incorrect Username or Password</span>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="../css/admin.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f2f2f2;
        }
        
        .login-container {
            background-color: #fff;
            border: 2px solid #2196F3;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 100%;
        }

        .login-container h1 {
            text-align: center;
            color: #2196F3;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        .form-group .error {
            color: red;
            font-size: 0.9em;
            margin-top: 5px;
        }

        .login-form {
            display: flex;
            flex-direction: column;
        }

        .login-form input[type="submit"] {
            background-color: #2196F3;
            color: #fff;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s;
        }

        .login-form input[type="submit"]:hover {
            background-color: #1976D2;
        }

        @media (max-width: 600px) {
            .login-container {
                padding: 15px;
            }

            .form-group input {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Admin Login</h1>
        <!-- Error message display -->
        <?php 
        if (isset($_SESSION['login'])) {
            echo $_SESSION['login'];
            unset($_SESSION['login']);
        }
        ?>

        <!-- Login form starts here -->
        <form action="" method="POST" onsubmit="return validateLoginForm()" class="login-form">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" placeholder="Enter Username">
                <span class="error"><?php if (isset($err['username'])) echo $err['username']; ?></span>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" placeholder="Enter Password">
                <span class="error"><?php if (isset($err['password'])) echo $err['password']; ?></span>
            </div>

            <input type="submit" name="submit" value="Login">
        </form>

        <script>
            function validateLoginForm() {
                let username = document.forms[0]["username"].value;
                let password = document.forms[0]["password"].value;
                
                if (username == "" || password == "") {
                    alert("Both Username and Password must be filled out");
                    return false;
                }
                return true;
            }
        </script>
    </div>
</body>
</html>
