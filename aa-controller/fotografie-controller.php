<?php
/**
 * @source /aa-controller/fotografie-controller.php
 * @author Massimo rainato <maxrainato@libero.it>
 * 
 * FOTOGRAFIE controller
 * 
 * Si occupa delle funzioni che riguardano la tabella fotografie 
 * e fotografie_dettagli 
 * 
 * - carica_fotografie_da_scansioni_disco        $scansioni_id 
 *   legge scansioni_disco scrive album scrive fotografie 
 * - carica_fotografie_da_album                  $album_id
 *   legge scansioni_disco scrive album scrive fotografie 
 * 
 * - leggi_fotografie_per_id 
 *   presenta pagina della fotografia 
 * - carica_richiesta_fotografie_per_id
 *   scrive richiesta in richieste 
 * - leggi_fotografia_precedente
 *   nella pagina fotografia va a cercare la fotografia precedente 
 * - leggi_fotografia_seguente 
 *   nella pagina fotografia va a cercare la fotografia seguente
 * 
 * - aggiungi_fotografie_dettagli_da_modulo
 *   presenta pagina per inserire dettaglio 
 *   aggiunge dettaglio da modulo per inserire dettaglio 
 * - modifica_fotografie_dettagli_da_modulo
 *   aggiorna dettaglio da modulo modifica dettaglio 
 *   se mancano i dati espone il modulo di modifica dettaglio 
 * - elimina_dettaglio_fotografia
 *   cancellazione non fisica del dettaglio 
 * 
 * - carica_fotografie_dettagli
 *   viene richiamata da carica_dettagli_da_fotografia
 *   e in molti alti punti
 * 
 * - carica_dettagli_da_fotografia            $fotografia_id
 *   rintraccia il file ed estrae dei dettagli in automatico
 *   da inserire in fotografie_dettagli
 * 
 */
if (!defined('ABSPATH')){
	include_once('../_config.php');
}
include_once(ABSPATH . 'aa-model/database-handler-oop.php');
include_once(ABSPATH . 'aa-model/fotografie-oop.php');
include_once(ABSPATH . 'aa-model/fotografie-dettagli-oop.php');
include_once(ABSPATH . 'aa-model/scansioni-disco-oop.php');
include_once(ABSPATH . 'aa-model/richieste-oop.php');
include_once(ABSPATH . 'aa-model/album-dettagli-oop.php');
include_once(ABSPATH . 'aa-model/chiavi-oop.php');
include_once(ABSPATH . 'aa-model/didascalie-oop.php');
include_once(ABSPATH . 'aa-controller/controller-base.php');
include_once(ABSPATH . 'aa-controller/carica-dettaglio-libreria.php');

/**
 * CREATE - aggiungi 
 * Fotografie  
 * Legge scansioni_disco scrive fotografie 
 * TODO Ma una giornata di lavoro e ...non è usato da nessuno?
 * @param  int   $scansioni_id  id dell'album in scansioni_disco
 * @return array 'ok' + 'message' | 'error' + 'message' 
 */
function carica_fotografie_da_scansioni_disco_con_id( int $scansioni_id ) : array {
	$dbh    = New DatabaseHandler();
	$scan_h = New ScansioniDisco($dbh);
	$alb_h  = New Album($dbh);
	$foto_h = New Fotografie($dbh);
	/* 
	  1. in scansioni_disco cerca scansioni_id 
	  2. in album cerca record_id_in_scansioni_disco
	  3. in scansioni disco cerca le fotografie dentro album
	  4. mette le fotografie da scansioni_disco in fotografie
	  5. (manca) mette i video da scansioni_disco in video 
	 */
	
	$titolo_pagina = 'Caricamento fotografia da deposito ' . $scansioni_id;
	$inizio_pagina = file_get_contents(ABSPATH.'aa-view/reload-5sec-view.php');
	$inizio_pagina = str_ireplace('<?=$titolo_pagina; ?>', $titolo_pagina, $inizio_pagina);
	echo $inizio_pagina;

	echo "<p class='text-monospace'>".__FUNCTION__." avvio :<br>"
	. 'input:'. $scansioni_id.'</p>';

	// verifica id in scansioni_disco 
	$ret_scan = $scan_h->get_scansioni_disco_per_id($scansioni_id);
	if (isset($ret_scan['error'])){
		echo '<div class="alert alert-danger" role="alert">'
		. ' Si è verificato un evento bloccante: '
		. '<br>' . $ret_scan['message'] 
		. '<br>STOP'
		. '</div>';
		exit(1);
	}
	
	$scansione_disco = $ret_scan['record'];
	echo '<p class="text-monospace">Scansioni_disco:<br>'
	. str_ireplace(';', '; ', serialize($ret_scan)).'</p>';

	// lettura in album tramite chiave esterna record_di_in_scansioni_disco 
	$ret_alb = $alb_h->get_album_from_scansioni_id($scansioni_id);
	if ( isset($ret_alb['error'])){
		echo '<div class="alert alert-danger" role="alert">'
		. ' Si è verificato un evento bloccante: '
		. '<br>' . $ret_alb['message'] 
		. '<br>STOP'
		. '</div>';
		exit(1);
	}
	
	$album = $ret_alb['record'];
	echo '<p class="text-monospace">Album:<br>'
	. str_replace(';', '; ', serialize($album)).'</p>';

	// Elenco da scansioni_disco di fotografie e video (no, i video no)
	$ret_scan = $scan_h->get_scansioni_disco_foto_da_album($album);
	if ( isset($ret_scan['error'])){
		echo '<div class="alert alert-danger" role="alert">'
		. ' Si è verificato un evento bloccante: '
		. '<br>' . $ret_scan['message'] 
		. '<br>STOP'
		. '</div>';
		exit(1);
	}

	// A "metter dentro" da scansioni_disco nella tabella delle fotografie
	$fotografia=[];
	$fotografia['record_id_in_album']     = $album['record_id'];
	$fotografia['disco']                  = $album['disco'];
	$ret=[];
	$ret['ok'] = true;
	$ret['numero'] = 0;
	$ret['data'] = [];
	echo '<ol>';
	for ($i=0; $i < count($ret_scan['data']); $i++) { 

		$record_id_in_scansioni_disco = $ret_scan['data'][$i]['record_id'];
		$ret_foto = $foto_h->get_fotografia_from_scansioni_id($record_id_in_scansioni_disco);

		if (isset($ret_foto['numero']) && $ret_foto['numero'] > 1){
			// troppa roba, si cancella il superfluo
			$campi=[];
			$campi['update'] = 'UPDATE ' . Fotografie::nome_tabella
			. ' SET record_cancellabile_dal = CURRENT_TIMESTAMP '
			. ' WHERE record_id_in_scansioni_disco = :record_id_in_scansioni_disco '
			. ' AND record_cancellabile_dal = :record_cancellabile_dal '
			. ' AND record_id > :record_id ';
			$campi['record_id_in_scansioni_disco'] = $ret_foto['data'][0]['record_id_in_scansioni_disco'];
			$campi['record_cancellabile_dal'] = $ret_foto['data'][0]['record_cancellabile_dal'];
			$campi['record_id'] = $ret_foto['data'][0]['record_id'];
			$ret_del = $foto_h->modifica($campi);
			if ( isset($ret_del['error'])){
				echo '<div class="alert alert-danger" role="alert">'
				. ' Si è verificato un evento bloccante: '
				. '<br>' . $ret_del['message'] 
				. '<br>STOP'
				. '</div>';
				exit(1);
			}
			// ne è restato solo uno
			continue;
		}

		if (isset($ret_foto['numero']) && $ret_foto['numero'] == 0){
			// manca si può inserire 
			$fotografia['titolo_fotografia'] = $ret_scan['data'][$i]['nome_file'];
			$fotografia['percorso_completo'] = $album['percorso_completo'].$fotografia['titolo_fotografia'];
			$fotografia['record_id_in_scansioni_disco'] = $ret_scan['data'][$i]['record_id'];
			$ret_foto = [];
			$ret_foto = $foto_h->aggiungi($fotografia);
			
			echo '<li style="font-family:monospace">Fotografia:<br>'
			. str_replace(';', '; ', serialize($ret_foto)).'</li>';	
			
			if (isset($ret_foto['ok'])){
				$ret['numero']++;
				$ret['data'][] = $fotografia['titolo_fotografia'];
			}
			// cambio stato in tabella scansioni_disco (...) > lavori completati 
			$ret_stato = $scan_h->set_stato_lavori_in_scansioni_disco($fotografia['record_id_in_scansioni_disco'], ScansioniDisco::stato_completati);
			if (isset($ret_stato['error'])){
				echo '<div class="alert alert-danger" role="alert">'
				. ' Si è verificato un evento bloccante: '
				. '<br>' . $ret_stato['message'] 
				. '<br>' . str_ireplace(';', '; ', serialize($ret_stato)) 
				. '<br>STOP'
				. '</div>';
				exit(1);
			}
			continue;
		}
		if (isset($ret_stato['error'])){
			echo '<div class="alert alert-danger" role="alert">'
			. ' Si è verificato un evento bloccante: '
			. '<br>' . $ret_stato['message'] 
			. '<br>' . str_ireplace(';', '; ', serialize($ret_stato)) 
			. '<br>STOP'
			. '</div>';
			exit(1);
		}
		echo '<li style="font-family:monospace">Fotografia:<br>'
		. str_replace(';', '; ', serialize($ret_foto)).' - già presente </li>';
	}
	echo '</ol>';

	echo "<p class='text-monospace w75'>" . __FUNCTION__ 
	. " uscita :<br>"
	. str_replace(';', '; ', serialize($ret)).'</p>';

	return $ret; 
} // carica_fotografie_da_scansioni_disco_con_id



