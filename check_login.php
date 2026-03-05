<?php
include "config.php";

header('Content-Type: application/json');

if(isset($_SESSION['user_id'])){
    // Provide a displayName formatted for UI (capitalized first letter) without modifying stored value
    $username = isset($_SESSION['username']) ? $_SESSION['username'] : '';
    // Capitalize first character for display, preserve the rest as-is
    $display = '';
    if ($username !== '') {
        $display = mb_strtoupper(mb_substr($username, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($username, 1, mb_strlen($username, 'UTF-8') - 1, 'UTF-8');
    }
    echo json_encode(["loggedIn" => true, "username" => $username, "displayName" => $display]);
} else {
    echo json_encode(["loggedIn" => false]);
}
?>
