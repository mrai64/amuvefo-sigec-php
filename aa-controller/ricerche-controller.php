<?php 
/**
 * @source /aa-controller/ricerche-controller.php
 * @author Massimo Rainato <maxrainato@libero.it>
 * 
 * RICERCHE controller
 * - nuova_ricerca_semplice
 *   Partendo dai termini di ricerca, estrae n elenchi di elementi 
 *   nei gruppi album, fotografie, video e li memorizza nella tabella 
 *   ricerche 
 * - leggi_ricerca
 *   parte da una ricerca_id ed estrae i primi 3 gruppi 
 *   di risultati pronti per la presentazione della pagina html 
 * - get_album_list 
 *   per la funzione nuova_ricerca_semplice parte dai termini ed esegue le ricerche
 *   nella tabella album, album dettagli, deposito e didascalie
 *   rintracciando un elenco di album_id
 * - get_fotografie_list
 *   per la funzione nuova_ricerca_semplice parte dai termini ed esegue le ricerche 
 *   nella tabella fotografie, fotografie_dettagli, deposito e didascalie
 *   rintracciando un elenco di fotografie_id 
 * - get_video_list
 *   per la funzione nuova_ricerca_semplice parte dai termini 
 *   ed esegue le ricerche nelle tabelle video video_dettagli, 
 *   deposito e didascalie rintracciando un elenco di video_id 
 * - get_blocco_ricerca_avanti
 *   per la gestione della paginazione per produrre il codice html
 *   che mostra "i record dopo" del blocco album, fotografie, video.
 * - get_blocco_ricerca_indietro
 *   per la gestione della paginazione per produrre il codice html
 *   che mostra i record prima del blocco album, fotografie, video 
 * - get_html_album
 *   fornito di un elenco di album_id, predispone il codice html 
 *   per visualizzare i risultati 
 * - get_html_fotografie
 *   fornito di un elenco di fotografie_id, predispone il codice html 
 *   per visualizzare i risultati 
 * - get_html_video 
 *   fornito di un elenco di video_id, predispone il codice html
 *   per visualizzare i risultati
 * 
 */
if (!defined('ABSPATH')){
	include_once('../_config.php');
}
include_once(ABSPATH . 'aa-model/database-handler-oop.php');
include_once(ABSPATH . 'aa-model/ricerche-oop.php');
include_once(ABSPATH . 'aa-model/album-oop.php');
include_once(ABSPATH . 'aa-model/album-dettagli-oop.php');
include_once(ABSPATH . 'aa-model/fotografie-oop.php');
include_once(ABSPATH . 'aa-model/fotografie-dettagli-oop.php');
include_once(ABSPATH . 'aa-model/video-oop.php');
include_once(ABSPATH . 'aa-model/video-dettagli-oop.php');
include_once(ABSPATH . 'aa-model/scansioni-disco-oop.php');
include_once(ABSPATH . 'aa-model/didascalie-oop.php');

/**
 * Nuova Ricerca semplice
 * @param  array $dati_input 
 * @return void  (espone html)
 */
function nuova_ricerca_semplice(array $dati_input) {
	// verifica 
	if (!isset($dati_input['esegui_ricerca'])){
		$ret = '<p>Si è verificato un errore in ' . __FUNCTION__ 
		.  '[1]<br>Non sono stati forniti i dati del modulo.'
		. str_ireplace(';', '; ', serialize($dati_input))
		.  ' </p>';
		echo $ret;
		exit(1);
	}
	if (!isset($dati_input['valore']) || $dati_input['valore'] == ""){
		$ret = '<p>Si è verificato un errore in ' . __FUNCTION__ 
		.  '[2]<br>Non sono stati forniti i dati del modulo.'
		. str_ireplace(';', '; ', serialize($dati_input))
		.  ' </p>';
		echo $ret;
		exit(1);
	}

	// si passa a registrare la ricerca per avere un ricerca_id 
	$dbh     = New DatabaseHandler();
	$rice_h  = New Ricerche($dbh);
	$campi=[];
	$campi['richiesta'] = $dati_input['valore'];
	$ret_ric = $rice_h->aggiungi($campi);
	//dbg 
	if (isset($ret_ric['error'])){
		$ret = '<p>Si è verificato un errore in ' . __FUNCTION__ 
		.  '[3]<br>ret_ric: ' . str_ireplace(';', '; ', serialize($ret_ric))
		.  ' </p>';
		echo $ret;
		exit(1);
	}
	$dati_input['record_id'] = $ret_ric['record_id'];
	$ricerca_id              = $ret_ric['record_id'];
	
	// TODO Queste ricerche si possono spostare portandole in javascript
	$risultato = [];
	$risultato['album']      = get_album_list($dati_input);
	$risultato['fotografie'] = get_fotografie_list($dati_input);
	$risultato['video']      = get_video_list($dati_input);
	
	$campi=[];
	$campi['update'] = 'UPDATE ' . Ricerche::nome_tabella
	. ' SET risultato = :risultato '
	. ' WHERE record_id = :record_id ';
	$campi['record_id'] = $ricerca_id;
	$campi['risultato'] = json_encode($risultato); // mysql mediumtext oltre 65k
	$ret_ric = [];
	$ret_ric = $rice_h->modifica($campi);
	if (isset($ret_ric['error'])){
		$ret = '<p>Si è verificato un errore in ' . __FUNCTION__ 
		.  '[4]<br>ret_ric: ' . str_ireplace(';', '; ', serialize($ret_ric))
		.  ' </p>';
		echo $ret;
		exit(1);
	}
	
	// posso "saltare" alla pagina che fa la mostra
	header("Location: ".URLBASE."ricerche.php/leggi/".$ricerca_id );
	exit(0);

} // nuova_ricerca_semplice()

/**
 * @param  int   $ricerca_id 
 * @return array html pagina di risultati oppure testo debug
 */
