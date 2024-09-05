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
 * - carica_scansioni_disco_da_scansioni_cartelle 
 *   nome di funzione lunghissimo ma almeno è chiara la funzione 
 * - carica_cartelle_da_scansionare
 *   espone il modulo per l'aggiunta di una cartella in scansioni_cartelle
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
 * @param  array  campi della tabella_scansioni_disco
 * @return string istruzione SQL per rintracciare eventuali sottocartelle
 */
function crea_query_cartella(array $campi) : string {
	$livello1 = isset($campi['livello1']) ? $campi['livello1'] : "";
	$livello2 = isset($campi['livello2']) ? $campi['livello2'] : "";
	$livello3 = isset($campi['livello3']) ? $campi['livello3'] : "";
	$livello4 = isset($campi['livello4']) ? $campi['livello4'] : "";
	$livello5 = isset($campi['livello5']) ? $campi['livello5'] : "";
	$livello6 = isset($campi['livello6']) ? $campi['livello6'] : "";

	if ($livello6 > ''){
		$sql = 'SELECT * FROM scansioni_disco '
		. 'WHERE livello1 = :livello1 '
		.   'AND livello2 = :livello2 '
		.   'AND livello3 = :livello3 '
		.   'AND livello4 = :livello4 '
		.   'AND livello5 = :livello5 '
		.   'AND livello6 = :livello6 '
		.   "AND estensione = '' "
		.   "AND record_cancellabile_dal = '9999-12-31 23:59:59' ";
		return $sql;
	}

	if ($livello5 > ''){
		$sql = 'SELECT * FROM scansioni_disco '
			. 'WHERE livello1 = :livello1 '
			.   'AND livello2 = :livello2 '
			.   'AND livello3 = :livello3 '
			.   'AND livello4 = :livello4 '
			.   'AND livello5 = :livello5 '
			.   "AND livello6 = '' "
			.   "AND estensione = '' "
			.   "AND record_cancellabile_dal = '9999-12-31 23:59:59' ";
		return $sql;
	}

	if ($livello4 > ''){
		$sql = 'SELECT * FROM scansioni_disco '
			. 'WHERE livello1 = :livello1 '
			.   'AND livello2 = :livello2 '
			.   'AND livello3 = :livello3 '
			.   'AND livello4 = :livello4 '
			.   "AND livello5 = '' "
			.   "AND estensione = '' "
			.   "AND record_cancellabile_dal = '9999-12-31 23:59:59' ";
		return $sql;
	}

	if ($livello3 > ''){
		$sql = 'SELECT * FROM scansioni_disco '
			. 'WHERE livello1 = :livello1 '
			.   'AND livello2 = :livello2 '
			.   'AND livello3 = :livello3 '
			.   "AND livello4 = '' "
			.   "AND estensione = '' "
			.   "AND record_cancellabile_dal = '9999-12-31 23:59:59' ";
		return $sql;
	}

	if ($livello2 > ''){
		$sql = 'SELECT * FROM scansioni_disco '
			. 'WHERE livello1 = :livello1 '
			.   'AND livello2 = :livello2 '
			.   "AND livello3 = '' "
			.   "AND estensione = '' "
			.   "AND record_cancellabile_dal = '9999-12-31 23:59:59' ";
		return $sql;
	}

		$sql = 'SELECT * FROM scansioni_disco '
			. 'WHERE livello1 = :livello1 '
			.   "AND livello2 = '' "
			.   "AND estensione = '' "
			.   "AND record_cancellabile_dal = '9999-12-31 23:59:59' ";
	return $sql;
} // crea_query_cartella()

