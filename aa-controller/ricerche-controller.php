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
 * - get_lista_album 
 *   ricerca avanzata (chiave operatore valore)
 *
 * - get_lista_fotografie 
 *   ricerca avanzata (chiave operatore valore)
 *   
 * - get_lista_album_semplice
 *   ricerca su tutti i campi (elenco valori)
 *   
 * - get_lista_fotografie_semplice
 *   ricerca su tutti i campi (elenco valori)
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
 * Ricerca avanzata - in base a delle terne chiave-operatore-valore
 * che vene passata alla funzione get_where si crea la ricerca
 * 
 * @param  array  $_POST
 * @return string html code
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
		$ret .= "\n\n"
		.'<table class="table table-sm borderless"><tbody>'
		.$rigo_album 
		.'</tbody></table>';
	}
	return $ret;
} // get_lista_album

/**
 * TEST 
 * https://fotomuseoathesis.it/aa-controller/ricerche-controller.php?test=ricerche-album
 * 
 */
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
} // test ricerche-album

/**
 * Ricerca avanzata - in base a delle terne chiave-operatore-valore
 * che vene passata alla funzione get_where si crea la ricerca
 * 
 * @param  array  $dati_ricerca
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
	 * Fare query con subquery - si può(1), preferita per debug la scelta 
	 * delle tre parti 
	 * select fotografie_dettagli 
	 * riduzione record_id_padre 
	 * select fotografie 
	 * 
	 * (1) A patto di restare all'interno della stessa tabella,
	 * non per un problema di sql ma per la definizione dell'architettura
	 * al servizio delle tabelle singole. per le query su tabelle incrociate
	 * potrebbero funzionare a patto che i campi passati in $campi
	 * siano tutti della tabella principale della query
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


/**
 * Ricerca semplice su tabella album e album_dettagli e scansioni_disco
 * In base a un elenco di termini viene composta una ricerca
 * "su ogni dove". Ritorna in forma di stringa un JSON di
 * album.* che dev'essere poi gestito dalla pagina
 * oppure un JSON che contiene un messaggio di errore
 * "Non trovato" non significa sbagliato.
 * 
 * @param  array  $dati_ricerca
 * @return string $json_obj_list | $json_error_object
 */
