<?php
session_start();

require('./include/Manage/db.php');
include './include/Manage/FavorisManager.php';

if (isset($_SESSION['authTwoBite']['id'])) {
    $id_user = $_SESSION['authTwoBite']['id'];
    // principale 66d055d8e2394a4c9dac91c1b30b8be0
  //secours 365477f7c21b4073846c6861571d4c3c
  //secours 2 : 9a727f334569449a8285ceec2a85c2c4
    //secours 3 : 0d41dd2d8cbc4cbb8c2bd145aee65af1
    $api_key = '66d055d8e2394a4c9dac91c1b30b8be0';
    $id_menu = $_GET['id'];
} else {
    // L'utilisateur n'est pas connecté, redirigez-le vers la page de connexion par exemple
    header("Location: login.php");
    exit();
}



// Traitement des commentaires

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier si l'utilisateur est connecté
    if (!isset($_SESSION['authTwoBite']['id'])) {
        http_response_code(403); // Accès interdit
        echo 'Accès interdit. Vous devez être connecté.';
        exit();
    }

    // Vérifier si les données POST nécessaires sont présentes
    if (!isset($_POST['id_menu']) || !isset($_POST['commentaire']) || !isset($_POST['note'])) {
        http_response_code(400); // Mauvaise requête
        $resultAvis = 'Données manquantes dans la requête.';
        exit();
    }

    $id_menu = intval($_POST['id_menu']);
    $commentaire = trim(htmlspecialchars($_POST['commentaire'], ENT_QUOTES, 'UTF-8'));
    $note = intval($_POST['note']);
    $id_user = $_SESSION['authTwoBite']['id']; // Utilisez l'ID de l'utilisateur connecté

    // Vérifier si les données ne sont pas vides
    if (empty($id_menu) || empty($commentaire) || empty($note)) {
        http_response_code(400); // Mauvaise requête
        $resultAvis = 'Les données ne peuvent pas être vides.';
        exit();
    }

    // Vérifier la validité de la note (entre 1 et 5, par exemple)
    if ($note < 1 || $note > 5) {
        http_response_code(400); // Mauvaise requête
        $resultAvis = 'La note doit être comprise entre 1 et 5.';
        exit();
    }

    // Inclure le fichier de connexion à la base de données
    include_once './include/Manage/db.php';

    // Vérifiez si la connexion à la base de données est réussie
    if ($conn === false) {
        http_response_code(500); // Erreur interne du serveur
        echo 'Erreur de connexion à la base de données.';
        exit();
    }

    // Préparer la requête SQL en utilisant des déclarations préparées pour éviter les injections SQL
    $query = "INSERT INTO avis_menu (id_menu, id_user, commentaire, note) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);

    // Vérifiez si la préparation de la requête a réussi
    if (!$stmt) {
        http_response_code(500); // Erreur interne du serveur
        $resultAvis = 'Erreur lors de la préparation de la requête : ' . $conn->error;
        exit();
    }

    // Lier les paramètres
    $stmt->bind_param("iisi", $id_menu, $id_user, $commentaire, $note);

    // Exécuter la requête
    if ($stmt->execute()) {
        http_response_code(201); // Créé avec succès
        $resultAvis = 'Avis ajouté avec succès.';
    } else {
        http_response_code(500); // Erreur interne du serveur
        $resultAvis = 'Erreur lors de l\'ajout de l\'avis : ' . $stmt->error;
    }

    // Fermer la déclaration et la connexion
    $stmt->close();
}

// Récupérez les avis pour ce menu + le nom d'utilisateur de la personne ayant ajouté l'avis
// Vérification de la connexion à la base de données
if (!$conn) {
    die('Erreur de connexion à la base de données : ' . mysqli_connect_error());
}

