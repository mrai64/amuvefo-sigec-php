<?php
/**
 * ALBUM controller
 * ALBUMDETTAGLI controller
 * Si occupa delle funzioni che riguardano la tabella album
 * e album_dettagli e fotografie e video e richieste
 * 
 * . get_item_foto_griglia
 * . get_carousel_foto
 * . get_item_video_griglia
 * . get_item_dettagli
 *   forniscono gli elementi da inserire in miniatura dentro
 *   la pagina di vista album 
 * 
 * . leggi_album_per_id
 *   carica, completa ed espone 
 *   la pagina di vista album
 * 
 * . carica_album_da_scansioni_disco
 *   carica in tabella album solo il record album 
 *   partendo dal record in scansioni_disco, 
 *   per caricare fotografie e per caricare video
 *   poi ci sono altre funzioni
 * 
 * . carica_richiesta_album_per_id
 * TODO cambiare in . carica_richiesta_album
 *   aggiunge una richiesta nella tabella richieste
 * 
 * . modifica_titolo_album
 *   espone il modulo per la modifica del titolo album
 *   sostituisce il titolo nella scheda dell'album
 * 
 * . carica_album_dettagli 
 *   occhio: non è aggiungi_dettaglio_album, carica_album_dettaglio  
 *   Aggiunge, ma se presente aggiorna, un record di album_dettagli 
 * 
 * . cancella_album_dettagli
 *   occhio: non è cancella_album_dettagli 
 *   esegue la cancellazione non fisica del dettaglio
 * 
 * . aggiungi_dettagli_album_da_album
 *   Suona strano? Sì, però 
 *   album_dettagli è una tabella e album è un'altra tabella, 
 *   in italiano si scrive carica dettaglio dell'album dall'album
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
include_once(ABSPATH . 'aa-model/vocabolario-oop.php');
include_once(ABSPATH . 'aa-model/fotografie-oop.php');
include_once(ABSPATH . 'aa-model/video-oop.php');
include_once(ABSPATH . 'aa-model/scansioni-disco-oop.php');
include_once(ABSPATH . 'aa-model/richieste-oop.php');
include_once(ABSPATH . 'aa-model/chiavi-oop.php');
include_once(ABSPATH . 'aa-model/didascalie-oop.php');

include_once(ABSPATH . 'aa-controller/controller-base.php');
include_once(ABSPATH . 'aa-controller/carica-dettaglio-libreria.php');
include_once(ABSPATH . 'aa-controller/fotografie-controller.php');
include_once(ABSPATH . 'aa-controller/video-controller.php');

/**
 * Elemento fotografia da esporre in griglia nell'album
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
 * 
 * TODO Sostituire il tag img con figure e inserire come caption il titolo?
 */
 function get_item_foto_griglia(array $fotografia) : string {
	$ret  = '<div class="float-start">'."\n";
	$ret .= '<a href="'.URLBASE.'fotografie.php/leggi/'.$fotografia['record_id'].'" ';
	$ret .=    'title="'.$fotografia['titolo_fotografia'].'" >'."\n";	
	
	$fotografia_src  = str_ireplace('//' , '/' , ABSPATH.$fotografia['percorso_completo']);
	// se si espone direttamente 
	// $fotografia_src  = $fotografia['percorso_completo'];

	$fotografia_src = html_entity_decode($fotografia_src); // per gli ' nel nome file &amp:039; > &039;
	$fotografia_src = html_entity_decode($fotografia_src); // per gli ' nel nome file &039; > '

	// ai primi tempi si caricava il file originale per poi sostituirlo con la miniatura jpg
	// jpg abbinato al psd, quando c'è
	// jpg abbinato al tif, quando c'è
	$fotografia_jpg  = str_ireplace('.psd', '.jpg', $fotografia_src);
	$fotografia_jpg  = str_ireplace('.tif', '.jpg', $fotografia_jpg);
	if (is_file(ABSPATH.$fotografia_jpg)) {
		$fotografia_src=$fotografia_jpg;
	}
	
	// l'immagine viene "intarsiata" nella pagina per dissuadere lo scarico
	$fotografia_src  = 'data:image/jpeg;base64,'.base64_encode(file_get_contents($fotografia_src));
	$ret .= '<img src="'.$fotografia_src.'" ';
	// se si espone direttamente la foto jpg 
	// $ret .= '<img src="'.URLBASE.$fotografia['percorso_completo'].'" ';
	$ret .=       'style="min-width:200px; min-height:200px; max-width:200px; max-height:200px;" ';
	$ret .=       'loading="lazy"  class="d-block w-100" />'."\n";
	$ret .= '</a>'."\n";
	$ret .= '</div>'."\n";
	return $ret;
} // get_item_foto_griglia()

/**
 * Elemento fotografia da esporre in carosello
 */
