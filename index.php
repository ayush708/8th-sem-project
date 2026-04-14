<?php
include('config/constants.php');
include('partials-front/menu.php');

class HomePageManager extends BaseManager {
    public function __construct($db = null) {
        parent::__construct($db);
    }

    public function getOrderMessage() {
        if (isset($_SESSION['order'])) {
            $message = $_SESSION['order'];
            unset($_SESSION['order']);
            return $message;
        }
        return '';
    }

    public function isUserLoggedIn() {
        return isset($_SESSION['user']);
    }

    public function getFeaturedCategories() {
        $sql = "SELECT * FROM tbl_category WHERE active='Yes' AND featured='Yes'";
        $stmt = $this->db->prepare($sql);
        $this->db->execute($stmt);
        $res = $this->db->getResult($stmt);
        return $res ? $this->db->fetchAll($res) : [];
    }

    public function getFeaturedItems($limit = 20) {
        $limit = (int)$limit;
        $sql = "SELECT * FROM tbl_items WHERE active='Yes' AND featured='Yes' LIMIT {$limit}";
        $stmt = $this->db->prepare($sql);
        $this->db->execute($stmt);
        $res = $this->db->getResult($stmt);
        return $res ? $this->db->fetchAll($res) : [];
    }
}

$homePageManager = new HomePageManager();
$orderMessage = $homePageManager->getOrderMessage();
$isUserLoggedIn = $homePageManager->isUserLoggedIn();
$featuredCategories = $homePageManager->getFeaturedCategories();
$featuredItems = $homePageManager->getFeaturedItems(20);
?>

<!-- Search section start -->
<section class="search-section">
    <div class="container">
        <div class="search-wrapper">
            <form action="<?php echo SITEURL; ?>item-search.php" method="POST" class="search-form">
                <div class="form-group">
                    <input type="search" name="search" placeholder="What are you looking for?" class="search-input">
                    <button type="submit" name="submit" class="search-button">
                        <i class="fas fa-search"></i>
                        <span>Search</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>
<!-- Search section end -->

<?php if (!empty($orderMessage)) {
    echo "<div class='notification success'>{$orderMessage}</div>";
} ?>

<!-- Recommendations Section -->
<?php if ($isUserLoggedIn): ?>
    <style>
.recommendations-cta {
    padding: 30px 0;
    background-color: #f5f7fb;
}

