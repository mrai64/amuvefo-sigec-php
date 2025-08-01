<?php
/**
 * DEPOSITO controller
 *
 * funzioni relative ai file e cartelle inseriti in archivio
 * nella tabella deposito o che vanno a leggere/scrivere nella tabella deposito
 *
 * - crea_query_cartelle
 *
 * - crea_query_sottocartelle
 *
 * - leggi_cartella_per_id
 *
 * - leggi_cartella_per_percorso
 *
 * - verifica_cartella_contiene_album
 *
 * - cambio_tinta_record
 *
 *
 */
if (!defined('ABSPATH')){
	include_once('../_config.php');
}
include_once(ABSPATH.'aa-model/database-handler-oop.php'); // Class DatabaseHandler
include_once(ABSPATH.'aa-model/deposito-oop.php'); //         Class Deposito
include_once(ABSPATH.'aa-model/zona-intro-oop.php'); //       Class Cartelle
include_once(ABSPATH.'aa-model/album-oop.php'); //            Class Album


/**
 * @param  array  campi della tabella_deposito
 * @return string istruzione SQL per rintracciare eventuali sottocartelle
 */
function crea_query_cartella(array $campi) : string {
	$dbh = New DatabaseHandler();
	$dep_h = New Deposito($dbh);

	$livello1 = isset($campi['livello1']) ? $campi['livello1'] : "";
	$livello2 = isset($campi['livello2']) ? $campi['livello2'] : "";
	$livello3 = isset($campi['livello3']) ? $campi['livello3'] : "";
	$livello4 = isset($campi['livello4']) ? $campi['livello4'] : "";
	$livello5 = isset($campi['livello5']) ? $campi['livello5'] : "";
	$livello6 = isset($campi['livello6']) ? $campi['livello6'] : "";

	if ($livello6 > ''){
		$sql = 'SELECT * FROM ' . Deposito::nome_tabella
		.' WHERE livello1 = :livello1 AND livello2 = :livello2 '
		.   'AND livello3 = :livello3 AND livello4 = :livello4 '
		.   'AND livello5 = :livello5 AND livello6 = :livello6 '
		.   "AND estensione = '' "
		.   "AND record_cancellabile_dal = '".$dbh->get_datetime_forever()."' ";
		return $sql;
	}

	if ($livello5 > ''){
		$sql = 'SELECT * FROM ' . Deposito::nome_tabella
		. ' WHERE livello1 = :livello1 '
		.   'AND livello2 = :livello2 '
		.   'AND livello3 = :livello3 '
		.   'AND livello4 = :livello4 '
		.   'AND livello5 = :livello5 '
		.   "AND livello6 = '' "
		.   "AND estensione = '' "
		.   "AND record_cancellabile_dal = '".$dbh->get_datetime_forever()."' ";
		return $sql;
	}

	if ($livello4 > ''){
		$sql = 'SELECT * FROM ' . Deposito::nome_tabella
		. ' WHERE livello1 = :livello1 '
		.   'AND livello2 = :livello2 '
		.   'AND livello3 = :livello3 '
		.   'AND livello4 = :livello4 '
		.   "AND livello5 = '' "
		.   "AND estensione = '' "
		.   "AND record_cancellabile_dal = '".$dbh->get_datetime_forever()."' ";
		return $sql;
	}

	if ($livello3 > ''){
		$sql = 'SELECT * FROM ' . Deposito::nome_tabella
		. ' WHERE livello1 = :livello1 '
		.   'AND livello2 = :livello2 '
		.   'AND livello3 = :livello3 '
		.   "AND livello4 = '' "
		.   "AND estensione = '' "
		.   "AND record_cancellabile_dal = '".$dbh->get_datetime_forever()."' ";
		return $sql;
	}

	if ($livello2 > ''){
		$sql = 'SELECT * FROM ' . Deposito::nome_tabella
		. ' WHERE livello1 = :livello1 '
		.   'AND livello2 = :livello2 '
		.   "AND livello3 = '' "
		.   "AND estensione = '' "
		.   "AND record_cancellabile_dal = '".$dbh->get_datetime_forever()."' ";
		return $sql;
	}

		$sql = 'SELECT * FROM ' . Deposito::nome_tabella
		. ' WHERE livello1 = :livello1 '
		.   "AND livello2 = '' "
		.   "AND estensione = '' "
		.   "AND record_cancellabile_dal = '".$dbh->get_datetime_forever()."' ";
	return $sql;
} // crea_query_cartella()

