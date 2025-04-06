<?php
// Database connection
require_once '../includes/config.php';
$db = new mysqli($host, $username, $password, $dbname, $port);
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Get article ID
$article_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch article
$article_query = "SELECT a.*, c.name as catego_name 
                 FROM articles a 
                 JOIN catego c ON a.catego_id = c.id 
                 WHERE a.id = ?";
$stmt = $db->prepare($article_query);
$stmt->bind_param("i", $article_id);
$stmt->execute();
$article = $stmt->get_result()->fetch_assoc();

// Increment view count if article exists
if ($article) {
    $update_views = "UPDATE articles SET views = views + 1 WHERE id = ?";
    $stmt = $db->prepare($update_views);
    $stmt->bind_param("i", $article_id);
    $stmt->execute();

    // Fetch related articles (same category, excluding current article)
    $related_query = "SELECT a.*, c.name as catego_name 
                     FROM articles a 
                     JOIN catego c ON a.catego_id = c.id 
                     WHERE a.catego_id = ? AND a.id != ?
                     ORDER BY a.publish_date DESC 
                     LIMIT 3";
    $stmt = $db->prepare($related_query);
    $stmt->bind_param("ii", $article['catego_id'], $article_id);
    $stmt->execute();
    $related_articles = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($article['title'] ?? 'Article Not Found') ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/frontendcss/blog.css">
    <style>
        /* Enhanced Article Styles */
        .article-header {
            padding: 60px 0 40px;
            background-color: #f8f9fa;
            margin-bottom: 40px;
        }
        
        .article-header .container {
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
        }
        
        .article-header .catego {
            display: inline-block;
            background-color: #e9ecef;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            margin-bottom: 15px;
            color: #495057;
        }
        
        .article-header h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
            line-height: 1.3;
        }
        
        .article-meta {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 15px;
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .article-content {
            max-width: 800px;
            margin: 0 auto 60px;
        }
        
        .article-image {
            width: 100%;
            height: 400px;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .article-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .content {
            line-height: 1.8;
            font-size: 1.1rem;
        }
        
        .content img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 20px 0;
        }
        
        /* Related Articles Section */
        .related-articles {
            background-color: #f8f9fa;
            padding: 60px 0;
        }
        
        .related-articles h2 {
            text-align: center;
            margin-bottom: 40px;
            font-size: 2rem;
        }
        
        .articles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .article-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .article-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .article-card .article-image {
            height: 200px;
            margin-bottom: 0;
            border-radius: 0;
            box-shadow: none;
        }
        
        .article-info {
            padding: 20px;
        }
        
        .article-info .catego {
            display: inline-block;
            background-color: #e9ecef;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            margin-bottom: 10px;
            color: #495057;
        }
        
        .article-info h3 {
            margin: 10px 0;
            font-size: 1.2rem;
            line-height: 1.4;
        }
        
        .article-info .excerpt {
            color: #6c757d;
            margin-bottom: 15px;
            font-size: 0.9rem;
            line-height: 1.6;
        }
        
        .article-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            font-size: 0.8rem;
            color: #6c757d;
            margin-bottom: 15px;
        }
        
        .read-more {
            color: #0077b6;
            text-decoration: none;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            transition: color 0.3s ease;
        }
        
        .read-more:hover {
            color: #005b8c;
        }
        
        .read-more i {
            margin-left: 5px;
            transition: transform 0.3s ease;
        }
        
        .read-more:hover i {
            transform: translateX(3px);
        }
        
        /* Article Not Found */
        .article-not-found {
            text-align: center;
            padding: 100px 0;
        }
        
        .article-not-found h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
        }
        
        .article-not-found p {
            font-size: 1.2rem;
            margin-bottom: 30px;
            color: #6c757d;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #0077b6;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        
        .btn:hover {
            background-color: #005b8c;
        }
        
        @media (max-width: 768px) {
            .article-header h1 {
                font-size: 2rem;
            }
            
            .article-image {
                height: 300px;
            }
            
            .articles-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php if ($article): ?>
    <section class="article-header">
        <div class="container">
            <span class="catego"><?= htmlspecialchars($article['catego_name']) ?></span>
            <h1><?= htmlspecialchars($article['title']) ?></h1>
            <div class="article-meta">
                <span class="author"><?= htmlspecialchars($article['author']) ?></span>
                <span class="date"><?= date('F j, Y', strtotime($article['publish_date'])) ?></span>
                <span class="read-time"><?= $article['read_time'] ?> min read</span>
                <span class="views"><?= $article['views'] ?> views</span>
            </div>
        </div>
    </section>

    <section class="article-content">
        <div class="container">
            <div class="article-image">
                <img src="<?= htmlspecialchars($article['image_url']) ?>" alt="<?= htmlspecialchars($article['title']) ?>">
            </div>
            <div class="content">
                <?= $article['content'] ?>
            </div>
        </div>
    </section>

    <?php if (!empty($related_articles)): ?>
    <section class="related-articles">
        <div class="container">
            <h2>Related Articles</h2>
            <div class="articles-grid">
                <?php foreach ($related_articles as $related): ?>
                <article class="article-card">
                    <div class="article-image">
                        <img src="<?= htmlspecialchars($related['image_url']) ?>" alt="<?= htmlspecialchars($related['title']) ?>">
                    </div>
                    <div class="article-info">
                        <span class="catego"><?= htmlspecialchars($related['catego_name']) ?></span>
                        <h3><?= htmlspecialchars($related['title']) ?></h3>
                        <p class="excerpt"><?= htmlspecialchars($related['excerpt']) ?></p>
                        <div class="article-meta">
                            <span class="date"><?= date('F j, Y', strtotime($related['publish_date'])) ?></span>
                            <span class="read-time"><?= $related['read_time'] ?> min read</span>
                        </div>
                        <a href="article.php?id=<?= $related['id'] ?>" class="read-more">Read Article <i class="fas fa-arrow-right"></i></a>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php else: ?>
    <section class="article-not-found">
        <div class="container">
            <h1>Article Not Found</h1>
            <p>The requested article could not be found.</p>
            <a href="blog.php" class="btn">Back to Blog</a>
        </div>
    </section>
    <?php endif; ?>

    <script src="../assets/js/script.js"></script>
</body>
</html>