<?php 
function routeFromUri( $uri = "" ){
    return route_from_uri($uri); 
}

function route_from_uri( string $uri = "" ) : array {
    $operazioni = [];
    $parametri  = [];
    if ($uri == ""){
        $uri = $_SERVER["REQUEST_URI"];
    }
    $uri = str_replace( URLBASE, '/', $uri);
    $separati   = explode('?', $uri );
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