function leggi_ricerca(int $ricerca_id) {

	$dbh     = New DatabaseHandler();
	$rice_h  = New Ricerche($dbh);
	$campi=[];
	$campi['query'] = 'SELECT * FROM ' . Ricerche::nome_tabella 
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. ' AND record_id = :record_id  ';
	$campi['record_id'] = $ricerca_id;
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
	$ret_ric = $rice_h->leggi($campi);
	//dbg 
	//dbg $ret = '<p>Sono in ' . __FUNCTION__ 
	//dbg .  '[2]<br>ret_ric: ' . str_ireplace(';', '; ', serialize($ret_ric))
	//dbg .  ' </p>';
	//dbg echo $ret;
	if (isset($ret_ric['error'])){
		$ret = '<p>Si è verificato un errore in ' . __FUNCTION__ 
		.  '[2]<br>ret_ric: ' . str_ireplace(';', '; ', serialize($ret_ric))
		.  ' </p>';
		echo $ret;
		exit(1);
	}
	if (isset($ret_ric['numero']) && $ret_ric['numero'] == 0){
		$ret = '<p>Non è stata trovata la ricerca in ' . __FUNCTION__ 
		.  '[3]<br>Può essere stata rimossa </p>';
		echo $ret;
		exit(1);
	}
	$ricerca = $ret_ric['data'][0];

	// esposizione dei risultati
	// $ricerca_id      = $ricerca['record_id'];
	$termini_ricerca = $ricerca['richiesta'];
	$risultato       = json_decode($ricerca['risultato'], true);
	if (!isset($risultato['album'])){
		$risultato['album']=[];
	}
	if (!isset($risultato['fotografie'])){
		$risultato['fotografie']=[];
	}
	if (!isset($risultato['video'])){
		$risultato['video']=[];
	}
	
	$quanti_album  = 12;
	$album_trovati = count($risultato['album']);
	$quante_foto   = 24;
	$foto_trovate  = count($risultato['fotografie']);
	$quanti_video  = 12;
	$video_trovati = count($risultato['video']);

	$html_album     = '';
	$contatore_album= '<p>Nessun album rintracciato.'
	. '<span id="albumTrovati" class="d-none">0</span>'
	. '<span id="totAlbum"     class="d-none">'.$quanti_album.'</span>'
	. '<span id="albumPrimo"   class="d-none">1</span>'
	. '<span id="albumUltimo"  class="d-none">0</span>'
	. '</p>';
	$indietro_album = '<p><i id="indietroAlbum" '
	. 'class="h2 bi bi-skip-backward-fill text-secondary"></i>'
	. '</p>';	
	$avanti_album = '<p><i id="avantiAlbum" '
	. 'class="h2 bi bi-skip-forward-fill text-secondary"></i>'
	. '</p>';	
	
	$html_fotografie = '';
	$contatore_fotografie= '<p>Nessuna fotografia rintracciata.'
	. '<span id="fotoTrovate" class="d-none">0</span>'
	. '<span id="totFoto" class="d-none">'.$quante_foto.'</span>'
	. '<span id="fotoPrima" class="d-none">1</span>'
	. '<span id="fotoUltima" class="d-none">0</span>'
	. '</p>';
	$avanti_fotografie  = '<p><i id="avantiFoto" '
	. 'class="h2 bi bi-skip-forward-fill text-secondary"></i>'	
	. '</p>';	
	$indietro_fotografie= '<p><i id="indietroFoto" '
	. 'class="h2 bi bi-skip-backward-fill text-secondary"></i>'
	. '</p>';	
		
	$html_video    = '';
	$contatore_video    = '<p>Nessun video rintracciato.'
	. '<span id="videoTrovati" class="d-none">0</span>'
	. '<span id="totVideo" class="d-none">'.$quanti_video.'</span>'
	. '<span id="videoPrimo" class="d-none">1</span>'
	. '<span id="videoUltimo" class="d-none">0</span>'
	. '</p>';
	$indietro_video = '<p><i id="indietroVideo" '
	. 'class="h2 bi bi-skip-backward-fill text-secondary"></i>'
	. '</p>';	
	$avanti_video = '<p><i id="avantiVideo" '
	. 'class="h2 bi bi-skip-forward-fill text-secondary"></i>'
	. '</p>';	
	
	// creo la parte html che riporta l'elenco degli album oppure
	if ($album_trovati > 0){
		$alb_h = New Album($dbh);
		$quanti_album = min($quanti_album, $album_trovati);
		$album_list   = array_slice($risultato['album'], 0, $quanti_album);
		
		$contatore_album  = '<p>In totale sono stati rintracciati '
		. '<span id="albumTrovati">'.$album_trovati.'<span> album.'
		. '<span id="totAlbum"     class="d-none">'.$quanti_album.'</span>'
		. '</p>';
		
		$campi = [];
		$query = 'SELECT a.*, s.tinta_rgb '
		. ' FROM ' . Album::nome_tabella . ' a, '
		.   ScansioniDisco::nome_tabella . ' s '
		. ' WHERE a.record_cancellabile_dal = :record_cancellabile_dal '
		. ' AND a.record_id_in_scansioni_disco = s.record_id '
		. " AND a.record_id IN ("
		. implode(', ' , $album_list)
		. ") ORDER BY a.record_id ";
		
		$campi['query']  = $query; 
		$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
		$ret_alb = $alb_h->leggi($campi);
		if (isset($ret_alb['error'])){
			$html_album .= '<p>'
			. "Si è verificato un errore nella funzione " . __FUNCTION__ 
			. " durante il recupero dei primi " . $quanti_album . " album."
			. '<br>campi: ' . str_ireplace(';', '; ', serialize($campi))
			. '<br>ret: ' . str_ireplace(';', '; ', serialize($ret_alb))
			. '<span id="albumPrimo"   class="d-none">0</span>'
			. '<span id="albumUltimo"  class="d-none">0</span>'
				. '</p>';

		} else {
			$html_album .= '<table>';
			$html_album .= '<tbody>';			
			for ($i=0; $i < count($ret_alb['data']); $i++) { 
				$album = $ret_alb['data'][$i];
				$html_album .= "\n".'<tr><td style="height:5rem;">'
				. '<a href="' . URLBASE . 'album.php/leggi/' . $album['record_id'] . '" '
				. 'target="_blank" ><i class="h1 bi bi-archive" style="color:#'.$album['tinta_rgb'].'"></i></a>&nbsp;</td><td>'
				. '<a href="' . URLBASE . 'album.php/leggi/' . $album['record_id'] . '" '
				. 'target="_blank" style="text-decoration:none;color:#'.$album['tinta_rgb'].'">'. $album['titolo_album'] 
				. '</a><br>'
				. '<span class="h6 small text-secondary text-wrap"><em>Siete in: </em>'
				. str_ireplace('/', ' / ', $album['percorso_completo'])
				. '</span></td></tr>';
			}
			$html_album .= "\n".'</tbody>';
			$html_album .= '</table>';
			$html_album .= "\n".'<span id="albumPrimo"  class="d-none">1</span>';
			$html_album .= '<span id="albumUltimo" class="d-none">'.count($ret_alb['data']).'</span>';

		}
	} 
	// creo la parte html che riporta l'elenco degli album

	// creo la parte html che riporta l'elenco delle foto - griglia
	if ($foto_trovate > 0){
		$indietro_fotografie = '<p><i id="indietroFoto" '
		. 'class="h2 bi bi-skip-backward-fill text-secondary"></i>'
		. '</p>';	
		$avanti_fotografie = '<p><i id="avantiFoto" '
		. 'class="h2 bi bi-skip-forward-fill  text-secondary"></i>'
		. '</p>';	

		$foto_h = New Fotografie($dbh);
		$quante_foto = 24;		
		$quante_foto = min($quante_foto, $foto_trovate);
		$foto_list   = array_slice($risultato['fotografie'], 0, $quante_foto);

		$contatore_fotografie  = '<p>In totale sono state rintracciate '
		. "\n". '<span id="fotoTrovate">'.$foto_trovate.'</span> fotografie.'
		. "\n". '<span id="totFoto" class="d-none">'.$quante_foto.'</span>'
		. '</p>';

		$query = ' SELECT f.*, a.titolo_album '
		. ' FROM ' . Fotografie::nome_tabella . ' f, '
		.                 Album::nome_tabella . ' a '
		. ' WHERE f.record_cancellabile_dal = :record_cancellabile_dal '
		. ' AND f.record_id_in_album = a.record_id '
		. ' AND f.record_id in ( '
		. implode(', ', $foto_list)
		. ' ) ORDER BY f.record_id ';
		$campi=[];
		$campi['query'] = $query;
		$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
		$ret_foto = $foto_h->leggi($campi);
		if (isset($ret_alb['error'])){
			$html_fotografie .= '<p>'
			. "Si è verificato un errore nella funzione " . __FUNCTION__ 
			. " durante il recupero delle prime " . $quante_foto . " fotografie."
			. '<br>campi: ' . str_ireplace(';', '; ', serialize($campi))
			. '<br>ret: ' . str_ireplace(';', '; ', serialize($ret_foto))
			. '</p>';

		} else {
			for ($i=0; $i < count($ret_foto['data']); $i++) { 
				$foto = $ret_foto['data'][$i];
				$foto_jpg = str_ireplace(['.jpg', '.psd', '.tif'], '.jpg' , $foto['percorso_completo']);
				$foto_jpg = str_ireplace('&amp;', '&' , $foto_jpg); // d&amp;039;Adige > d&039;Adige
				$foto_jpg = str_ireplace('&039;', "'" , $foto_jpg); // d&039;Adige     > d'Adige

				$html_fotografie .= "\n".'<div class="float-start" style="width:200px;height:200px;">'
				. '<a href="' . URLBASE . 'fotografie.php/leggi/'. $foto['record_id'] .'" '
				. ' target="_blank" title="FOTO [' . $foto['titolo_fotografia']. '] IN ALBUM ['.$foto['titolo_album'].'] ">'
				. '<img style="max-width:200px; max-height:200px;" loading="lazy"  class="d-block w-100" '
				. ' src="' . URLBASE . $foto_jpg . '" />'
				. '</a></div>';
			}
			$html_fotografie .= "\n"
			. '<span id="fotoPrima" class="d-none">1</span>'
			. '<span id="fotoUltima" class="d-none">'.count($ret_foto['data']).'</span>';
		}		
	}
	// creo la parte html che riporta l'elenco delle foto - griglia
		
	// creo la parte html che riporta l'elenco dei video - griglia
	if ($video_trovati > 0) {
		$vid_h  = New Video($dbh);
		$quanti_video = 12; 
		$quanti_video = min($quanti_video, $video_trovati);
		$video_list   = array_slice($risultato['video'], 0, $quanti_video );

		$indietro_video = '<p><i id="indietroVideo" '
		. 'class="h2 bi bi-skip-backward-fill text-secondary"></i>'
		. '<span id="videoPrimo" class="d-none">1</span>'
		.'</p>';	
		$avanti_video = '<p><i id="avantiVideo" '
		. 'class="h2 bi bi-skip-forward-fill text-secondary"></i>'
		. '<span id="videoUltimo" class="d-none">'.$quanti_video.'</span>'
		. '</p>';	
		
		$contatore_video  = '<p>In totale sono stati rintracciati '
		. '<span id="videoTrovati">'.$video_trovati.'</span> video.'
		. '<span id="totVideo" class="d-none">'.$quanti_video.'</span>'
		. '</p>';

		$query = 'SELECT v.*, a.titolo_album '
		. ' FROM ' . Video::nome_tabella . ' v, '
		.          Album::nome_tabella . ' a '
		. ' WHERE v.record_cancellabile_dal = :record_cancellabile_dal '
		. ' AND v.record_id_in_album = a.record_id '
		. ' AND v.record_id in ( '
		. implode(', ', $video_list)
		. ' ) ORDER BY v.record_id ';
		$campi=[];
		$campi['query'] = $query;
		$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
		$ret_vid = $vid_h->leggi($campi);

		if (isset($ret_vid['error'])){
			$html_video .= '<p>'
			. "Si è verificato un errore nella funzione " . __FUNCTION__ 
			. " durante il recupero dei primi " . $quanti_video . " video."
			. '<br>campi: ' . str_ireplace(';', '; ', serialize($campi))
			. '<br>ret: ' . str_ireplace(';', '; ', serialize($ret_vid))
			. '</p>';

		} else {
			for ($i=0; $i < count($ret_vid['data']); $i++) { 
				// recupero video
				$video = $ret_vid['data'][$i];
				// $foto_jpg = str_ireplace(['.jpg', '.psd', '.tif'], '.jpg' , $foto['percorso_completo']);
				$html_video .= "\n".'<div class="float-start" style="width:200px;height:200px;">'
				. '<a href="' . URLBASE . 'video.php/leggi/'. $video['record_id'] .'" '
				. ' target="_blank" title="VIDEO [' . $video['titolo_video']. '] IN ALBUM ['.$video['titolo_album'].'] ">'
				. '<img style="max-width:200px; max-height:200px;" loading="lazy"  class="d-block w-100" '
				. ' src="' . URLBASE . 'aa-img/video-segnalino.png" />'
				. '</a></div>';	
			}
			$html_video .= "\n"
			. '<span id="videoPrimo"  class="d-none">1</span>'
			. '<span id="videoUltimo" class="d-none">'.count($ret_vid['data']).'</span>';

		}
	}
	// creo la parte html che riporta l'elenco dei video - griglia

	// e alla fine mostro
	require_once(ABSPATH.'aa-view/ricerca-risultati-view.php');
	exit(0);

} // leggi_ricerca

