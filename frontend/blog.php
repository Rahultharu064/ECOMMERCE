<?php
// Database connection
require_once '../includes/config.php';
$db = new mysqli($host, $username, $password, $dbname, $port);
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Get parameters
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$catego_id = isset($_GET['catego']) ? intval($_GET['catego']) : null;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$per_page = 6;
$offset = ($page - 1) * $per_page;

// Fetch catego
$catego_query = "SELECT * FROM catego";
$catego_result = $db->query($catego_query);

// Build base query for featured articles
$featured_query = "SELECT a.*, c.name as catego_name 
                   FROM articles a 
                   JOIN catego c ON a.catego_id = c.id 
                   WHERE a.is_featured = 1";

// Add category filter if specified
if ($catego_id) {
    $featured_query .= " AND a.catego_id = $catego_id";
}

$featured_query .= " ORDER BY a.publish_date DESC LIMIT 2";
$featured_result = $db->query($featured_query);

// Build base query for latest articles
$latest_query = "SELECT a.*, c.name as catego_name 
                 FROM articles a 
                 JOIN catego c ON a.catego_id = c.id";

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

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM articles";
if ($catego_id) {
    $count_query .= " WHERE catego_id = $catego_id";
}
$total = $db->query($count_query)->fetch_assoc()['total'];
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
                <div class="catego-card" data-catego-id="<?= $catego['id'] ?>" style="background-color: <?= $catego['bg_color'] ?>">
                    <i class="<?= $catego['icon'] ?>"></i>
                    <h3><?= $catego['name'] ?></h3>
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
                    <div class="article-image" style="background-image: url('<?= $article['image_url'] ?>');">
                        <span class="badge"><?= $article['is_new'] ? 'New' : 'Popular' ?></span>
                    </div>
                    <div class="article-content">
                        <span class="catego"><?= $article['catego_name'] ?></span>
                        <h3><?= $article['title'] ?></h3>
                        <p class="excerpt"><?= $article['excerpt'] ?></p>
                        <div class="article-meta">
                            <span class="author"><?= $article['author'] ?></span>
                            <span class="date"><?= date('F j, Y', strtotime($article['publish_date'])) ?></span>
                            <span class="read-time"><?= $article['read_time'] ?> min read</span>
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
                        htmlspecialchars($db->query("SELECT name FROM catego WHERE id = $catego_id")->fetch_assoc()['name']) . ' Articles' : 
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
                    <div class="article-image" style="background-image: url('<?= $article['image_url'] ?>');"></div>
                    <div class="article-info">
                        <span class="catego"><?= $article['catego_name'] ?></span>
                        <h3><?= $article['title'] ?></h3>
                        <p class="excerpt"><?= $article['excerpt'] ?></p>
                        <div class="article-meta">
                            <span class="date"><?= date('F j, Y', strtotime($article['publish_date'])) ?></span>
                            <span class="read-time"><?= $article['read_time'] ?> min read</span>
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

    <script src="js/script.js">
        document.addEventListener('DOMContentLoaded', function() {
    // Sort articles functionality
    const sortSelect = document.getElementById('sort-articles');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            const sortValue = this.value;
            fetchSortedArticles(sortValue);
        });
    }

    // Search functionality
    const searchForm = document.querySelector('.search-bar form');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const query = this.querySelector('input').value.trim();
            if (query) {
                window.location.href = `search.php?query=${encodeURIComponent(query)}`;
            }
        });
    }

    // Newsletter subscription
    const newsletterForm = document.querySelector('.newsletter-form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const emailInput = this.querySelector('input');
            const email = emailInput.value.trim();
            
            if (email && validateEmail(email)) {
                subscribeToNewsletter(email)
                    .then(data => {
                        if (data.success) {
                            showNotification('Thank you for subscribing!', 'success');
                            emailInput.value = '';
                        } else {
                            showNotification(data.message || 'Subscription failed. Please try again.', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('An error occurred. Please try again.', 'error');
                    });
            } else {
                showNotification('Please enter a valid email address', 'error');
            }
        });
    }

    // Pagination buttons
    document.querySelectorAll('.pagination button').forEach(button => {
        button.addEventListener('click', function() {
            if (!this.classList.contains('active')) {
                const page = this.textContent.trim();
                if (page) {
                    window.location.href = `blog.php?page=${page}`;
                }
            }
        });
    });

    // Category cards click event
    document.querySelectorAll('.catego-card').forEach(card => {
        card.addEventListener('click', function() {
            const categoId = this.dataset.categoId;
            if (categoId) {
                window.location.href = `blog.php?catego=${categoId}`;
            }
        });
    });

    // Initialize any sliders
    initFeaturedSlider();
});

// Fetch sorted articles via AJAX
function fetchSortedArticles(sortValue) {
    const url = new URL(window.location.href);
    url.searchParams.set('sort', sortValue);
    
    fetch(`sort_articles.php?${url.searchParams.toString()}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateArticlesGrid(data.articles);
            } else {
                showNotification('Failed to sort articles', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while sorting articles', 'error');
        });
}

// Update articles grid with new data
function updateArticlesGrid(articles) {
    const articlesGrid = document.querySelector('.articles-grid');
    if (!articlesGrid) return;

    articlesGrid.innerHTML = articles.map(article => `
        <article class="article-card">
            <div class="article-image" style="background-image: url('${article.image_url}');"></div>
            <div class="article-info">
                <span class="catego">${article.catego_name}</span>
                <h3>${article.title}</h3>
                <p class="excerpt">${article.excerpt}</p>
                <div class="article-meta">
                    <span class="date">${formatDate(article.publish_date)}</span>
                    <span class="read-time">${article.read_time} min read</span>
                </div>
                <a href="article.php?id=${article.id}" class="read-more">Read Article <i class="fas fa-arrow-right"></i></a>
            </div>
        </article>
    `).join('');
}

// Initialize featured articles slider
function initFeaturedSlider() {
    // This would be implemented with a slider library like Slick or Swiper
    // For simplicity, we're just showing the basic structure
    const slider = document.querySelector('.featured-slider');
    if (slider) {
        // Initialize slider with your preferred library
        // Example: $(slider).slick({...});
    }
}

// Subscribe to newsletter
function subscribeToNewsletter(email) {
    return fetch('subscribe.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `email=${encodeURIComponent(email)}`
    }).then(response => response.json());
}

// Validate email format
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Format date for display
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('en-US', options);
}

// Show notification message
function showNotification(message, type = 'success') {
    // Remove any existing notifications
    const existingNotification = document.querySelector('.notification');
    if (existingNotification) {
        existingNotification.remove();
    }

    // Create and show new notification
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    document.body.appendChild(notification);

    // Auto-hide after 5 seconds
    setTimeout(() => {
        notification.classList.add('fade-out');
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}
    </script>
</body>
</html> 