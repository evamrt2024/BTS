<?php
class FavorisManager
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function ajouterFavori($id_user, $id_menu)
    {
        // Assurez-vous que l'id_user et l'id_menu sont valides
        if ($id_user <= 0 || $id_menu <= 0) {
            return false;
        }

        // Ajouter le favori
        $query = "INSERT INTO favoris (id_user, id_menu) VALUES (?, ?)";
        $stmt = $this->db->prepare($query);

        if ($stmt === false) {
            return false;
        }

        $stmt->bind_param("ii", $id_user, $id_menu);

        if ($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            $stmt->close();
            return false;
        }
    }

    public function supprimerFavori($id_user, $id_menu)
    {
        $query = "DELETE FROM favoris WHERE id_menu = ? AND id_user = ?";
        $stmt = $this->db->prepare($query);

        if ($stmt === false) {
            return false;
        }

        $stmt->bind_param("ii", $id_menu, $id_user);

        if ($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            $stmt->close();
            return false;
        }
    }

    public function getFavoris($id_user) {
        // Vérifier la connexion à la base de données
        if (!$this->db) {
            die('Erreur de connexion à la base de données : ' . $this->db->connect_error);
        }
    
        // Préparer la requête SQL en utilisant des déclarations préparées
        $query = "SELECT id_menu FROM favoris WHERE id_user = ?";
        $stmt = $this->db->prepare($query);
    
        // Vérifier si la préparation de la requête a réussi
        if (!$stmt) {
            die('Erreur de préparation de la requête : ' . $this->db->error);
        }
    
        // Lier les paramètres
        $stmt->bind_param("i", $id_user);
    
        // Exécuter la requête
        if (!$stmt->execute()) {
            die('Erreur lors de l\'exécution de la requête : ' . $stmt->error);
        }
    
        // Récupérer les résultats
        $result = $stmt->get_result();
    
        // Vérifier si le résultat est valide
        if (!$result) {
            die('Erreur lors de la récupération des résultats : ' . $stmt->error);
        }
    
        // Extraire les favoris
        $favoris = [];
        while ($row = $result->fetch_assoc()) {
            $favoris[] = $row['id_menu'];
        }
    
        // Fermer la déclaration
        $stmt->close();
    
        return $favoris;
    }
    
    
    // Vous pouvez ajouter d'autres méthodes selon vos besoins
}
?>
