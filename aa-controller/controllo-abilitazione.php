<?php
/**
 *	@source /aa-controller/controllo-abilitazione.php 
 *	@author Massimo Rainato <maxrainato@libero.it>
 *
 * ATTENZIONE: FA ACCESSO DIRETTO e non tramite OOP 
 * 
 * 1. Verifica se sono impostati i parametri $_COOKIE['consultatore']
 * e $_SESSION['consultatore']
 * 2. Accede alla tabella delle abilitazioni per verificare 
 * se è presente una abilitazione per la pagina e se questa 
 * è minore uguale a quella assegnata alla session
 * 2.1. Se no, passa o torna al modulo di accesso 
 * 2.2. Se sì, si prosegue
 * 
 */
if (!defined('ABSPATH')){
  include_once('../_config.php');
}
if (session_status() !== PHP_SESSION_ACTIVE){
	session_start();
	$_SESSION['messaggio'] = "Non risulta presente un consultatore "
	. '<br>' . serialize($_COOKIE);
	header("Location: ".URLBASE."consultatori.php/accesso/?p=3&return_to=".urlencode($_SERVER['REQUEST_URI']) );
	exit(0);
}
if (!isset($_COOKIE['abilitazione'])){
	$_SESSION['messaggio'] = "Non risulta presente un consultatore "
	. '<br>' . serialize($_COOKIE);
	header("Location: ".URLBASE."consultatori.php/accesso/?p=3&return_to=".urlencode($_SERVER['REQUEST_URI']) );
	exit(0);
}

/* 
	// inoltra alla pagina se i dati mancano o non sono uguali
	if ( !isset($_COOKIE['consultatore']) || !isset($_SESSION['consultatore']) ){
		$_SESSION['messaggio'] = "Non risulta presente un consultatore "
		. '<br>' . serialize($_COOKIE)
		. '<br>' . serialize($_SESSION);
		header("Location: ".URLBASE."consultatori.php/accesso/?p=1&return_to=".urlencode($_SERVER['REQUEST_URI']) );
		exit(0);
	}
	if ( empty($_COOKIE['consultatore']) || empty($_SESSION['consultatore']) ){
		header("Location: ".URLBASE."consultatori.php/accesso/?p=2&return_to=".urlencode($_SERVER['REQUEST_URI']) );
		exit(0);
	}
	if ( ("".$_COOKIE["consultatore"]) != ("".$_SESSION["consultatore"]) ){
		$_SESSION['messaggio'] = "Non risulta presente un consultatore "
		. '<br>' . serialize($_COOKIE)
		. '<br>' . serialize($_SESSION);
		header("Location: ".URLBASE."consultatori.php/accesso/?p=3&return_to=".urlencode($_SERVER['REQUEST_URI']) );
		exit(0);
	}
	if ( !isset($_COOKIE['abilitazione']) || (!$_COOKIE['abilitazione']) ){
		header("Location: ".URLBASE."consultatori.php/accesso/?p=4&return_to=".urlencode($_SERVER['REQUEST_URI']) );
		exit(0);
	}

	// TODO Valutare se in base al nome dei link sia possibile escludere la tabella abilitazioni
	// leggi lista aggiorna modifica amministra si ripetono nei link
 */

 // legge se l'abilitazione è sufficiente tramite la tabella abilitazioni 
include(ABSPATH."aa-model/database-handler.php"); // fornisce $con connessione archivio 
$url_pagina = $_SERVER['REQUEST_URI']; 
// in localhost la pagina ha qualcosa in più che non è in tabella abilitazioni
$url_pagina = str_replace( URLZERO, '', $url_pagina);

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
	header("Location: ".URLBASE."consultatori.php/accesso/?p=5&redirect_to=".urlencode($_SERVER['REQUEST_URI']) );
	exit(0);
}

// trovato - verifica abilitazione 
// può essere "1 lettura" ma anche "'1 lettura'"
$cookie_abilitazione = str_replace("'", '', $_COOKIE['abilitazione']);

$abilitazione = mysqli_fetch_array($record_letti);
$abilitazione_richiesta = str_replace("'", '', $abilitazione['abilitazione']);
// if ($_COOKIE["abilitazione"] < $abilitazione_richiesta["abilitazione"]){
if (strncmp($cookie_abilitazione, $abilitazione_richiesta, 2) < 0){ // A < B 
	$_SESSION["messaggio"] = "Non c'è abilitazione "
	. "sufficiente per accedere alla pagina: $url_pagina. "
	. '<br>c::' . $cookie_abilitazione . ':: vs. a::' . $abilitazione_richiesta .'::' ;
	header("Location: ".URLBASE."consultatori.php/accesso/?p=6&redirect_to=".urlencode($_SERVER['REQUEST_URI']) );
	exit(0);
}
unset($abilitazione);
unset($abilitazione_richiesta);
unset($cookie_abilitazione);
unset($record_letti);
unset($letti);
unset($con);
// tutto ok e continua...