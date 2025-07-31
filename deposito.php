<?php
/**
 * @source /deposito.php 
 * @author Massimo Rainato <maxrainato@libero.it>
 * 
 * Centralino router delle richieste relative alla tabella deposito 
 * questa pagina gestisce url fatti così:
 * https://archivio.athesis77.it/deposito.php/richiesta/parametro?limit=20# 
 * 
 * Operazioni gestite:
 * 
 * /deposito.php/leggi/{disco_id}. . . . . . . lettura
 * /deposito.php/cartella/{path_completo} . . . lettura
 *   queste due chiamate vanno a recuperare un record in tabella deposito, 
 *   ed espongono il contenuto in forma di sottocartelle. 
 *   Si deve verificare se in "album" siano presenti album che fanno riferimento 
 *   all'id di Deposito e nel caso sia presente caricare 
 *   /album.php/leggi/{album_id}
 * 
 * /deposito.php/cambio-tinta/{deposito-id}?t=tabella=deposito&back=pagina%20a%20cui%20tornare 
 * 
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
	case 'richiesta':
	case 'cambio-tinta':
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

if ($richiesta === 'leggi'){
	$deposito_id = $pezzi['operazioni'][1];
	if (!is_numeric($deposito_id)){
		http_response_code(404); // know not found
		echo '<pre style="color: red;"><strong>Il parametro deve essere un intero, '
    . 'invece è "'.$deposito_id.'".</strong></pre>'."\n";
		exit(1);
	}
	$deposito_id = (int) $deposito_id;
	if ($deposito_id < 1){
		http_response_code(404); // know not found
		echo '<pre style="color: red;"><strong>Il parametro deve essere un '
    . 'intero positivo, invece è "'.$deposito_id.'".</strong></pre>'."\n";
		exit(1);
	}

  leggi_cartella_per_id($deposito_id); 
	exit(0);
}

if ($richiesta === 'cartella'){
  $livelli = array_slice( $pezzi['operazioni'], 1);
  $percorso_completo = implode('/', $livelli);

  leggi_cartella_per_percorso($percorso_completo);
  exit(0);
}

// check 2 - livello abilitazione per tutte le richieste: almeno modifica
$cookie_abilitazione = get_set_abilitazione();
$abilitazione_richiesta = str_replace("'", '', constant('MODIFICA'));
if (strcmp($cookie_abilitazione, $abilitazione_richiesta) < 0){
	http_response_code(404); // know not found
	echo '<pre style="color: red;"><strong>Funzione ['.$richiesta.'] non autorizzata.</strong></pre>'."\n";
	exit(1);	
}

// ci sono i dati, aggiorna la tinta
if ($richiesta === 'cambio-tinta' && isset($_POST['tinta'])){
	cambia_tinta_record($_POST);	
	exit(0);	
}

// mancano i dati, espone il modulo per cambiare la tinta
if ($richiesta === 'cambio-tinta'){
	// deposito.php/cambio-tinta/{id}?t=tabella&back=pagina%20a%20cui%20tornare
	$dati_input=[];
	// per assegnare i dati input alla funzione (non si fanno altri controlli che "la presenza")
	// serve un id 
	// serve un t 
	// serve un back 
	if (!isset($pezzi['operazioni'][1])){
		http_response_code(500);
		echo "manca un id - l'indirizzo è mal formato";
		exit(1);
	}
	if (!isset($pezzi['parametri']['t'])){
		http_response_code(500);
		echo "manca un t - l'indirizzo è mal formato";
		exit(1);
	}
	if (!isset($pezzi['parametri']['back'])){
		http_response_code(500);
		echo "manca un back - l'indirizzo è mal formato";
		exit(1);
	}
	$dati_input['record_id'] = (int) $pezzi['operazioni'][1];
	$dati_input['tabella']   =       $pezzi['parametri']['t'];
	$dati_input['back']      =       $pezzi['parametri']['back'];
	cambia_tinta_record($dati_input);	
	exit(0);
}

// Qui non dovrebbe arrivarci, però...
http_response_code(404); // know not found
echo '<pre style="color: red;"><strong>Funzione ['.$richiesta.'] non supportata 2</strong></pre>'."\n";
exit(1);
