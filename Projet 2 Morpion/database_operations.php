<?php
require_once 'connect.php';

function insertGameResult($winner) {
    global $bdd;
    try {
        $query = $bdd->prepare("INSERT INTO parties (gagnant) VALUES (:gagnant)");
        $query->bindParam(':gagnant', $winner);
        $query->execute();
        return true;
    } catch (PDOException $e) {
        echo "Erreur d'insertion: " . $e->getMessage();
        return false;
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['winner'])) {
        $winner = $_POST['winner'];
        insertGameResult($winner);
    } else {
        echo "Données non reçues.";
    }
}
?>
