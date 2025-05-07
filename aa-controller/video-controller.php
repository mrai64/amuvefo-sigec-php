<?php
/**
 * @source /aa-controller/video-controller.php
 * @author Massimo Rainato <maxrainato@libero.it>
 *
 * VIDEO controller
 *
 * - leggi_video_per_id
 *   presenta la pagina del video con i controller 
 * 
 * - aggiungi_dettaglio_video_da_modulo
 *   se non ci sono i dati_input espone il modulo per 
 *   aggiungere un dettaglio 
 * 
 * - modifica_dettaglio_video_da_modulo
 * 
 * - elimina_dettaglio_video_da_modulo
 * 
 * - leggi_video_precedente
 *   rintraccia, se presente, un video nell'album
 * 
 * - leggi_video_seguente
 *   rintraccia, se presente, un video nell'album
 * 
 * - carica_video_da_album
 * 
 * - carica_dettaglio TODO può diventare un metodo della classe
 * 
 * - carica_dettagli_da_video
 *   carica dettagli dal nome del file, serve rintracciare qualche libreria che 
 *   consenta di tirare fuori dal file i "dati exif" che non sono dati exif
 *  
 *   -carica_dettagli_da_quicktime .mov .m4v .mp4 
 *   -carica_dettagli_da_matroska  .mkv 
 *   -carica_dettagli_da_ffmpeg 
 * @see https://stackoverflow.com/questions/34689518/get-mp4-file-tags/34689928#34689928
 * 
 */
if (!defined('ABSPATH')){
	include_once('../_config.php');
}
include_once(ABSPATH . 'aa-model/database-handler-oop.php');
include_once(ABSPATH . 'aa-model/video-oop.php');
include_once(ABSPATH . 'aa-model/video-dettagli-oop.php');
include_once(ABSPATH . 'aa-model/album-oop.php');
include_once(ABSPATH . 'aa-model/scansioni-disco-oop.php');
include_once(ABSPATH . 'aa-model/chiavi-oop.php');

include_once(ABSPATH . 'aa-controller/controller-base.php');
include_once(ABSPATH . 'aa-controller/carica-dettaglio-libreria.php');

/**
 * Espone la schermata del video singolo 
 */
function leggi_video_per_id(int $video_id){
	$dbh    = new DatabaseHandler();
	$vid_h  = new Video($dbh);
	$vdet_h = new VideoDettagli($dbh);
	
	$ret_video = $vid_h->get_video_from_id($video_id);
	if (isset($ret_video['error'])){
		$ret = '<p>' . __FUNCTION__
		. "<br>Si è verificato un errore nella lettura di " . Video::nome_tabella
		. '<br>' . $ret_video['message']
		. '<br>video_id: ' . $video_id . '</p>';
		echo $ret;
		exit(1);
	}
	if ($ret_video['numero']==0){
		$ret = '<p>' . __FUNCTION__
		. '<br>Nessun video trovato. '
		. '<br>video_id: ' . $video_id . '</p>';
		echo $ret;
		exit(1);
	}
	$video = $ret_video['data'][0];
	$video_src = str_ireplace('//', '/', $video['percorso_completo']);
	$torna_all_album = URLBASE.'album.php/leggi/'.$video['record_id_in_album'];
	$video_precedente= URLBASE.'video.php/precedente/'.$video['record_id'];
	$video_seguente  = URLBASE.'video.php/seguente/'  .$video['record_id'];
	$siete_in = $video['percorso_completo'];
	$siete_in = dirname($siete_in);
	$siete_in = str_ireplace('/', ' / ', $siete_in);

	$richiesta_originali = '#solalettura';
	$aggiungi_dettaglio  = '#sololettura';
	if (get_set_abilitazione() > SOLALETTURA){
		$richiesta_originali = URLBASE.'video.php/richiesta/'
		. $video['record_id']
		. '?return_to=' . urlencode($_SERVER['REQUEST_URI']);

		$aggiungi_dettaglio = URLBASE.'video.php/aggiungi_dettaglio/'
		. $video['record_id'];
	}

	// lettura dettaglio abbinati al video
	$campi=[];
	$campi['query']= 'SELECT * FROM ' . VideoDettagli::nome_tabella
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. ' AND record_id_padre = :record_id_padre '
	. ' ORDER BY chiave, record_id ';
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
	$campi['record_id_padre']         = $video['record_id'];
	$ret_dett = $vdet_h->leggi($campi);
	$dettagli = (isset($ret_dett['numero']) && $ret_dett['numero'] > 0) ? $ret_dett['data'] : [];

	$durata_video='n.d.';
	for ($i=0; $i < count($dettagli) ; $i++) { 
		$dettaglio = $dettagli[$i];
		if ($dettaglio['chiave'] == 'dimensione/durata'){
			$durata_video = $dettaglio['valore'];
			continue;
		}
	}

	// e via si mostra
	require_once(ABSPATH.'aa-view/video-view.php');
	exit(0); // e fine
} // leggi_video_per_id()

