<?php
/**
 * @source /96-chiavi-ricerca-modifica.php
 * @author Massimo Rainato <maxrainato@libero.it>
 * 
 * Questa parte di codice esegue l'aggiornamento 
 * e ritorna all'elenco delle chiavi di ricerca 
 * 
 */
if (!defined('ABSPATH')){
	include_once("./_config.php");
}
include_once ABSPATH.'aa-model/database-handler.php'; // $con 

// campi obbligatori 
if (!isset($_POST['modifica_chiave_ricerca'])){
	die('Form required');
}
$record_id    = mysqli_real_escape_string($con, $_POST['record_id']);
$chiave       = htmlspecialchars(strip_tags($_POST["chiave"]));
$chiave       = mysqli_real_escape_string($con, $chiave);
$url_manuale  = htmlspecialchars(strip_tags($_POST['url_manuale']));
$url_manuale  = mysqli_real_escape_string($con, $url_manuale);
// record_creato_il 
// record_cancellabile_dal 

$aggiorna = "UPDATE chiavi_elenco "
. "SET chiave = '$chiave', "
. "url_manuale = '$url_manuale', "
. "record_cancellabile_dal = '".FUTURO."' "
. "WHERE record_id = $record_id";
$esegui_aggiorna = mysqli_query($con, $aggiorna);
if ($esegui_aggiorna){
	// si torna al modulo con una variabile di sistema-messaggio ai posteri
	$_SESSION['messaggio'] = 'Chiave di ricerca aggiornata correttamente';  
	header("Location: 96-chiavi-ricerca.php");
	exit(0); // tutto ok - termina
}
// se passa qui è come un'else ma senza else
$_SESSION['messaggio'] = 'Chiave di ricerca non aggiornata';
header("Location: 96-chiavi-ricerca.php");
exit(0);
