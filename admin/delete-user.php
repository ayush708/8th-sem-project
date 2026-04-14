<?php

include('../config/constants.php');

class UserDeleteManager extends BaseManager {
    public function __construct($db = null) {
        parent::__construct($db);
    }
    
    public function deleteByUsername($username) {
        $sql = "DELETE FROM tbl_users WHERE username = ?";
        $stmt = $this->db->prepare($sql);
        $this->db->bind($stmt, "s", $username);
        return $this->db->execute($stmt);
    }
}

if (isset($_GET['username']) && !empty($_GET['username'])) {
    $username = trim($_GET['username']);
    
    if (!empty($username)) {
        $userDeleteManager = new UserDeleteManager();
        
        if ($userDeleteManager->deleteByUsername($username)) {
            $_SESSION['delete'] = "User Deleted Successfully";
            header('location:'.SITEURL.'admin/manage-user.php');
            exit;
        } else {
            $_SESSION['delete'] = "Failed to Delete User. Try Again";
            header('location:'.SITEURL.'admin/manage-user.php');
            exit;
        }
    } else {
        $_SESSION['delete'] = "Invalid Username";
        header('location:'.SITEURL.'admin/manage-user.php');
        exit;
    }
} else {
    $_SESSION['delete'] = "Username Missing";
    header('location:'.SITEURL.'admin/manage-user.php');
    exit;
}
