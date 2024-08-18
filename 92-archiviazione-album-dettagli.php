<?php 
/**
 *	nomefile: 92-archiviazione-album-dettagli.php 
 *
 * funzione:
 * Si tratta di un "controller" o eventualmente "worker", che deve aggiungere per un 
 *   nuovo album nella tabella album_dettagli i dettagli (chiave-valore) 
 *   che si possono ricavare automaticamente dalle informazioni della cartella 
 *   sia in se sia da scansioni_disco
 * 1. dati in input con $_POST per chiamata diretta o per chiamata jQuery ajax
 *    dati in input con $_GET  per debug 
 * ToDo: Questo "funziona ma va rifatto", l'idea è invece creare 
 *        un modulo indipendente per ciascuna chiave di ricerca,
 *        e avere per ogni estensione un elenco di chiavi caricabili automaticamente 
 * ToDo: sostituire gli accessi diretti con uno strato isolante a oggetti
 *       la classe Album contiene le tabelle padre e figlio album e album_dettagli
 *       i dati in input sono _POST
 *       i dati in output sono JSON
 *       alle funzioni e dalle funzioni array php
 *       in ritorno sempre uno 'status' string e un 'message' string, 
 *       se ok eventuali data array[object] 
 * ToDo: insert in chiave valore è replicato N volte, creare funzione con 
 *       parametri: tabella_figlio, record_id_padre, chiave, valore 
 *       oppure con un array associativo che li contiene
 */

/** 
 * ToDo: va definito il livello di abilitazione necessario, va verificato
 * che sia presente un livello abilitazione del consultatore. Va richiamato
 * un modulo di login se il livello di abilitazione del consultatore non sia
 * sufficiente. I moduli inseriti nella lista dei lanci aurtomatici che abilitazione 
 * hanno, e come superano il login? 
 */ 
session_start(); // recupera o costituisce i dati _SESSION

// accesso archivio 
include_once 'aa-model/database-handler.php'; // get $con 

$album_record_id = 0; // bigint
$album_dettagli_record_id = 0; // bigint
$tabella_padre = 'album';
$tabella_figlio = 'album_dettagli'; 

// pulizia input _GET / _POST 
if (isset($_GET['id'])) {
    $album_record_id = mysqli_real_escape_string($con, $_GET['id']);
}
if (isset($_POST['id'])) {
    $album_record_id = mysqli_real_escape_string($con, $_POST['id']);
}
echo '<pre>album_record_id: ' . ( $album_record_id ).  ':</pre><br />';

//  Niente da fare
if (!$album_record_id){
    // vedi anche https://it.wikipedia.org/wiki/Codici_di_stato_HTTP
    $risposta = [
        'status' => '422',
        'message' => "Niente da fare, alla prossima."
    ];
    echo json_encode($risposta);
    exit(0);
}

// $dati_album = $album->leggiId($album_record_id);
$leggi  = "SELECT * FROM ".$tabella_padre;
$leggi .= " WHERE record_id = $album_record_id";
$leggi .= "   AND record_cancellabile_dal = " . RECORD_VIVO ;
echo '<pre>' . ($leggi).  '</pre>';

$record_letti = mysqli_query($con, $leggi);
if (mysqli_num_rows($record_letti) < 1) { 
    $risposta = [
        'status' => '422',
        'message' => "L'album non è presente o utilizzabile."
    ];
    echo json_encode($risposta);
    exit(0);
}

// dati album dal primo record 
// D.: si può usare $album = $record_letti[0]; ? R.: NO
$album = [];
foreach ($record_letti as $record){
    $album = $record;
    break;
}
echo '<pre>album: ' . var_export($album).  '</pre><br />';

if (!isset($album['record_id'])){
    $risposta = [
        'status' => '422',
        'message' => "L'album non contiene dati utili."
    ];
    echo json_encode($risposta);
    exit(0);
}

// dati album_dettagli 
$record_id_padre = $album['record_id']; // proforma
$leggi  = "SELECT * FROM " . $tabella_figlio;
$leggi .= " WHERE record_cancellabile_dal = " . RECORD_VIVO;
$leggi .= "   AND record_id_padre = " . $record_id_padre;
echo '<pre>leggi: ' . ($leggi).  '</pre><br />';

$record_letti = mysqli_query($con, $leggi);
if (mysqli_num_rows($record_letti) > 0) { 
    $risposta = [
        'status' => '422',
        'message' => "Sono già presenti dettagli album, modificare a video."
    ];
    echo json_encode($risposta);
    exit(0);
}

// e qui inizia il business.... dettagli ... quali dettagli?
$titolo_album = $album['titolo_album'];
echo '<pre>titolo_album: '. ($titolo_album) .'</pre><br />';

/**
 * chiave: date/evento 
 * formato: "aaaa mm gg" oppure "aaaa-mm-gg"
 */
