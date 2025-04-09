<?php
/**
 * @source /cartelle.php 
 * @author Massimo Rainato <maxrainato@libero.it>
 * 
 * Centralino router delle richieste 
 * questa pagina gestisce url fatti così:
 * https://archivio.athesis77.it/cartelle.php/richiesta/parametro?limit=20# 
 * 
 * Operazioni gestite:
 * 
 * /cartelle.php/lista-cartelle-sospese/0 
 *   questa fornisce una lista di cartelle inserite 
 *   in scansioni_cartelle e pronte per caricare scansioni_disco
 * 
 * /cartelle.php/aggiungi-cartella/0 
 *   questa fa vedere il modulo di amministrazione che permette
 *   di aggiungere cartelle alla tabella scansioni_cartelle
 *   Se presente il _POST inserisce in tabella e ripropone il modulo, aggiornato  
 * 
 * /cartelle.php/archivia-cartella/{scansioni_cartelle_id}
 * 
 */

// recupero parametri  
 if (!defined('ABSPATH')){
	include_once('./_config.php');
}
include_once(ABSPATH.'aa-controller/controller-base.php');
$uri = $_SERVER['REQUEST_URI'];
$pos_richieste_php = strpos($uri, '/cartelle.php/');
$uri = substr($uri, $pos_richieste_php);
$pezzi=route_from_uri($uri, '/cartelle.php/');

$richiesta=$pezzi['operazioni'][0];

// secondo elemento obbligatorio
if (count($pezzi['operazioni']) < 2){
	http_response_code(404); // TODO sostituire con il codice errore parametro invalido 
	echo '<pre style="color: red;"><strong>Manca un id</strong></pre>'."\n";
	exit(1);
}

// check 1 - che richiesta è stata fatta? 
switch($richiesta){
	// queste si
	case 'lista-cartelle-sospese':
	case 'archivia-cartella':
	case 'aggiungi-cartella':
		break;
			
	// resto no 
	default:
		http_response_code(404); // know not found
		echo '<pre style="color: red;"><strong>Funzione ['.$richiesta.'] non supportata</strong></pre>'."\n";
		exit(1);
		break; // per check 
}

// Operazioni 
include_once(ABSPATH . "aa-controller/cartelle-controller.php"); // route_from_uri

// /cartelle.php/lista-cartelle-sospese/0
if ($richiesta == 'lista-cartelle-sospese'){
	echo 'lista cartelle sospese<br />';
	echo lista_cartelle_sospese(); // cartelle-controller
	exit(0);
}

// /cartelle.php/aggiungi-cartella/0 + $_POST['aggiungi-cartella]
// i dati ci sono, elabora il modulo - carica le cartelle in scansioni_cartelle
if ($richiesta == 'aggiungi-cartella' && isset($_POST['aggiungi_cartella'])){
	// TODO a prescindere da cosa contiene, sanificare $_POST
	carica_cartelle_in_scansioni_cartelle( $_POST );
	exit(0); //
}

// /cartelle.php/aggiungi-cartella/0 
// i dati mancano, espone il modulo 
if ($richiesta == 'aggiungi-cartella'){
	carica_cartelle_in_scansioni_cartelle([]);
	exit(0); 
}

// /cartelle.php/archivia-cartella/0 "il primo che trovi"
// /cartelle.php/archivia-cartella/scansioni_cartelle_id 
if($richiesta =='archivia-cartella'){
	$cartella_id = $pezzi['operazioni'][1];
	$cartella_id = (is_numeric($cartella_id) && $cartella_id > 0) ? $cartella_id : 0;
	carica_cartelle_in_scansioni_disco($cartella_id); // cartelle-controller
	exit(0);
}

// Qui non dovrebbe arrivarci, però...
http_response_code(404); // know not found
echo '<pre style="color: red;"><strong>Funzione ['.$richiesta.'] non supportata 2</strong></pre>'."\n";
exit(1);