/**
 * @param array  campi della tabella_scansioni_disco 
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
		$sql = 'SELECT * FROM scansioni_disco '
		. 'WHERE livello1 = :livello1 '
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
		$sql = 'SELECT * FROM scansioni_disco '
		. 'WHERE livello1 = :livello1 '
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
		$sql = 'SELECT * FROM scansioni_disco '
		. 'WHERE livello1 = :livello1 '
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
		$sql = 'SELECT * FROM scansioni_disco '
		. 'WHERE livello1 = :livello1 '
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
		$sql = 'SELECT * FROM scansioni_disco '
		. 'WHERE livello1 = :livello1 '
		.   'AND livello2 = :livello2 '
		.   "AND livello3 > '' " 
		.   "AND livello4 = '' "
		.   "AND estensione = '' "
		.   "AND record_cancellabile_dal = '9999-12-31 23:59:59' "
		.   'ORDER BY livello3 ';
		return $sql;
	}

	if ($livello1 > '' AND $livello2 == ''){
		$sql = 'SELECT * FROM scansioni_disco '
		. 'WHERE livello1 = :livello1 '
		.   "AND livello2 > '' " 
		.   "AND livello3 = '' " 
		.   "AND record_cancellabile_dal = '9999-12-31 23:59:59' "
		.   "AND estensione = '' "
		.   'ORDER BY livello2 ';
		return $sql;
	}

	$sql = "SELECT * FROM scansioni_disco WHERE livello1 = ''" ;
	return $sql;
} // crea_query_sottocartelle()

/**
 * collaudo della funzione - richiamo diretto della pagina 
 * con $_GET['test']="crea_query_sottocartelle"
 */
if (isset($_GET['test']) && 
				 ($_GET['test'] == "crea_query_sottocartelle")){
	echo "<p style='font-family: monospace;max-width:60rem;'>\n";
	echo "test: " . __FILE__ . ' ' . "crea_query_sottocartelle"."<br />";
	$campiTest=[];
	echo "<br />Vuoto: ";
	echo crea_query_sottocartelle($campiTest);
	$campiTest['livello1'] = "VIDEO";
	echo "<br /><br />livello1: ";
	echo crea_query_sottocartelle($campiTest);
	$campiTest['livello2'] = "BOARA PISANI";
	echo "<br /><br />livello2: ";
	echo crea_query_sottocartelle($campiTest);
	$campiTest['livello3'] = "BOARA PISANI";
	$campiTest['livello4'] = "BOARA PISANI";
	$campiTest['livello5'] = "BOARA PISANI";
	echo "<br /><br />livello5: ";
	echo crea_query_sottocartelle($campiTest);
	$campiTest['livello6'] = "BOARA PISANI";
	echo "<br /><br />livello6: ";
	echo crea_query_sottocartelle($campiTest);
	echo "<br /><br />fine";
	exit(0);
}



/**
 * Legge la cartella da scansioni_disco 
 * se è presente un album (con altro id ma legato all'id di scansioni_disco) 
 * "gira la palla a" leggi_album_per_id
 * 
 * Oppure mostra quanto trovato, cerca le sottocartelle 
 * se è presente un file _leggimi.txt lo mostra come didascalia 
 * 
 * @param int     $scansioni_disco_id 
 * @return string $html_ret 
 */
function leggi_cartella_per_id(int $scansioni_disco_id) {
	$dbh  = New DatabaseHandler(); // nessun parametro dedicato
	$scan = New ScansioniDisco($dbh);
	$albh = New Album($dbh);

	// verifica record in scansioni_disco 
	$scan->set_record_id($scansioni_disco_id);
	$campi = [];
	$campi['query'] = 'SELECT * FROM scansioni_disco '
	. 'WHERE record_id = :record_id ';
	$campi['record_id'] = $scan->get_record_id();
	$ret = $scan->leggi($campi);
	if (isset($ret['error']) || $ret['numero'] == 0){
		http_response_code(404);
		echo ("Non trovato" . $ret['message'] );
		exit(1);
	}

	// verifica album già presente, gira a mostrare l'album  
	$campi = [];
	$campi['query'] = 'SELECT record_id FROM album '
	. 'WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. 'AND record_id_in_scansioni_disco = :record_id_in_scansioni_disco ';
	$campi['record_cancellabile_dal']      = $dbh->get_datetime_forever();
	$campi['record_id_in_scansioni_disco'] = $scan->get_record_id();
	$ret_album = $albh->leggi($campi);
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
	$ret = $scan->leggi($campi);
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
		associata alla tabella scansioni disco + id 
	*/ 
	$leggimi = "";
	$leggimi_file = '../'.str_replace(' / ', '/', $cartella)."/_leggimi.txt";
	$leggimi_file = str_replace('+', ' ', $leggimi_file);
	if (is_file($leggimi_file)){
		$leggimi = file_get_contents($leggimi_file);
	}

	// finito,. si applica e si mostra.
	require_once(ABSPATH."aa-view/cartelle-sottocartelle-view.php");
	exit(0);
} // leggi_cartella_per_id 

