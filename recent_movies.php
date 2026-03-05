<?php
include "config.php";

if(!isset($_SESSION['user_id'])) exit;

$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM recent_searches WHERE user_id='$user_id' ORDER BY searched_at DESC LIMIT 6";
$result = mysqli_query($conn, $sql);

if(mysqli_num_rows($result) > 0){
    echo "<div class='recent-container'>";
    while($row = mysqli_fetch_assoc($result)){
        echo "
        <div class='recent-card'>
            <img src='{$row['poster']}' alt='{$row['title']}' onclick=\"loadMovieDetails('{$row['imdb_id']}')\">
            <div class='recent-info'>
                <h3>{$row['title']}</h3>
                <p>{$row['year']}</p>
            </div>
        </div>
        ";
    }
    echo "</div>";

} else {
    echo "<p>No recent searches yet 🔍</p>";
}
?>
