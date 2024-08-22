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
$pezzi=route_from_uri($uri, '/elenchi.php/');
$richiesta=$pezzi['operazioni'][0];

// check 1 - che richiesta è stata fatta? 
switch($richiesta){
	// queste si
	case 'elenco_chiavi':
	case 'backup':
		break;
		
		// resto no 
		default:
		http_response_code(404); // know not found
		echo '<pre style="color: red;"><strong>Funzione ['.$richiesta.'] non supportata</strong></pre>'."\n";
		exit(1);
		break; // per check 
}
	
//
// senza secondo parametro 
if ($richiesta=='elenco_chiavi'){
	include_once(ABSPATH.'aa-controller/chiavi-controller.php'); 
	$ret = get_chiavi_datalist();
	echo $ret;
	exit(0);
}

if ($richiesta=='backup'){
	// include_once(ABSPATH.'aa-controller/controllo-abilitazione.php'); // check & set cookie
	include_once(ABSPATH.'aa-controller/scrivi-backup-file.php'); 
	get_file_backup();
	exit(0);
}


// secondo elemento obbligatorio
if (count($pezzi['operazioni']) < 2){
	http_response_code(404); // TODO sostituire con il codice errore parametro invalido 
	echo '<pre style="color: red;"><strong>Manca un id</strong></pre>'."\n";
	exit(1);
}

include_once(ABSPATH . "aa-controller/cartelle-controller.php"); // cartelle
exit(0); 