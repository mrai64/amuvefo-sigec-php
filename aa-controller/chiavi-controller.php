<?php
/**
 * @source /aa-controller/chiavi-controller.php
 * @author Massimo Rainato <maxrainato@liboer.it>
 *
 * Controller che si occupa di fornire dati presi dalla tabella chiavi-elenco
 * e altre funzioni da definire
 *
 * dipendenze: classe DatabaseHandler
 * dipendenze: classe Chiavi
 * 
 * - get_chiavi_datalist 
 * 
 * - elenco_chiavi
 * 
 * - aggiungi_chiavi_ricerca
 * 
 * - modifica-chiavi_ricerca 
 * 
 */
if (!defined('ABSPATH')){
  include_once('../_config.php');
}
include_once(ABSPATH . 'aa-model/database-handler-oop.php');
include_once(ABSPATH . 'aa-model/chiavi-oop.php');

/**
 * Fornisce l'elenco per la option list dei moduli.
 * Omonimo del metodo.
 */
function get_chiavi_datalist() : string {
  $dbh   = New DatabaseHandler();
  $chi_h = New Chiavi($dbh);
  
  return $chi_h->get_chiavi_datalist();
}


function elenco_chiavi(){
  // ci sono i dati si aggiunge 
  $dbh   = new DatabaseHandler();
  $key_h = new Chiavi($dbh);
  $elenco_chiavi = '<tr><td colspan="4">Non trovato</td></tr>';
  $campi=[];
  $campi['query']='SELECT * FROM ' . Chiavi::nome_tabella
  . " WHERE record_cancellabile_dal = :record_cancellabile_dal "
  . " order by chiave, record_id ";
  $campi['record_cancellabile_dal'] = constant('FUTURO');
  $ret_key = $key_h->leggi($campi);
  if (isset($ret_key['error'])){
    $_SESSION['messaggio']="Si è verificato un errore nell'accesso "
    . "all'elenco degli autori.<br>Non proseguire e inviare "
    . "questa schermata al comitato di gestione."
    . "<br>" . $ret_key['message'];
    http_response_code(403);
    require_once(ABSPATH.'aa-view/chiavi-ricerca-elenco-view.php');
    exit(0);
  }
  if ($ret_key['numero'] == 0){
    $_SESSION['messaggio']="Si è verificato un errore nell'accesso "
    . "all'elenco degli autori.<br>Non proseguire e inviare "
    . "questa schermata al comitato di gestione."
    . "<br>Non trovato";
    http_response_code(403);
    require_once(ABSPATH.'aa-view/chiavi-ricerca-elenco-view.php');
    exit(0);
  }
  $elenco_chiavi='';
  foreach ($ret_key['data'] as $chiave) {
    $elenco_chiavi .= '<tr>'. PHP_EOL
    . '<td class="text-secondary" style="text-align:right;">'
    . $chiave['record_id'] . '</td>' . PHP_EOL 
    . '<td class="h5">'.$chiave['chiave'].'</td>' . PHP_EOL
    . '<td><a href="'.$chiave['url_manuale'].'" target="_blank">'
    . $chiave['url_manuale'].'</a></td>' . PHP_EOL
    . '<td><a href="'.constant('URLBASE').'chiavi.php/modifica/'.$chiave['record_id'].'" class="btn btn-success btn-sm">Modifica</a></td>' . PHP_EOL
    . '</tr>' . PHP_EOL;
    # code...
  }
  require_once(ABSPATH.'aa-view/chiavi-ricerca-elenco-view.php');
  exit(0);
} // elenco_chiavi()



function aggiungi_chiave_ricerca(array $dati_input){
  // non ci sono dati, esposizione 
  if (!isset($dati_input['chiave'])){
    require_once(ABSPATH.'aa-view/chiavi-ricerca-aggiungi-view.php');
    exit(0);
  }
  // ci sono i dati si aggiunge 
  $dbh   = new DatabaseHandler();
  $key_h = new Chiavi($dbh);
  if (!isset($dati_input['chiave'])){
    $_SESSION['messaggio']="Si è verificato un errore nell'accesso "
    . "all'elenco delle chiavi di ricerca.<br>Non proseguire e inviare "
    . "questa schermata al comitato di gestione."
    . "<br>Mancano i dati di input";
    require_once(ABSPATH.'aa-view/chiavi-ricerca-aggiungi-view.php');
    exit(0);
  }
  $key_h->set_chiave($dati_input['chiave']);
  if (!isset($dati_input['url_manuale'])){
    $dati_input['url_manuale'] = "";
  }
  $key_h->set_chiave($dati_input['chiave']);
  $campi=[];
  $campi['chiave']=$key_h->get_chiave();
  $campi['url_manuale'] =$key_h->get_url_manuale();
  $ret_key = $key_h->aggiungi($campi);
  if (isset($ret_key['error'])){
    $_SESSION['messaggio']="Si è verificato un errore nell'accesso "
    . "all'elenco degli autori.<br>Non proseguire e inviare "
    . "questa schermata al comitato di gestione."
    . "<br>" . $ret_key['message'];
    require_once(ABSPATH.'aa-view/chiavi-ricerca-aggiungi-view.php');
    exit(0);
  }
  $_SESSION['messaggio']="Chiave aggiunta.";
  require_once(ABSPATH.'aa-view/chiavi-ricerca-aggiungi-view.php');
  exit(0);
} // aggiungi_chiave_ricerca()


