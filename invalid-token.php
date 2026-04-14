<?php
class InvalidTokenPageRenderer {
    public function render() {
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invalid Token</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .message {
            text-align: center;
            margin-top: 50px;
            font-size: 1.2em;
            color: #ff0000;
        }
    </style>
</head>
<body>
    <div class="message">
        <p>The password reset link is invalid or has expired. Please try again.</p>
        <p><a href="login.php">Go back to login</a></p>
    </div>
</body>
</html>
<?php
    }
}

$invalidTokenPageRenderer = new InvalidTokenPageRenderer();
$invalidTokenPageRenderer->render();
?>