/**
 * @param  array $dati_input - i dati del modulo di ricerca
 * @return array [] oppure [album_id, ...]
 */
function get_album_list(array $dati_input) : array {
	// input 
	if (!isset($dati_input['valore'])){
		//dbg 
		$ret = '<p style="font-family:monospace">Errore in ' . __FUNCTION__ 
		. ' mancano i dati in input '
		. '<br>input: ' . str_ireplace(';', '; ', serialize($dati_input));
		echo $ret;
		return [];
	}
	
	$dbh    = New DatabaseHandler();
	$alb_h  = New Album($dbh);
	$adet_h = New AlbumDettagli($dbh);
	$scan_h = New ScansioniDisco($dbh);
	$dida_h = New Didascalie($dbh);
	
	$termini_ricerca = strtolower($dati_input['valore']);
	$termini_ricerca = preg_replace('/[^a-zA-Z0-9\']/u', ' ', $termini_ricerca);
	$termini_ricerca = explode(' ', $termini_ricerca, 5);
	$album_id=[];
	// Album - contiene gli stessi dati di deposito scansioni_disco
	
	// Dettagli Album
	$query = 'SELECT DISTINCT record_id_padre '
	. ' FROM ' . AlbumDettagli::nome_tabella 
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal ';
	foreach ($termini_ricerca as $termine) {
		$termine = addslashes($termine);
		$query .= ' AND record_id_padre in ('
		. ' SELECT record_id_padre FROM ' . AlbumDettagli::nome_tabella
		. " WHERE valore LIKE '%" . $termine . "%'"
		. ') ';
	}
	//$query .= ' ORDER BY record_id_padre ';
	$campi=[];
	$campi['query'] = $query;
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();		
	$ret_adet = $adet_h->leggi($campi);
	if (isset($ret_adet['error'])) {
		//dbg 
		$ret = '<p style="font-family:monospace">Errore in ' . __FUNCTION__ 
		. ' [1] <br>Si è verificato un errore nella ricerca in ' . AlbumDettagli::nome_tabella
		. '<br>campi: ' . str_ireplace(';', '; ', serialize($campi))
		. '<br>input: ' . str_ireplace(';', '; ', serialize($ret_adet));
		echo $ret;
		return[];
	}
	for ($i=0; $i < count($ret_adet['data']); $i++) {
		$album_id[]=$ret_adet['data'][$i]['record_id_padre'];
	}
	
	// deposito scansioni_disco - album
	$query = 'SELECT record_id FROM ' . ScansioniDisco::nome_tabella
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. " AND nome_file = '/' ";
	foreach ($termini_ricerca as $termine) {
		$termine= addslashes($termine); // non per sanificare
		$query .= " AND ( disco  like '%".$termine."%' "
		        . " OR  livello1 like '%".$termine."%' "
		        . " OR  livello2 like '%".$termine."%' "
		        . " OR  livello3 like '%".$termine."%' "
		        . " OR  livello4 like '%".$termine."%' "
		        . " OR  livello5 like '%".$termine."%' "
		        . " OR  livello6 like '%".$termine."%' "
		    . " OR  titolo_album like '%".$termine."%' "
		        . ') ';
	} // foreach
	// $query .= ' ORDER BY record_id ';
	$campi=[];
	$campi['query'] = $query;
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
	$ret_scan = $scan_h->leggi($campi);
	if (isset($ret_scan['error']))	{
		//dbg 
		$ret = '<p style="font-family:monospace">Errore in ' . __FUNCTION__ 
		. ' [2] <br> Si è verificato un errore nella ricerca in ' . ScansioniDisco::nome_tabella
		. '<br>campi: ' . str_ireplace(';', '; ', serialize($campi))
		. '<br>input: ' . str_ireplace(';', '; ', serialize($ret_scan));
		echo $ret;
		return[];
	}
	// da scansioni disco all'elenco album 
	$lista_deposito_id = [];
	for ($i=0; $i < count($ret_scan['data']); $i++) { 
		$lista_deposito_id[]=$ret_scan['data'][$i]['record_id'];
	}
	// non vuoto
	if (count($lista_deposito_id) > 0){
		// strizza e cerca gli album che ci sono
		asort($lista_deposito_id);
		$lista_deposito_id=array_unique($lista_deposito_id);
		$query = 'SELECT record_id FROM ' . Album::nome_tabella 
		. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
		. ' AND record_id_in_scansioni_disco IN ('
		. implode(', ', $lista_deposito_id)
		.') ';
		$campi=[];
		$campi['query'] = $query;
		$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
		$ret_alb = $alb_h->leggi($campi);
		if (isset($ret_alb['error']))	{
			//dbg 
			$ret = '<p style="font-family:monospace">Errore in ' . __FUNCTION__ 
			. ' [3] <br> Si è verificato un errore nella ricerca in ' . Album::nome_tabella
			. '<br>campi: ' . str_ireplace(';', '; ', serialize($campi))
			. '<br>input: ' . str_ireplace(';', '; ', serialize($ret_alb));
			echo $ret;
			return[];
		}
		for ($i=0; $i < count($ret_alb['data']); $i++) {
			$album_id[]=$ret_alb['data'][$i]['record_id'];
		}
	} // lista_deposito_id non vuoto

	// didascalie - fulltext
	$query = 'SELECT record_id_padre FROM ' . Didascalie::nome_tabella
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. ' AND tabella_padre = :tabella_padre '
	. " AND MATCH(didascalia) AGAINST ('" . $dati_input['valore'] . "') ";
	$campi=[];
	$campi['query'] = $query;
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();		
	$campi['tabella_padre'] = Album::nome_tabella;		
	$ret_dida = $dida_h->leggi($campi);
	if (isset($ret_dida['error']))	{
		//dbg 
		$ret = '<p style="font-family:monospace">Errore in ' . __FUNCTION__ 
		. ' [4] <br>Si è verificato un errore nella ricerca in ' . Didascalie::nome_tabella
		. '<br>campi: ' . str_ireplace(';', '; ', serialize($campi))
		. '<br>input: ' . str_ireplace(';', '; ', serialize($ret_dida));
		echo $ret;
		return[];
	}
	for ($i=0; $i < count($ret_dida['data']); $i++) {
		$album_id[] = $ret_dida['data'][$i]['record_id_padre'];
	}

	// alla fine strizza e ordina
	asort($album_id);
	$album_id = array_unique($album_id);
	return array_values($album_id);
} // get_album_list()

