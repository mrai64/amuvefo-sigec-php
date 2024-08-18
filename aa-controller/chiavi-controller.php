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
 */
if (!defined('ABSPATH')){
  include_once('../_config.php');
}
include_once(ABSPATH . 'aa-model/database-handler-oop.php');
include_once(ABSPATH . 'aa-model/chiavi-oop.php');

/**
 * omonimo del metodo 
 */
function get_chiavi_datalist() : string {
  $dbh   = New DatabaseHandler();
  $chi_h = New Chiavi($dbh);
  
  return $chi_h->get_chiavi_datalist();
}