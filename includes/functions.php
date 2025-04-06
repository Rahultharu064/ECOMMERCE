<?php
// functions.php
require_once 'config.php';

function getRecentArticles($limit = 5) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT a.*, c.name as category_name, c.slug as category_slug, 
                          au.name as author_name, au.avatar as author_avatar
                          FROM articles a 
                          JOIN categories c ON a.category_id = c.id
                          JOIN authors au ON a.author_id = au.id
                          WHERE a.published_at IS NOT NULL AND a.published_at <= NOW()
                          ORDER BY a.published_at DESC 
                          LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getArticleBySlug($slug) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT a.*, c.name as category_name, c.slug as category_slug, 
                          au.name as author_name, au.bio as author_bio, au.avatar as author_avatar
                          FROM articles a 
                          JOIN categories c ON a.category_id = c.id
                          JOIN authors au ON a.author_id = au.id
                          WHERE a.slug = ?");
    $stmt->execute([$slug]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getArticlesByCategory($category_id, $limit = 10) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT a.*, c.name as category_name, c.slug as category_slug, 
                          au.name as author_name, au.avatar as author_avatar
                          FROM articles a 
                          JOIN categories c ON a.category_id = c.id
                          JOIN authors au ON a.author_id = au.id
                          WHERE a.category_id = ? AND a.published_at IS NOT NULL AND a.published_at <= NOW()
                          ORDER BY a.published_at DESC 
                          LIMIT ?");
    $stmt->execute([$category_id, $limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAllCategories() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getPopularArticles($limit = 5) {
    // In a real implementation, you would track views and sort by popularity
    // For now, we'll just get recent articles
    return getRecentArticles($limit);
}

function formatDate($date) {
    return date('F j, Y', strtotime($date));
}
?>