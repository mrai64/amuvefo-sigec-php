<?php
/**
 * nomefile:	aa-registro.php
 * funzione:	crea una registrazione delle attività a uso di 
 *          	analisi post-evento
 *
 * 2024-05-07	massimo	prima versione mutuata da precedenti progetti
 *           	       	lo scopo è creare un registro alla prima chiamata 
 *           	       	che in seguito è aggiornabile con la funzione scrivi_registro
 *
 * DaFare
 * cambiare in una classe registro, 
 * attributo nomefile 
 * con funzione pubblica registro->scrivi
 *   e funzione pubblica registro->mostra (ora assente)
 */
if (empty($_SESSION)) {
	session_start();
}

/**
 * parametri_get[]
 * parametri_post[]
 * parametri_server[]
 * parametri_cookie[]
 */

if (!isset($parametri_get)) {
	function trim_str($val){ 
		return trim($val);
	} //trim_str

	$parametri_get    = array_filter($_GET,    'trim_str');
	$parametri_post   = array_filter($_POST,   'trim_str');
	$parametri_server = array_filter($_SERVER, 'trim_str');
	$parametri_cookie = array_filter($_COOKIE, 'trim_str');
//	if (empty($parametri_cookie["PHPSESSID"])) {
//		$parametri_cookie["PHPSESSID"] = md5( serialize($parametri_server) );
//	}
}
// base da inizio dov'è definito ABSPATH ???
$cartella_registro_base = ABSPATH . 'aa-log/';
if (false === is_dir($cartella_registro_base)) {
	if (!mkdir($cartella_registro_base, 0755, false)) {
	// non è andato bene
		throw new Exception("Non è stato possibile creare la cartella di registro delle operazioni e questa non risulta presente, crearla manualmente con possibilità di scrittura.");
	}
}
// usata 2 volte si candida ad essere una function
$cartella_registro = $cartella_registro_base . date("Y-m") . '/';
if (false === is_dir($cartella_registro)) {
	if (!mkdir($cartella_registro, 0755, false)) {
	// non è andato bene
		throw new Exception("Non è stato possibile creare la cartella di registro delle operazioni e questa non risulta presente, crearla manualmente con possibilità di scrittura.");
	}
}

[$microsecondi, $secondi] = explode(" ", microtime());
$file_registro = $cartella_registro . date("d-H-i-s") . '-' . substr($microsecondi,2) . '.txt';
try{
	$fr_aperto = fopen($file_registro, "a", false);
} catch (Exception $e){
	throw new Exception("Aprendo {$file_registro} si è verificato un problema, questo: " . $e->error() . PHP_EOL );
}

fwrite($fr_aperto, 'nome file: ' . $file_registro . PHP_EOL);
fwrite($fr_aperto, 'data e ora: ' . date(DATE_ATOM) . ' (UTC)' . PHP_EOL);
fwrite($fr_aperto, '- - ' . PHP_EOL);
// Elenco parametri GET
fwrite($fr_aperto, '_GET: ' . PHP_EOL );
foreach( $parametri_get as $vk => $v){
	fwrite($fr_aperto, '_GET: ' . $vk .': ' . $v . PHP_EOL );
}
// Elenco parametri POST 
fwrite($fr_aperto, '_POST: ' . PHP_EOL );
foreach( $parametri_post    as $vk => $v){
	if ("string" === gettype($v)) {
		fwrite($fr_aperto, '_POST: ' . $vk .': ' . $v . PHP_EOL );
	} elseif ("array" === gettype($v)){
		fwrite($fr_aperto, '_POST: ' . $vk .': ' . serialize($v) . PHP_EOL );
	} else {
		fwrite($fr_aperto, '_POST: ' . $vk .': ' . var_export($v) . PHP_EOL );
	}
}
//	Elenco parametri SERVER 
fwrite($fr_aperto, '_SERVER: ' . PHP_EOL );
foreach( $parametri_server  as $vk => $v){
	fwrite($fr_aperto, '_SERVER: ' . $vk .': ' . $v . PHP_EOL );
}
//	Elenco parametri COOKIE
fwrite($fr_aperto, '_COOKIE: ' . PHP_EOL );
foreach( $parametri_cookie  as $vk => $v){
	fwrite($fr_aperto, '_COOKIE: ' . $vk .': ' . $v . PHP_EOL );
}
//	Elenco parametri SESSION 
if (count($_SESSION) > 0) {
	fwrite($fr_aperto, '_SESSION: ' . PHP_EOL );
	foreach( $_SESSION  as $vk => $v){
		fwrite($fr_aperto, '_SESSION: ' . $vk .': ' . $v . PHP_EOL );
	}
}
fwrite($fr_aperto, '- - - -'. PHP_EOL );
fclose($fr_aperto);
/** 
 * scrivi_registro 
 */
function scrivi_registro( $messaggio = ''){
	global $file_registro; // nome 
	
	try{
		$fr_aperto = fopen($file_registro, "a", false);
	} catch (Exception $e){
		throw new Exception("Aprendo {$file_registro} si è verificato un problema, questo: " . $e->error() . PHP_EOL );
	}
	
	fwrite($fr_aperto, date(DATE_ATOM) . ' '. substr($messaggio, 0, 1000) . PHP_EOL);
	fclose($fr_aperto);
	return true;
}