/**
 * collaudo della funzione . richiamo diretto della pagina
 */
if ( isset($_GET['test']) && 
		 isset($_GET['id'])   && 
		 $_GET['test'] == "leggi_cartella_per_id" ){
	$scansioni_disco_id = (int) $_GET['id'];
	leggi_cartella_per_id($scansioni_disco_id);
	exit(0);
}

function leggi_cartella_per_percorso( string $percorso ){
	// input vuoto
	if ($percorso == ''){
		http_response_code(404);
		exit(1);
	}
	$percorso = urldecode($percorso);
	$percorso = htmlspecialchars(strip_tags($percorso));
	$percorso = str_replace(BASEURL, '', $percorso);
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
	$ind=1;
	foreach($spezzato as $tmp){
		if ($tmp > ''){
			$campi["livello".$ind] = $tmp;
			$ind++;
		}
	}
	//dbg echo "<pre style='font-family:monospace;max-width:50rem;'>";
	//dbg echo '<br>Campi:';
	//dbg echo var_dump($campi);
	$campi['query'] = crea_query_cartella($campi);
	//dbg echo 'query: ' . $campi['query'];

	$dbh  = New DatabaseHandler();
	$scan = New ScansioniDisco($dbh);
	//dbg echo "<br>Ricerca cartella";
	//dbg echo var_dump($campi);

	$ret = $scan->leggi($campi);
	if (isset($ret['error']) || $ret['numero'] == 0){
		http_response_code(404);
		exit("<p style='font-family:monospace;'>$percorso non trovato. <br>" . $ret['message'] .'</p>');
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

	$ret = $scan->leggi($campi);
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
	associata alla tabella scansioni disco + id
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
 * collaudo della funzione 
 */
if (isset($_GET['test']) &&
		isset($_GET['percorso']) &&
		$_GET['test'] == "leggi_cartella_per_percorso" ){
			
	leggi_cartella_per_percorso( $_GET['percorso'] );

}


/**
 * verifica_cartella_contiene_album
 * 
 * una volta caricata scansioni_disco con la cartelle 
 * e le fotografie o i video, devo stabilire se c'è un 
 * album, e se sì finisce lì, oppure se è possibile 
 * creare un album
 * 
 * @param  int    $scansioni_id 
 * @return string $ret          'no'|'si'|'da caricare' 
 */
function verifica_cartella_contiene_album( int $scansioni_id) : string {
	$dbh    = New DatabaseHandler();
	$scan_h = New ScansioniDisco($dbh);
	$alb_h  = New Album($dbh);

	$campi=[];
	$campi['query'] = 'SELECT 1 FROM album '
	. 'WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. 'AND record_is_in_scansioni_disco = :record_id_in_scansioni_disco ';
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
	$campi['record_id_in_scansioni_disco'] = $scansioni_id;
	$ret_alb = $alb_h->leggi($campi);
	if  (isset($ret_alb['ok']) && $ret_alb['numero'] > 0){
		return 'si'; // tutt'apposto
	}
	// return 'no' oppure 'da caricare' ?
	$campi = [];
	$campi['query'] = 'SELECT livello1, livello2, livello3, livello4, '
	. ' livello5, livello6 FROM scansioni_disco '
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. ' and record_id = :record_id ';
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
	$campi['record_id'] = $scansioni_id;
	$ret_scan = $scan_h->leggi($campi);
	if ( isset($ret_scan['error']) || $ret_scan['numero'] == 0){
		return 'no'; // manca pure scansione_disco
	}
	$campi = [];
	$campi = $ret_scan['data'][0]; // livello1 .. livello6 
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
	$campi['query'] = 'SELECT COUNT(*) AS NFOTO FROM scansioni_disco '
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. " AND nome_file <> '/' "
	. ' AND livello1 = :livello1  AND livello2 = :livello2 '
	. ' AND livello3 = :livello3  AND livello4 = :livello4 '
	. ' AND livello5 = :livello5  AND livello6 = :livello6 ';
	//dbg echo '<br>Verifica scansioni_disco';
	//dbg echo var_dump($campi);

	$ret_alb = [];
	$ret_alb = $scan_h->leggi($campi);
	if ( isset($ret_alb['error']) || $ret_alb['data'][0]['NFOTO'] == 0 ){
		return 'no';
	}
	return 'da caricare';
}

/**
 * test - id è record_id di scansioni_disco 
 * https://archivio.athesis77.it/aa-controller/cartelle-controller.php?id=66&test=verifica_cartella_contiene_album
 */
if (isset($_GET['test']) && 
		isset($_GET['id'])   && 
		$_GET['test'] == 'verifica_cartella_contiene_album'){
	echo '<pre style="max-width:50rem;">debug on'."\n";
	echo verifica_cartella_contiene_album($_GET['id']);
	echo '<br>fine';
	exit(0);
}


/**
 * 
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
 * https://archivio.athesis77.it/aa-controller/cartelle-controller.php?test=lista-cartelle-sospese
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
	//echo "\n" . __FUNCTION__ ."\n";
	$dbh        = New DatabaseHandler(); // nessun parametro dedicato
	$cartelle_h = New Cartelle($dbh);

	$campi=[];
	$campi['update'] = 'UPDATE scansioni_cartelle '  
	. ' SET stato_scansione = :stato_scansione'
	. ' WHERE record_id = :record_id ';
	$campi['stato_scansione'] = $stato_scansione;
	$campi['record_id']       = $cartella_id;
	//dbg echo var_dump($campi);
	$ret_sta = $cartelle_h->modifica($campi);
	//echo "\n" . __FUNCTION__ ." ret_sta: \n";
	//echo var_dump($ret_sta);
	return (isset($ret_sta['ok']));
}

/**
 * 
function carica_campi_da_percorso( string $percorso_fs_cartella, array &$campi ){
	$dbh        = New DatabaseHandler(); // nessun parametro dedicato
	$sd_h    = New ScansioniDisco($dbh);

	$campi['nome_file']      = '/';
	$campi['estensione']     = '';
	$campi['codice_verifica']= '0';

	@list($livello1, $livello2, $livello3, $livello4, $livello5, $livello6) = 
		explode('/', substr($percorso_fs_cartella, 1));
	//echo "\n" . __FUNCTION__ . "\n";
	//echo var_dump( explode('/', substr($percorso_fs_cartella, 1) ));	
	
	$sd_h->set_livello1($livello1);
	$campi['livello1']=$sd_h->get_livello1();
	//echo "\n" . __FUNCTION__ . " livello1 \n";
	
	if ($livello2 > '') {
		$sd_h->set_livello2($livello2);
	}
	$campi['livello2']=$sd_h->get_livello2();
	//echo "\n" . __FUNCTION__ . " livello2 \n";
	
	if ($livello3 > '') {
		$sd_h->set_livello3($livello3);
	}
	$campi['livello3']=$sd_h->get_livello3();
	// echo "\n" . __FUNCTION__ . " livello3 \n";

	if ($livello4 > '') {
		$sd_h->set_livello4($livello4);
	}
	$campi['livello4']=$sd_h->get_livello4();

	if ($livello5 > '') {
		$sd_h->set_livello5($livello5);
	}
	$campi['livello5']=$sd_h->get_livello5();

	if ($livello6 > '') {
		$sd_h->set_livello6($livello6);
	}
	$campi['livello6']=$sd_h->get_livello6();

	// echo "\ncampi:";
	// echo var_dump($campi);

} // carica_campi_da_percorso()
 * 
 */


/**
 * @param  int $cartella_id - facoltativo
 *   Si tratta del record in scansioni_cartelle, se non viene passato 
 *   la funzione va a cercare un primo record da elaborare
 * @return void 
 */
function carica_scansioni_disco_da_scansioni_cartelle( int $cartella_id = 0) {
	$dbh        = New DatabaseHandler(); // nessun parametro dedicato
	$cartelle_h = New Cartelle($dbh);
	// echo "\n".'<p style="font-family:monospace;">';
	// echo "\n".'cartella_id: '.$cartella_id;
	$errori = '';

	// Se non viene passato un id si recupera "il primo che capita" 
	if ($cartella_id == 0){
		$campi=[];
		$campi['query'] = 'SELECT * FROM ' . Cartelle::nomeTabella 
		. ' WHERE stato_scansione IN ( :stato_scansione ) LIMIT 1 ';
		$campi['stato_scansione']=Cartelle::statoDaFare;
	} else {
		$cartelle_h->set_record_id($cartella_id);
		$campi=[];
		$campi['query'] = 'SELECT * FROM ' . Cartelle::nomeTabella
		. ' WHERE record_id = :record_id ';
		$campi['record_id'] = $cartelle_h->get_record_id();	
	}
	$ret_car = $cartelle_h->leggi($campi);
	if (isset($ret_car['error'])){
		http_response_code(404);
		$ret = __FUNCTION__ 
		. '<br>Album non trovato per un errore:'
		. '<br>'. $ret_car['message'] 
		. '<br>campi: ' . serialize($campi);
		echo $ret; 
		exit(1);
	}
	if ($ret_car['numero'] == 0){
		http_response_code(404);
		$ret = "<p style='font-family:monospace;'>Album $cartella_id non trovato. ".'</p>';
		echo $ret; 
		exit(1);
	}
	$cartella = $ret_car['data'][0];
	$cartella_id=$cartella['record_id'];
	$ret_car=[];
	if ($cartella['stato_scansione'] != Cartelle::statoDaFare ){
		http_response_code(404);
		$ret = "<p style='font-family:monospace;'>Album "
		. " $cartella_id non lavorabile. stato_scansione: " 
		. $cartella['stato_scansione'] .'</p>';
		echo $ret;
		exit(1);
	}
	// echo "\n".'step 3';
	// cambio status 
	if (!set_stato_scansione( $cartella['record_id'], Cartelle::statoLavoriInCorso )){
		http_response_code(404);
		$ret = "<p style='font-family:monospace;'>Non è "
		. "stato variato lo stato della cartella $cartella_id. </p>";
		echo $ret;
		exit(1);
	}
	// echo "\n".'step 4';
	/**
	 * caricamento cartella in scansioni_disco 
	 * fs_cartella quello del server 
	 */
	$disco   = $cartella['disco'];
	$disco_h = New ScansioniDisco($dbh);

	// si converte il dato i tabella in un percorso nel server 
	// deve iniziare con ./ 
	$percorso_fs_cartella = str_ireplace( URLBASE, './', $cartella['percorso_completo']);
	$percorso_fs_cartella = str_replace('%20', ' ',    $percorso_fs_cartella);
	if ($percorso_fs_cartella[0]=='/'){
		$percorso_fs_cartella = '.'.$percorso_fs_cartella;
	}
	$percorso_con_abspath = str_replace('./', ABSPATH, $percorso_fs_cartella);
  // echo "\n". 'percorso_con_abspath: ';
	// echo $percorso_con_abspath; 
	// echo "\n". 'is_dir: '. is_dir($percorso_con_abspath);
	
	$percorso_fs_cartella = str_replace('./', '/'    , $percorso_fs_cartella);
	@list($livello1, $livello2, $livello3, 
	      $livello4, $livello5, $livello6) = explode('/', mb_substr($percorso_fs_cartella, 1));
	if (is_dir($percorso_con_abspath)){
		// echo "\n". 'is_dir(percorso_fs_cartella): Sì';
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
			echo "<p style='font-family:monospace;'>Nono è stata inserita la cartella in scansioni_disco.<br>"
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
				echo "\n".'Caricamento eseguito '.serialize($campi);
			} // is_dir($percorso_piu_elemento)

			if (is_dir($percorso_piu_elemento) ){
				// aggiunta alla tabella scansioni_disco 
				$campi=[];
				$campi['disco']    = $disco;
				$campi['livello1']=$livello1;
				$campi['livello2']=$livello2;
				$campi['livello3']=$livello3;
				$campi['livello4']=$livello4;
				$campi['livello5']=$livello5;
				$campi['livello6']=$livello6;
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
				if (!in_array($estensione, ['jpg', 'jpeg', 'tif', 'tiff', 'mp4'])){
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
					echo "\n".'Caricamento eseguito '.serialize($campi);
				}
			} // is_file($percorso_piu_elemento)
		} // while()
		echo "\n".'caricamento is_dir completato';
		if (!set_stato_scansione( $cartella['record_id'], Cartelle::statoCompletato )){
			$errori .= '<br>Non è stato possibile cambiare stato_scansione in completato.';
		}
		} // is_dir()

	if ( is_file($percorso_piu_elemento)){
		// Però però però dovrebbe essere una cartella però... 
		$punto_estensione = strrpos($percorso_piu_elemento, '.');
		if ($punto_estensione===false){
			$estensione='?';
		} else {
			$estensione=substr($elemento, ($punto_estensione + 1), 6);
			$estensione = strtolower($estensione);
		}
		// estensioni gestite:
		//! TODO gestione file tipo shortcut - versione windows
		if (!in_array($estensione, ['jpg', 'jpeg', 'tif', 'tiff', 'mp4'])){
			// salta tutto e avanti al prossimo
			exit(0);
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
		}
	} // is_file()

	// echo "\ncambio status:lavoro finito";
	// cambio status 
	if (!set_stato_scansione( $cartella['record_id'], Cartelle::statoCompletato )){
		$errori .= '<br>Non è stato possibile cambiare stato_scansione in completato.';
	}

	if ($errori){
		http_response_code(404); //! TODO Cambiare codice 
		echo '<pre>'.$errori;
		exit(1);
	}
	echo "<p>Lavoro eseguito</p>";
	exit(0);

} // carica_scansioni_disco_da_scansioni_cartelle()

