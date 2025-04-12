<?php
/**
 * @source /_config.php 
 * @author Massimo Rainato <maxrainato@libero.it>
 * 
 * Questo pezzo di codice, eseguito sempre per primo 
 * imposta delle costanti (ABSPATH, FUTURO, altre) usate 
 * in più punti nel resto del codice.
 * Inoltre mantiene o imposta i cookie per identificare consultatori 
 * e amministratori del sistema.
 *
 * versione online 
 */

// PHPSESSONID
$durata_sessione = 7200; // settemiladuecento secondi 120 minuti 2 ore - rinnovabili 
@session_start();
@setcookie(session_name(), session_id(), time() - $durata_sessione, "/"); // cancella vecchia
@setcookie(session_name(), session_id(), time() + $durata_sessione, "/"); // scrive nuova

if ( !defined( 'ABSPATH' ) ) {
	$debug_buffer .= '<br>ABSPATH undefined';
	
	// set ABSPATH
	switch (strtolower($_SERVER['SERVER_NAME'])) {
		case 'www.fotomuseoathesis.it':
			define( 'ABSPATH', '/web/htdocs/www.fotomuseoathesis.it/home/' );  
			define( 'URLBASE', 'https://www.fotomuseoathesis.it/' );  
			define( 'URLZERO', '');  // nessuna sottocartella 
			break;
			
		case 'archivio.athesis77.it':
			define( 'ABSPATH', '/web/htdocs/archivio.athesis77.it/home/' );  
			define( 'URLBASE', 'https://archivio.athesis77.it/' );  
			define( 'URLZERO', ''); // nessuna sottocartella
			break;
				
		default:
			// localhost
			define( 'ABSPATH', '/Users/massimorainato/Sites/AMUVEFO-sigec-php/' ); 
			define( 'URLBASE', 'http://localhost:8888/AMUVEFO-sigec-php/' ); 
			define( 'BASEURL', 'http://localhost:8888/AMUVEFO-sigec-php/' ); 
			define( 'URLZERO', '/AMUVEFO-sigec-php'); // la sottocartella che contiene il sito 
			break;

	} // switch set ABSPATH
	// debug_buffer serve per memorizzare senza fare echo perché più avanti
	// viene testato se header_sent
	$debug_buffer .= '<br>ABSPATH: '.ABSPATH;
	$debug_buffer .= '<br>URLBASE: '.URLBASE;
	$debug_buffer .= '<br>URLZERO: '.URLZERO;
	
	// definizione FUTURO / record valido, quello che 
	// ha nel campo record_cancellabile_dal questo valore futuro 
	define( 'FUTURO', "9999-12-31 23:59:59" ); // 
	
	// definizione di abilitazione lettura che non consente 
	// la richiesta originali e la modifica dei dati 
	// da confrontare con $_COOKIE['abilitazione']
	define( 'SOLALETTURA',  '1 lettura');
	define( 'MODIFICA',     '3 modifica');
	define( 'MODIFICAPLUS', '5 modifica originali');
	define( 'AMMINISTRA',   '7 amministrazione');
	$debug_buffer .= '<br>SOLALETTURA: '.SOLALETTURA;
	$debug_buffer .= '<br>COOKIE: '.str_replace(';', '; <br>', serialize($_COOKIE));
	
	// cookie 
	$scadenza = time()+3*86400; // 3*24*60*60; i secondi di 3 giorni 
	$expires  = date("D, d M Y H:i:s",$scadenza).' GMT'; // per formato setcookie sul fuso orario 

	$dominio  = str_replace('https://', '', URLBASE);
	$dominio  = str_replace('http://', '', $dominio);
	$dominio  = substr($dominio, 0, strpos($dominio, '/', 0));
	
	if (!isset($_COOKIE['abilitazione'])){
		$cookie_abilitazione    = SOLALETTURA;
		$cookie_email           = 'info@athesis77.it';
		$cookie_consultatore    = 'Anonimo, Consultatore';
		$cookie_consultatore_id = '10';
	} else {
		//refresh 
		$cookie_abilitazione    = $_COOKIE['abilitazione'];
		$cookie_email           = $_COOKIE['accesso_email'];
		$cookie_consultatore    = $_COOKIE['consultatore'];
		$cookie_consultatore_id = $_COOKIE['consultatore_id'];
		setcookie("abilitazione",    "", time() - 3600, "/", $dominio);
		setcookie("accesso_email",   "", time() - 3600, "/", $dominio);
		setcookie("consultatore",    "", time() - 3600, "/", $dominio);
		setcookie("consultatore_id", "", time() - 3600, "/", $dominio);
	}// not isset($_COOKIE['abilitazione']
	
	setcookie("abilitazione",    $cookie_abilitazione,    $scadenza, "/", $dominio);
	setcookie("accesso_email",   $cookie_email,           $scadenza, "/", $dominio);
	setcookie("consultatore",    $cookie_consultatore,    $scadenza, "/", $dominio);
	setcookie("consultatore_id", $cookie_consultatore_id, $scadenza, "/", $dominio);
	
/*
	if (!headers_sent()){
		// header("Set-Cookie: abilitazione=".urlencode($cookie_abilitazione)."; expires='$expires'; Path=/; SameSite=None; ", false);
		// header("Set-Cookie: accesso_email='$cookie_email'; expires='$expires'; Path=/; SameSite=None; ", false);
		// header("Set-Cookie: consultatore=$cookie_consultatore; expires='$expires'; Path=/; SameSite=None; ", false);
		// header("Set-Cookie: consultatore_id=$cookie_consultatore_id; expires='$expires'; Path=/; SameSite=None; ", false);
		header("Set-Cookie: abilitazione="    . str_ireplace('%2B', '%20', rawurlencode($cookie_abilitazione)   ) ."; expires=$expires; Max-Age=120; path=/; domain=".$dominio."; ", false);
		header("Set-Cookie: accesso_email="   . str_ireplace('%2B', '%20', rawurlencode($cookie_email)          ) ."; expires=$expires; Max-Age=120; path=/; domain=".$dominio."; ", false);
		header("Set-Cookie: consultatore="    . str_ireplace('%2B', '%20', rawurlencode($cookie_consultatore)   ) ."; expires=$expires; Max-Age=120; path=/; domain=".$dominio."; ", false);
		header("Set-Cookie: consultatore_id=" . str_ireplace('%2B', '%20', rawurlencode($cookie_consultatore_id)) ."; expires=$expires; Max-Age=120; path=/; domain=".$dominio."; ", false);
	}
*/
	unset($cookie_abilitazione);
	unset($cookie_email);
	unset($cookie_consultatore);
	unset($cookie_consultatore_id);
	// cookie 
	
	// altre impostazioni che dipendono dall'ambiente
	// TODO parecchie volte i valori qui dentro NON SOVRASCRIVONO 
	// TODO i valori preesistenti UAAAAIII? PE'CCHEEEE?
	$env = file_get_contents(ABSPATH.'.env'); 
	$lines = explode("\n",$env);
	foreach($lines as $line){
		preg_match("/([^#]+)\=(.*)/u",$line,$matches);
		if(isset($matches[2])){
			putenv(trim($line));
		}
	} 
	unset($env, $lines);

} // costante ABSPATH non definita 
$debug_buffer .= '<br>COOKIE[2]: '.str_replace(';', '; <br>', serialize($_COOKIE));

// recupero parametri per password da wordpress
include_once( ABSPATH . 'man/wp-config.php');

//dbg echo '<p style="font-family:monospace">';
//dbg echo $debug_buffer;
//dbg echo '</p>';
//dbg echo '<hr />ENV: '.phpinfo(INFO_ENVIRONMENT);
//dbg exit(1); 
