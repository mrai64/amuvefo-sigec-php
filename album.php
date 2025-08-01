<?php
/**
 * @source /album.php 
 * @author Massimo Rainato <maxrainato@libero.it>
 * 
 * Centralino router delle richieste 
 * https://archivio.athesis77.it/album.php/richiesta/parametri?limit=20#
 * 
 * operazioni gestite:
 * /album.php/leggi/{album_id} 
 *   leggi dalla tabella album e 
 *   mostra album a video 
 * 
 * /album.php/aggiungi-album/0 "il primo che trovi"
 * /album.php/aggiungi-album/{record_id_in_deposito}
 *   aggiunge in tabella album partendo dall'id di scansione_disco
 *   aggiunge i dettagli dell'album 
 *   aggiunge le fotografie dell'album 
 *   aggiunge i video dell'album 
 *   mostra album a video 
 * 
 * /album.php/richiesta/{album_id}
 *   aggiunge alla tabella delle richieste l'album 
 * 
 * TODO cancella/{album_id}
 *   cancellazione logica del record - attenzione che sia un album 
 *   già svuotato di dettagli e di contenuti, tutto quello che  
 *   dipende dall'album deve essere già stato precedentemente 
 *   marcato cancellabile o anche cancellato fisicamente 
 * 
 * /album.php/aggiungi-dettaglio/{dettaglio_id}
 *   aggiunge un dettaglio se sono presenti i dati di un modulo 
 *   oppure prepara ed espone un modulo 
 * 
 * /album.php/modifica-dettaglio/{dettaglio_id}
 *   modifica un dettaglio se sono presenti i dati 
 *   di un modulo oppure 
 *   carica e presenta il modulo per la modifica 
 * 
 * /album.php/modifica-titolo/{album_id}
 *   modifica il titolo dell'album che a differenza
 *   del nome cartella può contenere lettere accentate e altro 
 * 
 */
//dbg echo '<pre style="max-width:50rem;">debug on'."\n";
if (!defined('ABSPATH')){
	include_once("./_config.php");
}
include_once(ABSPATH."aa-controller/controller-base.php");  // route_from_uri
$uri = $_SERVER['REQUEST_URI'];
$pos_richieste_php = strpos($uri, '/album.php/');
$uri = substr($uri, $pos_richieste_php);
$pezzi=route_from_uri($uri, '/album.php/');

$richiesta=$pezzi['operazioni'][0];
// check 1 - che richiesta è stata fatta? 
switch($richiesta){
	// queste si
	case 'leggi':
	case 'aggiungi-album':
	case 'richiesta':		
	case 'aggiungi-dettaglio':
	case 'modifica-dettaglio':
	case 'aggiorna-dettaglio':
	case 'elimina-dettaglio':
	case 'modifica-titolo':
		break;

	// resto no 
	default:
		http_response_code(404); // know not found
		echo '<pre style="color: red;"><strong>Funzione ['.$richiesta.'] non supportata</strong></pre>'."\n";
		echo '<pre style="color: red;">';
		echo var_dump($pezzi);
		echo '</pre>'."\n";
		exit(1);
		break; // per check 
}

if (count($pezzi['operazioni']) < 2){
	http_response_code(404); // TODO sostituire con il codice errore parametro invalido 
	echo '<pre style="color: red;"><strong>Manca un id</strong></pre>'."\n";
	exit(1);
}
//
// check 2 - il parametro dev'essere intero senza segno 
$album_id           = $pezzi['operazioni'][1];
$deposito_id = $album_id; // il parametro è sempre il primo, 
$dettaglio_id       = $album_id; // ma dipende dalla richiesta il 'chi è chi'
if (!is_numeric($album_id)){
	http_response_code(404); // TODO sostituire con il codice errore parametro invalido 
	echo '<pre style="color: red;"><strong>Manca un id valido</strong></pre>'."\n";
	exit(1);
}
$album_id = (int) $album_id;
// aggiungi-album può avere zero 
if ($richiesta != 'aggiungi-album' && ($album_id < 1)){
	http_response_code(404); // TODO sostituire con il codice errore parametro invalido 
	echo '<pre style="color: red;"><strong>Manca un id valido</strong></pre>'."\n";
	exit(1);
}

include_once(ABSPATH."aa-controller/album-controller.php");
include_once(ABSPATH."aa-controller/fotografie-controller.php");

/**
 * LEGGI
 */
if ($richiesta == 'leggi'){
	leggi_album_per_id($album_id);// carica i dati ed espone la mappa
	exit(0); // qui non dovrebbe arrivarci, però...
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
 * AGGIUNGI ALBUM - controller album
 * Legge deposito e carica album, dettagli album e fotografie o video 
 * 
 * /album.php/aggiungi-album/0                    prende il primo che trova 
 * /album.php/aggiungi-album/{deposito_id} puntuale 
 */
if ($richiesta == 'aggiungi-album'){
	carica_album_dettagli_foto_video($deposito_id);
	exit(0); // qui non dovrebbe arrivarci, però...
} // aggiungi 

// TODO Si può spostare qui il controllo che l'id sia maggiore di zero 

if ($richiesta == 'richiesta'){
	carica_richiesta_album($album_id);
	//
	leggi_album_per_id($album_id); // carica i dati ed espone la mappa
	exit(0); // qui non dovrebbe arrivarci, però...
} // richiesta originali 

// aggiungi-dettaglio 1 di 2 
// espone il modulo per aggiungere il dettaglio all'album 
if ($richiesta == 'aggiungi-dettaglio' && !isset($_POST['valore'])){
	aggiungi_dettaglio_album_da_modulo($album_id, []);
	exit(0);
} 
// aggiunge il dettaglio all'album 
if ($richiesta == 'aggiungi-dettaglio' ){
	aggiungi_dettaglio_album_da_modulo($album_id, $_POST);
	exit(0);
}

// modifica-dettaglio 1 di 2 
// espone il modulo per aggiungere il dettaglio all'album 
if ($richiesta == 'modifica-dettaglio' && !isset($_POST['valore'])){
	modifica_dettaglio_album_da_modulo($dettaglio_id, []);
	exit(0);
} 
// modifica-dettaglio 2 di 2 
// modifica il dettaglio all'album dal modulo  
if ($richiesta == 'modifica-dettaglio' ){
	modifica_dettaglio_album_da_modulo($dettaglio_id, $_POST);
	exit(0);
}

if ($richiesta == 'elimina-dettaglio'){
	cancella_album_dettagli($dettaglio_id);
	exit(0);
}

/**
 * Modifica titolo
 */
if ($richiesta == 'modifica-titolo' && !isset($_POST['titolo'])){
	modifica_titolo_album($album_id, []);
	exit(0);
} 
if ($richiesta == 'modifica-titolo' ){
	modifica_titolo_album($album_id, $_POST);
	exit(0);
}


// Anche qui non dovrebbe arrivarci, però...
http_response_code(404); // know not found
echo '<pre style="color: red;"><strong>Funzione ['.$richiesta.'] non supportata</strong></pre>'."\n";
exit(1);
