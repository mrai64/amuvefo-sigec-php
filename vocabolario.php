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
	case 'elenco':
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
$chiave = str_ireplace('/vocabolario.php/elenco/', '', $uri);
// ottiene la pagina elenco della chiavi
if ($richiesta==='elenco'){
	echo "\n<br>". $chiave;
	exit(0); // Qui non dovrebbe arrivarci, però.
}

// Anche qui non dovrebbe arrivarci, però.
http_response_code(404); // know not found
echo '<pre style="color: red;"><strong>Funzione ['.$richiesta.'] non supportata</strong></pre>'."\n";
exit(1);
