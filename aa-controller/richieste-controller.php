<?php 
/**
 * @source /aa-controller/richieste-controller.php
 * @author Massimo Rainato <maxrainato@libero.it>
 * 
 * RICHIESTE controller
 * Nella navigazione il consultatore può marcare 
 * alcune foto o album per chiedere di avere accesso
 * agli originali. Un amministratore delegato dal comitato di gestione 
 * può rispondere sì o no, motivandolo. 
 *  
 * 
 */
if (!defined('ABSPATH')){
	include_once('../_config.php');
}
include_once(ABSPATH . 'aa-model/database-handler-oop.php');
include_once(ABSPATH . 'aa-model/consultatori-oop.php');
include_once(ABSPATH . 'aa-model/richieste-oop.php');
include_once(ABSPATH . 'aa-model/album-oop.php');
include_once(ABSPATH . 'aa-model/fotografie-oop.php');
include_once(ABSPATH . 'aa-model/video-oop.php');

/**
 * consultatore - elenco delle richieste in sospeso 
 * consultatore_id può essere prelevato da Cookie ma è 
 * preferibile fornirlo come parametro anche per usarlo nei test  
 * 
 * @param  int $consultatore_id 
 * @return void espone una schermata html
 * 
 */
function get_elenco_richieste_consultatore(int $consultatore_id){
	$dbh   = new DatabaseHandler();
	$con_h = new Consultatori($dbh); 
	$ric_h = new Richieste($dbh); 
	$alb_h = new Album($dbh); 
	$fot_h = new Fotografie($dbh); 
	$vid_h = new Video($dbh); 

	// verifiche 
	$ret_con = $con_h->get_consultatore_from_id($consultatore_id);
	if (isset($ret_con['error'])){
		http_response_code(404);
		$ret = '<p style="font-family:monospace;color:red;">' . __FUNCTION__
		. ' Errore in lettura consultatore' . '</p>'
		. '<p>'.$ret_con['message'].'</p>'	
		. '<p>Consultatore-id: '.$consultatore_id.'</p>';	
		echo $ret;
		exit(1);
	}
	if ($ret_con['numero'] == 0){
		http_response_code(404);
		$ret = '<p style="font-family:monospace;color:red;">' . __FUNCTION__
		. ' Errore in lettura consultatore' . '</p>'
		. '<p>Consultatore-id: '.$consultatore_id.'</p>';	
		echo $ret; 
		exit(1);
	}
	$consultatore = $ret_con['data'][0];

	$ric_h->set_record_id_richiedente($consultatore_id);
	//
	$campi=[];
	$campi['query']= 'SELECT * FROM ' . Richieste::nome_tabella
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. ' AND richiesta_evasa_il = :richiesta_evasa_il '
	. ' AND record_id_richiedente = :record_id_richiedente '
	. ' ORDER BY record_id ';
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
	$campi['richiesta_evasa_il']      = $dbh->get_datetime_forever();
	$campi['record_id_richiedente']   = $ric_h->get_record_id_richiedente();
	$ret_ric = $ric_h->leggi($campi);
	if (isset($ret_ric['error'])){
		http_response_code(404);
		$ret = '<p style="font-family:monospace;color:red;">'
		. 'Errore in lettura richieste' . '</p>'
		. '<p>'.$ret_ric['message'].'</p>'	
		. '<p>Campi: '.serialize($campi).'</p>';	
		exit(1);
	}

	// 
	// via si stampa 
	$nome_consultatore=$consultatore['cognome_nome'];
	$numero_richieste = $ret_ric['numero'];
	if ($numero_richieste==0){
		$elenco_richieste= '<tr><td colspan="2">'
		. '<p class="h4">Nessuna richiesta in sospeso.</p>'
		. '</td></tr>';
		require_once(ABSPATH.'aa-view/richieste-consultatore-view.php');
		exit(0);
	}

	// loop 
	$elenco_richieste='';
	$richieste=$ret_ric['data'];
	for ($i=0; $i < count($richieste) ; $i++) { 

		$richiesta_singola = $richieste[$i];
		$oggetto_richiesta = $richiesta_singola['oggetto_richiesta'];
		$oggetto_id        = $richiesta_singola['record_id_richiesta'];
		$data_richiesta    = substr($richiesta_singola['ultima_modifica_record'], 0, 10);
		$richiesta_id      = $richiesta_singola['record_id'];

		if ($oggetto_richiesta == 'fotografie'){
			$ret_foto = $fot_h->get_fotografia_from_id($oggetto_id);
			if (isset($ret_foto['error'])){
				http_response_code(404);
				$ret = '<p style="font-family:monospace;color:red;">'
				. 'Errore in lettura richieste' . '</p>'
				. '<p>'.$ret_foto['message'].'</p>'	
				. '<p>foto_id: '.$oggetto_id.'</p>';	
				echo $ret;
				exit(1);
			}
			if ($ret_foto['numero']>0){
				$fotografia=$ret_foto['data'][0];
				$titolo   = $fotografia['titolo_fotografia'];
				$siete_in = $fotografia['percorso_completo'];
			}
		} // fotografia 
		if ($oggetto_richiesta == 'album'){
			$rec_album= $alb_h->get_album_from_id($oggetto_id);
			if (isset($rec_album['error'])){
				http_response_code(404);
				$ret = '<p style="font-family:monospace;color:red;">'
				. 'Errore in lettura richieste' . '</p>'
				. '<p>'.$rec_album['message'].'</p>'	
				. '<p>album_id: '.$oggetto_id.'</p>';	
				echo $ret;
				exit(1);
			}
			if ($rec_album['numero']>0){
				$album=$rec_album['data'][0];
				$titolo   = $album['titolo_album'];
				$siete_in = $album['percorso_completo'];
			}
		} // album 
		if ($oggetto_richiesta == 'video'){
			$rec_video= $vid_h->get_video_from_id($oggetto_id);
			if (isset($rec_video['error'])){
				http_response_code(404);
				$ret = '<p style="font-family:monospace;color:red;">'
				. 'Errore in lettura richieste' . '</p>'
				. '<p>'.$ret_video['message'].'</p>'	
				. '<p>video_id: '.$oggetto_id.'</p>';	
				echo $ret;
				exit(1);
			}
			if ($rec_video['numero']>0){
				$video=$rec_video['data'][0];
				$titolo   = $video['titolo_video'];
				$siete_in = $video['percorso_completo'];
			}
		} // video

		// 
		$rec = '<tr><td>'.($i + 1).'. '. $titolo . '<br>'."\n"
		. 'Siete in: '.$siete_in . '<br>'."\n"
		. 'Richiesta del: ' . $data_richiesta 
		. '</td><td>'. "\n"
		. '<a class="btn btn-secondary cancella-richiesta" href="'
		. URLBASE.'richieste.php/cancella-richiesta/'.$richiesta_id
		. '" role="button"><i class="bi bi-trash-fill"></i></a>' . "\n"
		. '</td></tr>' . "\n";

		$elenco_richieste .= $rec;

	} // for 
	require_once(ABSPATH.'aa-view/richieste-consultatore-view.php');
	exit(0);

} // get_elenco_richieste_consultatore()

