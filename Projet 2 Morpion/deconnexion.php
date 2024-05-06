<?php

session_start();

$id = $_SESSION["user"]["id"];

unset($_SESSION["user"]);

header("location: connexion.php");
