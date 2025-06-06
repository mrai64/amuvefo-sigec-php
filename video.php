<?php
/**
 * @source /video.php
 * @author Massimo Rainato <maxrainato@libero.it>
 *
 * Centralino router delle richieste
 *
 * /video.php/leggi/{video_id}
 *   carica la scheda singola e
 *   mostra la pagina
 *
 * /video.php/precedente/{video_id}
 * /video.php/seguente/{video_id}
 *   rintraccia, se presente, un video precedente o seguente
 *   inserito nello stesso album
 *
 * /video.php/richiesta/{video_id}?return_to={url_pagina}
 *   inserisce una richiesta per l'originale
 *   e ritorna alla pagina chiamante
 *
 * /video.php/aggiungi_dettaglio/{video_id}
 *   carica la scheda per aggiungere un dettaglio
 *   al video, e gestisce l'inserimento dello
 *   stesso
 * 
 * /video.php/modifica_dettaglio/{dettaglio_id}?video={video_id}
 *   espone la scheda per modificare il record 
 *   di video_dettagli, e poi se presente $_POST
 *   aggiorna il record di video_dettagli 
 * 
 * /video.php/elimina-dettaglio/{dettaglio_id}?video={video_id}
 *   cancellazione non fisica, viene aggiornato il 
 *   campo record_cancellabile_dal
 *
 */
if (!defined('ABSPATH')){
	include_once("./_config.php");
}
include_once(ABSPATH."aa-controller/controller-base.php");  // route_from_uri
$uri = $_SERVER['REQUEST_URI'];
$pos_richieste_php = strpos($uri, '/video.php/');
$uri = substr($uri, $pos_richieste_php);
$pezzi=route_from_uri($uri, '/video.php/');

$richiesta=$pezzi['operazioni'][0];
switch($richiesta){
	case 'leggi':
	case 'precedente':
	case 'seguente':
	case 'richiesta':
	case 'aggiungi_dettaglio':
	case 'modifica_dettaglio':
	case 'elimina-dettaglio':
		break;

	default:
		http_response_code(404); // know not found
		echo '<pre style="color: red;"><strong>Funzione ['.$richiesta.'] non supportata</strong></pre>'."\n";
		exit(1);
		break;
}

// operazioni che non necessitano di parametro

// operazioni che necessitano di parametro
if (count($pezzi['operazioni']) < 2){
	http_response_code(404); // TODO sostituire con il codice errore parametro invalido
	echo '<pre style="color: red;"><strong>Manca un id</strong></pre>'."\n";
	exit(1);
}
$video_id     = $pezzi['operazioni'][1];
$dettaglio_id = $pezzi['operazioni'][1];
if (!is_numeric($video_id)){
	http_response_code(404); // TODO sostituire con il codice errore parametro invalido
	echo '<pre style="color: red;"><strong>Manca un id</strong></pre>'."\n";
	exit(1);
}

$return_to = '';
if (isset($pezzi['parametri']) && isset($pezzi['parametri']['return_to'])){
	$return_to = $pezzi['parametri']['return_to'];
}

$video_to= '';
if (isset($pezzi['parametri']) && isset($pezzi['parametri']['video'])){
	$video_to = $pezzi['parametri']['video'];
}

include_once(ABSPATH.'aa-controller/video-controller.php');
/**
 * LEGGI
 */
if ($richiesta=='leggi'){
	leggi_video_per_id($video_id);
	exit(0);
}

/**
 * PRECEDENTE
 */
if ($richiesta=='precedente'){
	$video_precedente = leggi_video_precedente($video_id);
	leggi_video_per_id($video_precedente);
	exit(0);
}

/**
 * SEGUENTE
 */
if ($richiesta=='seguente'){
	$video_seguente = leggi_video_seguente($video_id);
	leggi_video_per_id($video_seguente);
	exit(0);
}



/**
 * sbarramento abilitazione
 */
if (get_set_abilitazione() <= SOLALETTURA){
	http_response_code(404); // know not found
	echo '<pre style="color: red;"><strong>Funzione ['.$richiesta.'] non abilitata</strong></pre>'."\n";
	exit(1);
}

/**
 * AGGIUNGI DETTAGLIO 
 */
if ($richiesta=='aggiungi_dettaglio' && isset($_POST['chiave'])){
	aggiungi_dettaglio_video_da_modulo($video_id, $_POST);
	exit(0);
}
if ($richiesta=='aggiungi_dettaglio'){
	aggiungi_dettaglio_video_da_modulo($video_id, []);
	exit(0);
}

/**
 * MODIFICA DETTAGLIO 
 */
if ($richiesta=='modifica_dettaglio' && isset($_POST['valore'])){
	modifica_dettaglio_video_da_modulo($dettaglio_id, $_POST);
	exit(0);
}
if ($richiesta=='modifica_dettaglio'){
	modifica_dettaglio_video_da_modulo($dettaglio_id, []);
	exit(0);
}

/**
 * CANCELLA DETTAGLIO
 */
if ($richiesta=='elimina-dettaglio'){
	elimina_dettaglio_video_da_modulo($dettaglio_id);
	exit(0);
}



// Anche qui non dovrebbe arrivarci, però...
http_response_code(404); // know not found
echo '<pre style="color: red;"><strong>Funzione ['.$richiesta.'] non supportata</strong></pre>'."\n";
exit(1);
