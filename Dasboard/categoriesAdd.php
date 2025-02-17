<?php 
 include '../includes/config.php';

 if(isset($_POST['submit'])){

    $categories_name = $_POST['categories_name'];
   
    $sql = "INSERT INTO categories (categories_name	) VALUES ('$categories_name	')";
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
        <input type="submit" name="submit" value="Add ">
       </form>
      </main>
    </section>
    <script src="../assets/js/dashboard.js"></script>
</body>
</html>