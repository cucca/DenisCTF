<?php
/**
 * Calendario - Segna cedola come incassata
 * PHP Version 8.2.7
 *
 * @see https://w.c-net.me/cert
 *
 * @author    Denis Cuccarini (Cucca) <denis.cuccarini@gmail.com>
 * @copyright 2023 - 2023 Denis Cuccarini
 * @license   Copyright - All Right reserved
 * @note
 */
require_once 'include.php';


// verifico di aver ricevuto l'isin in input
if (!isset($_REQUEST['id'])){
    printf("Devi specificare l'id del calendario!!!!");
    require("../html/footer.html");
    exit;
}

if (!isset($_REQUEST['action'])){
    //LeggiCalendario($_REQUEST['isin']);
}else{
    switch ($_REQUEST['action']){
            case "inc":
               Incassa($_REQUEST['id']);
             //  LeggiCalendario($_REQUEST['isin']);
               break;
            default:
             //  LeggiCalendario($_REQUEST['isin']);
               break;
    }
    echo "altra azione...";
}

/*
function LeggiCalendario($isin){
    global $pdo;
    $PrimoLoop = TRUE;

    // leggo il calendario del certificato
    $sql = "SELECT * FROM calendari where isin_certificato=? order by data_rilevamento";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$isin]);

    echo '<div class="ps-3 pe-5">';

    while($res = $stmt->fetch(PDO::FETCH_ASSOC)){
        // Stampa intestazione colonne tabella
        if ($PrimoLoop){
           IntColonne($isin);
           $PrimoLoop = FALSE;
        }

        StaRiga($res['data_rilevamento'], $res['premio_rimborso'], $res['trigger_autocallable'], $res['cedola'], 
                $res['trigger_cedola'], $res['note'], $res['incassata'], $isin, $res['id']);
    }

    echo "</div>";
}
*/


/**
 * Segna su calendari che la cedola è stata incassata e scrive record su movimenti
**/
function Incassa($id){
    global $pdo;

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = "UPDATE calendari SET incassata='1' where id=?";
    $pdo->prepare($sql)->execute([$id]);

    // recupero id certificato
    //$Cert = $pdo->prepare("SELECT * FROM certificati where isin=:isin fetch first 1 row only");
    $sql= "SELECT certificati.* FROM certificati left join calendari on certificati.id=FK_CertId WHERE calendari.id=".$id
    $Cert = $pdo->prepare($sql);
    //$Cert->bindParam(':isin', $isin, PDO::PARAM_STR);
    $Cert->execute();
    $Cert = $Cert->fetch(PDO::FETCH_BOTH);

    // scrittura movimento
    $date = date('Y-m-d');
    $Cedola = ($Cert['prezzo_emissione']/100)*$Cert['percentuale_cedola'];
    $CapGain = ($Cedola/100)*26;
    $CedTot = $Cedola*$Cert['quantita_acquistata'];
    $CapTot = ($CedTot/100)*26;
    //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = "INSERT INTO movimenti (FK_CertId, data, causale, importo_netto, importo_lordo, quantita, capital_gain, totale_netto, totale_lordo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $pdo->prepare($sql)->execute([$Cert['id'], $date, 'CE', $Cedola-$CapGain, $Cedola, $Cert['quantita_acquistata'], $CapGain*-1, $CedTot-$CapTot, $CedTot]);
}
/*
function IntColonne($isin){
    echo "<br/>";
    echo "<h4>Certificato: " . $isin . '</h4>';
    echo "<br/>";
    echo '<div class="row">';
    echo '<div class="col-1 border bg-primary text-white">Data Rilevamento</div>';
    echo '<div class="col-1 border bg-primary text-white">Premio per il rimborso</div>';
    echo '<div class="col-1 border bg-primary text-white">Trigger autocallable</div>';
    echo '<div class="col-1 border bg-primary text-white">Cedola</div>';
    echo '<div class="col-1 border bg-primary text-white">Trigger Cedola</div>';
    echo '<div class="col-6 border bg-primary text-white">Note</div>';
    echo '<div class="col-1 border bg-primary text-white">Incassato?</div>';
    echo '</div>';
}

function StaRiga($DataRilevamento, $PremioRimborso, $TriggerAutocallable, $Cedola, $TriggerCedola, $Note, $incassata, $isin, $id){
    if ($incassata==TRUE){
        $bg = "bg-info";
    }else{
        $bg="";
    }

    echo '<div class="row">';
    echo '<div class="col-1 '.$bg.' border">' . date( 'd/m/y', strtotime($DataRilevamento)) .'</div>';
    echo '<div class="col-1 '.$bg.' border">' . $PremioRimborso .' &percnt;</div>';
    echo '<div class="col-1 '.$bg.' border text-end">' . $TriggerAutocallable+0 .' &percnt;</div>';
    echo '<div class="col-1 '.$bg.' border text-end">' . $Cedola .' &percnt;</div>';
    echo '<div class="col-1 '.$bg.' border text-end">' . $TriggerCedola .' &percnt;</div>';
    echo '<div class="col-6 '.$bg.' border">' . $Note .'</div>';
    if ($incassata==FALSE){
        echo '<div class="col-1 '.$bg.' border text-end"><a href="calendario.php?isin='.$isin.'&id='.$id.'&action=inc">Incassa</a></div>';
    }else{
        echo '<div class="col-1 '.$bg.' border text-end">&nbsp;</div>';
    }

    echo '</div>';

}
*/

/**
 * Converte una data in timestamp
**/
function getStrtotime($timeDateStr, $formatOfStr="j/m/Y"){
    // Same as strtotime() but using the format $formatOfStr.
    // Works with PHP version 5.5 and later.
    // On error reading the time string, returns a date that never existed. 3/09/1752 Julian/Gregorian calendar switch.
    $timeStamp = DateTimeImmutable::createFromFormat($formatOfStr,$timeDateStr);
    if($timeStamp===false){
        // Bad date string or format string.
        return -6858133619; // 3/09/1752
    } else {
        // Date string and format ok.
        return $timeStamp->format("U"); // UNIX timestamp from 1/01/1970,  0:00:00 gmt
    }
}
require ('../html/navbar_end.html');
require ('../html/footer.html');
?>