/**
 * CREATE - aggiungi 
 * Legge album scrive fotografie 
 * 
 * Parte da un album registrato in scansioni_disco 
 * aggiorna album in album
 * aggiorna fotografie in fotografie 
 */
function carica_fotografie_da_album(int $album_id = 0 ) : array {
	$dbh    = New DatabaseHandler();
	$alb_h  = New Album($dbh);
	$foto_h = New Fotografie($dbh);
	$scan_h = New ScansioniDisco($dbh);
	// si possono usare per gli echo le classi bootstrap
	/**
	 * 1. check album_id oppure trova il primo da lavorare 
	 * 2. da lettura album in album esco con lettura album in scansioni_disco
	 * 3. lettura fotografie da deposito scansioni_disco
	 * 
	 */

	// 1. check album id / trova album id 
	$campi=[];
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
	if ($album_id == 0 ){
		// prende il primo "da fare" 
		$campi['query']= 'SELECT * FROM ' . Album::nome_tabella
		. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
		. ' AND stato_lavori = :stato_lavori '
		. ' ORDER BY record_id ';
		$campi['stato_lavori'] = Album::stato_da_fare;

	} else {
		// verifica album_id
		$campi['query']= 'SELECT * FROM ' . Album::nome_tabella
		. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
		. ' AND record_id = :record_id';
		$campi['record_id']               = $album_id;
	}
	$ret_alb = $alb_h->leggi($campi);
	if (isset($ret_alb['error'])){
		$ret = [
			'error'   => true,
			'message' => 'Non è stato trovato un album da inserire.'
			. ' campi: ' . str_ireplace(';', '; ', serialize($campi)) 
			. ' ' . $ret_alb['error']
		];
		return $ret;
	}
	if ($ret_alb['numero'] == 0){
		$ret = [
			'error'   => true,
			'message' => 'Non è stato trovato un album da inserire.'
			. ' campi: ' . str_ireplace(';', '; ', serialize($campi)) 
		];
		return $ret;
	}
	$album    = $ret_alb['data'][0];
	$album_id = $album['record_id'];
	
	// 2. lettura album caricato in scansioni_disco 
	$campi=[];
	$campi['query'] = 'SELECT * FROM ' . ScansioniDisco::nome_tabella
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. ' AND record_id = :record_id ';
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
	$campi['record_id']               = $album['record_id_in_scansioni_disco'];
	$ret_scan = $scan_h->leggi($campi);

	if (isset($ret_scan['error'])){
		$ret = [
			'error'  => true,
			'message'=> 'Non è stato trovato un album in scansioni_disco.'
			. '<br>Errore: ' . $ret_scan['error']
			. '<br>campi: ' . str_ireplace(';', '; ', serialize($ret_scan)) 
		];
		return $ret;
	}
	if ($ret_scan['numero'] == 0){
		$ret = [
			'error'  => true,
			'message'=> 'Non è stato trovato un album in scansioni_disco.'
			. '<br>campi: ' . str_ireplace(';', '; ', serialize($ret_scan)) 
		];
		return $ret;
	}
	// $album album in album
	$album_in_scansioni = $ret_scan['data'][0];

	// 3. fotografie dell'album in deposito  
	// Tutte quelle che non sono cartella e hanno 
	//   gli stessi livelli della cartella, inserite dopo la cartella
	$campi=[];
	$campi['query']= 'SELECT * FROM ' . ScansioniDisco::nome_tabella
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. ' AND livello1 = :livello1    AND livello2 = :livello2 '
	. ' AND livello3 = :livello3    AND livello4 = :livello4 '
	. ' AND livello5 = :livello5    AND livello6 = :livello6 '
	. " AND nome_file <> '/' "
	. " AND estensione IN ('jpg', 'jpeg', 'psd', 'tif') "
	. ' ORDER BY nome_file, record_id ';
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
		$ret = [
			'error'  => true,
			'message'=> __FUNCTION__ . ' ' . __LINE__
			. '<br>' . $ret_scan['error']
			. '<br>campi: ' . str_ireplace(';', '; ', serialize($campi)) 
		];
		return $ret;
	}
	if ($ret_scan['numero'] == 0){
		$ret = [
			'ok'     => true,
			'message'=> "Non ci sono fotografie per l'album in scansioni_disco. "
			. '<br>campi: ' . str_ireplace(';', '; ', serialize($campi)) 
		];
		return $ret;
	}
	// vai di loop 
	$elenco_nomi_file=[];
	$fotografie_in_scansioni = $ret_scan['data']; 	

	$new_foto=[];
	$new_foto['disco']             =$album_in_scansioni['disco'];
	$new_foto['record_id_in_album']=$album_id;
	// ret     generale dei dati della funzione 
	// ret_foto_old del vecchio record 
	// ret_foto_new del nuovo record 
	$ret=[];
	$ret['data']=[];
	$ret_numero=0;
	for ($i=0; $i < count($fotografie_in_scansioni); $i++) { 
		// alternativa per sgamare doppioni 
		if ( in_array( $fotografie_in_scansioni[$i]['nome_file'], $elenco_nomi_file)){
			continue;
		}
		$elenco_nomi_file[]=$fotografie_in_scansioni[$i]['nome_file'];

		// Prima verificare se in fotografie la fotografia c'è già 
		$campi = [];
		$campi['query'] = 'SELECT * FROM ' . Fotografie::nome_tabella
		. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
		. ' AND record_id_in_album = :record_id_in_album '
		. ' AND percorso_completo LIKE :percorso_completo '
		. ' ORDER BY record_id ';
		$campi['record_cancellabile_dal']     = $dbh->get_datetime_forever();
		$campi['record_id_in_album']          = $album_id;
		$campi['percorso_completo']= '%/'.$fotografie_in_scansioni[$i]['nome_file'];
		$ret_foto_old = $foto_h->leggi($campi);
		if (isset($ret_foto_old['error'])){
			return $ret_foto_old;
		}	
		
		$ret_numero++;
		// Aggiungo se manca!
		if ($ret_foto_old['numero'] < 1){
			// titolo fotografia di partenza è il nome file senza estensione
			// Qualora serva si può modificare aggiungendo lettere accentate e anche emoji 
			$new_foto['titolo_fotografia'] = $fotografie_in_scansioni[$i]['nome_file'];
			$new_foto['titolo_fotografia'] = str_ireplace('.'.$fotografie_in_scansioni[$i]['estensione'], '', $new_foto['titolo_fotografia'] );
			$new_foto['titolo_fotografia'] = trim($new_foto['titolo_fotografia'] );
			// $new_foto['disco'] vedi fuori ciclo ^
			// $new_foto[album_id]
			$new_foto['percorso_completo'] = $album['percorso_completo'] . $fotografie_in_scansioni[$i]['nome_file'];
			$new_foto['record_id_in_scansioni_disco'] = $fotografie_in_scansioni[$i]['record_id'];
	
			$ret_foto_new=[];
			$ret_foto_new = $foto_h->aggiungi($new_foto);
			if (isset($ret_foto_new['error'])){
				return $ret_foto_new;
			}
			$ret['data'][]=$ret_numero .' '.$new_foto['titolo_fotografia'];
		}
		// Se c'è aggiorno lo stato_lavori della fotografia 
		// così è come se lo avessi inserito nuovo 
		// l'aggiornamento del record_id_in_scansioni_disco è temporaneo 
		if ($ret_foto_old['numero'] > 0 ){
			$campi=[];
			$campi['update'] = 'UPDATE ' . Fotografie::nome_tabella
			. ' SET stato_lavori = :stato_lavori '
			. '   , record_id_in_scansioni_disco = :record_id_in_scansioni_disco '
			. ' WHERE record_id = :record_id ';
			$campi['stato_lavori']                 = Fotografie::stato_da_fare;
			$campi['record_id_in_scansioni_disco'] = $fotografie_in_scansioni[$i]['record_id'];
			$campi['record_id']                    = $ret_foto_old['data'][0]['record_id'];
			$ret_foto_mod = [];
			$ret_foto_mod = $foto_h->modifica($campi);
			if (isset($ret_foto_mod['error'])){
				return $ret_foto_mod;
			}
		}
	} // for(fotografie_in_scansioni_disco)
	$ret['numero']=$ret_numero;
	$ret['ok'] = true;
	return $ret;
} // carica_fotografie_da_album



/**
 * LEGGI 
 */
