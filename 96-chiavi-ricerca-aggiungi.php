<?php
/** 
 *	@source 96-chiavi-ricerca-aggiungi.php
 *	@author Massimo Rainato <maxrainato@libero.it>
 *  
 *	funzione: riceve i dati dal modulo 
 *	e lo inserisce in archivio
 *
 */
if (!defined('ABSPATH')){
	include_once("./_config.php");
}
include_once ABSPATH.'aa-model/database-handler.php'; // $con 

if (!isset($_POST['aggiungi_chiave_ricerca'])){
	die('Form required');
}

// record_id                assegnato automaticamente
$chiave           = htmlspecialchars(strip_tags($_POST['chiave']));
$chiave           = mysqli_real_escape_string($con, $chiave);
$url_manuale      = htmlspecialchars(strip_tags($_POST['url_manuale']));
$url_manuale      = mysqli_real_escape_string($con, $url_manuale);
// record_creato_il         assegnato automaticamente
// record_cancellabile_dal  assegnato automaticamente

$insert = "INSERT INTO chiavi_elenco "
. "(chiave, url_manuale ) VALUES "
. "('$chiave', '$url_manuale')";
$esegui_insert = mysqli_query($con, $insert);
// se va bene non è false
if ($esegui_insert){
	// si torna al modulo con una variabile di sistema-messaggio ai posteri
	$_SESSION['messaggio'] = 'Chiave di ricerca inserita correttamente';  
	header("Location: 96-chiavi-ricerca-aggiungi-mod.php");
	exit(0); // tutto ok - termina
}
// se passa qui è come un'else ma senza else
$_SESSION['messaggio'] = 'Chiave di ricerca non inserita proprio';
header("Location: 96-chiavi-ricerca-aggiungi-mod.php");
exit(0); // tutto ok - termina
 