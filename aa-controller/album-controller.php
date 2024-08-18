<?php
/**
 * ALBUM controller
 * 
 * Si occupa delle funzioni che riguardano la tabella album 
 * e album_dettagli e fotografie e video e richieste 
 * 
 * . get_item_foto_griglia 
 * . get_item_video_griglia 
 *   forniscono gli elementi da inserire in miniatura dentro
 *   il pannello dell'album
 * Il file monoscopio è stato fornito da Wikipedia e concesso in pubblico dominio 
 * perché privo di "elementi creativi" 
 * https://it.wikipedia.org/wiki/Monoscopio#/media/File:SMPTE_Color_Bars.svg
 * 
 * 
 * . get_item_dettagli 
 *   elenco dettagli chiave-valore da inserire dentro
 *   il pannello dell'album 
 * 
 * . leggi_album_per_id 
 *   carica, completa ed espone la pagina di vista dell'album 
 * 
 * . carica_album_da_scansioni_disco
 *   carica in tabella album solo il record album partendo 
 *   dal record in scansioni_disco, poi ci son altre funzioni per 
 *   caricare fotografie e per caricare video 
 * 
 * . carica_richiesta_album_per_id 
 * TODO cambiare in . carica_richiesta_album 
 *   per l'album aggiunge una richiesta nella tabella richieste 
 * 
 * ALBUMDETTAGLI   
 * Estrazione dati da scansioni_disco
 * . aggiungi_dettagli_album_da_album 
 *   Suona strano? Sì, però 
 *   album_dettagli è una tabella e album è un'altra tabella, 
 *   in italiano si scrive carica dettaglio dell'album dall'album 
 * 
 * . elimina_dettaglio_album
 *   esegue la cancellazione non fisica del dettaglio 
 * 
 * . aggiungi_dettaglio_album_da_modulo
 *   espone il modulo per aggiungere un dettaglio 
 *   inserisce il dettaglio che riceve dal modulo 
 * 
 * . modifica_dettaglio_album_da_modulo
 *   espone il modulo che serve per modificare un dettaglio 
 *   elimina il dettaglio vecchio e inserisce il dettaglio 
 *   modificato come nuovo.
 * 
 */
if (!defined('ABSPATH')){
	include_once('../_config.php');
}
include_once(ABSPATH . 'aa-model/database-handler-oop.php');
include_once(ABSPATH . 'aa-model/album-oop.php');
include_once(ABSPATH . 'aa-model/album-dettagli-oop.php');
include_once(ABSPATH . 'aa-model/chiavi-valori-oop.php'); // vocabolario 
include_once(ABSPATH . 'aa-model/fotografie-oop.php');
include_once(ABSPATH . 'aa-model/video-oop.php');
include_once(ABSPATH . 'aa-model/scansioni-disco-oop.php');
include_once(ABSPATH . 'aa-model/richieste-oop.php');
include_once(ABSPATH . 'aa-model/chiavi-oop.php');

include_once(ABSPATH . 'aa-controller/controller-base.php');
include_once(ABSPATH . 'aa-controller/carica-dettaglio-libreria.php');
include_once(ABSPATH . 'aa-controller/fotografie-controller.php');
include_once(ABSPATH . 'aa-controller/video-controller.php');

/**
 * Elemento fotografia da esporre in griglia 
 * 
 * Per evitare che le immagini siano esportate con facilità 
 * viene sostituito l'indirizzo url con il contenuto della fotografia 
 * Nella pagina un javascript consente di fare richiesta della foto 
 * evitando allo stesso tempo che con il tasto destro del mouse 
 * si passi alla "salva con nome". 
 * Nelle direttive del CdGA le immagini devono avere lato lungo 800px
 * 
 * @param  array  $fotografia 
 * @return string $html - porzione di codice 
 */
 function get_item_foto_griglia(array $fotografia) : string {
	$ret  = '<div class="float-start">'."\n";
	$ret .= '<a href="'.URLBASE.'fotografie.php/leggi/'.$fotografia['record_id'].'" ';
	$ret .=    'title="'.$fotografia['titolo_fotografia'].'" >'."\n";
	$fotografia_src  = str_ireplace('//' , '/' , ABSPATH.$fotografia['percorso_completo']);
	$fotografia_src  = 'data:image/jpeg;base64,'.base64_encode(file_get_contents($fotografia_src));
	$ret .= '<img src="'.$fotografia_src.'" ';
	// $ret .= '<img src="'.URLBASE.$fotografia['percorso_completo'].'" ';
	$ret .=       'style="min-width:200px; min-height:200px; max-width:200px; max-height:200px;" ';
	$ret .=       'loading="lazy"  class="d-block w-100" />'."\n";
	$ret .= '</a>'."\n";
	$ret .= '</div>'."\n";
	return $ret; 
} // get_item_foto_griglia()

