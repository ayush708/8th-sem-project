<?php 
include('partials/menu.php');

class CategoryManager extends BaseManager {
    public function __construct($db = null) {
        parent::__construct($db);
    }
    
    public function validateTitle($title) {
        if (empty(trim($title))) {
            return "Enter title";
        }
        
        $sql = "SELECT * FROM tbl_category WHERE title = ?";
        $stmt = $this->db->prepare($sql);
        $this->db->bind($stmt, "s", $title);
        $this->db->execute($stmt);
        $res = $this->db->getResult($stmt);
        
        if ($this->db->numRows($res) > 0) {
            return "Category title already exists";
        }
        
        return null;
    }
    
    public function uploadImage($imageFile) {
        if (isset($imageFile['name']) && !empty($imageFile['name'])) {
            $imageName = $imageFile['name'];
            $imageTmp = $imageFile['tmp_name'];
            $imgExt = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));
            $imageName = "Category_" . rand(1000, 9999) . '.' . $imgExt;
            
            $uploadDir = "../images/category/";
            $uploadPath = $uploadDir . $imageName;
            
            if (move_uploaded_file($imageTmp, $uploadPath)) {
                return $imageName;
            }
        }
        return null;
    }
    
    public function addCategory($title, $imageName, $featured, $active) {
        $sql = "INSERT INTO tbl_category (title, image_name, featured, active) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $this->db->bind($stmt, "ssss", $title, $imageName, $featured, $active);
        return $this->db->execute($stmt);
    }
}

// Check if form is submitted
if (isset($_POST['submit'])) {
    $categoryManager = new CategoryManager();
    $errors = [];
    
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $featured = isset($_POST['featured']) ? $_POST['featured'] : "No";
    $active = isset($_POST['active']) ? $_POST['active'] : "No";
    
    // Validate title
    if ($error = $categoryManager->validateTitle($title)) {
        $errors[] = $error;
    }
    
    // Upload image
    $imageName = $categoryManager->uploadImage($_FILES['image']);
    if (!$imageName) {
        $_SESSION['upload'] = "Failed to Upload Image";
        header('location:'.SITEURL.'admin/add-category.php');
        exit;
    }
    
    // If no errors, add category
    if (empty($errors)) {
        if ($categoryManager->addCategory($title, $imageName, $featured, $active)) {
            $_SESSION['add'] = "Category Added Successfully";
            header('location:'.SITEURL.'admin/category.php');
            exit;
        } else {
            $_SESSION['add'] = "Failed to Add Category";
            header('location:'.SITEURL.'admin/add-category.php');
            exit;
        }
    }
}

?>

<div class="main">
    <div class="wrapper">
        
        <br><br>

        <?php
            if(isset($_SESSION['add'])) {
                echo $_SESSION['add'];
                unset($_SESSION['add']);
            }
            
            if(isset($_SESSION['upload'])) {
                echo $_SESSION['upload'];
                unset($_SESSION['upload']);
            }
        ?>

        <br><br>

        <!-- Add category form starts -->
        <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Category</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .error {
            color: red;
            font-size: 0.875rem;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="max-w-3xl mx-auto p-6 bg-white rounded-lg shadow-lg mt-10">
        <h1 class="text-2xl font-semibold mb-6">Add Category</h1>
        
        <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
            <div>
                <label for="title" class="block text-gray-700 font-medium mb-2">Title:</label>
                <input type="text" id="title" name="title" placeholder="Category Title" class="w-full p-3 border border-gray-300 rounded-lg" value="<?php echo isset($title) ? htmlspecialchars($title) : ''; ?>">
                <span class="error"><?php if(isset($err_title)) echo htmlspecialchars($err_title); ?></span>
            </div>

            <div>
                <label for="image" class="block text-gray-700 font-medium mb-2">Select Image:</label>
                <input type="file" id="image" name="image" class="w-full p-3 border border-gray-300 rounded-lg">
            </div>

            <div>
                <span class="block text-gray-700 font-medium mb-2">Featured:</span>
                <div class="flex items-center space-x-4">
                    <label class="inline-flex items-center">
                        <input type="radio" name="featured" value="Yes" class="form-radio" <?php if(isset($featured) && $featured=="Yes"){echo "checked";} ?>>
                        <span class="ml-2">Yes</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="featured" value="No" class="form-radio" <?php if(isset($featured) && $featured=="No"){echo "checked";} ?>>
                        <span class="ml-2">No</span>
                    </label>
                </div>
            </div>

            <div>
                <span class="block text-gray-700 font-medium mb-2">Active:</span>
                <div class="flex items-center space-x-4">
                    <label class="inline-flex items-center">
                        <input type="radio" name="active" value="Yes" class="form-radio" <?php if(isset($active) && $active=="Yes"){echo "checked";} ?>>
                        <span class="ml-2">Yes</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="active" value="No" class="form-radio" <?php if(isset($active) && $active=="No"){echo "checked";} ?>>
                        <span class="ml-2">No</span>
                    </label>
                </div>
            </div>

            <div>
                <input type="submit" name="submit" value="Add Category" class="w-full py-3 px-4 bg-blue-500 text-white rounded-lg cursor-pointer hover:bg-blue-600">
            </div>
        </form>
    </div>
</body>
</html>

        <!-- Add category form ends -->
    </div>
</div>

<?php include('partials/footer.php') ?>
