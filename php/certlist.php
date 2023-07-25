<?php

/**
 * CertList - Elenca i certificati in portafoglio
 * PHP Version 8.2.7
 *
 * @see https://w.c-net.me/cert
 *
 * @author    Denis Cuccarini (Cucca) <denis.cuccarini@gmail.com>
 * @copyright 2023 - 2023 Denis Cuccarini
 * @license   Copyright - All Right reserved
 * @note      Vengono presi tutti i certificati con saldo acquistato-venduto maggiore di 0
 */
require_once 'include.php';


LeggiCertificati();


/////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Legge dal db tutti i certificati
**/
function LeggiCertificati(){
    global $pdo;

    // leggo l'elenco dei certificati
    //$sql = "SELECT * FROM certificati where quantita_acquistata>0";
    $sql =   "with a as( "
           . "SELECT *, "
           . "(select IfNull(sum(quantita), 0) from movimenti where FK_CertId=certificati.id and causale='AQ') as acq, "
           . "(select IfNull(sum(quantita), 0) from movimenti where FK_CertId=certificati.id and causale='VE') as ven "
           . "FROM certificati "
           . ") "
           . "select *, (acq-ven) as qta_acq from a where (acq-ven)>0";

    $CertS = $pdo->prepare($sql);
    $CertS->execute();

    //echo '<div class="ps-3 pe-5">';

    $PrimoLoop = TRUE;
    $TestoSottostanti = '';

    while($Cert = $CertS->fetch(PDO::FETCH_BOTH)){
        // Stampa intestazione colonne tabella
        If ($PrimoLoop){
            $PrimoLoop = FALSE;
            IntColonne();
        }

        LeggiSottostanti($Cert['id'], $Cert['barriera']);
    }

    //echo "</div>";
}

/**
 * Legge i sottostanti dell'id certificato ricevuto
**/
function LeggiSottostanti($CertId, $CertBarriera){
    global $pdo;
    $DiffBarTxt = '';
    $DiffValIniTxt = '';

    // Ricavo la data del prossima rilevamento
    $sql = "SELECT data_rilevamento FROM `calendari` WHERE FK_certid=".$CertId." and data_rilevamento>CURRENT_DATE order by data_rilevamento limit 1";
    $DatRilS = $pdo->prepare($sql);
    $DatRilS->execute();
    $DatRil = $DatRilS->fetch(PDO::FETCH_BOTH);
    if (isset($DatRil['data_rilevamento'])){
        $ProxRil = $DatRil['data_rilevamento'];
    }else{
        $ProxRil = '';
    }

    $SottoS = $pdo->prepare("SELECT * FROM sottostanti where FK_certID=:CertID");
    $SottoS->bindParam(':CertID', $CertId, PDO::PARAM_STR);
    $SottoS->execute();

    while($Sotto = $SottoS->fetch(PDO::FETCH_BOTH)){
        $SottoUltVal = UltimoPrezzo($Sotto['FK_sottoID']);
        $SottoStrike = $Sotto['strike'];
        $BarVal = ($SottoStrike*$CertBarriera)/100;

        // Differenza da barriera
        if ($SottoUltVal!=0){
            $DiffBar = (($SottoUltVal-$BarVal)/$SottoUltVal)*100;
            if (!Empty($DiffBarTxt)){
                $DiffBarTxt .= ' / ';
            }
            $DiffBarTxt .= number_format($DiffBar, 2, ',', '.') . ' &percnt;';
            //echo "->".$Sotto['sottoISIN']." Diff: ".$DiffBar."\n";
        }

        // Differenza da valore iniziale
        if ($SottoStrike!=0){
            $DiffValIniz = (($SottoUltVal-$SottoStrike)/$SottoStrike)*100;
            if (!Empty($DiffValIniTxt)){
                $DiffValIniTxt .= ' / ';
            }
            if ($DiffValIniz<0){
                $DiffValIniTxt .= '<span class="bg-danger text-white"><b>'.number_format($DiffValIniz, 2, ',', '.'). ' &percnt;</b></span>';
            }else{
                $DiffValIniTxt .= number_format($DiffValIniz, 2, ',', '.'). ' &percnt;';
            }
        }

    }

    StaRiga($CertId, $ProxRil, $DiffValIniTxt, $DiffBarTxt);
}

/**
 * Stampa HTML intestazioni di colonna
**/
function IntColonne(){
    echo '<h1>Portafoglio</h1><br/><br/>';
    echo '<div class="row">';
    echo '<div class="col col-xxl-2 col-sm-12 d-sm-none d-none d-xxl-inline border bg-primary text-white">ISIN</div>';
    echo '<div class="col col-xxl-2 col-sm-12 d-sm-none d-none d-xxl-inline border bg-primary text-white">Prox. Rilev.</div>';
    echo '<div class="col col-xxl-4 col-sm-12 d-sm-none d-none d-xxl-inline border bg-primary text-white">Diff. Val. Iniz.</div>';
    echo '<div class="col col-xxl-4 col-sm-12 d-sm-none d-none d-xxl-inline border bg-primary text-white">Diff. Barriera</div>';
    echo '</div>';
}

/**
 * Stampa riga HTML certificato
**/
function StaRiga($CertId, $ProxRil, $DiffValIni, $DiffBar){
    global $pdo;
    $sql = "SELECT isin FROM certificati where id=".$CertId;
    $CertS = $pdo->prepare($sql);
    $CertS->execute();
    $Cert = $CertS->fetch(PDO::FETCH_BOTH);

    echo '<div class="row">';
    echo '<div class="col-12 col-xxl-2 col-sm-12 d-sm-inline d-xxl-none border">ISIN:</div>'; // su schermo piccolo stampa etichetta di riga
    echo '<div class="col col-xxl-2 col-sm-12 border"><a href="certdet.php?certid='.$CertId.'">'.$Cert['isin'].'</a></div>';
    echo '<div class="col-12 col-xxl-2 col-sm-12 d-sm-inline d-xxl-none border">Prox.Rilev.:</div>'; // su schermo piccolo stampa etichetta di riga
    echo '<div class="col col-xxl-2 col-sm-12 border">'.date("d/m/Y", strtotime($ProxRil)).'</div>';
    echo '<div class="col-12 col-xxl-2 col-sm-12 d-sm-inline d-xxl-none border">Diff. Val. Iniz.:</div>'; // su schermo piccolo stampa etichetta di riga
    echo '<div class="col col-xxl-4 col-sm-12 border">'.$DiffValIni.'</div>';
    echo '<div class="col-12 col-xxl-2 col-sm-12 d-sm-inline d-xxl-none border">Diff. Barriera:</div>'; // su schermo piccolo stampa etichetta di riga
    echo '<div class="col col-xxl-4 col-sm-12 border">'.$DiffBar.'</div>';
    echo '<div class="col-12 col-xxl-2 col-sm-12 d-sm-inline d-xxl-none">&nbsp;</div>';
    echo '</div>';

}

/**
 * Legge dal db l'ultimo rilevamento di prezzo del sottostante
**/
function UltimoPrezzo($IdSottostante){
    global $pdo;

    if ($IdSottostante!=''){
        $ValS = $pdo->prepare("SELECT prezzo_corrente FROM quotazioni where FK_Sotto=:IDSotto order by ultimo_aggiornamento desc fetch first 1 row only");
        $ValS->bindParam(':IDSotto', $IdSottostante, PDO::PARAM_STR);
        $ValS->execute();
        $result = $ValS->fetch(PDO::FETCH_BOTH);
        return $result[0];
    }
}

require ('../html/navbar_end.html');
require ('../html/footer.html');
?>
