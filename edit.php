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
 

// Si les données du formulaire ont été envoyées via la méthode "POST"
if ($_SERVER['REQUEST_METHOD'] === "POST") {

    $post_clean = [];
    $edit_formError = [];

    // Protéger le serveur contre les failles de type XSS une première fois
    foreach ($_POST as $key => $value) 
    {
        $post_clean[$key] = strip_tags(trim($value));
    }

    
    // Mettre en place les constraintes de validation des données du formulaire
    
    // Pour le nom du film
    if (isset($post_clean["name"])) 
    {
       if (empty($post_clean["name"])) //required
       {
            $edit_formError["name"] = "Le nom du film est obligatoire.";
       }
       else if(mb_strlen($post_clean["name"]) > 255)
       {
            $edit_formError["name"] = "Film doit contenir 255 caracteres max";
       }
    }


    // Pour le nom du ou des acteurs
    if (isset($post_clean["actors"])) 
    {
        if (empty($post_clean["actors"])) // required
        {
            $edit_formError["actors"] = "Le nom des acteurs est obligatoire";
        }
        else if (mb_strlen($post_clean["actors"]) > 255) 
        {
            $edit_formError["actors"] = "Le nom des acteurs doit contenir max 255 caracteres";
        }
    }   
    
    // Pour la note
    if (isset($post_clean["review"])) 
    {
       if (is_string($post_clean["review"]) && ($post_clean["review"] == ''))
       {
            $edit_formError["review"] = "La note est obligatoire";
       } 
       else if(empty($post_clean["review"]) && ($post_clean["review"]) != 0) 
       {
            $edit_formError["review"] = "La note est obligatoire.";
       }
       elseif (!is_numeric($post_clean["review"])) 
       {
            $edit_formError["review"] = "La note doit être un nombre";
       }
       elseif (($post_clean["review"] < 0) || ($post_clean["review"] > 5)) 
       {
            $edit_formError["review"] = "La note doit être comprise entre 0 et 5";
       }
       
    }
    
    // S'il y a des erreurs,
    if (count($edit_formError) > 0) 
    {
        // Stocker les messages d'erreurs en session
        $_SESSION["create_form_errors"] = $edit_formError;

        // Stocker les données provenant du formulaire en session
        $_SESSION["old"] = $post_clean;

        // Rediriger l'utilisateur vers la page de laquelle proviennent les données
        // J'arrête l'exécution du script
        return header("Location: " . $_SERVER["HTTP_REFERER"]);
    }
    
    
        // Dans le cas contraire,
        
    // Protéger le serveur contre les failles de type XSS une seconde fois
    $final_postClean = [];
    foreach ($post_clean as $key => $value) {
        $final_postClean[$key] = htmlspecialchars($value);
    }

    $filmName = $final_postClean["name"];
    $filmActor = $final_postClean["actors"];
    $filmReview = $final_postClean["review"];

    // Arrondir la note = un chiffre apres la virgule
    $filmReview_rounded = round($filmReview, 1);

    // Etablir une connexion avec la base de données
    require __DIR__ . "/db/connection.php";

    // Effectuer la requête de modification des données dans la table film de la base données
    $req = $db->prepare("UPDATE film SET name=:name, actors=:actors, review=:review, updated_at=now() WHERE id=:id");


    $req->bindValue(":name", $filmName);
    $req->bindValue(":actors", $filmActor);
    $req->bindValue(":review", $filmReview_rounded);
    $req->bindValue(":id", $film["id"]);

    $req->execute();
    $req->closeCursor();

    // Generation d'un message flash
    $_SESSION["success"] = "Le film a été modifié";

    // Rediriger l'utilisateur vers la page d'accueil

    // Arrêter l'execution du script.
    return header("Location: index.php");
}
?>

<!-------------------------- View --------------------------->
<?php $title = "Modifier un film"; ?>
<?php include "partials/head.php"; ?>
    
    <?php include "partials/nav.php"; ?>
        
        <!--Main represents the specific content of the page  -->
        <main class="container">
            <h1>Modifier un film</h1>

            <?php if(isset($_SESSION["create_form_errors"]) && !empty($_SESSION["create_form_errors"])) : ?>
                <div class=" alert alert-danger" role="alert">
                    <ul>
                        <?php foreach($_SESSION["create_form_errors"] as $errors) : ?>
                            <li>- <?= $errors ?></li>
                        <?php endforeach ?>       
                    </ul>
                </div>
                <?php unset($_SESSION["create_form_errors"]); ?>
            <?php endif ?>    


            <div class="form-container">
                <form method="post">
                    <div class="mb-3">
                        <label for="name">Nom du film</label>
                        <input type="text" name="name" id="name" class="form-control"  value="<?php echo isset($_SESSION["old"]["name"]) ? $_SESSION["old"]["name"] : $film["name"]; unset($_SESSION["old"]["name"]) ?>">
                    </div>

                    <div class="mb-3">
                        <label for="actors">Nom acteurs</label>
                        <input type="text" name="actors" id="actors" class="form-control" value="<?php  echo isset($_SESSION['old']['actors']) ? $_SESSION['old']['actors'] : $film["actors"]; unset($_SESSION['old']['actors']); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="review">Note sur 5</label>
                        <input type="text" name="review" id="review" class="form-control" value="<?php  echo isset($_SESSION['old']['review']) ? $_SESSION['old']['review'] : $film["review"]; unset($_SESSION['old']['review']); ?>">
                    </div>

                    <div class="mb-3">
                        <input type="submit" class="btn btn-primary">
                    </div>
                </form>
            </div>
        </main>

        
    <?php include "partials/footer.php"; ?>
    
<?php include "partials/foot.php"; ?>
    

<!-- <?php ?> -->