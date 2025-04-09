<?php
$searchResults = [];
$searchQuery = '';
$error = '';

// DB config
require_once "../includes/config.php";

if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["q"])) {
    $searchQuery = trim($_GET["q"]);
    
    if (!empty($searchQuery)) {
        try {
            // SQL: search products by name, category or description
            $stmt = $conn->prepare("
                SELECT p.*, c.name AS category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.name LIKE CONCAT('%', ?, '%')
                OR c.name LIKE CONCAT('%', ?, '%')
                OR p.description LIKE CONCAT('%', ?, '%')
                ORDER BY p.name ASC
            ");
            $stmt->bind_param("sss", $searchQuery, $searchQuery, $searchQuery);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $searchResults[] = $row;
            }
        } catch (Exception $e) {
            $error = "Error searching products: " . $e->getMessage();
        } finally {
            if (isset($stmt)) $stmt->close();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy Search</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f9f9f9;
            padding: 20px;
        }

        .search-container-1 {
            position: relative;
            width: 90%;
            max-width: 500px;
            margin: 0 auto;
        }

        .search-input {
            width: 100%;
            padding: 12px 30px 12px 45px;
            border: none;
            border-radius: 30px;
            font-size: 16px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            outline: none;
        }

        .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #888;
        }

        .results {
            max-width: 500px;
            margin: 20px auto;
        }

        .result-item {
            background: #ffffff;
            padding: 12px 16px;
            border-radius: 10px;
            box-shadow: 0 1px 6px rgba(0,0,0,0.1);
            margin-bottom: 12px;
            display: flex;
            gap: 15px;
        }

        .result-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
        }

        .result-content {
            flex: 1;
        }

        .result-item strong {
            font-size: 18px;
            display: block;
            margin-bottom: 5px;
        }

        .result-item small {
            color: #666;
            display: block;
            margin-bottom: 3px;
        }

        .error {
            color: #d9534f;
            text-align: center;
            margin: 20px 0;
        }
    </style>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

    <form method="get" action="search.php">
        <div class="search-container-1">
            <i class="fas fa-search search-icon"></i>
            <input type="text" name="q" class="search-input" placeholder="Search medicines..." value="<?php echo htmlspecialchars($searchQuery); ?>">
        </div>
    </form>

    <?php if (!empty($error)): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php elseif (!empty($searchQuery)): ?>
        <div class="results">
            <h3>Results for "<?php echo htmlspecialchars($searchQuery); ?>"</h3>
            <?php if (empty($searchResults)): ?>
                <p>No products found.</p>
            <?php else: ?>
                <?php foreach ($searchResults as $product): ?>
                    <div class="result-item">
                        <?php if (!empty($product['image'])): ?>
                            <img src="../uploads/products/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="result-image">
                        <?php endif; ?>
                        <div class="result-content">
                            <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                            <small>Category: <?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></small>
                            <small>Price: $<?php echo number_format($product['price'], 2); ?></small>
                            <small><?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?>...</small>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</body>
</html>
