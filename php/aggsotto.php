<?PHP
/**
 * AggSotto - Aggiornamento valore sottostanti
 * PHP Version 8.2.7
 *
 * @see https://w.c-net.me/cert
 *
 * @author    Denis Cuccarini (Cucca) <denis.cuccarini@gmail.com>
 * @copyright 2023 - 2023 Denis Cuccarini
 * @license   Copyright - All Right reserved
 * @note      Tramite scrapers e xpath legge i valori dei sottostanti e gli aggiorna
 */
require_once 'include.php';


echo '<div class="ps-3 pe-5">';

echo "<pre>";
AggiornaSottostanti();
echo "\n\n\n";
AggiornaPrezzoChiusura();

/**
 * Leggo valore di tutti i sottostanti con relativo scraper e lo scrivo su tabella quotazioni
**/
function AggiornaSottostanti(){
    global $pdo;
    $httpClient = new \GuzzleHttp\Client();

    // leggo tutti i sottostanti
    $sql= "SELECT * FROM anagrafica_sottostanti a left join scrapers s on a.scraperID=s.id";


    $sottoS = $pdo->prepare($sql);
    $sottoS->execute();

    while($Sotto = $sottoS->fetch(PDO::FETCH_BOTH)){

        if (isset($Sotto['isin'])){

            $url = $Sotto['url'];
            // sostituisco il tag {isin} con l'isin reale
            $url = str_replace('{isin}', $Sotto['isin'], $url);
            $response = $httpClient->get($url);

            $htmlString = (string) $response->getBody();
            // HTML is often wonky, this suppresses a lot of warnings
            libxml_use_internal_errors(true);

            $doc = new DOMDocument();
            $doc->loadHTML($htmlString);

            libxml_clear_errors();

            $xpath = new DOMXPath($doc);

            $rows = $xpath->query(trim($Sotto['xpath']));
            if (isset($rows[0])){
                $valore = $rows[0]->nodeValue;
                // rimuovo eventuali simboli valuta
                $valore = str_replace('$', '', $valore);
                $valore = str_replace('€', '', $valore);
                $valore = str_replace(',', '.', $valore);
                $valore = trim($valore);

                echo "\nAggiornamento sottostante ISIN: " .$Sotto["isin"].": con valore=".$valore ?? ''."\n";

                // insert 
                $sql  = "INSERT INTO quotazioni (descrizione, FK_Sotto, prezzo_corrente, ultimo_aggiornamento) VALUES (?,?,?, NOW())";
                $insstmt = $pdo->prepare($sql);
                $insstmt->execute([$Sotto['descrizione'], $Sotto[0], $valore]);
            }
        }
    }
}

/**
 * Aggiorna prezzo di chiusura del certificato (url e xpath fissi)
**/
function AggiornaPrezzoChiusura(){
    global $pdo;

    // leggo tutti i certificati
    $sql = "SELECT * FROM certificati";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    while($cert = $stmt->fetch(PDO::FETCH_ASSOC)){
        if (isset($cert['isin'])){
            // Leggo i dati dal web
            $url = "https://www.borsaitaliana.it/borsa/cw-e-certificates/eurotlx/dati-completi.html?isin=".$cert['isin']."&lang=it";

            $httpClient = new \GuzzleHttp\Client();
            $response = $httpClient->get($url);
            $ResHTML = (string) $response->getBody();

            // HTML is often wonky, this suppresses a lot of warnings
            libxml_use_internal_errors(true);

            $doc = new DOMDocument();
            $doc->loadHTML($ResHTML);

            libxml_clear_errors();

            $xpath = new DOMXPath($doc);
            $Data_Ultima_Chiusura = $xpath->query('//*[@id="fullcontainer"]/main/section/div[4]/div[5]/article/div/div[1]/table/tbody/tr[15]/td[2]/span');
            $Prezzo_Chiusura      = $xpath->query('//*[@id="fullcontainer"]/main/section/div[4]/div[5]/article/div/div[1]/table/tbody/tr[16]/td[2]/span');

            If (isset($Data_Ultima_Chiusura) && isset($Prezzo_Chiusura)){
                echo "Aggiornamento certificato ISIN: " .$cert['isin']." - Data Ultima Chiusura:".$Data_Ultima_Chiusura[0]->nodeValue." - Prezzo Chiusura: € ".$Prezzo_Chiusura[0]->nodeValue."\n";
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $sql2 = "UPDATE certificati SET data_ultima_chiusura=?, prezzo_chiusura=? WHERE id=?";

                // Formattazione dati letti
                $Data_Ultima_Chiusura = Date_Ita2Eng($Data_Ultima_Chiusura[0]->nodeValue);
                $Data_Ultima_Chiusura = date("Y-m-d", strtotime($Data_Ultima_Chiusura)); 

                $Prezzo_Chiusura = $Prezzo_Chiusura[0]->nodeValue;
                $Prezzo_Chiusura = str_replace('.', '', $Prezzo_Chiusura);
                $Prezzo_Chiusura = str_replace(',', '.', $Prezzo_Chiusura);

                // Aggiornamento parametri per SQL Update
                $pdo->prepare($sql2)->execute([$Data_Ultima_Chiusura, $Prezzo_Chiusura, $cert['id']]);

            }
        }
    }
}


/**
 * Converte la stringa di una data in formato italiano in formato americano
**/
function Date_Ita2Eng($DataEng){
    If (strlen($DataEng)==10){
        $dd = substr($DataEng, 0, 2);
        $mm = substr($DataEng, 3, 2);
        $yyyy = substr($DataEng, -4);
        return $mm . '/' . $dd . '/' .$yyyy;
    }
}


echo '</div>';
require ('../html/navbar_end.html');
require ('../html/footer.html');


?>