/**
 * @param array  campi della tabella_deposito
 * @return string istruzione SQL per rintracciare eventuali sottocartelle
 */
function crea_query_sottocartelle(array $campi) : string {
	$livello1 = isset($campi['livello1']) ? $campi['livello1'] : "";
	$livello2 = isset($campi['livello2']) ? $campi['livello2'] : "";
	$livello3 = isset($campi['livello3']) ? $campi['livello3'] : "";
	$livello4 = isset($campi['livello4']) ? $campi['livello4'] : "";
	$livello5 = isset($campi['livello5']) ? $campi['livello5'] : "";
	$livello6 = isset($campi['livello6']) ? $campi['livello6'] : "";

	
	if ($livello6 > ''){
		// non è possibile rilevare sottocartelle
		$sql = 'SELECT * FROM ' . Deposito::nome_tabella
		. ' WHERE livello1 = :livello1 '
		.   'AND livello2 = :livello2 '
		.   'AND livello3 = :livello3 '
		.   'AND livello4 = :livello4 '
		.   'AND livello5 = :livello5 '
		.   'AND livello6 = :livello6 '
		.   "AND estensione = '' "
		.   "AND record_cancellabile_dal = '9999-12-31 23:59:59' ";
		return $sql;
	}
 
	if ($livello5 > '' AND $livello6 == ''){
		$sql = 'SELECT * FROM ' . Deposito::nome_tabella
		. ' WHERE livello1 = :livello1 '
		.   'AND livello2 = :livello2 '
		.   'AND livello3 = :livello3 '
		.   'AND livello4 = :livello4 '
		.   'AND livello5 = :livello5 '
		.   "AND livello6 > '' "
		.   "AND estensione = '' "
		.   "AND record_cancellabile_dal = '9999-12-31 23:59:59' "
		.   'ORDER BY livello6 ';
		return $sql;
	}
 
	if ($livello4 > '' AND $livello5 == ''){
		$sql = 'SELECT * FROM ' . Deposito::nome_tabella
		. ' WHERE livello1 = :livello1 '
		.   'AND livello2 = :livello2 '
		.   'AND livello3 = :livello3 '
		.   'AND livello4 = :livello4 '
		.   "AND livello5 > '' "
		.   "AND livello6 = '' "
		.   "AND estensione = '' "
		.   "AND record_cancellabile_dal = '9999-12-31 23:59:59' "
		.   'ORDER BY livello5 ';
		return $sql;
	}

	if ($livello3 > '' AND $livello4 == ''){
		$sql = 'SELECT * FROM ' . Deposito::nome_tabella
		. ' WHERE livello1 = :livello1 '
		.   'AND livello2 = :livello2 '
		.   'AND livello3 = :livello3 '
		.   "AND livello4 > '' "
		.   "AND livello5 = '' "
		.   "AND estensione = '' "
		.   "AND record_cancellabile_dal = '9999-12-31 23:59:59' "
		.   'ORDER BY livello4 ';
		return $sql;
	}

	if ($livello2 > '' AND $livello3 == ''){
		$sql = 'SELECT * FROM ' . Deposito::nome_tabella
		. ' WHERE livello1 = :livello1 '
		.   'AND livello2 = :livello2 '
		.   "AND livello3 > '' "
		.   "AND livello4 = '' "
		.   "AND estensione = '' "
		.   "AND record_cancellabile_dal = '9999-12-31 23:59:59' "
		.   'ORDER BY livello3 ';
		return $sql;
	}

	if ($livello1 > '' AND $livello2 == ''){
		$sql = 'SELECT * FROM ' . Deposito::nome_tabella
		. ' WHERE livello1 = :livello1 '
		.   "AND livello2 > '' "
		.   "AND livello3 = '' "
		.   "AND record_cancellabile_dal = '9999-12-31 23:59:59' "
		.   "AND estensione = '' "
		.   'ORDER BY livello2 ';
		return $sql;
	}

	$sql = "SELECT * FROM ' . Deposito::nome_tabella
	. ' WHERE livello1 = ''" ;
	return $sql;
} // crea_query_sottocartelle()


