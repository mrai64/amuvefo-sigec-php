<?php
/**
 * @source /ricerche.php 
 * @author Massimo Rainato <maxrainato@libero.it>
 * 
 * Centralino router delle ricerche 
 * https://www.fotomuseoathesis.it/ricerche.php/album
 * https://www.fotomuseoathesis.it/ricerche.php/fotografie
 * https://www.fotomuseoathesis.it/ricerche.php/video
 * 
 * Invece la pagina che espone il modulo fi ricerca è
 * /ricerca.php 
 * 
 * La gestione delle chiavi di ricerca, quelle che danno un 
 * senso ai valori accoppiati, viene fatta attraverso il 
 * router /chiavi.php e il vocabolario per i gruppi limitati 
 * di valori validi on /vocabolari.php (sempre al plurale) 
 * 
 * Nota: questa soluzione è stata preferita a quella di 
 * /album.php/ricerca 
 * /fotografie.php/ricerca 
 * perché la "ricerca" condivide molte funzioni e quindi 
 * si sarebbe resa comunque necessario un file condiviso.
 * 
 */
if (!defined('ABSPATH')){
	include_once("./_config.php");
}
include_once(ABSPATH."aa-controller/controller-base.php");  // route_from_uri
$uri = $_SERVER['REQUEST_URI'];
$pos_richieste_php = strpos($uri, '/ricerche.php/');
$uri = substr($uri, $pos_richieste_php);
$pezzi=route_from_uri($uri, '/ricerche.php/');

$richiesta=$pezzi['operazioni'][0];
switch($richiesta){
	case 'album':
	case 'album-semplice':
	case 'fotografie':
	case 'fotografie-semplice':
//case 'video':
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
} // switch richiesta 

$ricerca_id = 0;
/*
 * 
 * 
// check 2 - il parametro dev'essere intero senza segno 
if (count($pezzi['operazioni'])>1){
	$ricerca_id = $pezzi['operazioni'][1];
	if (!is_numeric($ricerca_id)){
		http_response_code(404); // TODO sostituire con il codice errore parametro invalido 
		echo '<pre style="color: red;"><strong>Manca un id valido</strong>[1]</pre>'."\n";
		exit(1);

	}
	$ricerca_id = (int) $ricerca_id;
	if ($ricerca_id < 1){
		http_response_code(404); // TODO sostituire con il codice errore parametro invalido 
		echo '<pre style="color: red;"><strong>Manca un id valido</strong>[2]</pre>'."\n";
		exit(1);
	}		
	// ricerca per id - recupera la ricerca e la espone per essere eseguita 
	// ricerca per post - verifica se è gia registrata o gli assegna un nuovo id 
}	
	echo '<br>$_POST'.serialize($_POST); 
 * 
 */		
	
include_once(ABSPATH.'aa-controller/ricerche-controller.php'); 
// esegue ricerca per $_POST

if ($ricerca_id== 0 && !isset($_POST['esegui_ricerca'])){
	http_response_code(404); // TODO sostituire con il codice errore parametro invalido 
	echo '<pre style="color: red;"><strong>Mancano i dati per la ricerca</strong>[2]</pre>'."\n";
	exit(1);
}

if ($richiesta=='album-semplice') {
	// passa alla ricerca_album che ritorna json con errore o con dati
	echo get_lista_album_semplice( $_POST );
	exit(0);
}

if ($richiesta=='album') {
	// passa alla ricerca_album che ritorna html con elenco di album
	echo get_lista_album( $_POST );
	exit(0);
}

if ($richiesta=='fotografie-semplice') {
	// passa alla ricerca e ritorna json con errore o dati
	echo get_lista_fotografie_semplice( $_POST );
	exit(0);
}

if ($richiesta=='fotografie') {
	// passa alla ricerca e ritorna html con elenco di fotografie
	echo get_lista_fotografie( $_POST );
	exit(0);
}

// Qui non dovrebbe arrivarci, però...
http_response_code(404); // know not found
echo '<pre style="color: red;"><strong>Funzione ['.$richiesta.'] non supportata</strong></pre>'."\n";
exit(1);
