<?php 
include('partials-front/menu.php');

class CategoryCatalogManager extends BaseManager {
    public function __construct($db = null) {
        parent::__construct($db);
    }
    
    public function getAllActiveCategories() {
        $sql = "SELECT * FROM tbl_category WHERE active='Yes'";
        $res = $this->db->query($sql);
        return $res ? $this->db->fetchAll($res) : [];
    }
    
    public function renderCategories($categories) {
        if (empty($categories)) {
            return "<div class='error'>Category not Found</div>";
        }
        
        $html = '';
        foreach ($categories as $row) {
            $id = $row['id'];
            $title = $row['title'];
            $image_name = $row['image_name'];
            
            $imageHtml = "";
            if ($image_name == "") {
                $imageHtml = "<div class='error'>Image not available</div>";
            } else {
                $imageHtml = "<img src='" . SITEURL . "images/category/{$image_name}' alt='{$title}' class='img-responsive'>";
            }
            
            $html .= "
                <a href='" . SITEURL . "category-items.php?category_id={$id}'>
                    <div class='box-3 float-container'>
                        {$imageHtml}
                        <h3 class='float-text'>{$title}</h3>
                    </div>
                </a>
            ";
        }
        return $html;
    }
}

$category = new CategoryCatalogManager();
$categories = $category->getAllActiveCategories();
?>
    <!-- Categories Section Starts Here -->
    <section class="categories">
        <div class="container">
            <h2 class="text-center">Explore Items</h2>
            <?php echo $category->renderCategories($categories); ?>
            <div class="clearfix"></div>
        </div>
    </section>
    <!-- Categories Section Ends Here -->

    <?php include('partials-front/footer.php');?>