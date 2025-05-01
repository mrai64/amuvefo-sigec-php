<?php
/**
 * @source /didascalie.php
 * @author Massimo Rainato <maxrainato@libero.it
 * 
 * Centralino router per le richieste relative alle
 * didascalie di album, foto e video.
 * La chiamata alla pagina va fatta con questo schema
 * https://archivio.athesis77.it/didascalie.php/operazione/parametro?altri=parametri#
 * 
 */
// avvio 
if (!defined('ABSPATH')){
	include_once("./_config.php");
}
include_once(ABSPATH . "aa-controller/controller-base.php"); // route_from_uri
$richieste_gestite = [
  'aggiorna',
  'aggiungi',
  'recupera'
];

$uri = $_SERVER['REQUEST_URI'];
$pos_richieste_php = strpos($uri, '/didascalie.php/');
$uri = substr($uri, $pos_richieste_php);
$pezzi=route_from_uri($uri, '/didascalie.php/');

if (count($pezzi['operazioni']) < 2){
	http_response_code(404); // TODO sostituire con il codice errore parametro invalido 
	echo '<p style="font-family:monospace;color:red;">'
  . '<strong>Manca un id</strong></p>';
	exit(1);
}

$richiesta=$pezzi['operazioni'][0];
// filtro richiesta
if (!in_array($richiesta, $richieste_gestite)){
  http_response_code(404); // know not found
  echo '<p style="font-family:monospace;color:red;">'
  . '<strong>Funzione ['.$richiesta.'] non supportata</strong></p>'
  . '<p style="font-family:monospace;">pezzi: '
  . str_ireplace(';', '; ', serialize($pezzi))
  . '</p>';
  exit(1);
}
// e qui comincia il lavoro
include_once(ABSPATH.'aa-controller/didascalie-controller.php'); 

/**
 * Aggiungi didascalia mancante
 */
if ($richiesta == 'aggiungi'){
  $campi=[];
  $campi['tabella_padre']   = isset($pezzi['operazioni'][1]) ? $pezzi['operazioni'][1] : '';
  $campi['record_id_padre'] = isset($pezzi['operazioni'][2]) ? $pezzi['operazioni'][2] : 0;
  if ( $campi['record_id_padre'] < 1){
    http_response_code(404); // know not found
    echo '<p style="font-family:monospace;color: red;">'
    . '<strong>Funzione ['.$richiesta.'] non supportata</strong> [2]</p>'
    . '<p style="font-family:monospace;">pezzi: '
    . str_ireplace(';', '; ', serialize($pezzi))
    . '</p>';
    exit(1);
  }
  // propone modulo
  if (!isset($_POST['aggiorna_didascalia'])){
    aggiungi_didascalia($campi);
    exit(0);
  }
} // aggiungi

// record zero - aggiungi nuova didascalia
$didascalia_id = (int) $pezzi['operazioni'][1];
if ($didascalia_id == 0 && isset($_POST['record_id_padre']) && $_POST['record_id_padre'] > 0 ){
  aggiungi_didascalia($_POST);
  exit(0);
}

/**
 * Modifica didascalia presente 
 */
if ( $didascalia_id < 1){
  http_response_code(404); // know not found
  echo '<p style="font-family:monospace;color: red;">'
  . '<strong>Funzione ['.$richiesta.'] non supportata</strong> [2]</p>'
  . '<p style="font-family:monospace;">pezzi: '
  . str_ireplace(';', '; ', serialize($pezzi))
  . '</p>';
  exit(1);
}
// espone il modulo  
if ($richiesta == 'aggiorna' && !isset($_POST['aggiorna_didascalia'])){
	aggiorna_didascalia($didascalia_id, []);
	exit(0);
} 

// aggiorna dal modulo  
if ($richiesta == 'aggiorna' ){
	aggiorna_didascalia($didascalia_id, $_POST);
	exit(0);
}


// Qui non dovrebbe arrivarci, però...
http_response_code(404); // know not found
echo '<pre style="color: red;"><strong>Funzione ['.$richiesta.'] non supportata [3]</strong></pre>'."\n";
exit(1);
