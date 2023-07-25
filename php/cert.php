<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 'On');

require_once('database.php');
require ($config['ROOT_DIR'].'html/header.html');
//echo $config['ROOT_DIR'].'php/database.php';
// se l'utente non è loggato lo invito a loggarsi ed esco
if (!isset($_SESSION['session_id'])) {
    printf("Effettua il %s per accedere all'area riservata", '<a href="login.html">login</a>');
    require($config['ROOT_DIR']."html/footer.html");
    exit;
}

require ($config['ROOT_DIR'].'html/navbar_ini.html');

// Validazione variabili
if (!isset($action)){
    $action='';
}

if (!isset($CertId)){
    $CertId='';
}

if (!isset($_REQUEST['action'])){
    $action = '';
}else{
    $action = $_REQUEST['action'];
}
//$action = $_REQUEST['action'];
$submiturl = $config['ROOT_URL'].'php/cert.php';

switch($action){
    case 'insert':
        InsertCert();
        break;
    case 'delete':
        break;
    case 'edit':
        break;
    case 'save':
        save();
        break;
    default:
        $action = 'save';
        require ($config['ROOT_DIR'].'html/cert.html');
        echo "QUI!!!";
}
function save(){
    if (isset($_POST['CertId'])){
        /*
        $data = [
            'isin' => $_REQUEST['ISIN'],
            'emittente' => $_REQUEST['Emittente'],
            'data_acquisto' => $_REQUEST['DataAcquisto'],
            'data_scadenza' => $_REQUEST['DataScadenza'],
            'prezzo_acquisto' => $_REQUEST['PrezzoAcquisto'],
            'giorno_cedola' => $_REQUEST['GiornoCedola'],
            'percentuale_cedola' => $_REQUEST['PercentualeCedola'],
            'barriera' => $_REQUEST['Barriera'],
            'isin_sottostante1' => $_REQUEST['IsinSottostante1'],
            'strike_sottostante1' => $_REQUEST['StrikeSottostante1'],
            'isin_sottostante2' => $_REQUEST['IsinSottostante2'],
            'strike_sottostante2' => $_REQUEST['StrikeSottostante2'],
            'isin_sottostante3' => $_REQUEST['IsinSottostante3'],
            'strike_sottostante3' => $_REQUEST['StrikeSottostante3'],
            'isin_sottostante4' => $_REQUEST['IsinSottostante4'],
            'strike_sottostante4' => $_REQUEST['StrikeSottostante4'],
            'isin_sottostante5' => $_REQUEST['IsinSottostante5'],
            'strike_sottostante5' => $_REQUEST['StrikeSottostante5'],
            'isin_sottostante6' => $_REQUEST['IsinSottostante6'],
            'strike_sottostante6' => $_REQUEST['StrikeSottostante6'],
            'isin_sottostante7' => $_REQUEST['IsinSottostante7'],
            'strike_sottostante7' => $_REQUEST['StrikeSottostante7'],
            'isin_sottostante8' => $_REQUEST['IsinSottostante8'],
            'strike_sottostante8' => $_REQUEST['StrikeSottostante8'],
            'isin_sottostante9' => $_REQUEST['IsinSottostante9'],
            'strike_sottostante9' => $_REQUEST['StrikeSottostante9'],
            'isin_sottostante10' => $_REQUEST['IsinSottostante10'],
            'strike_sottostante10' => $_REQUEST['StrikeSottostante10'],
            'id' => $_REQUEST['CertId'],
        ];*/
        $Isin                = (isset($_REQUEST['ISIN']) ? $_REQUEST['ISIN'] : '');
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
        $id                  = (isset($_REQUEST['CertId']) ? $_REQUEST['CertId'] : 0);

        $sql =   "UPDATE certificati SET isin=?, emittente=?, data_acquisto=?, data_scadenza=?, prezzo_acquisto=?, "
               . "giorno_cedola=?, percentuale_cedola=?, barriera=?, isin_sottostante1=?, strike_sottostante1=?, "
               . "isin_sottostante2=?, strike_sottostante2=?, isin_sottostante3=?, strike_sottostante3=?, "
               . "isin_sottostante4=?, strike_sottostante4=?, isin_sottostante5=?, strike_sottostante5=?, "
               . "isin_sottostante8=?, strike_sottostante8=?, isin_sottostante9=?, strike_sottostante9=?, "
               . "isin_sottostante6=?, strike_sottostante6=?, isin_sottostante7=?, strike_sottostante7=?, "
               . "isin_sottostante10=?, strike_sottostante10=? WHERE Id=?";
        $stmt= $pdo->prepare($sql);
        $stmt->execute([$Isin, $Emittente, $DataAcquisto, $DataScadenza, $PrezzoAcquisto, $GiornoCedola, $PercentualeCedola,
                        $Barriera, $IsinSottostante1, $StrikeSottostante1, $IsinSottostante1, $StrikeSottostante1,
                        $IsinSottostante2, $StrikeSottostante2, $IsinSottostante3, $StrikeSottostante3, $IsinSottostante4, $StrikeSottostante4,
                        $IsinSottostante5, $StrikeSottostante5, $IsinSottostante6, $StrikeSottostante6, $IsinSottostante7, $StrikeSottostante7,
                        $IsinSottostante8, $StrikeSottostante8, $IsinSottostante9, $StrikeSottostante9, $IsinSottostante10, $StrikeSottostante10, $id]);
    }else{
        /*
        $data = [
            'isin' => $_REQUEST['ISIN'],
            'emittente' => $_REQUEST['Emittente'],
            'data_acquisto' => $_REQUEST['DataAcquisto'],
            'data_scadenza' => $_REQUEST['DataScadenza'],
            'prezzo_acquisto' => $_REQUEST['PrezzoAcquisto'],
            'giorno_cedola' => $_REQUEST['GiornoCedola'],
            'percentuale_cedola' => $_REQUEST['PercentualeCedola'],
            'barriera' => $_REQUEST['Barriera'],
            'isin_sottostante1' => $_REQUEST['IsinSottostante1'],
            'strike_sottostante1' => $_REQUEST['StrikeSottostante1'],
            'isin_sottostante2' => $_REQUEST['IsinSottostante2'],
            'strike_sottostante2' => $_REQUEST['StrikeSottostante2'],
            'isin_sottostante3' => $_REQUEST['IsinSottostante3'],
            'strike_sottostante3' => $_REQUEST['StrikeSottostante3'],
            'isin_sottostante4' => $_REQUEST['IsinSottostante4'],
            'strike_sottostante4' => $_REQUEST['StrikeSottostante4'],
            'isin_sottostante5' => $_REQUEST['IsinSottostante5'],
            'strike_sottostante5' => $_REQUEST['StrikeSottostante5'],
            'isin_sottostante6' => $_REQUEST['IsinSottostante6'],
            'strike_sottostante6' => $_REQUEST['StrikeSottostante6'],
            'isin_sottostante7' => $_REQUEST['IsinSottostante7'],
            'strike_sottostante7' => $_REQUEST['StrikeSottostante7'],
            'isin_sottostante8' => $_REQUEST['IsinSottostante8'],
            'strike_sottostante8' => $_REQUEST['StrikeSottostante8'],
            'isin_sottostante9' => $_REQUEST['IsinSottostante9'],
            'strike_sottostante9' => $_REQUEST['StrikeSottostante9'],
            'isin_sottostante10' => $_REQUEST['IsinSottostante10'],
            'strike_sottostante10' => $_REQUEST['StrikeSottostante10'],
        ];*/
        $Isin                = (isset($_REQUEST['ISIN']) ? $_REQUEST['ISIN'] : '');
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
        $stmt= $pdo->prepare($sql);
        $stmt->execute([$Isin, $Emittente, $DataAcquisto, $DataScadenza, $PrezzoAcquisto, $GiornoCedola, $PercentualeCedola,
                        $Barriera, $IsinSottostante1, $StrikeSottostante1, $IsinSottostante1, $StrikeSottostante1,
                        $IsinSottostante2, $StrikeSottostante2, $IsinSottostante3, $StrikeSottostante3, $IsinSottostante4, $StrikeSottostante4,
                        $IsinSottostante5, $StrikeSottostante5, $IsinSottostante6, $StrikeSottostante6, $IsinSottostante7, $StrikeSottostante7,
                        $IsinSottostante8, $StrikeSottostante8, $IsinSottostante9, $StrikeSottostante9, $IsinSottostante10, $StrikeSottostante10]);
    }
}
function InsertCert(){

}
function EditCert(){
    // leggo l'elenco dei certificati
    $sql = "SELECT * FROM certificati where id=:id";
    $stmt = $pdo->prepare($sql);
    $stmt2->bindParam(':id', $_POST['id'], PDO::PARAM_STR);
    $stmt->execute();
}