function get_carousel_foto(array $fotografia) : string{
	//$fotografia_src  = str_ireplace('//' , '/' , URLBASE.$fotografia['percorso_completo']);
	$fotografia_src = $fotografia['percorso_completo'];
	$fotografia_src = html_entity_decode($fotografia_src); // per gli ' nel nome file &amp;039; > &039;
	$fotografia_src = html_entity_decode($fotografia_src); // per gli ' nel nome file &039; > '
	// jpg abbinato al psd, quando c'è
	// jpg abbinato al tif, quando c'è
	$fotografia_jpg  = str_ireplace('.psd', '.jpg', $fotografia_src);
	$fotografia_jpg  = str_ireplace('.tif', '.jpg', $fotografia_jpg);
	if (is_file($fotografia_jpg)) {
		$fotografia_src=$fotografia_jpg;
	}

	$ret = "\n".'    <div class="carousel-item active">'
	     . "\n".'      <img src="'.URLBASE.$fotografia_src.'" class="d-block w-100" '
			       .'alt="'.$fotografia['titolo_fotografia'].'">'
			 . "\n".'    </div>';
	return $ret;
} // get_carousel_foto

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
	if (get_set_abilitazione() > SOLALETTURA ){
		$ret .= "\t\t".'<td>'
		. '<a href="'.URLBASE.'album.php/modifica-dettaglio/'.$dettaglio['record_id'].'" '
		. 'title="modifica dettaglio"><i class="h2 bi bi-pencil-square"></i></a>'
		. '<a href="'.URLBASE.'album.php/elimina-dettaglio/'.$dettaglio['record_id'].'" '
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
 * Prepara in piedipagina della view un CAROUSEL per bootstrap
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
	$dida_h = New Didascalie($dbh);

	//dbg echo '<p style="font-family:monospace;">' . __FUNCTION__
	//dbg . '<br />input album_id: '.$album_id.' </p>';

	// 1. lettura album_id in album
	// 2. lettura album_id "su di 1 livello" in album
	// 3. lettura scansioni_disco in scansioni_disco
	// 4. lettura fotografie 
	// 4.2. Composizione carousel 
	// 5. Lettura video 
	// 6. Lettura dettagli 
	// 7. Didascalia

	// 1. verifica album_id e lettura dati album
	$campi = [];
	$campi['query'] = 'SELECT * FROM ' . Album::nome_tabella
	. ' WHERE record_id = :record_id '
	. ' AND record_cancellabile_dal = :record_cancellabile_dal ';
	$campi['record_id'] = $album_id;
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
	$ret_album = $alb_h->leggi($campi);
	//dbg echo '<p style="font-family:monospace;max-width:90rem;">Lettura album: <br />'
	//dbg . str_replace(';', '; ', serialize($ret_album)).'</p>';

	if ( isset($ret_album['error']) || $ret_album['numero'] == 0 ){
		http_response_code(404);
		echo '<p style="font-family:monospace">Lettura album ko: fine </p>';
		exit(1);
	} 	
	
	$album = $ret_album['data'][0]; // $album['record_id'], $album['titolo_album'] ecc.
	$siete_in = str_replace('/' , ' / ', $album['percorso_completo']);

	if ( get_set_abilitazione() > SOLALETTURA ){
		$richieste_originali = URLBASE . 'album.php/richiesta/'. $album['record_id'];
		$aggiungi_dettaglio  = URLBASE . 'album.php/aggiungi-dettaglio/'.$album['record_id'];
	} else {
		$richieste_originali = '#sololettura';
		$aggiungi_dettaglio = '#sololettura';
	}
	
	// compone il link per andare all'album superiore
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
		$campi['query'] = 'SELECT * FROM ' . Album::nome_tabella
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
	// se è rimasto museo.php faccio un ulteriore tentativo sulla tabella
	// scansioni_disco
	if ($torna_su === URLBASE.'museo.php' || $torna_su === ""){
		$return_to_scansione_id = $scan_h->get_record_id_da_percorso( $percorso_quasi_completo );
		if ($return_to_scansione_id > 0){
			$torna_su = URLBASE.'deposito.php/leggi/'.$return_to_scansione_id;
		}else {
			$torna_su = URLBASE.'museo.php'; // dopo ingresso
		}
	}
	
	// si vanno a leggere fotografie e video presenti in album
	$float_foto='';
	/**
	 * Carousel - in bootstrap serve realizzare una serie di elementi
	 * div class carousel-item di cui almeno uno active
	 * racchiusi da un paio di div
	 * div class carousel slide carousel-fade
	 *   div class carousel inner
	 *     (lista di carousel-item)
	 *   /div
	 *   bottone precedente
	 *   bottone seguente
	 * /div
	 */

	$campi=[];
	$campi['query'] = 'SELECT * FROM ' . Fotografie::nome_tabella
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. ' AND record_id_in_album = :record_id_in_album '
	. ' ORDER BY titolo_fotografia, record_id ';
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
	$campi['record_id_in_album']      = $album['record_id'];
	//dbg echo '<p style="font-family:monospace">Ricerca fotografie >in: <br />'
	//dbg . str_replace(';', '; ', serialize($campi)).'</p>';

	$ret_foto = $foto_h->leggi($campi);
	//dbg echo '<p style="font-family:monospace">Ricerca fotografie out<: <br />'
	//dbg . str_replace(';', '; ', serialize($ret_foto)).'</p>';

	$float_foto='';
	$carousel_foto = '<div id="carouselAlbum" class="carousel slide carousel-fade" '
	                .' data-bs-ride="true"  data-bs-interval="10000" >'
	          . "\n".'  <div class="carousel-inner">';
	$carousel_active = ' active';
	if ( isset($ret_foto['numero']) && $ret_foto['numero'] > 0 ){
		$foto = $ret_foto['data']; // è sempre un array
		//dbg echo var_dump($foto);
		for ($i=0; $i < count($foto) ; $i++) { 
			$float_foto .= get_item_foto_griglia($foto[$i]);
			$carousel_item= get_carousel_foto($foto[$i]);
			$carousel_item = str_ireplace(' active', $carousel_active, $carousel_item);
			$carousel_active = '';
			$carousel_foto .= $carousel_item;
		}
		//dbg echo var_dump($float_foto);
	}
	// chiusura carosello
	$carousel_foto .= "\n".'  </div>' // carousel-inner
	. "\n".'  <button class="carousel-control-prev" type="button" data-bs-target="#carouselAlbum" data-bs-slide="prev">'
	. "\n".'    <span class="carousel-control-prev-icon" aria-hidden="true"></span>'
	. "\n".'    <span class="visually-hidden">Precedente</span>'
	. "\n".'  </button>'
	. "\n".'  <button class="carousel-control-next" type="button" data-bs-target="#carouselAlbum" data-bs-slide="next">'
	. "\n".'    <span class="carousel-control-next-icon" aria-hidden="true"></span>'
	. "\n".'    <span class="visually-hidden">Seguente</span>'
	. "\n".'  </button>'
	. "\n".'</div>';  // carouselAlbum

	
	// 5. lettura video 
	$float_video='';
	$campi=[];
	$campi['query'] = 'SELECT * FROM ' . Video::nome_tabella
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

	
	// 6. lettura dettagli album
	$table_dettagli='<tr><td colspan="3">Nessun dettaglio caricato</td></tr>';
	$campi=[];
	$campi['query'] = 'SELECT * FROM ' . AlbumDettagli::nome_tabella
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

	$torna_sala = $torna_su;
	/**
	 * 7. didascalia
	 * Verifica se è presente una didascalie e la espone
	 */
	$didascalia_id=0;
	$leggimi="";

	$leggimi_file = ABSPATH . $album['percorso_completo'].'_leggimi.txt';
	// verifica se esiste il file e se lo trova lo inserisce in tabella didascalie
	$ret_dida = $dida_h->recupera_didascalia($leggimi_file);
	if (isset($ret_dida['ok'])){
		$campi=[];
		$campi['tabella_padre']  = 'album';
		$campi['record_id_padre']= $album['record_id'];
		$campi['didascalia']     = $ret_dida['data'][0]['didascalia'];
		$ret_ins_dida = $dida_h->aggiungi($campi);
		if (isset($ret_ins_dida['error'])){
			echo '<p style="font-family:monospace;color: red;">'
			. "Non è riuscito l'inserimento della didascalia "
			. '<br />ret: '   . str_ireplace(';', '; ', serialize($ret_ins_dida))
			. '<br />campi: ' . str_ireplace(';', '; ', serialize($campi))
			. '</p>';
			exit(1);
		}
		// inserito in didascalie
		$didascalia_id = $ret_ins_dida['record_id'];
		$leggimi       = $ret_dida['data'][0]['didascalia'];
		// inserito in didascalie, si elimina il file sidecar txt
		if (!$dida_h->elimina_file_didascalia($leggimi_file)){
			// didascalia non cancellata, perché?
			echo '<p style="font-family:monospace;color: red;">'
			. 'Non è riuscita la cancellazione del file contenente la didascalia '
			. '<br />Verifica file: ' . $leggimi_file
			. '</p>';
			exit(1);
		}
	} // file leggimi trovato e inserito in didascalie
	$ret_dida=[];
	// Si cerca se c'è nella tabella didascalie
	if ($didascalia_id == 0){
		$campi=[];
		$campi['tabella_padre']          = 'album';
		$campi['record_id_padre']        = $album['record_id'];
		$campi['record_cancellabile_dal']= $dbh->get_datetime_forever();
		$campi['query'] = "SELECT * FROM " . Didascalie::nome_tabella
		. " WHERE record_cancellabile_dal = :record_cancellabile_dal "
		. " AND tabella_padre = :tabella_padre "
		. " AND record_id_padre = :record_id_padre "
		. " ORDER BY record_id DESC "; // nel caso fossero almeno 2 prendo l'ultimo
		$ret_dida = $dida_h->leggi($campi);
		if (isset($ret_dida['error'])){
			echo '<p style="font-family:monospace;color: red;">'
			. 'Non è riuscita la lettura della didascalia '
			. '<br />campi: ' . str_ireplace(';', '; ', serialize($ret_dida))
			. '</p>';
			exit(1);
		}
		if ($ret_dida['numero'] > 0){
			$didascalia=$ret_dida['data'][0];
			$didascalia_id = $didascalia['record_id'];
			$leggimi       = $didascalia['didascalia'];
		}
	} // lettura didascalia_id e leggimi dalla tabella didascalie

	// tutto pronto si passa ad esporre
	include_once(ABSPATH.'aa-view/album-view.php');
	exit(0);
} // leggi_album_per_id