/** TEST 
 * http://localhost:8888/AMUVEFO-sigec-php/aa-controller/richieste-controller.php?id=6&test=get_elenco_richieste_consultatore
 * 
 */
	if (isset($_GET['test'])     && 
	    isset($_GET['id'])       && 
			$_GET['test']='get_elenco_richieste_consultatore'){
		get_elenco_richieste_consultatore($_GET['id']);		
		echo "fine.";
		exit(0);
	}
//


function cancella_richiesta_per_id(int $richiesta_id){
	$dbh   = new DatabaseHandler();
	$ric_h = new Richieste($dbh); 

	// verifiche
	$ret_ric = $ric_h->get_richiesta_from_id($richiesta_id);
	if (isset($ret_ric['error'])){
		http_response_code(404);
		$ret = '<p style="font-family:monospace;color:red;">' . __FUNCTION__
		. ' Errore in lettura richiesta' . '</p>'
		. '<p>'.$ret_ric['message'].'</p>'	
		. '<p>Richiesta_id: '.$richiesta_id.'</p>';	
		echo $ret;
		exit(1);
	}
	if ($ret_ric['numero'] == 0){
		http_response_code(404);
		$ret = '<p style="font-family:monospace;color:red;">' . __FUNCTION__
		. ' Errore in lettura richiesta' . '</p>'
		. '<p>Richiesta_id: '.$richiesta_id.'</p>';	
		echo $ret; 
		exit(1);
	}
	$richiesta=$ret_ric['data'][0];
	// cancellazione non fisica 
	$campi=[];
	$campi['update'] = ' UPDATE ' . Richieste::nome_tabella
	. ' SET record_cancellabile_dal = :record_cancellabile_dal '
	. ' WHERE record_id = :record_id  ';
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_now();
	$campi['record_id'] = $richiesta_id;
	$ret_ric=[];
	$ret_ric = $ric_h->modifica($campi);
	if (isset($ret_ric['error'])){
		http_response_code(404);
		$ret = '<p style="font-family:monospace;color:red;">' . __FUNCTION__
		. ' Errore in cancellazione richiesta' . '</p>'
		. '<p>'.$ret_ric['message'].'</p>'	
		. '<p>Richiesta_id: '.$richiesta_id.'</p>';	
		echo $ret;
		exit(1);
	}
	// si torna all'elenco consultatore 
	$consultatore_id = $_COOKIE['consultatore_id'];
	get_elenco_richieste_consultatore($consultatore_id);
	exit(0);
} // cancella_richiesta_per_id()