function leggi_fotografie_per_id( int $fotografia_id){
	$dbh    = New DatabaseHandler();
	$foto_h = New Fotografie($dbh);
	$fdet_h = New FotografieDettagli($dbh); 
	$dida_h = New Didascalie($dbh); 

	// 
	$ret_foto = $foto_h->get_fotografia_from_id($fotografia_id);
	if (isset($ret_foto['error']) ){
		echo '<pre style="color: red;">Fotografia non trovata</pre>';
		echo var_dump($ret_foto);
		exit(0);
	}
	$fotografia      = $ret_foto['record']; // è sempre un array

	// facile ma consente scarico facile del file dell'immagine 
	// $fotografia_src  = urldecode($fotografia['percorso_completo']);
	
	// converte il file in un flusso dati che viene dichiarato immagine ed è privo di nome file,
	// anche salvandolo si dovranno rinominare tutti i file e organizzarli a mano. E ce ne sono per dei TeraByte.
	// TODO Per limitare il carico macchina, si potrebbe già salvare un file "sidecar" con estensione .b64
	// TODO dove si trova l'immagine
	$fotografia_src  = str_replace('//' , '/' , ABSPATH.$fotografia['percorso_completo']);

	// i file psd e tif sono sostituiti da miniature jpg 300K vs 40MB
	$fotografia_jpg = str_replace('.psd', '.jpg', $fotografia_src);
	$fotografia_jpg = str_replace('.tif', '.jpg', $fotografia_jpg);
	if (is_file($fotografia_jpg)){
		$fotografia_src = $fotografia_jpg;
	}
	$fotografia_src  = 'data:image/jpeg;base64,'.base64_encode(file_get_contents($fotografia_src));	

	$torna_all_album = URLBASE . 'album.php/leggi/'           . $fotografia['record_id_in_album'];
	$foto_precedente = URLBASE . 'fotografie.php/precedente/' . $fotografia['record_id'];
	$foto_seguente   = URLBASE . 'fotografie.php/seguente/'   . $fotografia['record_id'];
	// siete in ... 
	$siete_in = $fotografia['percorso_completo'];
	$siete_in = dirname($siete_in);
	$siete_in = str_replace('/', ' / ', $siete_in);
	
	$richiesta_originali = '#solalettura';
	$aggiungi_dettaglio  = '#solalettura';
	if (get_set_abilitazione() > SOLALETTURA ){
		$richiesta_originali = URLBASE . 'fotografie.php/richiesta/' . $fotografia['record_id'] 
		. '?return_to=' . urlencode($_SERVER['REQUEST_URI']); // TODO vedi /01-scansioni-disco_richiesta.php come esempio
		
		$aggiungi_dettaglio  = URLBASE . 'fotografie.php/carica-dettaglio/' . $fotografia['record_id'];
	}

	/**
	 * didascalia della fotografia
	 * 1. cerca se è presente un file sidecar
	 *    trovato: lo carica in tabella didascalie ed espone
	 * 2. cerca se è presente un record in tabella didascalie 
	 * 
	 * Nota: il contenuto della didascalia si chiama leggimi, 
	 * perché nel caricamento delle cartelle viene inserito
	 * un file _leggimi.txt con la descrizione del contenuto
	 * della cartella-album. "didascalia" viene riservato al 
	 * record della tabella didascalie, che contiene una colonna
	 * didascalia.
	 */
	$didascalia_id = 0;
	$leggimi = "";

	$leggimi_file = ABSPATH.$fotografia['percorso_completo'];
	$leggimi_file = str_replace('+', ' ', $leggimi_file);
	// verifica se esiste il file sidecar, e si passa il nomefile della fotografia
	$ret_dida = $dida_h->recupera_didascalia($leggimi_file);
	// se torna qualcosa si va a inserire in tabella didascalie 
	if (isset($ret_dida['ok'])){
		$campi=[];
		$campi['tabella_padre']  = 'fotografie';
		$campi['record_id_padre']= $fotografia['record_id'];
		$campi['didascalia']     = $ret_dida['data'][0]['didascalia'];
		$ret_ins_dida = $dida_h->aggiungi($campi);
		if (isset($ret_ins_dida['error'])){
			echo '<p style="font-family:monospace;color: red;">Non è riuscito l inserimento della didascalia '
			. '<br>ret: ' . str_ireplace(';', '; ', serialize($ret_ins_dida))
			. '<br>campi: ' . str_ireplace(';', '; ', serialize($campi))
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
			. '<br>Verifica file: ' . $leggimi_file
			. '</p>';
			exit(1);
		}
	}
	$ret_dida=[];
	// Si cerca se c'è in didascalie 
	if ($didascalia_id == 0){
		$campi=[];
		$campi['tabella_padre']          = 'fotografie';
		$campi['record_id_padre']        = $fotografia['record_id'];
		$campi['record_cancellabile_dal']= $dbh->get_datetime_forever();
		$campi['query'] = "SELECT * FROM " . Didascalie::nome_tabella
		. " WHERE record_cancellabile_dal = :record_cancellabile_dal "
		. " AND tabella_padre = :tabella_padre "
		. " AND record_id_padre = :record_id_padre "
		. " ORDER BY record_id DESC ";
		$ret_dida = $dida_h->leggi($campi);
		if (isset($ret_dida['error'])){
			echo '<p style="font-family:monospace;color: red;">'
			. 'Non è riuscita la lettura della didascalia '
			. '<br>campi: ' . str_ireplace(';', '; ', serialize($ret_dida))
			. '</p>';
			exit(1);
		}
		if ($ret_dida['numero']> 0){
			$didascalia=$ret_dida['data'][0];
			$didascalia_id = $didascalia['record_id'];
			$leggimi       = $didascalia['didascalia'];
		}
	} // lettura didascalia_id e leggimi dalla tabella didascalie

	// dettagli 
	$campi=[];
	$campi['query'] = 'SELECT * FROM ' . FotografieDettagli::nome_tabella
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. ' AND record_id_padre = :record_id_padre '
	. ' ORDER BY chiave, record_id ';
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
	$campi['record_id_padre']         = $fotografia_id;
	$ret_dett = $fdet_h->leggi($campi);
	$dettagli = (isset($ret_dett['numero']) && $ret_dett['numero'] > 0) ? $ret_dett['data'] : [];

	// e via si mostra
	require_once(ABSPATH.'aa-view/foto-view.php');
	exit(0); // e fine
} // leggi_fotografie_per_id()


/**
 * test 
 * 1. https://archivio.athesis77.it/aa-controller/fotografie-controller.php?id=1111&test=leggi_fotografie_per_id
 * atteso: 'fotografia non trovata' (almeno per un po')
 * 
 * 2. https://archivio.athesis77.it/aa-controller/fotografie-controller.php?id=20&test=leggi_fotografie_per_id
 * atteso: trova la fotografia 
 *  
		if (isset($_GET['test']) && 
				isset($_GET['id'])   && 
				$_GET['test'] == 'leggi_fotografie_per_id'){
			//dbg echo '<pre style="max-width:50rem;">'."\n";
			//dbg echo 'test leggi_fotografie_per_id'."\n";
			leggi_fotografie_per_id($_GET['id']);
			//dbg echo 'fine'."\n";
		}
 * 
 * 
 */

/**
 * 
 * @param  int  $fotografia_id 
 * @return bool richiesta inserita 
 * TODO return ret array 'ok' + message | 'error' + message 
 */
function carica_richiesta_fotografie_per_id( int $fotografia_id) : bool {
	$dbh    = New DatabaseHandler();
	$foto_h = New Fotografie($dbh);
	$ric_h  = New Richieste($dbh);

	// 
	$foto_h->set_record_id($fotografia_id); // fa anche validazione
	$campi=[];
	$campi['query']= 'SELECT * from ' . Fotografie::nome_tabella
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. ' AND record_id = :record_id ';
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
	$campi['record_id']               = $foto_h->get_record_id();
	$ret_foto = $foto_h->leggi($campi);
	if (isset($ret_foto['error']) || $ret_foto['numero'] == 0){
		echo '<pre style="color: red;">Fotografia non trovata</pre>';
		exit(0);
	}
	$fotografia = $ret_foto['data'][0]; // è sempre un array


	if (get_set_abilitazione() <= SOLALETTURA ){
		$_SESSION['messaggio'] = 'Operazione non consentita';
		return false;
	}

	$campi=[];
	$campi['record_id_richiedente'] = $_COOKIE['id_calendario'];
	$campi['oggetto_richiesta']     = 'fotografie';
	$campi['record_id_richiesta']   = $fotografia['record_id'];
	$ret_ric = $ric_h->aggiungi($campi);
	if (isset($ret_ric['error'])){
		$_SESSION['messaggio'] = 'Richiesta non inserita '
		. 'per errore ' . $ret_ric['message'];
		return false;
	}  

	$_SESSION['messaggio'] = 'Richiesta inserita di questa foto per '. $_COOKIE['consultatore'];
	return true;
} // carica_richiesta_fotografie_per_id


/**
 * test 
 * 1. https://archivio.athesis77.it/aa-controller/fotografie-controller.php?id=3&test=carica_richiesta_fotografie_per_id
 * 
 * 
		if (isset($_GET['test']) && 
				isset($_GET['id'])   && 
				$_GET['test'] == 'carica_richiesta_fotografie_per_id'){
			//dbg 
			echo '<pre style="max-width:50rem;">'."\n";
			//dbg 
			echo 'test carica_richiesta_fotografie_per_id'."\n";
			carica_richiesta_fotografie_per_id($_GET['id']);
			echo $_SESSION['messaggio'];
			//dbg echo 'fine'."\n";
		}
 * 
 * 
 */


/**
 * PRECEDENTE
 */

/**
 * Se la foto è la prima dell'album resta la foto di partenza 
 * 
 * @param  int $fotografia_id 
 * @return int $fotografia_precedente | fotografie_id 
 */
