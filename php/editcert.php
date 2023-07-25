<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 'On');

require_once('database.php');
require ($config['ROOT_DIR'].'html/header.html');

// se l'utente non è loggato lo invito a loggarsi ed esco
if (!isset($_SESSION['session_id'])) {
    printf("Effettua il %s per accedere all'area riservata", '<a href="login.html">login</a>');
    require($config['ROOT_DIR']."html/footer.html");
    exit;
}

require ($config['ROOT_DIR'].'html/navbar_ini.html');

// Validazione variabili
if (!isset($_REQUEST['action'])){
    $Action='';
}else{
    $Action = $_REQUEST['action'];
}

switch($Action){
    case 'save':
        save();
        break;
    default:
        LoadCert();
}

function save(){
    global $pdo;

    if (isset($_REQUEST['Emittente'])){
        echo "Emittente=".$_REQUEST['Emittente'];
    }else{
        echo "Emittente non settata!";
    }

    echo "</pre>";

    if (isset($_REQUEST['id'])){
        echo "SETTATO!";
        $Isin                = isset($_REQUEST['Isin']) ? $_REQUEST['Isin'] : '';
        $Emittente           = isset($_REQUEST['Emittente']) ? $_REQUEST['Emittente'] : '';
        $DataAcquisto        = isset($_REQUEST['DataAcquisto']) ? $_REQUEST['DataAcquisto'] : '1900-01-01';
        $DataScadenza        = isset($_REQUEST['DataScadenza']) ? $_REQUEST['DataScadenza'] : '1900-01-01';
        $PrezzoAcquisto      = isset($_REQUEST['PrezzoAcquisto']) ? $_REQUEST['PrezzoAcquisto'] : 0;
        $GiornoCedola        = isset($_REQUEST['GiornoCedola']) ? $_REQUEST['GiornoCedola'] : 1;
        $PercentualeCedola   = isset($_REQUEST['PercentualeCedola']) ? $_REQUEST['PercentualeCedola'] : 0;
        $Barriera            = isset($_REQUEST['Barriera']) ? $_REQUEST['Barriera'] : 0;
        $IsinSottostante1    = isset($_REQUEST['IsinSottostante1']) ? $_REQUEST['IsinSottostante1'] : '';
        // $StrikeSottostante1  = isset($_REQUEST['StrikeSottostante1']) ? $_REQUEST['StrikeSottostante1'] : 0;
        $StrikeSottostante1  = isset($_REQUEST['StrikeSottostante1']) ? number_format($_REQUEST['StrikeSottostante1'], 2, ',', '.') : 0;
        $IsinSottostante2    = isset($_REQUEST['IsinSottostante2']) ? $_REQUEST['IsinSottostante2'] : '';
        $StrikeSottostante2  = isset($_REQUEST['StrikeSottostante2']) ? $_REQUEST['StrikeSottostante2'] : 0;
        $IsinSottostante3    = isset($_REQUEST['IsinSottostante3']) ? $_REQUEST['IsinSottostante3'] : '';
        $StrikeSottostante3  = isset($_REQUEST['StrikeSottostante3']) ? $_REQUEST['StrikeSottostante3'] : 0;
        $IsinSottostante4    = isset($_REQUEST['IsinSottostante4']) ? $_REQUEST['IsinSottostante4'] : '';
        $StrikeSottostante4  = isset($_REQUEST['StrikeSottostante4']) ? $_REQUEST['StrikeSottostante4'] : 0;
        $IsinSottostante5    = isset($_REQUEST['IsinSottostante5']) ? $_REQUEST['IsinSottostante5'] : '';
        $StrikeSottostante5  = isset($_REQUEST['StrikeSottostante5']) ? $_REQUEST['StrikeSottostante5'] : 0;
        $IsinSottostante6    = isset($_REQUEST['IsinSottostante6']) ? $_REQUEST['IsinSottostante6'] : '';
        $StrikeSottostante6  = isset($_REQUEST['StrikeSottostante6']) ? $_REQUEST['StrikeSottostante6'] : 0;
        $IsinSottostante7    = isset($_REQUEST['IsinSottostante7']) ? $_REQUEST['IsinSottostante7'] : '';
        $StrikeSottostante7  = isset($_REQUEST['StrikeSottostante7']) ? $_REQUEST['StrikeSottostante7'] : 0;
        $IsinSottostante8    = isset($_REQUEST['IsinSottostante8']) ? $_REQUEST['IsinSottostante8'] : '';
        $StrikeSottostante8  = isset($_REQUEST['StrikeSottostante8']) ? $_REQUEST['StrikeSottostante8'] : 0;
        $IsinSottostante9    = isset($_REQUEST['IsinSottostante9']) ? $_REQUEST['IsinSottostante9'] : '';
        $StrikeSottostante9  = isset($_REQUEST['StrikeSottostante9']) ? $_REQUEST['StrikeSottostante9'] : 0;
        $IsinSottostante10   = isset($_REQUEST['IsinSottostante10']) ? $_REQUEST['IsinSottostante10'] : '';
        $StrikeSottostante10 = isset($_REQUEST['StrikeSottostante10']) ? $_REQUEST['StrikeSottostante10'] : 0;
        $Id                  = isset($_REQUEST['id']) ? $_REQUEST['id'] : 0;

        $sql =   "UPDATE certificati SET isin=?, emittente=?, data_acquisto=?, data_scadenza=?, prezzo_acquisto=?, "
               . "giorno_cedola=?, percentuale_cedola=?, barriera=?, isin_sottostante1=?, strike_sottostante1=?, "
               . "isin_sottostante2=?, strike_sottostante2=?, isin_sottostante3=?, strike_sottostante3=?, "
               . "isin_sottostante4=?, strike_sottostante4=?, isin_sottostante5=?, strike_sottostante5=?, "
               . "isin_sottostante8=?, strike_sottostante8=?, isin_sottostante9=?, strike_sottostante9=?, "
               . "isin_sottostante6=?, strike_sottostante6=?, isin_sottostante7=?, strike_sottostante7=?, "
               . "isin_sottostante10=?, strike_sottostante10=? WHERE Id=?";
        $stmt= $pdo->prepare($sql);
        $stmt->execute([$Isin, $Emittente, $DataAcquisto, $DataScadenza, $PrezzoAcquisto, $GiornoCedola, $PercentualeCedola,
                        $Barriera, $IsinSottostante1, $StrikeSottostante1, $IsinSottostante2, $StrikeSottostante2, 
                        $IsinSottostante3, $StrikeSottostante3, $IsinSottostante4, $StrikeSottostante4,
                        $IsinSottostante5, $StrikeSottostante5, $IsinSottostante6, $StrikeSottostante6, 
                        $IsinSottostante7, $StrikeSottostante7, $IsinSottostante8, $StrikeSottostante8, 
                        $IsinSottostante9, $StrikeSottostante9, $IsinSottostante10, $StrikeSottostante10, $Id]);
    }else{
        echo "NON SETTATO!!!!!!!!!!!!!!!!!!!!!!!!";
        $Isin                = (isset($_REQUEST['Isin']) ? $_REQUEST['Isin'] : '');
        $Emittente           = (isset($_REQUEST['Emittente']) ? $_REQUEST['Emittente'] : '');
        $DataAcquisto        = (isset($_REQUEST['DataAcquisto']) ? $_REQUEST['DataAcquisto'] : '1900-01-01');
        $DataScadenza        = (isset($_REQUEST['DataScadenza']) ? $_REQUEST['DataScadenza'] : '1900-01-01');
        $PrezzoAcquisto      = (isset($_REQUEST['PrezzoAcquisto']) ? $_REQUEST['PrezzoAcquisto'] : 0);
        $GiornoCedola        = (isset($_REQUEST['GiornoCedola']) ? $_REQUEST['GiornoCedola'] : 1);
        $PercentualeCedola   = (isset($_REQUEST['PercentualeCedola']) ? $_REQUEST['PercentualeCedola'] : 0);
        $Barriera            = (isset($_REQUEST['Barriera']) ? $_REQUEST['Barriera'] : 0);
        $IsinSottostante1    = (isset($_REQUEST['IsinSottostante1']) ? $_REQUEST['IsinSottostante1'] : '');
        $StrikeSottostante1  = (isset($_REQUEST['StrikeSottostante1']) ? $_REQUEST['StrikeSottostante1'] : 0);
        $IsinSottostante2    = (isset($_REQUEST['IsinSottostante2']) ? $_REQUEST['IsinSottostante2'] : '');
        $StrikeSottostante2  = (isset($_REQUEST['StrikeSottostante2']) ? $_REQUEST['StrikeSottostante2'] : 0);
        $IsinSottostante3    = (isset($_REQUEST['IsinSottostante3']) ? $_REQUEST['IsinSottostante3'] : '');
        $StrikeSottostante3  = (isset($_REQUEST['StrikeSottostante3']) ? $_REQUEST['StrikeSottostante3'] : 0);
        $IsinSottostante4    = (isset($_REQUEST['IsinSottostante4']) ? $_REQUEST['IsinSottostante4'] : '');
        $StrikeSottostante4  = (isset($_REQUEST['StrikeSottostante4']) ? $_REQUEST['StrikeSottostante4'] : 0);
        $IsinSottostante5    = (isset($_REQUEST['IsinSottostante5']) ? $_REQUEST['IsinSottostante5'] : '');
        $StrikeSottostante5  = (isset($_REQUEST['StrikeSottostante5']) ? $_REQUEST['StrikeSottostante5'] : 0);
        $IsinSottostante6    = (isset($_REQUEST['IsinSottostante6']) ? $_REQUEST['IsinSottostante6'] : '');
        $StrikeSottostante6  = (isset($_REQUEST['StrikeSottostante6']) ? $_REQUEST['StrikeSottostante6'] : 0);
        $IsinSottostante7    = (isset($_REQUEST['IsinSottostante7']) ? $_REQUEST['IsinSottostante7'] : '');
        $StrikeSottostante7  = (isset($_REQUEST['StrikeSottostante7']) ? $_REQUEST['StrikeSottostante7'] : 0);
        $IsinSottostante8    = (isset($_REQUEST['IsinSottostante8']) ? $_REQUEST['IsinSottostante8'] : '');
        $StrikeSottostante8  = (isset($_REQUEST['StrikeSottostante8']) ? $_REQUEST['StrikeSottostante8'] : 0);
        $IsinSottostante9    = (isset($_REQUEST['IsinSottostante9']) ? $_REQUEST['IsinSottostante9'] : '');
        $StrikeSottostante9  = (isset($_REQUEST['StrikeSottostante9']) ? $_REQUEST['StrikeSottostante9'] : 0);
        $IsinSottostante10   = (isset($_REQUEST['IsinSottostante10']) ? $_REQUEST['IsinSottostante10'] : '');
        $StrikeSottostante10 = (isset($_REQUEST['StrikeSottostante10']) ? $_REQUEST['StrikeSottostante10'] : 0);

        $sql =   "INSERT INTO certificati (isin, emittente, data_acquisto, data_scadenza, prezzo_acquisto, " 
               . "giorno_cedola, percentuale_cedola, barriera, isin_sottostante1, strike_sottostante1, " 
               . "isin_sottostante2, strike_sottostante2, isin_sottostante3, strike_sottostante3, " 
               . "isin_sottostante4, strike_sottostante4, isin_sottostante5, strike_sottostante5, " 
               . "isin_sottostante6, strike_sottostante6, isin_sottostante7, strike_sottostante7, " 
               . "isin_sottostante8, strike_sottostante8, isin_sottostante9, strike_sottostante9, " 
               . "isin_sottostante10, strike_sottostante10) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "
               . "?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$Isin, $Emittente, $DataAcquisto, $DataScadenza, $PrezzoAcquisto, $GiornoCedola, $PercentualeCedola,
                        $Barriera, $IsinSottostante1, $StrikeSottostante1, $IsinSottostante2, $StrikeSottostante2, 
                        $IsinSottostante3, $StrikeSottostante3, $IsinSottostante4, $StrikeSottostante4, $IsinSottostante5, $StrikeSottostante5, 
                        $IsinSottostante6, $StrikeSottostante6, $IsinSottostante7, $StrikeSottostante7, $IsinSottostante8, $StrikeSottostante8, 
                        $IsinSottostante9, $StrikeSottostante9, $IsinSottostante10, $StrikeSottostante10]);
    }
}

