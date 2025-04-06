<?php
session_start();

// 1. Database connection
include '../includes/config.php';




// 3. Get post slug from URL
$slug = isset($_GET['slug']) ? mysqli_real_escape_string($conn, $_GET['slug']) : null;

if (!$slug) {
    header("Location:blog.php");
    exit();
}

// 4. Get post data with category information
$query = "SELECT p.*, u.name as author_name, u.email as author_email, u.role,
          c.categories_name as category_name, c.categories_slug as category_slug 
          FROM posts p 
          JOIN users u ON p.author_id = u.id 
          JOIN categories c ON p.category_id = c.id 
          WHERE p.slug = ? AND p.published = 1";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $slug);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$post = mysqli_fetch_assoc($result);

if (!$post) {
    header("Location: ../blog.php");
    exit();
}

// 5. Increment view count
$updateQuery = "UPDATE posts SET views = views + 1 WHERE id = ?";
$updateStmt = mysqli_prepare($conn, $updateQuery);
mysqli_stmt_bind_param($updateStmt, "i", $post['id']);
mysqli_stmt_execute($updateStmt);

// 6. Get related posts
$relatedQuery = "SELECT p.id, p.title, p.slug, p.image, p.created_at 
                 FROM posts p 
                 WHERE p.category_id = ? AND p.published = 1 AND p.id != ? 
                 ORDER BY p.created_at DESC 
                 LIMIT 3";
$relatedStmt = mysqli_prepare($conn, $relatedQuery);
mysqli_stmt_bind_param($relatedStmt, "ii", $post['category_id'], $post['id']);
mysqli_stmt_execute($relatedStmt);
$relatedResult = mysqli_stmt_get_result($relatedStmt);
$relatedPosts = mysqli_fetch_all($relatedResult, MYSQLI_ASSOC);

