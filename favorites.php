<?php
include "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get favorite movies with stored details
$sql = "SELECT * FROM favorites WHERE user_id = '$user_id' ORDER BY created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>🎬 Movie Database - My Favorites</title>
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
            <a href="favorites.php" class="nav-link active">❤️ Favorites</a>
            <a href="#" id="authButton" class="nav-link auth-btn">🔑 Sign In</a>
        </div>
    </nav>

    <h2>❤️ My Favorite Movies</h2>

    <div class="recent-container">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "
                <div class='recent-card'>
                    <img src='".($row['poster'] ?: "no-image.png")."' onclick=\"window.location.href='movie_detail.php?imdb={$row['imdb_id']}'\" style='cursor:pointer;'>
                    <div class='recent-info'>
                        <h3>{$row['title']}</h3>
                        <p>{$row['year']}</p>
                    </div>
                </div>
                ";
            }
        } else {
            echo "<p>No favorite movies yet ❤️</p>";
        }
        ?>
    </div>
</div>

<script>
function updateAuthButtons() {
    fetch("check_login.php", { credentials: 'same-origin', cache: 'no-store' })
        .then(res => res.json())
        .then(data => {
            const authButton = document.getElementById("authButton");
            const authDropdown = document.getElementById("authDropdown");
            const logoutOption = document.getElementById("logoutOption");

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

document.addEventListener("DOMContentLoaded", () => {
    updateAuthButtons();

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

    // Add auth button functionality
    const authButton = document.getElementById("authButton");

    if (authButton) {
        authButton.addEventListener("click", (e) => {
            e.preventDefault();

            // Check if user is logged in
            fetch("check_login.php", { credentials: 'same-origin', cache: 'no-store' })
                .then(res => res.json())
                .then(data => {
                    if (data.loggedIn) {
                        // If logged in, go to logout page
                        window.location.href = "logout.php";
                    } else {
                        // If not logged in, go to sign in page
                        window.location.href = "login.php";
                    }
                })
                .catch(() => {
                    // Default to sign in page if check fails
                    window.location.href = "login.php";
                });
        });
    }
});
</script>

</body>
</html>