/**
 * Elenco richieste per amministratore - tutti richiedenti, tutte le richieste
 */
function get_elenco_richieste_per_amministratore(){
	$dbh   = new DatabaseHandler();
	$con_h = new Consultatori($dbh); 
	$ric_h = new Richieste($dbh); 
	$alb_h = new Album($dbh); 
	$fot_h = new Fotografie($dbh); 
	$vid_h = new Video($dbh); 

	// ricerca "de tutto" 
	$tabelle_richieste='';
	// 1.for richiedenti 
	//   2.for richieste per ogni richiedente 
	$campi=[];
	$campi['query'] = 'SELECT DISTINCT record_id_richiedente, '
	. ' count(*) as Num FROM ' . Richieste::nome_tabella
	. ' WHERE (record_cancellabile_dal = :record_cancellabile_dal) '
	. ' AND richiesta_evasa_il = :richiesta_evasa_il '
	. ' GROUP BY record_id_richiedente';
	$campi['record_cancellabile_dal']=$dbh->get_datetime_forever();
	$campi['richiesta_evasa_il']=$dbh->get_datetime_forever();
	$ret_ric = $ric_h->leggi($campi);
	if (isset($ret_ric['error'])){
		http_response_code(404);
		$ret = '<p style="font-family:monospace;color:red;">'
		. 'Errore in lettura richieste' . '</p>'
		. '<p>'.$ret_ric['message'].'</p>'	
		. '<p>Campi: '.serialize($campi).'</p>';	
		echo $ret;
		exit(1);
	}
	if ($ret_ric['numero'] == 0 ){
		http_response_code(404);
		$ret = '<p style="font-family:monospace;color:red;">'
		. 'Errore in lettura richieste' . '</p>'
		. '<p>Nessun record trovato</p>'	
		. '<p>Campi: '.serialize($campi).'</p>';	
		echo $ret;
		exit(1);
	}
	$consultatori = $ret_ric['data'];
	// echo '<br>Letti '.count($consultatori).' consultatori<br>';
	$ret_ric=[];
	$blocco_consultatore = file_get_contents(ABSPATH.'aa-view/richieste-amministratore-consultatore-view.php');
	// echo htmlentities($blocco_consultatore);
	// exit(0);
	for ($i=0; $i < count($consultatori) ; $i++) { 
		$consultatore_id = $consultatori[$i]['record_id_richiedente'];
		$ret_con = $con_h->get_consultatore_from_id($consultatore_id);
		if (isset($ret_con['error'])){
			http_response_code(404);
			$ret = '<p style="font-family:monospace;color:red;">'
			. 'Errore in lettura consultatori' . '</p>'
			. '<p>'.$ret_con['message'].'</p>'	
			. '<p>Consultatore: '.$consultatore_id.'</p>';	
			echo $ret;
			exit(1);
		}
		if ($ret_con['numero'] == 0){
			continue; // consultatore cancellato?
		}
		$consultatore= $ret_con['data'][0];
		$nome_consultatore = $consultatore['cognome_nome'];

		// echo '<br>Consultatore: '.$consultatore_id; 
		// echo '<br>Consultatore: '.$nome_consultatore; 
		// echo '<br>'; 
		$ric_h->set_record_id_richiedente($consultatore_id);
		//
		$campi=[];
		$campi['query']= 'SELECT * FROM ' . Richieste::nome_tabella
		. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
		. ' AND richiesta_evasa_il = :richiesta_evasa_il '
		. ' AND record_id_richiedente = :record_id_richiedente '
		. ' ORDER BY record_id ';
		$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
		$campi['richiesta_evasa_il']      = $dbh->get_datetime_forever();
		$campi['record_id_richiedente']   = $ric_h->get_record_id_richiedente();
		$ret_ric = $ric_h->leggi($campi);
		if (isset($ret_ric['error'])){
			http_response_code(404);
			$ret = '<p style="font-family:monospace;color:red;">'
			. 'Errore in lettura richieste' . '</p>'
			. '<p>'.$ret_ric['message'].'</p>'	
			. '<p>Campi: '.serialize($campi).'</p>';	
			echo $ret; 
			exit(1);
		}
		$elenco_richieste='';
		$richieste=$ret_ric['data'];
		// echo '<br>Richieste: '. str_replace(';', '; ', serialize($ret_ric));
		// echo '<br>'; 
		// echo '<br>'; 

		for ($j=0; $j < count($richieste) ; $j++) { 
	
			$richiesta_singola = $richieste[$j];
			$oggetto_richiesta = $richiesta_singola['oggetto_richiesta'];
			$oggetto_id        = $richiesta_singola['record_id_richiesta'];
			$data_richiesta    = substr($richiesta_singola['ultima_modifica_record'], 0, 10);
			$richiesta_id      = $richiesta_singola['record_id'];
			// echo '<br>Richiesta_id: '.$richiesta_id;
			// echo '<br>oggetto: '.$oggetto_richiesta;
	
			if ($oggetto_richiesta == 'fotografie'){
				$ret_foto = $fot_h->get_fotografia_from_id($oggetto_id);
				// echo '<br>ret_foto: '. serialize($ret_foto);
				// echo '<br>';
				if (isset($ret_foto['error'])){
					http_response_code(404);
					$ret = '<p style="font-family:monospace;color:red;">'
					. 'Errore in lettura richieste' . '</p>'
					. '<p>'.$ret_foto['message'].'</p>'	
					. '<p>foto_id: '.$oggetto_id.'</p>';	
					echo $ret;
					exit(1);
				}
				if ($ret_foto['numero']>0){
					$fotografia=$ret_foto['data'][0];
					$titolo   = $fotografia['titolo_fotografia'];
					$siete_in = $fotografia['percorso_completo'];
				}
			} // fotografia 

			if ($oggetto_richiesta == 'album'){
				$rec_album= $alb_h->get_album_from_id($oggetto_id);
				if (isset($rec_album['error'])){
					http_response_code(404);
					$ret = '<p style="font-family:monospace;color:red;">'
					. 'Errore in lettura richieste'.'</p>'
					. '<p>'.$rec_album['message'].'</p>'	
					. '<p>album_id: '.$oggetto_id.'</p>';	
					echo $ret;
					exit(1);
				}
				if ($rec_album['numero']>0){
					$album=$rec_album['data'][0];
					$titolo   = $album['titolo_album'];
					$siete_in = $album['percorso_completo'];
				}
			} // album 

			if ($oggetto_richiesta == 'video'){
				$ret_video= $vid_h->get_video_from_id($oggetto_id);
				if (isset($ret_video['error'])){
					http_response_code(404);
					$ret = '<p style="font-family:monospace;color:red;">'
					. 'Errore in lettura richieste' . '</p>'
					. '<p>'.$ret_video['message'].'</p>'	
					. '<p>video_id: '.$oggetto_id.'</p>';	
					echo $ret;
					exit(1);
				}
				if ($ret_video['numero']>0){
					$video=$ret_video['data'][0];
					$titolo   = $video['titolo_video'];
					$siete_in = $video['percorso_completo'];
				}
			} // video
	
			// 
			$rec = '<tr><td>'.($j + 1).'. '. $titolo . '<br>'."\n"
			. 'Siete in: '.$siete_in . '<br>'."\n"
			. 'Richiesta del: ' . $data_richiesta 
			. '</td><td nowrap>'. "\n"
			. '<a class="btn btn-success conferma-richiesta" href="'
			. URLBASE.'richieste.php/conferma-richiesta/'.$richiesta_id
			. '" role="button"><i class="bi bi-hand-thumbs-up-fill"></i></a>&nbsp;&nbsp;' 
			. '<a class="btn btn-danger rifiuta-richiesta" href="'
			. URLBASE.'richieste.php/rifiuta-richiesta/'.$richiesta_id
			. '" role="button"><i class="bi bi-hand-thumbs-down-fill"></i></a>' . "\n"
			. '</td></tr>' . "\n";
			// echo htmlentities($rec);

			$elenco_richieste .= $rec;
	
			// echo '<br>for consultatori '.$j;
		} // 2.for - richieste del consultatore_id '' si "" no

		$temp= str_ireplace('[nome_consultatore]', $nome_consultatore, $blocco_consultatore);
		$temp= str_ireplace('[elenco_richieste]',  $elenco_richieste, $temp);
		//  echo htmlentities($temp);
		$tabelle_richieste .= "\n".$temp;

	} // 1.for - consultatori

	require_once(ABSPATH.'aa-view/richieste-amministratore-view.php');
	exit(0);
} // get_elenco_richieste_per_amministratore()

