<?php 
/**
 * @param string uri 
 * Si tratta della parte di indirizzo url che viene separata dal nome 
 * del sito, quindi può essere 
 * /album.php/leggi/15 
 * per https://archivio.athesis77.it/album.php/leggi/15
 * e può essere 
 * /AMUVEFO-sigec.php/album.php/leggi/15 
 * per http::/localhost:8888/AMUVEFO-sigec-php/album.php/leggi/15
 * 
 * @param string router
 * Si tratta del file che sta chiamando la funzione 
 * e può essere '/album.php/'
 * 
 * @return array $ret ['operazioni'] e ['parametri']
 * nella sezione operazioni va tutto quello che precede ? 
 * nella sezione parametri tutti i parametri chiave=valore
 */

function route_from_uri( string $uri = '' , string $router = '') : array {
	$operazioni = [];
	$parametri  = [];
	if ($uri == ''){
		$uri = $_SERVER["REQUEST_URI"];
	}
	if ($router==''){
		$router='/'.$_SERVER["PHP_SELF"].'/';
	}
	$pos = strpos($uri, $router) || 0;
	$separati   = explode('?', substr($uri, $pos) );
	$operazioni = array_slice(explode('/', $separati[0]), 2);

	if (isset($separati[1])){
		$kequalv = explode('&', $separati[1]);
		foreach($kequalv as $kv){
			list($k, $v) = explode('=', $kv);
			$parametri["$k"] = "$v";
		}
	}
	$ret = [
		"operazioni" => $operazioni,
		"parametri"  => $parametri
	];
	return $ret; 
}
