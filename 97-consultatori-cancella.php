<?php
session_start();
include_once './aa-model/database-handler.php'; // $con 

if (!isset($_GET['id'])){
	die('Form required');
}
/* impostare sanificazione
 */

// record_id 
$record_id        = mysqli_real_escape_string($con, $_GET['id']);
$record_cancellabile_dal = date("Y-m-d H:i:s"); 

$aggiorna = "UPDATE consultatori_calendario SET record_cancellabile_dal = '".$record_cancellabile_dal."' WHERE record_id = $record_id";
$esegui_aggiorna = mysqli_query($con, $aggiorna);
// se va bene non è false
if ($esegui_aggiorna){
	// si torna al modulo con una variabile di sistema-messaggio ai posteri
	$_SESSION['messaggio'] = 'Operatore cancellato correttamente';  
	header("Location: 97-consultatori.php");
	exit(0); // tutto ok - termina
}
// se passa qui è come un'else ma senza else
$_SESSION['messaggio'] = 'Operatore non cancellato';
header("Location: 97-consultatori.php");
exit(0); // tutto ok - termina
 