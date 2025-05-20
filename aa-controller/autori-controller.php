<?php 
/**
 * AUTORI controller 
 * 
 * - elenco_autori() 
 *   Compila ed espone la pagina dell'intera lista autori
 *   (no datalist)
 * 
 * - modifica_autore 
 *   Espone un modulo o gestisce i dati del modulo per
 *   modificare un autore già presente in autori_elenco 
 * 
 * - aggiungi_autore
 *   Espone un modulo o gestisce i dati del modulo per aggiungere
 *   un autore in autori_elenco 
 * 
 * - verifica_sigla_6
 *   Verifica la presenta in tabella della sigla autore Athesis, che si ottiene 
 *   da 3 lettere del cognome e 3 lettere del nome come viene 
 *   fatto per il codice fiscale. Qualora il codice sia già 
 *   presente per un altro autore va cambiata una lettera del nome 
 *   o del cognome per creare un codice unico in archivio.
 * 
 * - estrai_consonanti
 * - estrai_vocali
 * - gen_sigla_6 
 *   Partendo dal dato cognome_nome come previsto in elenco
 *   cognome, nome (anno nascita - anno morte)
 *   genera un code autore athesis di 6 lettere maiuscole.
 *   Non verifica sia già presente in archivio
 */
if (!defined('ABSPATH')){
	include_once('../_config.php');
}
include_once(ABSPATH . 'aa-model/database-handler-oop.php');
include_once(ABSPATH . 'aa-model/autori-oop.php');

/**
 * Mostra Elenco Autori  
 * Espone una pagina con l'elenco autori 
 * 
 * @param void 
 * @return void (espone html)
 */
function elenco_autori(){
  $dbh   = new DatabaseHandler();
  $aut_h = new Autori($dbh);
  $campi=[];
  $campi['query'] = "SELECT * FROM " . Autori::nome_tabella 
  . " ORDER BY cognome_nome, record_id ";
  $ret_aut = $aut_h->leggi($campi);
  if (isset($ret_aut['error'])){
    $_SESSION['messaggio']="Si è verificato un errore nell'accesso "
    . "all'elenco degli autori.<br>Non proseguire e inviare "
    . "questa schermata al comitato di gestione."
    . "<br>" . $ret_aut['message'];
    $elenco_autori = '<tr>' . PHP_EOL 
    . '<td colspan="5">Elenco non reperibile</td>'
    . '</tr>' . PHP_EOL;
    require_once(ABSPATH.'aa-view/autori-elenco-view.php');
    exit(0);
  }
  if ($ret_aut['numero'] == 0){
    $_SESSION['messaggio']="Si è verificato un errore nell'accesso "
    . "all'elenco degli autori.<br>Non proseguire e inviare "
    . "questa schermata al comitato di gestione."
    . "<br>Elenco vuoto";
    $elenco_autori = '<tr>' . PHP_EOL 
    . '<td colspan="5">Elenco non reperibile</td>'
    . '</tr>' . PHP_EOL;
    require_once(ABSPATH.'aa-view/autori-elenco-view.php');
    exit(0);
  }
  $elenco_autori='';
  foreach ($ret_aut['data'] as $autore) {
    $elenco_autori .= '<tr>' . PHP_EOL
    . '<td align="right">' . $autore['record_id'] . '</td>' . PHP_EOL;
    if ($autore['detto'] > ''){
      $elenco_autori .= '<td>'
      . $autore['cognome_nome'] 
      . ' / <i>detto "' . $autore['detto'] . '"</i>'
      . '</td>' . PHP_EOL;
    } else {
      $elenco_autori .= '<td>'
      . $autore['cognome_nome'] 
      . '</td>' . PHP_EOL;
    }
    if ($autore['url_autore'] > ''){
      $elenco_autori .= '<td>'
      . '<a target="_blank" href="'.$autore['url_autore'].'">link bio</a>' . PHP_EOL
      . '</td>' . PHP_EOL;
    } else {
      $elenco_autori .= '<td>n.d.</td>' . PHP_EOL;
    }
    $elenco_autori .= '<td>'.$autore['sigla_6'].'</td>' . PHP_EOL;
    $elenco_autori .= '<td><a href="'.URLBASE.'autori.php/modifica/'.$autore['record_id'].'" class="btn btn-success btn-sm">Modifica</a></td>' . PHP_EOL
    . '</tr>' . PHP_EOL;
  }
  // Si espone la pagina 
  require_once(ABSPATH.'aa-view/autori-elenco-view.php');
  exit(0);
} // elenco_autori()


/**
 * 
 */