function get_lista_album_semplice(array $dati_ricerca) : string {
	// check dati modulo di ricerca 
	if (!isset($dati_ricerca['esegui_ricerca'])){
		$ret=[
			'error'   => true,
			'message' => "KO - serve utilizzare il modulo di ricerca semplificata, "
			. "clicca qui: https://fotomuseoathesis.it/ricerca.php?#."
		];
		return json_encode($ret);
	}
	if (!isset($dati_ricerca['valore'])){
		$ret=[
			'error'   => true,
			'message' => "KO - serve utilizzare il modulo di ricerca semplificata, "
			. "clicca qui: https://fotomuseoathesis.it/ricerca.php?#."
		];
		return json_encode($ret);
	}

	$dbh    = New DatabaseHandler();
	$alb_h  = New Album($dbh);
	$adet_h = New AlbumDettagli($dbh);
	$scan_h = New ScansioniDisco($dbh);

	/**
	 * Elenco dei termini - viene fatta una intersezione
	 * Sono gestite solo parole singole separate da spazi
	 * altre configurazioni +parola -parola da..a 
	 * di fatto restano letteralmente come sono e producono zero risultati
	 */
	$elenco_termini = explode(' ', $dati_ricerca['valore'], 4); // accetto al più 4 termini

	/**
	 * ricerca nella tabella album_dettagli 
	 */
	$query = 'SELECT DISTINCT record_id_padre from album_dettagli ' // . AlbumDettagli::nome_tabella 
	.' WHERE record_cancellabile_dal = :record_cancellabile_dal ';
	foreach ($elenco_termini as $valore_parziale) {
		$query .= 'AND record_id_padre in ('
		. ' select record_id_padre from album_dettagli ' // . AlbumDettagli::nome_tabella 
		. " where valore like '%".$valore_parziale."%' "
		. ') ';
	}
	$query .= ' ORDER BY record_id_padre ';

	$campi=[];
	$campi['query']=$query;
	$campi['record_cancellabile_dal']= $dbh->get_datetime_forever();
	
	$ret_adet = $adet_h->leggi($campi);

	if (isset($ret_adet['error'])){
		return json_encode($ret_adet);
	}
	/**
	 * Avendo aggiunto la ricerca in scansioni disco questo controllo va 
	 * spostato in avanti se nessuno dei due ha trovato nulla
	 * 
	if (isset($ret_adet['numero']) && $ret_adet['numero']==0){
		$ret=[
			'error'   => true,
			'message' => "Nessun album trovato [8]"
		];
		return json_encode($ret);
	}
	 * 
	 */

	/**
	 * Ricerca nella tabella deposito scansioni_disco 
	 * 
	 * Ho intenzionalmente escluso la TINTA dai termini di ricerca,
	 * a) se va fatta una ricerca per tinta si fa SOLO sulla tinta 
	 *    quindi una cosa dedicata
	 * b) per lo stesso motivo non ho fatto in tabella un indice sulla tinta
	 * 
	 * SELECT DISTINCT record_id from scansioni_disco 
	 * where record_da_esaminare = :record_da_esaminare
	 * and nome_file = '/' 
	 * and ( disco  like '%valore_parziale%' // ripetere
	 * 	or livello1 like '%valore_parziale%' // ripetere
	 * 	or livello2 like '%valore_parziale%' // ripetere
	 * 	or livello3 like '%valore_parziale%' // ripetere
	 * 	or livello4 like '%valore_parziale%' // ripetere
	 * 	or livello5 like '%valore_parziale%' // ripetere
	 * 	or livello6 like '%valore_parziale%' // ripetere
	 * 	)                                    // ripetere
	 * ORDER BY record_id
	 * 
	 * Secondo step, trovare gli album_id che fanno riferimento 
	 * ai record_in_scansioni_disco e ritornare l'elenco degli
	 * album_id per fare unione insieme con la ricerca precedente
	 * 
	 * Sarebbe elegante farle in un solo colpo? Sì, ma l'architettura 
	 * predisposta non consente di farlo, si opera solo su tabelle singole.
	 * 
	 */
	//. ' AND record_id > :ultimo_id_precedente ' // uso paginazione 
	$query = 'SELECT record_id FROM scansioni_disco '
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. " AND nome_file = '/' "; 
	foreach ($elenco_termini as $valore_parziale) {
		$query .= " AND ( disco  like '%".$valore_parziale."%' "
		          . " OR  livello1 like '%".$valore_parziale."%' "
		          . " OR  livello2 like '%".$valore_parziale."%' "
		          . " OR  livello3 like '%".$valore_parziale."%' "
		          . " OR  livello4 like '%".$valore_parziale."%' "
		          . " OR  livello5 like '%".$valore_parziale."%' "
		          . " OR  livello6 like '%".$valore_parziale."%' "
		        . ') ';
	} // foreach
	$query .= ' ORDER BY record_id ';
	$campi=[];
	$campi['query']= $query;
	$campi['record_cancellabile_dal']= $dbh->get_datetime_forever();

	$ret_scan = $scan_h->leggi($campi);

	if (isset($ret_scan['error'])){
		return json_encode($ret_scan);
	}

	if (isset($ret_scan['numero']) && $ret_scan['numero']>0){
		// lista dei record_in_scansioni_disco
		$ret_id_list = $ret_scan['data']; // quelli di deposito
		$deposito_id_list=[];
		for ($i=0; $i<count($ret_id_list); $i++){
			$deposito_id_list[]=$ret_id_list[$i]['record_id'];
		}
		asort($deposito_id_list);
		$deposito_id_list = array_unique($deposito_id_list);
		$ret_scan=[];

		$query = 'SELECT record_id FROM album '
		. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
		. ' AND record_id IN ( '
		. implode(', ', $deposito_id_list)
		. ' ) ORDER BY record_id ';
		$campi=[];
		$campi['query']=$query;
		$campi['record_cancellabile_dal']= $dbh->get_datetime_forever();
		$ret_alb = $alb_h->leggi($campi);
	
		if (isset($ret_alb['error'])){
			return json_encode($ret_alb);
		}
		
		$ret_scan=$ret_alb;
	}
	
	// la somma degli insiemi
	if (isset($ret_adet['numero']) && $ret_adet['numero']==0 && isset($ret_scan['numero']) && $ret_scan['numero']==0){
		$ret=[
			'error'   => true,
			'message' => "Nessun album trovato [8]"
		];
		return json_encode($ret);
	}
	
	// alzo di un livello i dati - mi servono solo album.record_id
	// qui si mette un limitatore a 20 senza gestione paginazione 
	// senza limitatore for ($i=0; $i<count($ret_id_list);$i++){
	// per la paginazione in avanti dentro al loop
	// if ($ret_id_list[$i]['record_id/id_padre'] <= $ultimo_precedente ) {
	//   continue;
	// }
	$ret_id_list = ($ret_adet['numero']==0) ? [] : $ret_adet['data']; // quelli di album_dettagli 
	$album_id_list = [];
	for ($i=0; $i<20 && $i<count($ret_id_list); $i++){
		$album_id_list[]=$ret_id_list[$i]['record_id_padre'];
	}
	$ret_id_list = ($ret_scan['numero']==0) ? [] : $ret_scan['data']; // quelli di deposito
	for ($i=0; $i<20 && $i<count($ret_id_list); $i++){
		$album_id_list[]=$ret_id_list[$i]['record_id'];
	}
	asort($album_id_list);
	$album_id_list = array_unique($album_id_list);
	
	// solo i dati che servono
	$query = 'SELECT record_id, titolo_album, percorso_completo FROM album '
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. ' AND record_id IN ( '
	. implode(', ', $album_id_list)
	. ' ) ORDER BY record_id ';
	$campi=[];
	$campi['query']=$query;
	$campi['record_cancellabile_dal']= $dbh->get_datetime_forever();
	
	$ret_alb = $alb_h->leggi($campi);

	if (isset($ret_alb['error'])){
		return json_encode($ret_alb);
	}
	
	if (isset($ret_alb['numero']) && $ret_alb['numero']==0){
		$ret=[
			'error'   => true,
			'message' => "Nessun album trovato [8]"
		];
		return json_encode($ret);
	}
  
	return json_encode($ret_alb);

} // get_lista_album_semplice 

