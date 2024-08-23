<?php 
/**
 * @source /aa-controller/ricerche-controller.php
 * @author Massimo Rainato <maxrainato@libero.it>
 * 
 * RICERCHE controller
 * 
 * Si occupa di eseguire le ricerche analitiche per dettagli richieste 
 * sulle tabelle album_dettagli, fotografie_dettagli, video_dettagli 
 * ogni ricerca restituisce un html "nessun X trovato" oppure 
 * una lista di elementi trovati, le impagina in html e le rende al mittente 
 * 
 * - get_where(c, o, v)
 *   restituisce una singola clausola avendo in input
 *   . una chiave 
 *   . un  operatore
 *   . un  valore parziale / comparativo 
 * 
 * - get_elenco_album 
 * 
 * - get_elenco_fotografie 
 * 
 */
if (!defined('ABSPATH')){
	include_once('../_config.php');
}
include_once(ABSPATH . 'aa-model/database-handler-oop.php');
// include_once(ABSPATH . 'aa-model/ricerche-oop.php');
include_once(ABSPATH . 'aa-model/album-oop.php');
include_once(ABSPATH . 'aa-model/album-dettagli-oop.php');
include_once(ABSPATH . 'aa-model/fotografie-oop.php');
include_once(ABSPATH . 'aa-model/fotografie-dettagli-oop.php');
include_once(ABSPATH . 'aa-model/video-oop.php');
include_once(ABSPATH . 'aa-model/video-dettagli-oop.php');
include_once(ABSPATH . 'aa-model/scansioni-disco-oop.php');


/**
 * @param  string  chiave    limited set
 * @param  string  operatore limited set
 * @param  string  valore_parziale 
 * @return string  chiave= $chiave AND valore $operatore $valore_parziale
 */
function get_where(string $chiave, string $operatore, string $valore_parziale) : string {
	// non controlla che chiave sia in tabella chiavi 
	$ret = " chiave = '$chiave' AND valore ";
	switch($operatore){
		case 'comincia con':
		case 'contiene':
			$ret .= 'LIKE ';
			break; 
			
		case 'maggiore uguale':
			$ret .= '>= ';
			break;
			
		case 'maggiore':
			$ret .= '> ';
			break;
			
		case 'uguale':
			$ret .= '= ';
			break;
			
		case 'minore uguale':
			$ret .= '<= ';
			break;
			
		case 'minore':
			$ret .= '< ';
			break;
		
		default: 
		  $ret .= '<> ';
			break;
	}
	
	switch($operatore){
		case 'comincia con':
			$ret .= "'$valore_parziale%' ";
			break;
			
		case 'contiene':
			$ret .= "'%$valore_parziale%' ";
			break; 
			
		default: 
		  $ret .= "'$valore_parziale' "; 
			break;
	}

	return $ret;
} // get_where()

/**
 * @param  void   $_POST
 * @return string html code
 * !TODO inserire un array in input che sostituisce _POST
 */
