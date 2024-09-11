<?php
/**
 * @source /deposito.php 
 * @author Massimo Rainato <maxrainato@libero.it>
 * 
 * Centralino router delle richieste relative alla tabella scansioni_disco 
 * questa pagina gestisce url fatti così:
 * https://archivio.athesis77.it/deposito.php/richiesta/parametro?limit=20# 
 * 
 * Operazioni gestite:
 * 
 * /deposito.php/leggi/{disco_id}. . . . . . . lettura
 * /deposito.php/cartella/{path_completo} . . . lettura
 *   queste due chiamate vanno a recuperare un record in tabella scansioni_disco, 
 *   ed espongono il contenuto in forma di sottocartelle. 
 *   Si deve verificare se in "album" siano presenti album che fanno riferimento 
 *   all'id di scansioni disco e nel caso sia presente caricare 
 *   /album.php/leggi/{album_id}
 * 
 * /deposito.php/cambia-tinta/{scansioni-disco-id}
 */
if (!defined('ABSPATH')){
	include_once("./_config.php");
}

include_once(ABSPATH . "aa-controller/controller-base.php"); // route_from_uri
$uri = $_SERVER['REQUEST_URI'];
$pos_richieste_php = strpos($uri, '/deposito.php/');
$uri = substr($uri, $pos_richieste_php);
$pezzi=route_from_uri($uri, '/deposito.php/');

$richiesta=$pezzi['operazioni'][0];
// check 1 - che richiesta è stata fatta? 
switch($richiesta){
	// queste si
	case 'leggi':
	case 'cartella':
	case 'cambia-tinta':
		break;
			
	// resto no 
	default:
		http_response_code(404); // know not found
		echo '<pre style="color: red;"><strong>Funzione ['.$richiesta.'] non supportata</strong></pre>'."\n";
		exit(1);
		break; // per check 
}

// check 3 - serve un parametro 
if (count($pezzi['operazioni']) < 2){
	http_response_code(403); 
	echo '<pre style="color: red;"><strong>Manca un id</strong></pre>'."\n";
	exit(1);
}

include_once(ABSPATH.'aa-controller/deposito-controller.php'); 
// include_once(ABSPATH.'aa-controller/cartelle-controller.php'); 

if ($richiesta === 'leggi'){
	$scansioni_disco_id = $pezzi['operazioni'][1];
	if (!is_numeric($scansioni_disco_id)){
		http_response_code(404); // know not found
		echo '<pre style="color: red;"><strong>Il parametro deve essere un intero, '
    . 'invece è "'.$scansioni_disco_id.'".</strong></pre>'."\n";
		exit(1);
	}
	$scansioni_disco_id = (int) $scansioni_disco_id;
	if ($scansioni_disco_id < 1){
		http_response_code(404); // know not found
		echo '<pre style="color: red;"><strong>Il parametro deve essere un '
    . 'intero positivo, invece è "'.$scansioni_disco_id.'".</strong></pre>'."\n";
		exit(1);
	}

  leggi_cartella_per_id($scansioni_disco_id); 
	exit(0);
}

if ($richiesta === 'cartella'){
  $livelli = array_slice( $pezzi['operazioni'], 1);
  $percorso_completo = implode('/', $livelli);

  leggi_cartella_per_percorso($percorso_completo);
  exit(0);
}

// check 2 - livello abilitazione per tutte le richieste: almeno modifica
if (!isset($_COOKIE['abilitazione'])){
	http_response_code(404); // know not found
	echo '<pre style="color: red;"><strong>Funzione ['.$richiesta.'] non autorizzata.</strong></pre>'."\n";
	exit(1);	
}
$cookie_abilitazione = str_replace("'", '', $_COOKIE['abilitazione']);
$abilitazione_richiesta = str_replace("'", '', constant('MODIFICA'));
if (strcmp($cookie_abilitazione, $abilitazione_richiesta) < 0){
	http_response_code(404); // know not found
	echo '<pre style="color: red;"><strong>Funzione ['.$richiesta.'] non autorizzata.</strong></pre>'."\n";
	exit(1);	
}


// Qui non dovrebbe arrivarci, però...
http_response_code(404); // know not found
echo '<pre style="color: red;"><strong>Funzione ['.$richiesta.'] non supportata 2</strong></pre>'."\n";
exit(1);
