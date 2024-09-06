<?php
/**
 * @source /aa-controller/accesso-checkpoint.php
 * @author Massimo Rainato <maxrainato@libero.it> 
 *
 *	Controller: prende dati dalle View e risponde alle View 
 *	dopo aver avuto accesso (direttamente) alla base dati 
 * Deve ricevere 4 dati:
 * - indirizzo email 
 * - password 
 * - indirizzo della pagina a cui tornare 
 * - verifica di essere chiamato dal modulo nella pagina accesso.php 
 * Fintanto che user/email e password non sono trovate nel 
 * calendario accessi, o anche vengono trovati ma il periodo 
 * di validità del pass è nel passato o nel futuro, si torna 
 * alla pagina di richiesta accesso e non si va avanti.
 *
 */
if (!defined('ABSPATH')){
  include_once("../_config.php");
}
include_once(ABSPATH . 'aa-model/database-handler.php'); // $con non oop

if (!isset($_POST['accesso_archivio'])){
	die('Form required codice errore 1');
}
if (!isset($_POST['accessoEmail'])){
	die('Form required codice errore 2');
}
if (!isset($_POST['accessoPassword'])){
	die('Form required codice errore 3');
}
if (!isset($_POST['return_to'])){
	die('Form required codice errore 4');
}
// campi in input dal 
$modulo_di_accesso   = URLBASE.'accesso.php';
$accesso_email       = mysqli_real_escape_string($con, $_POST['accessoEmail']);
$accesso_password    = mysqli_real_escape_string($con, $_POST['accessoPassword']);
$pagina_destinazione = mysqli_real_escape_string($con, $_POST['return_to']);
$oggi                = date("Y-m-d");
// filtri e convalide 
$accesso_email    = strtolower($accesso_email);
$accesso_email    = filter_var($accesso_email, FILTER_VALIDATE_EMAIL);

// Conversione password - usa sistema salt+key di wordpress
$password_mescolata = hash_hmac("sha512", $accesso_password . AUTH_SALT, AUTH_KEY);
$password_mescolata = substr($password_mescolata, 0, 250); // varchar(250) 

$leggi  = 'SELECT * from consultatori_calendario'
. " WHERE (record_cancellabile_dal = '". FUTURO ."' ) "
. " AND (email = '". $accesso_email               ."' ) "
. " AND (attivita_dal <= '". $oggi                ."' ) "
. " AND (attivita_fino_al >= '". $oggi            ."' ) ";
// $_SESSION['accessoQuery1'] = $leggi;

$record_letti = mysqli_query($con, $leggi);
if (mysqli_num_rows($record_letti) < 1){
	$_SESSION['messaggio'] = '1 Consultatore non trovato <br /><pre>'.$leggi;
	sleep(rand(3,11)); // aspetta un tempo variabile tra 3 e 11 secondi
	header("Location: ".$modulo_di_accesso );
	exit(0); // tutto ok - termina
}
$cognome_nome = '';
$abilitazione = '0 Nessuna';
$id_calendario = 0;
foreach($record_letti as $consultatore){
	if ($password_mescolata === $consultatore['password']){
		$cognome_nome  = $consultatore['cognome_nome'];
		$abilitazione  = $consultatore['abilitazione'];
		$id_calendario = $consultatore['record_id']; // primary key nella tabella, sempre > 0 
	}
}
if ($abilitazione == "0 Nessuna"){
	$_SESSION['messaggio'] = '2 Consultatore non trovato <br /><pre>'.$leggi;
	sleep(rand(3,11)); // aspetta un tempo variabile tra 3 e 11 secondi
	header("Location: ".$modulo_di_accesso );
	exit(0); // tutto ok - termina
}

// setcookie 
/* va bene online ma non in localhost 
// time() adesso + 60 secondi * 60 minuti * 24 ore * 10 giorni
setcookie("consultatore",  $cognome_nome,  time()+(60*60*24*10), "/", "archivio.athesis77.it", true, true); // 10gg 
setcookie("abilitazione",  $abilitazione,  time()+(60*60*24*10), "/", "archivio.athesis77.it", true, true); // 10gg 
setcookie("accesso_email", $accesso_email, time()+(60*60*24*10), "/", "archivio.athesis77.it", true, true); // 10gg 
setcookie("id_calendario", $id_calendario, time()+(60*60*24*10), "/", "archivio.athesis77.it", true, true); // 10gg 
setcookie("consultatore_id", $id_calendario, time()+(60*60*24*10), "/", "archivio.athesis77.it", true, true); // 10gg 
$_SESSION['consultatore']   = $cognome_nome;
$_SESSION['abilitazione']   = $abilitazione;
$_SESSION['accesso_email']  = $accesso_email;
$_SESSION['id_calendario']  = $id_calendario;
$_SESSION['consultatore_id']  = $id_calendario;
 */
// per online e localhost 
session_reset();
$_SESSION['consultatore']   = $cognome_nome;
$_SESSION['abilitazione']   = $abilitazione;
$_SESSION['accesso_email']  = $accesso_email;
$_SESSION['consultatore_id']= $id_calendario;

$scadenza = (int) time()+(60*60*24*10); //              setcookie php 
$expires  = date("D, d M Y H:i:s",$scadenza).' GMT'; // headers setcookie 
$dominio  = str_replace('https://', '', URLBASE);
$dominio  = str_replace('http://', '', $dominio);
$dominio  = substr($dominio, 0, strpos($dominio, '/', 0));

header("Set-Cookie: consultatore='$cognome_nome'; Expires='$expires'; Path=/; SameSite=None; ", false);
header("Set-Cookie: abilitazione='$abilitazione'; Expires='$expires'; Path=/; SameSite=None; ", false);
header("Set-Cookie: consultatore_id=$id_calendario; Expires='$expires'; Path=/; SameSite=None; ", false);

setcookie("consultatore",    $cognome_nome,  $scadenza, "/", $dominio); // 10gg
setcookie("abilitazione",    $abilitazione,  $scadenza, "/", $dominio); // 10gg
setcookie("accesso_email",   $accesso_email, $scadenza, "/", $dominio); // 10gg
setcookie("consultatore_id", $id_calendario, $scadenza, "/", $dominio); // 10gg

header("Location: ". $pagina_destinazione );
exit(0); // tutto ok - termina
//--
