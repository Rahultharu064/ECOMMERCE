<!-- 
<?php
include '../includes/config.php';
 

if(isset($_POST['submit'])){
    $product_category = $_POST['product_category'];
    $product_name = $_POST['product_name'];
    $product_price = $_POST['product_price'];
    $product_quantity = $_POST['product_quantity'];
    $product_description = $_POST['product_description'];
    
    $product_image = $_FILES['product_image']['name'];
    $upload_dir = '../uploads/';
    $files_size = $_FILES['product_image']['size'];
    $files_temp_name = $_FILES['product_image']['tmp_name'];
    $files_extension = pathinfo($product_image, PATHINFO_EXTENSION);
    $timestamp = time();
    $files_with_timestamp = $timestamp . '.' . $files_extension;
    $upload_file_path = $upload_dir . $files_with_timestamp;
    $allowed_file_types = array('jpg', 'jpeg', 'png', 'gif');

    if(!in_array(strtolower($files_extension), $allowed_file_types)){
        echo "Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.";
    } else {
        if($files_size <= 2000000){
            if(move_uploaded_file($files_temp_name, $upload_file_path)){
                $sql = "INSERT INTO `products` (`product_category`, `product_name`, `product_price`, `product_quantity`, `product_description`, `product_image`) VALUES ('$product_category', '$product_name', '$product_price', '$product_quantity', '$product_description', '$files_with_timestamp')";
                $result = mysqli_query($conn, $sql);
                if($result){
                    echo "Product added successfully";
                } else {
                    echo "Error adding product: " ;
                }
            } else {
                echo "Failed to move uploaded file.";
            }
        } else {
            echo "File size exceeds the maximum allowed size of 2MB.";
        }
    }
}

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
<?php include '../Dasboard/Navbar.php'; ?>
<section style="display: flex; width: 100%">
     <?php include '../Dasboard/Sidebar.php'; ?>
      <!-- //// main co -->
      <main class="content">
      <a href="./productsdisplay.php">Display Product</a>
      <a href="./categoriesAdd.php">ADDCATEGORIES</a>

    <form action="" method="POST" enctype="multipart/form-data">
       <select name="product_category" id="">
        <option value="none" selected disabled></option>
        <?php
        $sql = "SELECT * FROM categories";
        $result = mysqli_query($conn, $sql);
        $num = mysqli_num_rows($result);
        if($num>0){
            while($row = mysqli_fetch_assoc($result)){?>
                <option value="<?php echo $row['id']; ?>"><?php echo $row['categories_name']; ?></option>
              
                <?php
                }
        }
        ?>
            </select>
        <input type="text" name="product_name" placeholder="Product Name">
        <input type="number" name="product_price" placeholder="Product Price">
        <input type="number" name="product_quantity" placeholder="Product Quantity">
        <textarea name="product_description" id=""></textarea>
        <input type="file" name="product_image" accept="image/*" placeholder="Product Image">
        <input type="submit" name="submit" value="Add Product">
    </form>
      </main>
    </section>
    
</body>
</html> -->