// Récupération des avis pour ce menu + le nom d'utilisateur de la personne ayant ajouté l'avis
$query = "SELECT avis_menu.*, user_menu.username_User as pseudo FROM avis_menu JOIN user_menu ON avis_menu.id_user = user_menu.id_User WHERE id_menu = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die('Erreur de préparation de la requête : ' . $conn->error);
}
$stmt->bind_param("i", $id_menu);
$stmt->execute();
$result = $stmt->get_result();
$avis = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// // Vérifier si l'ID du menu est présent dans l'URL
if (isset($_GET['id'])) {
    // Effectuer une requête pour obtenir les détails de la recette
    $url = "https://api.spoonacular.com/recipes/$id_menu/information?apiKey=$api_key&lang=fr";
    $response = file_get_contents($url);
    $data = json_decode($response, true);

    if ($data) {
        $recetteData = $data;

        // // Effectuer une autre requête pour obtenir les instructions de préparation
        $instructionsUrl = "https://api.spoonacular.com/recipes/$id_menu/analyzedInstructions?apiKey=$api_key";
        $instructionsResponse = file_get_contents($instructionsUrl);
        $instructionsData = json_decode($instructionsResponse, true);

        // Afficher les détails de la recette
//         ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link rel="stylesheet" href="./include/CSS/style.css">
            <link rel="stylesheet" href="./include/CSS/navbar.css">
            <link rel="stylesheet" href="./include/CSS/menu.css">
            <script src="https://cdn.tailwindcss.com"></script>
            <title>Détails de la recette</title>
            
        </head>
        <body class="bg-gray-100">
        <?php include('./include/includes/navbar.html'); ?>



  <div lang="en" class="pt-6">


    <!-- Image gallery -->
    <div class="mx-auto mt-6 max-w-2xl sm:px-6 lg:grid lg:max-w-7xl lg:grid-cols-3 lg:gap-x-8 lg:px-8">
    <!-- Affichage de l'image principale -->
    <div class="aspect-h-4 aspect-w-3 hidden overflow-hidden rounded-lg lg:block">
        <img src="<?= isset($recetteData['image']) ? $recetteData['image'] : ''; ?>" alt="<?= isset($recetteData['title']) ? $recetteData['title'] : ''; ?>" class="h-full w-full object-cover object-center">
    </div>
    <!-- Affichage des images supplémentaires -->
    <div class="hidden lg:grid lg:grid-cols-1 lg:gap-y-8">
        <?php if (isset($recetteData['additionalImages']) && is_array($recetteData['additionalImages'])) : ?>
            <?php foreach ($recetteData['additionalImages'] as $additionalImage) : ?>
                <div class="aspect-h-2 aspect-w-3 overflow-hidden rounded-lg">
                    <img src="<?= $additionalImage['url']; ?>" alt="<?= isset($recetteData['title']) ? $recetteData['title'] : ''; ?>" class="h-full w-full object-cover object-center">
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>


<!-- Affichage des détails de la recette -->
<div class="mx-auto max-w-2xl px-4 pb-16 pt-10 sm:px-6 lg:grid lg:max-w-7xl lg:grid-cols-3 lg:grid-rows-[auto,auto,1fr] lg:gap-x-8 lg:px-8 lg:pb-24 lg:pt-16">
    <!-- Affichage du titre -->
    <div class="lg:col-span-2 lg:border-r lg:border-gray-200 lg:pr-8">
        <h1 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl"><?= isset($recetteData['title']) ? $recetteData['title'] : ''; ?></h1>
    </div>

    <!-- Affichage des ingrédients -->
    <div class="mt-4 lg:row-span-3 lg:mt-0">
        <?php if (!empty($recetteData['extendedIngredients'])) : ?>
            <!-- Liste des ingrédients -->
            <div class="mt-10">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Ingrédients</h3>
                </div>
                <ul role="list" class="list-disc space-y-2 pl-4 text-sm text-gray-600">
                    <?php foreach ($recetteData['extendedIngredients']  as $ingredient) : ?>
                        <li><?= gettext($ingredient['name']); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php

                // Appel de la méthode getFavoris pour obtenir la liste des favoris de l'utilisateur
                $favorisManager = new FavorisManager($conn); // Spécifiez votre connexion à la base de données ici
                $favorisUtilisateur = $favorisManager->getFavoris($id_user);

                // Vérifier si le menu actuel est déjà un favori pour cet utilisateur
                $estFavori = in_array($id_menu, $favorisUtilisateur);
                $favorisManager = new FavorisManager($conn);

                if ($favorisManager->ajouterFavori($id_user, $id_menu)) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'ajout aux favoris.']);
                }

