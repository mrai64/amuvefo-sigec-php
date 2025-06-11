<?php
/**
 * ZONA INTRO controller
 * 
 * funzioni relative ai file e cartelle inseriti in archivio
 * nelle tabelle zona_intro e scansioni_disco 
 * 
 * - crea_query_cartella 
 *   usata da leggi_cartella_per_id
 * - crea_query_sottocartelle
 *   usata da leggi_cartella_per_id
 * - leggi_cartella_per_id 
 *   legge un record della tabella scansioni_disco 
 *   e mostra la mappa cartelle + sotto-cartelle  
 * - leggi_cartella_per_percorso 
 *   alternativa alla funzione lecci_cartella_per_id 
 *   questa usa un percorso /cartella/cartella/cartella/
 *   per identificare il record in tabella scansioni_disco 
 *   e mostrare la schermata cartella + sotto-cartelle 
 * - verifica_cartella_contiene_album
 * 
 * - lista_cartelle_sospese 
 * - set_stato_lavori 
 * - carica_cartelle_in_scansioni_disco 
 *   carica in scansioni_disco partendo da zona_intro 
 * - carica_cartelle_in_zona_intro
 *   espone il modulo per l'aggiunta di una cartella in zona_intro
 * - cambia_tinta_record
 *   espone il modulo per cambiare la tinta di un elemento in 
 *   esponi cartelle e sottocartelle 
 *   oppure aggiorna 
 * 
 * - reset_stato_lavori_cartelle
 * 
 */
if (!defined('ABSPATH')){
	include_once('../_config.php');
}
include_once(ABSPATH . 'aa-model/database-handler-oop.php'); //   Class DatabaseHandler
include_once(ABSPATH . 'aa-model/scansioni-disco-oop.php');  //   Class ScansioniDisco
include_once(ABSPATH . 'aa-model/album-oop.php');//               Class Album 
include_once(ABSPATH . 'aa-model/zona-intro-oop.php');//  Class Cartelle


/**
 * Elenco delle cartelle che non hanno stato lavori "completati" 
 * Crea una porzione di codice che viene poi infilata in pagina tramite chiamata ajax 
 * 
 * @return string html - elenco delle cartelle | messaggio di errore
 */
function lista_cartelle_sospese() : string {
	$dbh        = New DatabaseHandler();
	$cartelle_h = New Cartelle($dbh);
	//dbg echo var_dump($cartelle_h);

	$campi=[];
	$campi['query'] = 'SELECT * FROM ' . Cartelle::nome_tabella 
	. " WHERE stato_lavori < '" . Cartelle::stato_completati . "' "
	. ' ORDER BY stato_lavori, record_id ';
	$ret_cartelle = $cartelle_h->leggi($campi);
	if (isset($ret_cartelle['error'])){
		$res = '<p class="h3 text-warning-text text-center mt-5">' . __FUNCTION__ 
		. " Si è verificato l'errore " . $ret_cartelle['message']
		. '<br>campi: ' . str_ireplace(';', '; ', serialize($campi))
		. '</p>';
		return $res;
	}
	if ($ret_cartelle['numero'] == 0){
		$res = '<p class="h3 text-warning-text text-center mt-5">'
		. 'Nessuna cartella da lavorare in sospeso</p>';
		return $res;
	}
	$cartelle=$ret_cartelle['data'];

	// si compone l'elenco 
	$res = "<table class='table table-bordered table-striped'>"
	. "<thead> <tr>"
	. "<td scope='col'>#</td>"
	. "<td scope='col'>Status</td>"
	. "<td scope='col'>Disco</td>"
	. "<td scope='col'>Percorso completo</td>"
	. "<td scope='col' nowrap>Azione</td>"
	. "</tr> </thead>\n\n<tbody>\n";

	foreach ($cartelle as $cartella) {
		$res .= "<tr><td scope='row'>".$cartella['record_id']."</td>";
		$res .= "<td>".$cartella['stato_lavori']."</td>";
		$res .= "<td>".$cartella['disco']."</td>";
		$res .= "<td>".$cartella['percorso_completo']."</td>";

		if ($cartella['stato_lavori'] == Cartelle::stato_da_fare) {
			$res .= "<td><a href='".URLBASE."zona_intro.php/archivia-cartella/".$cartella['record_id']."' "
			. "target='_blank' class='btn btn-secondary float-end' >"
			. "Elabora</a></td>".PHP_EOL;

		} elseif ($cartella['stato_lavori'] == Cartelle::stato_in_corso)  {
			$res .= "<td><a href='".URLBASE."zona_intro.php/reset-status/".$cartella['record_id']."'"
			. " target='_blank' class='btn btn-secondary float-end' >"
			. ' Reset</a</td>'.PHP_EOL;

		} else {
			$res .= '<td> &nbsp; </td>'.PHP_EOL;
		}

		$res .= "</tr>".PHP_EOL;
	} // foreach

	$res .= "\n<tbody>\n</table>";
	return $res;
} // lista_cartelle_sospese()