function leggi_fotografia_precedente( int $fotografia_id) : int {
	$dbh    = New DatabaseHandler();
	$foto_h = New Fotografie($dbh);

	$campi=[];
	$campi['query'] = 'SELECT * from ' . Fotografie::nome_tabella
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. ' AND record_id = :record_id ';
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
	$campi['record_id']      = $fotografia_id;
	//dbg echo var_dump($campi);
	$ret_foto = $foto_h->leggi($campi);
	if (isset($ret_foto['error']) || $ret_foto['numero'] == 0){
		return $fotografia_id;
	}
	//dbg echo '<hr>';
	//dbg echo var_dump($ret_foto);
	$fotografia=$ret_foto['data'][0];
	
	// l'ordinamento è quello in uso nella funzione 
	// leggi_album_per_id dentro album-controller.php 
	$campi=[];
	$campi['query'] = 'SELECT * FROM ' . Fotografie::nome_tabella
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. ' AND record_id_in_album = :record_id_in_album '
	. ' AND titolo_fotografia  < :titolo_fotografia '
	. ' ORDER BY titolo_fotografia DESC, record_id DESC ';
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
	$campi['record_id_in_album']      = $fotografia['record_id_in_album'];
	$campi['titolo_fotografia']       = $fotografia['titolo_fotografia'];
	$ret_foto=[];
	$ret_foto = $foto_h->leggi($campi);
	//dbg echo '<hr>';
	//dbg echo var_dump($ret_foto);
	if (isset($ret_foto['error']) || $ret_foto['numero'] == 0){
		return $fotografia_id;
	}
	$precedente=$ret_foto['data'][0];
	return $precedente['record_id'];
} // leggi_fotografia_precedente


/**
 * SEGUENTE
 */

/**
 * Se la foto è l'ultima dell'album, resta la foto di partenza 
 * 
 * @param  int $fotografia_id 
 * @return int $fotografia_seguente 
 */
function leggi_fotografia_seguente( int $fotografia_id ) : int {
	$dbh    = New DatabaseHandler();
	$foto_h = New Fotografie($dbh);

	$campi=[];
	$campi['query'] = 'SELECT * from ' . Fotografie::nome_tabella
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. ' AND record_id = :record_id ';
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
	$campi['record_id']      = $fotografia_id;
	//dbg echo var_dump($campi);
	$ret_foto = $foto_h->leggi($campi);
	if (isset($ret_foto['error']) || $ret_foto['numero'] == 0){
		return $fotografia_id;
	}
	//dbg echo '<hr>';
	//dbg echo var_dump($ret_foto);
	$fotografia=$ret_foto['data'][0];

	// l'ordinamento è quello in uso nella funzione 
	// leggi_album_per_id dentro album-controller.php 
	$campi=[];
	$campi['query'] = 'SELECT * FROM ' . Fotografie::nome_tabella
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. ' AND record_id_in_album = :record_id_in_album '
	. ' AND titolo_fotografia  > :titolo_fotografia '
	. ' ORDER BY titolo_fotografia ASC, record_id ASC ';
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
	$campi['record_id_in_album']      = $fotografia['record_id_in_album'];
	$campi['titolo_fotografia']       = $fotografia['titolo_fotografia'];
	$ret_foto=[];
	$ret_foto = $foto_h->leggi($campi);
	if (isset($ret_foto['error']) || $ret_foto['numero'] == 0){
		return $fotografia_id;
	}
	$seguente = $ret_foto['data'][0];
	return $seguente['record_id']; 
} // leggi_fotografia_seguente



/**
 * Aggiorna dettaglio fotografia 
 * Se per la modifica del dettaglio mancano i dati input 
 * espone il modulo altrimenti esegue l'aggiornamento 
 * In caso di errore 
 *  
 * @param   int $dettaglio_id 
 * @param array $dati_input
 * @return 'ok' + aggiornato | 'error' + message 
 */
function modifica_fotografie_dettagli_da_modulo(int $dettaglio_id = 0 , array $dati_input = []) : array {
	$dbh    = New DatabaseHandler();
	$fdet_h = New FotografieDettagli($dbh); 

	$titolo_pagina = 'Aggiornamento dettaglio fotografia ' . $dettaglio_id;
	$inizio_pagina = file_get_contents(ABSPATH.'aa-view/reload-5sec-view.php');
	$inizio_pagina = str_ireplace('<?=$titolo_pagina; ?>', $titolo_pagina, $inizio_pagina);


	$ret_det = $fdet_h->get_fotografie_dettagli_per_id( $dettaglio_id );
	if (isset($ret_det['error'])){
		$ret = [
			'error' => true, 
			'message' => __FILE__ . ' ' . __FUNCTION__ 
			. ' Non è stato possibile modificare il dettaglio '
			. ' id: ' . $dettaglio_id
		];
		echo $inizio_pagina;
		echo "\n".'<p class="alert alert-warning" role="alert">'.$ret['message'].'</p>';
		return $ret;
	}
	$dettaglio = $ret_det['record'];
	// se i dati del modulo mancano passo a mostrare il modulo 
	if (!isset($dati_input['aggiorna_dettaglio'])){
		// per la View 
		$leggi_fotografia   = URLBASE . 'fotografie.php/leggi/'.$dettaglio['record_id_padre'];
		$aggiorna_dettaglio = URLBASE . 'fotografie.php/aggiorna_dettaglio/'.$dettaglio['record_id'];
		$record_id     = $dettaglio['record_id'];
		$fotografia_id = $dettaglio['record_id_padre'];
		require_once( ABSPATH . 'aa-view/dettaglio-foto-modifica-view.php');
		exit(0); 
	}
	// Ma devo aggiornare quello che c'era già ? 
	if (strcmp($dettaglio['valore'], $dati_input['valore']) == 0){
		$ret = [
			'ok'     => true,
			'message'=> "Non sono state rilevate modifiche "
		];
		echo $inizio_pagina;
		echo "\n".'<p class="alert alert-warning" role="alert">'.$ret['message'].'</p>';
		return $ret;
	}
	// inserisco il record vecchio - già cancellato a uso backup 
	$dettaglio['record_cancellabile_dal'] = $dbh->get_datetime_now();
	$ret_bak = $fdet_h->aggiungi($dettaglio);
	if (isset($ret_bak['error'])){
		echo $inizio_pagina;
		echo "\n".'<p class="alert alert-warning" role="alert">'.$ret_bak['message'].'</p>';
		return $ret_bak;
	}
	//
	$fdet_h->set_record_id($dati_input['record_id']);
	$fdet_h->set_valore($dati_input['valore']);
	$campi=[];
	$campi['update'] = 'UPDATE fotografie_dettagli '
	. ' SET valore = :valore '
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. ' AND record_id = :record_id ';
	$campi['valore']                  = $fdet_h->get_valore();
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
	$campi['record_id']               = $dettaglio_id;
	$ret_mod = $fdet_h->modifica($campi);
	if (isset($ret_mod['error'])){
		echo $inizio_pagina;
		echo "\n".'<p class="alert alert-warning" role="alert">'.$ret_mod['message'].'</p>';
		$ret = [
			'error' => true, 
			'message' => __FILE__ . ' ' . __FUNCTION__ 
			. ' Non è stato possibile modificare il dettaglio '
			. ' id: ' . $dettaglio_id
		];
		return $ret;
	}
	// torniamo alla scheda fotografia
	leggi_fotografie_per_id($dettaglio['record_id_padre']);
	exit(0);
} // modifica_fotografie_dettagli_da_modulo


/**
 * @param  int  $dettaglio_id 
 * @return void 
 */
function elimina_dettaglio_fotografia( int $dettaglio_id){
	$dbh    = New DatabaseHandler();
	$fdet_h = New FotografieDettagli($dbh); 
	
	$campi=[];
	$campi['query'] = 'SELECT * from ' . FotografieDettagli::nome_tabella
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. ' AND record_id = :record_id ';
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
	$campi['record_id'] = $dettaglio_id;
	$ret_det = $fdet_h->leggi($campi);
	if ( isset($ret_det['error']) || $ret_det['numero'] == 0){
		$ret = [
			'error' => true, 
			'message' => __FILE__ . ' ' . __FUNCTION__ 
			. ' Non è stato possibile modificare il dettaglio '
			. ' per ' . $ret_det['error']
			. ' campi: ' . str_ireplace(';', '; ', serialize($campi))
		];
		echo var_dump($ret);
		exit(0);
	}
	$dettaglio = $ret_det['data'][0];

	$campi=[];
	$campi['update'] = 'UPDATE ' . FotografieDettagli::nome_tabella
	. ' SET record_cancellabile_dal = :record_cancellabile_dal  '
	. ' WHERE record_id = :record_id ';
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_now();
	$campi['record_id']               = $dettaglio_id;
	$ret_mod = $fdet_h->modifica($campi);
	if ( isset($ret_det['error']) || $ret_det['numero'] == 0){
		$ret = [
			'error' => true, 
			'message' => __FILE__ . ' ' . __FUNCTION__ 
			. ' Non è stato possibile modificare il dettaglio '
			. ' per ' . $ret_det['error']
			. ' campi: ' . str_ireplace(';', '; ', serialize($campi))
		];
		echo var_dump($ret);
		exit(0);
	}
	// torniamo alla scheda fotografia 
	leggi_fotografie_per_id($dettaglio['record_id_padre']);
	exit(0);  
} // elimina_dettaglio_fotografia()


/**
 * CREATE - aggiungi 
 * Aggiunge dettaglio da modulo via dati_input 
 * 
 * @param   int $fotografia_id
 * @param array $dati_input
 */
