<?php

require_once('db.php');

// if (isset($_SESSION['userId'])) {
//     header('Location: index.php'); // Redirigez vers la page de connexion
//     exit();
// }

class User {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function register($username, $name, $lastname, $email, $email2, $password, $password2) {
        // Validation des données d'entréeProjets\projet-perso\planning\src\includes
        $username = htmlspecialchars($username);
        $name = htmlspecialchars($name);
        $lastname = htmlspecialchars($lastname);
        $email = htmlspecialchars($email);
        $email2 = htmlspecialchars($email);


        // Vérifier si l'utilisateur existe déjà avec cette adresse e-mail ou ce nom d'utilisateur
        $stmtCheck = $this->db->prepare("SELECT id_User FROM `user_menu` WHERE email_User = ? OR username_User = ?");
        $stmtCheck->bind_param("ss", $email, $username);
        $stmtCheck->execute();
        $stmtCheck->store_result();
        
        if ($stmtCheck->num_rows > 0) {
            // Un utilisateur avec cette adresse e-mail ou nom d'utilisateur existe déjà
            $stmtCheck->close();
            return false;
        }

    
        if($email == $email2){
            if($password == $password2){ 
            
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $createdAt = date('Y-m-d H:i:s'); // Date actuelle au format MySQL

                $stmtInsert = $this->db->prepare("INSERT INTO `user_menu` (`username_User`, `name_User`, `lastname_User`, `email_User`, `password_User`, `created_at`) VALUES (?, ?, ?, ?, ?, ?)");
                    if ($stmtInsert) {
                        $stmtInsert->bind_param("ssssss", $username, $name, $lastname, $email, $hashedPassword, $createdAt);
                        $stmtInsert->execute();
                        // Récupérer l'ID de l'utilisateur inséré
                        $userId = $stmtInsert->insert_id;
                        $stmtInsert->close();
                    
                        // Créer la table favoris associée à cet utilisateur
                        $queryCreateFavoritesTable = $this->db->prepare("CREATE TABLE IF NOT EXISTS favoris_" . $userId . " (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            id_menu INT NOT NULL,
                            FOREIGN KEY (id_menu) REFERENCES menus(id)
                        )");
                            $queryCreateFavoritesTable->execute();
                            $queryCreateFavoritesTable->close();
                        
                            if (!$queryCreateFavoritesTable) {
                                die('Erreur lors de la création de la table favoris : ' . $this->db->error);
                            }                       
            
            }else{
                die ("Les 2 mots de passe ne correspondes pas !");
            }
        }else{
                die ("Les 2 emails ne sont pas les mêmes");
        }

    
        return true;
    }
    
}
    

public function login($username, $password) {
    $stmt = $this->db->prepare("SELECT `id_User`, `password_User`, `username_User`, `name_User` FROM `user_menu` WHERE `username_User` = ? OR `email_User` = ?");
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $stmt->bind_result($id, $hashedPassword, $username, $name);
    $stmt->fetch();
    $stmt->close();

    if ($hashedPassword !== null && password_verify($password, $hashedPassword)) {
        // Vérifier si la session est déjà démarrée
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Stocker les informations de l'utilisateur dans un tableau associatif
        $_SESSION['authTwoBite'] = [
            'id' => $id,
            'username' => $username,
            'name' => $name,
        ];

        // Définir le domaine et le protocole pour le cookie
        $domain = ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : false;
        $secure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? true : false;
        // Stocker le jeton dans le cookie
        setcookie('user_id', $id, time() + (86400 * 30), "/"); // Cookie valide pendant 30 jours
        return ['success' => true, 'message' => 'Connexion réussie.'];
    }
}

    public function logout() {
        unset($_SESSION["userId"]);
    }

    public function isLoggedIn() {
        return isset($_SESSION['userId']);
    }
}
?>
