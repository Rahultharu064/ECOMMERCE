<?php
session_start();
include '../includes/config.php';
if(isset($_POST['addtocart'])){
    $product_id = $_POST['pid'];
     $user_id = $_SESSION['user_id'];
//     $user_id = 1;
    $product_quantity = isset($_POST['product_quantity']) ? $_POST['product_quantity'] : 1;
    $sql = "SELECT * FROM carts WHERE product_id = $product_id AND user_id = $user_id";
   $result = mysqli_query($conn, $sql);
   $num=mysqli_num_rows($result);
   if($num >0){
    $addQuery = "UPDATE carts SET product_quantity = product_quantity + 1 Where product_id = $product_id";

   } else{
    $addQuery = "INSERT INTO carts (product_id, user_id, product_quantity) VALUES ($product_id, $user_id, $product_quantity)";
   }
   $res = mysqli_query($conn, $addQuery);
    if($res){
         echo "Product added to cart successfully";
     //     window.location.href = "../Dashboard/cartdisplay.php";
    } else{
         echo "Failed to add product to cart";
    }

}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add to Cart - Pharmacy</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

    <style>
        /* General Styles */
body {
    font-family: 'Arial', sans-serif;
    background-color: #e0f7fa; /* Light blue background */
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

.pharmacy-container {
    max-width: 600px;
    width: 100%;
    padding: 20px;
}

.product-card {
    background-color: #ffffff;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    text-align: center;
    animation-duration: 1s;
}

.product-title {
    font-size: 28px;
    color: #00796b; /* Dark teal for a medical feel */
    margin-bottom: 10px;
}

.product-description {
    font-size: 16px;
    color: #555;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 20px;
}

label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
    color: #00796b;
}

input[type="number"] {
    width: 100%;
    padding: 10px;
    border: 2px solid #00796b;
    border-radius: 8px;
    font-size: 16px;
    color: #00796b;
    text-align: center;
}

.add-to-cart-btn {
    padding: 12px 30px;
    background-color: #00796b; /* Dark teal */
    color: #ffffff;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.3s ease;
}

.add-to-cart-btn:hover {
    background-color: #004d40; /* Darker teal on hover */
    transform: translateY(-2px);
}

.add-to-cart-btn:active {
    transform: translateY(0);
}

.message {
    margin-top: 20px;
    font-size: 14px;
    color: #00796b;
    animation: fadeIn 0.5s ease-in-out;
}

/* Animations */
@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}
    </style>
</head>
<body>
    <div class="pharmacy-container">
        <div class="product-card animate__animated animate__fadeIn">
            <h1 class="product-title">Pharmacy Product</h1>
            <p class="product-description">A high-quality medical product designed for your health and wellness.</p>
            <form id="add-to-cart-form" method="POST">
                <input type="hidden" name="pid" value="1"> <!-- Replace with actual product ID -->
                <div class="form-group">
                    <label for="product_quantity">Quantity:</label>
                    <input type="number" id="product_quantity" name="product_quantity" value="1" min="1">
                </div>
                <button type="submit" name="addtocart" class="add-to-cart-btn">Add to Cart</button>
            </form>
            <div id="message" class="message"></div>
        </div>
    </div>

    <script src="script.js">document.getElementById('add-to-cart-form').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent the form from submitting the traditional way

    const formData = new FormData(this);
    const messageDiv = document.getElementById('message');

    fetch('add_to_cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        messageDiv.textContent = data;
        messageDiv.classList.add('animate__animated', 'animate__fadeIn');
    })
    .catch(error => {
        console.error('Error:', error);
        messageDiv.textContent = 'Failed to add product to cart';
        messageDiv.classList.add('animate__animated', 'animate__shakeX');
    });
});</script>
</body>
</html>



   



   