/**
 * Legge la cartella da deposito
 * se è presente un album (con altro id ma legato all'id di deposito)
 * "gira la palla a" leggi_album_per_id
 *
 * Oppure mostra quanto trovato, cerca le sottocartelle
 * se è presente un file _leggimi.txt lo mostra come didascalia
 *
 * @param int     $deposito_id
 * @return string $html_ret
 */
function leggi_cartella_per_id(int $deposito_id) {
	$dbh  = New DatabaseHandler(); // nessun parametro dedicato
	$dep = New Deposito($dbh);
	$alb_h = New Album($dbh);

	// verifica record in deposito
	$dep->set_record_id($deposito_id);
	$campi = [];
	$campi['query'] = 'SELECT * FROM ' . Deposito::nome_tabella
	. ' WHERE record_id = :record_id ';
	$campi['record_id'] = $dep->get_record_id();
	$ret = $dep->leggi($campi);
	if (isset($ret['error']) || $ret['numero'] == 0){
		http_response_code(404);
		echo ("Non trovato" . $ret['message'] );
		exit(1);
	}
	if ($ret['numero'] == 0){
		http_response_code(404);
		echo ("Non trovato" );
		exit(1);
	}

	// verifica album già presente, gira a mostrare l'album
	$campi = [];
	$campi['query'] = 'SELECT record_id FROM album '
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. 'AND record_id_in_deposito = :record_id_in_deposito ';
	$campi['record_cancellabile_dal']      = $dbh->get_datetime_forever();
	$campi['record_id_in_deposito'] = $dep->get_record_id();
	$ret_album = $alb_h->leggi($campi);
	if  (isset($ret_album['ok']) && $ret_album['numero'] > 0){
		$album_id = $ret_album['data'][0]['record_id'];
		// si passa la palla
		header('Location: '.URLBASE.'album.php/leggi/'.$album_id);
		exit(0);
	}
	
	$cartella_radice = $ret['data'][0]; // ret data è sempre array
	// no album
	// ricerca sottocartelle della cartella trovata
	
	$campi = [];
	$campi['query'] = crea_query_sottocartelle($cartella_radice);
	// se il campo è presente ma vuoto influisce sulla leggi() causando un errore
	if (str_contains($campi['query'], ':livello1')){
		$campi['livello1'] = $cartella_radice['livello1'];
	}
	if (str_contains($campi['query'], ':livello2')){
		$campi['livello2'] = $cartella_radice['livello2'];
	}
	if (str_contains($campi['query'], ':livello3')){
		$campi['livello3'] = $cartella_radice['livello3'];
	}
	if (str_contains($campi['query'], ':livello4')){
		$campi['livello4'] = $cartella_radice['livello4'];
	}
	if (str_contains($campi['query'], ':livello5')){
		$campi['livello5'] = $cartella_radice['livello5'];
	}
	if (str_contains($campi['query'], ':livello6')){
		$campi['livello6'] = $cartella_radice['livello6'];
	}
	$ret = $dep->leggi($campi);
	if (isset($ret['error'])){
		http_response_code(404);
		exit("Errore in ricerca sottocartelle" . $ret['message']);
	}
	$sottocartelle = $ret['data']; // sottocartelle è sempre array
	// dati per view
	$cartella  =  $cartella_radice['livello1'];
	$cartella .= ($cartella_radice['livello2']) ? ' / ' . $cartella_radice['livello2'] : "";
	$cartella .= ($cartella_radice['livello3']) ? ' / ' . $cartella_radice['livello3'] : "";
	$cartella .= ($cartella_radice['livello4']) ? ' / ' . $cartella_radice['livello4'] : "";
	$cartella .= ($cartella_radice['livello5']) ? ' / ' . $cartella_radice['livello5'] : "";
	$cartella .= ($cartella_radice['livello6']) ? ' / ' . $cartella_radice['livello6'] : "";
	
	/*
	 Si deve verificare: se nella cartella fisicamente
		collocata in livello1/livello2/... è presente un file _leggimi.txt
		e proporlo, in alternativa si può leggere una didascalia
		associata alla tabella Deposito + id
	*/
	$leggimi = "";
	$leggimi_file = '../'.str_replace(' / ', '/', $cartella)."/_leggimi.txt";
	$leggimi_file = str_replace('+', ' ', $leggimi_file);
	if (is_file($leggimi_file)){
		$leggimi = file_get_contents($leggimi_file);
	}

	// Ritorno a /museo.php
	$torna_base = URLBASE.'museo.php';
	$torna_sala = URLBASE.'deposito.php/cartella/'.$cartella_radice['livello1'].'/';

	// finito,. si applica e si mostra.
	require_once(ABSPATH."aa-view/cartelle-sottocartelle-view.php");
	exit(0);
} // leggi_cartella_per_id

