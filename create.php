<?php 
session_start();
//var_dump($_SERVER);
//die();
// Si les données du formulaire ont été envoyées via la méthode "POST"
if ($_SERVER['REQUEST_METHOD'] === "POST") {

        $post_clean = [];
        $create_formError = [];

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
                $create_formError["name"] = "Le nom du film est obligatoire.";
           }
           else if(mb_strlen($post_clean["name"]) > 255)
           {
                $create_formError["name"] = "Film doit contenir 255 caracteres max";
           }
        }


        // Pour le nom du ou des acteurs
        if (isset($post_clean["actors"])) 
        {
            if (empty($post_clean["actors"])) // required
            {
                $create_formError["actors"] = "Le nom des acteurs est obligatoire";
            }
            else if (mb_strlen($post_clean["actors"]) > 255) 
            {
                $create_formError["actors"] = "Le nom des acteurs doit contenir max 255 caracteres";
            }
        }   
        
        // Pour la note
        if (isset($post_clean["review"])) 
        {
           if (is_string($post_clean["review"]) && ($post_clean["review"] == ''))
           {
                $create_formError["review"] = "La note est obligatoire";
           } 
           else if(empty($post_clean["review"]) && ($post_clean["review"]) != 0) 
           {
                $create_formError["review"] = "La note est obligatoire.";
           }
           elseif (!is_numeric($post_clean["review"])) 
           {
                $create_formError["review"] = "La note doit être un nombre";
           }
           elseif (($post_clean["review"] < 0) || ($post_clean["review"] > 5)) 
           {
                $create_formError["review"] = "La note doit être comprise entre 0 et 5";
           }
           
        }
        
        // S'il y a des erreurs,
        if (count($create_formError) > 0) 
        {
            // Stocker les messages d'erreurs en session
            $_SESSION["create_form_errors"] = $create_formError;

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

        // Effectuer la requête d'insertion des données dans la table film de la base données
        $req = $db->prepare("INSERT INTO film (name, actors, review, created_at, updated_at) VALUES (:name, :actors, :review, now(), now() )");

        $req->bindValue(":name", $filmName);
        $req->bindValue(":actors", $filmActor);
        $req->bindValue(":review", $filmReview_rounded);

        $req->execute();
        $req->closeCursor();

        // Generation d'un message flash
        $_SESSION["success"] = "Le film a été ajouté";

        // Rediriger l'utilisateur vers la page d'accueil

        // Arrêter l'execution du script.
        return header("Location: index.php");
}
?>

<!-------------------------- View --------------------------->
<?php $title = "Ajouter nouveau film"; ?>
<?php include "partials/head.php"; ?>
    
    <?php include "partials/nav.php"; ?>
        
        <!--Main represents the specific content of the page  -->
        <main class="container">
            <h1>Nouveau film</h1>

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
                        <input type="text" name="name" id="name" class="form-control"  value="<?php echo isset($_SESSION["old"]["name"]) ? $_SESSION["old"]["name"] : ""; unset($_SESSION["old"]["name"]) ?>">
                    </div>

                    <div class="mb-3">
                        <label for="actors">Nom acteurs</label>
                        <input type="text" name="actors" id="actors" class="form-control" value="<?php  echo isset($_SESSION['old']['actors']) ? $_SESSION['old']['actors'] : ""; unset($_SESSION['old']['actors']); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="review">Note sur 5</label>
                        <input type="text" name="review" id="review" class="form-control" value="<?php  echo isset($_SESSION['old']['review']) ? $_SESSION['old']['review'] : ""; unset($_SESSION['old']['review']); ?>">
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