<?php
// Redirect to login page with signup section
header("Location: login.php#signup");
exit;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>🎬 Movie Database - Sign Up</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>🎥 Movie Database</h1>

    <!-- Navigation Bar -->
    <nav class="navbar">
        <a href="index.html" class="nav-link">🏠 Home</a>
        <a href="browse.php" class="nav-link">🔍 Browse</a>
        <a href="favorites.php" id="favoritesLink" class="nav-link" style="display:none;">❤️ Favorites</a>
        <a href="login.php" id="loginLink" class="nav-link">🔑 Sign In</a>
        <a href="signup.php" id="signupLink" class="nav-link active">📝 Sign Up</a>
        <a href="logout.php" id="logoutLink" class="nav-link" style="display:none;">🚪 Log Out</a>
    </nav>

    <h2>📝 Sign Up</h2>

    <!-- Display success message -->
    <?php if($success) echo "<p class='success'>$success</p>"; ?>

    <form method="post" action="">
        <input type="text" name="username" placeholder="Username" required><br><br>
        <input type="email" name="email" placeholder="Email" required><br><br>
        <input type="password" name="password" placeholder="Password" required><br><br>
        <button type="submit">Sign Up</button>
    </form>

    <p>Already have an account? <a href="login.php">Sign In</a></p>
</div>

<script>
function updateAuthButtons() {
    fetch("check_login.php", { credentials: 'same-origin', cache: 'no-store' })
        .then(res => res.json())
        .then(data => {
            const loginLink = document.getElementById("loginLink");
            const signupLink = document.getElementById("signupLink");
            const logoutLink = document.getElementById("logoutLink");
            const favoritesLink = document.getElementById("favoritesLink");

            if (loginLink && signupLink && logoutLink && favoritesLink) {
                if (data.loggedIn) {
                    loginLink.style.display = "none";
                    signupLink.style.display = "none";
                    logoutLink.style.display = "inline-block";
                    favoritesLink.style.display = "inline-block";
                } else {
                    loginLink.style.display = "inline-block";
                    signupLink.style.display = "inline-block";
                    logoutLink.style.display = "none";
                    favoritesLink.style.display = "none";
                }
            }
        })
        .catch(() => {});
}

document.addEventListener("DOMContentLoaded", updateAuthButtons);
</script>

</body>
</html>