?>

        <!-- Bouton "Ajouter au favoris" -->
        <button class="mt-10 flex w-full items-center justify-center rounded-md border border-transparent bg-indigo-600 px-8 py-3 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none z-50 favori-button" onclick="ajouterFavori(<?= $recetteData['id']; ?>)">Ajouter aux favoris</button>

    </div>

    <!-- Affichage de la description, des points forts et des détails -->
    <div class="py-10 lg:col-span-2 lg:col-start-1 lg:border-r lg:border-gray-200 lg:pb-16 lg:pr-8 lg:pt-6">
        <div>
            <!-- Affichage de la description -->
            <div class="space-y-6">
                <p class="text-base text-gray-900"><?= isset($recetteData['description']) ? $recetteData['description'] : ''; ?></p>
            </div>
        </div>

        <div class="mt-10">
            <!-- Affichage des détails -->
            <h2 class="text-lg font-semibold text-gray-900">Présentation</h2>
            <div class="mt-4 space-y-6">
            <p><?= $recetteData['summary']; ?></p>
            </div>
        </div>

        <?php if (!empty($instructionsData)) : ?>
          <div class="mt-10">
                <h2 class="text-lg font-semibold text-gray-900">Instructions</h2>

                <ol>
                    <?php foreach ($instructionsData as $step) : ?>
                        <?php if (!empty($step['steps'])) : ?>
                            <?php foreach ($step['steps'] as $instruction) : ?>
                                <li><?php echo $instruction['step']; ?></li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ol>
          </div>
            <?php endif; ?>

    </div>



    
<!-- Afficher les avis -->
<div id="commentaire">
    <h2 class="text-lg font-semibold mb-4">Commentaire</h2>
    <?php if (!empty($avis)) : ?>
      <ul role="list" class="divide-y divide-gray-200">
        <?php foreach ($avis as $avisItem) : ?>
            <li class="py-4 flex flex-col md:flex-row justify-between items-start md:items-center">
                <div class="flex items-center mb-2 md:mb-0">
                    <img class="h-12 w-12 flex-none rounded-full bg-gray-50" src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="">
                    <div class="ml-4">
                        <p class="text-sm font-semibold leading-6 text-gray-900"><?= $avisItem['pseudo']; ?></p>
                        <p class="mt-1 truncate text-xs leading-5 text-gray-500"><?=  $avisItem['commentaire']; ?></p>
                    </div>
                </div>
                <div class="hidden md:block ml-4">
                    <p class="text-sm leading-6 text-gray-900"><?= $avisItem['note']; ?> / 5</p>
                </div>
            </li>
        <?php endforeach; ?>   
      </ul>
    <?php else : ?>
        <p class="text-sm text-gray-500">Aucun avis pour le moment.</p>
    <?php endif; ?>
</div>
                    
