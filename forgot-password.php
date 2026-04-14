<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
include('partials-front/menu.php');
require('config/constants.php');

class PasswordResetManager extends BaseManager {
    public function __construct($db = null) {
        parent::__construct($db);
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
        $reset_expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));
        
        $sql = "UPDATE tbl_users SET reset_token=?, reset_expiry=? WHERE email=?";
        $stmt = $this->db->prepare($sql);
        $this->db->bind($stmt, "sss", $reset_token, $reset_expiry, $email);
        
        if ($this->db->execute($stmt)) {
            return $reset_token;
        }
        return false;
    }
    
    public function sendResetEmail($email, $resetToken) {
        $resetLink = SITEURL . "reset-password.php?token=" . $resetToken;
        $subject = "Password Reset Request";
        $message = "Click the following link to reset your password: <a href='$resetLink'>$resetLink</a>";
        
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'ayushstha708@gmail.com';
            $mail->Password = 'tkey jikr jeuv fggx';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            
            $mail->setFrom('no-reply@yourdomain.com', 'Your Website');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $message;
            
            return $mail->send();
        } catch (Exception $e) {
            return false;
        }
    }
}

$err = [];
$success_message = "";
$manager = new PasswordResetManager();

if (isset($_POST['submit'])) {
    if (isset($_POST['email']) && !empty(trim($_POST['email']))) {
        $email = trim($_POST['email']);
        
        if ($manager->getUserByEmail($email)) {
            $resetToken = $manager->generateResetToken($email);
            
            if ($resetToken && $manager->sendResetEmail($email, $resetToken)) {
                $success_message = "A password reset link has been sent to your email.";
            } else {
                $err['email'] = "Failed to send reset link.";
            }
        } else {
            $err['email'] = "Email address not found.";
        }
    } else {
        $err['email'] = "Enter your email address.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="main">
        <div class="wrapper">
            <h1 class="text-center">Forgot Password</h1>
            <br><br>

            <?php 
            if(!empty($success_message)) {
                echo "<div class='success'>$success_message</div>";
            }
            ?>

            <form action="" method="POST" style="width: 40%; margin: 100px auto; padding: 30px; border: 2px solid #2196F3; border-radius: 10px; background-color: #ffffff; box-shadow: 0 2px 5px rgba(0,0,0,0.3);">
                <h2 style="text-align: center; color: #2196F3; margin-bottom: 20px;">Reset Your Password</h2>
                
                <div style="margin-bottom: 20px;">
                    <label for="email" style="display: inline-block; width: 100px; font-weight: bold;">Email:</label>
                    <input type="email" name="email" id="email" placeholder="Enter Email Address" style="width: calc(100% - 120px); padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
                    <br>
                    <span class="error" style="color: red; font-size: 0.9em;">
                        <?php if(isset($err['email'])) echo $err['email']; ?>
                    </span>
                </div>

                <div style="text-align: center;">
                    <input type="submit" name="submit" value="Send Reset Link" style="width: 100%; padding: 12px; background-color: #2196F3; color: white; border: none; border-radius: 5px; font-size: 16px; cursor: pointer;">
                </div>
            </form>

        </div>
    </div>

    <?php include('partials-front/footer.php'); ?>
</body>
</html>