/**
 *
 */
function leggi_cartella_per_percorso( string $percorso ){

	// input vuoto
	if ($percorso == ''){
		http_response_code(404);
		exit(1);
	}
	$percorso = urldecode($percorso);
	$percorso = htmlspecialchars(strip_tags($percorso));
	$percorso = str_replace(URLBASE, '', $percorso);

	if (!str_contains($percorso, '/')){
		$spezzato = ["livello1" => $percorso, "livello2" => ''];
	} else {
		$spezzato = explode("/", $percorso);
	}
	// massimo 6 livelli, oltre: rifare codice un po'
	if (count($spezzato) > 6){
		http_response_code(404);
		exit("percorso con troppi / oltre 6 no ");
	}
	// caricamento $campi
	$campi = [];
	$ind=1; // non parte da 0
	foreach($spezzato as $tmp){
		if ($tmp > ''){
			$campi["livello".$ind] = $tmp;
			$ind++;
		}
	}
	
	$campi['query'] = crea_query_cartella($campi);
	
	// Ritorno a /museo.php
	$torna_base = URLBASE.'museo.php';
	$torna_sala = URLBASE.'deposito.php/cartella/'.$campi['livello1'].'/';


	$dbh  = New DatabaseHandler();
	$dep = New Deposito($dbh);

	$ret = $dep->leggi($campi);

	if (isset($ret['error'])){
		http_response_code(404);
		exit("<p style='font-family:monospace;'>$percorso non trovato. <br>" . $ret['message'] .'</p>');
	}
	if ($ret['numero'] == 0){
		http_response_code(404);
		exit("<p style='font-family:monospace;'>$percorso non trovato. <br>".'</p>');
	}
	// mi aspetto un solo record perché record_id fa chiave primaria
	$cartella_radice = $ret['data'][0]; // ret data è sempre array
	// ricerca sottocartelle - possono anche non essercene
	$campi = [];
	$campi['query'] = crea_query_sottocartelle($cartella_radice);
	// se il campo è presente ma vuoto influisce sulla leggi() causando un errore
	if (str_contains($campi['query'], ':livello1')){
		$campi['livello1'] = $cartella_radice['livello1'];
	}
	if (str_contains($campi['query'], ':livello2')){
		$campi['livello2'] = $cartella_radice['livello2'];
	}
	if (str_contains($campi['query'], ':livello3')){
		$campi['livello3'] = $cartella_radice['livello3'];
	}
	if (str_contains($campi['query'], ':livello4')){
		$campi['livello4'] = $cartella_radice['livello4'];
	}
	if (str_contains($campi['query'], ':livello5')){
		$campi['livello5'] = $cartella_radice['livello5'];
	}
	if (str_contains($campi['query'], ':livello6')){
		$campi['livello6'] = $cartella_radice['livello6'];
	}

	$ret = $dep->leggi($campi);

	if (isset($ret['error'])){
		http_response_code(404);
		exit("Errore in ricerca sottocartelle" . $ret['message']);
	}
	$sottocartelle = $ret['data']; // sottocartelle è sempre array
	// dati per view
	$cartella  =  $cartella_radice['livello1'];
	$cartella .= ($cartella_radice['livello2']) ? ' / ' . $cartella_radice['livello2'] : "";
	$cartella .= ($cartella_radice['livello3']) ? ' / ' . $cartella_radice['livello3'] : "";
	$cartella .= ($cartella_radice['livello4']) ? ' / ' . $cartella_radice['livello4'] : "";
	$cartella .= ($cartella_radice['livello5']) ? ' / ' . $cartella_radice['livello5'] : "";
	$cartella .= ($cartella_radice['livello6']) ? ' / ' . $cartella_radice['livello6'] : "";

	/* DIDASCALIA della cartella
	Verificare se è presente un file _leggimi.txt
	e proporlo.
	*/
	$leggimi = "";
	//$leggimi_file = '../'.str_replace(' / ', '/', $cartella)."/_leggimi.txt";
	$leggimi_file = ABSPATH.str_replace(' / ', '/', $cartella)."/_leggimi.txt";
	$leggimi_file = str_replace('+', ' ', $leggimi_file);
	if (is_file($leggimi_file)){
		$leggimi = file_get_contents($leggimi_file);
	}

	// finito,. si applica e si mostra.
	require_once(ABSPATH."aa-view/cartelle-sottocartelle-view.php");
	exit(0);
} // leggi_cartella_per_percorso()