/**
 * Aggiorna la colonna stato_lavori nella tabella zona_intro
 * 
 * @param  int     $cartella_id 
 * @param  string  $stato_lavori 
 * @return bool    true = stato cambiato 
 */
function set_stato_lavori(int $cartella_id, string $stato_lavori) : bool {
	$dbh        = New DatabaseHandler(); // nessun parametro dedicato
	$cartelle_h = New Cartelle($dbh);

	$record_cancellabile_dal = ($stato_lavori == Cartelle::stato_completati) ? $dbh->get_datetime_now() : $dbh->get_datetime_forever();

	$campi=[];
	$campi['update'] = 'UPDATE ' . Cartelle::nome_tabella  
	. ' SET stato_lavori = :stato_lavori, '
	. ' record_cancellabile_dal = :record_cancellabile_dal '
	. ' WHERE record_id = :record_id ';
	$campi['stato_lavori']            = $stato_lavori;
	$campi['record_id']               = $cartella_id;
	$campi['record_cancellabile_dal'] = $record_cancellabile_dal;
	$ret_sta = [];
	$ret_sta = $cartelle_h->modifica($campi);

	return (isset($ret_sta['ok']));
} // set_stato_lavori()



/**
 * Carica in scansioni_disco una cartella dalla tabella zona_intro
 * 
 * @param  int $cartella_id | 0 
 *   Se non viene passato o viene passato 0,
 *   la funzione va a cercare un primo record da elaborare
 *   zona_intro.record_id 
 * 
 * @return void però espone codice html che traccia la funzione svolta 
 * 
 * TODO diventerà carica_deposito_da_cartelle
 */
/**
 * 1. Se arriva un id dalla cartella zona_intro 
 *    altrimenti si prende "il primo che capita" tra quelli 
 *    nella tabella che hanno stato lavori: da fare
 * 2. Si verifica che il record ci sia in scansioni_tabelle
 * 3. si verifica che corrisponda a una cartella 
 *    se no: fine lavoro 
 * 4. si verifica se con i dati disponibili sia già presente 
 *    nella tabella deposito scansioni_disco un record per la cartella
 * 5. Se Manca: si inserisce, cambio stato 'lavori completati' e fine lavori 
 * 6. Se Presente: cambio stato 'lavori completati' e fine lavori 
 * 7. Vengono caricate  
 * 7.1. in zona_intro le sottocartelle trovate
 * 7.2. in scansioni_disco le fotografie (e i video) 
 *      che contiene la cartella 
 */