/** TEST 
 * http://localhost:8888/AMUVEFO-sigec-php/aa-controller/richieste-controller.php?test=get_elenco_richieste_per_amministratore
 * https://archivio.athesis77.it/aa-controller/richieste-controller.php?test=get_elenco_richieste_per_amministratore
 * 
 */
	if (isset($_GET['test'])     && 
	    $_GET['test']='get_elenco_richieste_per_amministratore'){
		echo get_elenco_richieste_per_amministratore();		
		echo "fine.";
		exit(0);
	}
//

<<<<<<< Updated upstream
function conferma_richiesta_per_id(int $richiesta_id, array $dati_input) : array{
	$dbh   = new DatabaseHandler();
	$con_h = new Consultatori($dbh); 
	$ric_h = new Richieste($dbh); 
	$alb_h = new Album($dbh); 
	$fot_h = new Fotografie($dbh); 
	$vid_h = new Video($dbh); 

	$ret_ric = $ric_h->get_richiesta_from_id($richiesta_id);
	if (isset($ret_ric['error'])){
		http_response_code(404);
		$ret = '<p style="font-family:monospace;color:red;">' . __FUNCTION__
		. ' Errore in lettura richiesta' . '</p>'
		. '<p>'.$ret_ric['message'].'</p>'	
		. '<p>Richiesta_id: '.$richiesta_id.'</p>';	
		echo $ret;
		exit(1);
	}
	if ($ret_ric['numero'] == 0){
		http_response_code(404);
		$ret = '<p style="font-family:monospace;color:red;">' . __FUNCTION__
		. ' Errore in lettura richiesta' . '</p>'
		. '<p>Richiesta_id: '.$richiesta_id.'</p>';	
		echo $ret; 
		exit(1);
	}
	$richiesta=$ret_ric['data'][0];
	if (isset($dati_input['motivazione'])){
		// aggiorniamo e torniamo a elenco amministratore 
		$campi=[];
		$campi['update'] = 'UPDATE ' . Richieste::nome_tabella 
		. ' SET richiesta_evasa_il = :richiesta_evasa_il, '
		. ' record_id_amministratore = :record_id_amministratore, '
		. ' motivazione = :motivazione '
		. ' WHERE record_id = :record_id ';
		$campi['richiesta_evasa_il'] = $dbh->get_datetime_now();
		$campi['record_id_amministratore'] = $_COOKIE['consultatore_id'];
		$campi['motivazione'] = $dati_input['motivazione'];
		$campi['record_id'] = $richiesta_id;
		$ric_agg = $ric_h->modifica($campi);
		if (isset($ret_con['error'])){
			http_response_code(404);
			$ret = '<p style="font-family:monospace;color:red;">'
			. 'Errore in aggiornamento' . '</p>'
			. '<p>'.$ric_agg['message'].'</p>'	
			. '<p>Richiesta_id: '.$richiesta_id.'</p>';	
			echo $ret;
			exit(1);
		}
		// si torna 
		header('Location: '.URLBASE.'richieste.php/elenco-amministratore/');
		exit(0);
	} // aggiornamento 

	// si prepara il modulo per la conferma 
	$consultatore_id = $richiesta['record_id_richiedente'];
	$ret_con = $con_h->get_consultatore_from_id($consultatore_id);
	if (isset($ret_con['error'])){
		http_response_code(404);
		$ret = '<p style="font-family:monospace;color:red;">'
		. 'Errore in lettura consultatori' . '</p>'
		. '<p>'.$ret_con['message'].'</p>'	
		. '<p>Consultatore: '.$consultatore_id.'</p>';	
		echo $ret;
		exit(1);
	}
	if ($ret_con['numero'] == 0){
		http_response_code(404);
		$ret = '<p style="font-family:monospace;color:red;">'
		. 'Errore in lettura consultatori' . '</p>'
		. '<p>Consultatore: '.$consultatore_id.'</p>';	
		echo $ret;
		exit(1);
	}
	$consultatore= $ret_con['data'][0];
	$richiedente = $consultatore['cognome_nome'];
	$oggetto_richiesta = $richiesta['oggetto_richiesta'];
	$oggetto_id        = $richiesta['record_id_richiesta'];
	if ($oggetto_richiesta== 'fotografie'){
		$ret_ogg = $fot_h->get_fotografia_from_id($oggetto_id);
		if (isset($rec_ogg['numero']) && $rec_ogg['numero'] > 0) {
			$oggetto = $ret_ogg['data'][0];
			$oggetto_richiesta = $richiesta_id .' '. $oggetto_richiesta 
			. '<br>Titolo: '. $oggetto['titolo_fotografia'] 
			. '<br>Siete in: ' . $oggetto['percorso_completo'];
		}
	}
	if ($oggetto_richiesta== 'album'){
		$ret_ogg = $alb_h->get_album_from_id($oggetto_id);
		if (isset($rec_ogg['numero']) && $rec_ogg['numero'] > 0) {
			$oggetto = $ret_ogg['data'][0];
			$oggetto_richiesta = $richiesta_id .' '. $oggetto_richiesta 
			. '<br>Titolo: '. $oggetto['titolo_album'] 
			. '<br>Siete in: ' . $oggetto['percorso_completo'];
		}
	}
	if ($oggetto_richiesta== 'video'){
		$ret_ogg = $vid_h->get_video_from_id($oggetto_id);
		if (isset($rec_ogg['numero']) && $rec_ogg['numero'] > 0) {
			$oggetto = $ret_ogg['data'][0];
			$oggetto_richiesta = $richiesta_id .' '. $oggetto_richiesta 
			. '<br>Titolo: '. $oggetto['titolo_video'] 
			. '<br>Siete in: ' . $oggetto['percorso_completo'];
		}
	}
	if (isset($ret_ogg['error'])){
		http_response_code(404);
		$ret = '<p style="font-family:monospace;color:red;">'
		. 'Errore in lettura ' .$oggetto_richiesta. '</p>'
		. '<p>'.$ret_ogg['message'].'</p>'	
		. '<p>oggetto_id: '.$oggetto_id.'</p>';	
		echo $ret;
		exit(1);
	}
	// via si va - esposizione modulo 
	require_once(ABSPATH.'aa-view/richieste-conferma-view.php');
	exit(0);
	
} // conferma_richiesta_per_id()

