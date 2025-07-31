<?php
/**
 * @source /aa-controller/vocabolario-controller.php
 * @author Massimo rainato <maxrainato@libero.it>
 * 
 * vocabolario controller
 * 
 * Si occupa delle funzioni che riguardano la tabella vocabolario 
 * 
 * - get_elenco_generale 
 *   realizza il codice html per la pubblicazione dell'elenco
 *   di tutte le chiavi e del loro vocabolario 
 * - aggiungi_vocabolario
 *   espone il modulo per chiedere il dato oppure 
 *   esegue l'inserimento in vocabolario 
 * - modifica_vocabolario
 *   espone il modulo per chiedere il dato oppure 
 *   esegue l'aggiornamento in vocabolario 
 * 
 * 
 */
if (!defined('ABSPATH')){
	include_once('../_config.php');
}
include_once(ABSPATH . 'aa-model/database-handler-oop.php');
include_once(ABSPATH . 'aa-model/vocabolario-oop.php');
include_once(ABSPATH . 'aa-model/chiavi-oop.php');
include_once(ABSPATH . 'aa-controller/controller-base.php');

/**
 * Espone la pagina con l'elenco generale
 * @param void
 * @return void - espone html 
 */
function get_elenco_generale(){
	$ret = '';
	$elenco_generale="";
	$dbh   = New DatabaseHandler();
	$voca_h= New Vocabolario($dbh);

	$campi=[];
	$campi['query'] = 'SELECT record_id, chiave, valore FROM ' . Vocabolario::nome_tabella
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. ' ORDER BY chiave, valore '; 
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
	$ret_voca = $voca_h->leggi($campi);
	if (isset($ret_voca['error'])){
		$ret = '<p class="text-monospace">'
		. 'Si è verificato un errore e non sono stati rintracciati record.'
		. '<br>' . $ret_voca['message']
		. '</p>';
		$_SESSION['messaggio'] = $ret;
		$elenco_generale = '<p>Nessun record</p>';
    require(ABSPATH.'aa-view/vocabolario-elenco-generale-view.php');
    exit(0);
	}
	if ($ret_voca['numero'] < 1 ){
		$ret = '<p class="fs-3 text-monospace">'
		. 'Non sono stati rintracciati record.'
		. '</p>';
		$_SESSION['messaggio'] = $ret;
		$elenco_generale = '<p>Nessun record</p>';
    require(ABSPATH.'aa-view/vocabolario-elenco-generale-view.php');
    exit(0);
	}
	// loop 
	$i=0;
	$ret  =         '<div class="card">';
	$ret .= "\n\t". '<div class="card-header">';
	$ret .= "\n\t\t". '<h4>'.$ret_voca['data'][$i]['chiave']
	                . '<a href="'.URLBASE.'vocabolario.php/aggiungi/'.$ret_voca['data'][$i]['chiave'].'" '
										. 'class="btn btn-success btn-sm float-end">Aggiungi valore</a>'
	                . '</h4>';
	$ret .= "\n\t". '</div>';
	$ret .= "\n\t". '<div class="card-body">';
	$ret .= "\n\t\t". '<table class="table table-bordered table-striped">';
	$ret .= "\n\t\t". '<tbody>';
	$ret .= "\n\t\t". '<tr><td>'.$ret_voca['data'][$i]['valore'].'</td>';
	$ret .= "\n\t\t". '<td><a href="'.URLBASE.'vocabolario.php/modifica/'.$ret_voca['data'][$i]['record_id'].'" '
	. 'class="btn btn-success btn-sm float-end">Modifica</a></td></tr>';
	for ($i=1; $i < count($ret_voca['data']); $i++) {
		$j=$i-1;
		if ($ret_voca['data'][$i]['chiave'] != $ret_voca['data'][$j]['chiave']) {
			$ret .= "\n\t\t". '</tbody>';
			$ret .= "\n\t\t". '</table>';
			$ret .= "\n\t". '</div><!-- card-body -->';
			$ret .= "\n\t". '<div class="card-header">';
			$ret .= "\n\t\t". '<h4>'.$ret_voca['data'][$i]['chiave']
											. '<a href="'.URLBASE.'vocabolario.php/aggiungi/'.$ret_voca['data'][$i]['chiave'].'" '
												. 'class="btn btn-success btn-sm float-end">Aggiungi valore</a>'
											. '</h4>';
			$ret .= "\n\t". '</div>';
			$ret .= "\n\t". '<div class="card-body">';
			$ret .= "\n\t\t". '<table class="table table-bordered table-striped">';
			$ret .= "\n\t\t". '<tbody>';
		}
		$ret .= "\n\t\t\t". '<tr><td>'.$ret_voca['data'][$i]['valore'].'</td>';
		$ret .= "\n\t\t\t". '<td><a href="'.URLBASE.'vocabolario.php/modifica/'.$ret_voca['data'][$i]['record_id'].'" '
		. 'class="btn btn-success btn-sm float-end">Modifica</a></td></tr>';
	}
	$ret .= "\n\t\t". '</tbody>';
	$ret .= "\n\t\t". '</table>';
	$ret .= "\n\t". '</div><!-- card-body -->';
	$ret .= "\n\t". '</div><!-- card -->';
	$elenco_generale = $ret;
	require(ABSPATH.'aa-view/vocabolario-elenco-generale-view.php');
	exit(0);
} // get_elenco_generale()

/**
 * Aggiungi a vocabolario
 * 
 * @param  string $chiave 
 * @param  array  $dati_input 
 * @return void   Espone le pagine web
 */