/**
 * Carica album in album leggendo scansioni_disco 
 * Viene richiamata all'interno della funzione carica_album_dettagli_foto_video
 * che espone a video i risultati. A sua volta può esporre a video i progressi 
 * del suo compito ma deve tornare un array uguale a quelli base 
 * degli accessi al database.
 * 
 * Va a inserire SOLO l'album partendo da un record di scansioni_disco
 * se viene passato id zero si prende il primo che trova
 * con le caratteristiche della cartella (no file) 
 * - i dettagli dell'album vengono caricati da carica_album_dettagli_foto_video()
 * - le foto e i video nell'album vengono caricati da carica_album_dettagli_foto_video()
 * 
 * @param  int   $scansioni_id
 * @return array $ret 'ok' + 'record_id' | 'error' + 'message'
 */
function carica_album_da_scansioni_disco( int $scansioni_id) : array {
	$dbh    = New DatabaseHandler();
	$scan_h = New ScansioniDisco($dbh);
	$alb_h  = New Album($dbh);

	/**
	 * 1. va a prendere il primo non lavorato o il record di scansioni_disco in input
	 * 2. si vede se il record c'è in scansioni_disco 
	 * 3. cambio in scansioni_disco lo stato_lavori (da ... > in corso )
	 * 4. verifico se in album c'è già un album che fa riferimento a questo 
	 * 5. Se c'è, quello torna come fosse stato inserito 
	 * 6. Se manca vado a inserirlo solo se ...
	 */

	// si possono usare le classi bootstrap
	echo '<p class="fs-2 text-monospace"><strong>'. __FUNCTION__ .'</strong></p>'."\n";

	// 1. va a prendere "il primo" o quello passato in input 
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
		$campi['query'] = 'SELECT * FROM ' . ScansioniDisco::nome_tabella
		. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
		. ' AND stato_lavori = :stato_lavori '
		. " AND nome_file = '/' "
		. " AND estensione = '' "
		. ' AND record_id = :record_id ';
		$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
		$campi['stato_lavori'] = ScansioniDisco::stato_da_fare;
		$scan_h->set_record_id($scansioni_id);
		$campi['record_id'] = $scan_h->get_record_id();
	} // va a prendere "il primo" o quello passato in input 

	// 2. si vede se c'è in scansioni disco
	echo '<p class="text-monospace">Ricerca >in: <br />'
	. str_ireplace(';', '; ', serialize($campi)).'</p>';

	$ret_scan = $scan_h->leggi($campi);

	echo '<p class="text-monospace">Ricerca out<: <br />'
	. str_ireplace(';', '; ', serialize($ret_scan)).'</p>';

	if ( isset($ret_scan['error'])){
		$ret = [
			'error' => true,
			'message' => __FUNCTION__ . ' ' . __LINE__
			. " Non è stato trovato in scansioni_disco il record " . $scansioni_id
			. '<br />' . $ret_scan['message']
			. '<br />' . str_replace(';', '; ', serialize($campi))
		];
		echo '<br />Errore: '.$ret['message']. '<br />STOP';
		return $ret;
	}
	if ($ret_scan['numero'] == 0 && $scansioni_id == 0){
		// L'operazione è inserire album, se non ci sono album da inserire è 
		// considerato cmq un errore
		$ret = [
			'ok' => true,
			'message' => 'Non ci sono record da elaborare.'
			. '<br />' . str_replace(';', '; ', serialize($campi))
		];
		echo '<br />Non sono stati trovati record da elaborare<br />STOP';
		return $ret;
	} // non trovato 
	if ($ret_scan['numero'] == 0){
		// L'operazione è inserire album, se non ci sono album da inserire è 
		// considerato cmq un errore
		$ret = [
			'error' => true,
			'message' => "Non è stato trovato in scansioni_disco il record " 
			. $scansioni_id . ', ecco. '
			. '<br />' . str_replace(';', '; ', serialize($campi))
		];
		echo '<br />Errore: '.$ret['message']. '<br />STOP';
		return $ret;
	} // non trovato 

	// trovato, avanti coi lavori 
	$futuro_album = $ret_scan['data'][0];
	echo '<p style="font-family:monospace">Futuro Album: <br />'
	. str_replace(';', '; ', serialize($futuro_album)).'</p>';

	// 3. In scansioni_disco cambio stato_lavori
	$scansioni_id = $futuro_album['record_id'];
	$ret_stato = $scan_h->set_stato_lavori_in_scansioni_disco($scansioni_id, ScansioniDisco::stato_in_corso);
	if (isset($ret_stato['error'])){
		$ret = [
			'error' => true,
			'message' => "Non è stato aggiornato in scansioni_disco lo stato_lavori per il record " 
			. $scansioni_id . '<br />'
			. $ret_stato['message']
		];
		return $ret;
	} // errore in aggiornamento di stato_lavori in scansioni_disco 
	echo '<p style="font-family:monospace">Aggiornamento stato lavori in scansioni_disco: <br />'
	. str_ireplace(';', '; ', serialize($ret_stato)).'</p>';

	// 4. Verifica se sia già presente un album in album che 
	// fa riferimento a questa scheda di scansioni_disco 
	// Se c'è, salta inserimento e ritorna il record gà presente  
	$campi=[];
	$campi['query'] = 'SELECT * FROM ' .Album::nome_tabella
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. ' AND record_id_in_scansioni_disco = :record_id_in_scansioni_disco ';
	$campi['record_cancellabile_dal']      = $dbh->get_datetime_forever();
	$campi['record_id_in_scansioni_disco'] = $scansioni_id;
	$ret_alb=[]; 
	$ret_alb = $alb_h->leggi($campi);
	if (isset($ret_alb['error'])){
		// cambio cmq stato_lavori
		$ret_stato=[];
		$ret_stato = $scan_h->set_stato_lavori_in_scansioni_disco($scansioni_id, ScansioniDisco::stato_completati);
		$ret = [
			'error' => true,
			'message' => "È successo qualcosa di inatteso nella ricerca "
			. "di un album (se ci fosse) che fa riferimento al record " 
			. $scansioni_id . ' della tabella deposito.<br />'
			. "L'errore è: " . $ret_alb['message']
		];
		echo '<br />'.$ret['message'].'<br />STOP';
		return $ret;
	} // errore in ricerca di album

	// Trovato, che aggiungo? Fuori con il primo album trovato
	if ($ret_alb['numero'] > 0){
		$ret_stato=[];
		$ret_stato = $scan_h->set_stato_lavori_in_scansioni_disco($scansioni_id, ScansioniDisco::stato_completati);
		$ret = [
			"ok"=> true, 
			"record_id" => $ret_alb['data'][0]['record_id'],
			"message" => Album::class . ' ' . __FUNCTION__ 
			. " Inserimento record effettuato, nuovo id: " 
			. $ret_alb['data'][0]['record_id']
		];
		return $ret;
	}

	// 6. se manca vado a inserire se ci sono record all'interno dell'album
	// 6.1. composizione query sql che va integrata man mano che si trovano
	//      livelli in uso alla cartella
	$campi=[];
	$campi['query'] = 'SELECT * FROM ' . ScansioniDisco::nome_tabella
	. " WHERE estensione > '' "
	. ' AND disco = :disco       AND livello1 = :livello1 ';
	$campi['disco']     = $futuro_album['disco'];
	$campi['livello1']  = $futuro_album['livello1'];
	if ($futuro_album['livello2'] == ''){
		$campi['query'] .= " AND livello2 = '' ";
	} else {
		$campi['query'] .= ' AND livello2 = :livello2 ';
		$campi['livello2'] = $futuro_album['livello2'];
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
	echo '<p style="font-family:monospace">Lettura Scansioni disco: <br />'
	. str_replace(';', '; ', serialize($campi)).'</p>';

	$ret_check = $scan_h->leggi($campi);

	echo '<p style="font-family:monospace">Lettura Scansioni disco: <br />'
	. str_replace(';', '; ', serialize($ret_check)).'</p>';
	
	if (isset($ret_check['error'])){
		// aggiorna stato - ci prova comunque 
		$ret_stato = $scan_h->set_stato_lavori_in_scansioni_disco($scansioni_id, ScansioniDisco::stato_completati);

		$ret = [
			'error' => true,
			'message' => __FUNCTION__ . ' ' . __LINE__
			. " La lettura del contenuto in scansioni_disco ha prodotto un errore " 
			. '<br />Errore: ' . $ret_stato['message']
			. '<br />' . str_replace(';', '; ', serialize($campi))
		];
		echo '<br />Errore: '.$ret['message']. '<br />STOP';
		return $ret;
	}
	if ($ret_check['numero']== 0){
		// aggiorna stato - ci prova comunque 
		$ret_stato = $scan_h->set_stato_lavori_in_scansioni_disco($scansioni_id, ScansioniDisco::stato_completati);
		echo  "Il record $scansioni_id in scansioni_disco non contiene materiali " 
			. '<br />ret_check: ' . str_ireplace(';', '; ', serialize($ret_check))
			. '<br />PASSO AL PROSSIMO';
		// ricarica 5 secondi
		echo '<script src="'.URLBASE.'aa-view/reload-5sec-jquery.js"></script>';
		exit(0);
	}
	
	//
	// inserimento album 
	// composizione record scansioni_disco > album 
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

	$ret_alb = $alb_h->aggiungi($album);
	echo '<p style="font-family:monospace">Inserimento album: <br />'
	. str_replace(';', '; ', serialize($ret_alb)).'</p>';
	// cambio cmq stato_lavori
	$ret_stato=[];
	$ret_stato = $scan_h->set_stato_lavori_in_scansioni_disco($scansioni_id, ScansioniDisco::stato_completati);

	if (isset($ret_alb['error'])){
		echo '<br />Errore: '.$ret_alb['message']. '<br />STOP';
		return $ret_alb;
	} 

	// 7. finale 

	// per caricare le fotografie
	// fotografie-controller/ carica_fotografie_da_album

	// per caricare i video
	// video-controller / carica_video_da_album

	echo '<p class="text-monospace">Aggiornato stato scheda scansioni_disco </p>';
	echo '<p class="fs-2 text-monospace">'. __FUNCTION__ . '</p>';
	echo '<p>fine</p>';
	// ricarica 5 secondi
	echo '<script src="'.URLBASE.'aa-view/reload-5sec-jquery.js"></script>';

	return $ret_alb;

} // carica_album_da_scansioni_disco


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

	if (get_set_abilitazione() <= SOLALETTURA ){
		http_response_code(404);
		echo '<pre style="color: red;"><strong>Operazione non consentita</strong></pre>'."\n";
		exit(1);
	}

	$alb_h->set_record_id($album_id);
	$campi=[];
	$campi['query'] = 'SELECT * FROM ' . Album::nome_tabella
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. ' AND record_id = :record_id ';
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
	$campi['record_id']               = $alb_h->get_record_id();
	$ret_alb = $alb_h->leggi($campi);
	if (isset($ret_alb['error'])){
		http_response_code(404);
		echo '<pre style="color: red;"><strong>'
		. 'Inserimento album non riuscito</strong></pre>'
		. '<p>'.$ret_alb['message'].'</p>'
		. '<p>Campi: '.serialize($campi).'</p>'
		. "\n";
		exit(1);
	}
	if ($ret_alb['numero'] == 0 ){
		http_response_code(404);
		echo '<pre style="color: red;"><strong>'
		. 'album non trovato</strong></pre>'
		. "\n";
		exit(1);
	}
	$album = $ret_alb['data'][0];

	// inserimento richiesta
	$campi=[];
	$campi['record_id_richiedente'] = $_COOKIE['id_calendario'];
	$campi['oggetto_richiesta']     = 'album';
	$campi['record_id_richiesta']   = $album_id;
	$ret_ric = $ric_h->aggiungi($campi);
	if (isset($ret_ric['error'])){
		http_response_code(404);
		echo '<pre style="color: red;"><strong>'
		. 'Inserimento album non riuscito</strong></pre>'
		. '<p>'.$ret_ric['message'].'</p>'
		. "\n";
		exit(1);
	}

	$_SESSION['messaggio'] = 'Richiesta di accesso alta risoluzione inoltrata';
} // carica_richiesta_album_per_id



