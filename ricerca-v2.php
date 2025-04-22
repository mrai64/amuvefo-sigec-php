<?php
/**
 * @source /ricerca-v2.php 
 * @author Massimo Rainato <maxrainato@libero.it>
 * 
 * Espone il modulo di ricerca semplice e basta 
 * 
 */
if (!defined('ABSPATH')){
  include_once('./_config.php');
}
include_once(ABSPATH.'aa-model/database-handler.php'); // $con non oop

require_once(ABSPATH.'aa-view/ricerca-v2-chiedi-view.php');
exit(0);
