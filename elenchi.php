<?php
/**
 * @source /elenchi.php 
 * @author Massimo Rainato <maxrainato@libero.it>
 * 
 * Centralino router delle richieste 
 * questa pagina gestisce url fatti così:
 * https://archivio.athesis77.it/elenchi.php/richiesta/parametro?limit=20# 
 * a differenza di altri router questo non si occupa di "una tabella",
 * ma delle tabelle 
 * - abilitazioni_elenco 
 * - autori_elenco 
 * - chiavi_elenco 
 * - chiavi_valori_vocabolario 
 * - richieste 
 * 
 * Operazioni gestite:
 * - senza parametri 
 * /elenchi.php/elenco_chiavi 
 * /elenchi.php/backup
 * - con parametri 
 * 
 */
if (!defined('ABSPATH')){
	include_once("./_config.php");
}
include_once(ABSPATH . "aa-controller/controller-base.php"); // route_from_uri
$uri = $_SERVER['REQUEST_URI'];
$pos_richieste_php = strpos($uri, '/elenchi.php/');
$uri = substr($uri, $pos_richieste_php);
$pezzi=route_from_uri($uri, '/elenchi.php/');

$richiesta=$pezzi['operazioni'][0];
// check 1 - che richiesta è stata fatta? 
switch($richiesta){
	// queste si
	case 'elenco_chiavi':
	case 'backup':
	case 'elimina':
		break;
		
	// resto no 
	default:
		http_response_code(404); // know not found
		echo '<pre style="color: red;"><strong>Funzione ['.$richiesta.'] non supportata</strong></pre>'."\n";
		exit(1);
		break; // per check 
}
	
//
// senza secondo parametro - richiesta abilitazione lettura
if ($richiesta=='elenco_chiavi'){
	include_once(ABSPATH.'aa-controller/chiavi-controller.php'); 
	$ret = get_chiavi_datalist();
	echo $ret;
	exit(0);
}

// operazioni ricercate amministratore - sostituisce controllo-abilitazione.php
// può essere "1 lettura" ma anche "'1 lettura'"
// ("'7 amministrazione'" < "1 lettura" ) === true
$abilitazione_cookie     = str_replace("'", '', $_COOKIE['abilitazione']);
$abilitazione_amministra = str_replace("'", '', AMMINISTRA);
if (strncmp($abilitazione_cookie, $abilitazione_amministra, 2) < 0){
	http_response_code(401); // Unauthorized
	echo '<pre style="color: red;"><strong>Funzione ['.$richiesta.'] non supportata</strong></pre>'."\n";
	exit(1);
}

if ($richiesta=='backup'){
	include_once(ABSPATH.'aa-controller/scrivi-backup-file.php'); 
	get_file_backup();
	exit(0);
}


if ($richiesta=='elimina'){
	include_once(ABSPATH.'aa-controller/cancellazione-record-file.php'); 
	remove_record_tutti();
	exit(0);
}


// Anche qui non dovrebbe arrivarci, però...
http_response_code(403); // know not found
echo '<pre style="color: red;"><strong>Funzione ['.$richiesta.'] non supportata</strong></pre>'."\n";
exit(1);
