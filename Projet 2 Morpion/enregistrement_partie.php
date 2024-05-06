<?php
session_start();
include 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $winner = $_POST['winner'];
    $fk_id_user = $_SESSION['id_user'];

    try {
        // Mettre à jour le nombre de victoires pour le gagnant
        $sql = "UPDATE partie SET nb_victoire = nb_victoire + 1 WHERE fk_id_user = :fk_id_user AND couleur = :couleur";
        $stmt = $bdd->prepare($sql);
        $stmt->execute([':fk_id_user' => $fk_id_user, ':couleur' => $winner]);

        // Vérifier si une ligne a été affectée par la mise à jour
        $rowCount = $stmt->rowCount();

        if ($rowCount == 0) {
            // Si aucune ligne n'a été affectée, insérer une nouvelle partie
            $sql = "INSERT INTO partie (fk_id_user, couleur, nb_victoire, date_partie) VALUES (:fk_id_user, :couleur, 1, NOW())";
            $stmt = $bdd->prepare($sql);
            $stmt->execute([':fk_id_user' => $fk_id_user, ':couleur' => $winner]);
        }

        $_SESSION["id_partie"] = $bdd->lastInsertId();
        header("Location: morpion.php"); // Rediriger vers la page principale

    } catch(PDOException $e) {
        echo "Erreur: " . $e->getMessage();
    }
} else {
    exit();
}
?>

