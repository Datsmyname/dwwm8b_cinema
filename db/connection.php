<?php
/* Connexion à une base MySQL avec l'invocation de pilote */
$dsn_db = 'mysql:dbname=cinema;host=127.0.0.1';
$user_db = 'root';
$password_db = '';

try {
    $db = new PDO($dsn_db, $user_db, $password_db);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

?>