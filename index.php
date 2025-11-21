<?php
session_start();
require_once 'db.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Guest button pressed
    if (isset($_POST['guest'])) {
        $_SESSION['user_id'] = null;           // no DB user
        $_SESSION['username'] = 'Guest';
        $_SESSION['role'] = 'guest';           // <-- key flag
        header("Location: dashboard.php");
        exit;
    }

    // Normal login
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username == '' || $password == '') {
        $error = "Please enter both fields.";
    } else {
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $error = "Invalid username or password.";
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = 'user';        // <-- full access
            header("Location: dashboard.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Login</title>
<link rel="stylesheet" href="css/styles.css">
</head>
<body>
<div class="login-container">
  <h2>Book Store Login</h2>

  <?php if ($error): ?>
    <div class="alert error"><?= $error ?></div>
  <?php endif; ?>

  <form method="POST">
  <label>Username</label>
  <input type="text" name="username">

  <label>Password</label>
  <input type="password" name="password">

  <!-- Main login button -->
  <button class="btn btn-primary" type="submit" name="login" value="1">
    Login
  </button>

  <!-- Guest button -->
  <button class="btn btn-secondary" type="submit" name="guest" value="1" style="margin-top:8px;">
    Continue as Guest
  </button>

  <p>Don't have an account? <a href="register.php">Register</a></p>
  </form>
</div>
</body>
</html>
