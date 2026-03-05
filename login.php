<?php
include "config.php";

$message = "";
$success = "";
$isLoggedIn = isset($_SESSION['username']);

// If user is already logged in, redirect to home or show logout option
if($isLoggedIn && !isset($_POST['logout'])) {
    // User is logged in, we'll show logout option instead of login form
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    // Check if this is a logout request
    if(isset($_POST['logout'])) {
        session_destroy();
        header("Location: login.php");
        exit;
    }

    // Check if this is a signup or login request
    if(isset($_POST['email']) && !empty($_POST['email']) && isset($_POST['username']) && !empty($_POST['username'])) {
        // This is a signup request
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

                // Redirect to home so the session is active when the page loads
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
    // This is a login request
    if(isset($_POST['username']) && !empty($_POST['username']) && isset($_POST['password']) && !empty($_POST['password'])) {
        $username_input = mysqli_real_escape_string($conn, $_POST['username']);
        $password = $_POST['password'];

        $sql = "SELECT * FROM users WHERE LOWER(username) = LOWER('$username_input')";
        $result = mysqli_query($conn, $sql);

        if(mysqli_num_rows($result) > 0){
            $row = mysqli_fetch_assoc($result);
            if(password_verify($password, $row['password'])){
                $_SESSION['user_id'] = $row['id']; // store user id
                $_SESSION['username'] = $row['username']; // Use stored username

                // Server redirect to home ensures session cookie is active when the page loads
                // Add a short query flag so client forces a fresh auth check
                echo '<!DOCTYPE html><html><head></head><body><script>window.location.href = "index.html?auth=1";</script></body></html>';
                exit;
            } else {
                $message = "⚠ Invalid password";
            }
        } else {
            $message = "⚠ User not found";
        }
    }
}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>🎬 Movie Database - Sign In</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>🎥 Movie Database</h1>

    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="hamburger" id="hamburger">
            <span></span>
            <span></span>
            <span></span>
        </div>
        <div class="nav-menu" id="navMenu">
            <a href="index.html" class="nav-link">🏠 Home</a>
            <a href="browse.php" class="nav-link">🔍 Browse</a>
            <a href="favorites.php" class="nav-link">❤️ Favorites</a>
            <a href="#" id="authButton" class="nav-link auth-btn">🔑 Sign In</a>
        </div>
    </nav>

    <div class="auth-container">
        <?php if($isLoggedIn): ?>
        <!-- Logout Section for Logged In Users -->
        <div id="logoutSection" class="auth-section">
            <h2>👋 Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
            <p>You are already signed in.</p>
            <form method="post" action="">
                <button type="submit" name="logout" class="logout-btn">🚪 Sign Out</button>
            </form>
            <p><a href="index.html">← Back to Home</a></p>
            <hr>
            <p>Want to sign in with a different account? <a href="signin.php">Click here to sign in</a></p>
        </div>
        <?php else: ?>
        <!-- Not logged in message -->
        <div class="auth-section">
            <h2>🔑 Sign In Required</h2>
            <p>You are not currently signed in.</p>
            <p><a href="signin.php">Click here to sign in</a></p>
            <p><a href="index.html">← Back to Home</a></p>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Add button click functionality
document.addEventListener("DOMContentLoaded", function() {
    // Add hamburger menu functionality
    const hamburger = document.getElementById("hamburger");
    const navMenu = document.getElementById("navMenu");

    if (hamburger && navMenu) {
        hamburger.addEventListener("click", () => {
            hamburger.classList.toggle("active");
            navMenu.classList.toggle("active");
        });

        // Close mobile menu when clicking on a link
        navMenu.addEventListener("click", (e) => {
            if (e.target.classList.contains("nav-link")) {
                hamburger.classList.remove("active");
                navMenu.classList.remove("active");
            }
        });
    }

    const authButton = document.getElementById("authButton");

    if (authButton) {
        authButton.addEventListener("click", function(e) {
            e.preventDefault();

            // Check if user is logged in (include session cookie)
            fetch("check_login.php", { credentials: 'same-origin', cache: 'no-store' })
                .then(res => res.json())
                .then(data => {
                    if (data.loggedIn) {
                        // If logged in, go to logout page
                        window.location.href = "logout.php";
                    } else {
                        // If not logged in, go to sign in page
                        window.location.href = "signin.php";
                    }
                })
                .catch(() => {
                    // Default to sign in page if check fails
                    window.location.href = "signin.php";
                });
        });
    }

    function updateAuthButtons() {
        fetch("check_login.php", { credentials: 'same-origin', cache: 'no-store' })
            .then(res => res.json())
            .then(data => {
                const authButton = document.getElementById("authButton");
                if (!authButton) return;

                if (data.loggedIn) {
                    // Show first letter of username/displayName
                    const name = data.displayName || data.username || '';
                    authButton.textContent = name ? name.charAt(0).toUpperCase() : '';
                    authButton.title = "Logged in as " + (data.displayName || data.username) + " - Click to sign out";
                    authButton.classList.add("profile-button");
                } else {
                    authButton.textContent = "🔑 Sign In";
                    authButton.title = "Click to sign in";
                    authButton.classList.remove("profile-button");
                }
            })
            .catch(() => {});
    }

    updateAuthButtons();
});
</script>


</body>
</html>