function get_lista_album(array $dati_ricerca) : string {
	$dbh    = New DatabaseHandler();
	$alb_h  = New Album($dbh);
	$adet_h = New AlbumDettagli($dbh);
	$scan_h = New ScansioniDisco($dbh);
	// 1. check parametri _POST 
	// 2. composizione query 
	// 3. ricerca album_dettagli
	// 4. ricerca album 
	// 5. completamento dati tinta_rgb in scansioni_disco
	// 6. costruzione risposta html  

	// l'input è comandato dai $_POST
	if (!isset($dati_ricerca['esegui_ricerca'])){
		$ret = '<p>Nessun album rilevato - mancano dati [1]</p>';
		return $ret;
	}
	if (!isset($dati_ricerca['scelta_tutte_o_almeno_una'])){
		$ret = '<p>Nessun album rilevato - mancano dati [2]</p>';
		return $ret;
	}
	$tutte_o = ($dati_ricerca['scelta_tutte_o_almeno_una']=='almeno_una') ? 'OR' : 'AND';
	
	if (!isset($dati_ricerca['chiave'])){
		$ret = '<p>Nessun album rilevato - mancano dati [3]</p>';
		return $ret;
	}
	if (!isset($dati_ricerca['operatore'])){
		$ret = '<p>Nessun album rilevato - mancano dati [4]</p>';
		return $ret;
	}
	if (!isset($dati_ricerca['valore'])){
		$ret = '<p>Nessun album rilevato - mancano dati [5]</p>';
		return $ret;
	}
	$chiavi    = $dati_ricerca['chiave'];
	$operatori = $dati_ricerca['operatore'];
	$valori_parziali = $dati_ricerca['valore'];
	if ( count($chiavi) !== count($operatori) || 
	     count($chiavi) !== count($valori_parziali)){
		$ret = '<p>Nessun album rilevato - mancano dati [6]</p>';
		return $ret;
	}
	/*
	 * Si può fare una query con subquery lasciando la complicazione 
	 * al gestore database 
	 * SELECT * FROM album a
	 * WHERE record_cancellabile_dal = :record_cancellabile_dal 
	 *   AND record_id IN 
	 *       (SELECT record_id_padre FROM album_dettagli 
	 *        WHERE record_cancellabile_dal = :record_cancellabile_dal 
	 *          AND §clausole§ )
	 * Per debug preferita la scelta in 3 parti 
	 * select album_dettagli 
	 * riduzione record_id_padre 
	 * select album 
	 */
	$query = ' SELECT * FROM album_dettagli '
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. ' AND ( §clausole§ )';
	$clausole = get_where($chiavi[0], $operatori[0], $valori_parziali[0]);
	// echo '<br>clausole: ' . $clausole;
	if (count($chiavi) > 1){
		$clausole = '( '.$clausole.' ) ';
		for ($i=1; $i < count($chiavi); $i++) { 
			$clausole .= $tutte_o . ' ( '. get_where($chiavi[$i], $operatori[$i], $valori_parziali[$i]) .' ) ';
		}
		// echo '<br>clausole: ' . $clausole;
	}
	$campi=[];
	$campi['query'] =str_ireplace('§clausole§', $clausole, $query);
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
	$ret_adet = $adet_h->leggi($campi);
	// echo '<br><br>ret_adet: ' . serialize($ret_adet);
	if (isset($ret_adet['error'])){
		$ret = '<p>Nessun album rilevato - errore in lettura [7]</p>';
		// . '<!-- vedi: '. serialize($ret_adet). '-->';
		return $ret;
	}
	if ($ret_adet['numero'] == 0){
		$ret = '<p>Nessun album rilevato [8]</p>';
		// . '<!-- vedi: '. serialize($ret_adet). '-->';
		return $ret;
	}
	$dettagli_letti = $ret_adet['data'];
	// dai dettagli agli album
	$campi=[];
	if (count($dettagli_letti)==1){
		$campi['query']= 'SELECT * FROM album '
		. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
		. ' AND record_id = :record_id ';
		$campi['record_id'] = $dettagli_letti[0]['record_id_padre'];
	} else {
		$lista_album_id=[];
		foreach ($dettagli_letti as $dettaglio) {
			$k = $dettaglio['record_id_padre'];
			$lista_album_id[$k]= $k;
		}
		$elenco_id = implode(', ', $lista_album_id);
		$campi['query']= 'SELECT * FROM album '
		. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
		. ' AND record_id IN ('.$elenco_id.' ) ';
	}
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
	$ret_alb = $alb_h->leggi($campi);
	// echo '<br><br>ret_alb: '. serialize($ret_alb);
	if (isset($ret_alb['error'])){
		$ret = '<p>Nessun album rilevato - errore in lettura [7]</p>';
		// . '<!-- vedi: '. serialize($ret_alb). '-->';
		return $ret;
	}
	if ($ret_alb['numero'] == 0){
		$ret = '<p>Nessun album rilevato [8]</p>';
		// . '<!-- vedi: '. serialize($ret_alb). '-->';
		return $ret;
	}
	// e qui si prepara 
	$album_lista = $ret_alb['data'];
	$album_rigo  = file_get_contents(ABSPATH.'aa-view/ricerca-per-chiavi-album-view.php');
	$ret = '';
	foreach($album_lista as $album){		
		$album_id = $album['record_id'];
		// echo '<br><br>album_id: ' . $album_id;
		// echo '<br>album: ' . serialize($album);
		// tinta_rgb 
		$tinta_rgb = '000000';
		$campi=[];
		$campi['query']= 'SELECT * from scansioni_disco '
		. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
		. ' AND record_id = :record_id ';
		$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
		$campi['record_id'] = $album['record_id_in_scansioni_disco'];
		$ret_scan = $scan_h->leggi($campi);
		// echo '<br><br>scansioni_disco: ' . serialize($ret_scan);
		if (isset($rec_scan['numero']) && $rec_scan['numero']> 0){
			$tinta_rgb = $ret_scan['data'][0]['tinta_rgb'];
			// echo '<br>'.$tinta_rgb;
		}
		// titolo_album
		$titolo_album=$album['titolo_album'];
		// siete in
		$siete_in = str_replace('/', ' / ', $album['percorso_completo']);
		$rigo_album=$album_rigo;
		$rigo_album=str_replace('<?=URLBASE; ?>',       URLBASE,       $rigo_album);  
		$rigo_album=str_replace('<?=$album_id; ?>',     $album_id,     $rigo_album);  
		$rigo_album=str_replace('<?=$tinta_rgb; ?>',    $tinta_rgb,    $rigo_album);  
		$rigo_album=str_replace('<?=$titolo_album; ?>', $titolo_album, $rigo_album);  
		$rigo_album=str_replace('<?=$siete_in; ?>',     $siete_in,     $rigo_album);  
    // echo '<br>rigo:';
    // echo '<pre>'.htmlentities($rigo_album).'<pre>';
		$ret .= "\n\n".$rigo_album;
	}
	return $ret;
} // get_lista_album

