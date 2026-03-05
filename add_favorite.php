<?php
include "config.php";

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}

$imdb = $_POST['imdb_id'];
$user = $_SESSION['user_id'];

// First, get movie details from OMDB API
$json = file_get_contents("https://www.omdbapi.com/?i=$imdb&apikey=865766");
$movie = json_decode($json, true);

if ($movie && $movie['Response'] === "True") {
    $title = mysqli_real_escape_string($conn, $movie['Title']);
    $year = mysqli_real_escape_string($conn, $movie['Year']);
    $poster = mysqli_real_escape_string($conn, $movie['Poster'] !== "N/A" ? $movie['Poster'] : "no-image.png");

    // Insert favorite with full details
    $sql = "INSERT INTO favorites (user_id, imdb_id, title, year, poster) VALUES ('$user', '$imdb', '$title', '$year', '$poster')
            ON DUPLICATE KEY UPDATE created_at = CURRENT_TIMESTAMP";

    $conn->query($sql);
    echo "success";
} else {
    echo "error";
}
?>