/**
 * Aggiunta dei dettagli dell'album - generico
 * aggiunta dei dettagli delle fotografie vedi fotografie-controller
 * aggiunta dei dettagli dei video vedi video-controller 
 * 
 * @param    int $album_id
 * @param string $chiave 
 * @param string $valore 
 * @param    int $consultatore_id
 * @return array 'ok' | 'error' + 'message'
 */
function carica_album_dettagli( int $album_id, string $chiave, string $valore, int $consultatore_id = 0) : array {
	$dbh    = New DatabaseHandler();
	$chi_h  = New Chiavi($dbh);
	$alb_dh = New AlbumDettagli($dbh);
	// 1. verifica di chiave in elenco chiavi 
	// 2. se tipo chiave ripetibile, si aggiunge e via 
	// 3. se tipo chiave unico si cerca se c'è già una chiave
	// 4. se c'è ma non cambia il valore 
	//    fuori senza far niente
	// 5. c'è e cambia il valore 
	//    si registra "il vecchio" come cancellato
	//    e si aggiorna il record con 

	// 1. verifica di chiave in elenco chiavi 
	$campi=[];
		$campi['query'] = 'SELECT unico FROM ' . Chiavi::nome_tabella
		. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
		. ' AND chiave = :chiave ';
		$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
		$chi_h->set_chiave($chiave); // sanificazione e check 
		$campi['chiave'] = $chi_h->get_chiave();
		$ret_chi = $chi_h->leggi($campi);
		if (isset($ret_chi['error'])){
			$ret = [
				'error'   => true,
				'message' => __FUNCTION__ . ' Errore in '
				. 'ricerca chiave: ' . $chiave 
				. '<br />' . $ret_chi['message']
				. '<br />' . str_ireplace(';', '; ', serialize($ret_chi)) 
			];
			return $ret; // non propago $ret_chi paro paro 
		}
		if ($ret_chi['numero'] < 1){
			$ret = [
				'error'   => true,
				'message' => __FUNCTION__ . ' Errore in '
				. 'ricerca chiave: ' . $chiave 
				. '<br />Non trovato.'
				. '<br />' . str_ireplace(';', '; ', serialize($ret_chi)) 
			];
			return $ret; // non propago $ret_chi paro paro 
		}
	$chiave_unico = $ret_chi['data'][0]['unico'];
	// 2. se tipo chiave ripetibile, si aggiunge e via 
	if ($chiave_unico == Chiavi::chiave_ripetibile){
		$alb_dh->set_record_id_padre($album_id);
		$alb_dh->set_chiave($chiave);
		$alb_dh->set_valore($valore);
		if ($consultatore_id == 0 && isset($_COOKIE['consultatore_id'])){
			$consultatore_id = $_COOKIE['consultatore_id'];
		}
		$alb_dh->set_consultatore_id($consultatore_id);
		$campi=[];
		$campi['record_id_padre'] = $alb_dh->get_record_id_padre();
		$campi['chiave']          = $alb_dh->get_chiave();
		$campi['valore']          = $alb_dh->get_valore();
		$campi['consultatore_id'] = $alb_dh->get_consultatore_id();
		$ret_ins = [];
		$ret_ins = $alb_dh->aggiungi($campi);
		if (isset($ret_ins['error'])){
			$ret = [
				'error'   => true,
				'message' => __FUNCTION__ . ' Errore in '
				. 'inserimento dettaglio: ' . $chiave 
				. '<br />' . str_ireplace(';', '; ', serialize($ret_ins)) 
			];
			return $ret; // non propago $ret_chi paro paro 
		}
		// andato bene
		return $ret_ins;
	}
	// 3. tipo chiave unico: si cerca se c'è già una chiave
	$campi=[];
	$campi['query'] = 'SELECT * FROM ' . AlbumDettagli::nome_tabella
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. ' AND chiave = :chiave '
	. ' AND record_id_padre = :record_id_padre '
	. ' ORDER BY record_id ';
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever(); 
	$campi['chiave']                  = $chiave; 
	$campi['record_id_padre']         = $album_id;
	$ret_det = $alb_dh->leggi($campi);
	if (isset($ret_det['error'])){
		$ret = [
			'error'   => true,
			'message' => __FUNCTION__ . ' Errore in '
			. 'ricerca chiave: ' . $chiave 
			. '<br />' . $ret_det['message']
			. '<br />' . str_ireplace(';', '; ', serialize($ret_det)) 
		];
		return $ret; // non propago $ret_chi paro paro 
	}
	// 4.a. manca, si inserisce e via
	if ($ret_det['numero'] < 1 ){
		$alb_dh->set_record_id_padre($album_id);
		$alb_dh->set_chiave($chiave);
		$alb_dh->set_valore($valore);
		$alb_dh->set_consultatore_id($consultatore_id);
		$campi=[];
		$campi['record_id_padre'] = $alb_dh->get_record_id_padre();
		$campi['chiave']          = $alb_dh->get_chiave();
		$campi['valore']          = $alb_dh->get_valore();
		$campi['consultatore_id'] = $alb_dh->get_consultatore_id();
		$ret_ins = [];
		$ret_ins = $alb_dh->aggiungi($campi);
		if (isset($ret_ins['error'])){
			$ret = [
				'error'   => true,
				'message' => __FUNCTION__ . ' Errore in '
				. 'inserimento dettaglio: ' . $chiave 
				. '<br />' . str_ireplace(';', '; ', serialize($ret_ins)) 
			];
			return $ret; // non propago $ret_chi paro paro 
		}
		// andato bene
		return $ret_ins;
	}
	// 4.b. se c'è ma non cambia il valore 
	//    fuori senza far niente
	$dettaglio_vecchio = $ret_det['data'][0];
	if ($dettaglio_vecchio['valore'] == $valore){
		$ret = [
			'ok' => true,
			'record_id' => $dettaglio_vecchio['record_id'],
			'message' => "Dettaglio già presente identico e "
			. "non aggiornato."
		];
		return $ret;
	}
	// 5. c'è e cambia il valore 
	//    si registra "il vecchio" come cancellato (ma ha un nuovo record_id)
	//    e si aggiorna il record trovato (mantiene il suo vecchio record_id)
	$campi = [];
	$campi['record_id_padre'] = $dettaglio_vecchio['record_id_padre'];
	$campi['chiave']          = $dettaglio_vecchio['chiave'];
	$campi['valore']          = $dettaglio_vecchio['valore'];
	$campi['consultatore_id'] = $dettaglio_vecchio['consultatore_id'];
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_now();
	$ret_del = $alb_dh->aggiungi($campi);
	if (isset($ret_del['error'])){
		$ret = [
			'error'   => true,
			'message' => __FUNCTION__ . ' Errore in '
			. 'aggiornamento dettaglio: ' . $chiave 
			. '<br />' . $ret_del['message']
			. '<br />' . str_ireplace(';', '; ', serialize($ret_del)) 
		];
		return $ret; // non propago $ret_chi paro paro 
	}
	// e si aggiorna il record con l valore nuovo 
	$campi = [];
	$campi['update'] = 'UPDATE ' . AlbumDettagli::nome_tabella
	. ' SET valore = :valore '
	. ' WHERE record_id = :record_id ';
	$campi['valore']    = $dettaglio_vecchio['valore'];
	$campi['record_id'] = $dettaglio_vecchio['record_id'];
	$ret_upd=[];
	$ret_upd = $alb_dh->modifica($campi);
	if (isset($ret_del['error'])){
		$ret = [
			'error'   => true,
			'message' => __FUNCTION__ . ' Errore in '
			. 'aggiornamento dettaglio: ' . $chiave 
			. '<br />' . $ret_upd['message']
			. '<br />' . str_ireplace(';', '; ', serialize($ret_upd)) 
		];
		return $ret; // non propago $ret_chi paro paro 
	}
	$ret = [
		'ok' => true,
		'record_id' => $dettaglio_vecchio['record_id'],
		'message' => "Dettaglio già presente, e "
		. "aggiornato."
	];
	return $ret;
} // carica_album_dettagli()