if (isset($_GET['test']) && $_GET['test'] == 'ricerche-album'){
	$dati_ricerca=[];
	$dati_ricerca['esegui_ricerca'] = '1';
	$dati_ricerca['scelta_tutte_o_almeno_una'] = 'tutte';
	$dati_ricerca['chiave']=[];
	$dati_ricerca['operatore']=[];
	$dati_ricerca['valore']=[];
	$dati_ricerca['chiave'][]= 'data/evento';
	$dati_ricerca['operatore'][]='maggiore';
	$dati_ricerca['valore'][]='1900';
	echo '<p style="font-family:monospace;">'.'debug on'.'</p>';
	echo 'POST: ';
	echo var_dump($dati_ricerca);
	echo '<br><br>get_lista_album()<br>';
	echo '<br><pre>'.htmlentities(get_lista_album($dati_ricerca)).'</pre>';

	echo '<hr>'; 

	$dati_ricerca=[];
	$dati_ricerca['esegui_ricerca'] = '1';
	$dati_ricerca['scelta_tutte_o_almeno_una'] = 'tutte';
	$dati_ricerca['chiave']=[];
	$dati_ricerca['operatore']=[];
	$dati_ricerca['valore']=[];
	$dati_ricerca['chiave'][]= 'nome/manifestazione-soggetto';
	$dati_ricerca['operatore'][]='contiene';
	$dati_ricerca['valore'][]='giuseppe';
	echo '<p style="font-family:monospace;">'.'debug on'.'</p>';
	echo 'POST: ';
	echo var_dump($dati_ricerca);
	echo '<br><br>get_lista_album()<br>';
	echo '<br><pre>'.htmlentities(get_lista_album($dati_ricerca)).'</pre>';

	echo '<hr>'; 

	$dati_ricerca=[];
	$dati_ricerca['esegui_ricerca'] = '1';
	$dati_ricerca['scelta_tutte_o_almeno_una'] = 'tutte';
	$dati_ricerca['chiave']=[];
	$dati_ricerca['operatore']=[];
	$dati_ricerca['valore']=[];
	$dati_ricerca['chiave'][]= 'nome/manifestazione-soggetto';
	$dati_ricerca['operatore'][]='contiene';
	$dati_ricerca['valore'][]='o';
	$dati_ricerca['chiave'][]= 'nome/manifestazione-soggetto';
	$dati_ricerca['operatore'][]='contiene';
	$dati_ricerca['valore'][]='a';
	echo '<p style="font-family:monospace;">'.'debug on'.'</p>';
	echo 'POST: ';
	echo var_dump($dati_ricerca);
	echo '<br><br>get_lista_album()<br>';
	echo '<br><pre>'.htmlentities(get_lista_album($dati_ricerca)).'</pre>';

	exit(0); 
}

/**
 * @param  void   $dati_ricerca
 * @return string html code
 */