function aggiungi_fotografie_dettagli_da_modulo(int $fotografia_id, ?array $dati_input = []) {
	$dbh    = New DatabaseHandler();
	$foto_h = New Fotografie($dbh);
	$fdet_h = New FotografieDettagli($dbh);
	$chi_h  = New Chiavi($dbh);

	// 1. Recupera la fotografia e se manca ferma tutto 
	$ret_foto = $foto_h->get_fotografia_from_id($fotografia_id);
	if (isset($ret_foto['error'])){
		$ret = '<h2>Errore</h2>'
		. '<p>Non è stato possibile rintracciare la fotografia</p>'
		. '<pre>campi: ' . str_ireplace(';', '; ', serialize($ret_foto));
		echo $ret;
		exit(1);
	}
	$fotografia=$ret_foto['record'];

	
	// Manca $dati_input - presento modulo 
	if (!isset($dati_input['aggiungi_dettaglio'])) {    
		$_SESSION['messaggio'] = "Aggiungi il dettaglio chiave+valore scegliendo la chiave tra quelle "
		. "disponibili, consulta il manuale in caso di dubbi.";
		// per le View 
		$record_id = 0;
		$fotografia_id = $fotografia['record_id'];
		$option_list_chiave = $chi_h->get_chiavi_option_list();
		$option_list_chiave = str_replace("\t", '                  ', $option_list_chiave);
		$leggi_fotografia = URLBASE.'fotografie.php/leggi/'.$fotografia['record_id'];
		$aggiungi_dettaglio = URLBASE.'fotografie.php/aggiungi-dettaglio/'.$fotografia['record_id'];

		require_once( ABSPATH . 'aa-view/dettaglio-foto-aggiungi-view.php');
		exit(0); 
	}

	// inserimento dettaglio 
	$fdet_h->set_record_id_padre($fotografia_id);
	$fdet_h->set_chiave($dati_input['chiave']);
	$fdet_h->set_valore($dati_input['valore']);

	$campi=[];
	$campi['record_id_padre'] = $fdet_h->get_record_id_padre();
	$campi['chiave'] = $fdet_h->get_chiave();
	$campi['valore'] = $fdet_h->get_valore();
	$ret_det = $fdet_h->aggiungi($campi);
	if (isset($ret_det['error'])){
		$_SESSION['messaggio'] = "Non è stato possibile aggiungere il dettaglio.<br><pre>".$ret_det['message'].'</pre>';
		require_once( ABSPATH . 'aa-view/dettaglio-foto-aggiungi-view.php');
		exit(0); 
	}

	// torniamo alla scheda fotografia 
	leggi_fotografie_per_id($fotografia_id);
	exit(0);  
} // aggiungi_fotografie_dettagli_da_modulo


/**
 * CREATE - aggiungi
 * Aggiunge un record alla tabella fotografie_dettagli 
 * 
 * 3 parametri per eseguire un compito ripetitivo 
 * chiamare FotografieDettagli->aggiungi()
 * Aggiornamento: aggiunta lettura Chiavi per stabilire se sia un parametro ripetibile 
 * Aggiunto cancellazione eventuali dettagli gia presenti  
 * Inserimento del dettaglio 
 *  
 * @param  int    $fotografia_id 
 * @param  string $chiave
 * @param  string $valore 
 * @return array  $ret 'ok' + carico effettuato | 'error' + message 
 */
function carica_fotografie_dettagli(int $fotografia_id, string $chiave, string $valore) : array {
	$dbh    = New DatabaseHandler(); // verificare se funziona global 
	$fdet_h = New FotografieDettagli($dbh);
	$chi_h  = New Chiavi($dbh);

	// 1. check chiave 
	$ret_chi = $chi_h->get_chiave_record_per_chiave($chiave);
	if (isset($ret_chi['error'])){
		$ret = '<h2>Errore</h2>'
		. '<p>Non è stato possibile aggiungere un dettaglio<br>'
		. 'errore: ' . $ret_chi['message'] . '<br>'
		. 'chiave: ' . $chiave .'</p>';
		echo $ret;
		return $ret_chi;
	}
	// La chiave c'è, 
	if ($ret_chi['record']['unico'] == 'unico'){
		// 2. se dettaglio "unico" cancello quelli che fossero già presenti
		$fdet_h->set_record_id_padre($fotografia_id);
		$campi=[];
		$campi['update'] = 'UPDATE ' . FotografieDettagli::nome_tabella
		. ' SET record_cancellabile_dal = :record_cancellabile_dal '
		. ' WHERE record_id_padre = :record_id_padre '
		. ' AND chiave = :chiave ' 
		. " AND record_cancellabile_dal = '9999-12-31 23:59:59' ";
		$campi['record_cancellabile_dal'] = $dbh->get_datetime_now();
		$campi['record_id_padre'] = $fdet_h->get_record_id_padre();
		$campi['chiave'] = $chiave;
		$ret_upd = $fdet_h->modifica($campi);
		if (isset($ret_upd['error'])){
			$ret = '<h2>Errore</h2>'
			. '<p>Non è stato possibile aggiungere un dettaglio<br>'
			. 'errore: ' . $ret_upd['message'] . '<br>'
			. 'campi: ' . str_ireplace(';', '; ', serialize($campi)) .'</p>';
			echo $ret;
			return $ret_upd;
		}
		// se ne aggiorna zero va ben lo stesso 
	}
	// 3. aggiungo o inserisco dettaglio
	$fdet_h->set_record_id_padre($fotografia_id);
	$fdet_h->set_chiave($chiave);
	$fdet_h->set_valore($valore);
	$campi=[];
	$campi['record_id_padre']=$fdet_h->get_record_id_padre();
	$campi['chiave']=$fdet_h->get_chiave();
	$campi['valore']=$fdet_h->get_valore();
	$ret_det = $fdet_h->aggiungi($campi);
	if (isset($ret_det['error'])){
		$ret = '<h2>Errore</h2>'
		. '<p>Non è stato possibile aggiungere un dettaglio<br>'
		. 'errore: ' . $ret_det['message'] . '<br>'
		. 'campi: ' . str_ireplace(';', '; ', serialize($campi)) .'</p>';
		echo $ret;
	} else {
		$aggiunti[]= $campi['chiave'].': '.$campi['valore'];
	}
	return $ret_det;
} // carica_fotografie_dettagli 

/**
 * Questa funzione carica i dettagli man mano che vengono individuati 
 * l'alternativa può essere raccogliere tutti i dettagli ed eseguire 
 * un loop di insert con eventuale rollBack
 * 
 * @param  int  $fotografia_id 
 * @return void | echo html messages 
 * 
 * @see https://exiftool.org/TagNames/EXIF.html
 * 
 *  @see https://www.php.net/manual/en/function.iptcparse.php
 * 
 * 
 *  '2#005'=>'DocumentTitle',
 *  '2#010'=>'Urgency',
 *  '2#015'=>'Category',
 *  '2#020'=>'Subcategories',
 *  '2#025'=>'Keywords', 
 *  '2#040'=>'SpecialInstructions',
 *  '2#055'=>'CreationDate',
 *  '2#080'=>'AuthorByline',
 *  '2#085'=>'AuthorTitle',
 *  '2#090'=>'City',
 *  '2#095'=>'State',
 *  '2#101'=>'Country',
 *  '2#103'=>'OTR',
 *  '2#105'=>'Headline',
 *  '2#110'=>'Source',
 *  '2#115'=>'PhotoSource',
 *  '2#116'=>'Copyright',
 *  '2#120'=>'Caption',
 *  '2#122'=>'CaptionWriter'
 *  
 *  Keywords:
 *  $iptc["2#025"][n];   (there is a list of keywords)
 *  
 *  Caption Writer:
 *  $iptc["2#122"][0];
 *
 *  Possono essere codificati UTF8 
 */
