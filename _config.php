<?php
/**
 * @source /_config.php 
 * @author Massimo Rainato <maxrainato@libero.it>
 * 
 * Questo pezzo di codice, eseguito sempre per primo 
 * imposta delle costanti ABSPATH RECORD_VALIDO usate 
 * in più punti nel resto del codice.
 * 
 * Versione per localhost 
 * 
 */
//dbg echo "<pre style='maw-width:100%;'>". __FILE__ . "\n";
$durata_sessione = 6000; // seimila secondi 100 minuti
session_start();
setcookie(session_name(), session_id(), time()+$durata_sessione);
if ( !defined( 'ABSPATH' ) ) {

  // percorso per accedere alla cartella / nel server senza modificare il $PATH del server
  define( 'ABSPATH', '/Users/massimorainato/Sites/AMUVEFO-sigec-php/' ); // deve terminare con 
  // define( 'ABSPATH', '/web/htdocs/archivio.athesis77.it/home/' ); // deve terminare con 

	define( 'URLBASE', 'http://localhost:8888/amuvefo-sigec-php/' ); //          deve terminare con 
	define( 'BASEURL', 'http://localhost:8888/AMUVEFO-sigec-php/' ); //          deve terminare con 
  // define( 'URLBASE', 'https://archivio.athesis77.it/' ); //          deve terminare con 
	// define( 'BASEURL', 'https://archivio.athesis77.it/' ); //          deve terminare con 

  // definizione record_vivo / record valido quello che 
  // ha nel campo record_cancellabile_dal questo valore futuro 
  define( 'RECORD_VIVO',   "9999-12-31 23:59:59" ); // da sostituire dove si trova con RECORD_VALIDO
  define( 'RECORD_VALIDO', "9999-12-31 23:59:59" ); // 
  
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
  //dbg echo __FILE__.' getenv '.var_dump(getenv())."\n";
  //dbg echo __FILE__.' '.var_dump($_ENV)."\n";
}
// definizioni per password da wordpress
include_once( ABSPATH . 'man/wp-config.php');
