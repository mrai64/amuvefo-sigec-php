<?php
/**
 * nomefile: 93-archiviazione-album.php
 * funzione: 
 * Si tratta di un "controller", questo può essere richiamato direttamente da solo oppure 
 *   può essere richiamato con Ajax e opzione GET oppure POST
 * 1. In tabella scansione_disco sono presenti cartelle e file
 *    quelli da scansionare hanno il campo record_da_esaminare = (datetime) '9999-12-31 23:59:59'
 *    e record_cancellabile_dal = (datetime) '9999-12-31 23:59:59'
 * 2. Identifica se in cartella album manca un album posizionato nello stesso spazio
 *    il Titolo album corrisponde al nome della cartella di livello più basso
 * 3. inserisce un nuovo album restituendo l'id assegnato automaticamente 
 * 4. Marca in scansioni disco la cartella come già esaminata cambiando il valore del campo apposito.
 * --
 * ToDo: se non ci sono parametri in input andare in automatico a prendere la prima 
 *       cartella da elaborare e marcarla subito con update
 * ToDo: sostituire le echo di ritorno con un oggetto json contenente: 
 *       - status (numerico codici html)      (obbligatorio)
 *       - message string                     (obbligatorio anche banale, in italiano)
 *       - data    object oppure object array (facoltativo)
 * 
 * TODO /disco-intero.php/archiviazione/ ?
 */
session_start(); 

// accesso archivio 
include_once 'aa-model/database-handler.php'; // get $con 

// parametri input 
$record_id = 0;
// test per chiamata in query 
if (isset($_GET['id'])){
    echo print_r($_GET);
    $record_id = mysqli_real_escape_string($con, $_GET['id']);
    echo "<pre>input GET record_id: ".$record_id.'</pre><br/>';
}
if (isset($_POST['id'])){
    echo print_r($_POST);
    $record_id = mysqli_real_escape_string($con, $_POST['id']);
    echo "<pre>input POST record_id: ".$record_id.'</pre><br/>';
}

// se record_id=0 andare a cercare il primo record in scansioni_disco
//  che ha entrambi i campi record_da_esaminare e record_cancellabile 
//  'alla fine dei tempi'

//  Niente da fare
if (!$record_id){
    // vedi anche https://en.wikipedia.org/wiki/List_of_HTTP_status_codes
    $risposta = [
        'status' => '422',
        'message' => "Niente da fare, alla prossima."
    ];
    echo json_encode($risposta);
    exit(0);
}

/**
 * lettura tabella scansioni_disco per record_id
 * - le cartelle hanno estensione VUOTA 
 * - le cartelle hanno nomefile '/'
 */
$leggi  = "SELECT * FROM scansioni_disco "; 
$leggi .=  "WHERE record_da_esaminare = " . FUTURO . " ";
$leggi .=  "AND record_cancellabile_dal = " . FUTURO . " ";
$leggi .=  "AND estensione = '' ";
$leggi .=  "AND record_id = " . $record_id;
//          ORDER BY RECORD_ID se record_id resta zero per 
$leggi .=  " LIMIT 1";
echo "<pre>leggi: " . $leggi . '::</pre><br />';

$record_letti = mysqli_query($con, $leggi);
if (mysqli_num_rows($record_letti) < 1) { 
    $risposta = [
        'status' => '422',
        'message' => "Scansione disco id: ".$record_id." non corrisponde a una cartella."
    ];
    echo json_encode($risposta);
    exit(0);
}

// ciclo foreach ma prende solo il primo
foreach ($record_letti as $record) {
    $record_id               = $record['record_id'];
    $disco                   = $record['disco'];
    $livello1                = $record['livello1'];
    $livello2                = $record['livello2'];
    $livello3                = $record['livello3'];
    $livello4                = $record['livello4'];
    $livello5                = $record['livello5'];
    $livello6                = $record['livello6'];
    $nome_file               = $record['nome_file'];
    $estensione              = $record['estensione'];
    $modificato_il           = $record['modificato_il'];
    $codice_verifica         = $record['codice_verifica'];
    $record_creato_il        = $record['record_creato_il'];
    $record_da_esaminare     = $record['record_da_esaminare'];
    $record_cancellabile_dal = $record['record_cancellabile_dal'];     
echo '<pre>' . print_r($record). '</pre><br />';
    break;
}

