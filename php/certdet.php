<?php

/**
 * CertDet - Dettaglio certificato
 * PHP Version 8.2.7
 *
 * @see https://w.c-net.me/cert
 *
 * @author    Denis Cuccarini (Cucca) <denis.cuccarini@gmail.com>
 * @copyright 2023 - 2023 Denis Cuccarini
 * @license   Copyright - All Right reserved
 * @note      Mostra il dettaglio del certificato, sottostanti e calendario
 */
require_once 'include.php';


if (!isset($_REQUEST['certid'])){
    die("E' necessario indicare un id!");
}

LeggiCert($_REQUEST['certid']);
LeggiCalendario($_REQUEST['certid']);


////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * leggo l'elenco dei certificati
**/
function LeggiCert($id){
    global $pdo;

    $sql = "SELECT * FROM certificati where id=".$id;
    $CertS = $pdo->prepare($sql);
    $CertS->execute();

    // echo '<div class="ps-3 pe-5">';

    while($Cert = $CertS->fetch(PDO::FETCH_ASSOC)){
        // Stampa intestazione colonne tabella
        CtfIntColonne($Cert['id']);

        // Leggo i sottostanti di ogni certificato
        $sql = "SELECT * FROM sottostanti s left join anagrafica_sottostanti a on a.id=s.FK_sottoID where s.FK_certID=".$Cert['id'];
        $SottoS = $pdo->prepare($sql);
        $SottoS->execute();

        while($Sotto = $SottoS->fetch(PDO::FETCH_ASSOC)){
            // Stampo i sottostanti
            If ($Sotto['FK_sottoID']!=''){
                $sql2 =   "SELECT * FROM quotazioni " 
                        . "left join anagrafica_sottostanti on quotazioni.FK_Sotto=anagrafica_sottostanti.id "
                        . "where "
                        . "FK_Sotto=".$Sotto['FK_sottoID']." and ultimo_aggiornamento = "
                        ."(SELECT MAX(ultimo_aggiornamento) FROM quotazioni WHERE FK_Sotto=".$Sotto['FK_sottoID'].")";
                $stmt2 = $pdo->prepare($sql2);
                //$stmt2->bindParam(':fk_sotto', $Sotto['sottoID'], PDO::PARAM_STR);
                //$stmt2->bindParam(':fk_sotto2', $Sotto['sottoID'], PDO::PARAM_STR);
                $stmt2->execute();
                $result = $stmt2->fetch(PDO::FETCH_ASSOC);

                If (isset($result['id'])){
                    CtfStaRiga($Sotto['isin'], $Sotto['descrizione'], $Sotto['strike'], $Cert['barriera'], $result['prezzo_corrente'], $result['ultimo_aggiornamento']);
                }
            }
        }
    }
    //echo "</div>";
}