<!-- Formulaire pour ajouter un avis -->
<div class="mt-8">
    <h2 class="text-lg font-semibold mb-4">Laisser un commentaire</h2>
    <form method="post" class="space-y-4 py-4">
        <input type="hidden" name="id_menu" value="<?= $id_menu; ?>">
        <label for="commentaire" class="block text-sm font-medium leading-5 text-gray-700">Commentaire :</label>
        <textarea name="commentaire" id="commentaire" rows="4" required class="block w-full rounded-md shadow-sm transition duration-150 ease-in-out sm:text-sm sm:leading-5"></textarea>
        <label for="note" class="block text-sm font-medium leading-5 text-gray-700">Note :</label>
        <input type="number" name="note" id="note" min="1" max="5" required class="block w-20 rounded-md shadow-sm transition duration-150 ease-in-out sm:text-sm sm:leading-5">
        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm leading-5 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition duration-150 ease-in-out">Soumettre l'avis</button>
    </form>
    <p>
        <?php
            if (isset($resultAvis) && $resultAvis === 'Avis ajouté avec succès.'){
                ?>
    <div id="toast-success" class="flex items-center w-full max-w-xs p-4 mb-4 text-gray-500 bg-white rounded-lg shadow dark:text-gray-400 dark:bg-gray-800" role="alert">
    <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-green-500 bg-green-100 rounded-lg dark:bg-green-800 dark:text-green-200">
        <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
        </svg>
        <span class="sr-only">Check icon</span>
    </div>
    <div class="ms-3 text-sm font-normal"><?= $resultAvis; ?></div>
    <button type="button" class="ms-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex items-center justify-center h-8 w-8 dark:text-gray-500 dark:hover:text-white dark:bg-gray-800 dark:hover:bg-gray-700" data-dismiss-target="#toast-success" aria-label="Close">
        <span class="sr-only">Close</span>
        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
        </svg>
    </button>
</div>
                <?php
            }elseif(isset($resultAvis)){
                ?>
<div id="toast-danger" class="flex items-center w-full max-w-xs p-4 mb-4 text-gray-500 bg-white rounded-lg shadow dark:text-gray-400 dark:bg-gray-800" role="alert">
    <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-red-500 bg-red-100 rounded-lg dark:bg-red-800 dark:text-red-200">
        <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 11.793a1 1 0 1 1-1.414 1.414L10 11.414l-2.293 2.293a1 1 0 0 1-1.414-1.414L8.586 10 6.293 7.707a1 1 0 0 1 1.414-1.414L10 8.586l2.293-2.293a1 1 0 0 1 1.414 1.414L11.414 10l2.293 2.293Z"/>
        </svg>
        <span class="sr-only">Error icon</span>
    </div>
    <div class="ms-3 text-sm font-normal"><?= $resultAvis; ?></div>
    <button type="button" class="ms-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex items-center justify-center h-8 w-8 dark:text-gray-500 dark:hover:text-white dark:bg-gray-800 dark:hover:bg-gray-700" data-dismiss-target="#toast-danger" aria-label="Close">
        <span class="sr-only">Close</span>
        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
        </svg>
    </button>
</div>

<?php
            }else{
                echo "";
            }
        ?>
    </p>
</div>



</div>

  </div>


  
        </body>

        </html>
        <?php
    } else {
        echo 'Erreur lors de la récupération des données de la recette.';
    }
} else {
    echo 'ID du menu non spécifié.';
}
// ?>




<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
function ajouterFavori(id_menu) {
    var favoriButton = document.querySelector('.favori-button');
    // Utilisez AJAX pour appeler le script PHP qui ajoute ou supprime le menu des favoris
    $.ajax({
        url: './include/fonction/ajouterFavori.php', // Le script PHP pour ajouter ou supprimer un favori
        type: 'POST',
        data: { id_menu: id_menu },
        success: function(response) {
            if (response.success) {
                var isFavori = favoriButton.getAttribute('data-favori') === 'true';
                favoriButton.setAttribute('data-favori', isFavori ? 'false' : 'true');
                favoriButton.textContent = isFavori ? 'Ajouter aux favoris' : 'Supprimer des favoris';
            } else {
                console.error('Erreur lors de l\'ajout ou de la suppression des favoris :', response.message);
            }
        },
        error: function(error) {
            console.error('Erreur lors de l\'ajout ou de la suppression des favoris :', error);
        }
    });
}
</script>