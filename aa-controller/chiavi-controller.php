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
 * - elenco-chiavi
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

/**
 * Espone la pagina dell'elenco delle chiavi in archivio 
 * 
 */
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
    . "all'elenco delle chiavi.<br>Non proseguire e inviare "
    . "questa schermata al comitato di gestione."
    . "<br>" . $ret_key['message'];
    http_response_code(403);
    require_once(ABSPATH.'aa-view/chiavi-ricerca-elenco-view.php');
    exit(0);
  }
  if ($ret_key['numero'] == 0){
    $_SESSION['messaggio']="Si è verificato un errore nell'accesso "
    . "all'elenco delle chiavi.<br>Non proseguire e inviare "
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
    . '<td>' . $chiave['unico'].'</td>' . PHP_EOL
    . '<td><a href="'.constant('URLBASE').'chiavi.php/modifica/'.$chiave['record_id'].'" class="btn btn-success btn-sm">Modifica</a></td>' . PHP_EOL
    . '</tr>' . PHP_EOL;
  }
  require_once(ABSPATH.'aa-view/chiavi-ricerca-elenco-view.php');
  exit(0);
} // elenco_chiavi()


/**
 * Gestisce il modulo di aggiunta chiavi di ricerca 
 * Se mancano i dati espone il modulo 
 * Se i dati sono presenti aggiunge il record all'elenco chiavi 
 */
function aggiungi_chiave_ricerca(array $dati_input){
  
  // non ci sono dati, esposizione 
  if (!isset($dati_input['chiave'])){
    require_once(ABSPATH.'aa-view/chiavi-ricerca-aggiungi-view.php');
    exit(0);
  }

  // ci sono i dati si aggiunge 
  if (!isset($dati_input['chiave'])){
    $_SESSION['messaggio']="Si è verificato un errore nell'accesso "
    . "all'elenco delle chiavi di ricerca.<br>Non proseguire e inviare "
    . "questa schermata al comitato di gestione."
    . "<br>Mancano i dati di input";
    require_once(ABSPATH.'aa-view/chiavi-ricerca-aggiungi-view.php');
    exit(0);
  }
  // facoltativo - default 
  if (!isset($dati_input['url_manuale'])){
    $dati_input['url_manuale'] = "";
  }
  $dbh   = new DatabaseHandler();
  $key_h = new Chiavi($dbh);
  $key_h->set_chiave($dati_input['chiave']);
  $key_h->set_url_manuale($dati_input['url_manuale']);
  $key_h->set_unico($dati_input['unico']);
  $ret_key = $key_h->aggiungi($dati_input);
  if (isset($ret_key['error'])){
    $_SESSION['messaggio']="Si è verificato un errore nell'accesso "
    . "all'elenco delle chiavi.<br>Non proseguire e inviare "
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
 * TODO devono cambiare la chiave? Certo che no, "dipende".
 */
function modifica_chiave_ricerca(int $chiave_id, array $dati_input){
  // ci sono i dati si aggiunge 
  $dbh   = new DatabaseHandler();
  $key_h = new Chiavi($dbh);

  // validazione e ricerca per id
  $key_h->set_record_id($chiave_id); 
  $ret_key = $key_h->get_chiave_record_per_id($chiave_id);
  if (isset($ret_key['error'])){
    $_SESSION['messaggio']="Si è verificato un errore nell'accesso "
    . "all'elenco delle chiavi di ricerca.<br>Non proseguire e inviare "
    . "questa schermata al comitato di gestione."
    . "<br>" . $ret_key['message'];
    http_response_code(403);
    require_once(ABSPATH.'aa-view/chiavi-ricerca-modifica-view.php');
    exit(0);
  }
  $chiave=$ret_key['record'];
  // se mancano i dati si espone  
  if (!isset($dati_input['chiave'])){
    $_SESSION['messaggio']="Fate la vostra modifica.";
    require_once(ABSPATH.'aa-view/chiavi-ricerca-modifica-view.php');
    exit(0);
  }

  // validazione 
  $key_h->set_record_id($chiave_id);
  $key_h->set_chiave($dati_input['chiave']);
  $key_h->set_url_manuale($dati_input['url_manuale']);
  $key_h->set_unico($dati_input['unico']);
  $dati_input['update']=' UPDATE ' . Chiavi::nome_tabella
  . ' SET chiave = :chiave , '
  . ' url_manuale = :url_manuale, '
  . ' unico = :unico '
  . ' WHERE record_id = :record_id ';
  $ret_key=[];
  $ret_key=$key_h->modifica($dati_input);
  if (isset($ret_key['error'])){
    $_SESSION['messaggio']="Si è verificato un errore nell'aggiornamento "
    . "dei dati.<br>Non proseguire e inviare "
    . "questa schermata al comitato di gestione."
    . "<br>" . $ret_key['message'];
    require_once(ABSPATH.'aa-view/chiavi-ricerca-modifica-view.php');
    exit(1);
  }
  // Tutto bene
  $_SESSION['messaggio']="Aggiornamento eseguito.";
  require_once(ABSPATH.'aa-view/chiavi-ricerca-modifica-view.php');
  exit(0);
} // modifica_chiave_ricerca()
