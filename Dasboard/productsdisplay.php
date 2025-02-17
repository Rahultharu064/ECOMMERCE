<?php
include '../includes/config.php';
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
   <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
<?php
include '../Dasboard/Navbar.php';
?>
<section style="display: flex; width: 100%">
     <?php include '../Dasboard/Sidebar.php'; ?>
      <!-- //// main co -->
      <main class="content">
    <table>
       <a href="./productsadd.php">Add Product</a>
        <tr>
            <th>id</th>
            <th>categories_name</th>
            <th>product_name</th>
            <th>product_price</th>
            <th>product_quantity</th>
            <th>product_description</th>
            <th>product_image</th>
            <th>Action</th>
           
</tr>

<?php
$sql = "SELECT * FROM products";
$result = mysqli_query($conn, $sql);
$num = mysqli_num_rows($result);
if ($num > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['product_category']; ?></td>
            <td><?php echo $row['product_name']; ?></td>
            <td>Rs.<?php echo $row['product_price']; ?></td>
            <td><?php echo $row['product_quantity']; ?></td>
            <td><?php echo $row['product_description']; ?></td>
             <td><img style="width: 100px; height: 100px;"  src="../uploads/<?php echo $row['product_image']; ?>" alt=""></td>
             <td><a href="productsupdate.php?id=<?php echo $row['id']; ?>">Edit</a> 
              <a href="productsdelete.php?id=<?php echo $row['id']; ?>">Delete</a></td>
           
            </tr>
            <?php
            }
        }
        ?>
    </table>
    </main>
    </section>
</body>
</html>