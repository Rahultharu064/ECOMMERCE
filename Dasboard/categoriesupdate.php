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
    include '../Dasboard/Navbar.php'; ?>
    <section style="display: flex; width: 100%">
     <?php include '../Dasboard/Sidebar.php'; ?>
      <!-- //// main co -->
      <main class="content">
      <a href="./categoriesAdd.php">Add categoreies</a>

    <table>
        <tr>
            <th>id</th>
            <th>categories_name</th>
            <th>Action</th>
            
        </tr>
        <?php
        $sql = "SELECT * FROM categories";
        $result = mysqli_query($conn, $sql);
        $num = mysqli_num_rows($result);
        if($num>0){
            while($row = mysqli_fetch_assoc($result)){
                ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['categories_name']; ?></td>
                    <td></td>
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