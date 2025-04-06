<?php
session_start();
include '../includes/config.php';

    


         
?>








<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
               
        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0,
            0, 0.1);
            }
        table {
            border-collapse: collapse;
            width: 100%;
            }
            th, td {
                border: 1px solid #ddd;
                padding: 8px;
                text-align: left;
                }
                th {
                    background-color: #f2f2f2;
                    }
                    tr:nth-child(even) {
                        background-color: #f9f9f9;
                        }
                        tr:hover {
                            background-color: #f1f1f1;
                            }
                            a {
                                text-decoration: none;
                                color: #007bff;
                                }
                                a:hover {
                                    text-decoration: underline;
                                    }
                                    </style>

    </style>
</head>
<body>
   <div class="container">
   <table>
    <tr>
        <th>
            id
        </th>
        <th>product</th>
        <th>price</th>
        <th>Quantity</th>
        <th>Total</th>
        <th>Action</th>

    </tr>
   
    <?php
    $grand_total = 0;

    $user_id = $_SESSION['user_id'];
    $sql = "SELECT *, carts.id As cart_id, mproducts.id AS product_id FROM carts INNER JOIN mproducts ON carts.product_id=mproducts.id WHERE user_id = $user_id";
    $result = mysqli_query($conn, $sql);
    $num = mysqli_num_rows($result);
   
    
   
    if ($num > 0) {

        while ($row = mysqli_fetch_assoc($result)) {
            ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><img src="../uploads/<?php echo $row['image_url']; ?>" alt="Product Image" width="50" height="50"></td>
                <td>Rs.<?php echo $row['price']; ?></td>
                <td><?php echo $row['product_quantity']; ?></td>
                <td>Rs.<?php echo $row['price'] * $row['product_quantity']; ?></td>
                <td><a href="cartdelete.php?id=<?php echo $row['id']; ?>">Delete</a></td>


                
            </tr>
          
            <?php
            $grand_total += $row['price'] * $row['product_quantity'];
            
        }
    }
    ?>


</table>
<div style="text-align: right;">
    <h2>Grand Total: Rs.<?php echo $grand_total; ?></h2>
    <a href="checkout.php">Proceed to Checkout</a>
   </div>
    
</body>
</html>