function LoadCert(){
    global $pdo;
    global $config;
    if (isset($_REQUEST['id'])){
        $submiturl = 'editcert.php';
        $Action    = 'save';

        // leggo il certificato
        $sql = "SELECT * FROM certificati where id=:id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $_REQUEST['id'], PDO::PARAM_STR);
        $result = $stmt->execute();
        If (!$result){
            die('Errore esecuzione query: ' . implode(',', $pdo->errorInfo()));
        }
        $cert = $stmt->fetch();
        //foreach ($stmt as $cert){
            $Id                  = $cert['id'];
            $Action              = 'save';
            $Isin                = $cert['isin'];
            $Emittente           = $cert['emittente'];
            $DataAcquisto        = $cert['data_acquisto'];
            $DataScadenza        = $cert['data_scadenza'];
            $PrezzoAcquisto      = $cert['prezzo_acquisto'];
            $GiornoCedola        = $cert['giorno_cedola'];
            $PercentualeCedola   = $cert['percentuale_cedola'];
            $Barriera            = $cert['barriera'];
            $IsinSottostante1    = $cert['isin_sottostante1'];
            $StrikeSottostante1  = $cert['strike_sottostante1'];
            $IsinSottostante2    = $cert['isin_sottostante2'];
            $StrikeSottostante2  = $cert['strike_sottostante2'];
            $IsinSottostante3    = $cert['isin_sottostante3'];
            $StrikeSottostante3  = $cert['strike_sottostante3'];
            $IsinSottostante4    = $cert['isin_sottostante4'];
            $StrikeSottostante4  = $cert['strike_sottostante4'];
            $IsinSottostante5    = $cert['isin_sottostante5'];
            $StrikeSottostante5  = $cert['strike_sottostante5'];
            $IsinSottostante6    = $cert['isin_sottostante6'];
            $StrikeSottostante6  = $cert['strike_sottostante6'];
            $IsinSottostante7    = $cert['isin_sottostante7'];
            $StrikeSottostante7  = $cert['strike_sottostante7'];
            $IsinSottostante8    = $cert['isin_sottostante8'];
            $StrikeSottostante8  = $cert['strike_sottostante8'];
            $IsinSottostante9    = $cert['isin_sottostante9'];
            $StrikeSottostante9  = $cert['strike_sottostante9'];
            $IsinSottostante10   = $cert['isin_sottostante10'];
            $StrikeSottostante10 = $cert['strike_sottostante10'];
        //}
        require ($config['ROOT_DIR'].'html/cert.html');
    }

}

require ($config['ROOT_DIR'].'html/navbar_end.html');
require ($config['ROOT_DIR'].'html/footer.html');
?>
