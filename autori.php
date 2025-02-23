<?php
/**
 * @source /autori.php
 * @author Massimo Rainato <maxrainato@libero.it>
 * 
 * Centralino router delle richieste per la tabella autori_elenco 
 * 
 * /autori.php/elenco-autori/ 
 * /autori.php/aggiungi/
 * /autori.php/modifica/{id}
 *  
 */
if (!defined('ABSPATH')){
	include_once("./_config.php");
}
include_once(ABSPATH."aa-controller/controller-base.php");  // route_from_uri
$uri = $_SERVER['REQUEST_URI'];
$pos_richieste_php = strpos($uri, '/autori.php/');
$uri = substr($uri, $pos_richieste_php);
$pezzi=route_from_uri($uri, '/autori.php/');

$richiesta=$pezzi['operazioni'][0];
// check 1 - che richiesta è stata fatta? 
switch($richiesta){
	// queste si
	case 'elenco-autori':
	case 'modifica':
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
include_once(ABSPATH."aa-controller/autori-controller.php"); // la libreria 'dedicata' 

//
if ($richiesta === 'elenco-autori'){
  elenco_autori();
  exit(0);
}

// blocco abilitazione 
// operazioni ricercate amministratore - sostituisce controllo-abilitazione.php
// può essere "1 lettura" ma anche "'1 lettura'"
// ("'7 amministrazione'" < "1 lettura" ) === true
$abilitazione_cookie   = str_replace("'", '', $_COOKIE['abilitazione']);
$abilitazione_modifica = str_replace("'", '', constant('MODIFICA'));
if (strncmp($abilitazione_cookie, $abilitazione_modifica, 2) < 0){
	http_response_code(401); // Unauthorized
	echo '<pre style="color: red;"><strong>Funzione ['.$richiesta.'] non supportata</strong></pre>'."\n";
	exit(1);
}

// 
if (count($pezzi['operazioni']) < 2){
	http_response_code(403);  
	echo '<pre style="color: red;"><strong>Manca un id</strong></pre>'."\n";
	exit(1);
}
//
// check 2 - il parametro dev'essere intero senza segno 
$autore_id = $pezzi['operazioni'][1];

// modifica 1 di 2 
// espone il modulo  
if ($richiesta == 'modifica' && !isset($_POST['aggiorna_autore'])){
	modifica_autore($autore_id, []);
	exit(0);
} 
// modifica_dettaglio 2 di 2 
// aggiorna dal modulo  
if ($richiesta == 'modifica' ){
	modifica_autore($autore_id, $_POST);
	exit(0);
}

// Anche qui non dovrebbe arrivarci, però...
http_response_code(404); // know not found
echo '<pre style="color: red;"><strong>Funzione ['.$richiesta.'] non supportata</strong></pre>'."\n";
exit(1);
