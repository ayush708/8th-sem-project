<?php
include('partials-front/menu.php');
include('config/constants.php');

class ItemSearchManager extends BaseManager {
    public function __construct($db = null) {
        parent::__construct($db);
    }
    
    public function searchItems($keyword) {
        $keyword = '%' . $keyword . '%';
        $sql = "SELECT * FROM tbl_items WHERE (title LIKE ? OR description LIKE ?) AND active='Yes'";
        $stmt = $this->db->prepare($sql);
        $this->db->bind($stmt, "ss", $keyword, $keyword);
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
            $imageHtml = "<img src='" . SITEURL . "images/item/{$imageName}' alt='{$title}' class='img-responsive' style='width: 100%; height: auto; max-height: 300px; object-fit: contain;'>";
        }
        
        return "
            <div class='item-menu-box'>
                <div class='item-menu-img'>
                    {$imageHtml}
                </div>
                <div class='item-menu-desc'>
                    <h4>{$title}</h4>
                    <p class='item-price'>Rs.{$price}</p>
                    <p class='item-description'>{$description}</p>
                    <p class='quantity'>Available: {$quantity}</p>
                    <a href='" . SITEURL . "order.php?item_id={$id}' class='btn btn-primary'>Order Now</a>
                    <a href='" . SITEURL . "add-to-cart.php?item_id={$id}' class='btn btn-primary' style='margin-left: 5px;'>Add to Cart</a>
                </div>
            </div>
        ";
    }
    
    public function renderAllSearchResults($items) {
        if (empty($items)) {
            return "<h2 class='text-center'>No items found for this search.</h2>";
        }
        
        $html = '';
        foreach ($items as $item) {
            $html .= $this->renderProductBox($item);
        }
        return $html;
    }
}

$search = isset($_POST['search']) ? htmlspecialchars($_POST['search']) : '';
$searchEngine = new ItemSearchManager();
$searchResults = [];

if (!empty($search)) {
    $searchResults = $searchEngine->searchItems($search);
}

<!-- Inline CSS for responsiveness and styling -->
<style>
    body {
        background-color: #f8f9fa; /* Light gray background */
    }

    .container {
        margin: 0 auto;
        padding: 1%;
    }

    .text-center {
        text-align: center;
    }

    .text-white {
        color: white;
    }

    .btn-primary {
        background-color: #007bff; /* Blue */
        color: white;
        cursor: pointer;
        padding: 10px 15px;
        border-radius: 5px;
        display: inline-block;
        text-decoration: none;
    }

    .btn-primary:hover {
        color: white;
        background-color: #0056b3; /* Darker blue */
    }

    .item-menu-box {
        background-color: white;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        overflow: hidden;
        margin-bottom: 20px;
        display: flex;
        flex-direction: column;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        transition: transform 0.3s;
    }

    .item-menu-box:hover {
        transform: scale(1.02);
    }

    .item-menu-img img {
        width: 100%;
        height: auto;
        display: block;
        max-height: 300px;
        object-fit: contain;
    }

    .item-menu-desc {
        padding: 15px;
        text-align: center;
    }

    .item-menu-desc h4 {
        font-size: 1.5rem;
        margin-bottom: 10px;
    }

    .item-price {
        font-size: 1.2rem;
        color: #28a745; /* Green */
        margin-bottom: 10px;
    }

    .item-detail {
        color: #6c757d; /* Gray */
        font-size: 1rem;
        margin-bottom: 15px;
    }

    .item-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }

    @media (max-width: 768px) {
        .item-grid {
            grid-template-columns: 1fr;
        }
    }

    .search {
        background-image: url('../images/landing1.jpg') !important;
        background-size: cover;
        background-repeat: no-repeat;
        background-position: center;
        padding: 10% 0;
    }

    .search input[type="search"] {
        width: 50%;
        padding: 1%;
        font-size: 1rem;
        border: none;
        border-radius: 5px;
    }

    @media (max-width: 768px) {
        .search input[type="search"] {
            width: 80%;
        }
    }

    @media (max-width: 480px) {
        .search input[type="search"] {
            width: 100%;
        }
    }
