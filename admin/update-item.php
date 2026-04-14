<?php 
include('partials/menu.php');

class ItemManager extends BaseManager {
    public function __construct($db = null) {
        parent::__construct($db);
    }
    
    public function getItemById($id) {
        $sql = "SELECT * FROM tbl_items WHERE id=?";
        $stmt = $this->db->prepare($sql);
        $this->db->bind($stmt, "i", $id);
        $this->db->execute($stmt);
        $res = $this->db->getResult($stmt);
        
        if ($this->db->numRows($res) == 1) {
            return $this->db->fetchAssoc($res);
        }
        return null;
    }
    
    public function validateItemInput($data) {
        $errors = [];
        
        if (empty($data['title'])) {
            $errors[] = "Title is required.";
        }
        
        if (empty($data['description'])) {
            $errors[] = "Description is required.";
        }
        
        if (empty($data['price']) || !is_numeric($data['price']) || $data['price'] <= 0) {
            $errors[] = "Price must be a valid number greater than zero.";
        }
        
        if (empty($data['quantity']) || !is_numeric($data['quantity']) || $data['quantity'] < 0) {
            $errors[] = "Quantity must be a valid number greater than or equal to zero.";
        }
        
        if ($data['category'] == '0') {
            $errors[] = "Please select a category.";
        }
        
        return $errors;
    }
    
    public function updateItemImage($newImageName, $currentImage) {
        $allowedExtensions = array('jpg', 'jpeg', 'png', 'gif');
        $fileExtension = strtolower(pathinfo($newImageName, PATHINFO_EXTENSION));
        
        if (!in_array($fileExtension, $allowedExtensions)) {
            return ['error' => "Invalid file type. Allowed types: jpg, jpeg, png, gif."];
        }
        
        $newImageName = 'item-name-' . rand(0000, 9999) . '.' . $fileExtension;
        $uploadPath = "../images/item/" . $newImageName;
        
        // Delete current image if it exists and is different
        if ($currentImage != "" && file_exists("../images/item/" . $currentImage)) {
            unlink("../images/item/" . $currentImage);
        }
        
        return ['success' => $newImageName];
    }
    
    public function updateItem($id, $title, $description, $price, $imageName, $category, $featured, $active, $quantity) {
        $sql = "UPDATE tbl_items SET title=?, description=?, price=?, image_name=?, category_id=?, featured=?, active=?, quantity=? WHERE id=?";
        $stmt = $this->db->prepare($sql);
        $this->db->bind($stmt, "ssssissii", $title, $description, $price, $imageName, $category, $featured, $active, $quantity, $id);
        return $this->db->execute($stmt);
    }
    
    public function getAllCategories() {
        $sql = "SELECT id, title FROM tbl_category";
        $res = $this->db->query($sql);
        return $res ? $this->db->fetchAll($res) : [];
    }
}

// Check if ID is set
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $itemManager = new ItemManager();
    $item = $itemManager->getItemById($id);
    
    if (!$item) {
        $_SESSION['update'] = "<div class='error'>Item not found.</div>";
        header('location:'.SITEURL.'admin/item.php');
        exit;
    }
    
    $title = $item['title'];
    $description = $item['description'];
    $price = $item['price'];
    $currentImage = $item['image_name'];
    $currentCategory = $item['category_id'];
    $featured = $item['featured'];
    $active = $item['active'];
    $quantity = $item['quantity'];
} else {
    header('location:'.SITEURL.'admin/item.php');
    exit;
}

// Process form submission
if (isset($_POST['submit'])) {
    $id = $_POST['id'];
    $title = htmlspecialchars($_POST['title']);
    $description = htmlspecialchars($_POST['description']);
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $currentImage = $_POST['current_image'];
    $category = $_POST['category'];
    $featured = isset($_POST['featured']) ? $_POST['featured'] : 'No';
    $active = isset($_POST['active']) ? $_POST['active'] : 'No';
    
    $itemManager = new ItemManager();
    $errors = $itemManager->validateItemInput([
        'title' => $title,
        'description' => $description,
        'price' => $price,
        'quantity' => $quantity,
        'category' => $category
    ]);
    
    $newImageName = $currentImage;
    
    if (isset($_FILES['image']['name']) && !empty($_FILES['image']['name'])) {
        $tmpName = $_FILES['image']['tmp_name'];
        $imageResult = $itemManager->updateItemImage($_FILES['image']['name'], $currentImage);
        
        if (isset($imageResult['error'])) {
            $errors[] = $imageResult['error'];
        } else {
            $newImageName = $imageResult['success'];
            if (!move_uploaded_file($tmpName, "../images/item/" . $newImageName)) {
                $errors[] = "Failed to upload image.";
            }
        }
    }
    
    if (empty($errors)) {
        if ($itemManager->updateItem($id, $title, $description, $price, $newImageName, $category, $featured, $active, $quantity)) {
            $_SESSION['update'] = "<div class='success'>Item Updated Successfully.</div>";
            header('location:'.SITEURL.'admin/item.php');
            exit;
        } else {
            $_SESSION['update'] = "<div class='error'>Failed to Update Item</div>";
            header('location:'.SITEURL.'admin/item.php');
            exit;
        }
    }
}

        }
    }
