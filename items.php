<?php 
include('partials-front/menu.php');

class ProductCatalogManager extends BaseManager {
    public function __construct($db = null) {
        parent::__construct($db);
    }
    
    public function getAllActiveProducts($limit = 50) {
        $sql = "SELECT * FROM tbl_items WHERE active='Yes' AND featured='Yes' LIMIT {$limit}";
        $res = $this->db->query($sql);
        return $res ? $this->db->fetchAll($res) : [];
    }
    
    public function renderProductBox($product) {
        $id = $product['id'];
        $title = $product['title'];
        $price = $product['price'];
        $description = $product['description'];
        $image_name = $product['image_name'];
        $quantity = $product['quantity'];
        
        $imageHtml = "";
        if ($image_name == "") {
            $imageHtml = "<div class='error'>Image not available</div>";
        } else {
            $imageHtml = "<img src='" . SITEURL . "images/item/{$image_name}' alt='{$title}' class='img-responsive'>";
        }
        
        return "
            <div class='explore-box'>
                <div class='explore-menu-img'>
                    {$imageHtml}
                </div>
                <div class='explore-menu-desc'>
                    <h4>{$title}</h4>
                    <p class='price'>Rs.{$price}</p>
                    <p class='desc'>{$description}</p>
                    <p class='quantity'>Items Left: {$quantity}</p>
                    <a href='" . SITEURL . "order.php?item_id={$id}' class='btn btn-primary'>Order Now</a>
                    <a href='" . SITEURL . "add-to-cart.php?item_id={$id}' class='btn btn-secondary add-to-cart'>
                        <i class='fas fa-shopping-basket'></i> Add to Cart
                    </a>
                </div>
            </div>
        ";
    }
    
    public function renderAllProducts($products) {
        if (empty($products)) {
            return "<div class='error'>Item not available</div>";
        }
        
        $html = '';
        foreach ($products as $product) {
            $html .= $this->renderProductBox($product);
        }
        return $html;
    }
}

$product = new ProductCatalogManager();
$products = $product->getAllActiveProducts();
?>

<!-- Search section start -->
<section class="search text-center">
    <div class="container">
        <form action="<?php echo SITEURL; ?>item-search.php" method="POST">
            <input type="search" name="search" placeholder="Search for items..." class="input-search">
            <input type="submit" name="submit" value="Search" class="btn btn-primary">
       </form>
    </div>
</section>
<!-- Search section end -->

<?php
if(isset($_SESSION['order']))
{
    echo '<div class="success">' . $_SESSION['order'] . '</div>';
    unset($_SESSION['order']);
}
?>

<!-- Explore section start -->
<section class="explore">
    <div class="container">
        <h2 class="text-center">Explore Items</h2>
        <div class="explore-grid">
        <?php echo $product->renderAllProducts($products); ?>
        </div>
        <div class="clearfix"></div>
    </div>
</section>
<!-- Explore section end -->


<?php include('partials-front/footer.php');?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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
