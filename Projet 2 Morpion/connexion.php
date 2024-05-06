<?php
session_start();

if(isset($_SESSION["user"])){
    header('Location: index.php');
}

if(!empty($_POST)){
    if(isset($_POST["login"], $_POST["pass"])
        && !empty($_POST["login"] && !empty($_POST["pass"]))
    ){
        if(!filter_var($_POST["login"], FILTER_VALIDATE_EMAIL)){
            $erreur = "ce n'est pas une adresse mail valide";
        }

        require_once "connect.php";

        $sql = "SELECT * FROM user WHERE login = :logs ";

        $query = $bdd->prepare($sql);

        $query->bindValue(":logs", $_POST["login"]);

        $query->execute();

        $user = $query->fetch();


        if(!$user){
            $erreur = "Login ou mot de passe incorrect";
        }

        if(!password_verify($_POST["pass"], $user["password"])){
            $erreur = "Login ou mot de passe incorrect t";
        }else{
             // on stock les informations de l'utilisateur 
             $_SESSION["user"] = [
                "id_user" => $user["id_user"],
                "nom" => $user["nom"],
                "prenom" => $user["prenom"]
            ];

            // redirection vers la page profil 
            header("Location: morpion");
        }

    }else{
        $erreur= "Champs manquant";
    }
}

?>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion-accueil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<div class="log">
<h2>Bienvenue</h2>
<form method="post" action="connexion.php">
  
<div class="row">
<div class="col">
    <div class="form-floating mb-3 mt-3">
    <input class="form-control" type="text" required name="login" placeholder="Entrez votre login">
    <label for="email">Login</label>
    </div>
    </div>
    <div class="col">
    <div class="form-floating mb-3 mt-3">
    <input  class="form-control" minlength="8" type="password" placeholder="Mot de passe" name="password" id="pass" required >
    <label for="pass">Votre mot de passe</label>
    </div>
    </div>

  </div>
  <?php  if(isset($erreur)){ ?>
  <div class="alert alert-danger alert-dismissible">
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    <strong> <?= $erreur; ?> </strong>
  </div>
<?php } ?>
    <button class="btn btn-outline-success" type="submit" name="connexion">Connexion</button>
    <button onclick="window.location.href = 'inscription'" class="btn btn-outline-dark" type="button">S'inscrire</button>
</form>
</div>

</body>
</html>

<style>

*, ::before, ::after {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

::-webkit-scrollbar {
    width: 0px;
}

h1{
    text-align: center;
    text-transform: uppercase;
    font-size: 100px;
}

h2{
    text-align: center;
    font-size: 70px;
    margin: 5%;
    font-family: 'Arvo', serif;
}

h3{
    font-size: 60px;
}

p{
    margin: 5% !important;
    margin-bottom: 0 !important;
    font-family: 'Red Hat Display', sans-serif;
    font-size: 16px;
}

a{
    text-decoration: none !important;
}

.marg{
    margin-left: 32%;
    display: flex;
}

.container {
    display: flex;
    justify-content: center;
    align-items: center;
}

.log{
    width: 65%;
    margin: auto;
    padding: auto;
    box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px;
    border-radius: 11px;
    margin-top: 13%;
    padding: 2%;
}

label{
  text-align: left;
}

.button{
    margin-top: 2%;
}

/* validation */

#message {
    display: none;
    background-color:transparent;
    color: #fff;
    position: relative;
  }

  #message p {
    font-size: 14px;
    margin: 0!important;
  }

  .valid {
    color: #6cff98;
  }

  .valid:before {
    position: relative;
    left: 0;
    content: "✔  ";
  }

  .invalid {
    color: red;
  }

  .invalid:before {
    position: relative;
    left: 0;
    content: "✖  ";
  }
</style>