/** TEST
 *  
 * https://archivio.athesis77.it/aa-controller/cartelle-controller.php?id=1111&test=carica_scansioni_disco_da_scansioni_cartelle
 */
	if (isset($_GET['test'])   && 
			isset($_GET['id'])     && 
			$_GET['test'] == 'carica_scansioni_disco_da_scansioni_cartelle'){
		echo '<pre style="max-width:50rem;">debug on'."\n";
		echo carica_scansioni_disco_da_scansioni_cartelle( $_GET['id']);
		echo '<br>fine';
	}
//

/**
 * Espone solo la mappa che chiede la cartella e con jQuery 
 * si popola l'elenco delle cartelle 'da fare' 
 */
function carica_cartelle_da_scansionare(){
	if (isset($_POST['aggiungi_cartella'])){
		$dbh    = New DatabaseHandler();
		$scan_h = New Cartelle($dbh);
		$_SESSION['messaggio']='';
		//
		if (!isset($_POST['disco'])){
			$_SESSION['messaggio'] = "1. Si è verificato un problema nel caricamento della cartella"
			. " mancano i dati del modulo.";
		}
		if (!isset($_POST['cartella'])){
			$_SESSION['messaggio'] = "2. Si è verificato un problema nel caricamento della cartella"
			. " mancano i dati del modulo.";
		}
		if ($_SESSION['messaggio']==''){
			$campi=[];
			$campi['disco']=$_POST['disco'];
			$campi['percorso_completo']=$_POST['cartella'];
			$ret_scan = $scan_h->aggiungi($campi);
			if (isset($ret_scan['ok'])){
				$_SESSION['messaggio']='Cartella inserita in elenco sospesi';
			} else {
				$_SESSION['messaggio']=$ret_scan['message'];
			}	
		}
	} // $_POST['aggiungi_cartella']

	include_once(ABSPATH.'aa-view/cartelle-da-scansionare.php');
	exit(0);
}