function modifica_autore(int $autore_id, array $dati_input){
  $dbh   = new DatabaseHandler();
  $aut_h = new Autori($dbh);
  // necessari 
  $aut_h->set_record_id($autore_id);
  $campi=[];
  $campi['query'] = 'SELECT * FROM ' . Autori::nome_tabella
  . ' WHERE record_id = :record_id ';
  $campi['record_id'] = $aut_h->get_record_id();
  $ret_aut = $aut_h->leggi($campi);
  if (isset($ret_aut['error'])){
    $_SESSION['messaggio']="Si è verificato un errore nell'accesso "
    . "all'elenco degli autori.<br>Non proseguire e inviare "
    . "questa schermata al comitato di gestione."
    . "<br>" . $ret_aut['message'];
    $elenco_autori = '<tr>' . PHP_EOL 
    . '<td colspan="5">Autore non reperibile</td>'
    . '</tr>' . PHP_EOL;
    require_once(ABSPATH.'aa-view/autori-elenco-view.php');
    exit(0);
  }
  if ($ret_aut['numero'] == 0){
    $_SESSION['messaggio']="Si è verificato un errore nell'accesso "
    . "all'elenco degli autori.<br>Non proseguire e inviare "
    . "questa schermata al comitato di gestione."
    . "<br>Non trovato";
    $elenco_autori = '<tr>' . PHP_EOL 
    . '<td colspan="5">Autore non reperibile</td>'
    . '</tr>' . PHP_EOL;
    require_once(ABSPATH.'aa-view/autori-elenco-view.php');
    exit(0);
  }
  $autore = $ret_aut['data'][0];
  // se non ci sono i dati si espone il modulo di modifica 
  if (!isset($dati_input['cognome_nome'])){
    require_once(ABSPATH.'aa-view/autori-modifica-view.php');
    exit(0);
  }
  // se ci sono si va ad aggiornare 
  $aut_h->set_record_id($dati_input['record_id']);
  $aut_h->set_cognome_nome($dati_input['cognome_nome']);
  $aut_h->set_detto($dati_input['detto']);
  $aut_h->set_fisica_giuridica($dati_input['fisica_giuridica']);
  $aut_h->set_sigla_6($dati_input['sigla_6']);
  $aut_h->set_url_autore($dati_input['url_autore']);
  $campi=[];
  $campi['update'] = "UPDATE " . Autori::nome_tabella
  . " SET cognome_nome = :cognome_nome "
  . " , detto = :detto "
  . " , fisica_giuridica = :fisica_giuridica "
  . " , sigla_6 = :sigla_6 "
  . " , url_autore = :url_autore "
  . " WHERE record_id = :record_id ";
  $campi['cognome_nome'] = $aut_h->get_cognome_nome();
  $campi['detto'] = $aut_h->get_detto();
  $campi['fisica_giuridica'] = $aut_h->get_fisica_giuridica();
  $campi['sigla_6'] = $aut_h->get_sigla_6();
  $campi['url_autore'] = $aut_h->get_url_autore();
  $campi['record_id'] = $aut_h->get_record_id();
  $ret_aut=[];
  $ret_aut = $aut_h->modifica($campi);
  if (isset($ret_aut['error'])){
    $_SESSION['messaggio']="Si è verificato un errore nell'aggiornamento "
    . "dei dati dell'autore.<br>Non proseguire e inviare "
    . "questa schermata al comitato di gestione."
    . "<br>" . $ret_aut['message'];
    require_once(ABSPATH.'aa-view/autori-modifica-view.php');
    exit(0);
  }
  // Tutto bene
  $_SESSION['messaggio']="Aggiornamento eseguito.";
  $autore['cognome_nome'] = $aut_h->get_cognome_nome();
  $autore['detto'] = $aut_h->get_detto();
  $autore['fisica_giuridica'] = $aut_h->get_fisica_giuridica();
  $autore['sigla_6'] = $aut_h->get_sigla_6();
  $autore['url_autore'] = $aut_h->get_url_autore();
  $autore['record_id'] = $aut_h->get_record_id();
  require_once(ABSPATH.'aa-view/autori-modifica-view.php');
  exit(0);

} // modifica_autore()

/**
 * Ottiene i dati dalla pagina e li aggiunge all'elenco autori
 */