/**
 * @param  array $dati_input - i dati del modulo di ricerca
 * @return array [] oppure [fotografia_id, ...]
 */
function get_fotografie_list(array $dati_input = []): array{
	// input 
	if (!isset($dati_input['valore'])){
		//dbg 
		$ret = '<p style="font-family:monospace">Errore in ' . __FUNCTION__ 
		. ' mancano i dati in input '
		. '<br>input: ' . str_ireplace(';', '; ', serialize($dati_input));
		echo $ret;
		return [];
	}
	$termini_ricerca = strtolower($dati_input['valore']);
	$termini_ricerca = preg_replace('/[^a-zA-Z0-9\']/u', ' ', $termini_ricerca);
	$termini_ricerca = explode(' ', $termini_ricerca, 5);
	$fotografie_id=[];

	$dbh    = New DatabaseHandler();
//$alb_h  = New Album($dbh);
	$foto_h = New Fotografie($dbh);
	$fdet_h = New FotografieDettagli($dbh);
	$scan_h = New ScansioniDisco($dbh);
	$dida_h = New Didascalie($dbh);
	
	// Dettagli fotografie 
	$query = 'SELECT DISTINCT record_id_padre '
	. ' FROM ' . FotografieDettagli::nome_tabella 
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal ';
	foreach ($termini_ricerca as $termine) {
		$termine = addslashes($termine);
		$query .= ' AND record_id_padre in ('
		. ' SELECT record_id_padre FROM ' . FotografieDettagli::nome_tabella
		. " WHERE valore LIKE '%" . $termine . "%' "
		. ') ';
	}

	// $query .= ' ORDER BY record_id_padre ';
	$campi=[];
	$campi['query'] = $query;
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();		
	$ret_fdet = $fdet_h->leggi($campi);
	if (isset($ret_fdet['error'])) {
		//dbg 
		$ret = '<p style="font-family:monospace">Errore in ' . __FUNCTION__ 
		. ' Si è verificato un errore nella ricerca in ' . FotografieDettagli::nome_tabella
		. '<br>campi: ' . str_ireplace(';', '; ', serialize($campi))
		. '<br>input: ' . str_ireplace(';', '; ', serialize($ret_fdet));
		echo $ret;
		return[];
	}

	for ($i=0; $i < count($ret_fdet['data']); $i++) {
		$fotografie_id[]=$ret_fdet['data'][$i]['record_id_padre'];
	}
	
	// deposito scansioni_disco - fotografie jpg psd tif
	$query = 'SELECT record_id FROM ' . ScansioniDisco::nome_tabella
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. " AND nome_file <> '/' "
	. " AND estensione in ('jpg', 'psd', 'tif' ) ";
	foreach ($termini_ricerca as $termine) {
		$termine= addslashes($termine); // non per sanificare
		$query .= " AND (disco  like '%".$termine."%' "
		. " OR  livello1 like '%".$termine."%' "
		. " OR  livello2 like '%".$termine."%' "
		. " OR  livello3 like '%".$termine."%' "
		. " OR  livello4 like '%".$termine."%' "
		. " OR  livello5 like '%".$termine."%' "
		. " OR  livello6 like '%".$termine."%' "
		. " OR titolo_fotografia like '%".$termine."%' "
		. " OR nome_file like '%".$termine."%' "
		. ') ';
	} // foreach
	// $query .= ' ORDER BY record_id ';
	$campi=[];
	$campi['query'] = $query;
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
	$ret_scan = $scan_h->leggi($campi);
	if (isset($ret_scan['error']))	{
		//dbg 
		$ret = '<p style="font-family:monospace">Errore in ' . __FUNCTION__ 
		. ' Si è verificato un errore nella ricerca in ' . ScansioniDisco::nome_tabella
		. '<br>campi: ' . str_ireplace(';', '; ', serialize($campi))
		. '<br>input: ' . str_ireplace(';', '; ', serialize($ret_scan));
		echo $ret;
		return[];
	}
	// da scansioni disco all'elenco fotografie 
	$lista_deposito_id = [];
	for ($i=0; $i < count($ret_scan['data']); $i++) { 
		$lista_deposito_id[]=$ret_scan['data'][$i]['record_id'];
	}
	// non vuoto 
	if (count($lista_deposito_id) > 0){
		
		// strizza e cerca le fotografie che ci sono
		asort($lista_deposito_id);
		$lista_deposito_id = array_unique($lista_deposito_id);
		$query = 'SELECT record_id FROM ' . Fotografie::nome_tabella 
		. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
		. ' AND record_id_in_scansioni_disco IN ('
		. implode(', ', $lista_deposito_id)
		.') ';
		$campi=[];
		$campi['query']=$query;
		$campi['record_cancellabile_dal']= $dbh->get_datetime_forever();
		$ret_foto = $foto_h->leggi($campi);
		if (isset($ret_foto['error']))	{
			//dbg 
			$ret = '<p style="font-family:monospace">Errore in ' . __FUNCTION__ 
			. ' Si è verificato un errore nella ricerca in ' . Fotografie::nome_tabella
			. '<br>campi: ' . str_ireplace(';', '; ', serialize($campi))
			. '<br>input: ' . str_ireplace(';', '; ', serialize($ret_foto));
			echo $ret;
			return[];
		}
		for ($i=0; $i < count($ret_foto['data']); $i++) {
			$fotografie_id[]=$ret_foto['data'][$i]['record_id'];
		}
	} // lista_deposito_id non vuoto

	// didascalie 
	$query = 'SELECT record_id_padre FROM ' . Didascalie::nome_tabella
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. ' AND tabella_padre = :tabella_padre '
	. " AND MATCH(didascalia) AGAINST ('" . $dati_input['valore'] . "')";
	$campi=[];
	$campi['query']=$query;
	$campi['record_cancellabile_dal']= $dbh->get_datetime_forever();		
	$campi['tabella_padre']= Fotografie::nome_tabella;		
	$ret_dida = $dida_h->leggi($campi);
	if (isset($ret_dida['error']))	{
		//dbg 
		$ret = '<p style="font-family:monospace">Errore in ' . __FUNCTION__ 
		. ' Si è verificato un errore nella ricerca in ' . Didascalie::nome_tabella
		. '<br>campi: ' . str_ireplace(';', '; ', serialize($campi))
		. '<br>input: ' . str_ireplace(';', '; ', serialize($ret_dida));
		echo $ret;
		return[];
	}
	for ($i=0; $i < count($ret_dida['data']); $i++) {
		$fotografie_id[]=$ret_dida['data'][$i]['record_id_padre'];
	}

	// alla fine strizza e ordina
	asort($fotografie_id);
	$fotografie_id = array_unique($fotografie_id);
	return array_values($fotografie_id);

} // get_fotografie_list()

