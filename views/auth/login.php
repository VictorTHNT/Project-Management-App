<?php
// login.php
session_start();
require '../../includes/connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        if ($user['activation'] == 'oui') {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nom'] = $user['nom'];
            $_SESSION['prenom'] = $user['prenom'];
            $_SESSION['role'] = $user['role']; // Ajout du rôle dans la session
            header('Location: ../../dashboard.php');
            exit;
        } else {
            $login_error = "Your account is not activated. Please contact the administrator.";
        }
    } else {
        $login_error = "Invalid credentials!";
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
        font-family: Arial, sans-serif;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
        background-color: #696969;
    }
    .container {
        background-color: #fff;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 400px;
        box-sizing: border-box;
        text-align: center;
    }
    h1 {
        color: #333;
        margin-bottom: 20px;
        font-size: 24px;
        font-weight: bold;
    }
    .form-group {
        margin-bottom: 20px;
        position: relative;
        text-align: left;
    }
    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: #555;
    }
    .form-group input {
        width: 100%;
        padding: 10px;
        padding-right: 40px;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-sizing: border-box;
        font-size: 16px;
        color: #333;
    }
    .form-group .icon {
        position: absolute;
        right: 10px;
        top: 70%;
        transform: translateY(-50%);
        font-size: 18px;
        color: #aaa;
        pointer-events: none;
    }
    .form-group button {
        width: 100%;
        padding: 10px;
        background: linear-gradient(90deg, #696969, #D3D3D3);
        border: none;
        border-radius: 4px;
        color: white;
        font-size: 16px;
        cursor: pointer;
        transition: background-color 0.3s ease, box-shadow 0.3s ease;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .form-group button:hover {
        background: linear-gradient(90deg, #696969, #D3D3D3);
        box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
    }
    .message {
        margin-bottom: 15px;
        padding: 10px;
        border-radius: 4px;
        border: 1px solid transparent;
    }
    .message.error {
        border-color: #f44336;
        color: #f44336;
        background-color: #fdecea;
    }
    .message.success {
        border-color: #4caf50;
        color: #4caf50;
        background-color: #e8f5e9;
    }
    p {
        color: #555;
        margin-top: 20px;
    }
    p a {
        color: #007bff;
        text-decoration: none;
    }
    p a:hover {
        text-decoration: underline;
    }
    .toggle-buttons {
        display: flex;
        justify-content: center;
        margin-bottom: 20px;
    }
    .toggle-buttons button {
        background: white;
        border: none;
        border-radius: 20px;
        color: linear-gradient(90deg, #696969, #D3D3D3);
        font-size: 16px;
        cursor: pointer;
        margin: 5px;
        padding: 10px 20px;
        transition: opacity 0.3s ease, box-shadow 0.3s ease;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .toggle-buttons button.active {
        background: linear-gradient(90deg, #696969, #D3D3D3);
        color:  white;
        font-weight: bold;
    }
    .toggle-buttons button:not(.active):hover {
        opacity: 0.8;
    }
    .form-container {
        display: none;
    }
    .form-container.active {
        display: block;
    }
</style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>


<body>


    <div class="container">
        <div class="toggle-buttons">
            <button class="active" onclick="location.href='login.php'">Log In</button>
            <button onclick="location.href='register.php'">Register</button>
        </div>
        
        <div class="form-container active">
            <h1>Login</h1>
            <?php if (isset($login_error)): ?>
                <div class="message error"><?php echo $login_error; ?></div>
            <?php endif; ?>
            <form method="POST" action="login.php">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                    <i class="fas fa-envelope icon"></i>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    <i class="fas fa-lock icon"></i>
                </div>
                
                <div class="form-group">
                    <button type="submit" name="login">Log In</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
