<?php 
/**
 *	@source /ingresso.php
 *  @author Massimo Rainato <maxrainato@libero.it>
 * 
 * Questa è la prima pagina cui si accede all'archivio, è quella di benvenuto, 
 * la seconda è di inserimento accredito, 
 * la terza la pagina museo che mostra le direzioni utilizzabili. 
 */
if (!defined('ABSPATH')){
  include_once('./_config.php');
}
include_once(ABSPATH.'aa-view/ingresso-benvenuto.htm');