/**
 * @param  array $dati_input - i dati del modulo di ricerca
 * @return array [] oppure [video_id, ...]
 */
function get_video_list(array $dati_input = []): array{
	// input 
	if (!isset($dati_input['valore'])){
		//dbg 
		$ret = '<p style="font-family:monospace">Errore in ' . __FUNCTION__ 
		. ' mancano i dati in input '
		. '<br>input: ' . str_ireplace(';', '; ', serialize($dati_input));
		echo $ret;
		return [];
	}
	$termini_ricerca = explode(' ', $dati_input['valore'], 5);
	$video_id=[];

	$dbh    = New DatabaseHandler();
//$alb_h  = New Album($dbh);
	$vid_h  = New Video($dbh);
	$vdet_h = New VideoDettagli($dbh);
	$scan_h = New ScansioniDisco($dbh);
	$dida_h = New Didascalie($dbh);
	
	// Dettagli Video 
	$query = 'SELECT DISTINCT record_id_padre '
	. ' FROM ' . VideoDettagli::nome_tabella 
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal ';
	foreach ($termini_ricerca as $termine) {
		$termine = addslashes($termine);
		$query .= ' AND record_id_padre in ('
		. ' SELECT record_id_padre FROM ' . VideoDettagli::nome_tabella
		. " WHERE valore LIKE '%" . $termine . "%'"
		. ') ';
	}
	$query .= ' ORDER BY record_id_padre ';
	$campi=[];
	$campi['query'] = $query;
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();		
	$ret_vdet = $vdet_h->leggi($campi);
	if (isset($ret_vdet['error']))	{
		//dbg 
		$ret = '<p style="font-family:monospace">Errore in ' . __FUNCTION__ 
		. ' Si è verificato un errore nella ricerca in ' . VideoDettagli::nome_tabella
		. '<br>campi: ' . str_ireplace(';', '; ', serialize($campi))
		. '<br>input: ' . str_ireplace(';', '; ', serialize($ret_vdet));
		echo $ret;
		return[];
	}
	for ($i=0; $i < count($ret_vdet['data']); $i++) {
		$video_id[]=$ret_vdet['data'][$i]['record_id_padre'];
	}
	// deposito scansioni_disco - video
	$query = 'SELECT record_id FROM ' . ScansioniDisco::nome_tabella
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. " AND nome_file <> '/' "
	. " AND estensione IN ('mp4', 'mov', 'mkv', 'avi') ";
	foreach ($termini_ricerca as $termine) {
		$termine= addslashes($termine); // non per sanificare
		$query .= " AND (disco  like '%".$termine."%' "
		       . " OR  livello1 like '%".$termine."%' "
		       . " OR  livello2 like '%".$termine."%' "
		       . " OR  livello3 like '%".$termine."%' "
		       . " OR  livello4 like '%".$termine."%' "
		       . " OR  livello5 like '%".$termine."%' "
		       . " OR  livello6 like '%".$termine."%' "
		       . " OR titolo_video like '%".$termine."%' "
		       . " OR nome_file like '%".$termine."%' "
		       . ') ';
	} // foreach
	// $query .= ' ORDER BY record_id ';
	$campi=[];
	$campi['query']= $query;
	$campi['record_cancellabile_dal']= $dbh->get_datetime_forever();
	$ret_scan = $scan_h->leggi($campi);
	if (isset($ret_scan['error']))	{
		//dbg 
		$ret = '<p style="font-family:monospace">Errore in ' . __FUNCTION__ 
		. ' Si è verificato un errore nella ricerca in ' . ScansioniDisco::nome_tabella
		. '<br>campi: ' . str_ireplace(';', '; ', serialize($campi))
		. '<br>input: ' . str_ireplace(';', '; ', serialize($ret_scan));
		echo $ret;
		return[];
	}
	// da scansioni disco all'elenco album 
	$lista_deposito_id = [];
	for ($i=0; $i < count($ret_scan['data']); $i++) { 
		$lista_deposito_id[]=$ret_scan['data'][$i]['record_id'];
	}
	// non vuoto 
	if (count($lista_deposito_id) > 0){
		// strizza e cerca gli album che ci sono
		asort($lista_deposito_id);
		$lista_deposito_id=array_unique($lista_deposito_id);
		$query = 'SELECT record_id FROM ' . Video::nome_tabella 
		. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
		. ' AND record_id_in_scansioni_disco IN ('
		. implode(', ', $lista_deposito_id)
		.') ';
		$campi=[];
		$campi['query']=$query;
		$campi['record_cancellabile_dal']= $dbh->get_datetime_forever();
		$ret_vid = $vid_h->leggi($campi);
		if (isset($ret_vid['error']))	{
			//dbg 
			$ret = '<p style="font-family:monospace">Errore in ' . __FUNCTION__ 
			. ' Si è verificato un errore nella ricerca in ' . Video::nome_tabella
			. '<br>campi: ' . str_ireplace(';', '; ', serialize($campi))
			. '<br>input: ' . str_ireplace(';', '; ', serialize($ret_vid));
			echo $ret;
			return[];
		}
		for ($i=0; $i < count($ret_vid['data']); $i++) {
			$video_id[]=$ret_vid['data'][$i]['record_id'];
		}
	} // lista_deposito_id non vuoto 

	// didascalie 
	$query = 'SELECT record_id_padre FROM ' . Didascalie::nome_tabella
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. ' AND tabella_padre = :tabella_padre '
	. " AND MATCH(didascalia) AGAINST ('" . $dati_input['valore'] . "')";
	$campi=[];
	$campi['query']=$query;
	$campi['record_cancellabile_dal']= $dbh->get_datetime_forever();		
	$campi['tabella_padre']= Video::nome_tabella;		
	$ret_dida = $dida_h->leggi($campi);
	if (isset($ret_dida['error']))	{
		//dbg 
		$ret = '<p style="font-family:monospace">Errore in ' . __FUNCTION__ 
		. ' Si è verificato un errore nella ricerca in ' . Didascalie::nome_tabella
		. '<br>campi: ' . str_ireplace(';', '; ', serialize($campi))
		. '<br>input: ' . str_ireplace(';', '; ', serialize($ret_dida));
		echo $ret;
		return[];
	}
	for ($i=0; $i < count($ret_dida['data']); $i++) {
		$video_id[]=$ret_dida['data'][$i]['record_id_padre'];
	}

	// alla fine strizza e ordina
	asort($video_id);
	$video_id = array_unique($video_id);
	return array_values($video_id);

} // get_video_list()