// 7. Set meta information
$pageTitle = $post['title'];
$metaTitle = $post['meta_title'] ?: $post['title'];
$metaDescription = $post['meta_description'] ?: $post['excerpt'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($metaTitle); ?> | PharmaCare</title>
    <meta name="description" content="<?php echo htmlspecialchars($metaDescription); ?>">
    
    <!-- Open Graph / Social Media Meta Tags -->
    <meta property="og:title" content="<?php echo htmlspecialchars($metaTitle); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($metaDescription); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($post['image']); ?>">
    <meta property="og:url" content="<?php echo "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>">
    <meta property="og:type" content="article">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($metaTitle); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($metaDescription); ?>">
    <meta name="twitter:image" content="<?php echo htmlspecialchars($post['image']); ?>">
    
    <!-- Favicon -->
    <link rel="icon" href="../assets/images/favicon.png" type="image/png">
    
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/font-awesome.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/pharmacy.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #4a9dcc;
            --secondary-color: #2d7ba6;
            --accent-color: #ff7e5f;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --text-color: #495057;
            --heading-color: #212529;
        }
        
        .post-header {
            background: linear-gradient(rgba(74, 157, 204, 0.8), rgba(74, 157, 204, 0.8)), url('../assets/images/post-bg.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 80px 0;
            margin-bottom: 100px;
            text-align: center;
        }
        
        .post-header h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
        }
        
        .post-meta {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
        
        .post-meta span {
            margin: 0 15px;
        }
        
        .post-meta i {
            margin-right: 5px;
            color: var(--accent-color);
        }
        
        .post-content {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        .post-content img {
            max-width: 100%;
            height: auto;
            margin: 30px 0;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .post-content h2, 
        .post-content h3 {
            margin-top: 40px;
            margin-bottom: 20px;
            color: var(--secondary-color);
        }
        
        .post-content p {
            margin-bottom: 20px;
            line-height: 1.8;
        }
        
        .post-tags {
            margin: 40px 0;
            padding: 20px 0;
            border-top: 1px solid #eee;
            border-bottom: 1px solid #eee;
        }
        
        .post-tags a {
            display: inline-block;
            background: var(--light-color);
            color: var(--text-color);
            padding: 5px 15px;
            margin-right: 10px;
            margin-bottom: 10px;
            border-radius: 20px;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .post-tags a:hover {
            background: var(--primary-color);
            color: white;
        }
        
        .author-box {
            display: flex;
            align-items: center;
            background: var(--light-color);
            padding: 30px;
            border-radius: 10px;
            margin: 40px 0;
        }
        
        .author-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 30px;
            border: 3px solid var(--primary-color);
        }
        
        .author-info h4 {
            margin-bottom: 10px;
            color: var(--heading-color);
        }
        
        .author-info p {
            margin-bottom: 15px;
            color: var(--text-color);
        }
        
        .social-share {
            margin: 40px 0;
        }
        
        .social-share a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: var(--light-color);
            color: var(--text-color);
            margin-right: 15px;
            transition: all 0.3s ease;
            text-decoration: none;
            font-size: 1.2rem;
        }
        
        .social-share a:hover {
            color: white;
            transform: translateY(-3px);
        }
        
        .social-share .facebook:hover { background: #3b5998; }
        .social-share .twitter:hover { background: #1da1f2; }
        .social-share .linkedin:hover { background: #0077b5; }
        .social-share .pinterest:hover { background: #bd081c; }
        
        .related-posts {
            margin: 60px 0;
        }
        
        .related-posts h3 {
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .related-post-card {
            border: none;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            margin-bottom: 30px;
            height: 100%;
        }
        
        .related-post-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .related-post-img {
            height: 180px;
            object-fit: cover;
        }
        
        .related-post-body {
            padding: 20px;
        }
        
        .related-post-title {
            font-size: 1.1rem;
            margin-bottom: 15px;
        }
        
        @media (max-width: 768px) {
            .post-header h1 {
                font-size: 2rem;
            }
            
            .author-box {
                flex-direction: column;
                text-align: center;
            }
            
            .author-avatar {
                margin-right: 0;
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include '../includes/header.php'; ?>
    
    <!-- Post Header -->
    <section class="post-header">
        <div class="container">
            <a href="../category/<?php echo htmlspecialchars($post['category_slug']); ?>" class="badge bg-primary mb-3">
                <?php echo htmlspecialchars($post['category_name']); ?>
            </a>
            <h1><?php echo htmlspecialchars($post['title']); ?></h1>
            <div class="post-meta">
                <span><i class="fa fa-user"></i> <?php echo htmlspecialchars($post['author_name']); ?></span>
                <span><i class="fa fa-calendar"></i> <?php echo date('F j, Y', strtotime($post['created_at'])); ?></span>
                <span><i class="fa fa-eye"></i> <?php echo number_format($post['views'] + 1); ?> views</span>
            </div>
        </div>
    </section>
    
    <!-- Post Content -->
    <section class="post-content">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <?php if ($post['image']): ?>
                        <img src="../uploads/posts/<?php echo htmlspecialchars($post['image']); ?>" class="img-fluid rounded mb-5" alt="<?php echo htmlspecialchars($post['title']); ?>">
                    <?php endif; ?>
                    
                    <article class="post-body">
                        <?php echo $post['content']; ?>
                    </article>
                    
                    <!-- Tags -->
                    <div class="post-tags">
                        <strong><i class="fa fa-tags"></i> Tags:</strong>
                        <a href="#">Pharmacy</a>
                        <a href="#">Health</a>
                        <a href="#"><?php echo htmlspecialchars($post['category_name']); ?></a>
                        <a href="#">Medication</a>
                    </div>
                    
                    <!-- Social Share -->
                    <div class="social-share">
                        <strong>Share this article:</strong>
                        <!-- Include Font Awesome CDN for icons -->
                        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
                        
                        <a href="#" class="facebook" title="Share on Facebook"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="twitter" title="Share on Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="linkedin" title="Share on LinkedIn"><i class="fab fa-linkedin"></i></a>
                        <a href="#" class="pinterest" title="Share on Pinterest"><i class="fab fa-pinterest"></i></a>
                    </div>
                    
                    <!-- Author Box -->
                    <div class="author-box">
                        <!-- <img src="../assets/posts/<?php echo ($post['role'] === 'pharmacist') ? 'pharmacist.jpg' : 'customer.jpg'; ?>" class="author-avatar" alt="<?php echo htmlspecialchars($post['author_name']); ?>"> -->
                        <div class="author-info">
                            <h4>About <?php echo htmlspecialchars($post['author_name']); ?></h4>
                            <p><?php echo ($post['role'] === 'pharmacist') ? 'Licensed pharmacist with over 10 years of experience in patient care and medication management.' : 'Health enthusiast sharing valuable insights and research.'; ?></p>
                            <a href="mailto:<?php echo htmlspecialchars($post['author_email']); ?>" class="btn btn-primary">Contact Author</a>
                        </div>
                    </div>
                    
                    <!-- Related Posts -->
                    <?php if (!empty($relatedPosts)): ?>
                        <div class="related-posts">
                            <h3>You May Also Like</h3>
                            <div class="row">
                                <?php foreach ($relatedPosts as $relatedPost): ?>
                                    <div class="col-md-4">
                                        <div class="related-post-card">
                                            <img src="../uploads/posts/<?php echo htmlspecialchars($relatedPost['image']); ?>" class="card-img-top related-post-img" alt="<?php echo htmlspecialchars($relatedPost['title']); ?>">
                                            <div class="related-post-body">
                                                <h5 class="related-post-title"><?php echo htmlspecialchars($relatedPost['title']); ?></h5>
                                                <a href="../post/<?php echo htmlspecialchars($relatedPost['slug']); ?>" class="btn btn-sm btn-primary">Read More</a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>
    
    <!-- JavaScript -->
    <script src="../assets/js/jquery-3.5.1.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/pharmacy.js"></script>
    
    <script>
        // Social share buttons
        $(document).ready(function() {
            const url = encodeURIComponent(window.location.href);
            const title = encodeURIComponent("<?php echo addslashes($post['title']); ?>");
            const image = encodeURIComponent("<?php echo htmlspecialchars($post['image']); ?>");
            
            $('.social-share .facebook').attr('href', `https://www.facebook.com/sharer/sharer.php?u=${url}`);
            $('.social-share .twitter').attr('href', `https://twitter.com/intent/tweet?url=${url}&text=${title}`);
            $('.social-share .linkedin').attr('href', `https://www.linkedin.com/shareArticle?mini=true&url=${url}&title=${title}`);
            $('.social-share .pinterest').attr('href', `https://pinterest.com/pin/create/button/?url=${url}&media=${image}&description=${title}`);
        });
    </script>
</body>
</html>