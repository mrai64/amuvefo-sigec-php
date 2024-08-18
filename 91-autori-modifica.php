<?php
/**
 *	91-autori-modifica 
 *
 *	Si tratta di un "controller" + "model"
 *	Ottiene i dati dal modulo 91-autori-modifica-mod 
 *	ed esegue "semplicemente" l'aggiornamento del record 
 *	per poi tornare all'elenco autori cominciando dall'autore 
 *	aggiornato 
 *
 */
session_start();
include_once './aa-model/database-handler.php'; // $con 

if (!isset($_POST['aggiorna_autore'])){
	die('Form required');
}
/* impostare sanificazione
 */

// record_id 
$record_id        = mysqli_real_escape_string($con, $_POST['record_id']);
$cognome_nome     = mysqli_real_escape_string($con, $_POST['cognome_nome']);
$detto            = mysqli_real_escape_string($con, $_POST['detto']);
$sigla_6          = mysqli_real_escape_string($con, $_POST['sigla_6']);
$fisica_giuridica = mysqli_real_escape_string($con, $_POST['fisica_giuridica']);
$url_autore       = mysqli_real_escape_string($con, $_POST['url_autore']);
// record_creato_il 

$aggiorna  = "UPDATE autori_elenco ";
$aggiorna .= "SET cognome_nome = '$cognome_nome', ";
$aggiorna .= "detto = '$detto', ";
$aggiorna .= "sigla_6 = '$sigla_6', ";
$aggiorna .= "fisica_giuridica = '$fisica_giuridica', ";
$aggiorna .= "url_autore = '$url_autore' ";
$aggiorna .= "WHERE record_id = $record_id ";
$esegui_aggiorna = mysqli_query($con, $aggiorna);
// se va bene non è false
if ($esegui_aggiorna){
	// si torna al modulo con una variabile di sistema-messaggio ai posteri
	$_SESSION['messaggio'] = 'Autore aggiornato correttamente';  
	$_SESSION['ultimo_cognome_nome'] = $cognome_nome; // l'elenco riparte dall'autore aggiornato 
	$_SESSION['ultimo_record_id']    = 0;
	header("Location: 91-autori-modifica-mod.php?id=$record_id");
	exit(0); // tutto ok - termina
}
// se passa qui è come un'else ma senza else
$_SESSION['messaggio'] = 'Autore non aggiornato';
$_SESSION['ultimo_cognome_nome'] = $cognome_nome;  
$_SESSION['ultimo_record_id']    = 0;  
header("Location: 91-autori-modifica-mod.php?id=$record_id");
exit(0); // tutto ok - termina
