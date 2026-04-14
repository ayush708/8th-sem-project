<?php 
include('partials-front/menu.php');
include('config/constants.php');

class CategoryItemsManager extends BaseManager {
    public function __construct($db = null) {
        parent::__construct($db);
    }
    
    public function getCategoryTitle($categoryId) {
        $sql = "SELECT title FROM tbl_category WHERE id=?";
        $stmt = $this->db->prepare($sql);
        $this->db->bind($stmt, "i", $categoryId);
        $this->db->execute($stmt);
        $res = $this->db->getResult($stmt);
        
        if ($res && $this->db->numRows($res) > 0) {
            $row = $this->db->fetchAssoc($res);
            return $row['title'];
        }
        return null;
    }
    
    public function getItemsByCategory($categoryId) {
        $sql = "SELECT * FROM tbl_items WHERE category_id=? AND active='Yes'";
        $stmt = $this->db->prepare($sql);
        $this->db->bind($stmt, "i", $categoryId);
        $this->db->execute($stmt);
        $res = $this->db->getResult($stmt);
        return $res ? $this->db->fetchAll($res) : [];
    }
    
    public function renderProductBox($product) {
        $id = $product['id'];
        $title = $product['title'];
        $price = $product['price'];
        $description = $product['description'];
        $imageName = $product['image_name'];
        $quantity = $product['quantity'];
        
        $imageHtml = "";
        if ($imageName == "") {
            $imageHtml = "<div class='error'>Image not available</div>";
        } else {
            $imageHtml = "<img src='" . SITEURL . "images/item/{$imageName}' alt='{$title}' class='img-responsive'>";
        }
        
        return "
            <div class='explore-box'>
                <div class='explore-menu-img'>
                    {$imageHtml}
                </div>
                <div class='explore-menu-desc'>
                    <h4>{$title}</h4>
                    <p class='item-price'>Rs. {$price}</p>
                    <p class='item-detail'>{$description}</p>
                    <p class='quantity'>Items Left: {$quantity}</p>
                    <br>
                    <a href='" . SITEURL . "order.php?item_id={$id}' class='btn btn-primary'>Order Now</a>
                    <a href='" . SITEURL . "add-to-cart.php?item_id={$id}' class='btn btn-secondary'>Add to Cart</a>
                </div>
            </div>
        ";
    }
    
    public function renderAllItems($items) {
        if (empty($items)) {
            return "<div class='error'>Item not available</div>";
        }
        
        $html = '';
        foreach ($items as $item) {
            $html .= $this->renderProductBox($item);
        }
        return $html;
    }
}

// Check whether id is passed or not
if (isset($_GET['category_id'])) {
    $categoryId = $_GET['category_id'];
    $categoryItems = new CategoryItemsManager();
    
    $categoryTitle = $categoryItems->getCategoryTitle($categoryId);
    
    if (!$categoryTitle) {
        header('location:' . SITEURL);
        exit();
    }
    
    $items = $categoryItems->getItemsByCategory($categoryId);
} else {
    header('location:' . SITEURL);
    exit();
}

<!-- Item Search Section Starts Here -->
<section class="search text-center">
    <div class="container">
        <h2>Items in <a href="#" class="text-white">"<?php echo $categoryTitle ?>"</a></h2>
    </div>
</section>
<!-- Item Search Section Ends Here -->

<!-- Item Menu Section Starts Here -->
<section class="item-menu">
    <div class="container">
        <h2 class="text-center">Item Menu</h2>

        <div class="explore-grid">
        <?php
            echo $categoryItems->renderAllItems($items);
        ?>
        </div>

        <div class="clearfix"></div>
    </div>
</section>
<!-- Item Menu Section Ends Here -->

<?php include('partials-front/footer.php');?>
<style>
    .quantity {
    display: inline-block;
    font-weight: bold;
    color: #ff6b6b; /* Red shade for emphasis */
    background-color: #f9f9f9;
    border: 1px solid #ff6b6b; /* Matching border color */
    border-radius: 5px;
    padding: 5px 10px;
    margin-top: 10px;
    font-size: 0.9em;
}

</style>
