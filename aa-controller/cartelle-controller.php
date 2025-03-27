<?php
/**
 * CARTELLE controller
 * 
 * funzioni relative ai file e cartelle inseriti in archivio
 * nelle tabelle scansioni_cartelle e scansioni_disco 
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
 * - lista_cartelle_sospese 
 * - set_stato_scansione 
 * - carica_cartelle_in_scansioni_disco 
 *   carica in scansioni_disco partendo da scansioni_cartelle 
 * - carica_cartelle_in_scansioni_cartelle
 *   espone il modulo per l'aggiunta di una cartella in scansioni_cartelle
 * - cambia_tinta_record
 *   espone il modulo per cambiare la tinta di un elemento in 
 *   esponi cartelle e sottocartelle 
 *   oppure aggiorna 
 * 
 */
if (!defined('ABSPATH')){
	include_once('../_config.php');
}
include_once(ABSPATH . 'aa-model/database-handler-oop.php'); //   Class DatabaseHandler
include_once(ABSPATH . 'aa-model/scansioni-disco-oop.php');  //   Class ScansioniDisco
include_once(ABSPATH . 'aa-model/album-oop.php');//               Class Album 
include_once(ABSPATH . 'aa-model/scansioni-cartelle-oop.php');//  Class Cartelle


/**
 * @return string elenco delle cartelle codificato in html
 */
function lista_cartelle_sospese() : string {
	$dbh        = New DatabaseHandler();
	$cartelle_h = New Cartelle($dbh);
	//dbg echo var_dump($cartelle_h);

	$campi=[];
	$campi['query'] = 'SELECT * FROM ' . Cartelle::nomeTabella 
	. ' WHERE stato_scansione < ' . Cartelle::statoCompletato 
	. ' ORDER BY stato_scansione, record_id ';
	$ret_cartelle = $cartelle_h->leggi($campi);
	//dbg echo '<pre style="max-width: 50rem;">'."\n";
	//dbg echo var_dump($campi);

	if ( isset($ret_cartelle['error']) || $ret_cartelle['numero'] == 0){
		$res = '<p class="h3 text-warning-text text-center mt-5">Nessuna cartella da lavorare in sospeso</p>';
		return $res;
	}
	$cartelle=$ret_cartelle['data'];
	$res = "<table class='table table-bordered table-striped'>"
	. "<thead> <tr>"
	. "<td>#</td>"
	. "<td>Status</td>"
	. "<td>Disco</td>"
	. "<td>Percorso completo</td>"
	. "<td>Azione</td>"
	. "</tr> </thead>\n\n<tbody>\n";
	foreach ($cartelle as $cartella) {
		$res .= "<tr><td>".$cartella['record_id']."</td>";
		switch ($cartella['stato_scansione']) {
			case '0':
				$res .= "<td>Da fare</td>";
				break;
			
			case '1':
				$res .= "<td>In corso</td>";
				break;
			
			case '2':
				$res .= "<td>Completato</td>";
				break;
			
			default:
				$res .= "<td>".$cartella['stato_scansioni']."</td>";
				break;
		}
		$res .= "<td>".$cartella['disco']."</td>";
		$res .= "<td>".$cartella['percorso_completo']."</td>";
		if ($cartella['stato_scansione'] == 0) {
			// era /95-archiviazione-cartella.php?id=
			$res .= "<td><a href='/cartelle.php/archivia-cartella/"
			. $cartella['record_id'] . "' target='_blank' "
			. "class='btn btn-secondary float-end'>Scansiona</a></td>".PHP_EOL;
		} else {
			$res .= "<td>--</td>".PHP_EOL;
		}
		$res .= "</tr>".PHP_EOL;
	}
	$res .= "\n<tbody>\n</table>";
	return $res;
} // lista_cartelle_sospese()

/**
 * test 
 * https://www.fotomuseoathesis.it/aa-controller/cartelle-controller.php?test=lista-cartelle-sospese
 */

if (isset($_GET['test']) && 
    $_GET['test'] == 'lista-cartelle-sospese'){
	echo '<pre style="max-width:50rem;">debug on'."\n";
	echo lista_cartelle_sospese();
	echo '<br>fine';
	exit(0);
}

/**
 * @param  int  $cartella_id 
 * @param  int  $stato_scansione 
 * @return bool true = stato cambiato 
 */
