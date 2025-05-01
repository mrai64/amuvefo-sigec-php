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
 * versione online per fotomuseoathesis.it
 */

// PHPSESSONID
$durata_sessione = 1800; // i secondi di 30 minuti 30 * 60  
if (session_status() !== PHP_SESSION_ACTIVE){
  @session_start(); // @ suppress warning message 
}
@setcookie(session_name(), session_id(), time() - $durata_sessione, "/"); // cancella vecchia
@setcookie(session_name(), session_id(), time() + $durata_sessione, "/"); // scrive nuova
/**
 * debug_buffer per quelo che non si può mostrare prima di header_sent()
 */
$debug_buffer='<p style="font-family:monospace;">Debug buffer</p>';
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
      define( 'URLZERO', '/AMUVEFO-sigec-php'); // la sottocartella che contiene il sito 
      break;

  } // switch set ABSPATH

/**
 * Questo valore FUTURO nel campo record_cancellabile_dal lo rende "attivo",
 * quando invece è impostato a un timestamp del passato è soft-deleted
 * e mantenuto in archivio in attesa di prima backup e poi rimozione fisica
 */  
  define( 'FUTURO', "9999-12-31 23:59:59" ); // 
  
  /**
   * Livelli di abilitazione previsti da inserire nel 
   * $_COOKIE['abilitazione']
   */
  define( 'SOLALETTURA',  '1 lettura');
  define( 'MODIFICA',     '3 modifica');
  define( 'MODIFICAPLUS', '5 modifica originali');
  define( 'AMMINISTRA',   '7 amministrazione');
  
  // cookie 
  $scadenza = time()+3*86400; // 3*24*60*60; i secondi di 3 giorni 
  $expires  = date("D, d M Y H:i:s",$scadenza).' GMT'; // per formato setcookie sul fuso orario 

  $dominio  = str_replace('https://', '', URLBASE);
  $dominio  = str_replace('http://', '', $dominio);
  $dominio  = substr($dominio, 0, strpos($dominio, '/', 0));
  
  $cookie_abilitazione = (isset($_COOKIE['abilitazione'])) ? $_COOKIE['abilitazione'] : SOLALETTURA;
  $cookie_email = (isset($_COOKIE['accesso_email'])) ? $_COOKIE['accesso_email'] : 'info@athesis77.it';
  $cookie_consultatore = (isset($_COOKIE['consultatore'])) ? $_COOKIE['consultatore'] : 'Anonimo, Consultatore';
  $cookie_consultatore_id = (isset($_COOKIE['consultatore_id'])) ? $_COOKIE['consultatore_id'] : '10';
  //refresh 
  @setcookie("abilitazione",    "", time() - 3600, "/", $dominio);
  @setcookie("accesso_email",   "", time() - 3600, "/", $dominio);
  @setcookie("consultatore",    "", time() - 3600, "/", $dominio);
  @setcookie("consultatore_id", "", time() - 3600, "/", $dominio);
  
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

/**
 * sistema di codifica delle password mutuato da wordpress
 * installato per ospitare il manuale utente del sistema
 */
include_once( ABSPATH . 'man/wp-config.php');

//dbg echo '<p style="font-family:monospace">';
//dbg echo $debug_buffer;
//dbg echo '</p>';
//dbg echo '<hr />ENV: '.phpinfo(INFO_ENVIRONMENT);
//dbg exit(1); 
