<?php
/**
 * @source /ricerche.php 
 * @author Massimo Rainato <maxrainato@libero.it>
 * 
 * Centralino router delle richieste 
 * - inserimento richieste per album in /album.php 
 * - inserimento richieste per fotografie in /fotografie.php 
 * - inserimento richieste per video in /video.php 
 * 
 * /richieste.php/elenco-consultatore
 * /richieste.php/elenco-amministrarore 
 * 
 * /richieste.php/cancella-richiesta/{richiesta_id}
 * 
 * /richieste.php/conferma-richiesta/{richiesta_id}
 * 
 * /richieste.php/respinta-richiesta/{richiesta_id}
 * 
 */
if (!defined('ABSPATH')){
	include_once("./_config.php");
}
include_once(ABSPATH."aa-controller/controller-base.php");  // routeFromUri
$uri = $_SERVER['REQUEST_URI'];
$pos_richieste_php = strpos($uri, '/richieste.php/');
$uri = substr($uri, $pos_richieste_php);
$pezzi=route_from_uri($uri);
$richiesta=$pezzi['operazioni'][0];

switch($richiesta){
	case 'elenco-consultatore':
	case 'elenco-amministratore':
	case 'cancella-richiesta':
	case 'conferma-richiesta':
	case 'respinta-richiesta':
		break; 

	// resto no 
	default:
		http_response_code(404); // know not found
		echo '<pre style="color: red;"><strong>Funzione ['.$richiesta.'] non supportata</strong></pre>'."\n";
		exit(1);
		break; // per check   
} // switch richiesta 


/**
 * sbarramento abilitazione 
 *
 */
if ($_COOKIE['abilitazione'] <= SOLALETTURA){
	http_response_code(404); // know not found
	echo '<pre style="color: red;"><strong>Funzione ['.$richiesta.'] non abilitata</strong></pre>'."\n";
	exit(1);
}

// parametri 
$richiesta_id    = (isset($pezzi['operazioni'][1])) ? (int) $pezzi['operazioni'][1] : 0;
$consultatore_id = $richiesta_id;

include_once(ABSPATH.'aa-controller/richieste-controller.php');

// senza parametro 
if ($richiesta == 'elenco-consultatore'  && 
    $consultatore_id == 0                && 
    isset($_COOKIE['consultatore_id'])){
	get_elenco_richieste_consultatore($_COOKIE['consultatore_id']);	
  exit(0);
}

// con parametro 
if ($richiesta == 'elenco-consultatore' && ($consultatore_id > 0)){
	get_elenco_richieste_consultatore($consultatore_id);	
  exit(0);
}

/** TEST 
 * http://localhost:8888/AMUVEFO-sigec-php/richieste.php/elenco-consultatore/6 
 * 
 */

if ($richiesta == 'cancella-richiesta' && $richiesta_id > 0){
	cancella_richiesta_per_id($richiesta_id);
	exit(0);
}
/** TEST 
 * http://localhost:8888/AMUVEFO-sigec-php/richieste.php/cancella-richiesta/3 
 * 
 */

/**
 * sbarramento abilitazione 
 */
if ($_COOKIE['abilitazione'] <  MODIFICAPLUS){
	http_response_code(404); // know not found
	echo '<pre style="color: red;"><strong>Funzione ['.$richiesta.'] non abilitata</strong></pre>'."\n";
	exit(1);
}

// senza parametro 
if ($richiesta == 'elenco-consultatore'){

  exit(0);
}

// con parametro 
if ($richiesta == 'conferma-richiesta' && $richieste_id > 0){

  exit(0);
}

if ($richiesta == 'respinta-richiesta' && $richieste_id > 0){

  exit(0);
}




// Qui non dovrebbe arrivarci, però...
http_response_code(404); // know not found
echo '<pre style="color: red;"><strong>Funzione ['.$richiesta.'] non supportata</strong></pre>'."\n";
exit(1);
