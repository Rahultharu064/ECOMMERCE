<?php
function clean_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}

function get_initials($name) {
    $names = explode(' ', $name);
    $initials = '';
    foreach ($names as $n) {
        $initials .= strtoupper(substr($n, 0, 1));
    }
    return substr($initials, 0, 2);
}

function get_specialty_icon($specialty) {
    $icons = [
        'Cardiology' => 'fa-heart',
        'Dermatology' => 'fa-allergies',
        'Pediatrics' => 'fa-baby',
        'Neurology' => 'fa-brain',
        'General Practice' => 'fa-user-md',
        'Orthopedics' => 'fa-bone',
        'Psychiatry' => 'fa-brain',
        'Gynecology' => 'fa-female',
        'Oncology' => 'fa-bacterium'
    ];
    return $icons[$specialty] ?? 'fa-user-md';
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function redirect_if_not_logged_in() {
    if (!is_logged_in()) {
        header("Location: login.php");
        exit();
    }
}

function require_role($required_role) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $required_role) {
        header("Location: ../frontend/Homepage.php");
        exit();
    }
}

function send_notification($user_id, $title, $message) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $title, $message);
    $stmt->execute();
}
?>