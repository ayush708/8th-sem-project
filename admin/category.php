<?php include('partials/menu.php');

class CategoryListManager extends BaseManager {
    public function __construct($db = null) {
        parent::__construct($db);
    }
    
    public function getAllCategories() {
        $sql = "SELECT * FROM tbl_category";
        $res = $this->db->query($sql);
        return $res ? $this->db->fetchAll($res) : [];
    }
}

$categoryListManager = new CategoryListManager();
$categories = $categoryListManager->getAllCategories();
?>

<div class="main">
    <div class="wrapper">
        <h1>Category</h1>
        <br>
        <br>

        <?php
            if (isset($_SESSION['add'])) {
                echo $_SESSION['add'];
                unset($_SESSION['add']);
            }
            if (isset($_SESSION['remove'])) {
                echo $_SESSION['remove'];
                unset($_SESSION['remove']);
            }
            if (isset($_SESSION['delete'])) {
                echo $_SESSION['delete'];
                unset($_SESSION['delete']);
            }
            if (isset($_SESSION['no-category-found'])) {
                echo $_SESSION['no-category-found'];
                unset($_SESSION['no-category-found']);
            }
            if (isset($_SESSION['update'])) {
                echo $_SESSION['update'];
                unset($_SESSION['update']);
            }
            if (isset($_SESSION['upload'])) {
                echo $_SESSION['upload'];
                unset($_SESSION['upload']);
            }
            if (isset($_SESSION['failed-remove'])) {
                echo $_SESSION['failed-remove'];
                unset($_SESSION['failed-remove']);
            }
        ?>

        <br><br>
        <a href="<?php echo SITEURL; ?>admin/add-category.php" class="btn-primary">Add Category</a>
        <br>
        <br>
        <table class="tbl-full">
            <tr>
                <th>S.N.</th>
                <th>Title</th>
                <th>Image</th>
                <th>Featured</th>
                <th>Active</th>
                <th>Actions</th>
            </tr>

            <?php
                $sn = 1;
                if (count($categories) > 0) {
                    foreach ($categories as $category) {
                        $id = $category['id'];
                        $title = $category['title'];
                        $image_name = $category['image_name'];
                        $featured = $category['featured'];
                        $active = $category['active'];
            ?>
                        <tr>
                            <td><?php echo $sn++; ?></td>
                            <td><?php echo htmlspecialchars($title); ?></td>
                            <td>
                                <?php 
                                    if ($image_name != "") {
            ?>
                                        <img src="<?php echo SITEURL; ?>images/category/<?php echo htmlspecialchars($image_name); ?>" width="100px">
            <?php
                                    } else {
                                        echo "Image not added";
                                    }
            ?>
                            </td>
                            <td><?php echo htmlspecialchars($featured); ?></td>
                            <td><?php echo htmlspecialchars($active); ?></td>
                            <td>
                                <a href="<?php echo SITEURL; ?>admin/update-category.php?id=<?php echo $id; ?>" class="btn-secondary">Update Category</a>
                                <a href="<?php echo SITEURL; ?>admin/delete-category.php?id=<?php echo $id; ?>&image_name=<?php echo htmlspecialchars($image_name); ?>" class="btn-secondary1">Delete Category</a>  
                            </td>
                        </tr>
            <?php
                    }
                } else {
                    echo "<tr><td colspan='6'>No Category Added</td></tr>";
                }
            ?>
        </table>
    </div>
</div>
<?php include('partials/footer.php')?>