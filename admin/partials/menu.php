<?php
include('../config/constants.php');
include('login-check.php');

class AdminMenuRenderer {
    public function render() {
        ?>
<html>
    <head>
        <title>Online Pet Shop Admin</title>
        <link rel="stylesheet" href="../css/admin.css">
    </head>

    <body>
        <!--menu section starts here-->
        <div class="menu text-center">
            <div class="wrapper">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="manage-admin.php">Admin</a></li>
                <li><a href="manage-user.php">Users</a></li>
                <li><a href="category.php">Category</a></li>
                <li><a href="item.php">Item</a></li>
                <li><a href="order.php">Order</a></li>
                <li><a href="logout.php">Log Out</a></li>
            </ul>
            </div>
        </div>

        <!--menu section ends here-->
    </body>
</html>
<?php
    }
}

$adminMenuRenderer = new AdminMenuRenderer();
$adminMenuRenderer->render();
?>