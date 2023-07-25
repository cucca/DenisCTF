<?PHP
    session_start();
    error_reporting(E_ALL);
    ini_set('display_errors', 'On');

    require_once('database.php');
    require '../../vendor/autoload.php';
    require ('../html/header.html');

    // se l'utente non è loggato lo invito a loggarsi ed esco
    if (!isset($_SESSION['session_id'])) {
        printf("Effettua il %s per accedere all'area riservata", '<a href="login.html">login</a>');
        require("../html/footer.html");
        exit;
    }

    require ('../html/navbar_ini.html');
?>