function carica_cartelle_in_scansioni_disco( int $cartella_id = 0){
	$dbh        = New DatabaseHandler(); // nessun parametro dedicato
	$cartelle_h = New Cartelle($dbh);
	// ret_car
	$scan_h     = New ScansioniDisco($dbh);
	// ret_scan
	// ret_scan_c
	// ret_scan_f 
	$errori = '';

	/**
	 * intestazione pagina web
	 */
	$titolo_pagina = "Caricamento cartella in deposito";
	$inizio_pagina = file_get_contents(ABSPATH.'aa-view/reload-5sec-view.php');
	$inizio_pagina = str_ireplace('<?=$titolo_pagina; ?>', $titolo_pagina, $inizio_pagina);
	echo $inizio_pagina;
	// si possono usare le classi di bootstrap 
	
	echo '<h2 class="text-monospace">Caricamento di una cartella in deposito</h2>'
	. '<p class="text-monospace">Da tabella zona_intro in tabella scansioni_disco</p>';

	// 1. id presente o primo che capita
	// get_zona_intro_per_id 
	// get_zona_intro_da_fare 
	if ($cartella_id === 0){		
		$campi=[];
		$campi['query'] = 'SELECT * FROM ' . Cartelle::nome_tabella 
		. ' WHERE stato_lavori = :stato_lavori  '
		. ' AND record_cancellabile_dal = :record_cancellabile_dal '
		. ' ORDER BY record_id ';
		$campi['stato_lavori'] = Cartelle::stato_da_fare;
		$campi['record_cancellabile_dal']=$dbh->get_datetime_forever();
		
	} else {
		// lo stato_lavori viene ignorato, intenzionalmente 
		$campi=[];
		$campi['query'] = 'SELECT * FROM ' . Cartelle::nome_tabella
		. ' WHERE record_id = :record_id '
		. ' AND record_cancellabile_dal = :record_cancellabile_dal '
		. ' AND stato_lavori = :stato_lavori  ';
		$cartelle_h->set_record_id($cartella_id);
		$campi['record_id']    = $cartelle_h->get_record_id();	
		$campi['stato_lavori'] = Cartelle::stato_da_fare;
		$campi['record_cancellabile_dal']=$dbh->get_datetime_forever();
		
	}
	$ret_car = $cartelle_h->leggi($campi);

	// arrivati errori - stop
	if (isset($ret_car['error'])){
		$res = '<p class="h3 text-warning-text text-center mt-5">'
		. "Si è verificato l'errore " . $ret_car['message']
		. '<br />campi: ' . str_ireplace(';', '; ', serialize($campi)) . '</p>';
		echo $res;
		exit(1);
	}
	
	// nessun record - stop
	if ($ret_car['numero'] == 0){
		$res = "<p style='font-family:monospace;'>"
		. "Nessun caricamento in deposito della cartella id: $cartella_id "
		. "(0 = la prima che c'è)."
		. "<br />Nessun lavoro in sospeso o Cartella non trovata."
		. "<br />Chiudere e passare al prossimo step." . '</p>';
		echo $res; 
		exit(1);
	}

	$cartella    = $ret_car['data'][0];
	$cartella_id = $cartella['record_id'];

	echo '<p style="font-family:monospace">Cartella: '.$cartella_id;
	echo '<br>'. str_replace(';', '; ', serialize($cartella)) . '</p>';
	
	// cambio stato_lavori in zona_intro 
	// set_stato_lavori_zona_intro
	if (!set_stato_lavori( $cartella_id, Cartelle::stato_in_corso )){
		$res = "<p style='font-family:monospace;'>Non è "
		. "stato cambiato in ".Cartelle::stato_in_corso
		. " lo stato della cartella $cartella_id. "
		. "<br>STOP LAVORI </p>";
		echo $res;
		exit(1);
	}
	/**
	 * prepara i dati per inserirli in scansioni_disco 
	 */
	$disco   = $cartella['disco'];
	$percorso_fs_cartella = str_ireplace( URLBASE, './', $cartella['percorso_completo']);
	$percorso_fs_cartella = str_replace('%20', ' ',    $percorso_fs_cartella);
	if ($percorso_fs_cartella[0]=='/'){
		$percorso_fs_cartella = '.'.$percorso_fs_cartella;
	}
	$percorso_con_abspath = str_replace('./', ABSPATH, $percorso_fs_cartella);
	$percorso_fs_cartella = str_replace('./', '/'    , $percorso_fs_cartella);
	// genera dei warning e delle exception quando il percorso non ha abbastanza / 
	// TODO Aggiungere alla stringa '/////// ' per fornire sempre un numero di / sufficienti ad arrivare a 6 
	@list($livello1, $livello2, $livello3, 
	      $livello4, $livello5, $livello6) = explode('/', mb_substr($percorso_fs_cartella, 1));
	$livello6 = ($livello6 > '') ? $livello6 : '';
	$livello5 = ($livello5 > '') ? $livello5 : '';
	$livello4 = ($livello4 > '') ? $livello4 : '';
	$livello3 = ($livello3 > '') ? $livello3 : '';
	$livello2 = ($livello2 > '') ? $livello2 : '';

	// se la cartella non c'è stop 
	if (!is_dir($percorso_con_abspath)){
		$res = '<p class="h3 text-warning-text text-center mt-5">'
		. "Si è verificato l'errore CARTELLA MANCANTE "
		. '<br />campi: ' . $percorso_con_abspath . '</p>';
		echo $res;
		exit(1);
	}
	// Verifica se il record della cartella in scansioni_disco c'è già 
	$campi=[];
		$campi['query'] = 'SELECT * FROM ' . ScansioniDisco::nome_tabella
		. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
		. " AND nome_file = '/'      AND estensione = '' "
		. ' AND disco = :disco '
		. ' AND livello1 = :livello1 AND livello2 = :livello2 ' 
		. ' AND livello3 = :livello3 AND livello4 = :livello4 ' 
		. ' AND livello5 = :livello5 AND livello6 = :livello6 '
		. ' ORDER BY record_id ';
		$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
		$campi['disco']    = $disco;
		$campi['livello1'] = $livello1;
		$campi['livello2'] = $livello2;
		$campi['livello3'] = $livello3;
		$campi['livello4'] = $livello4;
		$campi['livello5'] = $livello5;
		$campi['livello6'] = $livello6;
		$ret_scan_c = []; 
		$ret_scan_c = $scan_h->leggi($campi);
		if ( isset($ret_scan_c['error'])){
			$errori .= '<br>Cercando un record in scansioni_disco si è verificato questo Errore:'
			. '<br>' . $ret_scan_c['message']
			. '<br>campi: ' . str_ireplace(';', '; ', serialize($campi));

		}
	// Se Manca Aggiungo altrimenti il record c'è già, passo oltre 
	if ($ret_scan_c['numero'] < 1){
		// si inserisce
		$campi=[];
			$campi['disco']    = $disco;
			$campi['livello1']=$livello1;
			$campi['livello2']=$livello2;
			$campi['livello3']=$livello3;
			$campi['livello4']=$livello4;
			$campi['livello5']=$livello5;
			$campi['livello6']=$livello6;
			$campi['nome_file'] = '/'; // cartella
			$campi['estensione'] = '';
			$campi['modificato_il'] = date("Y-m-d H:i:s", filemtime($percorso_con_abspath));
			$campi['codice_verifica'] = '0';
			$campi['tinta_rgb'] = '000000';
			$ret_scan = [];
			$ret_scan = $scan_h->aggiungi($campi);
		
			if ( isset($ret_scan['error'])){
				echo "<p style='font-family:monospace;'>"
				. "Non è stato aggiornato lo stato di una cartella in scansioni_disco."
				. '<br />Errore: ' . $ret_scan['message']
				. '<br />campi: ' . str_ireplace(';', '; ', serialize($ret_scan))
				. "<br />STOP LAVORI</p>";
				exit(1);
			}
		// si passa a inserire foto e video in scansioni_disco 
	} else {
		// andrà ri-lavorato come fosse stato inserito ora
		$campi = [];
			$campi['update'] = 'UPDATE ' . ScansioniDisco::nome_tabella
			. ' SET stato_lavori = :stato_lavori '
			. ' WHERE record_id = :record_id ';
			$campi['stato_lavori'] = ScansioniDisco::stato_da_fare;
			$campi['record_id'] = $ret_scan_c['data'][0]['record_id'];
			$ret_scan = [];
			$ret_scan = $scan_h->modifica($campi);
			if (isset($ret_scan['error'])){
				echo "<p style='font-family:monospace;'>"
				. "Non è stato aggiornato lo stato di una cartella in scansioni_disco."
				. '<br />Errore: ' . $ret_scan['message']
				. '<br />campi: ' . str_ireplace(';', '; ', serialize($ret_scan))
				. "<br />STOP LAVORI</p>";
				exit(1);
			} // già presente in scansioni_disco
	} 

	/**
	 * scansiono elemento per elemento il contenuto della cartella 
	 * percorso_fs_cartella e carico in scansioni_disco 
	 * solo immagini e video, mentre le sotto-cartelle vengono aggiunte 
	 * alla tabella zona_intro
	 */
	$contenuto_fs = dir($percorso_con_abspath);
	if ( $contenuto_fs === false){
		// prima di fermarmi cambio stato lavori in zona_intro
		if (!set_stato_lavori( $cartella['record_id'], Cartelle::stato_completati )){
			$errori .= '<br>Non è stato possibile cambiare stato_lavori in completato.';
			echo $errori;
			exit(1);
		}
		echo "<p style='font-family:monospace;'>"
		. "Non è stato letto il contenuto di $percorso_fs_cartella.</p>";
		exit(1);
	}
	while ($elemento = $contenuto_fs->read()) {
		if ($elemento[0] == '.' ){
			// . .. .DS_Store .*
			continue;
		}
		
		$percorso_piu_elemento = str_ireplace( '//', '/', $percorso_con_abspath.'/'.$elemento);
		if (!is_dir($percorso_piu_elemento) && !is_file($percorso_piu_elemento)){
			echo "<p style='font-family:monospace;'>"
			. "Non è dir, non è file ...che è? $percorso_piu_elemento</p>";
			exit(1);
		} 
		// Se l'elemento interno è una cartella 
		// va aggiunto tra le cartelle da lavorare in zona_intro
		if (is_dir($percorso_piu_elemento) ){
			// 1. c'è già in zona_intro?
			$campi = [];
				$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
				$campi['percorso_completo'] = $percorso_fs_cartella.$elemento.'/';
				$campi['query'] = 'SELECT * FROM ' . Cartelle::nome_tabella 
				. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
				. ' AND LENGTH(percorso_completo) = ' . strlen($campi['percorso_completo'])
				. ' AND percorso_completo = :percorso_completo ';
				$ret_car = [];
				$ret_car = $cartelle_h->leggi($campi);
				if (isset($ret_car['error'])){
					$errori .= '<br>Nel caricamento in zona_intro si è verificato questo:'
					. '<br>' . $ret_car['message']
					. ' campi: ' . serialize($campi);
					continue;
				}
			// Se manca si aggiunge 
			if ($ret_car['numero'] < 1){
				// 1. si aggiunge alla tabella delle zona_intro
				$campi = [];
				$campi['disco'] = $disco;
				$campi['percorso_completo'] = $percorso_fs_cartella.$elemento.'/';
				$ret_car = [];
				$ret_car = $cartelle_h->aggiungi($campi);
				if (isset($ret_car['error'])){
					$errori .= '<br>Nel caricamento in zona_intro si è verificato questo:'
					. '<br>' . $ret_car['message']
					. ' campi: ' . serialize($campi);
				}
			}
			continue;
		} // is_dir($percorso_piu_elemento)
		
		// $elemento Non è cartella, è file 
		// ma carico solo immagini e video in deposito

		// prendo estensione 
		$punto_estensione = strrpos($elemento, '.');
		if ($punto_estensione===false){
			$estensione='?';
		} else {
			$estensione = substr($elemento, ($punto_estensione + 1), 6);
			$estensione = strtolower($estensione);
		}
		// estensioni gestite:
		// TODO gestione ' alias' macosx 
		// TODO gestione estensione lnk shortcut - versione windows
		if (!in_array($estensione, ['jpg', 'jpeg', 'tif', 'tiff', 'psd', 'mp4'])){
			// txt, doc, mkv ... salta tutto e avanti al prossimo
			continue;
		}

		// verifico se il file sia già in scansioni_disco 
		$campi = [];
		$campi['query'] = 'SELECT * FROM ' . ScansioniDisco::nome_tabella
		. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
		. ' AND disco = :disco '
		. ' AND livello1 = :livello1 AND livello2 = :livello2 '
		. ' AND livello3 = :livello3 AND livello4 = :livello4 '
		. ' AND livello5 = :livello5 AND livello6 = :livello6 '
		. ' AND nome_file = :nome_file AND estensione = :estensione ';
		$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
		$campi['disco']      = $disco;
		$campi['livello1']   = $livello1;
		$campi['livello2']   = $livello2;
		$campi['livello3']   = $livello3;
		$campi['livello4']   = $livello4;
		$campi['livello5']   = $livello5;
		$campi['livello6']   = $livello6;
		$campi['nome_file']  = $elemento;
		$campi['estensione'] = $estensione;
		$ret_scan_f = [];
		$ret_scan_f = $scan_h->leggi($campi);

		if ( isset($ret_scan_f['error'])){
			$errori .= '<br>Nel caricamento in scansioni_disco si è verificato questo:'
			. '<br>' . $ret_scan_f['message']
			. ' campi: ' . str_ireplace(';', '; ', serialize($campi));

		}
		// se Manca Aggiungo
		if ($ret_scan_f['numero'] < 1 ){
			// aggiunta alla tabella scansioni_disco
			$campi=[];
			$campi['disco']      = $disco;
			$campi['livello1']   = $livello1;
			$campi['livello2']   = $livello2;
			$campi['livello3']   = $livello3;
			$campi['livello4']   = $livello4;
			$campi['livello5']   = $livello5;
			$campi['livello6']   = $livello6;
			$campi['nome_file']  = $elemento;
			$campi['estensione'] = $estensione;
			$campi['codice_verifica'] = md5_file($percorso_piu_elemento); // prende tempo...
			$campi['tinta_rgb']  = '000000';
			$ret_car = [];
			$ret_car = $scan_h->aggiungi($campi);
			if ( isset($ret_car['error'])){
				$errori .= '<br>Nel caricamento in scansioni_disco si è verificato questo:'
				. '<br>' . $ret_car['message']
				. ' campi: ' . serialize($campi);
			} else {
				echo "\n".'<p style="font-family:monospace;">'
				. 'Caricamento eseguito in scansioni_disco'
				. '<br>elemento: ' . $estensione . ' ' . $elemento   
				. '<br>campi:' . str_ireplace(';', '; ',serialize($campi)) 
				. '<br>ret:' . str_ireplace(';', '; ',serialize($ret_car)) . '</p>';
			}
		} else {
			// se presente devo impostare lo stato_lavori come se fosse nuovo
			$campi = [];
			$campi['update'] = 'UPDATE ' . ScansioniDisco::nome_tabella
			. ' SET stato_lavori = :stato_lavori '  
			. ' WHERE record_id = :record_id ';
			$campi['stato_lavori'] = ScansioniDisco::stato_da_fare;
			$campi['record_id'] = $ret_scan_f['data'][0]['record_id'];
			$ret_car = [];
			$ret_car = $scan_h->modifica($campi);
			if ( isset($ret_car['error'])){
				$errori .= '<br>Nel caricamento in scansioni_disco si è verificato questo:'
				. '<br>' . $ret_car['message']
				. ' campi: ' . str_ireplace(';', '; ', serialize($campi));
			} else {
				echo "\n".'<p class="text-monospace;">'
				. 'Caricamento eseguito in scansioni_disco'
				. '<br>elemento: ' . $estensione . ' ' . $elemento  
				. '<br>campi:' . str_ireplace(';', '; ',serialize($campi)) 
				. '<br>ret:' . str_ireplace(';', '; ',serialize($ret_car)) . '</p>';
			}
		}
	} // while() - lettura directory - folder

	echo "\n".'<br>caricamento-aggiornamento immagini completato';
	// cambio stato lavori 
	if (!set_stato_lavori( $cartella['record_id'], Cartelle::stato_completati )){
		$errori .= '<br>Non è stato possibile cambiare stato_lavori in completato.';
	}

	if ($errori){
		echo '<div class="alert alert-danger" role="alert">'."\n"
		. $errori . "\n"
		. '</div>'. "\n";
		exit(1);
	}
	// ricarica 5 secondi
	echo "<p style='font-family:monospace;'>OK, lavoro eseguito. Ricarico in 5 secondi</p>";
	echo '<script src="'.URLBASE.'aa-view/reload-5sec-jquery.js"></script>';
	exit(0);

} // carica_cartelle_in_scansioni_disco()