/**
 * verifica_cartella_contiene_album
 *
 * una volta caricata deposito con la cartelle
 * e le fotografie o i video, devo stabilire se c'è già un
 * album, e se sì finisce lì, oppure se è possibile
 * creare un album
 *
 * @param  int    $deposito_id
 * @return string $ret          'no'|'si'|'da caricare'
 */
function verifica_cartella_contiene_album( int $deposito_id) : string {
	$dbh    = New DatabaseHandler();
	$dep_h  = New Deposito($dbh);
	$alb_h  = New Album($dbh);

	$campi=[];
	$campi['query'] = 'SELECT 1 FROM album '
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. 'AND record_is_in_deposito = :record_id_in_deposito ';
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
	$campi['record_id_in_deposito'] = $deposito_id;
	$ret_alb = $alb_h->leggi($campi);
	if  (isset($ret_alb['ok']) && $ret_alb['numero'] > 0){
		return 'si'; // 
	}
	// return 'no' oppure 'da caricare' ?
	$campi = [];
	$campi['query'] = 'SELECT livello1, livello2, livello3, livello4, '
	. ' livello5, livello6 FROM ' . Deposito::nome_tabella
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. ' and record_id = :record_id ';
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
	$campi['record_id'] = $deposito_id;
	$ret_dep = $dep_h->leggi($campi);
	if ( isset($ret_dep['error']) || $ret_dep['numero'] == 0){
		return 'no'; // manca pure scansione_disco
	}
	$campi = [];
	$campi = $ret_dep['data'][0]; // livello1 .. livello6
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
	$campi['query'] = 'SELECT COUNT(*) AS NFOTO FROM ' . Deposito::nome_tabella
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. " AND nome_file <> '/' "
	. ' AND livello1 = :livello1  AND livello2 = :livello2 '
	. ' AND livello3 = :livello3  AND livello4 = :livello4 '
	. ' AND livello5 = :livello5  AND livello6 = :livello6 ';

	$ret_alb = [];
	$ret_alb = $dep_h->leggi($campi);
	if ( isset($ret_alb['error']) || $ret_alb['data'][0]['NFOTO'] == 0 ){
		return 'no';
	}
	return 'da caricare';
}

