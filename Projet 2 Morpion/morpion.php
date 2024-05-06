<?php

session_start();
include 'connect.php';
    // Vérifier si l'utilisateur est connecté et si la session contient un ID d'utilisateur
    if (isset($_SESSION['id_user'])) {
        $fk_id_user = $_SESSION['id_user'];
        // Récupérer les scores vert et rouge de l'utilisateur connecté
        $sql = "SELECT SUM(nb_victoire) as total_victoire, couleur FROM partie WHERE fk_id_user = :fk_id_user GROUP BY couleur";
        $stmt = $bdd->prepare($sql);
        $stmt->execute([':fk_id_user' => $_SESSION['id_user']]);
        $scores = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Définir les variables pour les scores vert et rouge
        $scoreGreen = 0;
        $scoreRed = 0;

        // Parcourir les résultats et affecter les scores aux variables correspondantes
        foreach ($scores as $score) {
            if ($score['couleur'] == 'green') {
                $scoreGreen = $score['total_victoire'];
            } elseif ($score['couleur'] == 'red') {
                $scoreRed = $score['total_victoire'];
            }
        }
    } else {
        // Si l'utilisateur n'est pas connecté, définir les scores vert et rouge à 0
        $scoreGreen = 0;
        $scoreRed = 0;
    }

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jeu de Morpion</title>
    <link rel="stylesheet" href="style.css"> 
    <style>
        table {
            border-collapse: collapse;
            width: 300px;
            height: 300px;
        }
        td {
            border: 1px solid black;
            width: 100px;
            height: 100px;
        }
        button {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <h1>Jeu de Morpion</h1>
    <table id="tableau">
        <tr>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    </table>
    <p>Score vert : <span id="scoreGreen"><?= $scoreGreen ?></span></p>
    <p>Score rouge : <span id="scoreRed"><?= $scoreRed ?></span></p>
    <button id="nouvellePartie">Nouvelle partie</button>
    <script src="script.js"></script>

    <button onclick="window.location.href = 'deconnexion.php'">Se déconnecter</button>
</body>
</html>
