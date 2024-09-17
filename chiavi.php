<?php
/**
 * @source /chiavi.php 
 * @author Massimo Rainato <maxrainato@libero.it>
 * 
 * Centralino router delle ricerche 
 * https://archivio.athesis77.it/chiavi.php/aggiungi
 * https://archivio.athesis77.it/chiavi.php/modifica/{id}
 * 
 */
if (!defined('ABSPATH')){
	include_once("./_config.php");
}
include_once(ABSPATH."aa-controller/controller-base.php");  // route_from_uri
$uri = $_SERVER['REQUEST_URI'];
$pos_richieste_php = strpos($uri, '/chiavi.php/');
$uri = substr($uri, $pos_richieste_php);
$pezzi=route_from_uri($uri, '/chiavi.php/');

// richiesta
$richiesta=$pezzi['operazioni'][0];
switch($richiesta){
	case 'elenco-datalist':
	case 'elenco':
	case 'aggiungi':
	case 'modifica':
		break; 

	// resto no 
	default:
		http_response_code(404); // know not found
		echo '<pre style="color: red;"><strong>Funzione ['.$richiesta.'] non supportata</strong></pre>'."\n";
		echo '<pre style="color: red;">';
		echo var_dump($pezzi);
		echo '</pre>'."\n";
		exit(1);
		break; 
} // switch richiesta 

include_once(ABSPATH.'aa-controller/chiavi-controller.php'); 

// senza parametri 

// ottiene la datalist delle chiavi - vedi anche elenchi.php
if ($richiesta==='elenco-datalist'){
	$ret = get_chiavi_datalist();
	echo $ret;
	exit(0);
}

// ottiene la pagina elenco delle chiavi
if ($richiesta==='elenco'){
	elenco_chiavi();
	exit(0);
}



// aggiungi - aggiorna la tabella oppure espone il modulo
if ($richiesta==='aggiungi' && isset($_POST['chiave'])){
  aggiungi_chiave_ricerca($_POST);
  exit(0);
}
if ($richiesta==='aggiungi'){
  aggiungi_chiave_ricerca([]);
  exit(0);
}

// con id 
if (count($pezzi['operazioni']) < 2){
	http_response_code(403);  
	echo '<pre style="color: red;"><strong>Manca un id</strong></pre>'."\n";
	exit(1);
}
//
// check 2 - il parametro dev'essere intero senza segno 
$chiave_id = $pezzi['operazioni'][1];

// modifica 1 di 2 
// espone il modulo  
if ($richiesta === 'modifica' && !isset($_POST['chiave'])){
	modifica_chiave_ricerca($chiave_id, []);
	exit(0);
} 
// modifica 2 di 2 
// aggiorna dal modulo  
if ($richiesta === 'modifica'){
	modifica_chiave_ricerca($chiave_id, $_POST);
	exit(0);
}

// Anche qui non dovrebbe arrivarci, però...
http_response_code(404); // know not found
echo '<pre style="color: red;"><strong>Funzione ['.$richiesta.'] non supportata</strong></pre>'."\n";
exit(1);