function aggiungi_autore(array $dati_input){
  // necessari 
  // se non ci sono i dati si espone il modulo di modifica 
  if (!isset($dati_input['cognome_nome'])){
    require_once(ABSPATH.'aa-view/autori-aggiungi-view.php');
    exit(0);
  }
  // se ci sono si va ad inserire
  $dbh   = new DatabaseHandler();
  $aut_h = new Autori($dbh);
  $aut_h->set_cognome_nome($dati_input['cognome_nome']);
  $aut_h->set_detto($dati_input['detto']);
  $aut_h->set_fisica_giuridica($dati_input['fisica_giuridica']);
  $aut_h->set_sigla_6($dati_input['sigla_6']);
  $aut_h->set_url_autore($dati_input['url_autore']);
  $campi=[];
  $campi['cognome_nome'] = $aut_h->get_cognome_nome();
  $campi['detto'] = $aut_h->get_detto();
  $campi['fisica_giuridica'] = $aut_h->get_fisica_giuridica();
  $campi['sigla_6'] = $aut_h->get_sigla_6();
  $campi['url_autore'] = $aut_h->get_url_autore();
  $ret_ins = $aut_h->aggiungi($campi);
  if (isset($ret_ins['error'])){
    $_SESSION['messaggio']="Si è verificato un errore nell'inserimento "
    . "dei dati dell'autore.<br>Non proseguire e inviare "
    . "questa schermata al comitato di gestione."
    . "<br>" . $ret_ins['message'];
    require_once(ABSPATH.'aa-view/autori-aggiungi-view.php');
    exit(0);
  }
  // Tutto bene
  $_SESSION['messaggio']="Inserimento eseguito.";
  $autore['cognome_nome'] = $aut_h->get_cognome_nome();
  $autore['detto'] = $aut_h->get_detto();
  $autore['fisica_giuridica'] = $aut_h->get_fisica_giuridica();
  $autore['sigla_6'] = $aut_h->get_sigla_6();
  $autore['url_autore'] = $aut_h->get_url_autore();
  require_once(ABSPATH.'aa-view/autori-aggiungi-view.php');
  exit(0);

} // aggiungi_autore()

/**
 * Non effettua verifica formale del dato ma la presenza in archivio 
 * per evitare l'inserimento di doppioni. Al posto del true/false
 * si è preferito un valore stringa per usarlo nei test in jQuery
 * 
 * @param  array  $dati_input ($_POST)
 * @return string 'present' | 'absent' 
 */
function verifica_sigla_6(array $dati_input) : string {
  if (!isset($dati_input['sigla_6'])){
    return 'present';
  }
  // verifica presenza
  $dbh   = new DatabaseHandler();
  $aut_h = new Autori($dbh);
  $aut_h->set_sigla_6($dati_input['sigla_6']);
  $campi=[];
  $campi['sigla_6'] = $aut_h->get_sigla_6();
  if ($campi['sigla_6'] == ""){
    return 'present';
  }
  // modifica vs inserisci 
  if (isset($dati_input['record_id']) && $dati_input['record_id'] > 0){
    $aut_h->set_record_id($dati_input['record_id']);
    $campi['query'] = 'SELECT 1 FROM '. Autori::nome_tabella 
    . ' WHERE sigla_6 = :sigla_6 '
    . ' AND record_id <> :record_id '
    . ' LIMIT 1 ';
    $campi['record_id'] = $aut_h->get_record_id();
  } else {
    $campi['query'] = 'SELECT 1 FROM '. Autori::nome_tabella 
    . ' WHERE sigla_6 = :sigla_6 '
    . ' LIMIT 1 ';
  }
  $ret_aut = $aut_h->leggi($campi);
  if (isset($ret_aut['error']) || $ret_aut['numero'] > 0){
    return 'present';
  }
  return 'absent';

} // verifica_sigla_6()

/**
 * Dal cognome nome crea una sigla 
 * La sigla può già essere presente nella tabella autori_elenco
 * ma la verifica viene demandata ad altre funzioni
 */
function estrai_consonanti(string $stringa) : string {
    return preg_replace('/[^BCDFGHJKLMNPQRSTVWXYZ]/i', '', strtoupper($stringa));
}

// Funzione per estrarre vocali
function estrai_vocali(string $stringa) : string {
    return preg_replace('/[^AEIOU]/i', '', strtoupper($stringa));
}

function gen_sigla_6(string $cognome_nome) : string {
  // Rainato, Massimo (1964 - 2024)
  $cognome_nome = preg_replace('/[\(|\)|\-]/i', '', $cognome_nome);
  // Rainato, Massimo 1964  2024
  $cognome_nome = preg_replace('/[0-9]/i', '', $cognome_nome);
  // "Rainato, Massimo    "
  $cognome_nome = trim($cognome_nome);
  // "Rainato, Massimo"
  [$cognome, $nome ]= explode(',', $cognome_nome);
  $cognome = strtoupper(trim($cognome)); // Rainato
  $nome    = strtoupper(trim($nome));    // Massimo 

  // Elaborazione cognome
  $consonanti_cognome = estrai_consonanti($cognome);
  $vocali_cognome = estrai_vocali($cognome);
  $parte_cognome = substr($consonanti_cognome . $vocali_cognome . 'XXX', 0, 3);

  // Elaborazione nome
  $consonanti_nome = estrai_consonanti($nome);
  if (strlen($consonanti_nome) > 3) {
      $consonanti_nome = $consonanti_nome[0] . $consonanti_nome[2] . $consonanti_nome[3];
  }
  $vocali_nome = estrai_vocali($nome);
  $parte_nome = substr($consonanti_nome . $vocali_nome . 'XXX', 0, 3);
  // TODO fare check codice già presente in archivio
  return $parte_cognome . $parte_nome;
}
