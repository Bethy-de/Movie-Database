<?php
include "config.php";

$message = "";
$success = "";

// If user is already logged in, redirect to home
if(isset($_SESSION['username'])) {
    header("Location: index.html");
    exit;
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    // Distinguish signup (email present) vs login
    if(isset($_POST['email']) && !empty($_POST['email']) && isset($_POST['username']) && !empty($_POST['username'])) {
        // Signup flow
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        
        // Validate username (letters only)
        if (!preg_match('/^[a-zA-Z]+$/', $_POST['username'])) {
            $message = "⚠ Username must contain only letters (no numbers or special characters)!";
            echo "<script>alert('Username must contain only letters (no numbers or special characters)!');</script>";
        } else {
            $email    = mysqli_real_escape_string($conn, $_POST['email']);

        // Validate password strength
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};\':"\\\\|,.<>\/?]).{8,}$/', $_POST['password'])) {
            $message = "⚠ Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, one number, and one special character!";
            echo "<script>alert('Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, one number, and one special character!');</script>";
        } else {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

            // Check if username or email exists
            $check_username = "SELECT * FROM users WHERE LOWER(username) = LOWER('$username')";
            $check_email = "SELECT * FROM users WHERE LOWER(email) = LOWER('$email')";

            $username_result = mysqli_query($conn, $check_username);
            $email_result = mysqli_query($conn, $check_email);

            if(mysqli_num_rows($username_result) > 0 && mysqli_num_rows($email_result) > 0){
                $message = "⚠ Account already exists! Please sign in instead.";
                echo "<script>alert('Account already exists! Please sign in instead.');</script>";
            } elseif(mysqli_num_rows($username_result) > 0){
                $message = "⚠ Username already exists!";
                echo "<script>alert('Username already exists!');</script>";
            } elseif(mysqli_num_rows($email_result) > 0){
                $message = "⚠ Email already exists! Please sign in instead.";
                echo "<script>alert('Email already exists! Please sign in instead.');</script>";
            } else {
                $sql = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$password')";

                if(mysqli_query($conn, $sql)){
                    $_SESSION['user_id'] = mysqli_insert_id($conn); // store logged-in user id
                    $_SESSION['username'] = $username;

                    // Redirect immediately to home so session is active there
                    // Add a short query flag so client forces a fresh auth check
                    echo '<!DOCTYPE html><html><head></head><body><script>window.location.href = "index.html?auth=1";</script></body></html>';
                    exit;
                } else {
                    $message = "Error: " . mysqli_error($conn);
                }
            }
        }
    }
} else {
    // Login flow
    if(isset($_POST['username']) && !empty($_POST['username']) && isset($_POST['password']) && !empty($_POST['password'])) {
        $username_input = isset($_POST['username']) ? mysqli_real_escape_string($conn, $_POST['username']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';

        $sql = "SELECT * FROM users WHERE LOWER(username) = LOWER('$username_input')";
        $result = mysqli_query($conn, $sql);

        if(mysqli_num_rows($result) > 0){
            $row = mysqli_fetch_assoc($result);
            if(password_verify($password, $row['password'])){
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];  // Use the stored username

                // Redirect to home so session is active
                echo '<!DOCTYPE html><html><head></head><body><script>window.location.href = "index.html?auth=1";</script></body></html>';
                exit;
            } else {
                $message = "⚠ Invalid password";
            }
        } else {
            $message = "⚠ Username not found";
        }
    }
}
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>🎬 Movie Database - Sign In</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <h1>🎥 Movie Database</h1>

    <!-- Navigation Bar -->
    <nav class="navbar">
        <a href="index.html" class="nav-link">🏠 Home</a>
        <a href="browse.php" class="nav-link">🔍 Browse</a>
        <a href="favorites.php" class="nav-link">❤️ Favorites</a>
        <a href="#" id="authButton" class="nav-link auth-btn">🔑 Sign In</a>
    </nav>

    <div class="auth-container">
        <!-- Login Form -->
        <div id="loginSection" class="auth-section">
            <h2>🔑 Sign In</h2>
            <?php if($message && $_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['email'])) echo "<p class='error'>$message</p>"; ?>
            <form method="post" action="">
                <input type="text" name="username" placeholder="Username" required><br><br>
                <input type="password" name="password" placeholder="Password" required><br><br>
                <button type="submit">Sign In</button>
            </form>
            <p>Don't have an account? <a href="#" onclick="showSignup()">Sign Up</a></p>
        </div>

        <!-- Signup Form -->
        <div id="signupSection" class="auth-section" style="display:none;">
            <h2>📝 Sign Up</h2>
            <?php if($message && isset($_POST['email'])) echo "<p class='error'>$message</p>"; ?>
            <?php if($success && isset($_POST['email'])) echo "<p class='success'>$success</p>"; ?>
            <form method="post" action="">
                <input type="text" name="username" placeholder="Username" required><br><br>
                <input type="email" name="email" placeholder="Email" required><br><br>
                <input type="password" name="password" placeholder="Password" required><br><br>
                <button type="submit">Sign Up</button>
            </form>
            <p>Already have an account? <a href="#" onclick="showLogin()">Sign In</a></p>
        </div>
    </div>
</div>

<script>
function showLogin() {
    document.getElementById("loginSection").style.display = "block";
    document.getElementById("signupSection").style.display = "none";
}

function showSignup() {
    document.getElementById("loginSection").style.display = "none";
    document.getElementById("signupSection").style.display = "block";
}

function updateAuthButtons() {
    fetch("check_login.php", { credentials: 'same-origin', cache: 'no-store' })
        .then(res => res.json())
        .then(data => {
            const authButton = document.getElementById("authButton");
            if (!authButton) return;

            if (data.loggedIn) {
                const name = data.displayName || data.username || '';
                authButton.textContent = name ? name.charAt(0) : '';
                authButton.title = "Logged in as " + (data.displayName || data.username);
            } else {
                authButton.textContent = "🔑 Sign In";
                authButton.title = "";
            }
        })
        .catch(() => {});
}

// Add button click functionality
document.addEventListener("DOMContentLoaded", function() {
    const authButton = document.getElementById("authButton");

    if (authButton) {
        authButton.addEventListener("click", function(e) {
            e.preventDefault();
            // Do nothing on signin page
        });
    }

    updateAuthButtons();

    // Check if URL has #signup hash
    if (window.location.hash === '#signup') {
        showSignup();
    }
});
</script>

</body>
</html>