/**
 * Stampo card HTML con info certificato e intestazioni colonne
**/
function CtfIntColonne($CertId){
    global $pdo;

    // ottengo isin del certificato
    $sql = "SELECT * FROM certificati where id=".$CertId;
    $CertS = $pdo->prepare($sql);
    $CertS->execute();
    $Cert = $CertS->fetch(PDO::FETCH_BOTH);

    // Ricavo la data del prossima rilevamento
    $sql = "SELECT data_rilevamento FROM `calendari` WHERE FK_certid=".$Cert['id']." and data_rilevamento>CURRENT_DATE order by data_rilevamento limit 1";
    $DatRilS = $pdo->prepare($sql);
    $DatRilS->execute();
    $DatRil = $DatRilS->fetch(PDO::FETCH_BOTH);
    if (isset($DatRil['data_rilevamento'])){
        $ProxRil = $DatRil['data_rilevamento'];
    }else{
        $ProxRil = '';
    }

    // ottengo il prezzo d'acquisto e la quantità acquistata
    $sql =   "with a as( "
           . "SELECT  *, "
           . "(select IfNull(data, '0001-01-01') from movimenti where FK_CertId=certificati.id and causale='AQ' limit 1) as data_acq, "
           . "(select IfNull(sum(quantita), 0) from movimenti where FK_CertId=certificati.id and causale='AQ') as qta_acq, "
           . "(select IfNull(sum(totale_netto), 0) from movimenti where FK_CertId=certificati.id and causale='AQ') as imp_acq, "
           . "(select IfNull(sum(quantita), 0) from movimenti where FK_CertId=certificati.id and causale='VE') as qta_ven, "
           . "(select IfNull(sum(totale_netto), 0) from movimenti where FK_CertId=certificati.id and causale='VE') as imp_ven  "
           . "FROM certificati "
           . ") "
           . "select data_acq, (imp_acq-imp_ven) as imp_tot, (qta_acq-qta_ven) as qta_tot from a where (qta_acq-qta_ven)>0 and id=".$CertId;
    $TotAcqS = $pdo->prepare($sql);
    $TotAcqS->execute();
    $TotAcq = $TotAcqS->fetch(PDO::FETCH_BOTH);

    if (isset($TotAcq['imp_tot'])){
        $PrzAcq = $TotAcq['imp_tot']*-1;
    }else{
        $PrzAcq = 0;
    }
    if (isset($TotAcq['qta_tot'])){
        $QtaAcq = $TotAcq['qta_tot'];
    }else{
        $QtaAcq = 0;
    }
    if (isset($TotAcq['data_acq'])){
        $DataAcq = $TotAcq['data_acq'];
    }else{
        $DataAcq = '0001-01-01';
    }
    if (isset($TotAcq['imp_tot']) && isset($TotAcq['qta_tot'])){
        $CosMedio = ($TotAcq['imp_tot']*-1)/$TotAcq['qta_tot'];
    }else{
        $CosMedio = 0;
    }
    echo '<br/>';
    echo '<br/>';
    echo '<br/>';
    echo '<div class="card" style="width: 50rem; background-color:#00FFFF;">';
    echo '<div class="card-body">';
    echo '<h5 class="card-title">';
    echo 'Certificato: <a href="https://www.certificatiederivati.it/db_bs_scheda_certificato.asp?isin='.$Cert['isin'].'" target="_blank">'.$Cert['isin'].'</a>';
    echo '</h5>';
    echo '<div class="row"><div class="col">Data Acquisto:</div><div class="col text-start">'.date("d/m/Y", strtotime($DataAcq)).'</div></div>';
    echo '<div class="row"><div class="col">Data Scadenza:</div><div class="col text-start">'.date("d/m/Y", strtotime($Cert['data_scadenza'])).'</div></div>';
    echo '<div class="row"><div class="col">Cedola:</div><div class="col text-start">'. number_format($Cert['percentuale_cedola'], 2, ',', '.').' &percnt;</div></div>';
    echo '<div class="row"><div class="col">Emittente:</div><div class="col">'.$Cert['emittente']."</div></div>";
    echo '<div class="row"><div class="col">Cat.Acepi:</div><div class="col">'.$Cert['cat_acepi']."</div></div>";
    echo '<div class="row"><div class="col">Mercato:</div><div class="col">'.$Cert['mercato']."</div></div>";
    echo '<div class="row"><div class="col">Prezzo Emissione:</div><div class="col">&euro; '.number_format($Cert['prezzo_emissione'], 2, ',', '.')."</div></div>";
    echo '<div class="row"><div class="col">Qtà Acquistata:</div><div class="col">'.number_format($QtaAcq, 0, ',', '.')."</div></div>";
    echo '<div class="row"><div class="col">Costo Medio:</div><div class="col">&euro; '.number_format($CosMedio, 2, ',', '.')."</div></div>";
    echo '<div class="row"><div class="col">Prezzo Chiusura:</div><div class="col">&euro; '.number_format($Cert['prezzo_chiusura'], 2, ',', '.')."</div></div>";
    echo '<div class="row"><div class="col">Data Ultima Chiusura:</div><div class="col">'.date("d/m/Y", strtotime($Cert['data_ultima_chiusura']))."</div></div>";
    echo '<div class="row"><div class="col">Prossimo Rilevamento:</div><div class="col">'.date("d/m/Y", strtotime($ProxRil))."</div></div>";
    echo '<div class="row"><div class="col">Valuta:</div><div class="col">'.$Cert['valuta']."</div></div>";

    // Calcolo rendita
    $RendS = $pdo->prepare( "SELECT IfNull(sum(case when causale='CE' then totale_netto else 0 end), 0), "
                           ."IfNull(sum(totale_netto), 0) FROM movimenti WHERE FK_CertId=:certid ");
    $RendS->bindParam(':certid', $CertId, PDO::PARAM_STR);
    $RendS->execute();
    $Rendita = $RendS->fetch(PDO::FETCH_BOTH);

    echo '<div class="row"><div class="col">Rendita:</div><div class="col">'.number_format($Rendita[0], 2, ',', '.')." (".number_format($Rendita[1], 2, ',', '.').")</div></div>";
    echo '</div> <!-- card body -->';
    echo '</div> <!-- card -->';
    echo '<br/>';

    // Verifico se esiste il calendario
    $sql = "SELECT count(*) FROM calendari WHERE FK_certID = ".$CertId;
    $res = $pdo->prepare($sql);
    $res->execute();
    $righe = $res->fetchColumn();

    if ($righe==0){
        echo '&nbsp;&nbsp;<a href="creacal.php?CertId='.$CertId.'">Crea Calendario</a>';
    }

    echo '</h4>';

    echo "<br/>";
    echo '<div class="row">';
    echo '<div class="col-2 border bg-primary text-white">ISIN</div>';
    echo '<div class="col-3 border bg-primary text-white">Azione</div>';
    echo '<div class="col-1 border bg-primary text-white">Val.Iniziale</div>';
    echo '<div class="col-1 border bg-primary text-white">Barriera %</div>';
    echo '<div class="col-1 border bg-primary text-white">Barriera Val</div>';
    echo '<div class="col-1 border bg-primary text-white">Val.Oggi</div>';
    echo '<div class="col-1 border bg-primary text-white">Diff.Val.Iniz.</div>';
    echo '<div class="col-1 border bg-primary text-white">Diff. Barriera</div>';
    echo '<div class="col-1 border bg-primary text-white">Ult. Aggiorn.</div>';
    echo '</div>';
}