/**
 * TODO domanda: modifica comporta che tutti quelli che usano la chiave 
 * devono cambiare la chiave? Certo che no, "dipende".
 */
function modifica_chiave_ricerca(int $chiave_id, array $dati_input){
  // ci sono i dati si aggiunge 
  $dbh   = new DatabaseHandler();
  $key_h = new Chiavi($dbh);
  // necessari 
  $key_h->set_record_id($chiave_id); 
  $record_id = $key_h->get_record_id();
  //
  $campi=[];
  $campi['query']='SELECT * FROM ' . Chiavi::nome_tabella
  . " WHERE record_cancellabile_dal = :record_cancellabile_dal "
  . " AND record_id = :record_id ";
  $campi['record_cancellabile_dal'] = constant('FUTURO');
  $campi['record_id']=$key_h->get_record_id();
  $ret_key = $key_h->leggi($campi);
  if (isset($ret_key['error'])){
    $_SESSION['messaggio']="Si è verificato un errore nell'accesso "
    . "all'elenco degli autori.<br>Non proseguire e inviare "
    . "questa schermata al comitato di gestione."
    . "<br>" . $ret_key['message'];
    http_response_code(403);
    require_once(ABSPATH.'aa-view/chiavi-ricerca-modifica-view.php');
    exit(0);
  }
  if ($ret_key['numero'] == 0){
    $_SESSION['messaggio']="Si è verificato un errore nell'accesso "
    . "all'elenco degli autori.<br>Non proseguire e inviare "
    . "questa schermata al comitato di gestione."
    . "<br>Non trovato";
    http_response_code(403);
    require_once(ABSPATH.'aa-view/chiavi-ricerca-modifica-view.php');
    exit(0);
  }
  $chiave=$ret_key['data'][0];
  // se mancano i dati si espone  
  if (!isset($dati_input['chiave'])){
    $_SESSION['messaggio']="Fate la vostra modifica.";
    require_once(ABSPATH.'aa-view/chiavi-ricerca-modifica-view.php');
    exit(0);
  }
  // i dati ci sono e si aggiorna 
  $key_h->set_record_id($chiave_id);
  $key_h->set_chiave($dati_input['chiave']);
  $key_h->set_url_manuale($dati_input['url_manuale']);
  $campi=[];
  $campi['update']=' UPDATE ' . Chiavi::nome_tabella
  . ' SET chiave = :chiave , '
  . ' url_manuale = :url_manuale '
  . ' WHERE record_id = :record_id ';
  $campi['chiave']=$key_h->get_chiave();
  $campi['url_manuale']=$key_h->get_url_manuale();
  $campi['record_id']=$key_h->get_record_id();
  $chiave=[];
  $chiave['record_id']=$campi['record_id'];
  $chiave['chiave']=$campi['chiave'];
  $chiave['url_manuale']=$campi['url_manuale'];
  $ret_key=[];
  $ret_key=$key_h->modifica($campi);
  if (isset($ret_key['error'])){
    $_SESSION['messaggio']="Si è verificato un errore nell'aggiornamento "
    . "dei dati.<br>Non proseguire e inviare "
    . "questa schermata al comitato di gestione."
    . "<br>" . $ret_key['message'];
    http_response_code(403);
    require_once(ABSPATH.'aa-view/chiavi-ricerca-modifica-view.php');
    exit(1);
  }
  // Tutto bene
  $_SESSION['messaggio']="Aggiornamento eseguito.";
  require_once(ABSPATH.'aa-view/chiavi-ricerca-modifica-view.php');
  exit(0);
} // modifica_chiave_ricerca()