/**
 * Elemento video da esporre in griglia
 * Si può sostituire il monoscopio con 
 * video.mp4 -> video_copertina.jpg lato lungo 800px  
 * Usato nella leggi_album_per_id per preparare l'elenco delle immagini cliccabili 
 * 
 * @param  array  $video
 * @return string $html - porzione di codice 
 * 
 * Il file monoscopio è stato fornito da Wikipedia e concesso in pubblico dominio 
 * perché privo di "elementi creativi" 
 * https://it.wikipedia.org/wiki/Monoscopio#/media/File:SMPTE_Color_Bars.svg
 * 
 */
 function get_item_video_griglia(array $video) : string {
	
	$ret  = '<div class="float-start" style="width:200px;height:200px;max-width:200px;max-height:200px;" >'."\n";
	$ret .= '<a href="'.URLBASE.'video.php/leggi/'.$video['record_id'].'" title="'.$video['titolo_video'].'" >'."\n";
	// preload none - serve a stoppare troppo tempo per caricare GB 
	// $ret .= '<video controls preload="none" >'."\n";
	// $ret .= '<source src="'.URLBASE.$video['percorso_completo'].'" type="video/mp4">'."\n";
	// $ret .= '</video>'."\n";
	// niente - non si ridimensiona
	// $ret .= file_get_contents(ABSPATH.'aa-img/SMPTE_Color_Bars.svg'); // 800 byte 
	// 
	$ret .= '<img src="'.URLBASE.'aa-img/video-segnalino.png" ';
	$ret .=       'style="min-width:200px; min-height:200px; max-width:200px; max-height:200px;" ';
	$ret .=       'loading="lazy"  class="d-block w-100" />'."\n";
	$ret .= '</a>'."\n";
	$ret .= '</div>'."\n";
	return $ret; 
} // get_item_video_griglia()

/**
 * Usato nella leggi_album_per_id per preparare l'elenco dei dettagli 
 * 
 * @param  array  $dettaglio da tabella album_dettagli 
 * @return string $html - porzione di codice 
 */
 function get_item_dettagli(array $dettaglio) : string {
	$ret  = "\t".'<tr>'."\n"
	. "\t\t".'<td scope="row">'.$dettaglio['chiave'].'</td>'."\n"
	. "\t\t".'<td>'.$dettaglio['valore'].'</td>'."\n";
	if ($_COOKIE['abilitazione'] > SOLALETTURA ){
		$ret .= "\t\t".'<td>'
		. '<a href="'.URLBASE.'album.php/modifica_dettaglio/'.$dettaglio['record_id'].'" '
		. 'title="modifica dettaglio"><i class="h2 bi bi-pencil-square"></i></a>'
		. '<a href="'.URLBASE.'album.php/elimina_dettaglio/'.$dettaglio['record_id'].'" '
		. 'title="elimina dettaglio"><i class="h2 bi bi-eraser-fill"></i></a>'
		. '</td>'."\n";

	} else {
		$ret .= "\t\t".'<td>'
		. '<a href="#sololettura" '
		. 'title="modifica dettaglio"><i class="h2 bi bi-pencil-square"></i></a>'
		. '<a href="#sololettura" '
		. 'title="elimina dettaglio"><i class="h2 bi bi-eraser-fill"></i></a>'
		. '</td>'."\n";
	}
	$ret .= "\t".'</tr>'."\n";
	return $ret; 
} // get_item_dettagli()

/**
 * Mostra la pagina album 
 * /album.php/leggi/{album_id}
 * 
 * Legge la scheda dell'album, quella delle fotografie e dei video correlati 
 * tramite la chiave esterna record_id_in_album 
 * e carica la View 
 * 
 * @param   int    id    chiave tabella album 
 * @return  void   espone la pagina ed esce con exit()
 */
