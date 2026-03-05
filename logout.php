<?php
include "config.php";

// Check if this is a POST request to actually logout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_logout'])) {
    session_destroy();
    header("Location: index.html");
    exit;
}

// If user is not logged in, redirect to home
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>🎬 Movie Database - Logout</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <h1>🎬 Movie Database</h1>

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
        <div class="auth-section logout-section">
            <div class="welcome-message">
                <h3>👋 Welcome back!</h3>
                <p>Thanks for using Movie Database</p>
            </div>

            <h2>🚪 Confirm Logout</h2>
            <p>Are you sure you want to log out of your account?</p>

            <div class="logout-actions">
                <form method="POST" id="logoutForm" style="display: inline;">
                    <button type="submit" name="confirm_logout" value="1" class="logout-btn confirm-btn" id="confirmBtn">
                        <span class="btn-icon">🚪</span>
                        <span class="btn-text">Yes, Logout</span>
                    </button>
                </form>

                <button class="logout-btn cancel-btn" id="cancelBtn" onclick="cancelLogout()">
                    <span class="btn-icon">↩️</span>
                    <span class="btn-text">Cancel</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
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

    // Update auth button
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

    // Handle auth button click
    const authButton = document.getElementById("authButton");
    if (authButton) {
        authButton.addEventListener("click", (e) => {
            e.preventDefault();

            fetch("check_login.php", { credentials: 'same-origin', cache: 'no-store' })
                .then(res => res.json())
                .then(data => {
                    if (data.loggedIn) {
                        window.location.href = "logout.php";
                    } else {
                        window.location.href = "login.php";
                    }
                })
                .catch(() => {
                    window.location.href = "login.php";
                });
        });
    }

    // Simple cancel logout function
    window.cancelLogout = function() {
        // Add a nice animation before going back
        document.querySelector('.logout-section').style.animation = 'fadeOutDown 0.5s ease-in';
        setTimeout(() => {
            history.back();
        }, 300);
    };

    // Add fade out animation CSS
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeOutDown {
            from {
                opacity: 1;
                transform: translateY(0);
            }
            to {
                opacity: 0;
                transform: translateY(30px);
            }
        }
    `;
    document.head.appendChild(style);
});
</script>

</body>
</html>
