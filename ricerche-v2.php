<?php
/**
 * @source /ricerche-v2.php 
 * @author Massimo Rainato <maxrainato@libero.it>
 * 
 * Centralino router delle ricerche 
 * /ricerche-v2.php/ricerca
 * Questa riceve il modulo e prepara la pagina di risposta contenente
 * i risultati.
 * 
 */
// init 
if (!defined('ABSPATH')){
	include_once("./_config.php");
}
include_once(ABSPATH.'aa-controller/controller-base.php');

// ottengo i pezzi della chiamata operazioni & parametri
$router = '/ricerche-v2.php/';
// strippo la parte in testa 
$uri = substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], $router));
$pezzi=route_from_uri($uri, $router);
// operazione richiesta
$richiesta = $pezzi['operazioni'][0];

// check 
switch ($richiesta) {
  case 'ricerca':
  case 'leggi':
  case 'avanti':
  case 'indietro':
    break;
  
  default:
    // segnalazione ko
    echo '<p style="font-family:monospace">'
    . "la funzione $richiesta non è tra quelle gestite."
    . '<br>uri: ' . $_SERVER['REQUEST_URI']
    . '<br>pezzi: ' . str_ireplace(';', '; ', serialize($pezzi))
    . '</p>';
    exit(1);
    break;
}

include_once(ABSPATH.'aa-controller/ricerche-v2-controller.php');
/**
 * ricerca - se mancano i dati espone il modulo
 * se i dati ci sono li gestisce ed espone la pagina dei risultati
 */
if ($richiesta == 'ricerca' && !isset($_POST['esegui_ricerca'])){
  require_once(ABSPATH.'aa-view/ricerca-v2-chiedi-view.php');
  exit(0); //   
}
if ($richiesta == 'ricerca'){
  // sanificazione _post 

  // richiamo
  nuova_ricerca_semplice($_POST);
  exit(0); //   
}

$ricerca_id = $pezzi['operazioni'][1];
if ($richiesta == 'leggi' && $ricerca_id > 0) {
  leggi_ricerca($ricerca_id);
  exit(0);
}

// per avanti e indietro servono altro che un parametro, 4
// avanti/{ricerca_id}/{gruppo}/{ultimo_prec}/{tot}
if ($richiesta == 'avanti' && !isset($pezzi['operazioni'][4])){
  echo '<p style="font-family:monospace">'
  . "AD USO DEBUG<br>la funzione $richiesta non è tra quelle gestite."
  . '<br>uri: ' . $_SERVER['REQUEST_URI']
  . '<br>pezzi: ' . str_ireplace(';', '; ', serialize($pezzi))
  . '<br>post: ' . str_ireplace(';', '; ', serialize($_POST))
  . '</p>';
  exit(1);  
}
if ($richiesta === 'avanti'){
  $dati_input=[];
  $dati_input['ricerca_id'] = $pezzi['operazioni'][1];
  $dati_input['gruppo']     = $pezzi['operazioni'][2];
  $dati_input['ultimo']     = $pezzi['operazioni'][3];
  $dati_input['tot']        = $pezzi['operazioni'][4];
  // ritorna html da esporre con dati o con messaggio di errore 
  echo get_blocco_ricerca_avanti( $dati_input);
  exit(0);
}

// indietro/{ricerca_id}/{gruppo}/{primo}/{tot}
if ($richiesta == 'indietro' && !isset($pezzi['operazioni'][4])){
  echo '<p style="font-family:monospace">'
  . "AD USO DEBUG<br>la funzione $richiesta non è tra quelle gestite."
  . '<br>uri: ' . $_SERVER['REQUEST_URI']
  . '<br>pezzi: ' . str_ireplace(';', '; ', serialize($pezzi))
  . '<br>post: ' . str_ireplace(';', '; ', serialize($_POST))
  . '</p>';
  exit(1);  
}
if ($richiesta === 'indietro'){
  $dati_input=[];
  $dati_input['ricerca_id'] = $pezzi['operazioni'][1];
  $dati_input['gruppo']     = $pezzi['operazioni'][2];
  $dati_input['primo']      = $pezzi['operazioni'][3];
  $dati_input['tot']        = $pezzi['operazioni'][4];
  // ritorna html da esporre con dati o con messaggio di errore 
  echo get_blocco_ricerca_indietro( $dati_input);
  exit(0);
}

// se arrivo qui è una situa non gestita
echo '<p style="font-family:monospace">'
. "AD USO DEBUG<br>la funzione $richiesta non è tra quelle gestite."
. '<br>uri: ' . $_SERVER['REQUEST_URI']
. '<br>pezzi: ' . str_ireplace(';', '; ', serialize($pezzi))
. '<br>post: ' . str_ireplace(';', '; ', serialize($_POST))
. '</p>';
exit(1);
