<?php
/**
 * @source /aa-controller/chiavi-valore-controller.php
 * @author Massimo Rainato <maxrainato@liboer.it>
 *
 * Controller che si occupa di fornire dati presi dalla tabella chiavi-valori
 * e altre funzioni da definire
 *
 * dipendenze: classe DatabaseHandler
 * dipendenze: classe Chiavi
 */
if (!defined('ABSPATH')){
  include_once('../_config.php');
}
include_once(ABSPATH . 'aa-model/database-handler-oop.php');
include_once(ABSPATH . 'aa-model/vocabolario-oop.php');

function get_vocabolario(string $chiave ) string {
  if ($chiave == ""){
    http_response_code(404);
    exit("Album $id Non trovato. <br>" . $ret["message"] );
  }

  $chiave = htmlspecialchars(strip_tags($chiave));
  $chiave = mb_substr($chiave, 0, 250);
  $campi = [];
  $campi["chiave"] = $chiave;
  $campi["query"] = 'SELECT * FROM chiavi '
  . 'WHERE chiave = :chiave '
  . 'ORDER BY valore';

}