function carica_dettagli_da_fotografia(int $fotografia_id ) {
	$dbh    = New DatabaseHandler();
	$foto_h = New Fotografie($dbh);
	$fdet_h = New FotografieDettagli($dbh); 
	$aggiunti=[];
	$larghezza=0;
	$altezza=0;
	$data_evento_prima = '1880-01-01';

	if ($fotografia_id == 0 ){
		// cerca una non lavorata 
		$campi=[];
		$campi['query'] = 'SELECT * FROM ' . Fotografie::nome_tabella
		. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
		. ' AND stato_lavori = :stato_lavori '
		. ' ORDER BY titolo_fotografia '
		. ' LIMIT 1 ';
		$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
		$campi['stato_lavori'] = Fotografie::stato_da_fare;
		
	} else {
		// cerca se la fotografia_id è in tabella fotografie
		$foto_h->set_record_id($fotografia_id);
		$campi=[];
		$campi['query']='SELECT * FROM ' . Fotografie::nome_tabella
		. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
		. ' AND record_id = :record_id ';
		$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
		$campi['record_id'] = $foto_h->get_record_id(); 		
	}	
	// esegui ricerca 
	$ret_foto = $foto_h->leggi($campi);
	if (isset($ret_foto['error'])){
		$ret = '<h2 style="font-family:monospace;">Errore </h2>' 
		. '<p style="font-family:monospace;">Non è stato possibile rintracciare la fotografia indicata.'
		. '<br>campi: ' . str_ireplace(';', '; ', serialize($campi))
		. '<br>' .$ret_foto['message'];
		http_response_code(404);
		echo $ret;
		exit(1);
	}
	if (($fotografia_id == 0) && ($ret_foto['numero'] == 0) ){
		$ret = '<h2 style="font-family:monospace;">avviso</h2>' 
		. '<p>FINE / Non sono state rintracciare fotografie da elaborare.</p>';
		http_response_code(200);
		echo $ret;
		exit(0);
	}
	if ($ret_foto['numero'] == 0 ){
		$ret = '<h2 style="font-family:monospace;">Errore </h2>' 
		. '<p style="font-family:monospace;">Non è stato possibile rintracciare la fotografia indicata.'
		. '<br>campi: ' . str_ireplace(';', '; ', serialize($campi))
		. '<br>ret: ' . str_ireplace(';', '; ', serialize($ret_foto));
		http_response_code(404);
		echo $ret;
		exit(1);
	}
	$fotografia=$ret_foto['data'][0];
	$fotografia_id=$fotografia['record_id'];
	$foto_file = $fotografia['percorso_completo'];
	$foto_file = htmlspecialchars_decode($foto_file); // &amp; > &
	$foto_file = htmlspecialchars_decode($foto_file); // &039; > '
	$foto_file = str_replace('//', '/', ABSPATH.$foto_file);

	/**
	 * restart pagina web dopo 5 secondi
	 */
	$titolo_pagina = 'Caricamento dettagli | Foto Singola';
	$inizio_pagina = file_get_contents(ABSPATH.'aa-view/reload-5sec-view.php');
	$inizio_pagina = str_ireplace('<?=$titolo_pagina; ?>', $titolo_pagina, $inizio_pagina);
	echo $inizio_pagina;
	// si possono usare le classi di bootstrap 

	echo '<p style="font-family:monospace;">'
	. 'Elaborazione fotografia, id:' . $fotografia_id 
	. '<br>file: "' . $foto_file . '".</p>';

	// cambio stato in fotografie (...) > lavori in corso 
	$ret_stato = $foto_h->set_stato_lavori_in_fotografie($fotografia_id, Fotografie::stato_in_corso);
	if (isset($ret_stato['error'])){
		$ret = '<h2>Errore</h2>'
		. '<p>Non è stato possibile cambiare stato_lavori alla fotografia ['. $fotografia_id .']</p>'
		. '<p>Per: ' . $ret_stato['message'];
		echo $ret;
	}
	
	// manca il file? Non lo aggiungo, e pure tolgo il record dalle fotografie
	if (!is_file($foto_file)){
		// cambio stato in fotografie (...) > lavori terminati 
		$ret_stato = $foto_h->set_stato_lavori_in_fotografie($fotografia_id, Fotografie::stato_completati);
		if (isset($ret_stato['error'])){
			$ret = '<h2>Errore</h2>'
			. '<p>Non è stato possibile cambiare stato_lavori alla fotografia ['. $fotografia_id .']</p>'
			. '<p>Per: ' . $ret_stato['message'];
			echo $ret;
		}
		// cancellazione record in fotografia per 
		$campi = [];
		$campi['update'] = 'UPDATE ' . Fotografie::nome_tabella
		. ' SET record_cancellabile_dal = :record_cancellabile_dal '
		. ' WHERE record_id = :record_id ';
		$campi['record_cancellabile_dal'] = $dbh->get_datetime_now();
		$campi['record_id'] = $fotografia_id;
		$ret_foto_del = [];
		$ret_foto_del = $foto_h->modifica($campi);
		// avviso e chiudo.
		$ret = '<h2>Errore</h2>'
		. '<p>Non è stato possibile leggere la fotografia <br>['. $foto_file .']</p>'
		. '<p>campi: ' . str_ireplace(';', '; ', serialize($campi)) . '</p>';
		http_response_code(404);
		echo $ret;
		// avanti il prossimo 
		echo '<script src="'.URLBASE.'aa-view/reload-5sec-jquery.js"></script>';
		exit(0);
	}

	// nome file 	
	$ultima_barra = strrpos($foto_file, '/', 1);
	$nome_file    = trim(substr($foto_file, ($ultima_barra + 1)));
	$album_id     = $fotografia['record_id_in_album'];
	$adet_h       = New AlbumDettagli($dbh);
	
	echo '<p style="font-family:monospace">inizio esame data/evento</p>';
	// dati da nome file se nome standard:
	// aaaa mm gg luogo soggetto contatore
	
	// data/evento non usa carica_fotografie_dettagli
	$data_evento = get_data_evento($nome_file);
	echo '<p style="font-family:monospace">data_evento: '.$data_evento .'</p>'; 
	// le date 0000-00-00 DP vengono ignorate, ma più avanti, non qui 
	if ($data_evento>''){
		$data_evento_album = '';
		// Verificare se è già presente nell'album un dettaglio data/evento e che valore ha
			$campi=[];
			$campi['query']='SELECT * FROM ' . AlbumDettagli::nome_tabella 
			. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
			. ' AND record_id_padre = :record_id_padre '
			. ' AND chiave = :chiave ';
			$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
			$campi['record_id_padre']         = $album_id;
			$campi['chiave']                  = 'data/evento';
			$ret_adet = $adet_h->leggi($campi);
			if (isset($ret_adet['error'])){
				$ret = 'Non è stato trovato un dettaglio album legato a data/evento ' 
				. "per l'errore: " . $ret_adet['error'];
				echo $ret;
				// no exit()
			} elseif ($ret_adet['numero']>0) {
				$data_evento_album = $ret_adet['data'][0]['valore'];
				$ret = 'È stato trovato un dettaglio album legato a data/evento ' 
				. "per l'album: " . $data_evento_album;
				echo $ret;
				//no exit()
			}
			// assente o presente ma diverso, inserisco
			if ($data_evento != $data_evento_album){
				// escludo data 0000-00-00 
				if (!str_contains($data_evento, '0000-')){
					$ret_det   = carica_fotografie_dettagli( $fotografia_id, 'data/evento', $data_evento);

				}
			}
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
		if (str_contains($nome_file, 'DP ') && strpos($nome_file, 'DP ', 0) < 5){ 
			$nome_file = str_replace('DP ', '', $nome_file);
		}
		$nome_file = trim($nome_file);
		echo "<br>Per effetto dell'inserimento di data/elenco, ora nomefile è: " .$nome_file.'<br>';
	} // data/evento - viene escluso 0000-00-00 
	echo '<p style="font-family:monospace">Fine esame data/evento</p>';
	$data_evento_prima = $data_evento;
	if ( str_contains($data_evento, 'decennio')){
		$data_evento_prima=str_replace(' decennio', '-01-01', $data_evento_prima);
	}
	echo '<br>data_evento_prima: ::'.$data_evento_prima .'::'; 

	$ultimo_punto = strrpos($foto_file, '.');
	$estensione   = substr($foto_file, ($ultimo_punto + 1), 6);
	$estensione   = trim(strtolower($estensione));

	if (!in_array($estensione, ['jpg', 'jpeg', 'psd', 'tif', 'tiff'])){
		$ret = '<h2>Errore</h2>'
		. '<p>La fotografia non è in un formato che contiene dati exif ['. $foto_file .']</p>';
		echo $ret;
	}

	/**
	 * EXIF - inizio
	 */
	echo '<p style="font-family:monospace;">Dati EXIF.</p>';
	// 1. cerca se in fotografie_dettagli ci sono già dettagli dedicati exif 
	$campi=[];
	$campi['query']= 'SELECT * FROM '. FotografieDettagli::nome_tabella 
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. ' AND record_id_padre = :record_id_padre '
	. ' ORDER BY chiave, record_id ';
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
	$campi['record_id_padre']         = $foto_h->get_record_id(); 
	$ret_det = $fdet_h->leggi($campi);
	// 2. errore n lettura
	if (isset($ret_det['error'])){
		// cambio stato al record - non lavorabile
		$ret_stato = $foto_h->set_stato_lavori_in_fotografie($fotografia_id, Fotografie::stato_completati);
		if (isset($ret_stato['error'])){
			$ret = '<h2>Errore</h2>'
			. '<p>Non è stato possibile cambiare stato_lavori alla fotografia ['. $fotografia_id .']</p>'
			. '<p>Per: ' . $ret_stato['message'];
			echo $ret;
		} // errore nel cambio stato 

		// ret_det error
		$ret = '<h2>Errore</h2>'
		. '<p>Cercando dettagli exif già registrati si è verificato '
		. 'un errore, chi ci capisce è bravo</p>'
		. '<p>' . $ret_det['message'] . '<br>'
		. 'campi: ' . str_ireplace(';', '; ', serialize($campi)) . '</p>';
		http_response_code(404);
		echo $ret;
		exit(1);
	} // errore in lettura fotografie_dettagli 
	// 3. dati già presenti 
	if ($ret_det['numero'] > 0){
		echo '<h3 style="font-family:monospace;">Avviso</h3>';
		echo '<p style="font-family:monospace;">Saranno aggiunti '
		. 'dettagli ai dettagli già presenti che sono elencati qui sotto.<br>'
		. str_ireplace(';', '; ', serialize($ret_det['data'])).'</p>';
	}

	// 4. lettura dati nel file jpg
	$exif = exif_read_data($foto_file, null, true, false );
	if ($exif === false){
		$ret = '<h2 style="font-family:monospace;">Errore</h2>'
		. '<p style="font-family:monospace;>Cercando dettagli exif nel file si è verificato '
		. 'un errore, non sono stati estratti dati.</p>';
		$exif=[];
		echo $ret;
		// proseguo per dati su file dal nome file 
	}
	echo '<p style="font-family:monospace;width:90%;max-width:90%;">Sono stati rintracciati dati exif <br>';
	$exif_str = serialize($exif);
	$re = '/[^a-zA-Z0-9_\-,\s\/:";%]/i';
	$exif_str = preg_replace($re, ' ', $exif_str); // sostituisco i caratteri non stampabili con spazi
	$exif_str = str_ireplace('s:',' s:',$exif_str); // separo gli indicatori di stringa:lunghezza per consentire l'a-capo
	echo $exif_str . '</p>';

	// oltre i 300dpi sono scansioni 800 dpi 1200 dpi 4000 dpi 

	// Marca e modello: sono scansioni?
	if (isset($exif['IFD0']['Make']) && 
			isset($exif['IFD0']['Model'])){
		$marca = $exif['IFD0']['Make'];
		$modello = $exif['IFD0']['Model'];
		echo '<p style="font-family:monospace;">';
		echo 'Marca: '. $marca . ' Modello: ' . $modello; 
		echo '</p>';
		// scanner Athesis 1 
		if ($marca   == 'Nikon' && 
				$modello == 'Nikon SUPER COOLSCAN 5000 ED'){
			// scansioni
			$ret_det = carica_fotografie_dettagli( $fotografia_id, 'materia/tecnica', 'diapositiva/pellicola');
			$ret_det = carica_fotografie_dettagli( $fotografia_id, 'dimensione/unita-di-misura', 'mm');
			$ret_det = carica_fotografie_dettagli( $fotografia_id, 'dimensione/altezza',  '24 mm');
			$ret_det = carica_fotografie_dettagli( $fotografia_id, 'dimensione/larghezza','36 mm');
			$aggiunti[] = "'materia/tecnica': 'diapositiva/pellicola'";
			$aggiunti[] = "'dimensione/unita-di-misura': 'mm'";
			$aggiunti[] = "'dimensione/altezza': '24 mm'";
			$aggiunti[] = "'dimensione/larghezza': '36 mm'";
		}
		if ($marca   == 'NIKON CORPORATION' && 
				$modello == 'NIKON D100'){
			// scansioni
			$ret_det = carica_fotografie_dettagli( $fotografia_id, 'materia/tecnica', 'file');
			$aggiunti[] = "'materia/tecnica': 'file'";

			$date_det = $exif['EXIF']['DateTimeOriginal'];
			$date_det = data_exif_in_timestamp($date_det); // aaaa:mm:gg > aaaa-mm-gg 
			$ret_det  = carica_fotografie_dettagli( $fotografia_id, 'data/evento', $date_det);
			$aggiunti[] = "'data/evento': ".$date_det;
		}

	} // $exif['IFD0']['Make'] 

	// sequenza di dettagli exif 
	/**
	 * Per le scansioni non interessano i dati di scansione, per le foto native digitali 
	 * non interessano le dimensioni del sensore, insomma non interessano.
		if (isset($exif['COMPUTED'])){
			$ret_det   = carica_fotografie_dettagli( $fotografia_id, 'dimensione/unita-di-misura', 'px');
			$altezza   = $exif['COMPUTED']['Height'];
			$larghezza = $exif['COMPUTED']['Width'];
			// TODO scaglioni 800px 1080px 1440px 1920px 2500px 3000px 4000px 6000px
			$ret_det   = carica_fotografie_dettagli( $fotografia_id, 'dimensione/altezza', $altezza);		
			$ret_det   = carica_fotografie_dettagli( $fotografia_id, 'dimensione/larghezza', $larghezza);		
			$ret_det   = carica_fotografie_dettagli( $fotografia_id, 'dimensione/altezza-larghezza', $altezza . ' x ' . $larghezza);
			$aggiunti[] = "'dimensione/altezza-larghezza': '$altezza x $larghezza'";
		} // exif
	 * 
	 */
	
	// data scansione, spesso
	// Se è uguale non si inserisce un doppione
	if (isset($exif['EXIF']['DateTimeOriginal'])){
		$date_det = $exif['EXIF']['DateTimeOriginal'];
		$date_det = data_exif_in_timestamp($date_det); // aaaa:mm:gg > aaaa-mm-gg 
		if($data_evento_prima < $date_det){
			$ret_det  = carica_fotografie_dettagli( $fotografia_id, 'data/scansione', $date_det);
			$aggiunti[] = "'data/scansione': ".$date_det;
		}
		if($data_evento_prima > $date_det){
			$ret_det  = carica_fotografie_dettagli( $fotografia_id, 'data/evento', $date_det);
			$aggiunti[] = "'data/evento': ".$date_det;
		}
	} // $exif['EXIF']['DateTimeOriginal']
	
	if (isset($exif['FILE']['FileDateTime'])){
		$date_det  = date('Y-m-d H:i:s', $exif['FILE']['FileDateTime']);
		if($data_evento_prima > $date_det){
			$ret_det   = carica_fotografie_dettagli( $fotografia_id, 'data/evento', $date_det);
			$aggiunti[] = "'data/evento': ".$date_det;
		}
	} // $exif['FILE']['FileDateTime']
	
	// Si verifica il caso che il dato c'è ma vale " "
	// TODO tabella conversioni per identificare le sigle nelle macchine digitali che
	// TODO   vanno convertite in un valore nome/diritti
	if (isset($exif['IFD0']['Copyright'])){
		$copy_det = $exif['IFD0']['Copyright'];
		$copy_det = trim($copy_det);
		if ($copy_det>''){
			$ret_det   = carica_fotografie_dettagli( $fotografia_id, 'nome/diritti', $copy_det);
			$aggiunti[] = "'nome/diritti': ".$copy_det;
		}
	} // $exif['IFD0']['Copyright']

	// Si verifica il caso che il dato c'è ma vale " "
	if (isset($exif['IFD0']['Artist'])){
		$copy_det = $exif['IFD0']['Artist'];
		$copy_det = trim($copy_det);
		if ($copy_det>''){
			// TODO usare il vocabolario per la conversione delle sigle usate nelle fotocamere
			// TODO es.: Anto > Zambon, Antonello; Dino Angeli > Angeli, Dino
			$ret_det   = carica_fotografie_dettagli( $fotografia_id, 'nome/diritti', $copy_det);
			$aggiunti[] = "'nome/diritti': ".$copy_det;
		}
	} // $exif['IFD0']['Copyright']
	
	/**
	 * EXIF - fine
	 */
	echo '<p style="font-family:monospace;">Fine esame dati exif</p>';
	
	/**
	 * LUOGO are-geografica, comune, nazione, provincia 
	 * 
		echo '<p style="font-family:monospace">inizio esame luogo/area-geografica';
		$luogo = get_luogo_localita($nome_file);
		if ($luogo>''){
			$ret_det   = carica_fotografie_dettagli( $fotografia_id, 'luogo/area-geografica', $luogo);
			// sfilo 
			$nome_file = str_ireplace($luogo, '', $nome_file);
			$nome_file = trim($nome_file);
			echo "<br>Per effetto dell'inserimento di luogo/area-geografica, ora nomefile è: " .$nome_file.'<br>';
			$aggiunti[] = "'luogo/area-geografica': ".$luogo;
		} // luogo/area-geografica 
		echo '<br>Fine esame luogo/area-geografica: '.$luogo.'.</p>';
		//dbg echo $nome_file;

		// luogo/comune 
		echo '<p style="font-family:monospace">inizio esame luogo/comune';
		$luogo = get_luogo_comune($nome_file);
		if ($luogo>''){
			$ret_det   = carica_fotografie_dettagli( $fotografia_id, 'luogo/comune', $luogo);
			// sfilo 
			$nome_file = str_ireplace($luogo, '', $nome_file);
			$nome_file = trim($nome_file);
			echo "<br>Per effetto dell'inserimento di luogo/comune, ora nomefile è: " .$nome_file.'<br>';
			$aggiunti[] = "'luogo/comune': ".$luogo;
		} // luogo/comune 
		echo '<br>Fine esame luogo/comune: '.$luogo.'.</p>';
		//dbg echo $nome_file;
	 * 
	 */
	echo '<p style="font-family:monospace">inizio esame luogo/*';
		$kv = get_luogo($nome_file);
		if (isset($kv['chiave']) && $kv['chiave'] > ""){
			if (isset($kv['luogo']) && $kv['luogo'] > ""){
				echo '<br>'.$kv['chiave'].': '.$kv['luogo'];
				$ret_det   = carica_fotografie_dettagli( $fotografia_id, $kv['chiave'], $kv['luogo']);
				$aggiunti[] = "'".$kv['chiave']."': ".$kv['luogo'];
				}
		}
		// TODO Serve trovare il modo di stabilire che la località è in testa al $titolo 
		// TODO   per rimuoverla dal $titolo 
	echo '<br>Fine esame luogo/*: '.(isset($kv['luogo']) ? $kv['luogo'] : "").'</p>';	
	
	// nome/autore
	echo '<p style="font-family:monospace">inizio esame nome/autore';
		$ret         = get_autore($nome_file);
		echo (isset($_SESSION['messaggio'])) ? '<br>messaggio ['.$_SESSION['messaggio'].']' : '';
		echo '<br>nome_file ['.$nome_file.']';
		echo '<br>ret '.str_ireplace(';', '; ',serialize($ret));
		$autore      = $ret[0];
		$sigla_autore= $ret[1];
		echo '<br>autore: ' . $autore;
		echo '<br>sigla: '  . $sigla_autore;
		if ($autore>''){
			$ret_det   = carica_fotografie_dettagli( $fotografia_id, 'nome/autore', $autore);
			$aggiunti[]= "'nome/autore': ".$autore .' [1]';
		}
	echo '<br>Fine esame nome/autore: '.$autore.'</p>';
	
	// codice/autore/athesis
	echo '<p style="font-family:monospace">inizio esame codice/autore/athesis';
		// se ho già trovato autore e sigla $sigla_autore c'è
		if ($sigla_autore == ''){
			// $sigla_autore = get_autore_sigla_6($nome_file);
			$ret = get_autore_e_sigla($nome_file);
			echo (isset($_SESSION['messaggio'])) ? '<br>messaggio ['.$_SESSION['messaggio'].']' : '';
			echo '<br>nome_file ['.$nome_file.']';
			echo '<br>ret '.str_ireplace(';', '; ',serialize($ret));
			$autore2 = $ret['autore'];
			$sigla_autore = $ret['sigla_6'];
			echo '<br>2 autore: '.$autore2;
			echo '<br>2 sigla: '.$sigla_autore;
			if ($autore2>'' && $autore == ''){
				$ret_det   = carica_fotografie_dettagli( $fotografia_id, 'nome/autore', $autore2);
				$aggiunti[] = "'nome/autore': ".$autore2 . ' [2]';
			}
		}
		// valori predefiniti se manca 
		if ($sigla_autore==''){
			$foto_file_maiuscole = strtoupper($foto_file);
			$sigla_autore = match (true) {
				str_contains($foto_file_maiuscole, '1AUTORI') => 'AAA001',
				str_contains($foto_file_maiuscole, '2AUTOF')  => 'AAA002',
				str_contains($foto_file_maiuscole, '3FONDI')  => 'AAA003',
				str_contains($foto_file_maiuscole, '4LIBRI')  => 'AAA004',
				str_contains($foto_file_maiuscole, '5LOCA')   => 'AAA005',
				str_contains($foto_file_maiuscole, '6LOCA')   => 'AAA006<',
				str_contains($foto_file_maiuscole, '7DATI')   => 'AAA007',
				str_contains($foto_file_maiuscole, '8SCUOLA') => 'AAA008',
				str_contains($foto_file_maiuscole, '9TERRI')  => 'AAA009',
				str_contains($foto_file_maiuscole, '10VIDEO') => 'AAA010',
			};
			echo '<br>sigla: '.$sigla_autore;
		}
		$ret_det   = carica_fotografie_dettagli( $fotografia_id, 'codice/autore/athesis', $sigla_autore);
		$aggiunti[]= "'codice/autore/athesis': ".$sigla_autore;

		// sfilo 
		$nome_file = str_replace($sigla_autore, '', $nome_file);
		$nome_file = str_replace(' ph',         '', $nome_file);
		$nome_file = trim($nome_file);
		echo "<br>Per effetto dell'inserimento di codice/autore/athesis, ora nomefile è: " .$nome_file.'<br>';
		// codice/autore/sigla 
	echo '<br>Fine esame codice/autore/athesis</p>';
	
	// nome/ente-societa
	echo '<p style="font-family:monospace">inizio esame nome/ente-societa';
	$ente = get_ente_societa($nome_file);
	if ($ente>''){
		$ret_det   = carica_fotografie_dettagli( $fotografia_id, 'nome/ente-societa', $ente);
		$aggiunti[] = "'nome/ente-societa': ".$ente;
	} // nome/ente-societa 
	echo '<br>Fine esame nome/ente-societa: '.$ente.'</p>';
	
	// nome/fondo 
	echo '<p style="font-family:monospace">Inizio esame fondo';
	$fondo = get_fondo($nome_file);
	if ($fondo>''){
		$ret_det   = carica_fotografie_dettagli( $fotografia_id, 'nome/fondo', $fondo);
		$aggiunti[] = "'nome/fondo': ".$fondo;
	}
	echo '<br>Fine esame fondo_* : '.$fondo.'</p>';

	// codice archivio esterno
	echo '<p style="font-family:monospace">Inizio esame codice/esterno';
	$cod_esterno="";
	if (preg_match('/id_\d+/', $nome_file, $match)){
		$cod_esterno = trim($match[0]);
	}
	if ($cod_esterno > ''){
		$ret_det   = carica_fotografie_dettagli( $fotografia_id, 'codice/esterno', $cod_esterno);
		$aggiunti[] = 'codice/esterno: '.$cod_esterno;
	}
	echo '<br>Fine esame id_* : '.$cod_esterno.'</p>';

	// codice archivio athesis
	echo '<p style="font-family:monospace">Inizio esame codice/archivio-athesis';
	$cod_athesis="";
	if (preg_match('/ath_\d+/', $nome_file, $match)){
		$cod_athesis = trim($match[0]);
	}
	if ($cod_athesis>''){
		$ret_det   = carica_fotografie_dettagli( $fotografia_id, 'codice/archivio-athesis', $cod_athesis);
		$aggiunti[] = 'codice/archivio-athesis: '.$cod_athesis;
	}
	echo '<br>Fine esame ath_* : '.$cod_athesis.'</p>';

	// dimensioni 
	echo '<p style="font-family:monospace">Inizio esame dimensioni';
	$dimensioni="";
	if (preg_match('/cm_\d{2}x\d{2}/', $nome_file, $match)){
		$dimensioni = trim($match[0]);

		$dimensioni = str_ireplace('cm_', '', $dimensioni);
		[ $altezza, $larghezza ] = explode('x', $dimensioni);
		$dimensioni = str_ireplace('x', ' x ', $dimensioni);
		$dimensioni .= ' cm';

		$ret_det   = carica_fotografie_dettagli( $fotografia_id, 'dimensione/unita-di-misura', 'cm');
		$aggiunti[] = 'dimensione/unita-di-misura: cm';
		$ret_det   = carica_fotografie_dettagli( $fotografia_id, 'dimensione/altezza', $altezza.' cm');
		$aggiunti[] = 'dimensione/altezza: '.$altezza.' cm';
		$ret_det   = carica_fotografie_dettagli( $fotografia_id, 'dimensione/larghezza', $larghezza.' cm');
		$aggiunti[] = 'dimensione/larghezza: '.$larghezza.' cm';
		// sfilo 
		$nome_file = trim(str_ireplace('cm_'.$match[0], '', $nome_file));		
		echo "<br>Per effetto dell'inserimento di dimensioni, ora nomefile è: " .$nome_file.'<br>';
	}
	echo '<br>Fine esame cm_* : '.$dimensioni.'</p>';

	// materiale lastra fotografica
	echo '<p style="font-family:monospace">Inizio esame materiale';
	$materiale=(str_contains(strtolower($nome_file), ' lastra')) ? 'lastra' : "";
	if ($materiale>''){
		$ret_det   = carica_fotografie_dettagli( $fotografia_id, 'materia/tecnica', 'negativo/lastra-di-vetro');
		// sfilo 
		$nome_file = str_ireplace($materiale, '', $nome_file);
		$nome_file = trim($nome_file);
		echo "<br>Per effetto dell'inserimento di materia/tecnica, ora nomefile è: " .$nome_file.'<br>';
		$aggiunti[] = "'materia/tecnica': negativo/lastra-di-vetro";
	}
	echo '<br>Fine esame materiale : '.$materiale.'</p>';
	
	// sfilettato nome_file quello che resta al centro  
	// nome/manifestazione-soggetto
	// sfilettare estensioni
	echo '<p style="font-family:monospace">Inizio esame nome/manifestazione-soggetto';
	$nome_soggetto = str_replace('.'.$estensione, '', $nome_file);
	$nome_soggetto = trim($nome_soggetto);
	if ($nome_soggetto>''){
		$ret_det=carica_fotografie_dettagli( $fotografia_id, 'nome/manifestazione-soggetto', $nome_soggetto);
		$aggiunti[] = "'nome/manifestazione-soggetto': ".$nome_soggetto;
	}
	echo '<br>Fine esame nome/manifestazione-soggetto</p>';
	
	// cambio stato al record 
	$ret_stato=[];
	$ret_stato = $foto_h->set_stato_lavori_in_fotografie($fotografia_id, Fotografie::stato_completati);
	if (isset($ret_stato['error'])){
		$ret = '<h2>Errore</h2>'
		. '<p>Non è stato possibile cambiare stato_lavori alla fotografia ['. $fotografia_id .']</p>'
		. '<p>Per: ' . $ret_stato['message'];
		echo $ret;
	}

	// riepilogo 
	if (count($aggiunti)){
		echo '<br><h4 style="font-family:monospace;">Aggiunti alla fotografia ' . count($aggiunti) . ' record.</h4>';
		echo '<p style="font-family:monospace;">';
		for ($i=0; $i < count($aggiunti); $i++) { 
			# code...
			echo $aggiunti[$i].'<br>';
		}	
		echo '</p>';
	}

	// ricarica 5 secondi
	echo '<script src="'.URLBASE.'aa-view/reload-5sec-jquery.js"></script>';
	exit(0);

} // carica_dettagli_da_fotografia()