/**
 * DELETE
 * Cancella il record e torna alla vista album 
 * 
 * @param  int  $dettaglio_id
 * @return void
 */
function cancella_album_dettagli( int $dettaglio_id){
	$dbh    = New DatabaseHandler();
	$adet_h = New AlbumDettagli($dbh);
	// 1. verifica presenza record 
	// 2. imposta record_cancellabile_dal 
	
	$campi=[];
	$campi['query'] = 'SELECT * FROM ' . AlbumDettagli::nome_tabella
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. ' AND record_id = :record_id ';
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
	$campi['record_id'] = $dettaglio_id;
	$ret_det = $adet_h->leggi($campi);

	if (isset($ret_det['error'])){
		$ret = [
			'error' => true, 
			'message' => __FILE__ . ' ' . __FUNCTION__
			. ' Non è stato possibile modificare il dettaglio '
			. ' per ' . $ret_det['message']
		];
		echo '<br />Errore: ' . $ret['message'] . '<br />STOP';
		exit(0);
	}
	if ( $ret_det['numero'] < 1){
		$ret = [
			'error' => true, 
			'message' => __FILE__ . ' ' . __FUNCTION__
			. ' Non è stato possibile modificare il dettaglio ' . $dettaglio_id
			. ' per Non trovato '
		];
		echo '<br />Errore: ' . $ret['message'] . '<br />STOP';
		exit(0);
	}
	$dettaglio = $ret_det['data'][0];

	// resta il consultatore_id originario
	$campi=[];
	$campi['update'] = 'UPDATE ' . AlbumDettagli::nome_tabella
	. ' SET record_cancellabile_dal = :record_cancellabile_dal  '
	. ' WHERE record_id = :record_id ';
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_now();
	$campi['record_id']               = $dettaglio_id;
	$ret_mod = $adet_h->modifica($campi);
	if ( isset($ret_mod['error'])){
		$ret = [
			'error' => true, 
			'message' => __FILE__ . ' ' . __FUNCTION__
			. ' Non è stato possibile cancellare il dettaglio '
			. ' per ' . $ret_mod['message']
		];
		echo '<br />Errore: ' . $ret['message'] . '<br />STOP';
		exit(0);
	}

	// torniamo alla scheda dell'album 
	leggi_album_per_id($dettaglio['record_id_padre']);
	exit(0);
} // cancella_album_dettagli()



