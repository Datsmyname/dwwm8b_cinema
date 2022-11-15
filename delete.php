<?php 
session_start();
// Si aucun id de film n'a été reçu via la methode GET
if (!isset($_GET['film_id']) || empty($_GET['film_id'])) 
{
    // On effectue une redirection vers la page d'acceuil
    // + arreter execution du script
    return header("Location: index.php");
}

// Dans le cas contraire
// Recuperer id du film de GET
$filmId = strip_tags($_GET['film_id']);
//echo $filmId;

// Convertir id du film pour etre sur de travailler avec un int
$filmId = (int) $filmId;

// Etablir connexion
require __DIR__ . "/db/connection.php";

// Effectuer requete de selection pour verifier si match celui de l'url
$req = $db->prepare("SELECT * FROM film WHERE id = :id");
$req-> bindValue(":id", $filmId);
$req-> execute();
$count = $req-> rowCount();

// Si nombre total d'enregistrements recupéré n'egal pas 1
// Arreter automatiquement l'execution du script
if ($count != 1) 
{
    return header("Location: index.php");
}

// Dans le cas contraire recuperer le film en question
$film = $req-> fetch();

// et fermer le curseur
$req-> closeCursor();

// Effectuer une seconde requete pour suppr le film
$req = $db-> prepare("DELETE FROM film WHERE id = :id ");
$req-> bindValue(":id", $film["id"]);
$req-> execute();
$req-> closeCursor();

// Generer le massage flash
$_SESSION["success"] = $film['name'] . " a été retiré de la liste";

// Effectuer la redirection vers home page et arreter le script
return header("Location: index.php");


?>