<?php
/**
 * @source /zona_intro.php
 * @author Massimo Rainato <maxrainato@libero.it>
 *
 * Centralino router delle richieste
 * questa pagina gestisce url fatti così:
 * https://archivio.athesis77.it/zona_intro.php/richiesta/parametro?limit=20#
 *
 * Operazioni gestite:
 *
 * /zona_intro.php/lista-cartelle-sospese/0
 *   questa fornisce una lista di cartelle inserite
 *   in zona_intro e pronte per caricare la tabella deposito
 *
 * /zona_intro.php/aggiungi-cartella/0
 *   questa fa vedere il modulo di amministrazione che permette
 *   di aggiungere cartelle alla tabella zona_intro
 *   Se presente il _POST inserisce in tabella e ripropone il modulo, aggiornato
 *
 * /zona_intro.php/archivia-cartella/{zona_intro_id}
 *
 * /zona_intro.php/reset-status/{zona_intro_id}
 *   Per le situazioni in cui si vuole rimettere una cartella in
 *   lavorazione ma è rimata bloccata per qualche errore su "in corso"
 *
 */

// recupero parametri
 if (!defined('ABSPATH')){
	include_once('./_config.php');
}
include_once(ABSPATH.'aa-controller/controller-base.php');
$uri = $_SERVER['REQUEST_URI'];
$pos_richieste_php = strpos($uri, '/zona_intro.php/');
$uri = substr($uri, $pos_richieste_php);
$pezzi=route_from_uri($uri, '/zona_intro.php/');

$richiesta=$pezzi['operazioni'][0];

// check 1 - che richiesta è stata fatta?
switch($richiesta){
	// queste si
	case 'lista-cartelle-sospese':
	case 'archivia-cartella':
	case 'aggiungi-cartella':
	case 'reset-status':
		break;
			
	// resto no
	default:
		http_response_code(404); // know not found
		echo '<pre style="color: red;"><strong>Funzione ['.$richiesta.'] non supportata</strong></pre>'."\n";
		exit(1);
		break; // per check
}

// Operazioni
include_once(ABSPATH . "aa-controller/zona-intro-controller.php"); // route_from_uri

// /zona_intro.php/lista-cartelle-sospese/
if ($richiesta === 'lista-cartelle-sospese'){
	echo lista_cartelle_sospese(); // zona-intro-controller
	exit(0);
}

// /zona_intro.php/aggiungi-cartella/
// i dati ci sono, elabora il modulo - carica le cartelle in zona_intro
if ($richiesta === 'aggiungi-cartella' && isset($_POST['aggiungi_cartella'])){
	carica_cartelle_in_zona_intro( $_POST );
	exit(0); //
}
// i dati mancano, espone il modulo
if ($richiesta === 'aggiungi-cartella'){
	carica_cartelle_in_zona_intro([]);
	exit(0);
}

$cartella_id = (isset($pezzi['operazioni'][1])) ? $pezzi['operazioni'][1] : 0;
$cartella_id = (is_numeric($cartella_id) && $cartella_id > 0) ? $cartella_id : 0;
// carica da zona_intro in tabella deposito: album e fotografie e video
if ($richiesta === 'archivia-cartella'){
	carica_cartelle_in_deposito($cartella_id); // zona-intro-controller
	exit(0);
}

//
if ($richiesta === 'reset-status'){
	reset_stato_lavori_cartelle( $cartella_id);
	header("Refresh:1; url=".URLBASE."zona_intro.php/aggiungi-cartella/0");
	exit(0);
}

// Qui non dovrebbe arrivarci, però...
http_response_code(404); // know not found
echo '<pre style="color: red;"><strong>'
. 'Funzione ['.$richiesta.'] non supportata 2'
. '</strong></pre>';
exit(1);
