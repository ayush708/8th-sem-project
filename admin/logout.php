<?php
include('../config/constants.php');

class SessionManager {
    public static function logout() {
        session_destroy();
    }
}

SessionManager::logout();
header('location:'.SITEURL.'admin/login.php');