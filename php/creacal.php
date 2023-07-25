<?PHP
/**
 * CreaCal - Legge il calendario tramite straper e lo scrive su tabella calendari
 * PHP Version 8.2.7
 *
 * @see https://w.c-net.me/cert
 *
 * @author    Denis Cuccarini (Cucca) <denis.cuccarini@gmail.com>
 * @copyright 2023 - 2023 Denis Cuccarini
 * @license   Copyright - All Right reserved
 * @note      scraper fisso
 */
require_once 'include.php';
echo '<div class="ps-3 pe-5">';

////////////////////////////
// LETTURA CALENDARIO ONLINE
////////////////////////////
LeggiCalendario($_REQUEST['CertId']);


echo '</div>';
require ('../html/navbar_end.html');
require ('../html/footer.html');

/**
 * Esegue scraping del calendario dell'isin ricevuto in input
**/
function LeggiCalendario($CertId){
    // Ricavo l'isin del certificato dall'id
    $sql = "SELECT isin FROM certificati WHERE ID = ".$CertId." LIMIT 1";
    $CertS = $pdo->prepare($sql);
    $CertS->execute();
    $Cert = $CertS->fetchColumn(PDO::FETCH_BOTH);

    if (isset($Cert['isin']!=''){
        $url = "https://www.certificatiederivati.it/db_bs_scheda_certificato.asp?isin=" . $Cert['isin'];
        echo $url;
    }else{
        die("isin non trovato!");
    }
    $httpClient = new \GuzzleHttp\Client();

    // $rows = $xpath->query('//table[@class="xyz"]/tbody/tr');
    $response = $httpClient->get($url);

    $htmlString = (string) $response->getBody();
    // HTML is often wonky, this suppresses a lot of warnings
    libxml_use_internal_errors(true);

    $doc = new DOMDocument();
    $doc->loadHTML($htmlString);

    libxml_clear_errors();

    $xpath = new DOMXPath($doc);

    echo "<pre>";

    // 0=>DATA RILEVAMENTO
    // 1=>PREMIO PER IL RIMBORSO
    // 2=>TRIGGER AUTOCALLABLE
    // 3=>CEDOLA
    // 4=>TRIGGER CEDOLA
    // 5=>NOTE
    $rows = $xpath->query('/html/body/div[2]/div[6]/div/div/table/tbody/tr');
    foreach ($rows as $row) {
        $cells = $row->getElementsByTagName('td');
        // alt $cells = $xpath->query('td', $row)

        $cellData = [];
        foreach ($cells as $cell) {
            $cellData[] = $cell->nodeValue;
        }
        ScriviDB($CertId, $cellData[0], $cellData[1], $cellData[2], $cellData[3], $cellData[4], $cellData[5], FALSE);
    }
}

/**
 * Scrive calendario su DB. Se è già presente e $overtrica=TRUE, prima esegue una delete
**/
function ScriviDB($CertId, $DataRilevamento, $PremioRimborso, $TriggerAutocallable, $Cedola, $TriggerCedola, $Note, $overwrite){
    global $pdo;
    if ($overwrite){
        $del = $pdo->prepare('DELETE FROM calendari where FK_CertId='.$CertId);
        $del->execute();
    }

    // Formattazione dati
    $DataRilevamento = Date_Ita2iso($DataRilevamento);

    $Cedola = str_replace("%", "", $Cedola);
    $Cedola = str_replace(",", ".", $Cedola);
    $Cedola = (double)$Cedola;

    $TriggerCedola = str_replace("%", "", $TriggerCedola);
    $TriggerCedola = str_replace(",", ".", $TriggerCedola);
    $TriggerCedola = (double)$TriggerCedola;

    if ($PremioRimborso==""){
        $PremioRimborso = 0;
    }else{
        $PremioRimborso = str_replace("%", "", $PremioRimborso);
        $PremioRimborso = str_replace(",", ".", $PremioRimborso);
        $PremioRimborso = (double)$PremioRimborso;
    }

    if ($TriggerAutocallable==""){
        $TriggerAutocallable = 0;
    }else{
        $TriggerAutocallable = str_replace("%", "", $TriggerAutocallable);
        $TriggerAutocallable = str_replace(",", ".", $TriggerAutocallable);
        $TriggerAutocallable = (double)$TriggerAutocallable;
    }
    echo "\nCreo calendario per ".$isin."data: ".$DataRilevamento;
    $sql  = "INSERT INTO calendari (FK_certId, data_rilevamento, premio_rimborso, trigger_autocallable, cedola, trigger_cedola, note, incassata) VALUES (?,?,?,?,?,?,?,?)";
    $insstmt = $pdo->prepare($sql);
    $insstmt->execute([$CertID, $DataRilevamento, $PremioRimborso, $TriggerAutocallable, $Cedola, $TriggerCedola, $Note, 0]);
}

/**
 * Converte la stringa di una data in formato italiano in formato americano
**/
function Date_Ita2Eng($DataEng){
    If (strlen($DataEng)==10){
        $dd = substr($DataEng, 0, 2);
        $mm = substr($DataEng, 2, 2);
        $yyyy = substr($DataEng, -4);
        return $mm . '/' . $dd . '/' .$yyyy;
    }
}
/**
 * Converte la stringa di una data in formato italiano in formato iso
**/
function Date_Ita2iso($DataIta){
    If (strlen($DataIta)==10){
        $dd = substr($DataIta, 0, 2);
        $mm = substr($DataIta, 3, 2);
        $yyyy = substr($DataIta, -4);
        return $yyyy . '-' . str_pad($mm, 2, '0', STR_PAD_LEFT) . '-' .str_pad($dd, 2, '0', STR_PAD_LEFT);
    }
}

?>