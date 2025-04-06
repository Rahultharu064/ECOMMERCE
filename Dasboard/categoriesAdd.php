<?php 
 include '../includes/config.php';

 if(isset($_POST['submit'])){

    $categories_name = $_POST['categories_name'];
    $categories_description = $_POST['categories_description'];
    $categories_slug = $_POST['categories_slug'];
   
    $sql = "INSERT INTO categories (categories_name	, categories_description,categories_slug) VALUES ('$categories_name	','$categories_description','$categories_slug')";
    $result = mysqli_query($conn, $sql);
    if($result){
        echo "ADDED sucessfully";
        }  else {
            echo "Error adding category: " ;
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
      <a href="./categoriesupdate.php">Display</a>
      <form action="" method="post">
        
        <input type="text" name="categories_name" placeholder="Category Name" id="">
        
        <input type="text" name="categories_description" placeholder="Category Description" id="">
        <input type="text" name="categories_slug" placeholder="Category Slug" id="">
        <input type="submit" name="submit" value="Add ">
       </form>
      </main>
    </section>
    <script src="../assets/js/dashboard_style.js"></script>
</body>
</html>