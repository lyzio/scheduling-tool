<?php
// Hantera formulärinmatning
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST['password'];

    // Kontrollera lösenordet (anpassa detta efter ditt system)
    if ($password === 'your_secret_password') {
        $_SESSION['loggedin'] = true;
        setcookie('loggedin', true, time() + (86400 * 30), "/"); // Sätt kaka för att hålla användaren inloggad i 30 dagar
        header('Location: edit_schedule.php');
        exit;
    } else {
        $login_error = "Incorrect password. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        body {
            background-color: #f1f1f1;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background-color: #d2dae2;
            width: 40%;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .login-container input[type="password"] {
            width: 80%;
            padding: 10px;
            margin-top: 20px;
            border: none;
            border-radius: 5px;
            background-color: #ff5e57;
            color: #1e272e;
            font-size: 16px;
        }
        .login-container button {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #ff5e57;
            color: #1e272e;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .login-container button:hover {
            background-color: #ff3b30;
        }
        .error-message {
            color: #ff3b30;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <?php if (isset($login_error)): ?>
            <p class="error-message"><?php echo htmlspecialchars($login_error); ?></p>
        <?php endif; ?>
        <form method="POST" action="">
            <input type="password" name="password" placeholder="Enter your password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