/** 
 * Quello che è stato caricato in scansioni_disco diventa:
 * - album
 * - fotografie dell'album
 * - video dell'album
 * Non ritorna dati ma mostra il progresso del lavoro fino alla conclusione
 * 
 * @param  int  scansioni_id scansioni_disco
 * @return void Espone codice a video 
 * 
 * Legge scansioni_disco record_id
 * Scrive album
 * Scrive album_dettagli
 * Scrive fotografie
 * scrive video
 */
function carica_album_dettagli_foto_video(int $scansioni_id){
	$dbh    = New DatabaseHandler();
	$alb_h  = New Album($dbh);

	// 1. carica album da scansioni_disco 
	// 2. aggiunge album_dettagli 
	// 3. aggiunge fotografie 
	// 4. aggiunge video 	

	/**
	 * Esposizione lavori in corso 
	 */
	$titolo_pagina = 'Caricamento Album da deposito | Album';
	$inizio_pagina = file_get_contents(ABSPATH.'aa-view/reload-5sec-view.php');
	$inizio_pagina = str_ireplace('<?=$titolo_pagina; ?>', $titolo_pagina, $inizio_pagina);
	echo $inizio_pagina;
	// si possono usare le classi bootstrap
	echo '<p class="fs-2 text-monospace"><strong>'.$titolo_pagina.'</strong></p>'."\n";

	// 1. legge scansioni_disco e carica album
	$ret_a = carica_album_da_scansioni_disco($scansioni_id);
	// può tornare messaggi di errore ma gestibili 
	if (isset($ret_a['message'])){

		if (str_contains($ret_a['message'], 'non contiene materiali')){
			// cartella vuota, niente di che
			http_response_code(404);
			echo '<h2 style="font-family:monospace;">avviso</h2>'
			. '<p style="font-family:monospace;">FINE '
			.$ret_a['message'].'</p>'."\n";
			exit(1);
		}

		if (str_contains($ret_a['message'], 'Non ci sono record')){
			// cartella vuota, niente di che
			http_response_code(404);
			echo '<h2 style="font-family:monospace;">avviso</h2>'
			. '<p style="font-family:monospace;">FINE '
			. $ret_a['message'] . "</p>\n";
			exit(1);
		}
	} // ret_a message

	// ritorna senza assegnare un codice interno ?
	if (!isset($ret_a['record_id'])){
			echo '<p style="font-family:monospace;color:red;">' .  __FUNCTION__
			. '<br />' . str_ireplace(';', '; ', serialize($ret_a)) . '</p>'."\n";
			exit(1);
	} // ret_a ma senza record_id

	$album_id=$ret_a['record_id'];
	echo '<p class="fs-3 text-monospace"> '. __FUNCTION__ .'</p>'
	. '<p class="text-monospace">Caricato album_id: ' . $album_id 
	. '<br />Passo ai dettagli</p>'."\n";
	
	// cambio stato dell'album '...' > 'in corso' 
	$ret_cambio_stato = [];
	$ret_cambio_stato = $alb_h->set_stato_lavori_album($album_id, Album::stato_in_corso);
	echo '<p class="text-monospace">'
	. 'Aggiornato stato_lavori per album_id: '.$album_id 
	. '<br>ret_cambio_stato : ' . str_ireplace(';', '; ', serialize($ret_cambio_stato)).'</p>';

	// 2. Aggiunta dettagli album da album
	echo '<p class="text-monospace">Carica dettagli da album </p>'."\n";
	$ret_a=[];
	$ret_a = aggiungi_dettagli_album_da_album($album_id);
	echo '<p class="text-monospace">' . str_ireplace(';', '; ', serialize($ret_a)) . '</p>';
	
	if (isset($ret_a['error'])){
		echo '<div class="alert alert-danger" role="alert">'."\n"
		. '<p>Inserimento dettagli per album non riuscito.' 
		. '<br />'. $ret_a['message'] . '</p>'
		. '</div>'. "\n";
		exit(1);
	}

	// carica fotografie dell'album da scansioni_disco
	// fotografie-controller
	echo '<p class="text-monospace;">Carica foto da album '.$album_id.'</p>'."\n";
	$ret_f = carica_fotografie_da_album($album_id);
	//  cartella senza immagini - possono anche esserci 0 fotografie
	if (isset($ret_f['ok']) && $ret_f['numero'] > 0 ){
		echo '<p class="text-monospace">Sono state caricate o aggiornate le fotografie: '
		. "\n".'<ol>';
		for ($i=0; $i < count($ret_f['data']) ; $i++) { 
			echo "\n".'<li>'.str_ireplace(';', '; ', serialize($ret_f['data'][$i])).'</li>';
		}
		echo "\n".'</ol>';
	}
	if (isset($ret_f['message']) && str_contains($ret_f['message'], 'Non ci sono')){
		echo '<div class="alert alert-danger" role="alert">'."\n"
		. '<p>Inserimento foto per album non effettuato.' 
		. '<br />'. $ret_f['message'] . '</p>'
		. '</div>'. "\n";
	
	} elseif (isset($ret_f['error'])){
		echo '<div class="alert alert-danger" role="alert">'."\n"
		. '<p>Inserimento foto per album non riuscito.' 
		. '<br />'. $ret_f['message'] 
		. '<br />'. str_ireplace(';', '; ', serialize($ret_f)) . '</p>'
		. '</div>'. "\n";
		exit(1);

	} // carica_fotografie_da_album
	echo '<p style="font-family:monospace;color: red;">Carica foto da album - fine</p>'."\n";

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

	// ricarica 5 secondi
	echo '<script src="'.URLBASE.'aa-view/reload-5sec-jquery.js"></script>';
	exit(0);
} // carica_album_dettagli_foto_video()

/**
 * Dai dati dell'album carica album_dettagli 
 * richiamando carica_album_dettagli() 
 * Espone il progresso lavoro con echo 
 * 
 * @param  int $album_id    record_id della tabella album
 * @return array elenco dei dettagli caricati | [] 
 * 
 */