</style>

<!-- Item SEARCH Section Starts Here -->
<section class="search text-center">
    <div class="container">
        <h2>Search for Items</h2>
        <!-- Search form -->
        <div class="search-bar">
            <form action="item-search.php" method="post">
                <input type="search" name="search" placeholder="Enter item name or description" required>
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
        </div>
        
        <?php if ($search): ?>
            <h2>Items on Your Search <a href="#" class="text-white">"<?php echo htmlspecialchars($search); ?>"</a></h2>
        <?php endif; ?>
    </div>
</section>
<!-- Item SEARCH Section Ends Here -->

<!-- Item Menu Section Starts Here -->
<section class="menu">
    <div class="container">
        <h2 class="text-center">Item Menu</h2>

        <div class="item-grid">
            <?php
            // Database connection
            $servername = "localhost";
            $username = "root";
            $password = "";
            $database = "petshop";

            $conn = mysqli_connect($servername, $username, $password, $database);

            if (!$conn) {
                die("Connection failed: " . mysqli_connect_error());
            }

            // SQL query to get items based on search
            $sql = "SELECT * FROM tbl_items WHERE title LIKE '%$search%' OR description LIKE '%$search%'";

            // Execute query
            $res = mysqli_query($conn, $sql);

            // Check whether items are available or not
            if (mysqli_num_rows($res) > 0) {
                // Array to hold items and their relevance scores
                $items = array();

                while ($row = mysqli_fetch_assoc($res)) {
                    $title = $row['title'];
                    $description = $row['description'];

                    // Calculate the Levenshtein distance between the search term and the title/description
                    $lev_title = levenshtein($search, $title);
                    $lev_description = levenshtein($search, $description);

                    // Choose the smaller distance as the relevance score (lower is better)
                    $relevance = min($lev_title, $lev_description);

                    // Store the item and its relevance score in the array
                    $row['relevance'] = $relevance;
                    $items[] = $row;
                }

                // Sort the items by their relevance score
                usort($items, function($a, $b) {
                    return $a['relevance'] - $b['relevance'];
                });

                // Display the sorted items
                foreach ($items as $item) {
                    $image_path = 'images/item/' . htmlspecialchars($item['image_name']);

                    ?>
                    <div class="item-menu-box">
                        <div class="item-menu-img">
                            <?php
                            // Debugging information
                            if (file_exists($image_path)) {
                                ?>
                                <img src="<?php echo $image_path; ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="img-responsive img-curve">
                                <?php
                            } else {
                                echo "<div class='error'>Image not Available. Path tried: $image_path</div>";
                            }
                            ?>
                        </div>

                        <div class="item-menu-desc">
                            <h4><?php echo htmlspecialchars($item['title']); ?></h4>
                            <p class="item-price">Rs<?php echo htmlspecialchars($item['price']); ?></p>
                            <p class="item-detail">
                                <?php echo htmlspecialchars($item['description']); ?>
                            </p>
                            <br>
                            <a href="order.php?item_id=<?php echo $item['id']; ?>" class="btn btn-primary">Order Now</a>
                            <!-- <a href="<?php echo SITEURL; ?>add-to-cart.php?item_id=<?php echo $id; ?>" class="btn btn-secondary add-to-cart">
                                <i class="fas fa-shopping-basket"></i> Add to Cart
                            </a> -->
                            <a href="<?php echo SITEURL; ?>add-to-cart.php?item_id=<?php echo $item['id']; ?>" class="btn btn-secondary add-to-cart">
                                 <i class="fas fa-shopping-basket"></i> Add to Cart
                            </a>

                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "<div class='error'>Item not found.</div>";
            }

            mysqli_close($conn);
            ?>
        </div>
    </div>
</section>
<!-- Item Menu Section Ends Here -->

<?php include('partials-front/footer.php'); ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">