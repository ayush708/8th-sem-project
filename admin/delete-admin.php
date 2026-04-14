<?php

include('../config/constants.php');

class AdminDeleteManager extends BaseManager {
    public function __construct($db = null) {
        parent::__construct($db);
    }
    
    public function deleteById($id) {
        $sql = "DELETE FROM tbl_admin WHERE id=?";
        $stmt = $this->db->prepare($sql);
        $this->db->bind($stmt, "i", $id);
        return $this->db->execute($stmt);
    }
}

$id = isset($_GET['id']) ? $_GET['id'] : null;

if ($id) {
    $adminDeleteManager = new AdminDeleteManager();
    
    if ($adminDeleteManager->deleteById($id)) {
        $_SESSION['delete'] = "Admin Deleted Successfully";
        header('location:'.SITEURL.'admin/manage-admin.php');
    } else {
        $_SESSION['delete'] = "Failed to Delete Admin. Try Again";
        header('location:'.SITEURL.'admin/manage-admin.php');
    }
} else {
    $_SESSION['delete'] = "Invalid Admin ID";
    header('location:'.SITEURL.'admin/manage-admin.php');
}