/**
 * Prende una ricerca già eseguita e va a cercare un tot di id
 * in un gruppo, in avanti
 * @param  array  $dati_input 
 * @return string html con i dati o messaggio di errore 
 */
function get_blocco_ricerca_avanti(array $dati_input) : string {
	if (!isset($dati_input['ricerca_id'])){
		$ret = '<p>Si è verificato un errore in ' . __FUNCTION__ 
		. ' [1]<br>Non sono stati forniti i dati corretti.'
		. str_ireplace(';', '; ', serialize($dati_input))
		. ' </p>';
		return $ret;
	}
	$ricerca_id = filter_var($dati_input['ricerca_id'], FILTER_SANITIZE_NUMBER_INT);
	$ricerca_id = (int) $ricerca_id;
	if ($ricerca_id < 1){
		$ret = '<p>Si è verificato un errore in ' . __FUNCTION__ 
		. ' [3]<br>Non sono stati forniti i dati corretti.'
		. str_ireplace(';', '; ', serialize($dati_input))
		. ' </p>';
		return $ret;
	}
	if (!isset($dati_input['gruppo'])){
		$ret = '<p>Si è verificato un errore in ' . __FUNCTION__ 
		. ' [4]<br>Non sono stati forniti i dati corretti.'
		. str_ireplace(';', '; ', serialize($dati_input))
		. ' </p>';
		return $ret;
	}
	$gruppo_valori_ok = [
		'album',
		'fotografie',
		'video'
	];
	$gruppo = strtolower($dati_input['gruppo']);
	if (!in_array($gruppo, $gruppo_valori_ok)){
		$ret = '<p>Si è verificato un errore in ' . __FUNCTION__ 
		. ' [5]<br>Non sono stati forniti i dati corretti.'
		. str_ireplace(';', '; ', serialize($dati_input))
		. ' </p>';
		return $ret;
	}

	if (!isset($dati_input['ultimo'])){
		$ret = '<p>Si è verificato un errore in ' . __FUNCTION__ 
		. ' [6]<br>Non sono stati forniti i dati corretti.'
		. str_ireplace(';', '; ', serialize($dati_input))
		. ' </p>';
		return $ret;
	}
	$ultimo = (int) $dati_input['ultimo'];
	if ($ultimo < 0){
		$ret = '<p>Si è verificato un errore in ' . __FUNCTION__ 
		. ' [7]<br>Non sono stati forniti i dati corretti.'
		. str_ireplace(';', '; ', serialize($dati_input))
		. ' </p>';
		return $ret;
	}

	if (!isset($dati_input['tot'])){
		$ret = '<p>Si è verificato un errore in ' . __FUNCTION__ 
		. ' [8]<br>Non sono stati forniti i dati corretti.'
		. str_ireplace(';', '; ', serialize($dati_input))
		. ' </p>';
		return $ret;
	}
	$tot = (int) $dati_input['tot'];
	if ($tot < 1){
		$ret = '<p>Si è verificato un errore in ' . __FUNCTION__ 
		. ' [9]<br>Non sono stati forniti i dati corretti.'
		. str_ireplace(';', '; ', serialize($dati_input))
		. ' </p>';
		return $ret;
	}

	$dbh    = New DatabaseHandler();
	$rice_h = New Ricerche($dbh);
	$query  = 'SELECT risultato FROM ' . Ricerche::nome_tabella
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. ' AND record_id = :record_id ';
	$campi=[];
	$campi['query'] = $query;
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
	$campi['record_id'] = $ricerca_id;
	$ret_ric = $rice_h->leggi($campi);

	if (isset($ret_ric['error'])){
		$ret = '<p>Si è verificato un errore in ' . __FUNCTION__ 
		.  '[2]<br>ret_ric: ' . str_ireplace(';', '; ', serialize($ret_ric))
		.  ' </p>';
		return $ret;
	}
	if (isset($ret_ric['numero']) && $ret_ric['numero'] == 0){
		$ret = '<p>Non è stata trovata la ricerca in ' . __FUNCTION__ 
		.  '[3]<br>Può essere stata rimossa </p>';
		return $ret;
	}
	$ricerca   = $ret_ric['data'][0];
	$risultato = json_decode($ricerca['risultato'], true);
	if (!isset($risultato['album'])){
		$risultato['album']=[];
	}
	if (!isset($risultato['fotografie'])){
		$risultato['fotografie']=[];
	}
	if (!isset($risultato['video'])){
		$risultato['video']=[];
	}
	// split n strip 
	if ($gruppo === 'album'){
		$blocco_id  = array_slice($risultato['album'], $ultimo, $tot, true);
		$tot        = count($blocco_id);
		$html_album = '<p>Da n.'.($ultimo + 1) . ' fino a ' . ($ultimo + $tot) 
		.       ' album.</p>';
		$html_album .= get_html_album( $blocco_id );
		// albumPrimo albumUltimo fanno rifermento all'indice cardinale nell'elenco dei risultati
		// ma se si crea un buco nella lettura (per qualcosa di cancellato) SI CREANO buchi
		$html_album .= "\n"
		. '<span id="albumPrimo"  class="d-none">'.($ultimo+1)     .'</span>'
		. '<span id="albumUltimo" class="d-none">'.($ultimo+$tot).'</span>';
		return $html_album;
	}

	if ($gruppo === 'fotografie'){
		$blocco_id  = array_slice($risultato['fotografie'], $ultimo, $tot, true);
		$tot        = count($blocco_id);
		$html_fotografie  = '<p>Vista da n.'.($ultimo + 1) . ' fino a ' . ($ultimo + $tot) 
		. ' foto.</p>';
		$html_fotografie .= get_html_fotografie( $blocco_id );
		$html_fotografie .= "\n"
		. '<span id="fotoPrima"  class="d-none">'.($ultimo+1)     .'</span>'
		. '<span id="fotoUltima" class="d-none">'.($ultimo+1+$tot).'</span>';
		return $html_fotografie;
	}
	// restano i video 
	$blocco_id  = array_slice($risultato['video'], $ultimo, $tot, true);
	$tot        = count($blocco_id);
	$html_video = '<p>Vista da n.'.($ultimo + 1) . ' fino a ' . ($ultimo + $tot) 
	. ' video.</p>';
	$html_video .= get_html_video( $blocco_id );
	$html_video .= "\n"
	. '<span id="videoPrimo"  class="d-none">' .($ultimo+1)     .'</span>'
	. '<span id="videoUltimo" class="d-none">'.($ultimo+1+$tot).'</span>';
	return $html_video;

} // get_blocco_ricerca_avanti