?>

<div class="main">
    <div class="wrapper">
        <h1>Update Item</h1>
        <br><br>

        <?php if(!empty($errors)): ?>
            <div class="error">
                <?php foreach($errors as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="container">
            <h2>Update Item</h2>
            <form action="" method="POST" enctype="multipart/form-data">
                <table class="tbl-30">
                    <tr>
                        <td>Title:</td>
                        <td>
                            <input type="text" name="title" value="<?php echo htmlspecialchars($title); ?>" >
                        </td>
                    </tr>
                    <tr>
                        <td>Description:</td>
                        <td>
                            <textarea name="description" cols="30" rows="5"><?php echo htmlspecialchars($description); ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td>Price:</td>
                        <td>
                            <input type="number" name="price" value="<?php echo htmlspecialchars($price); ?>" >
                        </td>
                    </tr>
                    <tr>
                        <td>Number of Items Left:</td>
                        <td>
                            <input type="number" name="quantity" placeholder="Enter quantity available" min="0" value="<?php echo htmlspecialchars($quantity); ?>">
                        </td>
                    </tr>
                    <tr>
                        <td>Current Image:</td>
                        <td>
                            <?php if(empty($current_image)) : ?>
                                <div class='error'>Image not available</div>
                            <?php else : ?>
                                <img src='<?php echo SITEURL; ?>images/item/<?php echo $current_image; ?>' width='150px'>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Select New Image:</td>
                        <td>
                            <input type="file" name="image">
                        </td>
                    </tr>
                    <tr>
                        <td>Category:</td>
                        <td>
                            <select name="category">
                                <?php
                                $sql = "SELECT * FROM tbl_category WHERE active='Yes'";
                                $res = mysqli_query($conn, $sql);

                                if(mysqli_num_rows($res) > 0) {
                                    while($row = mysqli_fetch_assoc($res)) {
                                        $category_id = $row['id'];
                                        $category_title = $row['title'];
                                        ?>
                                        <option value="<?php echo $category_id; ?>" <?php if($current_category == $category_id) echo 'selected'; ?>><?php echo $category_title; ?></option>
                                        <?php
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
                            <input type="radio" name="featured" value="Yes" <?php if($featured == "Yes") echo "checked"; ?>> Yes
                            <input type="radio" name="featured" value="No" <?php if($featured == "No") echo "checked"; ?>> No
                        </td>
                    </tr>
                    <tr>
                        <td>Active:</td>
                        <td>
                            <input type="radio" name="active" value="Yes" <?php if($active == "Yes") echo "checked"; ?>> Yes
                            <input type="radio" name="active" value="No" <?php if($active == "No") echo "checked"; ?>> No
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <input type="hidden" name="current_image" value="<?php echo $current_image; ?>">
                            <input type="submit" name="submit" value="Update Item" class="btn-secondary">
                        </td>
                    </tr>
                </table>
            </form>
        </div>
    </div>
</div>

<style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        .tbl-30 {
            width: 100%;
            border-spacing: 10px;
        }

        .tbl-30 td {
            padding: 10px;
        }

        .tbl-30 input[type="text"],
        .tbl-30 input[type="number"],
        .tbl-30 textarea,
        .tbl-30 select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 16px;
        }

        .tbl-30 textarea {
            resize: vertical;
        }

        .tbl-30 input[type="radio"] {
            margin-right: 10px;
        }

        .tbl-30 img {
            margin-top: 10px;
            border-radius: 8px;
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

        .error {
            color: red;
            font-size: 14px;
            margin-top: 5px;
        }

        @media (max-width: 600px) {
            .tbl-30 td {
                display: block;
                width: 100%;
            }

            .tbl-30 td input[type="radio"] {
                margin-right: 5px;
            }
        }
    </style>