/** TEST
 *
 * https://archivio.athesis77.it/video.php/leggi/14
 * https://archivio.athesis77.it/aa-controller/video-controller.php?id=14&test=leggi_video_per_id
 *
 */
	if (isset($_GET['test']) &&
      isset($_GET['id'])   &&
	    $_GET['test'] == 'leggi_video_per_id'){
		leggi_video_per_id($_GET['id']);
		exit(0);
	}
//

function aggiungi_dettaglio_video_da_modulo(int $video_id, array $dati_input) {
	$dbh    = New DatabaseHandler();
	$vid_h  = New Video($dbh);
	$vdet_h = New VideoDettagli($dbh);
	$chi_h  = New Chiavi($dbh);

	$ret_video = $vid_h->get_video_from_id($video_id);
	if (isset($ret_video['error'])){
		$ret = '<p>' . __FUNCTION__
		. "<br>Si è verificato un errore nella lettura di " . Video::nome_tabella
		. '<br>' . $ret_video['message']
		. '<br>id: ' . $video_id. '</p>';
		echo $ret;
		exit(1);
	}
	if ($ret_video['numero']==0){
		$ret = '<p>' . __FUNCTION__
		. '<br>Nessun video trovato. '
		. '<br>id: ' . $video_id . '</p>';
		echo $ret;
		exit(1);
	}
	$video = $ret_video['data'][0];
	//
	// elenco chiavi disponibili 
	$option_list_chiave = $chi_h->get_chiavi_option_list();
	// 
	// mancano i dati - si espone il modulo
	if (!isset($dati_input['valore'])){
		$_SESSION['messaggio'] = "Aggiungi il dettaglio chiave+valore "
		. "scegliendo la chiave tra quelle "
		. "disponibili, consulta il manuale in caso di dubbi.";
		$leggi_video        = URLBASE.'video.php/leggi/'.$video['record_id'];
		$aggiungi_dettaglio = URLBASE.'video.php/aggiungi_dettaglio/'.$video['record_id'];

		require_once(ABSPATH.'aa-view/dettaglio-video-aggiungi-view.php');
		exit(0); 
	}

	// abbiamo i dati - si aggiunge il dettaglio 
	carico_dettaglio_video($video['record_id'], $dati_input['chiave'], $dati_input['valore']);
	//
	// inserimento effettuato, si va alla pagina del video 
	leggi_video_per_id($video['record_id']);
	exit(0);
} // aggiungi_dettaglio_video_da_modulo()

