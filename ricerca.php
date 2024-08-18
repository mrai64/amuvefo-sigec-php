<?php
/**
 * @source /ricerca.php 
 * @author Massimo Rainato <maxrainato@libero.it>
 * 
 * Espone il modulo di ricerca analitica e basta 
 * 
 */
if (!defined('ABSPATH')){
  include_once('./_config.php');
}
include_once(ABSPATH.'aa-model/database-handler.php'); // $con non oop
require_once(ABSPATH.'aa-view/ricerca-per-chiavi.php');
exit(0);
