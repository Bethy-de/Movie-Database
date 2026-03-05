document.addEventListener("DOMContentLoaded", () => {

    // =========================
    // ELEMENT REFERENCES
    // =========================
    const searchInput = document.getElementById("movieSearch");
    const resultsContainer = document.getElementById("searchResults");
    const resultBox = document.getElementById("result");
    const favoritesBtn = document.getElementById("favoritesBtn");
    const homeBtn = document.getElementById("homeBtn");

    const OMDB_API_KEY = "865766";
    let searchTimeout = null;

    // =========================
    // AUTH BUTTONS
    // =========================
    updateAuthButtons();
    loadRecentMovies();

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

    // If redirected after signup/login include a short auth flag, force a fresh check
    try {
        const params = new URLSearchParams(window.location.search);
        if (params.has('auth')) {
            // Force another auth check that bypasses cache
            updateAuthButtons();
            // Remove the flag from the URL for cleanliness
            params.delete('auth');
            const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '') + window.location.hash;
            history.replaceState(null, '', newUrl);
        }
    } catch (e) {
        // ignore URL parsing errors
    }

    if (favoritesBtn) {
        favoritesBtn.addEventListener("click", () => {
            window.location.href = "favorites.php";
        });
    }

    // =========================
    // LIVE SEARCH (MAIN FIX)
    // =========================
    if (searchInput) {
        searchInput.addEventListener("input", () => {
            const query = searchInput.value.trim();
            console.log("Typing:", query);

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
    // HOME BUTTON
    // =========================
    window.goHome = function () {
        resultBox.innerHTML = "";
        if (searchInput) searchInput.value = "";
        loadRecentMovies();
    };

    // =========================
    // RECENT MOVIES
    // =========================
    function loadRecentMovies() {
        fetch("recent_movies.php")
            .then(res => res.text())
            .then(data => {
                document.getElementById("recentMovies").innerHTML = data;
            });
    }

    window.saveRecent = function (imdbID) {
        fetch("save_recent.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `imdb_id=${imdbID}`
        }).then(() => loadRecentMovies());
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

    window.loadMovieDetails = async function (imdbID) {
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
    };

});

// =========================
function updateAuthButtons() {
    // Include credentials so the session cookie is sent with the request; bypass cache
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

// Add button click functionality
// MOVED TO MAIN DOMContentLoaded EVENT ABOVE
