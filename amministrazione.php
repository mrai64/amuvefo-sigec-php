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

include_once(ABSPATH . "aa-controller/controller-base.php"); // route_from_uri
include_once(ABSPATH . "aa-controller/controllo-abilitazione.php"); // check & set cookie

// lettura della pagina "base"
$ingresso = file_get_contents(ABSPATH."aa-view/amministrazione-view.php");
if ($ingresso === false){
	header('Content-Type: text/plain; charset=UTF-8');
	http_response_code(503);
	exit("La lettura del file non è andata a buon fine.");
}

$ingresso = str_ireplace('URLBASE.', URLBASE, $ingresso);

// Esposizione pagina trattata
echo $ingresso;
exit(0);