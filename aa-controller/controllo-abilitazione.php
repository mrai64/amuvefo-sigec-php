<?php
/**
 *	@source /aa-controller/controllo-abilitazione.php 
 *	@author Massimo Rainato <maxrainato@libero.it>
 *
 * !TODO ATTENZIONE: FA ACCESSO DIRETTO e non tramite OOP 
 * !TODO va creato un /aa-model/abilitazioni-oop.php
 * 
 * 1. Verifica se sono impostati i parametri $_COOKIE['consultatore']
 * e $_SESSION['consultatore']
 * 2. Accede alla tabella delle abilitazioni per verificare 
 * se è presente una abilitazione per la pagina e se questa 
 * è minore uguale a quella assegnata alla session
 * 2.1. Se no, passa o torna al modulo di accesso 
 * 2.2. Se sì, si prosegue
 * 
 */
if (!defined('ABSPATH')){
  include_once('../_config.php');
}
include_once(ABSPATH.'aa-model/database-handler-oop.php'); //    Class DatabaseHandler
include_once(ABSPATH.'aa-model/abilitazioni-elenco-oop.php'); // Class Abilitazioni

/**
 * Se ci sono problemi richiama una pagina impostando un messaggio 
 * se va tutto bene esce con true.
 * In input tutti campi "globali" $_SERVER $_COOKIE $_SESSION   
 * 
 * @return bool
 */
function controllo_abilitazione() : bool {
	$dbh   = New DatabaseHandler();
	$abi_h = New Abilitazioni($dbh);

	$torna_al_via = 'Location: ' . URLBASE . 'consultatori.php/accesso/?'
	. 'p=§'
	. '&return_to=' . urlencode( $_SERVER['REQUEST_URI']);

	if (session_status() !== PHP_SESSION_ACTIVE){
		@session_start();
		$abilitazione = get_set_abilitazione();
		$_SESSION['messaggio'] = "Non risulta presente un consultatore "
		. '<br>' . $dbh->esponi($_COOKIE);
		header( str_replace('§', '1', $torna_al_via) );
		exit(0);
	}
	if (!isset($_SESSION['abilitazione'])){
		$_SESSION['messaggio'] = "Non risulta presente un consultatore "
		. '<br>' . $dbh->esponi($_COOKIE);
		header( str_replace('§', '2', $torna_al_via) );
		exit(0);
	}

	// in localhost la pagina ha qualcosa in più che non è in tabella abilitazioni
	$url_pagina = $_SERVER['REQUEST_URI']; 
	$url_pagina = str_replace( URLZERO, '', $url_pagina);

	$operazione = ''; // in uso nei router 
	if (str_contains($url_pagina, '/modifica/')){
		$operazione = 'modifica';
	}
	if (str_contains($url_pagina, '/backup/')){
		$operazione = 'backup';
	}

	$abi_h->set_url_pagina($url_pagina);
	$abi_h->set_operazione($operazione);

	$campi=[];
	$campi['query'] = 'SELECT * FROM ' . Abilitazioni::nome_tabella
	. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
	. ' AND url_pagina = :url_pagina ';
	$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
	$campi['url_pagina'] = $abi_h->get_url_pagina();
	if ($operazione > ''){
		$campi['query'] .= ' AND operazione = :operazione ';
		$campi['operazione'] = $abi_h->get_operazione();
	}
	$campi['query'] .= ' ORDER BY record_id DESC '; // dovrebbe essere unico ma...

	$ret_abi = $abi_h->leggi($campi);
	if (isset($ret_abi['error'])){
		// si esce con avviso 
		$_SESSION['messaggio'] = 'Errore nella verifica abilitazione : '
		. '<br>' . $ret_abi['message'];
		header( str_replace('§', '3', $torna_al_via) );
		exit(0);		
	}
	if ($ret_abi['numero'] < 1){
		// si esce con avviso 
		$_SESSION['messaggio'] = 'Errore nella verifica abilitazione : '
		. '<br>Va inserito un record nella tabella ' . Abilitazioni::nome_tabella
		. '<br>per url: ' . $url_pagina . ', operazione: ' . $operazione;
		header( str_replace('§', '4', $torna_al_via) );
		exit(0);
	}

	// trovato - verifica abilitazione 
	$cookie_abilitazione = get_set_abilitazione();
	$abilitazione        = $ret_abi['data'][0];
	// può essere "1 lettura" ma anche "'1 lettura'"
	$abilitazione_richiesta = str_replace("'", '', $abilitazione['abilitazione']);
	// if ($_COOKIE["abilitazione"] < $abilitazione_richiesta["abilitazione"]){
	if (strncmp($cookie_abilitazione, $abilitazione_richiesta, 2) < 0){ // A < B 
		$_SESSION["messaggio"] = "Non c'è abilitazione "
		. "sufficiente per accedere alla pagina: $url_pagina. "
		. '<br>c::' . $cookie_abilitazione . ':: vs. a::' . $abilitazione_richiesta .'::' ;
		header( str_replace('§', '6', $torna_al_via) );
		exit(0);
	}

	return true; 
} // controllo_abilitazione

$passa = controllo_abilitazione();
// se arriva qui tutto ok e si continua...