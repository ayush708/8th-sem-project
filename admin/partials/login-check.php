<?php
class AdminLoginGuard {
    public function enforce() {
        if (!isset($_SESSION['user'])) {
            $_SESSION['no-login-msg'] = "<div class='text-center'>Please login to access admin pannel</div>";
            header('location:' . SITEURL . 'admin/login.php');
            exit();
        }
    }
}

$adminLoginGuard = new AdminLoginGuard();
$adminLoginGuard->enforce();
?>