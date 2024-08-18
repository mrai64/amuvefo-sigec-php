<?php
/**
 *	@source /96-chiavi-ricerca-option-list.php
 *  @author Massimo Rainato <maxrainato@libero.it>
 * 
 * Questo spezzone serve a ottenere un elenco 
 * di chiavi ricerca per aggiornar eun modulo online
 * 
 *	@return string html option list compiled w/sql data
 */
if (!defined('ABSPATH')){
  include_once("./_config.php");
}
require(ABSPATH.'aa-model/database-handler.php');

$risposta_html = '';
$leggi = "SELECT chiave FROM chiavi_elenco " 
. "ORDER BY chiave, record_id "; 
$record_letti = mysqli_query($con, $leggi);
if (mysqli_num_rows($record_letti) < 1) { 
  $risposta_html .= "<option value='nessuno'>nessuno</option>".PHP_EOL;
} else {
  foreach ($record_letti as $chiave){
  $risposta_html .= '<option value="'.$chiave['chiave'].'">'.$chiave['chiave'].'</option>'.PHP_EOL;
}
echo $risposta_html;
exit(0);