function modifica_dettaglio_video_da_modulo(int $dettaglio_id, array $dati_input){
	$dbh    = New DatabaseHandler();
	$vid_h  = New Video($dbh);
	$vdet_h = New VideoDettagli($dbh);
	//
	// verifica dettaglio_id
	$vdet_h->set_record_id($dettaglio_id);
	$campi=[];
	$campi['query']= 'SELECT * FROM ' . VideoDettagli::nome_tabella 
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. ' AND record_id = :record_id ';
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
	$campi['record_id']               = $vdet_h->get_record_id();
	$ret_det = $vdet_h->leggi($campi);
	if (isset($ret_det['error'])){
		$ret = '<p>' . __FILE__ .' '. __FUNCTION__
		. '<br>Si è verificato un errore nella scrittura di ' . VideoDettagli::nome_tabella
		. '<br>' . $ret_det['message']
		. '<br>Campi: ' . serialize($campi) . '</p>';
		echo $ret;
		exit(1);
	}
	$dettaglio          = $ret_det['data'][0];
	$leggi_video        = URLBASE.'video.php/leggi/'.$dettaglio['record_id_padre'];
	$aggiorna_dettaglio = URLBASE.'video.php/modifica_dettaglio/'.$dettaglio['record_id'];
	$dettaglio_id       = $dettaglio['record_id'];
	$video_id           = $dettaglio['record_id_padre'];
	// 
	// mancano i dati - si espone per la modifica 
	if (!isset($dati_input['valore'])){
		require_once(ABSPATH.'aa-view/dettaglio-video-modifica-view.php');
		exit(0);
	}
	// ci sono i dati - si modifica
	$vdet_h->set_valore($dati_input['valore']);
	$vdet_h->set_record_id($dettaglio_id);
	$vdet_h->set_record_id_padre($video_id);
	$campi=[];
	$campi['update'] = 'UPDATE ' . VideoDettagli::nome_tabella
	. ' SET valore = :valore '
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. ' AND record_id = :record_id ';
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
	$campi['record_id']               = $vdet_h->get_record_id();
	$campi['valore']                  = $vdet_h->get_valore();
	$ret_mod = $vdet_h->modifica($campi);
	if (isset($ret_mod['error'])){
		$ret = '<p>' . __FILE__ .' '. __FUNCTION__
		. '<br>Si è verificato un errore nella scrittura di ' . VideoDettagli::nome_tabella
		. '<br>' . $ret_mod['message']
		. '<br>Campi: ' . serialize($campi) . '</p>';
		echo $ret;
		exit(1);
	}
	//
	// aggiornamento effettuato, si va alla pagina del video 
	leggi_video_per_id($video_id);
	exit(0);
} // modifica_dettaglio_video_da_modulo()



function elimina_dettaglio_video_da_modulo(int $dettaglio_id){
	$dbh    = New DatabaseHandler();
	$vdet_h = New VideoDettagli($dbh);
	//
	// verifica dettaglio_id
	$vdet_h->set_record_id($dettaglio_id);
	$campi=[];
	$campi['query']= 'SELECT * FROM ' . VideoDettagli::nome_tabella 
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. ' AND record_id = :record_id ';
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
	$campi['record_id']               = $vdet_h->get_record_id();
	$ret_det = $vdet_h->leggi($campi);
	if (isset($ret_det['error'])){
		$ret = '<p>' .__FILE__ .' '. __FUNCTION__
		. '<br>Si è verificato un errore nella scrittura di ' . VideoDettagli::nome_tabella
		. '<br>' . $ret_det['message']
		. '<br>Campi: ' . serialize($campi) . '</p>';
		echo $ret;
		exit(1);
	}
	$dettaglio          = $ret_det['data'][0];
	$dettaglio_id       = $dettaglio['record_id'];
	$video_id           = $dettaglio['record_id_padre'];
	// 
	// aggiornamento 
	$vdet_h->set_record_id($dettaglio_id);
	$vdet_h->set_record_cancellabile_dal($dbh->get_datetime_now());
	$campi=[];
	$campi['update'] = 'UPDATE ' . VideoDettagli::nome_tabella
	. ' SET record_cancellabile_dal = :record_cancellabile_dal '
	. ' WHERE record_id = :record_id ';
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_now();
	$campi['record_id']               = $vdet_h->get_record_id();
	$ret_mod = $vdet_h->modifica($campi);
	if (isset($ret_mod['error'])){
		$ret = '<p>' . __FILE__ .' '. __FUNCTION__
		. '<br>Si è verificato un errore nella scrittura di ' . VideoDettagli::nome_tabella
		. '<br>' . $ret_mod['message']
		. '<br>Campi: ' . serialize($campi) . '</p>';
		echo $ret;
		exit(1);
	}
	//
	// aggiornamento effettuato, si va alla pagina del video 
	header('Location: '. URLBASE.'video.php/leggi/'.$video_id); 
	leggi_video_per_id($video_id);
	exit(0);
} // elimina_dettaglio_video_da_modulo()


