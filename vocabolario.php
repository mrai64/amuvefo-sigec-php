<?php
/**
 * @source /vocabolario.php 
 * @author Massimo Rainato <maxrainato@libero.it>
 * 
 * Centralino router delle ricerche 
 * https://archivio.athesis77.it/vocabolario.php/elenco/{chiave}
 * 
 */
if (!defined('ABSPATH')){
	include_once("./_config.php");
}
include_once(ABSPATH."aa-controller/controller-base.php");  // route_from_uri
$uri = $_SERVER['REQUEST_URI'];
$pos_richieste_php = strpos($uri, '/vocabolario.php/');
$uri = substr($uri, $pos_richieste_php);
$pezzi=route_from_uri($uri, '/vocabolario.php/');

// richiesta
$richiesta=$pezzi['operazioni'][0];
switch($richiesta){
	case 'elenco-generale':
	case 'aggiungi':
	case 'modifica':
		break; 

	// resto no 
	default:
		http_response_code(404); // know not found
		echo '<p style="font-family:monospace;color: red;">'
		. "\n" . '<strong>Funzione ['.$richiesta.'] non supportata</strong>'
		. "\n<br>" . str_ireplace(';', '; ', serialize($pezzi)) . '</p>';
		exit(1);
		break; 
} // switch richiesta 

include_once(ABSPATH.'aa-controller/vocabolario-controller.php'); 

// senza parametri

// espone l'elenco generale ordinato
if ($richiesta==='elenco-generale'){
	get_elenco_generale();
	exit(0); // Qui non dovrebbe arrivarci, però.
}

// parametro chiave 
$chiave = str_ireplace('/vocabolario.php/aggiungi/', '', $uri);
if (isset($_POST['chiave'])){
	$chiave = $_POST['chiave'];
}
// aggiunge un valore all'elenco delle chiavi
if ($richiesta==='aggiungi' && isset($_POST['aggiungi_vocabolario'])){
	aggiungi_vocabolario($chiave, $_POST);
	exit(0); // Qui non dovrebbe arrivarci, però.
}
// espone il modulo per chiedere il valore
if ($richiesta==='aggiungi'){
	aggiungi_vocabolario($chiave, []);
	exit(0); // Qui non dovrebbe arrivarci, però.
}

// parametro 
$vocabolario_id = 0;
if (isset($pezzi['operazioni'][1])){
	$vocabolario_id=$pezzi['operazioni'][1];
}
if ($richiesta === 'modifica' && isset($_POST['modifica_vocabolario'])){
	modifica_vocabolario( $vocabolario_id, $_POST);
	exit(0);
}
if ($richiesta === 'modifica'){
	modifica_vocabolario( $vocabolario_id, []);
	exit(0);
}

// Anche qui non dovrebbe arrivarci, però.
http_response_code(404); // know not found
echo '<pre style="color: red;"><strong>Funzione ['.$richiesta.'] non supportata</strong></pre>'."\n";
exit(1);
