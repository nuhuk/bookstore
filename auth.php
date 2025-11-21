<?php
// auth.php - handle login, register, logout via JSON API
header('Content-Type: application/json; charset=utf-8');
session_start();

require_once 'db.php';
$pdo = getPDO();

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'register':
        register($pdo);
        break;
    case 'login':
        login($pdo);
        break;
    case 'logout':
        logout();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid auth action']);
}

function register(PDO $pdo) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm']  ?? '';

    if ($username === '' || $password === '' || $confirm === '') {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        return;
    }
    if ($password !== $confirm) {
        echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
        return;
    }

    // Check if username exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :u");
    $stmt->execute([':u' => $username]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Username already taken']);
        return;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, password_hash) VALUES (:u, :p)");

    try {
        $stmt->execute([':u' => $username, ':p' => $hash]);
        echo json_encode(['success' => true, 'message' => 'Registration successful, please log in']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Registration failed']);
    }
}

function login(PDO $pdo) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        echo json_encode(['success' => false, 'message' => 'Username and password required']);
        return;
    }

    $stmt = $pdo->prepare("SELECT id, password_hash FROM users WHERE username = :u");
    $stmt->execute([':u' => $username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
        return;
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $username;

    echo json_encode(['success' => true, 'message' => 'Login successful', 'username' => $username]);
}

function logout() {
    session_start();
    session_unset();
    session_destroy();
    echo json_encode(['success' => true, 'message' => 'Logged out']);
}