function aggiungi_vocabolario(string $chiave = '', array $dati_input = []){
	// se mancano i dati del modulo passo a esporre il modulo 
	$dbh    = New DatabaseHandler();
	$chi_h  = New Chiavi($dbh);
	$voca_h = New Vocabolario($dbh);
	
	$chiave = strtolower($chiave);
	$chiave = strip_tags($chiave);
	$chiave = htmlentities($chiave);

	$campi=[];
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
	$campi['chiave'] = strtolower($chiave);
	$campi['query'] = 'SELECT 1 FROM ' . Chiavi::nome_tabella
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. ' AND chiave = :chiave '
	. ' LIMIT 1 ';
	$ret_chi = $chi_h->leggi($campi);
	if (isset($ret_chi['error'])){
		// non si fa niente, però per debug 
		$ret = 'Non è stata rintracciata la chiave fornita. '
		. '<br>' . $ret_chi['message'];
		$_SESSION['messaggio'] = $ret;
		require(ABSPATH.'aa-view/vocabolario-aggiungi-view.php');
		exit(0);
	}
	if ($ret_chi['numero'] < 1){
		// non si fa niente, però per debug 
		$ret = 'Non è stata rintracciata la chiave fornita. ';
		$_SESSION['messaggio'] = $ret;
		require(ABSPATH.'aa-view/vocabolario-aggiungi-view.php');
		exit(0);
	}
	// Se mancano i dati si espone il modulo 
	if (!isset($dati_input['aggiungi_vocabolario'])){
		$_SESSION['messaggio'] = "L'inserimento di un nuovo dato dev'essere fatto "
		. "con competenza e aggiornando nel caso il manuale.";
		require(ABSPATH.'aa-view/vocabolario-aggiungi-view.php');
		exit(0);
	}
	// Si passa all'inserimento 
	$campi=[];
	$campi['chiave'] = $chiave;
	$campi['valore'] = $dati_input['valore'];
	$ret_voca = $voca_h->aggiungi($campi);
	if (isset($ret_voca['error'])){
		$_SESSION['messaggio'] = "NO, si è verificato qualcosa durante l'inserimento."
		. "<br>".$ret_voca['message'];
		require(ABSPATH.'aa-view/vocabolario-aggiungi-view.php');
		exit(0);
	} 
	// Ok, fatto e avanti il prossimo
	$_SESSION['messaggio'] = "Coppia chiave-valore aggiunta al vocabolario";
	require(ABSPATH.'aa-view/vocabolario-aggiungi-view.php');
	exit(0);
} // aggiungi_vocabolario()


function modifica_vocabolario( int $vocabolario_id, array $dati_input){
	$ret = '';
	$dbh   = New DatabaseHandler();
	$voca_h= New Vocabolario($dbh);

	// 
	$voca_h->set_record_id($vocabolario_id);
	$campi=[];
	$campi['query'] = 'SELECT * FROM ' . Vocabolario::nome_tabella
	. ' WHERE record_id = :record_id '
	. ' AND record_cancellabile_dal = :record_cancellabile_dal ';
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
	$campi['record_id'] = $voca_h->get_record_id();
	$ret_voca = $voca_h->leggi($campi);

	if (isset($ret_voca['error'])){
		$ret = '<p class="text-monospace">'
		. 'Si è verificato un errore e non sono stati rintracciati record.'
		. '<br>' . $ret_voca['message']
		. '</p>';
		$_SESSION['messaggio'] = $ret;
		$chiave = '***';
		$valore = '***';
    require(ABSPATH.'aa-view/vocabolario-modifica-view.php');
    exit(0);
	}
	if ($ret_voca['numero'] < 1){
		$ret = '<p class="text-monospace">'
		. 'Si è verificato un errore e non sono stati rintracciati record.'
		. '</p>';
		$_SESSION['messaggio'] = $ret;
		$chiave = '***';
		$valore = '***';
    require(ABSPATH.'aa-view/vocabolario-modifica-view.php');
    exit(0);
	}
	// mancano i dati si espone il modulo
	if (!isset($dati_input['modifica_vocabolario'])){
		$chiave = $ret_voca['data'][0]['chiave'];
		$valore = $ret_voca['data'][0]['valore'];
    require(ABSPATH.'aa-view/vocabolario-modifica-view.php');
    exit(0);
	}
	//
	$voca_h->set_record_id($vocabolario_id);
	$voca_h->set_valore($dati_input['valore']);
	$campi=[];
	$campi['update'] = 'UPDATE ' . Vocabolario::nome_tabella
	. ' SET valore = :valore '
	. ' WHERE record_id  = :record_id ';
	$campi['valore'] = $voca_h->get_valore();
	$campi['record_id'] = $voca_h->get_record_id();
	$ret_voca = [];
	$ret_voca = $voca_h->modifica($campi);
	if (isset($ret_voca['error'])) {
		$_SESSION['messaggio'] = "NO, si è verificato qualcosa durante l'inserimento."
		. "<br>".$ret_voca['message'];
		require(ABSPATH.'aa-view/vocabolario-modifica-view.php');
		exit(0);
	}
	// Ok, fatto e avanti il prossimo
	$_SESSION['messaggio'] = "Coppia chiave-valore aggiornata nel vocabolario";
	$vocabolario_id = $voca_h->get_record_id();
	$chiave = $voca_h->get_chiave();
	$valore = $voca_h->get_valore();
	require(ABSPATH.'aa-view/vocabolario-modifica-view.php');
	exit(0);
} // modifica_vocabolario()