.cta-button {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 12px 24px;
    font-size: 1rem;
    color: #2d3748;
    background-color: #ffffff;
    border-radius: 4px;
    text-decoration: none;
    transition: all 0.2s ease;
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.cta-button:hover {
    background-color: #f8fafc;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    transform: scale(1.02);
}

.cta-button i {
    color: #4f46e5;
    font-size: 1rem;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    text-align: center;
}
</style>

<section class="recommendations-cta">
    <div class="container">
        <a href="<?php echo SITEURL; ?>recommendations.php" class="cta-button">
            <i class="fas fa-heart"></i>
            See My Recommendations
        </a>
    </div>
</section>
<?php endif; ?>

<!-- Categories section start -->
<section class="categories-section">
    <div class="container">
        <h2 class="section-title">Shop by Category</h2>
        
        <div class="categories-grid">
            <?php
            if (!empty($featuredCategories)) {
                foreach ($featuredCategories as $row) {
                    $id = $row['id'];
                    $title = $row['title'];
                    $image_name = $row['image_name'];
                    ?>
                    <a href="<?php echo SITEURL; ?>category-items.php?category_id=<?php echo $id; ?>" class="category-card">
                        <div class="card-image">
                            <?php if ($image_name != ""): ?>
                                <img src="<?php echo SITEURL; ?>images/category/<?php echo $image_name; ?>" alt="<?php echo $title; ?>">
                            <?php else: ?>
                                <div class="image-placeholder"><i class="fas fa-box-open"></i></div>
                            <?php endif; ?>
                        </div>
                        <h3 class="card-title"><?php echo $title; ?></h3>
                    </a>
                    <?php
                }
            } else {
                echo "<div class='notification info'>No categories available at the moment.</div>";
            }
            ?>
        </div>
    </div>
</section>
<!-- Categories section end -->

<!-- Explore section start -->
<section class="products-section">
    <div class="container">
        <h2 class="section-title">Featured Products</h2>
        
        <div class="products-grid">
            <?php
            if (!empty($featuredItems)) {
                foreach ($featuredItems as $row) {
                    $id = $row['id'];
                    $title = $row['title'];
                    $price = $row['price'];
                    $description = $row['description'];
                    $image_name = $row['image_name'];
                    $quantity = $row['quantity'];
                    ?>
                    <div class="product-card">
                        <div class="card-header">
                            <?php if ($image_name != ""): ?>
                                <img src="<?php echo SITEURL; ?>images/item/<?php echo $image_name; ?>" alt="<?php echo $title; ?>">
                            <?php else: ?>
                                <div class="image-placeholder"><i class="fas fa-camera-retro"></i></div>
                            <?php endif; ?>
                            <div class="quantity-badge"><?php echo $quantity; ?> left</div>
                        </div>
                        <div class="card-body">
                            <h3 class="product-title"><?php echo $title; ?></h3>
                            <p class="product-description"><?php echo substr($description, 0, 80); ?>...</p>
                            <div class="price-container">
                                <span class="price">Rs. <?php echo number_format($price, 2); ?></span>
                            </div>
                        </div>
                        <div class="card-actions">
                            <a href="<?php echo SITEURL; ?>order.php?item_id=<?php echo $id; ?>" class="action-button primary">
                                <i class="fas fa-shopping-bag"></i> Buy Now
                            </a>
                            <a href="<?php echo SITEURL; ?>add-to-cart.php?item_id=<?php echo $id; ?>" class="action-button secondary">
                                <i class="fas fa-cart-plus"></i> Add to Cart
                            </a>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "<div class='notification info'>No products available at the moment.</div>";
            }
            ?>
        </div>
    </div>
</section>
<!-- Explore section end -->

<?php include('partials-front/footer.php'); ?>

<style>
/* Base Styles */
:root {
    --primary-color: #2a2a72;
    --secondary-color: #ff9f1a;
    --success-color: #28a745;
    --info-color: #17a2b8;
    --warning-color: #ffc107;
    --danger-color: #dc3545;
    --light-bg: #f8f9fa;
    --dark-text: #2d3436;
    --transition: all 0.3s ease;
}

/* Search Section */
.search-section {
    padding: 4rem 0;
    background: linear-gradient(135deg, var(--primary-color) 0%, #009ffd 100%);
}

.search-form .form-group {
    max-width: 600px;
    margin: 0 auto;
    position: relative;
}

.search-input {
    width: 100%;
    padding: 1.2rem 2rem;
    border: none;
    border-radius: 50px;
    font-size: 1.1rem;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.search-button {
    position: absolute;
    right: 5px;
    top: 50%;
    transform: translateY(-50%);
    padding: 0.8rem 2rem;
    border: none;
    border-radius: 50px;
    background: var(--secondary-color);
    color: white;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: var(--transition);
}

.search-button:hover {
    background: #ff8a00;
    transform: translateY(-50%) scale(0.98);
}

/* Categories Section */
.categories-section {
    padding: 4rem 0;
    background: var(--light-bg);
}

.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.category-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: var(--transition);
}

.category-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.15);
}

.card-image {
    height: 200px;
    position: relative;
    overflow: hidden;
}

.card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: var(--transition);
}

.category-card:hover img {
    transform: scale(1.05);
}

.card-title {
    padding: 1.5rem;
    margin: 0;
    text-align: center;
    color: var(--dark-text);
    font-size: 1.2rem;
}

/* Products Section */
.products-section {
    padding: 4rem 0;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.product-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: var(--transition);
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.15);
}

.card-header {
    height: 250px;
    position: relative;
    overflow: hidden;
}

.quantity-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    background: rgba(255, 255, 255, 0.9);
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.9rem;
    color: var(--dark-text);
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.card-body {
    padding: 1.5rem;
}

.product-title {
    margin: 0 0 0.5rem;
    color: var(--dark-text);
    font-size: 1.2rem;
}

.product-description {
    color: #666;
    font-size: 0.95rem;
    margin-bottom: 1rem;
}

.price-container {
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
}

.price {
    font-size: 1.4rem;
    font-weight: bold;
    color: var(--primary-color);
}

.card-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.5rem;
    padding: 0 1.5rem 1.5rem;
}

.action-button {
    padding: 0.8rem;
    text-align: center;
    border-radius: 8px;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: var(--transition);
}

.action-button.primary {
    background: var(--primary-color);
    color: white;
}

.action-button.secondary {
    background: var(--secondary-color);
    color: white;
}

.action-button:hover {
    opacity: 0.9;
    transform: translateY(-2px);
}

/* Notifications */
.notification {
    padding: 1rem 2rem;
    margin: 1rem auto;
    max-width: 1200px;
    border-radius: 8px;
}

.success {
    background: var(--success-color);
    color: white;
}

.info {
    background: var(--info-color);
    color: white;
}

/* Responsive Design */
@media (max-width: 768px) {
    .categories-grid {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    }
    
    .products-grid {
        grid-template-columns: 1fr;
    }
    
    .search-input {
        font-size: 1rem;
    }
}
</style>