/**
 * Espone un modulo per cambiare la tinta in un record di
 * cartelle o sottocartelle in deposito
 */
function cambia_tinta_record(array $dati_input){
	if (!isset($dati_input['record_id']) || !isset($dati_input['tabella'])){
		echo "\n".'<p style="font-family:monospace;">';
		echo "\n"."Errore: la chiamata alla funzione è senza parametri corretti ";
		echo '</p>' . PHP_EOL;
		exit(1);
	}
	// necessari - record id
	if (!isset($dati_input['record_id']) || !is_numeric($dati_input['record_id']) || $dati_input['record_id'] < 1){
		echo "\n".'<p style="font-family:monospace;">';
		echo "\n"."Errore: la chiamata alla funzione è senza parametri corretti ";
		echo '<br>id</p>' . PHP_EOL;
		exit(1);
	}
	$record_id = (int) $dati_input['record_id'];
	// necessari - tabella
	$tabelle_valide=[
		'deposito'
	];
	if (!isset($dati_input['tabella']) || !in_array($dati_input['tabella'], $tabelle_valide)){
		echo "\n".'<p style="font-family:monospace;">';
		echo "\n"."Errore: la chiamata alla funzione è senza parametri corretti ";
		echo '<br>tabella</p>' . PHP_EOL;
		exit(1);
	}
	$tabella = $dati_input['tabella'];
	// necessari - pagina di ritorno
	if (!isset($dati_input['back']) || $dati_input['back'] === ''){
		echo "\n".'<p style="font-family:monospace;">';
		echo "\n"."Errore: la chiamata alla funzione è senza parametri corretti ";
		echo '<br>back | pagina di ritorno</p>' . PHP_EOL;
		exit(1);
	}
	// per evitare di metterlo doppio prima lo tolgo se c'è poi lo aggiungo
	$return_to = $dati_input['back'];
	$return_to = URLBASE . str_replace(URLBASE, '', $return_to);
	$return_to = str_replace('%20', '+', $return_to);
	$return_to = str_replace(' ', '+', $return_to);

	// Se manca il campo del modulo espongo il modulo
	if (!isset($dati_input['tinta'])){
		require_once(ABSPATH.'aa-view/cartelle-sottocartelle-tinta.php');
		exit(0);
	}
	// il campo c'è e si passa ad aggiornare SE...
	$tinta = $dati_input['tinta'];
	$tinta = substr(str_replace('#', '', $tinta), 0, 6);
	$campi=[];
	$campi['update'] = 'UPDATE ' . $tabella
	. " SET tinta_rgb = '$tinta' "
	. " WHERE record_cancellabile_dal = '". constant('FUTURO')."' "
	. " AND record_id = $record_id ";
	$dbh = new DatabaseHandler();
	$ret_att=[];
	switch ($tabella) {
		case 'deposito':
			$dep = New Deposito($dbh);
			$ret_att = $dep->modifica($campi);
			break;
		
		default:
			$ret_att= [
				'error'   => true,
				'message' => "Tabella $tabella non gestita"
			];
			break;
	}
	if (isset($ret_att['error'])){
		http_response_code(404);
		$ret = "<p style='font-family:monospace;'>Errore "
		. "in aggiornamento tinta: "
		. '<br>'. $ret_att['message'] .'</p>';
		$_SESSION['messaggio'] = $ret;
		echo "$ret" . PHP_EOL;
		echo "$return_to" . PHP_EOL;

    header("Location: ".$return_to);
		exit(1);
	}
	$_SESSION['messaggio'] = 'Tinta aggiornata';
	http_response_code(200);
	// echo "return_to::$return_to::" . PHP_EOL;
  header("Location: ".$return_to);
	exit(0);
}
