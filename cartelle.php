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
 * /cartelle.php/leggi/{disco_id}. . . . . . . lettura
 * /cartelle.php/cartella/{path_completo} . . . lettura
 *   queste due chiamate vanno a recuperare una cartella in tabella scansioni_disco, 
 *   ed espongono il contenuto in forma di sottocartelle. 
 *   Si deve verificare se in "album" siano presenti album che fanno riferimento 
 *   all'id di scansioni disco e nel caso sia presente caricare 
 *   /album.php/leggi/{album_id}
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
if (!defined('ABSPATH')){
	include_once("./_config.php");
}
include_once(ABSPATH . "aa-controller/controller-base.php"); // route_from_uri
$uri = $_SERVER['REQUEST_URI'];
$pezzi=route_from_uri($uri, '/cartelle.php/');
$richiesta=$pezzi['operazioni'][0];
//dbg echo '<pre>'. var_dump($pezzi); 

// secondo elemento obbligatorio
if (count($pezzi['operazioni']) < 2){
	http_response_code(404); // TODO sostituire con il codice errore parametro invalido 
	echo '<pre style="color: red;"><strong>Manca un id</strong></pre>'."\n";
	exit(1);
}
// check 1 - che richiesta è stata fatta? 
switch($richiesta){
	// queste si
	case 'leggi':
	case 'cartella':
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
		
include_once(ABSPATH . "aa-controller/cartelle-controller.php"); // route_from_uri
/* 
	Struttura dell'url attesa da cartelle.php/ in avanti:
	operazione/parametri?parametri-aggiuntivi 
	array $operazioni restituito da route_from_uri
	[0]        [1]
 */


// /cartelle.php/cartella/nome-della-cartella
if ($richiesta == 'cartella'){
		$livelli = array_slice( $pezzi['operazioni'], 1);
		$percorso_completo = implode('/', $livelli);
		leggi_cartella_per_percorso($percorso_completo);
		exit(0);
} 

// /cartelle.php/leggi/scansioni_disco_id
if ($richiesta=='leggi'){
	$scansioni_disco_id = $pezzi['operazioni'][1];
	if (!is_numeric($scansioni_disco_id)){
		http_response_code(404); // know not found
		echo '<pre style="color: red;"><strong>Il parametro deve essere un intero, invece è "'.$scansioni_disco_id.'".</strong></pre>'."\n";
		exit(1);
	}
	$scansioni_disco_id = (int) $scansioni_disco_id;
	if ($scansioni_disco_id < 1){
		http_response_code(404); // know not found
		echo '<pre style="color: red;"><strong>Il parametro deve essere un intero positivo, invece è "'.$scansioni_disco_id.'".</strong></pre>'."\n";
		exit(1);
	}
	leggi_cartella_per_id($scansioni_disco_id); // cartelle-controller
	exit(0);
}

// /cartelle.php/lista-cartelle-sospese/0
if ($richiesta == 'lista-cartelle-sospese'){
	echo lista_cartelle_sospese(); // cartelle-controller
	exit(0);
}

// /cartelle.php/aggiungi-cartella/0 
// 1 di 2 espone il modulo 
if ($richiesta == 'aggiungi-cartella' && !isset($_POST['aggiungi_cartella'])){
	carica_cartelle_da_scansionare();
	exit(0); 
}

// /cartelle.php/aggiungi-cartella/0 + $_POST['aggiungi-cartella]
// 2 di 2 elabora il modulo 
if ($richiesta == 'aggiungi-cartella' && isset($_POST['aggiungi_cartella'])){
	carica_cartelle_da_scansionare();
	exit(0); 
}

// /cartelle.php/archivia-cartella/0 "il primo che trovi"
// /cartelle.php/archivia-cartella/scansioni_cartelle_id 
if($richiesta =='archivia-cartella'){
	$cartella_id = $pezzi['operazioni'][1];
	if (!is_numeric($cartella_id)){
		http_response_code(404); // know not found
		echo '<pre style="color: red;"><strong>Il parametro deve essere un intero, invece è "'.$cartella_id.'".</strong></pre>'."\n";
		exit(1);
	}
	$cartella_id = (int) $cartella_id;
	if ($cartella_id < 1){
		$cartella_id = 0; // con questo parametro viene preso "il primo dei sospesi"
	}
	carica_scansioni_disco_da_scansioni_cartelle($cartella_id); // cartelle-controller
	exit(0);
}