/**
 * prende una ricerca già eseguita e va a cercare un tot di id
 * in un gruppo, a gambero
 * @param  array  $dati_input
 * @return string html
 */
function get_blocco_ricerca_indietro(array $dati_input) : string {
	// test input
	if (!isset($dati_input['ricerca_id'])){
		$ret = '<p>Si è verificato un errore in ' . __FUNCTION__ 
		. ' [1]<br>Non sono stati forniti i dati corretti.'
		. str_ireplace(';', '; ', serialize($dati_input))
		. ' </p>';
		return $ret;
	}
	$ricerca_id = filter_var($dati_input['ricerca_id'], FILTER_SANITIZE_NUMBER_INT);
	$ricerca_id = (int) $ricerca_id;
	if ($ricerca_id < 1){
		$ret = '<p>Si è verificato un errore in ' . __FUNCTION__ 
		. ' [3]<br>Non sono stati forniti i dati corretti.'
		. str_ireplace(';', '; ', serialize($dati_input))
		. ' </p>';
		return $ret;
	}
	if (!isset($dati_input['gruppo'])){
		$ret = '<p>Si è verificato un errore in ' . __FUNCTION__ 
		. ' [4]<br>Non sono stati forniti i dati corretti.'
		. str_ireplace(';', '; ', serialize($dati_input))
		. ' </p>';
		return $ret;
	}
	$gruppo_valori_ok = [
		'album',
		'fotografie',
		'video'
	];
	$gruppo = strtolower($dati_input['gruppo']);
	if (!in_array($gruppo, $gruppo_valori_ok)){
		$ret = '<p>Si è verificato un errore in ' . __FUNCTION__ 
		. ' [5]<br>Non sono stati forniti i dati corretti.'
		. str_ireplace(';', '; ', serialize($dati_input))
		. ' </p>';
		return $ret;
	}

	if (!isset($dati_input['tot'])){
		$ret = '<p>Si è verificato un errore in ' . __FUNCTION__ 
		. ' [8]<br>Non sono stati forniti i dati corretti.'
		. str_ireplace(';', '; ', serialize($dati_input))
		. ' </p>';
		return $ret;
	}
	$tot = (int) $dati_input['tot'];
	if ($tot < 1){
		$ret = '<p>Si è verificato un errore in ' . __FUNCTION__ 
		. ' [9]<br>Non sono stati forniti i dati corretti.'
		. str_ireplace(';', '; ', serialize($dati_input))
		. ' </p>';
		return $ret;
	}

	if (!isset($dati_input['primo'])){
		$ret = '<p>Si è verificato un errore in ' . __FUNCTION__ 
		. ' [6]<br>Non sono stati forniti i dati corretti.'
		. str_ireplace(';', '; ', serialize($dati_input))
		. ' </p>';
		return $ret;
	}
	$primo = (int) $dati_input['primo'];
	if ($primo < 0){
		$ret = '<p>Si è verificato un errore in ' . __FUNCTION__ 
		. ' [7]<br>Non sono stati forniti i dati corretti.'
		. str_ireplace(';', '; ', serialize($dati_input))
		. ' </p>';
		return $ret;
	}

	// get_risultato_ricerca_id(id): array
	$dbh    = New DatabaseHandler();
	$rice_h = New Ricerche($dbh);
	$query  = 'SELECT risultato FROM ' . Ricerche::nome_tabella
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. ' AND record_id = :record_id ';
	$campi=[];
	$campi['query'] = $query;
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
	$campi['record_id'] = $ricerca_id;
	$ret_ric = $rice_h->leggi($campi);
	if (isset($ret_ric['error'])){
		$ret = '<p>Si è verificato un errore in ' . __FUNCTION__ 
		.  '[2]<br>ret_ric: ' . str_ireplace(';', '; ', serialize($ret_ric))
		.  ' </p>';
		return $ret;
	}
	if (isset($ret_ric['numero']) && $ret_ric['numero'] == 0){
		$ret = '<p>Non è stata trovata la ricerca in ' . __FUNCTION__ 
		.  '[3]<br>Può essere stata rimossa </p>';
		return $ret;
	}
	$ricerca   = $ret_ric['data'][0];
	// get_risultato_ricerca_id(id): array
	$risultato = json_decode($ricerca['risultato'], true);
	if (!isset($risultato['album'])){
		$risultato['album']=[];
	}
	if (!isset($risultato['fotografie'])){
		$risultato['fotografie']=[];
	}
	if (!isset($risultato['video'])){
		$risultato['video']=[];
	}
	// 
	if ($gruppo === 'album'){

		$blocco_id  = array_slice($risultato['album'], $primo, $tot, true);
		$tot        = count($blocco_id); // al più resta $tot, però...
		$html_album = "<p>In totale sono stati rintracciati "
		. "\n". '<span id="albumTrovati">'.count($risultato['album']).'</span> album.'
		. "\n". 'Vista da n.'.($primo) . ' fino a ' . ($primo + $tot - 1)
		.       '<span id="totAlbum" class="d-none">'.$tot.'</span> album.'
		. '</p>';
		$html_album .= get_html_album( $blocco_id );
		// albumPrimo albumUltimo fanno rifermento all'indice cardinale nell'elenco dei risultati
		// ma se si crea un buco nella lettura (per qualcosa di cancellato) SI CREANO buchi
		$html_album .= "\n"
		. '<span id="albumPrimo"  class="d-none">'.($primo)     .'</span>'
		. '<span id="albumUltimo" class="d-none">'.($primo+$tot-1).'</span>';
		return $html_album;
	}

	if ($gruppo === 'fotografie'){
		$blocco_id  = array_slice($risultato['fotografie'], $primo, $tot, true);
		$tot        = count($blocco_id);
		$html_fotografie  = '<p>In totale sono state rintracciate '
		. "\n". '<span id="fotoTrovate">'.count($risultato['fotografie']).'</span> fotografie.'
		. "\n". 'Vista da n.'.($primo) . ' fino a ' . ($primo + $tot - 1)
		.       '<span id="totFoto" class="d-none">'.$tot.'</span> foto.'
		. '</p>';
		$html_fotografie .= get_html_fotografie( $blocco_id );
		$html_fotografie .= "\n"
		. '<span id="fotoPrima"  class="d-none">'.($primo)     .'</span>'
		. '<span id="fotoUltima" class="d-none">'.($primo+$tot-1).'</span>';
		return $html_fotografie;
	}
	// restano i video 
	$blocco_id  = array_slice($risultato['video'], $primo, $tot, true);
	$tot        = count($blocco_id);
	$html_video = '<p>In totale sono stati rintracciati '
	. "\n". '<span id="videoTrovati">'.count($risultato['video']).'</span> video.'
	. "\n". 'Vista da n.'.($primo) . ' fino a ' . ($primo + $tot - 1)
	.       '<span id="totVideo" class="d-none">'.$tot.'</span> video.'
	. '</p>';
	$html_video = get_html_video( $blocco_id );
	$html_video.= "\n"
	. '<span id="videoPrimo"  class="d-none">'.($primo)       .'</span>'
	. '<span id="videoUltimo" class="d-none">'.($primo+$tot-1).'</span>';
	return $html_video;
} // get_blocco_ricerca_indietro()



