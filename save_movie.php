<?php
include "config.php";

if(!isset($_SESSION['username'])){
    echo "<p>⚠ You must log in first!</p>";
    echo "<a href='login.php'>Log In</a>";
    exit;
}


$imdb_id = $_POST['imdb_id'];
$title   = $_POST['title'];
$year    = $_POST['year'];
$poster  = $_POST['poster'];
$user    = $_SESSION['username']; // store which user added
$user_id = $_SESSION['user_id']; // logged-in user

// Check duplicates for this user
$check = "SELECT * FROM favorites WHERE imdb_id='$imdb_id' AND user_id='$user_id'";
$result = mysqli_query($conn, $check);
if(mysqli_num_rows($result) > 0){
    header("Location: index.php");
    exit;
}

// Insert favorite
$sql = "INSERT INTO favorites (imdb_id, title, year, poster, user_id)
        VALUES ('$imdb_id', '$title', '$year', '$poster', '$user_id')";
mysqli_query($conn, $sql);
header("Location: index.php");
exit;

// Prevent duplicate favorites
$check = "SELECT * FROM favorites WHERE imdb_id = '$imdb_id'";
$result = mysqli_query($conn, $check);

if (mysqli_num_rows($result) > 0) {
    echo "<h2>⚠ Movie already in favorites</h2>";
    echo "<a href='index.html'>Go Back</a>";
    exit;
}

$sql = "INSERT INTO favorites (imdb_id, title, year, poster)
        VALUES ('$imdb_id', '$title', '$year', '$poster')";

if (mysqli_query($conn, $sql)) {
    echo "<h2>❤️ Movie added to favorites!</h2>";
    echo "<a href='index.html'>Go Back</a>";
} else {
    echo "Error: " . mysqli_error($conn);
}
?>