function leggi_album_per_id(int $album_id){
	$dbh    = New DatabaseHandler();
	$alb_h  = New Album($dbh);
	$det_h  = New AlbumDettagli($dbh);
	$foto_h = New Fotografie($dbh);
	$vid_h  = New Video($dbh);
	$scan_h = New ScansioniDisco($dbh);
	// 1. lettura album_id in album 
	// 2. lettura album_id "su di 1 livello" in album 
	// 3. lettura scansioni_disco in scansioni_disco 

	// verifica album_id e lettura dati album 
	$campi = [];
	$campi['query'] = 'SELECT * FROM ' . Album::nome_tabella
	. ' WHERE record_id = :record_id '
	. ' AND record_cancellabile_dal = :record_cancellabile_dal ';
	$campi['record_id'] = $album_id;
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
	$ret_album = $alb_h->leggi($campi);
	if ( isset($ret_album['error']) || $ret_album['numero'] == 0 ){
		http_response_code(404);
		$ret = "<p>Album $album_id Non trovato. <br>" . $ret_album['message'].'</p>';
		echo $ret;
		exit(1);
	} 
	$album = $ret_album['data'][0]; // $album['record_id'], $album['titolo_album'] ecc.
	$siete_in = str_replace('/' , ' / ', $album['percorso_completo']);
	if (isset($_COOKIE['abilitazione']) && $_COOKIE['abilitazione'] > SOLALETTURA ){
		$richieste_originali = URLBASE . 'album.php/richiesta/'. $album['record_id'];
		$aggiungi_dettaglio  = URLBASE . 'album.php/aggiungi_dettaglio/'.$album['record_id'];
	} else {
		$richieste_originali = '#sololettura';
		$aggiungi_dettaglio = '#sololettura';
	}
	
	// Su di 1 livello - album dentro album 
	$torna_su='';
	// percorso_completo termina con /
	$percorso_quasi_completo = substr($album['percorso_completo'], 0, strlen($album['percorso_completo']) - 1);
	$ultima_barra_al = strrpos($percorso_quasi_completo, '/');
	if ($ultima_barra_al===false || $ultima_barra_al==0){
		$torna_su = URLBASE.'museo.php'; // dopo ingresso
	}
	if ($torna_su == '' && $ultima_barra_al>0){
		$percorso_quasi_completo = substr($album['percorso_completo'], 0, $ultima_barra_al);
		$campi=[];
		$campi['query'] = 'SELECT * FROM album '
		. ' WHERE percorso_completo = :percorso_completo '
		. ' AND record_cancellabile_dal = :record_cancellabile_dal ';
		$campi['percorso_completo'] = $percorso_quasi_completo.'/';	
		$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
		$ret_album = $alb_h->leggi($campi);
		if ( isset($ret_album['numero']) && $ret_album['numero'] > 0){
			$torna_su = $ret_album['data'][0]['record_id']; 
			$torna_su = URLBASE.'album.php/leggi/'.$torna_su;
		} 
	}
	// ricerca con scansioni_disco 
	$return_to_scansione_id = $scan_h->get_record_id_da_percorso( $percorso_quasi_completo );
	if ($return_to_scansione_id > 0){
		$torna_su = URLBASE.'cartelle.php/leggi/'.$return_to_scansione_id;
	}else {
		$torna_su = URLBASE.'museo.php'; // dopo ingresso
	}
	
	// si vanno a leggere fotografie e video presenti in album
	$float_foto='';
	$campi=[];
	$campi['query'] = 'SELECT * FROM fotografie '
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. ' AND record_id_in_album = :record_id_in_album '
	. ' ORDER BY titolo_fotografia, record_id ';
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
	$campi['record_id_in_album']      = $album['record_id'];
	$ret_foto = $foto_h->leggi($campi);
	if ( isset($ret_foto['numero']) && $ret_foto['numero'] > 0 ){
		$float_foto='';
		$foto = $ret_foto['data']; // è sempre un array 
		//dbg echo var_dump($foto);
		for ($i=0; $i < count($foto) ; $i++) { 
			$float_foto .= get_item_foto_griglia($foto[$i]);
		}
		//dbg echo var_dump($float_foto);
	}

	$float_video='';
	$campi=[];
	$campi['query'] = 'SELECT * FROM video '
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. ' AND record_id_in_album = :record_id_in_album '
	. ' ORDER BY titolo_video, record_id ';
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
	$campi['record_id_in_album']      = $album['record_id'];
	$ret_video = $vid_h->leggi($campi);
	if ( isset($ret_video['numero']) && $ret_video['numero'] > 0){
		$video = $ret_video['data']; // è sempre un array
		for ($i=0; $i < count($video) ; $i++) { 
			$float_video .= get_item_video_griglia($video[$i]);
		}
	}

	// lettura dettagli album 
	$table_dettagli='<tr><td colspan="3">Nessun dettaglio caricato</td></tr>';
	$campi=[];
	$campi['query'] = 'SELECT * FROM album_dettagli '
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. ' AND record_id_padre = :record_id_padre ' // quello dell'album per album_dettagli 
	. ' ORDER BY chiave, valore, record_id '
	. ' ';
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
	$campi['record_id_padre']         = $album['record_id'];
	$ret_det = $det_h->leggi($campi);
	if ( isset($ret_det['numero']) && $ret_det['numero'] > 0){
		$dettagli = $ret_det['data'];
		$table_dettagli='';
		for ($i=0; $i < count($dettagli) ; $i++) { 
			$table_dettagli .= "\n" . get_item_dettagli($dettagli[$i]);
		}
	}

	include_once(ABSPATH.'aa-view/album-view.php');
	exit(0);
} // leggi_album_per_id 

/**
 * Test 
 * https://archivio.athesis77.it/aa-controller/album-controller.php?id=17&test=leggi_album_per_id
 * 
 */
if ( isset($_GET['test']) && 
		 isset($_GET['id']) && 
		 $_GET['test'] == 'leggi_album_per_id' ) {
	//dbg echo '<pre style="max-width:50rem;">debug on'."\n";
	//dbg echo 'id: '. $_GET['id'] ."\n";
	$ret = leggi_album_per_id($_GET['id']);
	//dbg echo var_dump($ret);
	//dbg echo 'fine'."\n";
}
//




/**
 * Va a inserire SOLO l'album partendo da un record di scansioni_disco 
 * se viene passato id zero si prende il primo che trova 
 * con le caratteristiche della cartella (no file) 
 * 
 * @param  int   $scansioni_id
 * @return array $ret 'ok' + 'record_id' | 'error' + 'message'
 */
