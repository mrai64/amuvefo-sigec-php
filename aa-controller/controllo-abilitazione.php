<?php
/**
 *	@source /aa-controller/controllo-abilitazione.php 
 *	@author Massimo Rainato <maxrainato@libero.it>
 *
 * Controller: inserito all'inizio dei php 
 * di gestione delle pagine verifica se sono presenti 
 * cookie e parametri di sessione rinviando alla pagina 
 * di login se la sessione è scaduta. 
 *
 * TODO: L'accesso all'archivio è diretto, va isolato 
 * in un accesso più "asettico" con l'uso di una classe 
 * AbilitazioniURL, e AbilitazioniURL->leggi() e un 
 * CalendarioConsultazioni->leggiAbilitazione()
 * 
 * Per esempio se il record della pagina manca viene 
 * inserito con abilitazioni di amministrazione, e in seguito 
 * dalla gestione abilitazioni "ridimensionato"2" o lasciato com'è 
 */
if (!defined('ABSPATH')){
  include_once('../_config.php');
}

if ( !isset($_SESSION['consultatore']) ){
	session_start(); // se già dato crea un warning
}

//
// inoltra alla pagina se i dati mancano o non sono uguali
if ( !isset($_COOKIE['consultatore']) || !isset($_SESSION['consultatore']) ){
	header("Location: ".URLBASE."accesso.php?p=1&redirect_to=".urlencode($_SERVER['REQUEST_URI']) );
	exit(0);
}
if ( empty($_COOKIE['consultatore']) || empty($_SESSION['consultatore']) ){
	header("Location: ".URLBASE."accesso.php?p=2&redirect_to=".urlencode($_SERVER['REQUEST_URI']) );
	exit(0);
}
if ( ("".$_COOKIE["consultatore"]) != ("".$_SESSION["consultatore"]) ){
	header("Location: ".URLBASE."accesso.php?p=3&redirect_to=".urlencode($_SERVER['REQUEST_URI']) );
	exit(0);
}
if ( !isset($_COOKIE['abilitazione']) || (!$_COOKIE['abilitazione']) ){
	header("Location: ".URLBASE."accesso.php?p=4&redirect_to=".urlencode($_SERVER['REQUEST_URI']) );
	exit(0);
}

// TODO Valutare se in base al nome dei link sia possibile escludere la tabella abilitazioni
// leggi lista aggiorna modifica amministra si ripetono nei link

// legge se l'abilitazione è sufficiente tramite la tabella abilitazioni 
include(ABSPATH."aa-model/database-handler.php"); // fornisce $con connessione archivio 
$url_pagina = $_SERVER['REQUEST_URI']; 
$operazione = ""; // in uso nei router 
if (str_contains($url_pagina, '/modifica/')){
	$operazione = 'modifica';
}
if (str_contains($url_pagina, '/backup/')){
	$operazione = 'backup';
}

$leggi  = "SELECT * FROM abilitazioni_elenco "
. " WHERE (record_cancellabile_dal = '".FUTURO."' ) "
. "   AND url_pagina = '$url_pagina' ";
if ($operazione){
	$leggi .= " AND operazione = '$operazione' ";
}
$record_letti = mysqli_query($con, $leggi);

// non trovato - si torna con avviso
if (mysqli_num_rows($record_letti) < 1) {
	$_SESSION["messaggio"] = "Non è stata trovata la pagina $url_pagina in elenco abilitazioni ";
	header("Location: ".URLBASE."accesso.php?p=5&redirect_to=".urlencode($_SERVER['REQUEST_URI']) );
	exit(0);
}

// trovato - verifica abilitazione 
$abilitazione_richiesta = mysqli_fetch_array($record_letti);
if ($_COOKIE["abilitazione"] < $abilitazione_richiesta["abilitazione"]){
	$_SESSION["messaggio"] = "Non c'è abilitazione sufficiente per accedere alla pagina: $url_pagina. ";
	header("Location: ".URLBASE."accesso.php?p=6&redirect_to=".urlencode($_SERVER['REQUEST_URI']) );
	exit(0);
}
unset($abilitazione_richiesta);
unset($record_letti);
unset($letti);
// tutto ok e continua...