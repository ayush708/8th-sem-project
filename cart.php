<?php 
session_start();
include('config/constants.php'); 
include('partials-front/menu.php');

// Cart Management Class
class Cart {
    public function getCartItems() {
        return isset($_SESSION['cart']) && is_array($_SESSION['cart']) ? $_SESSION['cart'] : [];
    }
    
    public function hasItems() {
        return isset($_SESSION['cart']) && count($_SESSION['cart']) > 0;
    }
    
    public function calculateTotal() {
        $total = 0;
        if ($this->hasItems()) {
            foreach ($_SESSION['cart'] as $item) {
                $total += $item['price'] * $item['quantity'];
            }
        }
        return $total;
    }
    
    public function getItemTotal($item) {
        return $item['price'] * $item['quantity'];
    }
    
    public function renderCartTable() {
        if (!$this->hasItems()) {
            return "<div class='error text-red-600 text-lg font-bold text-center mt-4'>Your cart is empty.</div>";
        }
        
        $html = "<table class='min-w-full bg-white border border-gray-200'>"; 
        $html .= "<thead class='bg-gray-100'>
                    <tr>
                        <th class='py-2 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-600'>Item</th>
                        <th class='py-2 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-600'>Price</th>
                        <th class='py-2 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-600'>Quantity</th>
                        <th class='py-2 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-600'>Total</th>
                        <th class='py-2 px-4 border-b border-gray-200 text-left text-sm font-semibold text-gray-600'>Action</th>
                    </tr>
                </thead>
                <tbody>";
        
        $totalPrice = $this->calculateTotal();
        
        foreach ($_SESSION['cart'] as $key => $item) {
            $itemTotal = $this->getItemTotal($item);
            $html .= "<tr class='hover:bg-gray-50'>
                        <td class='py-3 px-4 border-b border-gray-200'>{$item['title']}</td>
                        <td class='py-3 px-4 border-b border-gray-200'>Rs. {$item['price']}</td>
                        
                        <td class='py-3 px-4 border-b border-gray-200'>
                            <form action='update-cart.php' method='POST'>
                                <input type='number' name='quantity' value='{$item['quantity']}' min='1' class='w-16 p-1 border rounded'>
                                <input type='hidden' name='item_id' value='{$item['id']}'>
                                <button type='submit' class='ml-2 bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded'>Update</button>
                            </form>
                        </td>
                        
                        <td class='py-3 px-4 border-b border-gray-200'>Rs. {$itemTotal}</td>
                        <td class='py-3 px-4 border-b border-gray-200'>
                            <a href='remove-from-cart.php?item_id={$item['id']}' class='btn btn-danger bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded'>Remove</a>
                        </td>
                    </tr>";
        }
        
        $html .= "<tr class='font-bold'>
                    <td colspan='3' class='py-3 px-4 border-b border-gray-200'>Total</td>
                    <td class='py-3 px-4 border-b border-gray-200'>Rs. {$totalPrice}</td>
                    <td class='py-3 px-4 border-b border-gray-200'>
                        <a href='ordered.php' class='btn btn-primary bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded'>Proceed to Checkout</a>
                    </td>
                </tr>";
        
        $html .= "</tbody></table>";
        return $html;
    }
}

$cart = new Cart();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <title>Your Cart</title>
</head>
<body>

<section class="cart py-8">
    <div class="container mx-auto">
        <h2 class="text-center text-3xl font-bold mb-6">Your Cart</h2>

        <?php
        // Check if the cart exists and is not empty
        if($cart->hasItems()) {
            echo $cart->renderCartTable();
        } else {
            // Display message if the cart is empty
            echo "<div class='error text-red-600 text-lg font-bold text-center mt-4'>Your cart is empty.</div>";
        }
        ?>
    </div>
</section>

<?php include('partials-front/footer.php'); ?>

</body>
</html>