function carica_album_da_scansioni_disco( int $scansioni_id) {
	$dbh    = New DatabaseHandler();
	$scan_h = New ScansioniDisco($dbh);
	$alb_h  = New Album($dbh);
	
	if ($scansioni_id == 0){
		// cerca il primo che c'è
		$campi=[];
		$campi['query'] = 'SELECT * FROM ' . ScansioniDisco::nome_tabella
		. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
		. ' AND stato_lavori = :stato_lavori '
		. " AND nome_file = '/' "
		. ' LIMIT 1 ';
		$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
		$campi['stato_lavori'] = ScansioniDisco::stato_da_fare;
		
	} else {
		// verifica id in scansioni_disco 
		$campi=[];
		$campi['query'] = 'SELECT * FROM scansioni_disco '
		. 'WHERE record_cancellabile_dal = :record_cancellabile_dal '
		. ' AND stato_lavori = :stato_lavori '
		. " AND nome_file = '/' "
		. " AND estensione = '' "
		. ' AND record_id = :record_id ';
		$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
		$campi['stato_lavori'] = ScansioniDisco::stato_da_fare;
		$campi['record_id'] = $scansioni_id;
	}
	$ret_scan = $scan_h->leggi($campi);
	if ( isset($ret_scan['error'])){
		$ret = [
			'error' => true,
			'message' => __FUNCTION__ . ' ' . __LINE__ 
			. " Non è stato trovato in scansioni_disco il record " . $scansioni_id 
			. '<br>' . $ret_scan['message']
			. '<br>campi: ' . serialize($campi)
		];
		return $ret;
	}
	if ($ret_scan['numero'] == 0 && $scansioni_id == 0){
		// L'operazione è inserire album, se non ci sono album da inserire è 
		// considerato cmq un errore
		$ret = [
			'ok' => true,
			'message' => 'Non ci sono record da elaborare.'
			. '<br>campi: ' . serialize($campi)
		];
		return $ret;
	}
	if ($ret_scan['numero'] == 0){
		// L'operazione è inserire album, se non ci sono album da inserire è 
		// considerato cmq un errore
		$ret = [
			'error' => true,
			'message' => "Non è stato trovato in scansioni_disco il record " 
			. $scansioni_id . ', ecco. '
			. '<br>campi: ' . serialize($campi)
		];
		return $ret;
	}
	$futuro_album = $ret_scan['data'][0];

	// Cambio stato - album lavori in corso 
	$scansioni_id = $futuro_album['record_id'];
	$ret_stato = $scan_h->set_stato_lavori_in_scansioni_disco($scansioni_id, ScansioniDisco::stato_in_corso);
	if (isset($ret_stato['error'])){
		$ret = [
			'error' => true,
			'message' => "Non è stato aggiornato in scansioni_disco lo stato_lavori per il record " 
			. $scansioni_id . '<br>'
			. $ret_stato['message']
		];
		return $ret;
	}

	// deve essere presente almeno un record diverso da se stesso 
	$campi=[];
	$campi['query']= 'SELECT * FROM ' . ScansioniDisco::nome_tabella 
	. " WHERE estensione > '' "
	. ' AND disco = :disco       AND livello1 = :livello1 ';
	$campi['disco']     = $futuro_album['disco'];
	$campi['livello1']  = $futuro_album['livello1'];
	if ($futuro_album['livello2'] == ''){
		$campi['query'] .= " AND livello2 = '' ";
	} else {
		$campi['query'] .= ' AND livello2 = :livello2 ';
		$campi['livello2']  = $futuro_album['livello2'];
	}
	if ($futuro_album['livello3'] == ''){
		$campi['query'] .= " AND livello3 = '' ";
	} else {
		$campi['query'] .= ' AND livello3 = :livello3 ';
		$campi['livello3']  = $futuro_album['livello3'];
	}
	if ($futuro_album['livello4'] == ''){
		$campi['query'] .= " AND livello4 = '' ";
	} else {
		$campi['query'] .= ' AND livello4 = :livello4 ';
		$campi['livello4']  = $futuro_album['livello4'];
	}
	if ($futuro_album['livello5'] == ''){
		$campi['query'] .= " AND livello5 = '' ";
	} else {
		$campi['query'] .= ' AND livello5 = :livello5 ';
		$campi['livello5']  = $futuro_album['livello5'];
	}
	if ($futuro_album['livello6'] == ''){
		$campi['query'] .= " AND livello6 = '' ";
	} else {
		$campi['query'] .= ' AND livello6 = :livello6 ';
		$campi['livello6']  = $futuro_album['livello6'];
	}
	$ret_check = $scan_h->leggi($campi);
	if (isset($ret_check['error']) || $ret_check['numero']== 0){
		// aggiorna stato 
		$ret_stato = $scan_h->set_stato_lavori_in_scansioni_disco($scansioni_id, ScansioniDisco::stato_completati);

		$ret = [
			'error' => true,
			'message' => "Il record $scansioni_id in scansioni_disco non contiene materiali " 
			. '<br>futuro: ' . serialize($futuro_album)
			. '<br>ret_check: ' . serialize($ret_check)
			. '<br><br>campi: ' . serialize($campi)
		];
		return $ret;
	}

	//
	// inserimento album - prende il primo 
	$album=[];
	$album['disco']= $futuro_album['disco'];
	$album['titolo_album']= $futuro_album['livello1'];
	$album['percorso_completo'] = $futuro_album['livello1'];
	if ($futuro_album['livello2'] > ''){
		$album['titolo_album']= $futuro_album['livello2'];
		$album['percorso_completo'] .= '/'.$futuro_album['livello2'];
	}
	if ($futuro_album['livello3'] > ''){
		$album['titolo_album']= $futuro_album['livello3'];
		$album['percorso_completo'] .= '/'.$futuro_album['livello3'];
	}
	if ($futuro_album['livello4'] > ''){
		$album['titolo_album']= $futuro_album['livello4'];
		$album['percorso_completo'] .= '/'.$futuro_album['livello4'];
	}
	if ($futuro_album['livello5'] > ''){
		$album['titolo_album']= $futuro_album['livello5'];
		$album['percorso_completo'] .= '/'.$futuro_album['livello5'];
	}
	if ($futuro_album['livello6'] > ''){
		$album['titolo_album']= $futuro_album['livello6'];
		$album['percorso_completo'] .= '/'.$futuro_album['livello6'];
	}
	$album['percorso_completo'] .= '/';
	$album['record_id_in_scansioni_disco'] = $scansioni_id;
	// echo "\n".'album'; 
	// echo var_dump($album);
	$ret_alb = $alb_h->aggiungi($album);
	if (isset($ret_alb['error'])){
		return $ret_alb;
	} 
	// aggiorna stato - i record che restano a "in corso" vanno esaminati
	$ret_stato = $scan_h->set_stato_lavori_in_scansioni_disco($scansioni_id, ScansioniDisco::stato_completati);
	if (isset($ret_stato['error'])){
		$ret = [
			'error' => true,
			'message' => "Non è stato aggiornato in scansioni_disco lo stato per il record " 
			. $scansioni_id . '<br>'
			. ' ' . $ret_stato['message'] . '<br>'
			. serialize($campi)
		];
		return $ret;
	}
	// per caricare le fotografie 
	// fotografie-controller/ carica_fotografie_da_album 
	// per caricare i video 
	// video-controller / carica_video_da_album 

	return $ret_alb;
} // carica_album_da_scansioni_disco

