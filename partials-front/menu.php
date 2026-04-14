<?php include('config/constants.php');

class FrontMenuRenderer {
    private function isUserLoggedIn() {
        return isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;
    }

    private function getCartCount() {
        return (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) ? count($_SESSION['cart']) : 0;
    }

    public function render() {
        $cartCount = $this->getCartCount();
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- Important for making website responsive -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Pet Shop</title>

    <!-- Link to external CSS -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin.css">

    <style>
        /* Additional CSS for cart icon styling */
        .cart-icon-container {
            position: absolute;
            top: 20px;
            right: 20px;
        }

        .cart-icon {
            font-size: 24px;
            color: #333;
            text-decoration: none;
            position: relative;
        }

        .cart-icon::before {
            content: "\1F6D2"; /* Unicode for cart icon */
            font-size: 32px;
        }

        .cart-count {
            background-color: red;
            color: white;
            border-radius: 50%;
            padding: 2px 8px;
            font-size: 14px;
            position: absolute;
            top: -10px;
            right: -15px;
        }

        .clearfix {
            clear: both;
        }
    </style>
</head>
<body>
    <!-- Navbar section start -->
    <section class="navbar">
        <div class="container">
            <div class="logo">
                <a href="<?php echo SITEURL; ?>"><img src="images/logo.jpg" alt="Online Pet Shop logo" class="img-responsive"></a>
            </div>

            <div class="menu text-right">
                <ul>
                    <li>
                        <a href="<?php echo SITEURL; ?>">Home</a>
                    </li>
                    <!-- Show menu based on login status -->
                    <?php if ($this->isUserLoggedIn()) : ?>
                        <li>
                            <a href="<?php echo SITEURL; ?>categories.php">Categories</a>
                        </li>
                        <li>
                            <a href="<?php echo SITEURL; ?>items.php">Items</a>
                        </li>
                        <li>
                            <a href="<?php echo SITEURL; ?>user-dashboard.php">Dashboard</a>
                        </li>
                        <li>
                            <a href="<?php echo SITEURL; ?>logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li>
                            <a href="<?php echo SITEURL; ?>register.php">Sign Up</a>
                        </li>
                        <li>
                            <a href="<?php echo SITEURL; ?>login.php">Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Basket/Cart icon on top-right -->
            <div class="cart-icon-container">
                <a href="<?php echo SITEURL; ?>cart.php" class="cart-icon">
                    <?php echo "<span class='cart-count'>{$cartCount}</span>"; ?>
                </a>
            </div>
            <!-- Basket/Cart icon end -->

            <div class="clearfix"></div>
        </div>
    </section>
    <!-- Navbar section end -->
</body>
</html>
<?php
    }
}

$frontMenuRenderer = new FrontMenuRenderer();
$frontMenuRenderer->render();
?>
