<?php
/**
 * @source /fotografie.php 
 * @author Massimo Rainato <maxraianto@libero.it>
 * 
 * Centralino router delle richieste 
 * /fotografie.php/leggi/{fotografie_id}
 *   carica la scheda della fotografia e 
 *   mostra la pagina 
 * 
 * /fotografie.php/richiesta/{fotografie_id}
 *   carica in tabella la richiesta e riespone la fotografia 
 * 
 * /fotografie.php/precedente/{fotografie_id}
 *   carica la scheda della fotografia precedente 
 * 
 * /fotografie.php/seguente/{fotografie_id}
 *   carica la scheda della fotografia seguente 
 * 
 * /fotografie.php/elimina-dettaglio/{dettaglio_id}
 *   cancella non fisicamente il dettaglio già presente 
 *   e ritorna alla vista della fotografia 
 * 
 * /fotografie.php/modifica-dettaglio/{dettaglio_id}
 *   modifica il dettaglio già presente - presenta un modulo
 * 
 * /fotografia.php/aggiorna_dettaglio/{dettaglio_id}
 *   esegue l'aggiornamento del dettaglio e ritorna alla vista della fotografia di appartenenza 
 * 
 * /fotografie.php/carica_dettaglio/{fotografie_id}
 *   prepara e mostra la pagina di aggiunta dettaglio 
 * 
 * /fotografie.php/aggiungi-dettaglio/{fotografie_id}
 *   esegue l'aggiunta del dettaglio fotografia 
 * 
 * /fotografie.php/carica-dettagli-da-fotografia/{fotografia_id}
 *   apre la fotografia e cerca dati exif, 
 *   e/o carica dettagli dal nome file
 *   NON carica dettagli dall'album, perché dovrebbero essere già nell'album 
 * 
 * TODO /fotografie.php/cancella/{fotografie_id}
 *  cancellazione non fisica della fotografia che sparisce dalla vista. 
 * 
 */
//dbg echo '<pre style="max-width:50rem;">debug on'."\n";
if (!defined('ABSPATH')){
	include_once("./_config.php");
}
include_once(ABSPATH."aa-controller/controller-base.php");  // route_from_uri
$uri = $_SERVER['REQUEST_URI'];
$pos_richieste_php = strpos($uri, '/fotografie.php/');
$uri = substr($uri, $pos_richieste_php);
$pezzi=route_from_uri($uri, '/fotografie.php/');

$richiesta=$pezzi['operazioni'][0];
switch($richiesta){
	case 'leggi':
	case 'richiesta':
  case 'precedente':
  case 'seguente':
	case 'elimina-dettaglio':
	case 'modifica-dettaglio':
	case 'aggiorna_dettaglio':
	case 'carica-dettaglio':
	case 'aggiungi-dettaglio':
	case 'carica-dettagli-da-fotografia':
//  case 'cancella':
		break;

	default:
		http_response_code(404); // know not found
		echo '<pre style="color: red;"><strong>Funzione ['.$richiesta.'] non supportata</strong></pre>'."\n";
		exit(1);
		break; // per check 
}

if (count($pezzi['operazioni']) < 2){
	http_response_code(404); // TODO sostituire con il codice errore parametro invalido 
	echo '<pre style="color: red;"><strong>Manca un id</strong></pre>'."\n";
	exit(1);
}
// check 
// verifica unsigned int 
$fotografie_id = $pezzi['operazioni'][1];
$dettaglio_id  = $pezzi['operazioni'][1]; // lo stesso ma con altro significato 
if (!is_numeric($fotografie_id)){
	http_response_code(404); // TODO sostituire con il codice errore parametro invalido 
	echo '<pre style="color: red;"><strong>Manca un id</strong></pre>'."\n";
	exit(1);
}
if ($richiesta != 'carica-dettagli-da-fotografia' && ($fotografie_id)<1 ){
	http_response_code(404); // TODO sostituire con il codice errore parametro invalido 
	echo '<pre style="color: red;"><strong>Manca un id</strong></pre>'."\n";
	exit(1);
}

include_once(ABSPATH."aa-controller/fotografie-controller.php"); // la libreria 'dedicata' 
/**
 * LEGGI
 */
if ($richiesta == 'leggi'){
	leggi_fotografie_per_id($fotografie_id);
	exit(0); // qui non ci dovrebbe passare, però... 
}

/**
 * PRECEDENTE
 */
if ($richiesta == 'precedente'){
	$fotografia_precedente = leggi_fotografia_precedente($fotografie_id);
	// $_SESSION['messaggio]
	leggi_fotografie_per_id($fotografia_precedente);
	exit(0);
}

/**
 * SEGUENTE
 */
if ($richiesta == 'seguente'){
	$fotografia_seguente = leggi_fotografia_seguente($fotografie_id);
	// $_SESSION['messaggio]
	leggi_fotografie_per_id($fotografia_seguente);
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
 * CARICA dettaglio passando per la tabella fotografie o per un fotografia_id 
 *
 */
if ($richiesta == 'carica-dettagli-da-fotografia'){
	carica_dettagli_da_fotografia($fotografie_id);
	exit(0);
}


/**
 * RICHIESTA
 */
if ($richiesta == 'richiesta'){
	carica_richiesta_fotografie_per_id($fotografie_id);
	// $_SESSION['messaggio]
	leggi_fotografie_per_id($fotografie_id);
	exit(0);
}

/**
 * modifica dettaglio 1/2 mostra il modulo 
 */
if ($richiesta == 'modifica-dettaglio'){
	modifica_fotografie_dettagli_da_modulo($dettaglio_id, []);
	exit(0);
}

/**
 * modifica dettaglio 2/2 aggiorna 
 */
if ($richiesta == 'aggiorna_dettaglio' && !isset($_POST['aggiorna_dettaglio'])){
	modifica_fotografie_dettagli_da_modulo($dettaglio_id, []);
	exit(0);
}
if ($richiesta == 'aggiorna_dettaglio'){
	modifica_fotografie_dettagli_da_modulo($dettaglio_id, $_POST);
	exit(0);
}

/**
 * carica dettaglio 1/2 mostra il modulo 
 */
if ($richiesta == 'carica-dettaglio'){
	aggiungi_fotografie_dettagli_da_modulo($fotografie_id, []);
	exit(0);
}

/**
 * carica dettaglio 2/2 aggiunge 
 */
if ($richiesta == 'aggiungi-dettaglio' && isset($_POST['aggiungi_dettaglio'])){
	aggiungi_fotografie_dettagli_da_modulo($fotografie_id, $_POST);
	exit(0);
}


/**
 * elimina dettaglio  
 */
if ($richiesta == 'elimina-dettaglio'){
	elimina_dettaglio_fotografia($dettaglio_id);
	exit(0);
}

// Anche qui non dovrebbe arrivarci, però...
http_response_code(404); // know not found
echo '<pre style="color: red;"><strong>Funzione ['.$richiesta.'] non supportata</strong></pre>'."\n";
exit(1);