/** TEST
 * https://archivio.athesis77.it/aa-controller/richieste-controller.php?id=2&test=conferma_richiesta_per_id
 */
	if (isset($_GET['test'])     && 
	    $_GET['test']='get_elenco_richieste_per_amministratore'){
		echo get_elenco_richieste_per_amministratore();		
		echo "fine.";
		exit(0);
	}
//

function respinta_richiesta_per_id(int $richiesta_id, array $dati_input){
	$dbh   = new DatabaseHandler();
	$con_h = new Consultatori($dbh); 
	$ric_h = new Richieste($dbh); 
	$alb_h = new Album($dbh); 
	$fot_h = new Fotografie($dbh); 
=======

/**
 * Se i dati del modulo mancano propone la scheda 
 */
function set_conferma_richiesta_per_id(int $richiesta_id, array $dati_input) {
	$dbh   = new DatabaseHandler();
	$alb_h = new Album($dbh); 
	$con_h = new Consultatori($dbh); 
	$fot_h = new Fotografie($dbh); 
	$ric_h = new Richieste($dbh); 
>>>>>>> Stashed changes
	$vid_h = new Video($dbh); 

	$ret_ric = $ric_h->get_richiesta_from_id($richiesta_id);
	if (isset($ret_ric['error'])){
		http_response_code(404);
		$ret = '<p style="font-family:monospace;color:red;">' . __FUNCTION__
		. ' Errore in lettura richiesta' . '</p>'
		. '<p>'.$ret_ric['message'].'</p>'	
		. '<p>Richiesta_id: '.$richiesta_id.'</p>';	
		echo $ret;
		exit(1);
	}
	if ($ret_ric['numero'] == 0){
		http_response_code(404);
		$ret = '<p style="font-family:monospace;color:red;">' . __FUNCTION__
		. ' Errore in lettura richiesta' . '</p>'
		. '<p>Richiesta_id: '.$richiesta_id.'</p>';	
		echo $ret; 
		exit(1);
	}
	$richiesta=$ret_ric['data'][0];
<<<<<<< Updated upstream
	if (isset($dati_input['motivazione'])){
		// aggiorniamo e torniamo a elenco amministratore 
		$campi=[];
		$campi['update'] = 'UPDATE ' . Richieste::nome_tabella 
		. ' SET richiesta_evasa_il = :richiesta_evasa_il, '
		. ' record_id_amministratore = :record_id_amministratore, '
		. ' motivazione = :motivazione '
		. ' WHERE record_id = :record_id ';
		$campi['richiesta_evasa_il'] = $dbh->get_datetime_now();
		$campi['record_id_amministratore'] = $_COOKIE['consultatore_id'];
		$campi['motivazione'] = $dati_input['motivazione'];
		$campi['record_id'] = $richiesta_id;
		$ric_agg = $ric_h->modifica($campi);
		if (isset($ret_con['error'])){
			http_response_code(404);
			$ret = '<p style="font-family:monospace;color:red;">'
			. 'Errore in aggiornamento' . '</p>'
			. '<p>'.$ric_agg['message'].'</p>'	
			. '<p>Richiesta_id: '.$richiesta_id.'</p>';	
			echo $ret;
			exit(1);
		}
		// si torna 
		header('Location: '.URLBASE.'richieste.php/elenco-amministratore/');
		exit(0);
	} // aggiornamento 

	// si prepara il modulo per la respinta
	$consultatore_id = $richiesta['record_id_richiedente'];
	$ret_con = $con_h->get_consultatore_from_id($consultatore_id);
	if (isset($ret_con['error'])){
		http_response_code(404);
		$ret = '<p style="font-family:monospace;color:red;">'
		. 'Errore in lettura consultatori' . '</p>'
		. '<p>'.$ret_con['message'].'</p>'	
		. '<p>Consultatore: '.$consultatore_id.'</p>';	
		echo $ret;
		exit(1);
	}
	if ($ret_con['numero'] == 0){
		http_response_code(404);
		$ret = '<p style="font-family:monospace;color:red;">'
		. 'Errore in lettura consultatori' . '</p>'
		. '<p>Consultatore: '.$consultatore_id.'</p>';	
		echo $ret;
		exit(1);
	}
	$consultatore= $ret_con['data'][0];
	$richiedente = $consultatore['cognome_nome'];
	$oggetto_richiesta = $richiesta['oggetto_richiesta'];
	$oggetto_id        = $richiesta['record_id_richiesta'];
	if ($oggetto_richiesta== 'fotografie'){
		$ret_ogg = $fot_h->get_fotografia_from_id($oggetto_id);
		if (isset($rec_ogg['numero']) && $rec_ogg['numero'] > 0) {
			$oggetto = $ret_ogg['data'][0];
			$oggetto_richiesta = $richiesta_id .' '. $oggetto_richiesta 
			. '<br>Titolo: '. $oggetto['titolo_fotografia'] 
			. '<br>Siete in: ' . $oggetto['percorso_completo'];
		}
	}
	if ($oggetto_richiesta== 'album'){
		$ret_ogg = $alb_h->get_album_from_id($oggetto_id);
		if (isset($rec_ogg['numero']) && $rec_ogg['numero'] > 0) {
			$oggetto = $ret_ogg['data'][0];
			$oggetto_richiesta = $richiesta_id .' '. $oggetto_richiesta 
			. '<br>Titolo: '. $oggetto['titolo_album'] 
			. '<br>Siete in: ' . $oggetto['percorso_completo'];
		}
	}
	if ($oggetto_richiesta== 'video'){
		$ret_ogg = $vid_h->get_video_from_id($oggetto_id);
		if (isset($rec_ogg['numero']) && $rec_ogg['numero'] > 0) {
			$oggetto = $ret_ogg['data'][0];
			$oggetto_richiesta = $richiesta_id .' '. $oggetto_richiesta 
			. '<br>Titolo: '. $oggetto['titolo_video'] 
			. '<br>Siete in: ' . $oggetto['percorso_completo'];
		}
	}
	if (isset($ret_ogg['error'])){
		http_response_code(404);
		$ret = '<p style="font-family:monospace;color:red;">'
		. 'Errore in lettura ' .$oggetto_richiesta. '</p>'
		. '<p>'.$ret_ogg['message'].'</p>'	
		. '<p>oggetto_id: '.$oggetto_id.'</p>';	
		echo $ret;
		exit(1);
	}
	// via si va - esposizione modulo 
	require_once(ABSPATH.'aa-view/richieste-respinta-view.php');
	exit(0);
	
} // respinta_richiesta_per_id()
=======
	// per modulo 
	if (!isset($_POST['motivazione'])){		
		$richiedente_id = $richiesta['record_id_richiedente'];
		$ret_con= $con_h->get_consultatore_from_id($richiedente_id);
		if (isset($ret_con['error'])){
			http_response_code(404);
			$ret = '<p style="font-family:monospace;color:red;">'
			. 'Errore in lettura consultatori' . '</p>'
			. '<p>'.$ret_con['message'].'</p>'	
			. '<p>Consultatore: '.$richiedente_id.'</p>';	
			echo $ret;
			exit(1);
		}
		if ($ret_con['numero'] == 0){
			http_response_code(404);
			$ret = '<p style="font-family:monospace;color:red;">'
			. 'Errore in lettura consultatori' . '</p>'
			. '<p>Consultatore: '.$richiedente_id.'</p>';	
			echo $ret;
			exit(1);
		}
		$richiedente=$ret_con['data'][0]['cognome_nome'];
		$oggetto_richiesta=$richiesta['oggetto_richiesta'];
		$oggetto_id       =$richiesta['record_id_richiesta'];
		if ($oggetto_richiesta == 'fotografie'){
			$ret_ogg = $fot_h->get_fotografia_from_id($oggetto_id);
			if (isset($ret_ogg['numero']) && $ret_ogg['numero'] > 0){
				$oggetto=$ret_ogg['data'][0];
				$titolo   = $oggetto['titolo_fotografia'];
				$siete_in = $oggetto['percorso_completo'];
			}
		}
		if ($oggetto_richiesta == 'album'){
			$ret_ogg = $alb_h->get_album_from_id($oggetto_id);
			if (isset($ret_ogg['numero']) && $ret_ogg['numero'] > 0){
				$oggetto=$ret_ogg['data'][0];
				$titolo   = $oggetto['titolo_album'];
				$siete_in = $oggetto['percorso_completo'];
			}
		}
		if ($oggetto_richiesta == 'video'){
			$ret_ogg = $vid_h->get_video_from_id($oggetto_id);
			if (isset($ret_ogg['numero']) && $ret_ogg['numero'] > 0){
				$oggetto=$ret_ogg['data'][0];
				$titolo   = $oggetto['titolo_video'];
				$siete_in = $oggetto['percorso_completo'];
			}
		}
		if (isset($ret_['error'])){
			http_response_code(404);
			$ret = '<p style="font-family:monospace;color:red;">'
			. 'Errore in lettura richieste'.'</p>'
			. '<p>'.$ret_ogg['message'].'</p>'	
			. '<p>richiesta_id: '.$richiesta_id.'</p>';	
			echo $ret;
			exit(1);
		}
		require_once(ABSPATH.'aa-view/richieste-conferma-view.php');
		exit(0);		
	} // esposizione modulo
	
	$ric_h->set_record_id($richiesta_id);
	$ric_h->set_record_id_amministratore($_COOKIE['consultatore_id']);
	$ric_h->set_richiesta_evasa_il($dbh->get_datetime_now());
	$ric_h->set_motivazione($_POST['motivazione']);
	$campi=[];
	$campi['update'] = 'UPDATE ' . Richieste::nome_tabella 
	. ' SET record_id_amministratore = :record_id_amministratore, '
	.     ' richiesta_evasa_il = :richiesta_evasa_il, '
	.     ' motivazione = :motivazione '
	. ' WHERE record_id = :record_id ';
	$campi['record_id_amministratore'] = $ric_h->get_record_id_amministratore();
	$campi['richiesta_evasa_il'] = $ric_h->get_richiesta_evasa_il();
	$campi['motivazione'] = $ric_h->get_motivazione();
	$campi['record_id'] = $ric_h->get_record_id();
	// ultima_modifica_record viene aggiornato in automatico 
	$ret_agg = $ric_h->modifica($campi);
	if (isset($ret_agg['error'])){
		http_response_code(404);
		$ret = '<p style="font-family:monospace;color:red;">'
		. 'Errore in aggiornamento richieste'.'</p>'
		. '<p>'.$ret_agg['message'].'</p>'	
		. '<p>richiesta_id: '.$richiesta_id.'</p>';	
		echo $ret;
		exit(1);
	}
	// 
	// aggiornamento completato si torna all'elenco
	$_SESSION['messaggio'] = 'Aggiornamento completato';
	get_elenco_richieste_per_amministratore();
	exit(0);

	} // set_conferma_richiesta_per_id()
>>>>>>> Stashed changes
