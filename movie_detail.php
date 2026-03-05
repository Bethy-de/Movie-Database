<?php
$imdb = $_GET['imdb'] ?? '';
if (!$imdb) die("Movie not found");
?>

<!DOCTYPE html>
<html>
<head>
    <title>🎬 Movie Database - Movie Details</title>
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

    <div id="movieDetail"></div>
</div>

<script>
const imdbID = "<?php echo $imdb; ?>";
const API_KEY = "865766";

fetch(`https://www.omdbapi.com/?i=${imdbID}&apikey=${API_KEY}`)
.then(res => res.json())
.then(movie => {
    document.getElementById("movieDetail").innerHTML = `
        <div class="movie-card">
            <img src="${movie.Poster}">
            <div>
                <h2>${movie.Title}</h2>
                <p><b>Year:</b> ${movie.Year}</p>
                <p><b>Genre:</b> ${movie.Genre}</p>
                <p>${movie.Plot}</p>
                <button onclick="addToFavorites('${movie.imdbID}')">❤️ Add to the Favourite</button>
            </div>
        </div>
    `;
});

function addToFavorites(imdbID){
    fetch("check_login.php", { credentials: 'same-origin', cache: 'no-store' })
        .then(res => res.json())
        .then(data => {
            if (!data.loggedIn) {
                alert("Sign in first");
                return;
            }
            fetch("add_favorite.php", {
                method: "POST",
                headers: {"Content-Type":"application/x-www-form-urlencoded"},
                body: `imdb_id=${imdbID}`
            }).then(res => {
                if (res.ok) {
                    alert("Added to favorites ❤️");
                } else {
                    alert("Error adding to favorites. Please try again.");
                }
            });
        })
        .catch(() => alert("Error checking login status"));
}

function updateAuthButtons() {
    fetch("check_login.php", { credentials: 'same-origin', cache: 'no-store' })
        .then(res => res.json())
        .then(data => {
            const authButton = document.getElementById("authButton");

            if (authButton) {
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
});
</script>

</body>
</html>