/** TEST 
 * 
 * https://archivio.athesis77.it/aa-controller/album-controller.php?id=66&test=carica_album_da_scansioni_disco
 */
	if ( isset($_GET['test']) && 
		 isset($_GET['id']) && 
	   $_GET['test'] == 'carica_album_da_scansioni_disco') {
		echo '<pre>debug on'."\n";
		$ret = carica_album_da_scansioni_disco($_GET['id']);
		echo 'fine'."\n";
	}
//


/**
 * carica_album_dettagli_foto_video 
 * 
 * Quello che è stato caricato in scansioni_disco diventa:
 * - album 
 * - fotografie dell'album 
 * - video dell'album 
 * Con l'occasione 
 */
function carica_album_dettagli_foto_video(int $scansioni_id){
	$dbh    = New DatabaseHandler();
	$alb_h  = New Album($dbh);

	// carica album da scansioni_disco - solo album 
	$ret_a = carica_album_da_scansioni_disco($scansioni_id);
	if (isset($ret_a['error'])){
		if (str_contains($ret_a['message'], 'non contiene materiali')){
			// cartella vuota, niente di che
			http_response_code(404);
			echo '<pre style="color: red;"><strong>'.$ret_a['message'].'</strong></pre>'."\n";
			exit(1);
		}
		if (str_contains($ret_a['message'], 'non ci sono')){
			// cartella vuota, niente di che
			http_response_code(404);
			echo '<pre style="color: red;"><strong>'.$ret_a['message'].'</strong></pre>'."\n";
			exit(1);
		}
	}
	if (!isset($ret_a['record_id'])){
		// che è?
			http_response_code(404);
			echo '<p style="color: red;"><strong>' .  __FUNCTION__ 
			. '<br>' . serialize($ret_a).'</strong></p>'."\n";
			exit(1);
	}
	$album_id=$ret_a['record_id'];
	echo '<br>Caricato album_id: '.$album_id;
	
	// cambio stato dell'album 'da fare' 
	$alb_h->set_stato_lavori_album($album_id, Album::stato_in_corso);
	echo '<br>Aggiornato stato_lavori per album_id: '.$album_id .'<br>';

	//
	// Aggiunta dettagli album da album 
	echo '<pre style="color: red;">Carica dettagli da album </pre>'."\n";
	$ret_a=[];
	$ret_a = aggiungi_dettagli_album_da_album($album_id);
	if (isset($ret_a['error'])){
		http_response_code(404);
		echo '<pre style="color: red;"><strong>Inserimento dettagli per album non riuscito</strong></pre>'."\n";
		echo '<p style="color: red;">'.$ret_a['message'].'</p>'."\n";
		echo var_dump($ret_a);
		exit(1);
	}

	// carica fotografie dell'album da scansioni_disco 
	// fotografie-controller
	echo '<pre style="color: red;">Carica foto da album </pre>'."\n";
	$ret_f = carica_fotografie_da_album($album_id);
	// possono anche esserci 0 fotografie 
	if (isset($ret_f['message']) && str_contains($ret_f['message'], 'Non ci sono')){
		echo '<pre style="color: red;"><strong>Inserimento foto per album non effettuato</strong></pre>'."\n";
		echo '<p style="color: red;">'.$ret_f['message'].'</p>'."\n";
	} elseif (isset($ret_f['error'])){
		http_response_code(404);
		echo '<pre style="color: red;"><strong>Inserimento foto per album non riuscito</strong></pre>'."\n";
		echo '<p style="color: red;">'.$ret_f['message'].'</p>'."\n";
		echo var_dump($ret_f);
		exit(1);
 	} // carica_fotografie_da_album

	// carica video dell'album da scansioni_disco 
	// video-controller 
	echo '<pre style="color: red;">Carica video da album </pre>'."\n";
	$ret_v = carica_video_da_album($album_id);
	if (isset($ret_v['message']) && str_contains($ret_v['message'], 'Non ci sono')){
		echo '<pre style="color: red;"><strong>Inserimento video per album non effettuato</strong></pre>'."\n";
		echo '<p style="color: red;">'.$ret_f['message'].'</p>'."\n";
	} elseif (isset($ret_v['error'])){
		http_response_code(404);
		echo '<pre style="color: red;"><strong>Inserimento video per album non riuscito</strong></pre>'."\n";
		echo '<p style="color: red;">'.$ret_v['message'].'</p>'."\n";
		echo var_dump($ret_v);
		exit(1);
	}
	
	// cambio stato dell'album 'in corso'
	echo '<pre style="color: red;">Cambio stato lavori all album in 2 completati </pre>'."\n";
	$alb_h->set_stato_lavori_album($album_id, Album::stato_completati);

	// mostra l'album caricato 
	leggi_album_per_id($album_id);
	exit(0);
} // carica_album_dettagli_foto_video()

