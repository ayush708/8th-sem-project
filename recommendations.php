<?php
include('config/constants.php');

interface RecommendationStrategyInterface {
    public function calculateScore($totalSold, $totalViews);
}

abstract class BaseRecommendationStrategy implements RecommendationStrategyInterface {
    protected $weightSold;
    protected $weightViews;

    public function __construct($weightSold = 1.5, $weightViews = 0.5) {
        $this->weightSold = $weightSold;
        $this->weightViews = $weightViews;
    }
}

final class PopularityRecommendationStrategy extends BaseRecommendationStrategy {
    public function calculateScore($totalSold, $totalViews) {
        return ($totalSold * $this->weightSold) + ($totalViews * $this->weightViews);
    }
}

class Recommendations extends BaseManager {
    private $strategy;
    private $limit = 20;

    public function __construct($strategy = null, $db = null) {
        parent::__construct($db);
        $this->strategy = $strategy ?: new PopularityRecommendationStrategy();
    }

    public function setStrategy(RecommendationStrategyInterface $strategy) {
        $this->strategy = $strategy;
        return $this;
    }

    public function setLimit($limit) {
        $this->limit = max(1, (int)$limit);
        return $this;
    }

    protected function sanitize($value) {
        return parent::sanitize($value);
    }
    
    public function getRecommendedItems() {
        $sql = "SELECT * FROM tbl_items WHERE active='Yes' AND featured='Yes' ORDER BY (total_sold * 1.5 + total_views * 0.5) DESC LIMIT " . $this->limit;
        $stmt = $this->db->prepare($sql);
        $this->db->execute($stmt);
        $res = $this->db->getResult($stmt);
        
        $items = [];
        if ($this->db->numRows($res) > 0) {
            while ($row = $this->db->fetchAssoc($res)) {
                $items[] = $row;
            }
        }
        
        return $items;
    }
    
    public function calculatePopularityScore($totalSold, $totalViews) {
        return $this->strategy->calculateScore($totalSold, $totalViews);
    }
}

include('partials-front/menu.php');
?>
<section class="search text-center">
    <div class="container">
        <form action="<?php echo SITEURL; ?>item-search.php" method="POST">
            <input type="search" name="search" placeholder="Search..." aria-label="Search">
            <input type="submit" name="submit" value="Search" class="btn btn-primary">
        </form>
    </div>
</section>

<?php
if (isset($_SESSION['order'])) {
    echo "<div class='order-message'>" . $_SESSION['order'] . "</div>";
    unset($_SESSION['order']);
}
?>

<!-- Recommended Items section start -->
<section class="recommendation">
    <div class="container">
        <h2 class="text-center">Recommended Items</h2>

        <div class="explore-grid">
            <?php
            $recommendations = (new Recommendations())
                ->setLimit(20)
                ->setStrategy(new PopularityRecommendationStrategy());
            $recommended_items = $recommendations->getRecommendedItems();

            if (!empty($recommended_items)) {
                foreach ($recommended_items as $item) {
                    // Get item values
                    $id = $item['id'];
                    $title = $item['title'];
                    $price = $item['price'];
                    $image_name = $item['image_name'];
                    $quantity = $item['quantity'];
                    $total_sold = $item['total_sold'];
                    $total_views = $item['total_views'];

                    $popularity_score = $recommendations->calculatePopularityScore($total_sold, $total_views);

                    ?>
                    <div class="explore-box">
                        <div class="explore-menu-img">
                            <?php
                            if ($image_name == "") {
                                echo "<div class='error'>Image not available</div>";
                            } else {
                                ?>
                                <img src="<?php echo SITEURL; ?>images/item/<?php echo $image_name; ?>" alt="<?php echo $title; ?>" class="img-responsive">
                                <?php
                            }
                            ?>
                        </div>

                        <div class="explore-menu-desc">
                            <h4><?php echo $title; ?></h4>
                            <p class="price">Rs. <?php echo $price; ?></p>
                            <p class="quantity">Items Left: <?php echo $quantity; ?></p>
                            <p class="popularity-score">Popularity Score: <?php echo round($popularity_score, 2); ?></p>
                            <a href="<?php echo SITEURL; ?>order.php?item_id=<?php echo $id; ?>" class="btn btn-primary">Order Now</a>
                            <a href="<?php echo SITEURL; ?>add-to-cart.php?item_id=<?php echo $id; ?>" class="btn btn-secondary add-to-cart">
                                <i class="fas fa-shopping-basket"></i> Add to Cart
                            </a>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <?php
                }
            } else {
                echo "<div class='error'>No recommended items at the moment</div>";
            }
            ?>
        </div>

        <div class="clearfix"></div>
    </div>
</section>
<?php include('partials-front/footer.php'); ?>