/**
 * test
 * 
 * https://www.fotomuseoathesis.it/aa-controller/ricerche-controller.php?test=ricerche-semplici-album
 * 
 */
if (isset($_GET['test']) && $_GET['test'] == 'ricerche-semplici-album'){
	$dati_ricerca=[];
	echo '<p style="font-family:monospace">'.str_ireplace(';', '; ', serialize($dati_ricerca)).'</p>';
	echo '<p style="font-family:monospace">'.htmlentities(get_lista_album_semplice($dati_ricerca)).'</p>';
	echo '<hr >';

	$dati_ricerca=[];
	$dati_ricerca['esegui_ricerca'] = '1';
	$dati_ricerca['valore'] = '1936 regio';
	echo '<p style="font-family:monospace">'.str_ireplace(';', '; ', serialize($dati_ricerca)).'</p>';
	echo '<p style="font-family:monospace">'.(get_lista_album_semplice($dati_ricerca)).'</p>';

} // test ricerche-semplici-album

/**
 * ricerca semplice su tabella fotografie e fotografie_dettagli e scansioni_disco
 * In base a un elenco di termini viene composta una ricerca
 * "su ogni dove". Ritorna in forma di stringa un JSON di
 * fotografie.* che dev'essere poi gestito dalla pagina
 * oppure un JSON che contiene un messaggio di errore
 * "Non trovato" non significa sbagliato.
 * 
 * @param  array  $dati_ricerca
 * @return string $json_obj_list | $json_error_object
 */