/**
 * Inserimento richiesta di accesso all'originale per tutto l'album 
 * 
 * @param  int $album_id 
 * @return void|true 
 */
function carica_richiesta_album(int $album_id){
	$dbh    = New DatabaseHandler();
	$alb_h  = New Album($dbh);
	$ric_h  = New Richieste($dbh);

	if ($_COOKIE['abilitazione'] <= SOLALETTURA ){
		http_response_code(404);
		echo '<pre style="color: red;"><strong>Operazione non consentita</strong></pre>'."\n";
		exit(1);
	}

	$alb_h->set_record_id($album_id);
	$campi=[];
	$campi['query'] = 'SELECT * FROM album ' // TODO Album::tabella
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. ' AND record_id = :record_id ';
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
	$campi['record_id']               = $alb_h->get_record_id();
	$ret_alb = $alb_h->leggi($campi);
	if (isset($ret_alb['error']) || $ret_alb['numero'] == 0 ){
		http_response_code(404);
		echo '<pre style="color: red;"><strong>Inserimento album non riuscito</strong></pre>'."\n";
		exit(1);
	}
	$album = $ret_alb['data'][0];

	// inserimento richiesta 
	$campi=[];
	$campi['record_id_in_consultatori_calendario'] = $_COOKIE['id_calendario'];
	$campi['oggetto_richiesta']     = 'album';
	$campi['record_id_richiesta']   = $album_id;
	$ret_ric = $ric_h->aggiungi($campi);
	if (isset($ret_ric['error'])){
		http_response_code(404);
		echo '<pre style="color: red;"><strong>Inserimento album non riuscito</strong></pre>'."\n";
		exit(1);
	}

	$_SESSION['messaggio'] = 'Richiesta di accesso alta risoluzione inoltrata'; 
} // carica_richiesta_album_per_id



/**
 * CREATE
 * 
 * @param  int $album_id 
 * @return array elenco dei dettagli caricati
 * 
 */
function aggiungi_dettagli_album_da_album( int $album_id ) : array {
	$dbh    = New DatabaseHandler(); 
	$alb_dh = New AlbumDettagli($dbh);
	$aggiunti = [];

	$titolo_album = get_titolo_album( $album_id);
	if ($titolo_album == ''){
		throw new Exception("album id:{$album_id} non trovato", 1);		
	}

	$data_evento = get_data_evento($titolo_album);
	if ($data_evento > ''){
		$campi=[];
		$campi['record_id_padre'] = $album_id;
		$campi['chiave'] = 'data/evento';
		$campi['valore'] = $data_evento; 
		$ret_aggiungi = $alb_dh->aggiungi($campi);
		//dbg echo '<br>data_evento si: '. $data_evento . "\n";
		//dbg echo var_dump($ret_aggiungi);
	if (isset($ret_aggiunti['ok'])){
		$aggiunti[] = 'data/evento: '. $data_evento;
	}
		// sfilo 
		$titolo_album = str_replace($data_evento, '', $titolo_album);
		if (str_contains($data_evento, ' DP')){
			$data_evento = str_replace(' DP', '', $data_evento);
			$titolo_album = str_replace($data_evento, '', $titolo_album);
		}
		if (str_contains($data_evento, '-')){
			$data_evento = str_replace('-', ' ', $data_evento);
			$titolo_album = str_replace($data_evento, '', $titolo_album);
		}
		$titolo_album = trim($titolo_album);
	}

	$luogo = get_luogo($titolo_album);
	//dbg echo '<br>luogo: ' . $luogo; 
	if ($luogo > ''){
		$campi=[];
		$campi['record_id_padre'] = $album_id;
		$campi['chiave'] = 'luogo/comune';
		$campi['valore'] = $luogo; 
		$ret_aggiungi = $alb_dh->aggiungi($campi);
		//dbg echo '<br>luogo si: '. $luogo . "\n";
		//dbg echo var_dump($ret_aggiungi);
	if (isset($ret_aggiunti['ok'])){
		$aggiunti[] = 'luogo/comune: '. $luogo;
	}
		// sfilo 
		$titolo_album = str_replace($luogo, '', $titolo_album);
		$titolo_album = trim($titolo_album);
	}

	$sigla_autore = get_autore_sigla_6($titolo_album);
	//dbg echo '<br>Sigla autore: ' . $sigla_autore; 
	if ($sigla_autore > ''){
		$campi=[];
		$campi['record_id_padre'] = $album_id;
		$campi['chiave'] = 'codice/autore/athesis';
		$campi['valore'] = $sigla_autore; 
		$ret_aggiungi = $alb_dh->aggiungi($campi);
		//dbg echo '<br>codice/autore/athesis si: '. $sigla_autore . "\n";
		//dbg echo var_dump($ret_aggiungi);
	if (isset($ret_aggiunti['ok'])){
		$aggiunti[] = 'codice/autore/athesis: '. $sigla_autore;
	}
	// sfilo 
		$titolo_album = str_replace($sigla_autore, '', $titolo_album);
		$titolo_album = trim($titolo_album);
	}
	// e alla fine  
	$campi=[];
	$campi['record_id_padre'] = $album_id;
	$campi['chiave'] = 'nome/manifestazione-soggetto';
	$campi['valore'] = $titolo_album; 
	$ret_aggiungi = $alb_dh->aggiungi($campi);
	//dbg echo '<br>nome/manifestazione-soggetto OK : ' . $titolo_album . '<br>';
	//dbg echo var_dump($ret_aggiungi);
	$aggiunti[] = 'nome/manifestazione-soggetto: '. $titolo_album;
	$ret = [
	'ok' => true,
	'numero' => count($aggiunti),
	'data' => $aggiunti
	];
	return $ret;
}

