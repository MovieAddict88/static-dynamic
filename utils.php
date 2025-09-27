<?php
// Function to get or create a genre and return its ID
function get_or_create_genre($link, $genre_name) {
    $genre_stmt = mysqli_prepare($link, "SELECT id FROM genres WHERE name = ?");
    mysqli_stmt_bind_param($genre_stmt, "s", $genre_name);
    mysqli_stmt_execute($genre_stmt);
    $result = mysqli_stmt_get_result($genre_stmt);
    if ($row = mysqli_fetch_assoc($result)) {
        return $row['id'];
    } else {
        $insert_genre_stmt = mysqli_prepare($link, "INSERT INTO genres (name) VALUES (?)");
        mysqli_stmt_bind_param($insert_genre_stmt, "s", $genre_name);
        mysqli_stmt_execute($insert_genre_stmt);
        return mysqli_insert_id($link);
    }
}
?>