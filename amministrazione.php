<?php 
/**
 *	@source /amministrazione.php
 *	@author Massimo Rainato <maxrainato@libero.it>
 *
 *	Pagina di ingresso per funzioni diverse dalla consultazione, 
 *	richiede una abilitazione superiore alla sola lettura e 
 *	i link cui si ha accesso sono convertiti come per la pagina di 
 *	ingresso in link veri, altrimenti si resta qui. 
 *
 *	TODO: da completare, vanno riportati i link per accedere all'aruba drive 
 */
if (!defined('ABSPATH')){
  include_once("./_config.php");
}

include_once(ABSPATH . "aa-controller/controller-base.php"); // routeFromUri
include_once(ABSPATH . "aa-controller/controllo-abilitazione.php"); // check & set cookie

require_once(ABSPATH.'aa-view/amministrazione-view.php');
exit(0);