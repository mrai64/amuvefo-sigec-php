<?php
/**
 * @source /consultatori.php
 * @author Massimo Rainato <maxrainato@libero.it>
 * 
 * Centralino router delle richieste che fanno riferimento 
 * alla tabella consultatori_calendario 
 * 
 * /consultatori.php/accesso/ 
 * - pubblica il modulo di accesso / login 
 * - gestisce i dati del modulo di accesso e in caso positivo 
 *   dirotta alla pagina di destinazione
 * 
 * /consultatori.php/elenco/
 * /consultatori.php/aggiungi/
 * /consultatori.php/modifica/{id}
 * /consultatori.php/dettaglio/{id}
 * /consultatori.php/elimina/{id}
 * 
 * Attenzione: le ricerche dei consultatori non c'entrano con 
 * il calendario accessi, hanno una tabella differente di riferimento 
 * 
 */
if (!defined('ABSPATH')){
	include_once("./_config.php");
}
include_once(ABSPATH.'aa-controller/controller-base.php');  // routeFromUri
$uri = $_SERVER['REQUEST_URI'];
$pos_richieste_php = strpos($uri, '/consultatori.php/');
$uri = substr($uri, $pos_richieste_php);
$pezzi=route_from_uri($uri, '/consultatori.php/');

$richiesta=$pezzi['operazioni'][0];
switch($richiesta){
	case 'accesso':
	case 'elenco':
	case 'aggiungi':
	case 'modifica':
	case 'elimina':
	case 'dettaglio':
		break; 

	// resto no 
	default:
		http_response_code(404); // know not found
		echo '<pre style="color: red;"><strong>Funzione ['.$richiesta.'] non supportata 1</strong></pre>'."\n";
		exit(1);
		break; // per check   
} // switch richiesta 

include_once(ABSPATH.'aa-controller/consultatori-controller.php');

// accesso 
if ($richiesta === 'accesso' && isset($_POST['accesso_email'])){
  accesso_checkpoint($_POST);
  exit(0);
}
if ($richiesta === 'accesso'){
  accesso_checkpoint([]);
  exit(0);
}

// sbarramento abilitazioni - sostituisce tabella abilitazioni
$cookie_abilitazione = str_replace("'", '', $_COOKIE['abilitazione']);
$abilitazione_richiesta = str_replace("'", '', constant('MODIFICAPLUS'));
if (strcmp($cookie_abilitazione, $abilitazione_richiesta) < 0){
	http_response_code(404); // know not found
	echo '<pre style="color: red;"><strong>Funzione ['.$richiesta.'] non autorizzata.</strong></pre>'."\n";
	exit(1);	
}

// elenco 
if ($richiesta === 'elenco'){
	calendario_consultatori();
	exit(0);
}

// aggiunta 
if ($richiesta === 'aggiungi' && isset($_POST['password1'])){
  aggiunta_consultatore($_POST);
  exit(0);
}
if ($richiesta === 'aggiungi'){
  aggiunta_consultatore([]);
  exit(0);
}

// id 
if (count($pezzi['operazioni']) < 2){
	http_response_code(403); 
	echo '<pre style="color: red;"><strong>Manca un id</strong></pre>'."\n";
	exit(1);
}
// check 2 - il parametro dev'essere intero senza segno 
$consultatore_id = $pezzi['operazioni'][1];
if (!is_numeric($consultatore_id)){
	http_response_code(403);  
	echo '<pre style="color: red;"><strong>Manca un id valido</strong></pre>'."\n";
	exit(1);
}
$consultatore_id = (int) $consultatore_id;

// modifica id 
if ($richiesta === 'modifica' && isset($_POST['password1'])){
  modifica_consultatore($consultatore_id, $_POST);
  exit(0);
}
if ($richiesta === 'modifica'){
  modifica_consultatore($consultatore_id, []);
  exit(0);
}

// dettaglio
if ($richiesta === 'dettaglio'){
  dettaglio_consultatore($consultatore_id);
  exit(0);
}

// elimina id 
if ($richiesta === 'elimina'){
  cancella_consultatore($consultatore_id);
  exit(0);
}


// Qui non dovrebbe arrivarci, però...
http_response_code(404); // know not found
echo '<pre style="color: red;"><strong>Funzione ['.$richiesta.'] non supportata 2</strong></pre>'."\n";
exit(1);
