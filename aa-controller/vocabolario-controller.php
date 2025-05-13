<?php
/**
 * @source /aa-controller/vocabolario-controller.php
 * @author Massimo rainato <maxrainato@libero.it>
 * 
 * chiavi_valori_vocabolario controller
 * 
 * Si occupa delle funzioni che riguardano la tabella chiavi_valori_vocabolario 
 * 
 * - get_elenco_generale 
 *   realizza il codice html per la pubblicazione dell'elenco
 *   di tutte le chiavi e del loro vocabolario 
 * 
 * 
 */
if (!defined('ABSPATH')){
	include_once('../_config.php');
}
include_once(ABSPATH . 'aa-model/database-handler-oop.php');
include_once(ABSPATH . 'aa-model/vocabolario-oop.php');
include_once(ABSPATH . 'aa-controller/controller-base.php');

/**
 * Espone la pagina con l'elenco generale
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
}