/**
 * Da file a cartelle 
 * 
 * Il modulo chiede due dati
 * - il disco
 * - il "percorso completo" es. "/9TERRI/Bonifiche/", 
 *   che deve avere alla fine la /
 * Se mancano i dati espone il modulo 
 * Se i dati ci sono inserisce la cartella in tabella 
 * @param  array $dati_input 
 */
function carica_cartelle_in_zona_intro(array $dati_input = [] ){

	// inserimento del record nella tabella Cartelle 
	if (isset($dati_input['aggiungi_cartella'])){
		$dbh    = New DatabaseHandler();
		$scan_h = New Cartelle($dbh);
		$_SESSION['messaggio']='';
		//
		if (!isset($dati_input['disco'])){
			$_SESSION['messaggio'] = "1. Si è verificato un problema nel caricamento della cartella"
			. " mancano i dati del modulo.";
		}
		if (!isset($dati_input['cartella'])){
			$_SESSION['messaggio'] = "2. Si è verificato un problema nel caricamento della cartella"
			. " mancano i dati del modulo.";
		}
		if ($_SESSION['messaggio']==''){
			$campi=[];
			$campi['disco']    = $dati_input['disco'];
			$percorso_completo = '/'.$dati_input['cartella'].'/'; // prima lo aggiungo 
			$percorso_completo = str_replace('%20', ' ', $percorso_completo);
			$percorso_completo = str_replace('+', ' ', $percorso_completo);
			$percorso_completo = str_replace('//', '/', $percorso_completo);
			$campi['percorso_completo'] = $percorso_completo;
			$ret_scan = $scan_h->aggiungi($campi);
			if (isset($ret_scan['ok'])){
				$_SESSION['messaggio']='Cartella inserita in elenco sospesi';
			} else {
				$_SESSION['messaggio']=$ret_scan['message'];
			}	
		}
	} // inserimento del record 

	// esposizione del modulo che chiede i dati ed espone il messaggio
	include_once(ABSPATH.'aa-view/cartelle-da-scansionare.php');
	exit(0);
} // carica_cartelle_in_zona_intro()


/**
 * Per le cartelle che restano bloccate con i 'lavori in corso'
 * @param int $cartella_id
 */
function reset_stato_lavori_cartelle(int $cartella_id = 0 ){
	set_stato_lavori($cartella_id, Cartelle::stato_da_fare);
} // reset_stato_lavori_cartelle