function set_stato_scansione(int $cartella_id, int $stato_scansione) : bool {
	$dbh        = New DatabaseHandler(); // nessun parametro dedicato
	$cartelle_h = New Cartelle($dbh);

	$campi=[];
	$campi['update'] = 'UPDATE scansioni_cartelle '  
	. ' SET stato_scansione = :stato_scansione'
	. ' WHERE record_id = :record_id ';
	$campi['stato_scansione'] = $stato_scansione;
	$campi['record_id']       = $cartella_id;
	$ret_sta = $cartelle_h->modifica($campi);
	return (isset($ret_sta['ok']));
} // set_stato_scansione()



/**
 * @param  int $cartella_id - facoltativo
 *   Si tratta del record in scansioni_cartelle, se non viene passato, 
 *   o viene passato 0, la funzione va a cercare un primo record da elaborare
 * @return void 
 * 
 * TODO diventerà carica_deposito_da_cartelle
 */
function carica_cartelle_in_scansioni_disco( int $cartella_id = 0){
	$dbh        = New DatabaseHandler(); // nessun parametro dedicato
	$cartelle_h = New Cartelle($dbh);
	$errori = '';

	// Se non viene passato un id si recupera "il primo che capita" 
	if ($cartella_id == 0){
		// TODO diventa una funzione del model get_cartella_da_elaborare() : int
		$campi=[];
		$campi['query'] = 'SELECT * FROM ' . Cartelle::nomeTabella 
		. ' WHERE stato_scansione IN ( :stato_scansione ) LIMIT 1 ';
		$campi['stato_scansione']=Cartelle::statoDaFare;
	} else {
		// viene ignorato lo stato scansione
		$cartelle_h->set_record_id($cartella_id);
		$campi=[];
		$campi['query'] = 'SELECT * FROM ' . Cartelle::nomeTabella
		. ' WHERE record_id = :record_id ';
		$campi['record_id'] = $cartelle_h->get_record_id();	
	}

	echo '<h2 style="font-family:monospace;">Carica in scansioni_disco da scansioni_cartelle</h2>';
	$ret_car = $cartelle_h->leggi($campi);

	// arrivati errori
	if (isset($ret_car['error'])){
		http_response_code(404);
		$ret = __FUNCTION__ 
		. '<br>Album non trovato per un errore:'
		. '<br>'. $ret_car['message'] 
		. '<br>campi: ' . serialize($campi);
		echo $ret; 
		exit(1);
	}

	// nessun errore ma nessun record
	if ($ret_car['numero'] == 0){
		http_response_code(404);
		$ret = "<p style='font-family:monospace;'>".
		"Nessun caricamento in deposito della cartella id: $cartella_id "
		. " (0 = la prima che c'è)<br>Nessun lavoro in sospeso o Cartella non trovata."
		. "<br>Chiudere e passare al prossimo step.".'</p>';
		echo $ret; 
		exit(1);
	}

	$cartella = $ret_car['data'][0];
	$cartella_id=$cartella['record_id'];
	echo '<p style="font-family:monospace">Cartella: '.$cartella_id;
	echo '<br>'. str_replace(';', '; ', serialize($cartella)) . '</p>';
	if ($cartella['stato_scansione'] != Cartelle::statoDaFare ){
		http_response_code(404);
		$ret = "<p style='font-family:monospace;'>Album "
		. " $cartella_id non lavorabile. stato_scansione: " 
		. $cartella['stato_scansione'] .'</p>';
		echo $ret;
		exit(1);
	}
	$ret_car=[];

	// cambio status 
	if (!set_stato_scansione( $cartella['record_id'], Cartelle::statoLavoriInCorso )){
		http_response_code(404);
		$ret = "<p style='font-family:monospace;'>Non è "
		. "stato variato lo stato della cartella $cartella_id. </p>";
		echo $ret;
		exit(1);
	}

	/**
	 * caricamento cartella (da scansioni_cartelle) in scansioni_disco 
	 * fs_cartella quello del server 
	 */
	$disco   = $cartella['disco'];
	$disco_h = New ScansioniDisco($dbh);

	// si converte il percorso_completo tabella in un percorso nel server 
	// deve iniziare con ./ 
	$percorso_fs_cartella = str_ireplace( URLBASE, './', $cartella['percorso_completo']);
	$percorso_fs_cartella = str_replace('%20', ' ',    $percorso_fs_cartella);
	if ($percorso_fs_cartella[0]=='/'){
		$percorso_fs_cartella = '.'.$percorso_fs_cartella;
	}
	$percorso_con_abspath = str_replace('./', ABSPATH, $percorso_fs_cartella);
	$percorso_fs_cartella = str_replace('./', '/'    , $percorso_fs_cartella);
	@list($livello1, $livello2, $livello3, 
	      $livello4, $livello5, $livello6) = explode('/', mb_substr($percorso_fs_cartella, 1));
	if (is_dir($percorso_con_abspath)){
		// Se c'è già cosa va fatto?

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
		$ret_car = $disco_h->aggiungi($campi);
		//echo "\n ret_car : ";
		//echo var_dump($ret_car);
		if ( isset($ret_car['error'])){
			http_response_code(404); // cambiare codice
			echo "<p style='font-family:monospace;'>"
			. "Non è stata inserita la cartella in scansioni_disco.<br>"
			. $ret_car['message']."</p>";
			exit(1);
		}
		$ret_car=[];

		/**
		 * scansiono elemento per elemento 
		 * il contenuto della directory percorso_fs_cartella
		 */
		$contenuto_fs = dir($percorso_con_abspath);
		if ( $contenuto_fs === false){
			if (!set_stato_scansione( $cartella['record_id'], Cartelle::statoCompletato )){
				$errori .= '<br>Non è stato possibile cambiare stato_scansione in completato.';
			}
			http_response_code(404); // cambiare codice
			echo "<p style='font-family:monospace;'>Non è stato letto il contenuto di $percorso_fs_cartella.</p>";
			exit(1);
		}
		while ($elemento = $contenuto_fs->read()) {
			// escludo i file che iniziano con '.' 
			// . 
			// .. 
			// ._nomefile 
			// .DS_Store ecc 
			if ($elemento[0] == '.' ){
				continue; // 
			} 

			$percorso_piu_elemento = str_ireplace( '//', '/', $percorso_con_abspath.'/'.$elemento);

			// aggiunta alla tabella scansioni_cartelle 
			if (is_dir($percorso_piu_elemento) ){
				// Se c'è già viene "resettato" in 
				$campi=[];
				$campi['disco'] = $disco;
				$campi['percorso_completo'] = $percorso_fs_cartella.$elemento.'/';
				$ret_car = $cartelle_h->aggiungi($campi);
				if (isset($ret_car['error'])){
					$errori .= '<br>Nel caricamento in scansioni_cartelle si è verificato questo:'
					. '<br>' . $ret_car['message']
					. ' campi: ' . serialize($campi);
				}
				$ret_car=[];
				echo "\n".'<br>Caricamento eseguito in cartelle: '.serialize($campi);
			} // is_dir($percorso_piu_elemento)

			// aggiunta alla tabella scansioni_disco 
			if (is_dir($percorso_piu_elemento) ){
				$campi=[];
				$campi['disco']   = $disco;
				$campi['livello1']= $livello1;
				$campi['livello2']= $livello2;
				$campi['livello3']= $livello3;
				$campi['livello4']= $livello4;
				$campi['livello5']= $livello5;
				$campi['livello6']= $livello6;
				// brutta ma serve 
				if ($livello2 == '') { 
					$campi['livello2'] = $elemento; 
				} elseif ($livello3 == '') {
					$campi['livello3'] = $elemento; 
				} elseif ($livello4 == '') {
					$campi['livello4'] = $elemento; 
				} elseif ($livello5 == '') {
					$campi['livello5'] = $elemento; 
				} else {
					//! if livello6>'' houston abbiamo un problema 
					$campi['livello6'] = $elemento; 
				}
				$campi['nome_file'] = '/';
				$campi['estensione'] = '';
				$campi['modificato_il'] = date("Y-m-d H:i:s", filemtime($percorso_piu_elemento));
				$campi['codice_verifica'] = '0';
				$campi['tinta_rgb'] = '000000';
				if ( isset($ret_car['error'])){
					$ret_car = $disco_h->aggiungi($campi);
					$errori .= '<br>Nel caricamento in scansioni_disco si è verificato questo:'
					. '<br>' . $ret_car['message']
					. ' campi: ' . serialize($campi);
				}
				continue;
			} // is_dir($percorso_piu_elemento)
			
			// aggiunta alla tabella scansioni_disco
			if (is_file($percorso_piu_elemento)){
				$punto_estensione = strrpos($elemento, '.');
				if ($punto_estensione===false){
					$estensione='?';
				} else {
					$estensione=substr($elemento, ($punto_estensione + 1), 6);
					$estensione = strtolower($estensione);
				}
				// estensioni gestite:
				//! TODO gestione file tipo shortcut - versione windows
				if (!in_array($estensione, ['jpg', 'jpeg', 'tif', 'tiff', 'psd', 'mp4'])){
					// salta tutto e avanti al prossimo
					continue;
				}
				// aggiunta alla tabella scansioni_disco
				$campi=[];
				$campi['disco']    = $disco;
				$campi['livello1']=$livello1;
				$campi['livello2']=$livello2;
				$campi['livello3']=$livello3;
				$campi['livello4']=$livello4;
				$campi['livello5']=$livello5;
				$campi['livello6']=$livello6;
				$campi['nome_file']  = $elemento;
				$campi['estensione'] = $estensione;
				$campi['codice_verifica'] = md5_file($percorso_piu_elemento); // prende tempo...
				$campi['tinta_rgb']  = '000000';
				$ret_car = $disco_h->aggiungi($campi);
				if ( isset($ret_car['error'])){
					$errori .= '<br>Nel caricamento in scansioni_disco si è verificato questo:'
					. '<br>' . $ret_car['message']
					. ' campi: ' . serialize($campi);
				} else {
					echo "\n".'<p style="font-family:monospace;">Caricamento eseguito in scansioni_disco <br>' 
					. str_replace(';', '; ',serialize($campi)) . '<br>'
					. str_replace(';', '; ',serialize($ret_car)) . '</p>';
				}
			} // is_file($percorso_piu_elemento)

		} // while() - lettura directory - folder
		echo "\n".'<br>caricamento directory completato';
		if (!set_stato_scansione( $cartella['record_id'], Cartelle::statoCompletato )){
			$errori .= '<br>Non è stato possibile cambiare stato_scansione in completato.';
		}
	} // is_dir()

	if (is_file($percorso_piu_elemento)){
		// Però però però dovrebbe essere una cartella però... 

		// recupero estensione 
		$punto_estensione = strrpos($percorso_piu_elemento, '.');
		if ($punto_estensione===false){
			$estensione='?';
		} else {
			$estensione=substr($elemento, ($punto_estensione + 1), 6);
			$estensione = strtolower($estensione);
		}
		// estensioni gestite:
		//! TODO gestione file tipo shortcut - versione windows
		if (!in_array($estensione, ['jpg', 'jpeg', 'tif', 'tiff', 'psd', 'mp4'])){
			// salta tutto e avanti al prossimo
			exit(0);
		}

		// aggiunta file alla tabella scansioni_disco
		$campi=[];
		$campi['disco']    = $disco;
		$campi['livello1'] = $livello1;
		$campi['livello2'] = $livello2;
		$campi['livello3'] = $livello3;
		$campi['livello4'] = $livello4;
		$campi['livello5'] = $livello5;
		$campi['livello6'] = $livello6;
		$campi['nome_file']  = $elemento;
		$campi['estensione'] = $estensione;
		$campi['codice_verifica'] = md5_file($percorso_piu_elemento); // prende tempo...
		$campi['tinta_rgb']  = '000000';
		$ret_car = $disco_h->aggiungi($campi);

		if ( isset($ret_car['error'])){
			$errori .= '<br>Nel caricamento in scansioni_disco si è verificato questo:'
			. '<br>' . $ret_car['message']
			. ' campi: ' . serialize($campi);
		}

	} // is_file()

	// cambio status - la cartella in scansione_cartelle non viene rielaborata
	if (!set_stato_scansione( $cartella['record_id'], Cartelle::statoCompletato )){
		$errori .= '<br>Non è stato possibile cambiare stato_scansione in completato.';
	}

	if ($errori){
		http_response_code(404); //! TODO Cambiare codice 
		echo '<pre>'.$errori;
		exit(1);
	}
	echo "<p style='font-family:monospace;'>Lavoro eseguito</p>";
	exit(0);

} // carica_cartelle_in_scansioni_disco()

/** TEST
 *  
 * https://www.fotomuseoathesis.it/aa-controller/cartelle-controller.php?id=1111&test=carica_cartelle_in_scansioni_disco
 */
	if (isset($_GET['test'])   &&	 
			isset($_GET['id'])     && 
			$_GET['test'] == 'carica_cartelle_in_scansioni_disco'){
		echo '<pre style="max-width:50rem;">debug on'."\n";
		echo carica_cartelle_in_scansioni_disco( $_GET['id']);
		echo '<br>fine';
	}
//

/**
 * Il modulo chiede due dati
 * - il disco
 * - il "percorso completo" es. "/9TERRI/Bonifiche/", 
 *   che deve avere alla fine la /
 * 
 */
function carica_cartelle_in_scansioni_cartelle(array $dati_input = [] ){

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
			$percorso_completo = $dati_input['cartella'].'/'; // prima lo aggiungo 
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
} // carica_cartelle_in_scansioni_cartelle()