/**
 * https://archivio.athesis77.it/aa-controller/album-controller.php?id=17&test=aggiungi_dettagli_album_da_album
 * 
 */
 if ( isset($_GET['test']) && 
			isset($_GET['id']) && 
			$_GET['test'] == 'aggiungi_dettagli_album_da_album' ) {
	echo '<pre>debug on'."\n";
	echo 'id: '. $_GET['id'] ."\n";
	$ret = aggiungi_dettagli_album_da_album($_GET['id']);
	echo var_dump($ret);
	echo 'fine'."\n";
}


/**
 * DELETE 
 * @param  int  $dettaglio_id 
 * @return void 
 */
function elimina_dettaglio_album( int $dettaglio_id){
	$dbh    = New DatabaseHandler();
	$adet_h = New AlbumDettagli($dbh); 
	
	$campi=[];
	$campi['query'] = 'SELECT * from album_dettagli '
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. ' AND record_id = :record_id ';
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
	$campi['record_id'] = $dettaglio_id;
	$ret_det = $adet_h->leggi($campi);
	if ( isset($ret_det['error']) || $ret_det['numero'] == 0){
		$ret = [
			'error' => true, 
			'message' => __FILE__ . ' ' . __FUNCTION__ 
			. ' Non è stato possibile modificare il dettaglio '
			. ' per ' . $ret_det['error']
			. ' campi: ' . serialize($campi)
		];
		echo var_dump($ret);
		exit(0);
	}
	$dettaglio = $ret_det['data'][0];

	// 
	// resta il consultatore_id originario 
	$campi=[];
	$campi['update'] = 'UPDATE album_dettagli '
	. ' SET record_cancellabile_dal = :record_cancellabile_dal  '
	. ' WHERE record_id = :record_id ';
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_now();
	$campi['record_id']               = $dettaglio_id;
	$ret_mod = $adet_h->modifica($campi);
	if ( isset($ret_det['error']) || $ret_det['numero'] == 0){
		$ret = [
			'error' => true, 
			'message' => __FILE__ . ' ' . __FUNCTION__ 
			. ' Non è stato possibile modificare il dettaglio '
			. ' per ' . $ret_det['error']
			. ' campi: ' . serialize($campi)
		];
		echo var_dump($ret);
		exit(0);
	}
	// torniamo alla scheda fotografia 
	leggi_album_per_id($dettaglio['record_id_padre']);
	exit(0);  
} // elimina_dettaglio_album()


/**
 * sostituisce alcune funzioni precedenti 
 * @param  int  $album_id 
 * @param array $dati_input quelli del modulo 
 * Se mancano espone la mappa 
 * Se presenti aggiunge il dettaglio 
 * 
 */
function aggiungi_dettaglio_album_da_modulo(int $album_id, array $dati_input){
	$dbh   = new DatabaseHandler(); 
	$alb_h = new Album($dbh);
	$chi_h = new Chiavi($dbh);
	$adet_h = new AlbumDettagli($dbh);

	//
	// verifica album_id
	$alb_h->set_record_id($album_id);
	$campi=[];
	$campi['query']= 'SELECT * FROM ' . Album::nome_tabella 
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. ' AND record_id = :record_id ';
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
	$campi['record_id'] = $alb_h->get_record_id();
	$ret_album= $alb_h->leggi($campi);
	if (isset($ret_album['error'])){
		$ret = '<p>' . __FUNCTION__
		. "<br>Si è verificato un errore nella lettura di " . Album::nome_tabella
		. '<br>' . $ret_album['message']
		. '<br>Campi: ' . serialize($campi) . '</p>';
		echo $ret;
		exit(1);
	}
	if ($ret_album['numero']==0){
		$ret = '<p>' . __FUNCTION__
		. '<br>Nessun album trovato. '
		. '<br>Campi: ' . serialize($campi) 
		. '</p>';
		echo $ret;
		exit(1);
	}
	$album = $ret_album['data'][0];
	//
	// elenco chiavi disponibili per la SELECT
	$option_list_chiave = $chi_h->get_chiavi_option_list();

	//
	// mancano i dati - si espone il modulo 
	if (!isset($dati_input['aggiungi_dettaglio'])){
		$_SESSION['messaggio'] = "Aggiungi il dettaglio chiave+valore "
		. "scegliendo la chiave tra quelle "
		. "disponibili, consulta il manuale in caso di dubbi.";
		$leggi_album = URLBASE.'album.php/leggi/'.$album['record_id'];
		$aggiungi_dettaglio = URLBASE.'album.php/aggiungi_dettaglio/'.$album['record_id'];
		require_once(ABSPATH.'aa-view/dettaglio-album-aggiungi-view.php');
		exit(0); 
	}

	// 
	// i dati ci sono, si va a inserire 
	$adet_h->set_record_id_padre($album['record_id']);
	$adet_h->set_chiave($dati_input['chiave']);
	$adet_h->set_valore($dati_input['valore']);
	$adet_h->set_consultatore_id($_COOKIE['id_calendario']);
	$campi=[];
	$campi['record_id_padre'] = $adet_h->get_record_id_padre();
	$campi['chiave']          = $adet_h->get_chiave();
	$campi['valore']          = $adet_h->get_valore();
	$campi['consultatore_id'] = $adet_h->get_consultatore_id();
	$ret_det = $adet_h->aggiungi($campi);
	if (isset($ret_det['error'])){
		$ret = '<p>' . __FUNCTION__
		. '<br>Si è verificato un errore nella scrittura di ' . AlbumDettagli::nome_tabella
		. '<br>' . $ret_det['message']
		. '<br>Campi: ' . serialize($campi) 
		. '</p>';
		echo $ret;
		exit(1);
	}
	//
	// inserimento effettuato, si va alla pagina dell'album 
	leggi_album_per_id($album['record_id']);
	exit(0);
} // aggiungi_dettaglio_album_da_modulo()


