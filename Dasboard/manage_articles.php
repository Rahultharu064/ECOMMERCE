<?php
session_start();
include '../includes/config.php';




$db = new mysqli($host, $username, $password, $dbname, $port);
if($db){
    // echo "Connected to database";
}



// Check connection
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Handle search and pagination
$search = isset($_GET['search']) ? $db->real_escape_string($_GET['search']) : '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Build base query
$query = "SELECT a.id, a.title, a.publish_date, a.views, c.name as catego_name 
          FROM articles a 
          JOIN catego c ON a.catego_id = c.id";

// Add search condition if provided
if (!empty($search)) {
    $query .= " WHERE a.title LIKE '%$search%' OR a.excerpt LIKE '%$search%' OR a.content LIKE '%$search%'";
}

// Add sorting and pagination
$query .= " ORDER BY a.publish_date DESC LIMIT $per_page OFFSET $offset";

$articles_result = $db->query($query);

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM articles";
if (!empty($search)) {
    $count_query .= " WHERE title LIKE '%$search%' OR excerpt LIKE '%$search%' OR content LIKE '%$search%'";
}
$total = $db->query($count_query)->fetch_assoc()['total'];
$total_pages = ceil($total / $per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Articles</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'admin_nav.php'; ?>
    
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Manage Articles</h2>
            <a href="create_blog.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Create New Article
            </a>
        </div>
        
        <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success_message'] ?></div>
        <?php unset($_SESSION['success_message']); endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error_message'] ?></div>
        <?php unset($_SESSION['error_message']); endif; ?>
        
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="manage_articles.php" class="row g-3">
                    <div class="col-md-9">
                        <input type="text" name="search" class="form-control" placeholder="Search articles..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">Search</button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Publish Date</th>
                                <th>Views</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($article = $articles_result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($article['title']) ?></td>
                                <td><?= $article['catego_name'] ?></td>
                                <td><?= date('M j, Y', strtotime($article['publish_date'])) ?></td>
                                <td><?= $article['views'] ?></td>
                                <td>
                                    <a href="../frontend/edit_blog.php?id=<?= $article['id'] ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete_article.php?id=<?= $article['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this article?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="manage_articles.php?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="manage_articles.php?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="manage_articles.php?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>