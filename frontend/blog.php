<?php
// Database connection
require_once '../includes/config.php';
$db = new mysqli($host, $username, $password, $dbname, $port);
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get parameters
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$catego_id = isset($_GET['catego']) ? intval($_GET['catego']) : null;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$per_page = 6;
$offset = ($page - 1) * $per_page;

// Fetch categories
$catego_query = "SELECT * FROM cat";
$catego_result = $db->query($catego_query);

if (!$catego_result) {
    die("Error fetching categories: " . $db->error);
}

// Build base query for featured articles
$featured_query = "SELECT a.*, c.name as catego_name 
                   FROM art a 
                   JOIN cat c ON a.catego_id = c.id 
                   WHERE a.is_featured = 1";

// Add category filter if specified
if ($catego_id) {
    $featured_query .= " AND a.catego_id = $catego_id";
}

$featured_query .= " ORDER BY a.publish_date DESC LIMIT 2";
$featured_result = $db->query($featured_query);

if (!$featured_result) {
    die("Error fetching featured articles: " . $db->error);
}

// Build base query for latest articles
$latest_query = "SELECT a.*, c.name as catego_name 
                 FROM art a 
                 JOIN cat c ON a.catego_id = c.id";

// Add category filter if specified
if ($catego_id) {
    $latest_query .= " WHERE a.catego_id = $catego_id";
}

// Add sorting
switch ($sort) {
    case 'oldest':
        $latest_query .= " ORDER BY a.publish_date ASC";
        break;
    case 'popular':
        $latest_query .= " ORDER BY a.views DESC";
        break;
    default: // newest
        $latest_query .= " ORDER BY a.publish_date DESC";
}

// Add pagination
$latest_query .= " LIMIT $per_page OFFSET $offset";
$latest_result = $db->query($latest_query);

if (!$latest_result) {
    die("Error fetching latest articles: " . $db->error);
}

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM art";
if ($catego_id) {
    $count_query .= " WHERE catego_id = $catego_id";
}
$count_result = $db->query($count_query);
if (!$count_result) {
    die("Error executing count query: " . $db->error);
}
$total = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total / $per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy Insights & Health Tips</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/frontendcss/blog.css">
</head>
<body>
    <section class="blog-hero">
        <div class="container">
            <h1>Pharmacy Insights & Health Tips</h1>
            <p>Expert advice from our licensed pharmacists to help you make informed health decisions</p>
            <div class="search-bar">
                <form action="search.php" method="GET">
                    <input type="text" name="query" placeholder="Search blog articles...">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>
        </div>
    </section>

    <section class="blog-catego">
        <div class="container">
            <h2>Browse by Category</h2>
            <div class="catego-grid">
                <?php while($catego = $catego_result->fetch_assoc()): ?>
                <div class="catego-card" data-catego-id="<?= htmlspecialchars($catego['id']) ?>" style="background-color: <?= htmlspecialchars($catego['bg_color']) ?>">
                    <i class="<?= htmlspecialchars($catego['icon']) ?>"></i>
                    <h3><?= htmlspecialchars($catego['name']) ?></h3>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <?php if ($featured_result->num_rows > 0): ?>
    <section class="featured-articles">
        <div class="container">
            <h2>Featured Articles</h2>
            <div class="featured-slider">
                <?php while($article = $featured_result->fetch_assoc()): ?>
                <div class="featured-article">
                    <div class="article-image" style="background-image: url('<?= htmlspecialchars($article['image_url']) ?>');">
                        <span class="badge"><?= $article['is_new'] ? 'New' : 'Popular' ?></span>
                    </div>
                    <div class="article-content">
                        <span class="catego"><?= htmlspecialchars($article['catego_name']) ?></span>
                        <h3><?= htmlspecialchars($article['title']) ?></h3>
                        <p class="excerpt"><?= htmlspecialchars($article['excerpt']) ?></p>
                        <div class="article-meta">
                            <span class="author"><?= htmlspecialchars($article['author']) ?></span>
                            <span class="date"><?= date('F j, Y', strtotime($article['publish_date'])) ?></span>
                            <span class="read-time"><?= htmlspecialchars($article['read_time']) ?> min read</span>
                        </div>
                        <a href="article.php?id=<?= $article['id'] ?>" class="read-more">Read Article <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <section class="all-articles">
        <div class="container">
            <div class="section-header">
                <h2>
                    <?= $catego_id ? 
                        htmlspecialchars($db->query("SELECT name FROM cat WHERE id = $catego_id")->fetch_assoc()['name']) . ' Articles' : 
                        'Latest Articles' ?>
                </h2>
                <div class="sort-options">
                    <span>Sort by:</span>
                    <select id="sort-articles">
                        <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest First</option>
                        <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>Oldest First</option>
                        <option value="popular" <?= $sort === 'popular' ? 'selected' : '' ?>>Most Popular</option>
                    </select>
                </div>
            </div>
            <div class="articles-grid">
                <?php while($article = $latest_result->fetch_assoc()): ?>
                <article class="article-card">
                    <div class="article-image" style="background-image: url('<?= htmlspecialchars($article['image_url']) ?>');"></div>
                    <div class="article-info">
                        <span class="catego"><?= htmlspecialchars($article['catego_name']) ?></span>
                        <h3><?= htmlspecialchars($article['title']) ?></h3>
                        <p class="excerpt"><?= htmlspecialchars($article['excerpt']) ?></p>
                        <div class="article-meta">
                            <span class="date"><?= date('F j, Y', strtotime($article['publish_date'])) ?></span>
                            <span class="read-time"><?= htmlspecialchars($article['read_time']) ?> min read</span>
                        </div>
                        <a href="article.php?id=<?= $article['id'] ?>" class="read-more">Read Article <i class="fas fa-arrow-right"></i></a>
                    </div>
                </article>
                <?php endwhile; ?>
            </div>
            
            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                <a href="blog.php?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>"><i class="fas fa-chevron-left"></i></a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="blog.php?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                <a href="blog.php?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>"><i class="fas fa-chevron-right"></i></a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="newsletter">
        <div class="container">
            <div class="newsletter-content">
                <h2>Stay Updated with Health Insights</h2>
                <p>Subscribe to our newsletter for the latest pharmacy news, health tips, and exclusive offers.</p>
                <form class="newsletter-form" action="subscribe.php" method="POST">
                    <input type="email" name="email" placeholder="Enter your email address" required>
                    <button type="submit">Subscribe</button>
                </form>
            </div>
        </div>
    </section>

    <script src="../assets/js/blog.js"></script>
</body>
</html>