function get_lista_fotografie(array $dati_ricerca) : string {
	$dbh    = New DatabaseHandler();
	$foto_h = New Fotografie($dbh);
	$fdet_h = New FotografieDettagli($dbh);
	// 1. check parametri dati_ricerca

	/*
	 * 1. Check parametri post 
	 */
	if (!isset($dati_ricerca['esegui_ricerca'])){
		$ret = '<p>Nessuna fotografia rilevata - mancano dati [1]</p>';
		return $ret;
	}
	if (!isset($dati_ricerca['scelta_tutte_o_almeno_una'])){
		$ret = '<p>Nessuna fotografia rilevata - mancano dati [2]</p>';
		return $ret;
	}
	$tutte_o = ($dati_ricerca['scelta_tutte_o_almeno_una']=='almeno_una') ? 'OR' : 'AND';

	if (!isset($dati_ricerca['chiave'])){
		$ret = '<p>Nessuna fotografia rilevata - mancano dati [3]</p>';
		return $ret;
	}
	if (!isset($dati_ricerca['operatore'])){
		$ret = '<p>Nessuna fotografia rilevata - mancano dati [4]</p>';
		return $ret;
	}
	if (!isset($dati_ricerca['valore'])){
		$ret = '<p>Nessuna fotografia rilevata - mancano dati [5]</p>';
		return $ret;
	}
	$chiavi    = $dati_ricerca['chiave'];
	$operatori = $dati_ricerca['operatore'];
	$valori_parziali = $dati_ricerca['valore'];
	if ( count($chiavi) !== count($operatori) || 
	     count($chiavi) !== count($valori_parziali)){
		$ret = '<p>Nessuna fotografia rilevata - mancano dati [6]</p>';
		return $ret;
	}

	/**
	 * Fare query con subquery - si può, preferita per debug la scelta 
	 * delle tre parti 
	 * select fotografie_dettagli 
	 * riduzione record_id_padre 
	 * select fotografie 
	 */

	$query = ' SELECT * FROM fotografie_dettagli '
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. ' AND ( §clausole§ )';
	$clausole = get_where($chiavi[0], $operatori[0], $valori_parziali[0]);
	if (count($chiavi) > 1){
		$clausole = '( '.$clausole.' ) ';
		for ($i=1; $i < count($chiavi); $i++) { 
			$clausole .= $tutte_o . ' ( '. get_where($chiavi[$i], $operatori[$i], $valori_parziali[$i]) . ' ) ';
		}
	}
	
	$campi=[];
	$campi['query'] =str_ireplace('§clausole§', $clausole, $query);
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
	// echo var_dump($campi);
	// echo '<br>';
	// exit(0); 
	$ret_fdet = $fdet_h->leggi($campi);
	if (isset($ret_fdet['error'])){
		$ret = '<p>Nessuna fotografia rilevata - errore in lettura [7]</p>';
		// . '<!-- vedi: '. serialize($ret_fdet). '-->';
		return $ret;
	}
	if ($ret_fdet['numero'] == 0){
		$ret = '<p>Nessuna fotografia rilevata [8]</p>';
		// . '<!-- vedi: '. serialize($ret_fdet). '-->';
		return $ret;
	}
	$dettagli_letti = $ret_fdet['data'];
	$campi=[];
	// compattamento record_id_padre 
	if (count($dettagli_letti) == 1){
		// FACCIAMOLA SEMPLICE
		$campi['query'] = 'SELECT * FROM fotografie '
		. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
		. ' AND record_id = :record_id ';
		$campi['record_id'] = $dettagli_letti[0]['record_id_padre'];
	} else {
		$lista_foto_id=[];
		foreach ($dettagli_letti as $dettaglio) {
			$k = $dettaglio['record_id_padre'];
			$lista_foto_id[$k] = $k;
		}
		$elenco_id = implode(', ', $lista_foto_id);
		$campi['query']= 'SELECT * FROM fotografie '
		. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
		. ' AND record_id IN ('.$elenco_id.' ) ';
	}	
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
	$ret_foto = $foto_h->leggi($campi);
	if (isset($ret_foto['error'])){
		$ret = '<p>Nessuna fotografia rilevata - errore in lettura [9]</p>';
		// . '<!-- vedi: '. serialize($ret_foto). '-->';
		return $ret;
	}
	if ($ret_foto['numero'] == 0){
		$ret = '<p>Nessuna fotografia rilevata [10]</p>';
		// . '<!-- vedi: '. serialize($ret_foto). '-->';
		return $ret;
	}
	// e qui si prepara la lista 
	$fotografie_lista = $ret_foto['data']; // è sempre un array 
	$fotografia_rigo  = file_get_contents(ABSPATH.'aa-view/ricerca-per-chiavi-fotografie-view.php');
	$ret = '';
	foreach ($fotografie_lista as $fotografia) {
		$fotografia_id     = $fotografia['record_id'];
		$titolo_fotografia = $fotografia['titolo_fotografia'];
		$percorso_completo = $fotografia['percorso_completo'];
		$ultima_barra_al = strrpos($percorso_completo, '/');
		if ($ultima_barra_al===false || $ultima_barra_al==0){
			$siete_in='Principale';
		} else {
			$siete_in = str_ireplace('/', ' / ', substr($percorso_completo, 1, $ultima_barra_al));
		}
	
		$rigo_fotografia   = str_replace('<?=URLBASE; ?>',            URLBASE,            $fotografia_rigo);
		$rigo_fotografia   = str_replace('<?=$fotografia_id; ?>',     $fotografia_id,     $rigo_fotografia);
		$rigo_fotografia   = str_replace('<?=$titolo_fotografia; ?>', $titolo_fotografia, $rigo_fotografia);
		$rigo_fotografia   = str_replace('<?=$percorso_completo; ?>', $percorso_completo, $rigo_fotografia);
		$rigo_fotografia   = str_replace('<?=$siete_in; ?>',          $siete_in,          $rigo_fotografia);
		$ret .= $rigo_fotografia;
	}
	return $ret;
} // get_lista_fotografie()