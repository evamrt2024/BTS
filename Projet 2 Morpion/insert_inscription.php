<?php
include 'connect.php';

// vérification de l'envoie du formulaire 

if (!empty($_POST)){
    // username = ce que le formulaire renvoie 

    if(isset($_POST["nom"],$_POST["password"],$_POST["prenom"],$_POST["login"])
        && !empty($_POST["nom"]) && !empty($_POST["password"]) && !empty($_POST["prenom"]) && !empty($_POST["login"]))
    {
       
        // protection des données 
        $login = $_POST["login"];
        $password = hash('sha256',$_POST["password"]);
        //$password=$_POST["password"];
        $nom = $_POST["nom"];
        $prenom = $_POST["prenom"];
        $userExists = $bdd->prepare("SELECT * FROM user WHERE login = '".$login."' and password = '".$password."'");
        $count = $userExists->rowCount();
        if($count == 0){
            $pass = strip_tags($_POST['password']);
            $nbCaractere = strlen($pass);
            if ($nbCaractere > 8){
                $password = hash('sha256',$_POST["password"]);
                $rq = "INSERT INTO user (login,password,nom,prenom) VALUES ('$login', '$password', '$nom', '$prenom')";
                //print $rq;
                $createUser = $bdd->prepare($rq);
                //$createUser->execute(array('',$login, $password, $nom, $prenom,''));
                $createUser->execute();
                var_dump($createUser);
                header("Location: connexion");
            } else {
                $erreur = "Le mot de passe doit contenir 8 caractères au minimum !";
            }
        } else {
            $erreur = "Email et/ou nom d'utilisateur déja utilisé.";
        }

    }
}

?>