function aggiungi_dettagli_album_da_album( int $album_id ) : array {
	$dbh    = New DatabaseHandler();
	$alb_dh = New AlbumDettagli($dbh);
	$aggiunti = [];

	// dal titolo_album vengono estratti dettagli e 
	// quello che non viene scorporato diventa il dettaglio 
	// nome/soggetto 
	echo '<p style="font-family:monospace">' . __FUNCTION__ ;
	$titolo_album = get_titolo_album($album_id);

	if ($titolo_album == ''){
		$ret = [
			'error' => true, 
			'message' => __FILE__ . ' ' . __FUNCTION__
			. ' Non è stato possibile leggere in album il titolo per record_id: ' . $album_id
		];
		echo '<br />Errore: ' . $ret['message'] . '<br />STOP';
		return $ret;
	}

	echo '<br />album_id: ' . $album_id . ' titolo: ' . $titolo_album;
	echo "<br />Si passa a inserire dettagli per l'album";
	$consultatore_id = $_COOKIE['consultatore_id'];
	
	// data/evento ma se non è 0000
	echo "<br />data/evento - inizio ";
	$data_evento = get_data_evento($titolo_album);
	echo "<br />data/evento: trovata [$data_evento]";
	if ($data_evento > '' && !str_contains($data_evento, '0000-')){
		$ret_aggiungi=[];
		$ret_aggiungi = carica_album_dettagli($album_id, 'data/evento', $data_evento, $consultatore_id);

		// sfilo
		if (str_contains($data_evento, ' DP')){
			$data_evento = str_replace(' DP', '', $data_evento);
			$titolo_album = str_replace($data_evento, '', $titolo_album);
		}
		if (str_contains($data_evento, '-')){
			$data_evento = str_replace('-', ' ', $data_evento);
			$titolo_album = str_replace($data_evento, '', $titolo_album);
		}
		$titolo_album = str_replace($data_evento, '', $titolo_album);
		$titolo_album = trim($titolo_album);
	} // data-evento 
	echo '<br />data/evento - fine ';
	
	// luogo/area-geografica
	echo '<br />luogo/località - inizio ';
	$luogo = get_luogo_localita($titolo_album);
	if ($luogo > ''){
		$ret_aggiungi=[];
		$ret_aggiungi = carica_album_dettagli($album_id, 'luogo/localita', $luogo, $consultatore_id);
		if (isset($ret_aggiungi['ok'])){
			$aggiunti[] = 'luogo/località: '. $luogo;
		}
		// sfilo
		$titolo_album = str_ireplace($luogo, '', $titolo_album);
		$titolo_album = trim($titolo_album);
	}
	echo '<br />luogo/località - fine ';
	
	// luogo/comune
	echo '<br />luogo/comune - inizio ';
	$luogo = get_luogo_comune($titolo_album);
	if ($luogo > ''){
		$ret_aggiungi=[];
		$ret_aggiungi = carica_album_dettagli($album_id, 'luogo/comune', $luogo, $consultatore_id);
		if (isset($ret_aggiungi['ok'])){
			$aggiunti[] = 'luogo/comune: '. $luogo;
		}
		// sfilo
		$titolo_album = str_ireplace($luogo, '', $titolo_album);
		$titolo_album = trim($titolo_album);
	}
	echo '<br />luogo/comune - fine ';
	
	echo '<br />codice/autore/athesis - inizio ';
	$sigla_autore = get_autore_sigla_6($titolo_album);
	if ($sigla_autore > ''){
		$ret_aggiungi=[];
		$ret_aggiungi = carica_album_dettagli($album_id, 'codice/autore/athesis', $sigla_autore, $consultatore_id);
		if (isset($ret_aggiungi['ok'])){
			$aggiunti[] = 'codice/autore/athesis: '. $sigla_autore;
		}
		// sfilo
		$titolo_album = str_replace($sigla_autore, '', $titolo_album);
		$titolo_album = trim($titolo_album);
	}
	echo '<br />codice/autore/athesis - fine ';

	// e alla fine avanza qualcosa? 
	echo '<br />nome/soggetto - inizio ';
	if ($titolo_album > ''){
		$ret_aggiungi=[];
		$ret_aggiungi = carica_album_dettagli($album_id, 'nome/manifestazione-soggetto', $titolo_album, $consultatore_id);
		if (isset($ret_aggiungi['ok'])){
			$aggiunti[] = 'nome/manifestazione-soggetto: '. $titolo_album;
		}
	}
	echo '<br />nome/soggetto - fine ';

	$ret = [
	'ok'     => true,
	'numero' => count($aggiunti),
	'data'   => $aggiunti
	];
	echo __FUNCTION__ . ' FINE '
	. '<br />' . str_replace(';', '; ', serialize($ret));
	return $ret;
} // aggiungi_dettagli_album_da_album()


/**
 * Dalla vista Album in elenco dettagli c'è un pulsante  
 * aggiungi dettaglio, che richiama un modulo e ne gestisce i dati
 *  
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
	// 1. verifica album_id
	// 2. mancano i dati - si espone il modulo
	// 3. dati presenti - si aggiunge il dettaglio 

	// 1. verifica album_id
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
		. "<br />Si è verificato un errore nella lettura di " . Album::nome_tabella
		. '<br />' . $ret_album['message']
		. '<br />Campi: ' . serialize($campi) . '</p>';
		echo $ret;
		exit(1);
	}
	if ($ret_album['numero']==0){
		$ret = '<p>' . __FUNCTION__
		. '<br />Nessun album trovato. '
		. '<br />Campi: ' . serialize($campi) 
		. '</p>';
		echo $ret;
		exit(1);
	}

	$album = $ret_album['data'][0];

	// 2. mancano i dati - si espone il modulo
	if (!isset($dati_input['aggiungi_dettaglio'])){
		$_SESSION['messaggio'] = "Aggiungi il dettaglio chiave+valore "
		. "scegliendo la chiave tra quelle "
		. "disponibili, consulta il manuale in caso di dubbi.";
		$record_id = 0; // aggiungi
		$leggi_album = URLBASE.'album.php/leggi/'.$album['record_id'];
		$aggiungi_dettaglio = URLBASE.'album.php/aggiungi-dettaglio/'.$album['record_id'];
		$option_list_chiave = $chi_h->get_chiavi_option_list();
		require_once(ABSPATH.'aa-view/dettaglio-album-aggiungi-view.php');
		exit(0);
	}
	
	// 3. i dati ci sono, si va a inserire
	$album_id = $album['record_id'];
	$chiave = $dati_input['chiave'];
	$valore = $dati_input['valore'];
	$consultatore_id = $_COOKIE['consultatore_id'];
	$ret_aggiungi=[];
	$ret_aggiungi = carica_album_dettagli($album_id, $chiave, $valore, $consultatore_id);
	if (isset($ret_aggiungi['error'])){
		$ret = '<p>' . __FUNCTION__
		. '<br />Si è verificato un errore nella scrittura di ' . AlbumDettagli::nome_tabella
		. '<br />' . $ret_aggiungi['message']
		. '</p>';
		echo $ret;
		exit(1);
	}

	// inserimento effettuato, si va alla pagina dell'album
	leggi_album_per_id($album['record_id']);
	exit(0);
} // aggiungi_dettaglio_album_da_modulo()


/**
 * Dalla vista Album in elenco dettagli c'è un pulsante  
 * modifica dettaglio, che richiama un modulo e ne gestisce i dati
 *  
 * @param  int  $album_id
 * @param array $dati_input quelli del modulo
 * Se mancano espone la mappa
 * Se presenti aggiunge il dettaglio
 * 
 */