function leggi_video_precedente(int $video_id) : int {
	$dbh   = new DatabaseHandler();
	$vid_h = new Video($dbh);

	$ret_video = $vid_h->get_video_from_id($video_id);
	if (isset($ret_video['error'])){
		$ret = '<p>' . __FILE__ .' '. __FUNCTION__
		. '<br>Si è verificato un errore nella lettura di ' . Video::nome_tabella
		. '<br>' . $ret_video['message']
		. '<br>Campi: ' . serialize($campi) . '</p>';
		echo $ret;
		exit(1);
	}
	if ($ret_video['numero']==0){
		$ret = '<p>' . __FILE__ .' '. __FUNCTION__
		. '<br>Si è verificato un errore nella lettura di ' . Video::nome_tabella
		. '<br>Campi: ' . serialize($campi) . '</p>';
		echo $ret;
		exit(1);
	}
	$video = $ret_video['data'][0];
	$video_precedente= $video['record_id'];
	//
	// cerca quello precedente 
	$campi=[];
	$campi['query'] = 'SELECT * FROM ' . Video::nome_tabella
	. 'WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. 'AND record_id_in_album = :record_id_in_album '
	. 'AND titolo_video < :titolo_video '
	. ' ORDER BY titolo_video DESC, record_id DESC ';
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
	$campi['record_id_in_album']      = $video['record_id_in_album'];
	$campi['titolo_video']            = $video['titolo_video'];
	$ret_video=[];
	$ret_video = $vid_h->leggi($campi);

	if (isset($ret_video['numero']) && $ret_video['numero'] > 0){
		$video_precedente= $ret_video['data'][0]['record_id'];
	}
	return $video_precedente;
} // leggi_video_precedente

/**
 * @param  int $video_id 
 * @return int $video_seguente | $video_id 
 */
function leggi_video_seguente(int $video_id) : int {
	$dbh   = new DatabaseHandler();
	$vid_h = new Video($dbh);

	$ret_video = $vid_h->get_video_from_id($video_id);
	if (isset($ret_video['error'])){
		$ret = '<p>' . __FILE__ .' '. __FUNCTION__
		. '<br>Si è verificato un errore nella lettura di ' . Video::nome_tabella
		. '<br>' . $ret_video['message']
		. '<br>id: ' . $video_id . '</p>';
		echo $ret;
		exit(1);
	}
	if ($ret_video['numero']==0){
		$ret = '<p>' . __FILE__ .' '. __FUNCTION__
		. '<br>Si è verificato un errore nella lettura di ' . Video::nome_tabella
		. '<br>id: ' . $video_id . '</p>';
		echo $ret;
		exit(1);
	}
	$video = $ret_video['data'][0];
	$video_seguente= $video['record_id'];
	//
	// cerca quello seguente
	$campi=[];
	$campi['query'] = 'SELECT * FROM ' . Video::nome_tabella
	. 'WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. 'AND record_id_in_album = :record_id_in_album '
	. 'AND titolo_video > :titolo_video '
	. ' ORDER BY titolo_video, record_id ';
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
	$campi['record_id_in_album']      = $video['record_id_in_album'];
	$campi['titolo_video']            = $video['titolo_video'];
	$ret_video=[];
	$ret_video = $vid_h->leggi($campi);

	if (isset($ret_video['numero']) && $ret_video['numero'] > 0){
		$video_seguente= $ret_video['data'][0]['record_id'];
	}
	return $video_seguente;
} // leggi_video_seguente