/**
 * Stampa riga con dettaglio sottostante
**/
function CtfStaRiga($isin, $Azione, $ValIniz, $BarPer, $ValOggi, $UltAgg ){

    If ($ValIniz!=0){
        $DiffValIniz = (($ValOggi-$ValIniz)/$ValIniz)*100;
    }else{
        $DiffValIniz = 0;
    }
    $BarVal = ($ValIniz*$BarPer)/100;
    If ($ValOggi!=0){
        $DiffBar = (($ValOggi-$BarVal)/$ValOggi)*100;
    }else{
        $DiffBar = 0;
    }
    If ($DiffValIniz<0){
        $DiffValInizbg = 'bg-danger text-white';
    }else{
        $DiffValInizbg = '';
    }

    echo '<div class="row">';
    echo '<div class="col-2 border">' . $isin .'</div>';
    echo '<div class="col-3 border">' . $Azione .'</div>';
    echo '<div class="col-1 border text-end">&euro; ' . number_format($ValIniz, 2, ',', '.') . '</div>';
    echo '<div class="col-1 border text-end">' . number_format($BarPer, 2, ',', '.') . ' &percnt;</div>';
    echo '<div class="col-1 border text-end">&euro; ' . number_format($BarVal, 2, ',', '.') . '</div>';
    echo '<div class="col-1 border text-end">&euro; ' . number_format($ValOggi, 2, ',', '.') . '</div>';
    echo '<div class="col-1 border text-end ' . $DiffValInizbg . '"> ' . Round($DiffValIniz, 2) .' &percnt;</div>';
    echo '<div class="col-1 border text-end">' . Round($DiffBar, 2) .' &percnt;</div>';
    echo '<div class="col-1 border text-end">' . date( 'd/m/y H:i', strtotime($UltAgg)) .'</div>';
    echo '</div>';

}

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

/**
 * Leggo il calendario relativo al certificato
**/
function LeggiCalendario($CertId){
    global $pdo;
    $PrimoLoop = TRUE;

    // leggo il calendario del certificato
    $sql = "SELECT * FROM calendari left join certificati on certificati.id=calendari.FK_certID  where FK_certid=? order by data_rilevamento";
    $CalS = $pdo->prepare($sql);
    $CalS->execute([$CertId]);    // <-----------------------------------------------------------------------------------------------------------

    //echo '<div class="ps-3 pe-5">';

    while($Cal = $CalS->fetch(PDO::FETCH_BOTH)){
        // Stampa intestazione colonne tabella
        if ($PrimoLoop){
            CalIntColonne();
            $PrimoLoop = FALSE;
        }

        CalStaRiga($Cal['data_rilevamento'], $Cal['premio_rimborso'], $Cal['trigger_autocallable'], $Cal['cedola'], 
                   $Cal['trigger_cedola'], $Cal['note'], $Cal['incassata'], $Cal[0]);
    }

    //echo "</div>";
}
/*
function Incassa($id){
    global $pdo;

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = "UPDATE calendari SET incassata='1' where id=?";
    $pdo->prepare($sql)->execute([$id]);
}
*/

/**
 * Stampa HTML intestazioni colonna calendario
**/
function CalIntColonne(){
    echo "<br/>";
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

/**
 * Stampa HTML riga di calendario
**/
function CalStaRiga($DataRilevamento, $PremioRimborso, $TriggerAutocallable, $Cedola, $TriggerCedola, $Note, $incassata, $id){
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
        echo '<div class="col-1 '.$bg.' border text-end"><a href="calendario.php?id='.$id.'&action=inc">Incassa</a></div>';
    }else{
        echo '<div class="col-1 '.$bg.' border text-end">&nbsp;</div>';
        //echo '<div class="col-1 '.$bg.' border text-end"><a href="calendario.php?isin='.$isin.'&id='.$id.'&action=inc">Incassa</a></div>';
    }

    echo '</div>';

}

require ('../html/navbar_end.html');
require ('../html/footer.html');
?>