function get_html_album( array $album_list = [] ) :string {
	$dbh     = New DatabaseHandler();
	$alb_h   = New Album($dbh);
	$html_album='';

	// test $album_list
	$query = 'SELECT a.*, s.tinta_rgb '
	. ' FROM ' . Album::nome_tabella . ' a, '
	.   ScansioniDisco::nome_tabella . ' s '
	. ' WHERE a.record_cancellabile_dal = :record_cancellabile_dal '
	. ' AND a.record_id_in_scansioni_disco = s.record_id '
	. " AND a.record_id IN ("
	. implode(', ' , $album_list)
	. ") ORDER BY a.record_id ";
	$campi=[];	
	$campi['query']  = $query; 
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();

	$ret_alb = $alb_h->leggi($campi);
	if (isset($ret_alb['error'])){
		$html_album = '<p>'
		. "Si è verificato un errore nella funzione " . __FUNCTION__ 
		. " durante il recupero dei record in album."
		. '<br>campi: '. str_ireplace(';', '; ', serialize($campi))
		. '<br>ret: '  . str_ireplace(';', '; ', serialize($ret_alb))
		. '</p>';
		return $html_album;
	} 
	$html_album .= '<table>';
	$html_album .= '<tbody>';			
	for ($i=0; $i < count($ret_alb['data']); $i++) { 
		$album = $ret_alb['data'][$i];
		$html_album .= "\n".'<tr><td style="height:5rem;">'
		. '<span class="aa-album" data-risultato-id="'.$album_list[$i].'"></span>'
		. '<a href="' . URLBASE . 'album.php/leggi/' . $album['record_id'] . '" '
		. 'target="_blank" ><i class="h1 bi bi-archive" style="color:#'.$album['tinta_rgb'].'"></i></a>&nbsp;</td><td>'
		. '<a href="' . URLBASE . 'album.php/leggi/' . $album['record_id'] . '" '
		. 'target="_blank" style="text-decoration:none;color:#'.$album['tinta_rgb'].'">'. $album['titolo_album'] 
		. '</a><br>'
		. '<span class="h6 small text-secondary text-wrap"><em>Siete in: </em>'
		. str_ireplace('/', ' / ', $album['percorso_completo'])
		. '</span></td></tr>';

	}
	$html_album .= "\n".'</tbody>';
	$html_album .= '</table>';
	return $html_album;
	
} // get_html_album()

function get_html_fotografie( array $foto_list = []) : string {
	$dbh    = New DatabaseHandler();
	$foto_h = New Fotografie($dbh);
	$html_fotografie='';
	$quante_foto =24;
	$query = ' SELECT f.*, a.titolo_album '
	. ' FROM ' . Fotografie::nome_tabella . ' f, '
	.                 Album::nome_tabella . ' a '
	. ' WHERE f.record_cancellabile_dal = :record_cancellabile_dal '
	. ' AND f.record_id_in_album = a.record_id '
	. ' AND f.record_id in ( '
	. implode(', ', $foto_list)
	. ' ) ORDER BY f.record_id ';
	$campi=[];
	$campi['query'] = $query;
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
	$ret_foto = $foto_h->leggi($campi);
	if (isset($ret_alb['error'])){
		$html_fotografie .= '<p>'
		. "Si è verificato un errore nella funzione " . __FUNCTION__ 
		. " durante il recupero delle prime " . $quante_foto . " fotografie."
		. '<br>campi: ' . str_ireplace(';', '; ', serialize($campi))
		. '<br>ret: ' . str_ireplace(';', '; ', serialize($ret_foto))
		. '</p>';
		return $html_fotografie;

	} else {
		$quante_lette = count($ret_foto['data']);
		for ($i=0; $i < $quante_lette; $i++) { 
			$foto = $ret_foto['data'][$i];
			$foto_jpg = str_ireplace(['.jpg', '.psd', '.tif'], '.jpg' , $foto['percorso_completo']);
			$foto_jpg = str_ireplace('&amp;', '&' , $foto_jpg); // d&amp;039;Adige > d&039;Adige
			$foto_jpg = str_ireplace('&039;', "'" , $foto_jpg); // d&039;Adige     > d'Adige

			$html_fotografie .= "\n".'<div class="float-start" style="width:200px;height:200px;">'
			. '<a href="' . URLBASE . 'fotografie.php/leggi/'. $foto['record_id'] .'" '
			. ' target="_blank" title="FOTO [' . $foto['titolo_fotografia']. '] IN ALBUM ['.$foto['titolo_album'].'] ">'
			. '<img style="max-width:200px; max-height:200px;" loading="lazy"  class="d-block w-100" '
			. ' src="' . URLBASE . $foto_jpg . '" />'
			. '</a></div>';	
		}
		return $html_fotografie;
	}

} // get_html_fotografie()

function get_html_video( array $video_list=[] ) : string {
	$dbh    = New DatabaseHandler();
	$vid_h  = New Video($dbh);

	$quanti_video = 12; 
	$quanti_video = min($quanti_video, count($video_list));
	
	$query = 'SELECT v.*, a.titolo_album '
	. ' FROM ' . Video::nome_tabella . ' v, '
	.          Album::nome_tabella . ' a '
	. ' WHERE v.record_cancellabile_dal = :record_cancellabile_dal '
	. ' AND v.record_id_in_album = a.record_id '
	. ' AND v.record_id in ( '
	. implode(', ', $video_list)
	. ' ) ORDER BY v.record_id ';
	$campi=[];
	$campi['query'] = $query;
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
	$ret_vid = $vid_h->leggi($campi);

	if (isset($ret_vid['error'])){
		$html_video .= '<p>'
		. 'Si è verificato un errore nella funzione ' . __FUNCTION__ 
		. ' durante il recupero dei video.'
		. '<br>campi: '. str_ireplace(';', '; ', serialize($campi))
		. '<br>ret: '.   str_ireplace(';', '; ', serialize($ret_vid))
		. '</p>';
		return $html_video;
	} 
	for ($i=0; $i < count($ret_vid['data']); $i++) { 
		// recupero video
		$video = $ret_vid['data'][$i];
		// $foto_jpg = str_ireplace(['.jpg', '.psd', '.tif'], '.jpg' , $foto['percorso_completo']);
		$html_video .= "\n".'<div class="float-start" style="width:200px;height:200px;">'
		. '<a href="' . URLBASE . 'video.php/leggi/'. $video['record_id'] .'" '
		. ' target="_blank" title="VIDEO ['. $video['titolo_video']. '] IN ALBUM ['. $video['titolo_album']. '] ">'
		. '<img style="max-width:200px; max-height:200px;" loading="lazy"  class="d-block w-100" '
		. ' src="' . URLBASE . 'aa-img/video-segnalino.png" />'
		. '</a></div>';	
	}
	return $html_video;
} // get_html_video()