$chiave  = 'data/evento'; // check in tabella chiavi_elenco
$pattern = '/\d{4}(\.|\-|\s)\d{2}(\.|\-|\s)\d{2}/';
if (preg_match($pattern, $titolo_album, $valori)){
echo '<pre>'.serialize($valori).'</pre><br />';
    $valore = $valori[0]; 
    // formattazione data in ISO: aaaa mm gg > 0123-56-89
    $valore = substr($valore, 0, 4) . '-' . substr($valore,5,2) . '-' . substr($valore,8,2);
    // $album-dettagli->create(...)
    $insert  = "INSERT IGNORE INTO " . $tabella_figlio; 
    $insert .= " (  record_id_padre,   chiave,    valore  ) VALUES ";
    $insert .= " ( $record_id_padre, '$chiave', '$valore' ) ";
    echo '<pre>insert: ' . ($insert).  '</pre><br />';
    $esegui_insert = mysqli_query($con, $insert);
    // se va bene non è false
    if (false === $esegui_insert){
        $risposta = [
            'status' => '422',
            'message' => "Avrebbe inserito il dettaglio $chiave con valore: $valore ma non è andata bene."
        ];
        echo json_encode($risposta);
        exit(0);
    }
    // eventualmente strip della data $valore è stata 
    $titolo_album = str_replace($valori[0], '', $titolo_album);
    echo "<pre>titolo senza $valori[0]: $titolo_album</pre><br />";
} else {
    echo '<pre>manca data aaaa mm gg</pre><br />';
}

/**
 * chiave: data/evento 
 * formato: valore compreso nel vocabolario
 *          inserire manualmente?
 */

/**
 * chiave: luogo/comune
 * formato: testo libero presente in elenco
 * ToDo: Questo elenco non cambia spesso, prima o poi andrà sostituito
 *       da un campo statico  
 */
$chiave = 'luogo/comune'; // check in tabella chiavi_elenco
//  carico elenco_luoghi 
$elenco_luoghi = [];
$leggi  = "SELECT valore FROM chiavi_valori_vocabolario";
$leggi .= " WHERE chiave = '$chiave'";
$leggi .= "   AND record_cancellabile_dal = " . RECORD_VIVO;
$leggi .= " ORDER BY valore DESC, record_id ";
echo '<pre>' .print_r($leggi).  '</pre><br />';

$record_letti = mysqli_query($con, $leggi);
if (mysqli_num_rows($record_letti) < 1) { 
    $risposta = [
        'status' => '422',
        'message' => "L'elenco dei luogo/comune in vocabolario è stato rimosso."
    ];
    echo json_encode($risposta);
    exit(0);
}

$luogo_trovato = "";
foreach ($record_letti as $luogo) {
    echo "<pre>check luogo: " . $luogo['valore'] . "</pre>";
    if (stripos($luogo['valore'], $titolo_album)){
        $luogo_trovato = $luogo['valore'];
        break;
    }
}
if ($luogo_trovato){
    $valore = $luogo_trovato; 
    // $album-dettagli->create(...)
    $insert  = "INSERT IGNORE INTO " . $tabella_figlio; 
    $insert .= " (  record_id_padre,   chiave,    valore  ) VALUES ";
    $insert .= " ( $record_id_padre, '$chiave', '$valore' ) ";
    echo '<pre>insert: ' . ($insert).  '</pre><br />';
    $esegui_insert = mysqli_query($con, $insert);
    // se va bene non è false
    if (false === $esegui_insert){
        $risposta = [
            'status' => '422',
            'message' => "Avrebbe inserito il dettaglio $chiave con valore: $valore ma non è andata bene."
        ];
        echo json_encode($risposta);
        exit(0);
    }
    // strip del luogo dal titolo_album 
    $titolo_album = str_ireplace($valori[0], '', $titolo_album);
} else {
    echo '<pre>Luogo non rintracciato in '.$titolo_album.'<pre>';
}

/** 
 * chiave: codice/autore/athesis
 */


/**
 * chiave: nome/manifestazione-soggetto
 * di fatto "quello che avanza" di Titolo-album 
 */
$chiave = 'nome/manifestazione-soggetto';
$valore = $titolo_album;
$insert  = "INSERT IGNORE INTO " . $tabella_figlio; 
$insert .= " (  record_id_padre,   chiave,    valore  ) VALUES ";
$insert .= " ( $record_id_padre, '$chiave', '$valore' ) ";
echo '<pre>insert: ' . ($insert).  '</pre><br />';
$esegui_insert = mysqli_query($con, $insert);
// se va bene non è false
if (false === $esegui_insert){
    $risposta = [
        'status' => '422',
        'message' => "Avrebbe inserito il dettaglio $chiave con valore: $valore ma non è andata bene."
    ];
    echo json_encode($risposta);
    exit(0);
}

// Si può segnalare cosa è stato inserito ma serve solo in fase di debug-sviluppo
$risposta = [
    'status' => '200',
    'message' => "Attività conclusa senza errori."
];
echo json_encode($risposta);
exit(0);
