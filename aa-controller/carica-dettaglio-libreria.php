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
}

/**
 * data_evento può essere espressa con aaaa mm gg, indicando anche mm == 00 
 * e gg == 00 per le date non certe, oltreche essere una delle terminologie 
 * comprese nel vocabolario per il dettaglio data/evento 
 * 
 * @param string $titolo 
 * @return string $data_evento | "" 
 */
function get_data_evento(string $titolo) : string {
	// check 1: aaaa mm gg ...
	if (preg_match('/\d{4} \d{2} \d{2} /', $titolo, $match)){
		$data_evento = str_replace(' ', '-', trim($match[0]));
		if (str_contains($data_evento, '-00')){
			$data_evento .= ' DP';
		}
		return $data_evento; // aaaa-mm-gg 
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
 * luogo viene memorizzato in un vocabolario collegato alla chiave luogo/comune
 * Serve ordinare per lunghezza decrescente in modo che 
 * Boara Polesine si identificata come Boara Polesine e non come Boara
 * Boara Pisani sia identificata come Boara e non come Boara Pisani 
 * (scelta del comitato di gestione, non capisco ma mi adeguo) 
 * 
 * @param string $titolo 
 * @return string $luogo | ""
 */
function get_luogo(string $titolo) : string {
	// vocabolario 
	$dbh = New DatabaseHandler(); // no connessioni dedicate 
	$vh  = New ChiaviValori($dbh);
	$campi=[];
	$campi["chiave"]='luogo/comune';
	$campi["record_cancellabile_dal"]=$dbh->get_datetime_forever(); // record valido 
	$campi["query"] = 'SELECT valore, LENGTH(valore) FROM chiavi_valori_vocabolario '
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal  '
	. ' AND chiave = :chiave '
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
	$luogo = "";
	foreach ($elenco_luoghi as $luogo_e) {
		if (str_contains($titolo, $luogo_e)){
			$luogo = $luogo_e;
			break;
		}
	}
	return $luogo;
} // get_luogo 

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