<?php
/** 
 *	nomefile: 97-consultatori-aggiungi.php
 *	funzione: riceve i dati dal modulo e lo inserisce in archivio
 */
session_start();
include_once './aa-model/database-handler.php'; // $con 

if (!isset($_POST['aggiungi_operatore'])){
	die('Form required');
}

// record_id 
$cognome_nome     = mysqli_real_escape_string($con, $_POST['cognome_nome']);
$abilitazione     = mysqli_real_escape_string($con, $_POST['abilitazione']);
$email            = mysqli_real_escape_string($con, $_POST['email']);
$password1        = mysqli_real_escape_string($con, $_POST['password1']);
$password2        = mysqli_real_escape_string($con, $_POST['password2']);
$attivita_dal     = mysqli_real_escape_string($con, $_POST['attivita_dal']);
$attivita_fino_al = mysqli_real_escape_string($con, $_POST['attivita_fino_al']);
// record_creato_il 
// record_cancellabile_dal 
if ($password1 != $password2){
	$_SESSION['messaggio'] = 'Le due password devono essere uguali';
	header("Location: 97-consultatori-aggiungi-mod.php");
	exit(0); // tutto ok - termina
}

$accesso_email    = strtolower($email);
$accesso_email    = filter_var($accesso_email, FILTER_VALIDATE_EMAIL);
// Conversione password 
// ToDo: deve diventare una funzione condivisa e centralizzata (classe Database?)
$password_mescolata = hash_hmac("sha512", $password1 . AUTH_SALT, AUTH_KEY);
$password_mescolata = substr($password_mescolata, 0, 250); // varchar(250) 

$insert  = "INSERT INTO consultatori_calendario ";
$insert .= " (cognome_nome, abilitazione, attivita_dal, attivita_fino_al, ";
$insert .= "  email, password )";
$insert .= " VALUES";
$insert .= " ('$cognome_nome', '$abilitazione', '$attivita_dal', '$attivita_fino_al',";
$insert .= " '$accesso_email', '$password_mescolata')";
$esegui_insert = mysqli_query($con, $insert);
// se va bene non è false
if ($esegui_insert){
	// si torna al modulo con una variabile di sistema-messaggio ai posteri
	$_SESSION['messaggio'] = 'Operatore inserito correttamente';  
	header("Location: 97-consultatori-aggiungi-mod.php");
	exit(0); // tutto ok - termina
}
// se passa qui è come un'else ma senza else
$_SESSION['messaggio'] = 'Operatore non inserito proprio';
header("Location: 97-consultatori-aggiungi-mod.php");
exit(0); // tutto ok - termina
 