function get_lista_fotografie_semplice(array $dati_ricerca) : string {
	// check dati modulo di ricerca 
	if (!isset($dati_ricerca['esegui_ricerca'])){
		$ret=[
			'error'   => true,
			'message' => "KO - serve utilizzare il modulo di ricerca semplificata, "
			. "clicca qui: https://fotomuseoathesis.it/ricerca.php?# [1]."
		];
		return json_encode($ret);
	}
	if (!isset($dati_ricerca['valore'])){
		$ret=[
			'error'   => true,
			'message' => "KO - serve utilizzare il modulo di ricerca semplificata, "
			. "clicca qui: https://fotomuseoathesis.it/ricerca.php?# [2]."
		];
		return json_encode($ret);
	}

	$dbh    = New DatabaseHandler();
	$foto_h = New Fotografie($dbh);
	$fdet_h = New FotografieDettagli($dbh);
	$scan_h = New ScansioniDisco($dbh);

	/**
	 * Elenco dei termini
	 * Sono gestite solo parole singole separate da spazi
	 * altre configurazioni +parola -parola da..a 
	 * di fatto restano letteralmente come sono e producono zero risultati
	 */
	$elenco_termini = explode(' ', $dati_ricerca['valore'], 4); // accetto al più 4 termini

	/**
	 * Ricerca nella tabella deposito scansioni_disco 
	 * 
	 * Ho intenzionalmente escluso la TINTA dai termini di ricerca,
	 * a) se va fatta una ricerca per tinta si fa SOLO sulla tinta 
	 *    quindi una cosa dedicata
	 * b) per lo stesso motivo non ho fatto in tabella un indice sulla tinta
	 * 
	 * SELECT DISTINCT record_id from scansioni_disco 
	 * where record_da_esaminare = :record_da_esaminare
	 * and nome_file <> '/' 
	 * and ( disco  like '%valore_parziale%' // ripetere
	 * 	or livello1 like '%valore_parziale%' // ripetere
	 * 	or livello2 like '%valore_parziale%' // ripetere
	 * 	or livello3 like '%valore_parziale%' // ripetere
	 * 	or livello4 like '%valore_parziale%' // ripetere
	 * 	or livello5 like '%valore_parziale%' // ripetere
	 * 	or livello6 like '%valore_parziale%' // ripetere
	 * 	)                                    // ripetere
	 * ORDER BY record_id
	 * 
	 * Secondo step, trovare le foto_id che fanno riferimento 
	 * ai record_in_scansioni_disco e ritornare l'elenco degli
	 * fotografie_id per fare unione insieme con le altre ricerche
	 * 
	 * Sarebbe elegante farle in un solo colpo? Sì, ma l'architettura 
	 * predisposta non consente di farlo, si opera solo su tabelle singole.
	 * 
	 */
	//. ' AND record_id > :ultimo_id_precedente ' // uso paginazione 
	$query = 'SELECT record_id FROM scansioni_disco '
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. " AND nome_file <> '/' "; 
	foreach ($elenco_termini as $valore_parziale) {
		$query .= " AND ( disco  like '%".$valore_parziale."%'"
		          . " OR nome_file like '%".$valore_parziale."%'"
		          . " OR  livello1 like '%".$valore_parziale."%'"
		          . " OR  livello2 like '%".$valore_parziale."%'"
		          . " OR  livello3 like '%".$valore_parziale."%'"
		          . " OR  livello4 like '%".$valore_parziale."%'"
		          . " OR  livello5 like '%".$valore_parziale."%'"
		          . " OR  livello6 like '%".$valore_parziale."%'"
			        . ') ';
	} // foreach
	$query .= ' ORDER BY record_id ';
	$campi=[];
	$campi['query']= $query;
	$campi['record_cancellabile_dal']= $dbh->get_datetime_forever();

	$ret_scan = $scan_h->leggi($campi);

	if (isset($ret_scan['error'])){
		return json_encode($ret_scan);
	}

	if (isset($ret_scan['numero']) && $ret_scan['numero']>0){
		// lista dei record_in_scansioni_disco
		$ret_id_list = $ret_scan['data']; // quelli di deposito
		$deposito_id_list=[];
		for ($i=0; $i<count($ret_id_list); $i++){
			$deposito_id_list[]=$ret_id_list[$i]['record_id'];
		}
		asort($deposito_id_list);
		$deposito_id_list = array_unique($deposito_id_list);
		$ret_scan=[];

		$query = 'SELECT record_id FROM fotografie '
		. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
		. ' AND record_id IN ( '
		. implode(', ', $deposito_id_list)
		. ' ) ORDER BY record_id ';
		$campi=[];
		$campi['query']=$query;
		$campi['record_cancellabile_dal']= $dbh->get_datetime_forever();
		$ret_foto = $foto_h->leggi($campi);
	
		if (isset($ret_foto['error'])){
			return json_encode($ret_foto);
		}
		
		$ret_scan=$ret_foto;
	}

	/**
	 * ricerca nella tabella fotografie_dettagli 
	 */
	$query = 'SELECT DISTINCT record_id_padre '
	. ' FROM fotografie_dettagli ' 
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal ';
	foreach ($elenco_termini as $valore_parziale) {
		$query .= 'AND record_id_padre IN ('
		. ' SELECT record_id_padre FROM fotografie_dettagli ' // . AlbumDettagli::nome_tabella 
		. " WHERE valore LIKE '%".$valore_parziale."%' "
		. ') ';
	}
	$query .= ' ORDER BY record_id_padre';

	$campi=[];
	$campi['query']=$query;
	$campi['record_cancellabile_dal']= $dbh->get_datetime_forever();
	
	$ret_fdet = $fdet_h->leggi($campi);

	if (isset($ret_fdet['error'])){
		return json_encode($ret_fdet);
	}
	// la somma degli insiemi
	if (isset($ret_fdet['numero']) && $ret_fdet['numero']==0 && isset($ret_scan['numero']) && $ret_scan['numero']==0){
		$ret=[
			'error'   => true,
			'message' => "Nessuna fotografia trovata [8]"
		];
		return json_encode($ret);
	}
	// alzo di un livello i dati - mi servono solo album.record_id
	// qui si mette un limitatore a 24 senza gestione paginazione 
	// senza limitatore for ($i=0; $i<count($ret_id_list);$i++){
	// per la paginazione in avanti dentro al loop
	// if ($ret_id_list[$i]['record_id/id_padre'] <= $ultimo_precedente ) {
	//   continue;
	// }
	$ret_id_list = ($ret_fdet['numero']==0) ? [] : $ret_fdet['data']; // quelli di fotografie_dettagli 
	$foto_id_list = [];
	for ($i=0; $i<24 && $i<count($ret_id_list); $i++){
		$foto_id_list[]=$ret_id_list[$i]['record_id_padre'];
	}
	$ret_id_list = ($ret_scan['numero']==0) ? [] : $ret_scan['data']; // quelli di deposito
	for ($i=0; $i<24 && $i<count($ret_id_list); $i++){
		$foto_id_list[]=$ret_id_list[$i]['record_id'];
	}
	asort($foto_id_list);
	$foto_id_list = array_unique($foto_id_list);

	// estrazione delle foto - solo i dati che servono	
	$query = 'SELECT record_id, titolo_fotografia, percorso_completo FROM fotografie '
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. ' AND record_id IN ( '
	. implode(', ', $foto_id_list)
	. ' ) ORDER BY record_id ';
	$campi=[];
	$campi['query']=$query;
	$campi['record_cancellabile_dal']= $dbh->get_datetime_forever();
	$ret_foto=[];
	$ret_foto = $foto_h->leggi($campi);

	if (isset($ret_foto['error'])){
		return json_encode($ret_foto);
	}
	
	if (isset($ret_foto['numero']) && $ret_foto['numero']==0){
		$ret=[
			'error'   => true,
			'message' => "Nessuna fotografia trovata [8]"
		];
		return json_encode($ret);
	}
  // le estensioni .psd .tif vanno cambiate in .jpg 
	// qualche apice viene memorizzato come &amp;#039;
	for ($i=0; $i < $ret_foto['numero']; $i++) { 

		$titolo_fotografia = $ret_foto['data'][$i]['titolo_fotografia'];
		$titolo_fotografia = str_ireplace('&amp;', "&", $titolo_fotografia);
		$ret_foto['data'][$i]['titolo_fotografia'] = $titolo_fotografia;

		$percorso_completo=$ret_foto['data'][$i]['percorso_completo'];
		$percorso_completo=str_ireplace('&amp;#039;', "'", $percorso_completo);
		$percorso_completo=str_ireplace('&#039;', "'", $percorso_completo);
		$percorso_completo=str_ireplace('.tif', '.jpg', $percorso_completo);
		$percorso_completo=str_ireplace('.psd', '.jpg', $percorso_completo);
		$ret_foto['data'][$i]['percorso_completo']=$percorso_completo;
	}
	return json_encode($ret_foto);

} // get_lista_fotografie_semplice