<?php 
include('partials/menu.php');

class ProductManager extends BaseManager {
    public function __construct($db = null) {
        parent::__construct($db);
    }
    
    public function validateInput($data) {
        $errors = [];
        
        if (empty($data['title'])) {
            $errors['title'] = 'Title is required';
        }
        
        if (empty($data['description'])) {
            $errors['description'] = 'Description is required';
        }
        
        if (empty($data['price'])) {
            $errors['price'] = 'Price is required';
        } elseif (!preg_match('/^\d+(\.\d{1,2})?$/', $data['price'])) {
            $errors['price'] = 'Invalid price format';
        }
        
        if (empty($data['quantity']) || !is_numeric($data['quantity']) || $data['quantity'] <= 0) {
            $errors['quantity'] = 'Please enter a valid quantity';
        }
        
        return $errors;
    }
    
    public function validateImage($imageFile) {
        if (!isset($imageFile['name']) || empty($imageFile['name'])) {
            return 'Please choose an image';
        }
        return null;
    }
    
    public function uploadImage($imageFile) {
        if (isset($imageFile['name']) && !empty($imageFile['name'])) {
            $imageName = $imageFile['name'];
            $imageTmp = $imageFile['tmp_name'];
            $ext = pathinfo($imageName, PATHINFO_EXTENSION);
            $imageName = 'item-name-' . rand(0000, 9999) . '.' . $ext;
            $uploadDir = "../images/item/";
            $uploadPath = $uploadDir . $imageName;
            
            if (move_uploaded_file($imageTmp, $uploadPath)) {
                return $imageName;
            }
        }
        return null;
    }
    
    public function addProduct($title, $description, $price, $imageName, $categoryId, $featured, $active, $quantity) {
        $sql = "INSERT INTO tbl_items (title, description, price, image_name, category_id, featured, active, quantity) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $this->db->bind($stmt, "ssssissi", $title, $description, $price, $imageName, $categoryId, $featured, $active, $quantity);
        return $this->db->execute($stmt);
    }
    
    public function getAllCategories() {
        $sql = "SELECT id, title FROM tbl_category WHERE active='Yes'";
        $res = $this->db->query($sql);
        return $res ? $this->db->fetchAll($res) : [];
    }
}

$productManager = new ProductManager();
$errors = [];

// Check if form is submitted
if (isset($_POST['submit'])) {
    $formData = [
        'title' => isset($_POST['title']) ? $_POST['title'] : '',
        'description' => isset($_POST['description']) ? $_POST['description'] : '',
        'price' => isset($_POST['price']) ? $_POST['price'] : '',
        'quantity' => isset($_POST['quantity']) ? $_POST['quantity'] : '',
    ];
    
    $errors = $productManager->validateInput($formData);
    $imageError = $productManager->validateImage($_FILES['image']);
    
    if ($imageError) {
        $errors['image'] = $imageError;
    }
    
    if (empty($errors)) {
        $imageName = $productManager->uploadImage($_FILES['image']);
        
        if ($imageName) {
            $category = isset($_POST['category']) ? $_POST['category'] : '';
            $featured = isset($_POST['featured']) ? $_POST['featured'] : 'No';
            $active = isset($_POST['active']) ? $_POST['active'] : 'No';
            
            if ($productManager->addProduct($formData['title'], $formData['description'], $formData['price'], $imageName, $category, $featured, $active, $formData['quantity'])) {
                $_SESSION['add'] = '<div class="success">Item Added Successfully</div>';
                header('location:'.SITEURL.'admin/item.php');
                exit;
            } else {
                $_SESSION['add'] = '<div class="error">Failed to add Item</div>';
                header('location:'.SITEURL.'admin/item.php');
                exit;
            }
        } else {
            $_SESSION['upload'] = '<div class="error">Failed to upload image</div>';
            header('location:'.SITEURL.'admin/add-item.php');
            exit;
        }
    }
}

?>

<div class="main-content">
    <div class="wrapper">
        <h1>Add Item</h1>

        <br><br>

        <?php
            // Display upload error message if any
            if(isset($_SESSION['upload'])) {
                echo $_SESSION['upload'];
                unset($_SESSION['upload']);
            }
        ?>

        <!-- Display validation errors -->
        <?php if(!empty($errors)): ?>
            <div class="error">
                <?php foreach($errors as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Item</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 900px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            padding: 10px;
            vertical-align: top;
        }

        td:first-child {
            text-align: right;
            font-weight: bold;
        }

        input[type="text"], 
        input[type="number"], 
        textarea, 
        select, 
        input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
            margin-top: 5px;
        }

        textarea {
            resize: vertical;
        }

        .btn-secondary {
            display: block;
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
            text-align: center;
        }

        .btn-secondary:hover {
            background-color: #0056b3;
        }

        .radio-group {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .radio-group label {
            margin: 0;
            font-weight: normal;
        }

        @media (max-width: 600px) {
            td:first-child {
                text-align: left;
                font-weight: normal;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Add New Item</h1>

        <form action="" method="POST" enctype="multipart/form-data">
            <table>
                <tr>
                    <td>Title:</td>
                    <td><input type="text" name="title" placeholder="Title of the Item" value="<?php echo isset($title) ? htmlspecialchars($title) : ''; ?>"></td>
                </tr>
                <tr>
                    <td>Description:</td>
                    <td><textarea name="description" cols="30" rows="5" placeholder="Description of Item"><?php echo isset($description) ? htmlspecialchars($description) : ''; ?></textarea></td>
                </tr>
                <tr>
                    <td>Price:</td>
                    <td><input type="text" name="price" placeholder="Price of the Item" value="<?php echo isset($price) ? htmlspecialchars($price) : ''; ?>"></td>
                </tr>
                <tr>
                    <td>Number of Items Left:</td>
                    <td><input type="number" name="quantity" placeholder="Enter quantity available" min="1" value="<?php echo isset($quantity) ? htmlspecialchars($quantity) : ''; ?>"></td>
                </tr>
                <tr>
                    <td>Select Image:</td>
                    <td><input type="file" name="image"></td>
                </tr>
                <tr>
                    <td>Category:</td>
                    <td>
                        <select name="category">
                            <?php
                                // Display categories from database
                                $sql = "SELECT * FROM tbl_category WHERE active='Yes'";
                                $res = mysqli_query($conn, $sql);

                                if(mysqli_num_rows($res) > 0) {
                                    while($row = mysqli_fetch_assoc($res)) {
                                        $id = $row['id'];
                                        $title = $row['title'];
                                        echo "<option value='$id'>$title</option>";
                                    }
                                } else {
                                    echo "<option value='0'>No Category Found</option>";
                                }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Featured:</td>
                    <td>
                        <div class="radio-group">
                            <label><input type="radio" name="featured" value="Yes"> Yes</label>
                            <label><input type="radio" name="featured" value="No"> No</label>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>Active:</td>
                    <td>
                        <div class="radio-group">
                            <label><input type="radio" name="active" value="Yes"> Yes</label>
                            <label><input type="radio" name="active" value="No"> No</label>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><input type="submit" name="submit" value="Add Item" class="btn-secondary"></td>
                </tr>
            </table>
        </form>
    </div>
</body>
</html>
