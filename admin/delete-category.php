<?php
include('../config/constants.php');

class CategoryDeleteManager extends BaseManager {
    public function __construct($db = null) {
        parent::__construct($db);
    }
    
    public function deleteImageFile($imageName) {
        if ($imageName !== "") {
            $path = "../images/category/" . $imageName;
            if (file_exists($path)) {
                return unlink($path);
            }
        }
        return true;
    }
    
    public function deleteCategoryFromDatabase($id) {
        $sql = "DELETE FROM tbl_category WHERE id=?";
        $stmt = $this->db->prepare($sql);
        $this->db->bind($stmt, "i", $id);
        return $this->db->execute($stmt);
    }
}

if (isset($_GET['id']) && isset($_GET['image_name'])) {
    $id = $_GET['id'];
    $imageName = $_GET['image_name'];
    
    $categoryDeleteManager = new CategoryDeleteManager();
    
    // Remove physical image file if available
    if (!$categoryDeleteManager->deleteImageFile($imageName)) {
        $_SESSION['remove'] = "Failed to remove category image";
        header('location:'.SITEURL.'admin/category.php');
        die();
    }
    
    // Delete data from database
    if ($categoryDeleteManager->deleteCategoryFromDatabase($id)) {
        $_SESSION['delete'] = "Category deleted successfully";
    } else {
        $_SESSION['delete'] = "Failed to delete Category";
    }
    
    header('location:'.SITEURL.'admin/category.php');
} else {
    header('location:'.SITEURL.'admin/category.php');
}