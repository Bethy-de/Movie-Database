<?php
$apikey = "865766";
$movie = urlencode($_GET['movie']);

$url = "http://www.omdbapi.com/?apikey=$apikey&s=$movie"; // "s=" returns list of matches
$response = file_get_contents($url);
$data = json_decode($response, true);

if($data['Response'] === "True"){
    foreach($data['Search'] as $movie){
        echo "
        <div class='movie-card'>
            <img src='{$movie['Poster']}' alt='{$movie['Title']}'>
            <div class='movie-info'>
                <h3>{$movie['Title']}</h3>
                <p>{$movie['Year']}</p>
                <form method='post' action='save_movie.php'>
                    <input type='hidden' name='imdb_id' value='{$movie['imdbID']}'>
                    <input type='hidden' name='title' value='{$movie['Title']}'>
                    <input type='hidden' name='year' value='{$movie['Year']}'>
                    <input type='hidden' name='poster' value='{$movie['Poster']}'>
                    <button type='submit'>❤️ Add to Favorites</button>
                </form>
            </div>
        </div>
        ";
    }


} else {
     echo "<p>No movies found 🎬</p>";
}
?>
