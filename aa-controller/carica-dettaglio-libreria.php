<?php
/**
 * @source /aa-controller/carica-dettaglio-libreria.php 
 * @author Massimo Rainato <maxrainato@libero.it>
 * 
 * Funzioni in comune tra più "worker" dedicati al caricamento 
 * dettagli, in album ma potenzialmente anche in fotografie e video 
 * 
 * 
 */
if (!defined('ABSPATH')){
	include_once("../_config.php");
}

include_once(ABSPATH . 'aa-model/database-handler-oop.php');
include_once(ABSPATH . 'aa-model/chiavi-valori-oop.php');
include_once(ABSPATH . 'aa-model/autori-oop.php');
include_once(ABSPATH . 'aa-model/album-oop.php');

/**
 * Legge titolo album da tabella album
 * @param  int    $album_id 
 * @return string $titolo | "" 
 */
function get_titolo_album( int $album_id) : string {
	$dbh  = New DatabaseHandler();
	$alh  = New Album($dbh); 

	$campi=[];
	$campi["query"] = 'SELECT titolo_album FROM album ' 
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. ' AND record_id = :record_id ';
	$campi["record_cancellabile_dal"] = $dbh->get_datetime_forever();
	$campi["record_id"] = $album_id;
	$ret = $alh->leggi($campi);
	
	if ( isset($ret['error']) || $ret['numero'] == 0){
		return "";
	}
	$titolo = $ret["data"][0]["titolo_album"];
	return $titolo;
} // get_titolo_album()

/**
 * Data_evento può essere espressa con aaaa mm gg, indicando anche mm == 00 
 * e gg == 00 per le date non certe, oltreche essere una delle terminologie 
 * comprese nel vocabolario per il dettaglio data/evento 
 * @param  string $titolo 
 * @return string $data_evento | "" 
 */
function get_data_evento(string $titolo) : string {
	// check 1: aaaa mm gg ...
	if (preg_match('/\d{4} \d{2} \d{2} /', $titolo, $match)){
		$data_evento = str_replace(' ', '-', trim($match[0]));
		if (str_contains($data_evento, '-00')){
			$data_evento .= ' DP';
		}
		return $data_evento; // aaaa-mm-gg oppure aaaa-mm-gg DP
	}

	// vocabolario 
	$dbh = New DatabaseHandler(); // no connessioni dedicate 
	$vh  = New ChiaviValori($dbh);
	$campi=[];
	$campi["chiave"]='data/evento';
	$campi["record_cancellabile_dal"]=$dbh->get_datetime_forever(); // record valido 
	$campi["query"] = 'SELECT valore FROM chiavi_valori_vocabolario '
	. 'WHERE chiave = :chiave '
	. 'AND record_cancellabile_dal = :record_cancellabile_dal '
	. 'ORDER BY valore ';
	$ret_valori = $vh->leggi($campi);
	if ( isset($ret_valori['error']) || $ret_valori['numero'] == 0){
		return "";
	}
	$elenco_date=[];
	for ($i=0; $i < count($ret_valori["data"]); $i++) { 
		$elenco_date[]=$ret_valori["data"][$i]["valore"];
	}
	// TODO $elenco_date si può salvare in un file
	// check 2: str_contains() 
	$data_evento="";
	foreach($elenco_date as $data){
		if (str_contains($titolo, $data)){
			$data_evento = $data;
			break;
		}
	}
	return $data_evento;  
} // get_data_evento

/**
 * !TODO dove veniva usata come ricerca luogo/comune va tolta e deve 
 * !TODO diventare la ricerca di chiave E valore unendo la ricerca di 
 * !TODO chiave like luogo/% 
 */
function get_luogo(string $titolo) : string {
	return get_luogo_comune($titolo);
} // get_luogo

/**
 * lettura dettaglio luogo/comune 
 * @param  string $titolo 
 * @return string $luogo | ""
 */
function get_luogo_comune(string $titolo) : string {
	// vocabolario 
	$dbh = New DatabaseHandler(); // no connessioni dedicate 
	$vh  = New ChiaviValori($dbh);
	$campi=[];
	$campi["chiave"]='luogo/comune';
	$campi["record_cancellabile_dal"]=$dbh->get_datetime_forever(); // record valido 
	$campi["query"] = 'SELECT valore, LENGTH(valore), chiave '
	. ' FROM chiavi_valori_vocabolario '
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. " AND chiave = :chiave "
	. ' ORDER BY 2 DESC, 1 ASC ';
	$ret_valori = $vh->leggi($campi);
	if ( isset($ret_valori['error']) || $ret_valori['numero'] == 0){
		return "";
	}
	$elenco_luoghi =[];
	for ($i=0; $i < count($ret_valori["data"]); $i++) { 
		$elenco_luoghi[]=$ret_valori["data"]["$i"]["valore"];
	}
	// TODO $elenco_luoghi si può salvare in un file 
	$titolo_low=strtolower($titolo);
	$luogo = "";
	foreach ($elenco_luoghi as $luogo_e) {
		if (str_contains($titolo_low, strtolower($luogo_e))){
			$luogo = $luogo_e;
			break;
		}
	}
	return $luogo;
} // get_luogo_comune 


/**
 * lettura dettaglio luogo/area-geografica
 * @param  string $titolo 
 * @return string $luogo | ""
 */