function modifica_dettaglio_album_da_modulo(int $dettaglio_id, array $dati_input){
	$dbh   = new DatabaseHandler();
	$chi_h = new Chiavi($dbh);
	$adet_h = new AlbumDettagli($dbh);
	// 1 . verifica dettaglio esistente
	// 2. mancano i dati - si espone il modulo
	// 3. i dati ci sono si aggiorna e si torna alla vista album 

	// 1 . verifica dettaglio esistente
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
		. "<br />Si è verificato un errore nella lettura di " . AlbumDettagli::nome_tabella
		. '<br />' . $ret_det['message']. '</p>';
		echo $ret;
		exit(1);
	}
	if ($ret_det['numero']==0){
		$ret = '<p>' . __FUNCTION__
		. '<br />Nessun dettaglio album trovato. '
		. '</p>';
		echo $ret;
		exit(1);
	}
	$dettaglio          = $ret_det['data'][0];

	$dettaglio_id       = $dettaglio['record_id'];
	$album_id           = $dettaglio['record_id_padre'];

	// 2. mancano i dati - si espone il modulo
	if (!isset($dati_input['valore'])){
		$_SESSION['messaggio'] = "Modifica il dettaglio chiave+valore "
		. "scegliendo la chiave tra quelle "
		. "disponibili, consulta il manuale in caso di dubbi.";
		$leggi_album        = URLBASE.'album.php/leggi/'.$dettaglio['record_id_padre'];
		$aggiorna_dettaglio = URLBASE.'album.php/modifica-dettaglio/'.$dettaglio['record_id'];
		$record_id          = $dettaglio['record_id'];
		$option_list_chiave = $chi_h->get_chiavi_option_list();
		require_once(ABSPATH.'aa-view/dettaglio-album-modifica-view.php');
		exit(0);
	}

	// i dati ci sono e andiamo a modificare il dettaglio
	// cancellazione dettaglio vecchio (resta storia) 
	$adet_h->set_record_id($dettaglio_id);
	$campi['update'] ='UPDATE ' . AlbumDettagli::nome_tabella
	. ' SET record_cancellabile_dal = :record_cancellabile_dal '
	. ' WHERE record_id = :record_id ';
	$campi['record_cancellabile_dal']=$dbh->get_datetime_now();
	$campi['record_id']= $dettaglio_id;
	$ret_upd = $adet_h->modifica($campi);

	if (isset($ret_upd['error'])){
		$ret = '<p>' . __FUNCTION__
		. '<br />Si è verificato un errore nella modifica di ' . AlbumDettagli::nome_tabella
		. '<br />' . $ret_upd['message']. '</p>';
		echo $ret;
		exit(1);
	}
	// la versione attuale gestisce anche l'aggiornamento 
	// album
	$chiave = $dati_input['chiave'];
	$valore = $dati_input['valore'];
	$consultatore_id = $_COOKIE['consultatore_id'];
	$ret_aggiungi=[];
	$ret_aggiungi = carica_album_dettagli($album_id, $chiave, $valore, $consultatore_id);

	// modifica effettuata, si va alla pagina dell'album
	leggi_album_per_id($album_id);
	exit(0);
} // modifica_dettaglio_album_da_modulo()


/**
 * Modifica titolo album 
 * propone il modulo per modificare il titolo dell'album e 
 * se son stati passati i dati del modulo, effettua la registrazione della modifica
 * Nota: a uso registrazione vecchio - nuovo, viene 
 * inserito un record in album ma con lo stato di già cancellato. 
 * 
 * @param   int $album_id 
 * @param array $dati_input (quelli del modulo online)
 * @return void (html page exposed)
 */
function modifica_titolo_album(int $album_id, array $dati_input ){
	$dbh   = new DatabaseHandler();
	$alb_h = new Album($dbh);

	$view  = ABSPATH . 'aa-view/album-titolo-modifica-view.php';
	$leggi_album = 'n.d.'; // campi in uso alla mappa $view 
	$aggiorna_titolo = 'n.d.';
	$titolo_originale = 'n.d.';

	$alb_h->set_record_id($album_id);
	$campi=[];
	$campi['query']= 'SELECT * FROM ' . Album::nome_tabella 
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. ' AND record_id = :record_id ';
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
	$campi['record_id'] = $alb_h->get_record_id();
	$ret_alb = $alb_h->leggi($campi);
	if (isset($ret_alb['error'])){
		$_SESSION['messaggio'] = "Nel reperire l'album si è verificato un problema. "
		. '<br />campi: '. str_ireplace(';', '; ', serialize($campi))
		. '<br />ret: '. str_ireplace(';', '; ', serialize($ret_alb));
		require_once($view);
		exit(1);
	}
	if ($ret_alb['numero'] < 1){
		$_SESSION['messaggio'] = "Nel reperire l'album si è verificato un problema. "
		. '<br />campi: '. str_ireplace(';', '; ', serialize($campi))
		. '<br />Non trovato.';
		require_once($view);
		exit(1);
	}
	$aggiorna_titolo  = URLBASE.'album.php/modifica-titolo/'.$ret_alb['data'][0]['record_id'];
	$leggi_album      = URLBASE.'album.php/leggi/'.$ret_alb['data'][0]['record_id'];
	$titolo_originale = $ret_alb['data'][0]['titolo_album'];
	// dato manca - espone modulo
	if (!isset($dati_input['aggiorna_titolo'])){
		$_SESSION['messaggio'] = "Modificate il titolo dell'album.";
		require_once($view);
		exit(0);
	}

	// Vecchio record registrato a uso backup
	$campi=[];
	$campi['titolo_album']                 = $ret_alb['data'][0]['titolo_album'];
	$campi['disco']                        = $ret_alb['data'][0]['disco'];
	$campi['percorso_completo']            = $ret_alb['data'][0]['percorso_completo'];
	$campi['record_id_in_scansioni_disco'] = $ret_alb['data'][0]['record_id_in_scansioni_disco'];
	$campi['stato_lavori']                 = $ret_alb['data'][0]['stato_lavori'];
	$campi['record_cancellabile_dal']      = $dbh->get_datetime_now();
	$ret_ins = $alb_h->aggiungi($campi);
	if (isset($ret_ins['error'])){
		$_SESSION['messaggio'] = "Nell'aggiornare l'album si è verificato un problema. "
		. '<br />Msg: '. $ret_ins['message']
		. '<br />ret: '. str_ireplace(';', '; ', serialize($ret_ins));
		require_once($view);
		exit(1);
	}
	// registrazione titolo modificato 
	$campi=[];
	$campi['update'] = 'UPDATE ' . Album::nome_tabella 
	. ' SET titolo_album = :titolo_album '
	. ' WHERE record_id = :record_id ';
	$titolo_nuovo = strip_tags($dati_input['titolo']);
	$titolo_nuovo = str_ireplace(';', ';§', $titolo_nuovo);
	$campi['titolo_album'] = $titolo_nuovo;
	$campi['record_id']    = $ret_alb['data'][0]['record_id'];
	$ret_upd = $alb_h->modifica($campi);

	if (isset($ret_upd['error'])){
		$_SESSION['messaggio'] = "Nell'aggiornare l'album si è verificato un problema. "
		. '<br />Msg: '. $ret_upd['message']
		. '<br />ret: '. str_ireplace(';', '; ', serialize($ret_upd));
		require_once($view);
		exit(1);
	}
	$_SESSION['messaggio'] = "Aggiornamento eseguito.";
	require_once($view);
	exit(0);	
} // modifica_titolo_album()