/**
 * carica video da album
 * 
 * 1. verifica album_id i tabella album 
 * 2. prende dei dati dell'album da tabella scansioni 
 * 3. cerca video dentro la stessa cartella da tabella scansioni 
 * 4. se ce ne sono loop di aggiunta in tabella video 
 * 
 */
function carica_video_da_album( int $album_id) : array{
	$dbh    = New DatabaseHandler();
	$alb_h  = New Album($dbh);
	$scan_h = new ScansioniDisco($dbh);
	$vid_h  = new Video($dbh);

	// verifica album_id 
	// echo '<br>albm_id: ' . $album_id; 
	$alb_h->set_record_id($album_id);
	$campi['query'] = ' SELECT * FROM ' . Album::nome_tabella 
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. ' AND record_id = :record_id';
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
	$campi['record_id']               = $alb_h->get_record_id();
	$ret_alb = $alb_h->leggi($campi);
	if (isset($ret_alb['error'])){
		$ret = '<p>' . __FILE__ .' '. __FUNCTION__
		. '<br>Si è verificato un errore nella lettura di ' . Album::nome_tabella
		. '<br>' . $ret_alb['message']
		. '<br>Campi: ' . serialize($campi) . '</p>';
		echo $ret;
		exit(1);
	}
	if ($ret_alb['numero']==0){
		$ret = '<p>' . __FUNCTION__
		. '<br>Si è verificato un errore nella lettura di ' . Album::nome_tabella
		. '<br>Campi: ' . serialize($campi) . '</p>';
		echo $ret;
		exit(1);
	}
	$album    = $ret_alb['data'][0];
	// echo '<br>album: ' . serialize($album); 
	//
	// album in scansioni_disco 
	$scansioni_disco_id = $album['record_id_in_scansioni_disco'];
	$scan_h->set_record_id($scansioni_disco_id);
	$campi=[];
	$campi['query'] = ' SELECT * FROM ' . ScansioniDisco::nome_tabella 
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. ' AND record_id = :record_id';
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
	$campi['record_id']               = $scan_h->get_record_id();
	$ret_scan = $scan_h->leggi($campi);
	if (isset($ret_scan['error'])){
		$ret = '<p>' . __FILE__ .' '. __FUNCTION__
		. '<br>Si è verificato un errore nella lettura di ' . ScansioniDisco::nome_tabella
		. '<br>' . $ret_scan['message']
		. '<br>Campi: ' . serialize($campi) . '</p>';
		echo $ret;
		exit(1);
	}
	if ($ret_scan['numero']==0){
		$ret = '<p>' . __FILE__ .' '. __FUNCTION__
		. '<br>Si è verificato un errore nella lettura di ' . ScansioniDisco::nome_tabella
		. '<br>Campi: ' . serialize($campi) . '</p>';
		echo $ret;
		exit(1);
	}
	$album_in_scansioni = $ret_scan['data'][0];
	// echo '<br>scansioni_disco: ' . serialize($album_in_scansioni); 
	// 
	// video in scansioni_disco 
	// estensioni 
	$estensioni_video = "( 'mp4', 'm4v', 'mov', 'mkv' )";
	// 
	// lettura in scansioni disco 
	$campi=[];
	$campi['query']= 'SELECT * FROM ' . ScansioniDisco::nome_tabella
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. ' AND livello1 = :livello1    AND livello2 = :livello2 '
	. ' AND livello3 = :livello3    AND livello4 = :livello4 '
	. ' AND livello5 = :livello5    AND livello6 = :livello6 '
	. " AND nome_file <> '/' "
	. " AND estensione IN " . $estensioni_video
	. ' ORDER BY record_id ';
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
	$campi['livello1'] = $album_in_scansioni['livello1'];
	$campi['livello2'] = $album_in_scansioni['livello2'];
	$campi['livello3'] = $album_in_scansioni['livello3'];
	$campi['livello4'] = $album_in_scansioni['livello4'];
	$campi['livello5'] = $album_in_scansioni['livello5'];
	$campi['livello6'] = $album_in_scansioni['livello6'];
	$ret_scan=[];
	$ret_scan=$scan_h->leggi($campi);
	if (isset($ret_scan['error'])){
		$ret = '<p>' . __FUNCTION__
		. '<br>Si è verificato un errore nella lettura di ' . ScansioniDisco::nome_tabella
		. '<br>' . $ret_scan['message']
		. '<br>Campi: ' . serialize($campi) . '</p>';
		echo $ret;
		exit(1);
	}
	if ($ret_scan['numero']==0){
		$ret = [
			'ok' => true,
			'message' => "Non si sono trovati file video per l'album in scansioni_disco. "
			. ' campi: ' . serialize($campi) 
		];
		return $ret;
	}
	// 
	// vai di loop 
	$video_trovati=$ret_scan['data'];
	// echo '<hr><br>record_trovati: ' , serialize($video_trovati);
	$new_video=[];
	$new_video['disco']              = $album_in_scansioni['disco'];
	$new_video['record_id_in_album'] = $album_id;
	if ($album['percorso_completo'][0] != '/'){
		$album['percorso_completo']='/'.$album['percorso_completo'];
		$album['percorso_completo'] = str_ireplace('/./', '/', $album['percorso_completo']);
		$album['percorso_completo'] = str_ireplace('//', '/', $album['percorso_completo']);
	}
	$ret=[];
	$ret['data']   = [];
	$ret['numero'] = 0;
	$ret['ok']     = true;
	for ($i=0; $i < count($video_trovati); $i++) { 
		$new_video['titolo_video'] = $video_trovati[$i]['nome_file'];
		$new_video['percorso_completo'] = $album['percorso_completo'] . $video_trovati[$i]['nome_file'];
		$new_video['record_id_in_scansioni_disco'] = $video_trovati[$i]['record_id'];
		$ret_video = $vid_h->aggiungi($new_video);
		if (isset($ret_video['error'])){
			$ret = '<p>' . __FUNCTION__
			. '<br>Si è verificato un errore nella scrittura di ' . Video::nome_tabella
			. '<br>' . $ret_video['message']
			. '<br>Campi: ' . serialize($new_video) . '</p>';
			echo $ret;
			exit(1);

		} else {
			$ret['numero']++;
			$ret['data'][]=$ret['numero'] .' '.$new_video['titolo_video'];
		}
	} // for 
	return $ret;
} // carica_video_da_album()


