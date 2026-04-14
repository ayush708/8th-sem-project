<?php 
include('partials/menu.php'); 

class CategoryUpdateManager extends BaseManager {
    public function __construct($db = null) {
        parent::__construct($db);
    }
    
    public function getCategoryById($id) {
        $sql = "SELECT * FROM tbl_category WHERE id=?";
        $stmt = $this->db->prepare($sql);
        $this->db->bind($stmt, "i", $id);
        $this->db->execute($stmt);
        $res = $this->db->getResult($stmt);
        
        if ($this->db->numRows($res) == 1) {
            return $this->db->fetchAssoc($res);
        }
        return null;
    }
    
    public function validateTitle($title) {
        if (empty($title)) {
            return "Title is required";
        }
        return null;
    }
    
    public function validateImageUpload($fileSize, $fileType) {
        $max_size = 5 * 1024 * 1024; // 5 MB
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        
        if ($fileSize > $max_size) {
            return "File size exceeds limit (5MB)";
        }
        if (!in_array($fileType, $allowed_types)) {
            return "Only JPEG, PNG, and GIF files are allowed";
        }
        return null;
    }
    
    public function checkDuplicateTitle($title, $id) {
        $sql = "SELECT * FROM tbl_category WHERE title=? AND id!=?";
        $stmt = $this->db->prepare($sql);
        $this->db->bind($stmt, "si", $title, $id);
        $this->db->execute($stmt);
        $res = $this->db->getResult($stmt);
        
        return $this->db->numRows($res) > 0;
    }
    
    public function uploadCategoryImage($imageName, $imageTmp) {
        $ext = pathinfo($imageName, PATHINFO_EXTENSION);
        $newImageName = "Category_" . rand(000, 999) . '.' . $ext;
        $uploadDir = "../images/category/";
        $uploadPath = $uploadDir . $newImageName;
        
        if (move_uploaded_file($imageTmp, $uploadPath)) {
            return $newImageName;
        }
        return false;
    }
    
    public function deleteCategoryImage($imageName) {
        if ($imageName != "") {
            $path = "../images/category/" . $imageName;
            if (file_exists($path)) {
                return unlink($path);
            }
        }
        return true;
    }
    
    public function updateCategory($id, $title, $imageName, $featured, $active) {
        $sql = "UPDATE tbl_category SET title=?, image_name=?, featured=?, active=? WHERE id=?";
        $stmt = $this->db->prepare($sql);
        $this->db->bind($stmt, "ssssi", $title, $imageName, $featured, $active, $id);
        return $this->db->execute($stmt);
    }
}

// Initialize error array and updater
$errors = [];
$categoryUpdateManager = new CategoryUpdateManager();
$id = isset($_GET['id']) ? $_GET['id'] : null;
$title = $current_image = $featured = $active = '';

// Fetch category on GET request
if ($id && $_SERVER['REQUEST_METHOD'] == 'GET') {
    $category = $categoryUpdateManager->getCategoryById($id);
    if (!$category) {
        $_SESSION['no-category-found'] = "Category not found";
        header('location:'.SITEURL.'admin/category.php');
        exit;
    }
    $title = $category['title'];
    $current_image = $category['image_name'];
    $featured = $category['featured'];
    $active = $category['active'];
}

