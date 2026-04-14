<?php include('partials/menu.php'); 

class ItemListManager extends BaseManager {
    public function __construct($db = null) {
        parent::__construct($db);
    }
    
    public function getAllItems() {
        $sql = "SELECT * FROM tbl_items";
        $res = $this->db->query($sql);
        return $res ? $this->db->fetchAll($res) : [];
    }
}

$itemListManager = new ItemListManager();
$items = $itemListManager->getAllItems();
?>

<div class="main">
    <div class="wrapper">
        <h1>Item</h1>

        <br><br>
        <a href="<?php echo SITEURL; ?>admin/add-item.php" class="btn-primary">Add Item</a>
        <br><br>

        <?php 
            if(isset($_SESSION['add'])) {
                echo $_SESSION['add'];
                unset($_SESSION['add']);
            }
            if(isset($_SESSION['delete'])) {
                echo $_SESSION['delete'];
                unset($_SESSION['delete']);
            }
            if(isset($_SESSION['upload'])) {
                echo $_SESSION['upload'];
                unset($_SESSION['upload']);
            }
            if(isset($_SESSION['unauthorize'])) {
                echo $_SESSION['unauthorize'];
                unset($_SESSION['unauthorize']);
            }
            if(isset($_SESSION['update'])) {
                echo $_SESSION['update'];
                unset($_SESSION['update']);
            }
        ?>

        <table class="tbl-full">
            <tr>
                <th>S.N.</th>
                <th>Title</th>
                <th>Price</th>
                <th>Image</th>
                <th>Featured</th>
                <th>Active</th>
                <th>Actions</th>
            </tr>

            <?php
                $sn = 1;
                if (count($items) > 0) {
                    foreach ($items as $item) {
                        $id = $item['id'];
                        $title = $item['title'];
                        $price = $item['price'];
                        $image_name = $item['image_name'];
                        $featured = $item['featured'];
                        $active = $item['active'];
            ?>
                        <tr>
                            <td><?php echo $sn++; ?></td>
                            <td><?php echo htmlspecialchars($title); ?></td>
                            <td>Rs.<?php echo htmlspecialchars($price); ?></td>
                            <td>
                                <?php 
                                    if($image_name == "") {
                                        echo "<div class='error'>Image not added</div>";
                                    } else {
            ?>
                                        <img src="<?php echo SITEURL; ?>images/item/<?php echo htmlspecialchars($image_name); ?>" width="100px">
            <?php
                                    }
            ?>
                            </td>
                            <td><?php echo htmlspecialchars($featured); ?></td>
                            <td><?php echo htmlspecialchars($active); ?></td>
                            <td>
                                <a href="<?php echo SITEURL; ?>admin/update-item.php?id=<?php echo $id; ?>" class="btn-secondary">Update Item</a>
                                <a href="<?php echo SITEURL; ?>admin/delete-item.php?id=<?php echo $id; ?>&image_name=<?php echo htmlspecialchars($image_name); ?>" class="btn-secondary1">Delete Item</a>  
                            </td>
                        </tr>
            <?php
                    }
                } else {
                    echo "<tr><td colspan='7' class='error'>Item not added yet</td></tr>";
                }
            ?>
        </table>
    </div>
</div>
<?php include('partials/footer.php'); ?>
        background-color: #d9534f;
        color: #fff;
    }
</style>

<?php include('partials/footer.php'); ?>