/** TEST 
 * 
 * album 31 
 * https://archivio.athesis77.it/aa-controller/video-controller.php?id=31&test=carica_video_da_album
 * 
 */
	if (isset($_GET['test']) && 
	    isset($_GET['id'])   && 
			$_GET['test']== 'carica_video_da_album') {
		carica_video_da_album($_GET['id']);
		exit(0);
	}
//


/**
 * CREATE aggiungi dettaglio video 
 */
function carico_dettaglio_video(int $video_id, string $chiave, string $valore)  : array {
	$dbh    = new DatabaseHandler();
	$vdet_h = new VideoDettagli($dbh); 
	$consultatore_id = (isset($_COOKIE['consultatore_id'])) ? $_COOKIE['consultatore_id'] : 0;
	global $aggiunti; 

	$vdet_h->set_record_id_padre($video_id);
	$vdet_h->set_chiave($chiave);
	$vdet_h->set_valore($valore);
	$vdet_h->set_consultatore_id($consultatore_id);
	$create = 'INSERT INTO ' . VideoDettagli::nome_tabella
	. ' ( record_id_padre,  chiave,  valore,  consultatore_id ) VALUES '
	. ' (:record_id_padre, :chiave, :valore, :consultatore_id ) ';
	if (!$dbh->inTransaction()) { $dbh->beginTransaction(); }
	try{
		$aggiungi = $dbh->prepare($create);
		$aggiungi->bindValue('record_id_padre',  $video_id, PDO::PARAM_INT);
		$aggiungi->bindValue('chiave',           $chiave);
		$aggiungi->bindValue('valore',           $valore);
		$aggiungi->bindValue('consultatore_id',  $consultatore_id, PDO::PARAM_INT);
		$aggiungi->execute();
		$record_id = $dbh->lastInsertID();
		$dbh->commit();

	} catch(\Throwable $th ){
		$dbh->rollBack(); 
		$ret = [
			'record_id' => 0,
			'error'     => true,
			'message' => '<br>'. __CLASS__ . ' ' . __FUNCTION__ 
			. '<br>' . $th->getMessage() 
			. '<br>Campi: id:' . $video_id .' c:'. $chiave .' v:'. $valore
			. '<br>Istruzione SQL: ' . $create 
		];
		echo var_dump($ret);
		return $ret;      
	} // try catch 
	$ret = [
		'ok'        => true, 
		'record_id' => $record_id,
		'message'   => __CLASS__ . ' ' . __FUNCTION__ 
		. ' Inserimento record effettuato, nuovo id: ' 
		. $record_id 
	];
	return $ret;
} // carico_dettaglio_video

