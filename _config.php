<?php
/**
 * @source /_config.php 
 * @author Massimo Rainato <maxrainato@libero.it>
 * 
 * Questo pezzo di codice, eseguito sempre per primo 
 * imposta delle costanti ABSPATH FUTURO usate 
 * in più punti nel resto del codice.
 *
 * versione online  
 */
//dbg echo "<pre style='maw-width:100%;'>". __FILE__ . "\n";
$durata_sessione = 6000; // seimila secondi 100 minuti
session_start();
setcookie(session_name(), session_id(), time()+$durata_sessione);
if ( !defined( 'ABSPATH' ) ) {
	if (str_contains($_SERVER['SERVER_NAME'], 'athesis77.it')){
		// percorso per accedere alla cartella / nel server senza modificare il $PATH del server
		define( 'ABSPATH', '/web/htdocs/archivio.athesis77.it/home/' ); // deve terminare con 
		define( 'URLBASE', 'https://archivio.athesis77.it/' ); //          deve terminare con 
		define( 'BASEURL', 'https://archivio.athesis77.it/' ); //          deve terminare con 

	} else {
		define( 'ABSPATH', '/Users/massimorainato/Sites/AMUVEFO-sigec-php/' ); // deve terminare con 
		define( 'URLBASE', 'http://localhost:8888/AMUVEFO-sigec-php/' ); //          deve terminare con 
		define( 'BASEURL', 'http://localhost:8888/AMUVEFO-sigec-php/' ); //          deve terminare con 

	}

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
	
	// altre impostazioni che dipendono dall'ambiente
	$env = file_get_contents(ABSPATH.'.env'); 
	$lines = explode("\n",$env);
	//dbg   echo var_dump($lines);
	foreach($lines as $line){
		preg_match("/([^#]+)\=(.*)/",$line,$matches);
		//dbg echo "\n".var_dump($matches);
		if(isset($matches[2])){
			putenv(trim($line));
		}
	} 
}
// recupero parametri per password da wordpress
include_once( ABSPATH . 'man/wp-config.php');
