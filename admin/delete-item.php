<?php
include('../config/constants.php');
session_start();

class ItemDeleteManager extends BaseManager {
    public function __construct($db = null) {
        parent::__construct($db);
    }
    
    public function deleteItemImage($imageName) {
        if ($imageName != "") {
            $path = "../images/item/" . $imageName;
            if (file_exists($path)) {
                return unlink($path);
            }
        }
        return true;
    }
    
    public function deleteItemFromDatabase($id) {
        $sql = "DELETE FROM tbl_items WHERE id=?";
        $stmt = $this->db->prepare($sql);
        $this->db->bind($stmt, "i", $id);
        return $this->db->execute($stmt);
    }
}

if (isset($_GET['id']) && isset($_GET['image_name'])) {
    $id = $_GET['id'];
    $imageName = $_GET['image_name'];
    
    $itemDeleteManager = new ItemDeleteManager();
    
    // Remove image file from folder
    if (!$itemDeleteManager->deleteItemImage($imageName)) {
        $_SESSION['upload'] = "<div class='error'>Failed to remove Image file</div>";
        header('location:' . SITEURL . 'admin/add-item.php');
        die();
    }
    
    // Delete item from database
    if ($itemDeleteManager->deleteItemFromDatabase($id)) {
        $_SESSION['delete'] = "<div class='success'>Item Deleted Successfully.</div>";
    } else {
        $_SESSION['delete'] = "<div class='error'>Failed to Delete Item.</div>";
    }
    
    header('location:' . SITEURL . 'admin/item.php');
} else {
    $_SESSION['unauthorize'] = "<div class='error'>Unauthorized Access</div>";
    header('location:' . SITEURL . 'admin/item.php');
}
?>
