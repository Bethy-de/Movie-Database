<?php
include "config.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>🎬 Movie Database - Browse</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <h1>🔍 Browse Movies</h1>

    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="hamburger" id="hamburger">
            <span></span>
            <span></span>
            <span></span>
        </div>
        <div class="nav-menu" id="navMenu">
            <a href="index.html" class="nav-link">🏠 Home</a>
            <a href="browse.php" class="nav-link active">🔍 Browse</a>
            <a href="favorites.php" class="nav-link">❤️ Favorites</a>
            <a href="#" id="authButton" class="nav-link auth-btn">🔑 Sign In</a>
        </div>
    </nav>

    <div class="search-container">
        <input id="movieSearch" placeholder="Search movies...">
        <div id="searchResults"></div>
    </div>

    <div id="result"></div>

    <div id="popularMovies" class="movie-grid">
        <h2>🎬 Popular Movies</h2>
        <!-- Popular movies will be loaded here -->
    </div>
</div>

<script>
const OMDB_API_KEY = "865766";
let searchTimeout = null;

// Load popular movies on page load
document.addEventListener("DOMContentLoaded", () => {
    loadPopularMovies();
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

// =========================
// AUTH STATUS
// =========================
function updateAuthButtons() {
    // Send credentials so session cookie is included and the server can verify the session
    fetch("check_login.php", { credentials: 'same-origin', cache: 'no-store' })
        .then(res => res.json())
        .then(data => {
            const authButton = document.getElementById("authButton");
            const authDropdown = document.getElementById("authDropdown");
            const logoutOption = document.getElementById("logoutOption");

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

// =========================
// SEARCH FUNCTIONALITY
// =========================
const searchInput = document.getElementById("movieSearch");
const resultsContainer = document.getElementById("searchResults");
const resultBox = document.getElementById("result");

if (searchInput) {
    searchInput.addEventListener("input", () => {
        const query = searchInput.value.trim();
        clearTimeout(searchTimeout);

        if (query.length < 2) {
            resultsContainer.innerHTML = "";
            return;
        }

        searchTimeout = setTimeout(() => {
            liveSearch(query);
        }, 400);
    });
}

async function liveSearch(query) {
    try {
        const res = await fetch(
            `https://www.omdbapi.com/?s=${encodeURIComponent(query)}&apikey=${OMDB_API_KEY}`
        );
        const data = await res.json();

        resultsContainer.innerHTML = "";

        if (data.Response === "True") {
            data.Search.forEach(movie => {
                const div = document.createElement("div");
                div.className = "search-item";
                div.innerHTML = `<strong>${movie.Title}</strong> (${movie.Year})`;
                div.onclick = () => loadMovieDetails(movie.imdbID);
                resultsContainer.appendChild(div);
            });
        } else {
            resultsContainer.innerHTML = `<div class="no-result">No movies found</div>`;
        }
    } catch (err) {
        console.error("Search error:", err);
    }
}

async function loadMovieDetails(imdbID) {
    resultsContainer.innerHTML = "";
    searchInput.value = "";

    const res = await fetch(
        `https://www.omdbapi.com/?i=${imdbID}&apikey=${OMDB_API_KEY}`
    );
    const movie = await res.json();

    resultBox.innerHTML = `
        <div class="movie-card">
            <img src="${movie.Poster !== "N/A" ? movie.Poster : "no-image.png"}">
            <div>
                <h2>${movie.Title}</h2>
                <p><b>Year:</b> ${movie.Year}</p>
                <p><b>Genre:</b> ${movie.Genre}</p>
                <p><b>Plot:</b> ${movie.Plot}</p>

                <button onclick="addToFavorites('${movie.imdbID}')">❤️ Add to Favorites</button>
            </div>
        </div>
    `;

    // Save to recent searches
    saveRecent(imdbID);
}

// =========================
// POPULAR MOVIES
// =========================
async function loadPopularMovies() {
    const popularTitles = ["Avengers", "Batman", "Spider-Man", "Star Wars", "Harry Potter"];

    const popularContainer = document.getElementById("popularMovies");
    let html = "<h2>🎬 Popular Movies</h2><div class='recent-container'>";

    for (const title of popularTitles) {
        try {
            const res = await fetch(`https://www.omdbapi.com/?s=${title}&apikey=${OMDB_API_KEY}`);
            const data = await res.json();

            if (data.Response === "True" && data.Search && data.Search[0]) {
                const movie = data.Search[0];
                html += `
                    <div class='recent-card'>
                        <img src='${movie.Poster !== "N/A" ? movie.Poster : "no-image.png"}' onclick="loadMovieDetails('${movie.imdbID}')" style='cursor:pointer;'>
                        <div class='recent-info'>
                            <h3>${movie.Title}</h3>
                            <p>${movie.Year}</p>
                        </div>
                    </div>
                `;
            }
        } catch (err) {
            console.error("Error loading popular movie:", err);
        }
    }

    html += "</div>";
    popularContainer.innerHTML = html;
}

// =========================
// UTILITY FUNCTIONS
// =========================
window.saveRecent = function (imdbID) {
    fetch("save_recent.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `imdb_id=${imdbID}`
    }).then(() => {
        // Optional: Show success message
    });
};

window.addToFavorites = function (imdbID) {
    fetch("check_login.php", { credentials: 'same-origin', cache: 'no-store' })
        .then(res => res.json())
        .then(data => {
            if (!data.loggedIn) {
                alert("Sign in first");
                return;
            }
            fetch("add_favorite.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
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
};
</script>

</body>
</html>