/**
 * 
 */
function modifica_dettaglio_album_da_modulo(int $dettaglio_id, array $dati_input){
	$dbh   = new DatabaseHandler(); 
	$alb_h = new Album($dbh);
	$chi_h = new Chiavi($dbh);
	$adet_h = new AlbumDettagli($dbh);
	// verifica
	$adet_h->set_record_id($dettaglio_id);
	$campi=[];
	$campi['query']= 'SELECT * FROM ' . AlbumDettagli::nome_tabella 
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. ' AND record_id = :record_id ';
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
	$campi['record_id'] = $adet_h->get_record_id();
	$ret_det= $adet_h->leggi($campi);
	if (isset($ret_det['error'])){
		$ret = '<p>' . __FUNCTION__
		. "<br>Si è verificato un errore nella lettura di " . AlbumDettagli::nome_tabella
		. '<br>' . $ret_det['message']
		. '<br>Campi: ' . serialize($campi) . '</p>';
		echo $ret;
		exit(1);
	}
	if ($ret_det['numero']==0){
		$ret = '<p>' . __FUNCTION__
		. '<br>Nessun dettaglio album trovato. '
		. '<br>Campi: ' . serialize($campi) 
		. '</p>';
		echo $ret;
		exit(1);
	}
	$dettaglio          = $ret_det['data'][0];
	$leggi_album        = URLBASE.'album.php/leggi/'.$dettaglio['record_id_padre'];
	$aggiorna_dettaglio = URLBASE.'album.php/modifica_dettaglio/'.$dettaglio['record_id'];
	$dettaglio_id       = $dettaglio['record_id'];
	$record_id          = $dettaglio['record_id'];
	$album_id           = $dettaglio['record_id_padre'];
	//
	// elenco chiavi disponibili per la SELECT
	$option_list_chiave = $chi_h->get_chiavi_option_list();
	//
	// mancano i dati - si espone il modulo 
	if (!isset($dati_input['valore'])){
		$_SESSION['messaggio'] = "Aggiungi il dettaglio chiave+valore "
		. "scegliendo la chiave tra quelle "
		. "disponibili, consulta il manuale in caso di dubbi.";
		require_once(ABSPATH.'aa-view/dettaglio-album-modifica-view.php');
		exit(0); 
	}
	// i dati ci sono e andiamo a modificare il dettaglio 
	$adet_h->set_record_id($dettaglio_id);
	$campi['update'] ='UPDATE ' . AlbumDettagli::nome_tabella
	. 'SET record_cancellabile_dal = :record_cancellabile_dal '
	. 'WHERE record_id = :record_id ';
	$campi['record_cancellabile_dal']=$dbh->get_datetime_now();
	$campi['record_id']= $dettaglio_id;
	$ret_del = $adet_h->modifica($campi);
	if (isset($ret_del['error'])){
		$ret = '<p>' . __FUNCTION__
		. '<br>Si è verificato un errore nella modifica di ' . AlbumDettagli::nome_tabella
		. '<br>' . $ret_del['message']
		. '<br>Campi: ' . serialize($campi) . '</p>';
		echo $ret;
		exit(1);
	}
	$adet_h->set_record_id_padre($album_id);
	$adet_h->set_chiave($dati_input['chiave']);
	$adet_h->set_valore($dati_input['valore']);
	$adet_h->set_consultatore_id($_COOKIE['id_calendario']);
	$campi=[];
	$campi['record_id_padre']=$adet_h->get_record_id_padre();
	$campi['chiave']=$adet_h->get_chiave();
	$campi['valore']=$adet_h->get_valore();
	$campi['consultatore_id']=$adet_h->get_consultatore_id();
	$ret_ins = $adet_h->aggiungi($campi);
	if (isset($ret_ins['error'])){
		$ret = '<p>' . __FUNCTION__
		. '<br>Si è verificato un errore nella scrittura di ' . AlbumDettagli::nome_tabella
		. '<br>' . $ret_ins['message']
		. '<br>Campi: ' . serialize($campi) 
		. '</p>';
		echo $ret;
		exit(1);
	}
	//
	// inserimento effettuato, si va alla pagina dell'album 
	leggi_album_per_id($album_id);
	exit(0);
} // modifica_dettaglio_album_da_modulo()