// lettura tabella scansioni_disco per trovare i file contenuti nella cartella
$leggi  = "SELECT * FROM scansioni_disco "; 
$leggi .= "WHERE estensione in ('tif', 'jpg', 'mp4', 'mkv') ";
$leggi .=  "AND record_cancellabile_dal = " . FUTURO . " ";
$leggi .=  "AND disco = '$disco' ";
$leggi .=  "AND livello1 = '$livello1' ";
$leggi .=  "AND livello2 = '$livello2' ";
$leggi .=  "AND livello3 = '$livello3' ";
$leggi .=  "AND livello4 = '$livello4' ";
$leggi .=  "AND livello5 = '$livello5' ";
$leggi .=  "AND livello6 = '$livello6' ";
echo "leggi: " . $leggi . '::<br/>';

$record_letti = mysqli_query($con, $leggi);
if (mysqli_num_rows($record_letti) < 1) { 
    // per marcare la cartella come già esaminata 
    $adesso = new DateTime("NOW");
    $aggiorna = "UPDATE scansioni_disco SET record_da_esaminare = '" . $adesso->format('Y-m-d H:i:s') . "'"; 
    echo $aggiorna.'<br />';
    $esegui_aggiorna = mysqli_query($con, $aggiorna);
    $risposta = [
        'status' => '422',
        'message' => "La cartella non contiene immagini o video."
    ];
    echo json_encode($risposta);
    exit(0);
}

// la cartella contiene immagini e/o video - è già presente in tabella album?
// record_id è di scansioni_disco 
$leggi  = "SELECT * FROM album ";
$leggi .= "WHERE record_id_in_scansioni_disco = $record_id ";
echo "leggi: " . $leggi . '::<br/>';
$record_letti = mysqli_query($con, $leggi);
if (mysqli_num_rows($record_letti) > 0) {
    $risposta = [
        'status' => '422',
        'message' => "La cartella è già stata inserita in album."
    ];
    echo json_encode($risposta);
    exit(0);
}   

// inserimento album 
//  $record_id di album viene assegnato
$titolo_album = $livello1;
$titolo_album = ($livello2) ? $livello2 : $titolo_album;
$titolo_album = ($livello3) ? $livello3 : $titolo_album;
$titolo_album = ($livello4) ? $livello4 : $titolo_album;
$titolo_album = ($livello5) ? $livello5 : $titolo_album;
$titolo_album = ($livello6) ? $livello6 : $titolo_album;
//  $disco        = $disco;
$percorso_completo = './'.$livello1 . '/';
$percorso_completo .= ( ($livello2) ? ($livello2 . '/') : '');
$percorso_completo .= ( ($livello3) ? ($livello3 . '/') : '');
$percorso_completo .= ( ($livello4) ? ($livello4 . '/') : '');
$percorso_completo .= ( ($livello5) ? ($livello5 . '/') : '');
$percorso_completo .= ( ($livello6) ? ($livello6 . '/') : '');
$record_id_in_scansioni_disco = $record_id; 
//  $record_creato_il = assegnato in automatico
//  $record_cancellabile_dal = assegnato in automatico

// inserimento nuovo album 
$aggiungi  = "INSERT INTO album ";
$aggiungi .= "(titolo_album, disco, percorso_completo, record_id_in_scansioni_disco) VALUES ";
$aggiungi .= "('$titolo_album', '$disco', '$percorso_completo', $record_id_in_scansioni_disco) ";
$esegui_insert = mysqli_query($con, $aggiungi);
// se va bene è un oggetto e non è (bool)false
if (false === $esegui_insert){
    $risposta = [
        'status' => '422',
        'message' => "Inserimento cartella in album non riuscito."
    ];
    echo json_encode($risposta);
    exit(0);
}
$new_record_id = mysqli_insert_id($con);

// per marcare la cartella come già esaminata 
$adesso = new DateTime("NOW");
$aggiorna  = "UPDATE scansioni_disco "; 
$aggiorna .= "SET record_da_esaminare = '" . $adesso->format('Y-m-d H:i:s') . "' "; 
$aggiorna .= "WHERE record_id = $record_id ";
echo $aggiorna.'<br />';
$esegui_aggiorna = mysqli_query($con, $aggiorna);
$data = [
    'assigned_id' => $new_record_id,
    'titolo_album' => $titolo_album
];
$risposta = [
    'status' => '200',
    'message' => "Cartella inserita in album.",
    'data' => $data
];
echo json_encode($risposta);
exit(0);
