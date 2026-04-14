<?php
include('partials-front/menu.php');
include('config/constants.php');

class PasswordResetRequester extends BaseManager {
    
    public function __construct($db = null) {
        parent::__construct($db);
    }

    public function sanitize($value) {
        return filter_var(parent::sanitize($value), FILTER_SANITIZE_EMAIL);
    }
    
    public function getUserByEmail($email) {
        $sql = "SELECT * FROM tbl_users WHERE email=?";
        $stmt = $this->db->prepare($sql);
        $this->db->bind($stmt, "s", $email);
        $this->db->execute($stmt);
        $res = $this->db->getResult($stmt);
        
        return $this->db->numRows($res) > 0 ? $this->db->fetchAssoc($res) : null;
    }
    
    public function generateResetToken($email) {
        $reset_token = bin2hex(random_bytes(32));
        $reset_expiry = date('Y-m-d H:i:s', strtotime('+1 minute'));
        
        $sql = "UPDATE tbl_users SET reset_token=?, reset_expiry=? WHERE email=?";
        $stmt = $this->db->prepare($sql);
        $this->db->bind($stmt, "sss", $reset_token, $reset_expiry, $email);
        
        return $this->db->execute($stmt) ? $reset_token : false;
    }
    
    public function sendResetEmail($email, $resetToken) {
        $reset_link = "http://localhost/OPS/reset-password.php?token=" . $resetToken;
        $subject = "Password Reset Request";
        $message = "Please click on the following link to reset your password (link expires in 1 minute): " . $reset_link;
        $headers = "From: no-reply@example.com\r\n";
        
        return mail($email, $subject, $message, $headers);
    }
}

$err = [];
$success_message = "";
$requester = new PasswordResetRequester();

if (isset($_POST['submit'])) {
    $email = isset($_POST['email']) ? $requester->sanitize($_POST['email']) : '';

    if (empty($email)) {
        $err['email'] = "Email address is required.";
    } else {
        $user = $requester->getUserByEmail($email);
        
        if ($user) {
            $resetToken = $requester->generateResetToken($email);
            if ($resetToken && $requester->sendResetEmail($email, $resetToken)) {
                $success_message = "A password reset link has been sent to your email address.";
            } else {
                $err['email'] = "Failed to send reset email.";
            }
        } else {
            $err['email'] = "No account found with that email address.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Password Reset</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="main">
        <div class="wrapper">
            <h1 class="text-center">Request Password Reset</h1>
            <br><br>

            <?php 
            if (!empty($success_message)) {
                echo "<div class='success'>$success_message</div>";
            }
            ?>
            <?php 
            if (isset($err['email'])) {
                echo "<div class='error'>{$err['email']}</div>";
            }
            ?>

            <form action="" method="POST">
                <label for="email">Email Address:</label>
                <input type="email" name="email" id="email" placeholder="Enter your email address" required>
                <input type="submit" name="submit" value="Send Reset Link">
            </form>
        </div>
    </div>
</body>
</html>