// Process form submission
if(isset($_POST['submit'])) {
    $id = $_POST['id'] ?? null;
    $title = $_POST['title'] ?? '';
    $featured = $_POST['featured'] ?? "No";
    $active = $_POST['active'] ?? "No";
    
    // Validate title
    if ($titleError = $categoryUpdateManager->validateTitle($title)) {
        $errors['title'] = $titleError;
    }
    
    // Check for existing image
    $image_name = $_FILES['image']['name'] ?? '';
    $image_tmp = $_FILES['image']['tmp_name'] ?? '';
    
    // Validate image if provided
    if (!empty($image_name)) {
        if ($imageError = $categoryUpdateManager->validateImageUpload($_FILES['image']['size'], $_FILES['image']['type'])) {
            $errors['image'] = $imageError;
        }
    }
    
    // If no validation errors, proceed
    if (empty($errors)) {
        // Get current category data
        $category = $categoryUpdateManager->getCategoryById($id);
        if (!$category) {
            $_SESSION['no-category-found'] = "Category not found";
            header('location:'.SITEURL.'admin/category.php');
            exit;
        }
        
        $current_image = $category['image_name'];
        
        // Check for duplicate title
        if ($categoryUpdateManager->checkDuplicateTitle($title, $id)) {
            $errors['title'] = "Category name already exists";
        }
        
        if (empty($errors)) {
            // Handle image upload
            if (!empty($image_name)) {
                $newImageName = $categoryUpdateManager->uploadCategoryImage($image_name, $image_tmp);
                if (!$newImageName) {
                    $_SESSION['upload'] = "Failed to Upload Image";
                    header('location:'.SITEURL.'admin/update-category.php?id='.$id);
                    exit;
                }
                // Delete old image
                $categoryUpdateManager->deleteCategoryImage($current_image);
                $image_name = $newImageName;
            } else {
                $image_name = $current_image;
            }
            
            // Update category
            if ($categoryUpdateManager->updateCategory($id, $title, $image_name, $featured, $active)) {
                $_SESSION['update'] = "Category Updated Successfully";
                header('location:'.SITEURL.'admin/category.php');
                exit;
            } else {
                $_SESSION['update'] = "Failed to Update Category";
                header('location:'.SITEURL.'admin/category.php');
                exit;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Category</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .container h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        .tbl-30 {
            width: 100%;
            border-spacing: 10px;
            margin-bottom: 20px;
        }

        .tbl-30 td {
            padding: 10px;
            vertical-align: top;
        }

        .tbl-30 input[type="text"], 
        .tbl-30 input[type="file"], 
        .tbl-30 input[type="radio"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .tbl-30 input[type="radio"] {
            width: auto;
            margin-right: 5px;
        }

        .tbl-30 img {
            border-radius: 5px;
            margin-top: 10px;
        }

        .tbl-30 .btn-secondary {
            display: block;
            width: 100%;
            padding: 12px;
            background-color: #2196F3;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
            text-align: center;
        }

        .tbl-30 .btn-secondary:hover {
            background-color: #1976D2;
        }

        .error p {
            color: red;
            margin: 0;
        }

        @media (max-width: 600px) {
            .tbl-30 td {
                display: block;
                width: 100%;
                box-sizing: border-box;
            }

            .tbl-30 td input[type="radio"] {
                margin-right: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Update Category</h1>

        <!-- Display validation errors -->
        <?php if(!empty($errors)): ?>
            <div class="error">
                <?php foreach($errors as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data">
            <table class="tbl-30">
                <tr>
                    <td>Title:</td>
                    <td>
                        <input type="text" name="title" value="<?php echo isset($title) ? htmlspecialchars($title) : ''; ?>">
                    </td>
                </tr>

                <tr>
                    <td>Current Image:</td>
                    <td>
                        <?php if ($current_image != ""): ?>
                            <img src="<?php echo SITEURL; ?>images/category/<?php echo htmlspecialchars($current_image); ?>" width="150px">
                        <?php else: ?>
                            <p style="color: #888;">Image not added</p>
                        <?php endif; ?>
                    </td>
                </tr>

                <tr>
                    <td>New Image:</td>
                    <td>
                        <input type="file" name="image"> 
                    </td>
                </tr>

                <tr>
                    <td>Featured:</td>
                    <td>
                        <input type="radio" name="featured" value="Yes" <?php if (isset($featured) && $featured == "Yes") { echo "checked"; } ?>> Yes
                        <input type="radio" name="featured" value="No" <?php if (isset($featured) && $featured == "No") { echo "checked"; } ?>> No
                    </td>
                </tr>

                <tr>
                    <td>Active:</td>
                    <td>
                        <input type="radio" name="active" value="Yes" <?php if (isset($active) && $active == "Yes") { echo "checked"; } ?>> Yes
                        <input type="radio" name="active" value="No" <?php if (isset($active) && $active == "No") { echo "checked"; } ?>> No
                    </td>
                </tr>

                <tr>
                    <td colspan="2">
                        <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($current_image); ?>">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
                        <input type="submit" name="submit" value="Update Category" class="btn-secondary">
                    </td>
                </tr>
            </table>
        </form>
    </div>
</body>
</html>

<?php include('partials/footer.php'); ?>