function get_luogo_localita(string $titolo) : string {
	// vocabolario 
	$dbh = New DatabaseHandler(); // no connessioni dedicate 
	$vh  = New ChiaviValori($dbh);
	$campi=[];
	$campi["chiave"]='luogo/area-geografica';
	$campi["record_cancellabile_dal"]=$dbh->get_datetime_forever(); // record valido 
	$campi["query"] = 'SELECT valore, LENGTH(valore) '
	. ' FROM chiavi_valori_vocabolario '
	. ' WHERE (record_cancellabile_dal = :record_cancellabile_dal ) '
	. ' AND chiave = :chiave '
	. ' ORDER BY 2 DESC, 1 ASC ';
	$ret_valori = $vh->leggi($campi);
	if ( isset($ret_valori['error']) || $ret_valori['numero'] == 0){
		return "";
	}
	//dbg echo var_dump($ret_valori); 
	$elenco_luoghi =[];
	for ($i=0; $i < count($ret_valori["data"]); $i++) { 
		$elenco_luoghi[]=$ret_valori["data"]["$i"]["valore"];
	}
	// TODO $elenco_luoghi si può salvare in un file 
	$titolo_low=strtolower($titolo);
	$luogo = "";
	foreach ($elenco_luoghi as $luogo_e) {
		if (str_contains($titolo_low, strtolower($luogo_e))){
			$luogo = $luogo_e;
			break;
		}
	}
	return $luogo;
} // get_luogo_localita


/**
 * estrazione vocabolario 
 */
function get_ente_societa(string $titolo) : string {
	// vocabolario 
	$dbh = New DatabaseHandler(); // no connessioni dedicate 
	$vh  = New ChiaviValori($dbh);
	$campi=[];
	$campi["chiave"]='nome/ente-societa';
	$campi["record_cancellabile_dal"]=$dbh->get_datetime_forever(); // record valido 
	$campi["query"] = 'SELECT valore, LENGTH(valore) '
	. ' FROM chiavi_valori_vocabolario '
	. ' WHERE (record_cancellabile_dal = :record_cancellabile_dal ) '
	. ' AND chiave = :chiave '
	. ' ORDER BY 2 DESC, 1 ASC ';
	$ret_valori = $vh->leggi($campi);
	if ( isset($ret_valori['error']) || $ret_valori['numero'] == 0){
		return "";
	}
	//dbg echo var_dump($ret_valori); 
	$elenco_enti =[];
	for ($i=0; $i < count($ret_valori["data"]); $i++) { 
		$elenco_enti[]=($ret_valori["data"]["$i"]["valore"]);
	}
	// TODO $elenco_enti si può salvare in un file 
	$titolo_low=strtolower($titolo);
	$ente = "";
	foreach ($elenco_enti as $ente_e) {
		if (str_contains($titolo_low, strtolower($ente_e))){
			$ente = $ente_e;
			break;
		}
	}
	return $ente;
} // get_ente_societa

/**
 * La sigla_6 è un codice assegnato univoco alle autrici e autori 
 * che sono archiviati. Si è deciso di assegnare un codice 
 * simile al codice fiscale, e viene riportato nel nome di 
 * ogni cartella.
 */
function get_autore_sigla_6(string $titolo) : string {
	// Autori
	$dbh = New DatabaseHandler(); // no connessioni dedicate 
	$auh = New Autori($dbh); // auh non auth perché sarebbe frainteso
	$campi=[];
	$campi["query"] = 'SELECT * FROM autori_elenco '
	. "WHERE sigla_6 > '' "
	. 'ORDER BY sigla_6 ';
	$ret_autori = $auh->leggi($campi);
	if ( isset($ret_autori['error']) || $ret_autori['numero'] == 0 ){
		return "";
	}
	$elenco_sigle = [];
	for ($i=0; $i < count($ret_autori["data"]); $i++) { 
		$elenco_sigle[]=$ret_autori["data"][$i]["sigla_6"];
	}
	// TODO $elenco_sigle si può salvare in un file 
	$sigla = "";
	foreach ($elenco_sigle as $sigla_6e) {
		if (str_contains($titolo, $sigla_6e)){
			$sigla = $sigla_6e;
			break;
		}
	}
	return $sigla;  
}

/**
 * elenco autori Cognome, nome 
 * elenco autori Cognome, nome (nascita - morte) per gli scomparsi 
 * elenco autori Cognome, nome (nascita - ) per gli omonimi 
 * nome file ... Cognome nome ... 
 * nome file ... nome cognome ... 
 * 
 * La funzione deve separare tutti i termini del nomefile
 * e andare a fare una ricerca per ciascun termine in elenco autori
 * 
 * @param  string titolo o nome file
 * @return string "" oppure un cognome, nome da elenco autori
 */
function get_autore(string $titolo) : string {
	return "";
}

function get_durata(string $titolo) : string{
		// check 00h00m00s
		$durata = '';
		if (preg_match('/\d{2}h\d{2}m\d{2}s/', $titolo, $match)){
			$durata = trim($match[0]);
		} elseif (preg_match('/\dh\d{2}m\d{2}s/', $titolo, $match)){
			$durata = '0'.trim($match[0]);
		}
		return $durata;
}

/**
 * All'interno del nomefile se è presente un termine
 * fondo_
 * viene caricato come dettaglio, altrimenti la funzione torna una stringa vuota
 * Si può definire un vocabolario dei fondi
 */
function get_fondo(string $titolo) : string {
	$fondo = "";
	if (preg_match('/ fondo_(.*)/', $titolo, $match)){
		$fondo = trim($match[0]);
		$fondo = str_replace('.tiff',  '', $fondo);
		$fondo = str_replace('.tif',   '', $fondo);
		$fondo = str_replace('.jpeg',  '', $fondo);
		$fondo = str_replace('.jpg',   '', $fondo);
		$fondo = str_replace('fondo_', '', $fondo);
	}
	return $fondo; 

} // get_fondo