/*
echo '<div class="ps-3 pe-5">';

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
    // Stampa intestazione colonne tabella
    IntColonne($row['isin']);

    // stampo i sottostanti
    for ($x=1; $x<10; $x++) {
        $sotto = 'isin_sottostante' . $x;

        If ($row[$sotto]!=''){
            $sql2 = 'SELECT * FROM sottostanti where isin=:isin'; // . $row[$sotto] . '"';
            $stmt2 = $pdo->prepare($sql2);
            $stmt2->bindParam(':isin', $row[$sotto], PDO::PARAM_STR);
            $stmt2->execute();
            $result = $stmt2->fetch(PDO::FETCH_ASSOC);

            If (isset($result['id'])){
                StaRiga("", "", $result['isin'], $result['descrizione'], $row['strike_sottostante'.$x], $row['barriera'], $result['prezzo_corrente']);
            }
        }
    }

    //$dataAcquisto = $row['data_acquisto']->createDate;
    echo '<div class="row"><div class="col-2">Data Acquisto:</div><div class="col-10 text-start">'.date("d/m/Y", strtotime($row['data_acquisto'])).'</div></div>';
    echo '<div class="row"><div class="col-2">Data Scadenza:</div><div class="col-10 text-start">'.date("d/m/Y", strtotime($row['data_scadenza'])).'</div></div>';
    echo '<div class="row"><div class="col-2">Cedola:</div><div class="col-10 text-start">'. $row['percentuale_cedola']+0 .' &percnt;</div></div>';
    echo '<div class="row"><div class="col-2">Emittente:</div><div class="col-10">'.$row['emittente']."</div></div>";
    echo '<div class="row"><div class="col-2">Prezzo Acquisto:</div><div class="col-10">'.Round($row['prezzo_acquisto'], 2)."</div></div>";
    echo '<div class="row"><div class="col-2">Giorno Cedola:</div><div class="col-10">'.$row['giorno_cedola']."</div></div><br/>";
}

echo "</div>";
*/
require ($config['ROOT_DIR'].'html/navbar_end.html');
require ($config['ROOT_DIR'].'html/footer.html');
?>