/**
 * Caricamento dettagli album da file 
 * - dati ricavati dall'interno del file (tipo EXIF)
 * 
 * - dati ricavati dal nome del file (se segue certi criteri)
 * 
 */
function carica_dettagli_video_da_video(int $video_id){
	$dbh    = new DatabaseHandler();
	$vid_h  = new Video($dbh);
	$vdet_h = new VideoDettagli($dbh);
	$aggiunti=[];

	//
	if ($video_id == 0){
		// cerca il primo "da fare"
		$campi=[];
		$campi['query']= 'SELECT * FROM ' . Video::nome_tabella
		. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
		. ' AND stato_lavori = :stato_lavori '
		. ' LIMIT 1'; 
		$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
		$campi['stato_lavori']            = Video::stato_da_fare;

	} else {
		// cerca il video passato 
		$vid_h->set_record_id($video_id);
		$campi=[];
		$campi['query']= 'SELECT * FROM ' . Video::nome_tabella
		. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
		. ' AND record_id = :record_id ';
		$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
		$campi['record_id']               = $video_id;

	}
	$ret_video = $vid_h->leggi($campi);
	if (isset($ret_video['error'])){
		$ret = '<h3>ERRORE</h3>'
		. '<p>Non è stato possibile rintracciare il video'
		. '<br>' . $ret_video['message']
		. '<br>Campi: ' . serialize($campi);
		http_response_code(404);
		echo $ret;
		exit(1);
	}
	if ($video_id==0 && $ret_video['numero'] == 0){
		$ret = '<p>Non sono stati rintracciati video da lavorare. Fine.</p>';
		echo $ret;
		exit(0);
	}
	if ($ret_video['numero'] == 0){
		$ret = '<h3>ERRORE</h3>'
		. '<p>Non è stato possibile rintracciare il video ' . $video_id 
		. '<br>Campi: ' . serialize($campi) . '</p>';
		http_response_code(404);
		echo $ret;
		exit(1);
	}
	$video = $ret_video['data'][0];
	$video_id = $video['record_id'];
	$album_id = $video['record_id_in_album'];
	$video_file = str_ireplace('//', '/', ABSPATH.$video['percorso_completo']);
	if (!is_file($video_file)){
		$ret = '<h3>ERRORE</h3>'
		. '<p>Non è stato possibile rintracciare il file del video'
		. '<br>file: ' . $video_file;
		http_response_code(404);
		echo $ret;
		exit(1);
	}
	
	$ret_stato = $vid_h->set_stato_lavori_in_video($video_id, Video::stato_in_corso);
	if (isset($ret_stato['error'])){
		$ret = '<h2>Errore</h2>'
		. '<p>Non è stato possibile cambiare stato_lavori al video ['. $video_id .']</p>'
		. '<p>Per: ' . $ret_stato['message'];
		echo $ret;
		exit(1);
	}

	// get estensione da nome_file 
	$ultimo_punto     = strrpos($video_file, '.');
	$video_estensione = substr($video_file, ($ultimo_punto + 1));
	$video_estensione = strtolower($video_estensione);
	
	/*
	*   -carica_dettagli_da_quicktime .mov .m4v .mp4 
	*   -carica_dettagli_da_matroska  .mkv 
	*   -carica_dettagli_da_ffmpeg 
	*/
 
	if (in_array($video_estensione, ['mp4', 'mov'])){
		// formato QuickTime 
	}
	
	// basati su nome_file
	$ultima_barra = strrpos($video_file, '/');
	$nome_file    = substr($video_file, ($ultima_barra + 1) );
	// aaaa mm gg luogo manifestazione-soggetto durata sigla_autore.ext
	$nome_file    = str_ireplace('.'.$video_estensione, '', $nome_file);
	// aaaa mm gg luogo manifestazione-soggetto durata sigla_autore
	
	// chiave: data/evento 
	$data_evento = get_data_evento($nome_file);
	if ($data_evento > ''){
		carico_dettaglio_video($video_id, 'data/evento', $data_evento);
		// sfilo
		$nome_file = str_replace($data_evento, '', $nome_file);
		if (str_contains($data_evento, ' DP')){
			$data_evento = str_replace(' DP', '', $data_evento);
			$nome_file = str_replace($data_evento, '', $nome_file);
		}
		if (str_contains($data_evento, '-')){
			$data_evento = str_replace('-', ' ', $data_evento);
			$nome_file = str_replace($data_evento, '', $nome_file);
		}
		$nome_file = trim($nome_file);
	} // data/evento 
	
	// aaaa mm gg luogo manifestazione-soggetto durata sigla_autore
	//            luogo manifestazione-soggetto durata sigla_autore
	// luogo/comune 
	$luogo = get_luogo($nome_file);
	if ($luogo > ''){
		echo '<br>Luogo:'.$luogo;
		$ret_det   = carico_dettaglio_video( $video_id, 'luogo/comune', $luogo);
		// sfilo 
		$nome_file = str_replace($luogo, '', $nome_file);
		$nome_file = trim($nome_file);
	} // luogo/comune 

	//            luogo manifestazione-soggetto durata sigla_autore
	//                  manifestazione-soggetto durata sigla_autore
	// codice/autore/athesis
	$sigla_autore = get_autore_sigla_6($nome_file);
	if ($sigla_autore>''){
		$ret_det   = carico_dettaglio_video( $video_id, 'codice/autore/athesis', $sigla_autore);
		// sfilo 
		$nome_file = str_replace($sigla_autore, '', $nome_file);
		$nome_file = trim($nome_file);
	} // codice/autore/sigla 
	//                  manifestazione-soggetto durata sigla_autore
	//                  manifestazione-soggetto durata  
	
	// dimensione/durata 
	$durata = get_durata($nome_file);
	echo '<br>durata:'. $durata;
	if ($durata > ''){
		$ret_det   = carico_dettaglio_video( $video_id, 'dimensione/durata', $durata);
		// sfilo 
		$nome_file = str_replace($durata, '', $nome_file);
		$nome_file = trim($nome_file);
	}	// dimensione/durata 

	// quello che resta 
	// nome/manifestazione-soggetto
	$ret_det = carico_dettaglio_video( $video_id, 'nome/manifestazione-soggetto', $nome_file);

	// cambio stato al record 
	$ret_stato = $vid_h->set_stato_lavori_in_video($video_id, Video::stato_completati);
	if (isset($ret_stato['error'])){
		$ret = '<h2>Errore</h2>'
		. '<p>Non è stato possibile cambiare stato_lavori al video ['. $video_id .']</p>'
		. '<p>Per: ' . $ret_stato['message'];
		echo $ret;
		exit(1);
	}
	echo '<p>Caricamento completato</p>';
	exit(0);
} // carica_dettagli_video_da_video()

/** TEST
 * 
 * https://archivio.athesis77.it/aa-controller/video-controller.php?id=14&test=carica_dettagli_video_da_video
 */
	if (isset($_GET['test']) && 
	    isset($_GET['id'])   && 
			$_GET['test']=='carica_dettagli_video_da_video'){
		carica_dettagli_video_da_video($_GET['id']);
		exit(0);
	}
//