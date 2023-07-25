<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');
session_start();

require($_SERVER['DOCUMENT_ROOT']."/cert//html/header.html");

if (isset($_SESSION['session_id'])) {
    $session_user = htmlspecialchars($_SESSION['session_user'], ENT_QUOTES, 'UTF-8');
    $session_id = htmlspecialchars($_SESSION['session_id']);
    require ("./html/navbar_ini.html");
    echo '<p class="h1">Benvenuto '.$session_user .'!</p>';
    //printf("Benvenuto %s, il tuo session ID è %s", $session_user, $session_id);
    //printf("%s", '<a href="php/logout.php">logout</a>');
    require ("./html/navbar_end.html");
} else {
    printf("Effettua il %s per accedere all'area riservata", '<a href="/cert/login.html">login</a>');
}
require($_SERVER['DOCUMENT_ROOT']."/cert/html/footer.html");