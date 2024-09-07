<?php 
/**
 * CONSULTATORI controller 
 * 
 * - accesso_checkpoint
 *   presenta o gestisce l'accredito nel sito 
 * 
 * - calendario_consultatori
 *   Presenta l'elenco dell'agenda accessi per i consultatori 
 *   "di oggi" (non si vedono i record futuri o terminati)
 * 
 * - aggiunta_consultatore
 * - modifica_consultatore
 * - dettaglio_consultatore 
 * - cancella_consultatore 
 *   
 */
if (!defined('ABSPATH')){
	include_once('../_config.php');
}
include_once(ABSPATH . 'aa-model/database-handler-oop.php');
include_once(ABSPATH . 'aa-model/consultatori-oop.php');


function accesso_checkpoint(array $dati_input){
  // pagina destinazione 
  $return_to = '';
  if (isset($dati_input['return_to'])){
    $return_to = $dati_input['return_to'];
  } elseif (isset($_GET['return_to'])){
    $return_to = urldecode($_GET['return_to']);
  } else {
    $return_to = URLBASE.'museo.php';
  }

  // niente dati - espone il modulo
  if (!isset($dati_input['accesso_email']) || 
      !isset($dati_input['accesso_password'])){
    $_SESSION['messaggio']='';
    require_once(ABSPATH.'aa-view/consultatori-accesso-view.php');
    exit(0);
  }
  // i dati ci sono 1/verifica 
  $dbh = new DatabaseHandler();
  $acc_h = new Consultatori($dbh);
  $acc_h->set_email($dati_input['accesso_email']);
  $data_di_oggi = date("Y-m-d");
  $campi=[];
  $campi['query'] = ' SELECT * FROM ' . Consultatori::nome_tabella
  . ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
  . ' AND attivita_dal <= :attivita_dal '
  . ' AND attivita_fino_al >= :attivita_fino_al '
  . ' AND email = :email ';
  $campi['record_cancellabile_dal'] = constant('FUTURO');
  $campi['attivita_dal']     = $data_di_oggi;
  $campi['attivita_fino_al'] = $data_di_oggi;
  $campi['email'] = $acc_h->get_email();
  $ret_acc = $acc_h->leggi($campi);
  if (isset($ret_acc['error'])){
    $_SESSION['messaggio']="Si è verificato un errore nell'accesso "
    . "all'archivio dei consultatori.<br>Non proseguire e inviare "
    . "questa schermata al comitato di gestione."
    . "<br>" . $ret_acc['message'];
    require_once(ABSPATH.'aa-view/consultatori-accesso-view.php');
    exit(0);
  }
  if ($ret_acc['numero'] == 0){
    $_SESSION['messaggio']="Si è verificato un errore nell'accesso "
    . "all'archivio dei consultatori.<br>Non proseguire e inviare "
    . "questa schermata al comitato di gestione."
    . "<br>". str_replace(';' , '; ', serialize($campi))
    . "<br> Record non trovato o scaduto.";
    require_once(ABSPATH.'aa-view/consultatori-accesso-view.php');
    exit(0);
  }
  $acc_h->set_password($dati_input['accesso_password']);
  $pass = $acc_h->get_password();
  // si possono trovare più record
  $id_consultatore = 0;
  $abilitazione_consultatore = '';
  $cognome_nome = '';
  $email='';
  foreach ($ret_acc['data'] as $consultatore) {
    if (strcmp($consultatore['password'], $pass) != 0){
      continue;
    }
    $abilitazione_calendario = str_replace("'", '', $consultatore['abilitazione']);
    if (strncmp($abilitazione_consultatore, $abilitazione_calendario, 2) < 0){
      $id_consultatore = $consultatore['record_id'];
      $abilitazione_consultatore = $abilitazione_calendario;
      $cognome_nome = $consultatore['cognome_nome'];
      $email = $consultatore['email'];
    }
  }
  if ($id_consultatore == 0){
    $_SESSION['messaggio']="Si è verificato un errore nell'accesso "
    . "all'archivio dei consultatori.<br>Non proseguire e inviare "
    . "questa schermata al comitato di gestione."
    . "<br> Record non trovato o scaduto. [2]";
    require_once(ABSPATH.'aa-view/consultatori-accesso-view.php');
    exit(0);
  }
  // trovato, settaggio cookie e sessione e rinvio alla pagina 
  session_reset();
  $_SESSION['consultatore']   =$cognome_nome;
  $_SESSION['abilitazione']   =$abilitazione_consultatore;
  $_SESSION['accesso_email']  =$email;
  $_SESSION['consultatore_id']=$id_consultatore;
  // cookie 
  $scadenza = (int) time()+10*24*60*60; // 10 giorni in secondi 
  $expires  = date("D, d M Y H:i:s",$scadenza).' GMT'; // headers setcookie 
  $dominio  = str_replace('https://', '', URLBASE);
  $dominio  = str_replace('http://', '', $dominio);
  $dominio  = substr($dominio, 0, strpos($dominio, '/', 0));
  // servono in localhost   
  header("Set-Cookie: consultatore='$cognome_nome'; Expires='$expires'; Path=/; SameSite=None; ", false);
  header("Set-Cookie: abilitazione='$abilitazione_consultatore'; Expires='$expires'; Path=/; SameSite=None; ", false);
  header("Set-Cookie: consultatore_id=$id_consultatore; Expires='$expires'; Path=/; SameSite=None; ", false);
  // servono online 
  setcookie("consultatore",    $cognome_nome,  $scadenza, "/", $dominio); 
  setcookie("abilitazione",    $abilitazione,  $scadenza, "/", $dominio); 
  setcookie("accesso_email",   $accesso_email, $scadenza, "/", $dominio); 
  setcookie("consultatore_id", $id_calendario, $scadenza, "/", $dominio); 

  // si gira alla pagina di destinazione 
  header("Location: ". $return_to );
  exit(0); // tutto ok - termina  
} // accesso_checkpoint()

function calendario_consultatori(){
  // accesso alla tabella per i record "validi" 
  $dbh = new DatabaseHandler();
  $acc_h = new Consultatori($dbh);
  $campi=[];
  $campi['query'] = 'SELECT * FROM ' . Consultatori::nome_tabella
  . ' WHERE record_cancellabile_dal = :record_cancellabile_dal  '
  . ' AND attivita_dal <= :attivita_dal '
  . ' AND attivita_fino_al >= :attivita_fino_al '
  . ' ORDER BY cognome_nome, abilitazione DESC, attivita_fino_al, '
  . ' attivita_dal ';
  $campi['record_cancellabile_dal'] = constant('FUTURO');
  $data_di_oggi = date("Y-m-d");
  $campi['attivita_dal'] = $data_di_oggi;
  $campi['attivita_fino_al'] = $data_di_oggi;
  $ret_acc = $acc_h->leggi($campi);
  $calendario_consultatori = '<tr><td colspan="7"><h5>Nessun record trovato</h5></td></tr>';
  if (isset($ret_acc['error'])){
    $_SESSION['messaggio']="Si è verificato un errore nell'accesso "
    . "all'archivio dei consultatori.<br>Non proseguire e inviare "
    . "questa schermata al comitato di gestione."
    . "<br>" . $ret_acc['message'];
    require_once(ABSPATH.'aa-view/consultatori-elenco-view.php');
    exit(0);
  }
  if ($ret_acc['numero'] == 0){
    $_SESSION['messaggio']="Si è verificato un errore nell'accesso "
    . "all'archivio dei consultatori.<br>Non proseguire e inviare "
    . "questa schermata al comitato di gestione."
    . "<br>". str_replace(';' , '; ', serialize($campi))
    . "<br> Record non trovato o scaduto.";
    require_once(ABSPATH.'aa-view/consultatori-elenco-view.php');
    exit(0);
  }
  $calendario_consultatori = '';
  foreach($ret_acc['data'] as $consultatore){
    $calendario_consultatori .= '<tr>' . PHP_EOL;
    $calendario_consultatori .= '<td>'. $consultatore['record_id'] . '</td>'. PHP_EOL;
    $calendario_consultatori .= '<td>'. $consultatore['cognome_nome'] . '</td>'. PHP_EOL;
    $calendario_consultatori .= '<td>'. $consultatore['abilitazione'] . '</td>'. PHP_EOL;
    $calendario_consultatori .= '<td>'. $consultatore['attivita_dal'] . '</td>'. PHP_EOL;
    $calendario_consultatori .= '<td>'. $consultatore['attivita_fino_al'] . '</td>'. PHP_EOL;
    $calendario_consultatori .= '<td>'. $consultatore['ultima_modifica_record'] . '</td>'. PHP_EOL;
    $calendario_consultatori .= '<td>'. PHP_EOL;
    $calendario_consultatori .= '<a href="'.URLBASE.'consultatori.php/dettaglio/'. $consultatore['record_id'] .'" class="btn btn-info btn-sm">Dettagli</a>'. PHP_EOL;
    $calendario_consultatori .= '<a href="'.URLBASE.'consultatori.php/modifica/'. $consultatore['record_id'] .'" class="btn btn-success btn-sm">Modifica</a>'. PHP_EOL;
    $calendario_consultatori .= '<a href="'.URLBASE.'consultatori.php/elimina/'. $consultatore['record_id'] .'" class="btn btn-danger btn-sm">Elimina</a>'. PHP_EOL;
    $calendario_consultatori .= '</td>'. PHP_EOL;
    $calendario_consultatori .= '</tr>'. PHP_EOL;
  }
  // si espone
  require_once(ABSPATH.'aa-view/consultatori-elenco-view.php');
  exit(0);

} // calendario_consultatori()

/**
 * Espone un  modulo se mancano i dati e aggiorna archivio
 * se i dati ci sono
 */
function aggiunta_consultatore(array $dati_input){
  $dbh   = New DatabaseHandler();
  $acc_h = New Consultatori($dbh);

  // niente dati - espone il modulo
  if (!isset($dati_input['email']) || 
      !isset($dati_input['password1'])){
    $_SESSION['messaggio']='Inserimento dati';
    require_once(ABSPATH.'aa-view/consultatori-aggiungi-view.php');
    exit(0);
  }

  $password1 = $dati_input['password1'];
  $password2 = $dati_input['password2'];
  if (strcmp($password1, $password2) != 0){
    $_SESSION['messaggio']='Le password non erano uguali, '
    . 'riscriverle daccapo e fare attenzione';
    require_once(ABSPATH.'aa-view/consultatori-aggiungi-view.php');
    exit(0);
  }
  // verifiche 
  $acc_h->set_cognome_nome($dati_input['cognome_nome']);
  $acc_h->set_abilitazione($dati_input['abilitazione']);
  $acc_h->set_attivita_dal($dati_input['attivita_dal']);
  $acc_h->set_attivita_fino_al($dati_input['attivita_fino_al']);
  $acc_h->set_email($dati_input['email']);
  $acc_h->set_password($dati_input['password1']);
  //
  $campi=[];
  $campi['cognome_nome'] = $acc_h->get_cognome_nome();
  $campi['abilitazione'] = $acc_h->get_abilitazione();
  $campi['attivita_dal'] = $acc_h->get_attivita_dal();
  $campi['attivita_fino_al'] = $acc_h->get_attivita_fino_al();
  $campi['email'] = $acc_h->get_email();
  $campi['password'] = $acc_h->get_password();
  $ret_acc=[];
  $ret_acc = $acc_h->aggiungi($campi);
  if (isset($ret_acc['error'])){
    $_SESSION['messaggio']="Si è verificato un errore nell'accesso "
    . "all'archivio dei consultatori.<br>Non proseguire e inviare "
    . "questa schermata al comitato di gestione."
    . "<br><br>Campi:" . serialize($dati_input)
    . "<br><br>" . $ret_acc['message'];
    require_once(ABSPATH.'aa-view/consultatori-aggiungi-view.php');
    exit(0);
  }
  // si torna all'elenco 
  $_SESSION['messaggio']="Agenda inserita."
  . "<br>dati::". $dati_input['abilitazione'] . '::'
  . '<br>acc::' .$acc_h->get_abilitazione() .'::' ;
  calendario_consultatori();
  exit(0);
} // aggiunta_consultatore()


/**
 * 
 */
function modifica_consultatore(int $consultatore_id, array $dati_input){
  $dbh = new DatabaseHandler();
  $acc_h = new Consultatori($dbh);
  // verifica 
  $acc_h->set_record_id($consultatore_id);
  $campi=[];
  $campi['query'] = 'SELECT * FROM ' . Consultatori::nome_tabella
  . ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
  . ' AND record_id = :record_id ';
  $campi['record_cancellabile_dal'] = constant('FUTURO');
  $campi['record_id'] = $acc_h->get_record_id();
  $ret_acc = $acc_h->leggi($campi);
  if (isset($ret_acc['error'])){
    $_SESSION['messaggio']="Si è verificato un errore nell'accesso "
    . "all'archivio dei consultatori.<br>Non proseguire e inviare "
    . "questa schermata al comitato di gestione."
    . "<br>" . $ret_acc['message'];
    require_once(ABSPATH.'aa-view/consultatori-modifica-view.php');
    exit(1);
  }
  if ($ret_acc['numero'] == 0){
    $_SESSION['messaggio']="Si è verificato un errore nell'accesso "
    . "all'archivio dei consultatori.<br>Non proseguire e inviare "
    . "questa schermata al comitato di gestione."
    // . "<br>". str_replace(';' , '; ', serialize($campi))
    . "<br> Record non trovato o scaduto.";
    require_once(ABSPATH.'aa-view/consultatori-modifica-view.php');
    exit(1);
  }
  $consultatore=$ret_acc['data'][0];
  // espongo per la modifica o modifico? 
  // mancano i dati, espongo per la modifica 
  if (!isset($dati_input['password1'])){
    $_SESSION['messaggio'] = "Modificate quello che serve.";
    require_once(ABSPATH.'aa-view/consultatori-modifica-view.php');
    exit(0);
  }
  //
  // i dati ci sono, modifico 
  $password1 = $dati_input['password1'];
  $password2 = $dati_input['password2'];
  if (strcmp($password1, $password2) != 0){
    $_SESSION['messaggio']='Le password non erano uguali, riscriverle daccapo e fare attenzione';
    require_once(ABSPATH.'aa-view/consultatori-modifica-view.php');
    exit(0);
  }
  $data_inizio = $dati_input['attivita_dal'];
  $data_termine = $dati_input['attivita_fino_al'];
  if (strcmp($data_inizio, $data_termine) > 0){
    $_SESSION['messaggio']='Le date non sono coerenti, riscriverle daccapo e fare attenzione';
    require_once(ABSPATH.'aa-view/consultatori-modifica-view.php');
    exit(0);
  }

  $acc_h->set_record_id($dati_input['record_id']);
  $acc_h->set_cognome_nome($dati_input['cognome_nome']);
  $acc_h->set_abilitazione($dati_input['abilitazione']);
  $acc_h->set_attivita_dal($dati_input['attivita_dal']);
  $acc_h->set_attivita_fino_al($dati_input['attivita_fino_al']);
  $acc_h->set_email($dati_input['email']);
  $acc_h->set_password($dati_input['password1']);
  //
  $campi=[];
  $campi['update'] = 'UPDATE ' . Consultatori::nome_tabella
  . ' SET cognome_nome = :cognome_nome, '
  . ' abilitazione = :abilitazione, '
  . ' attivita_dal = :attivita_dal, '
  . ' attivita_fino_al = :attivita_fino_al, '
  . ' email = :email, '
  . ' password = :password '
  . ' WHERE record_id = :record_id ';
  $campi['cognome_nome'] = $acc_h->get_cognome_nome();
  $campi['abilitazione'] = $acc_h->get_abilitazione();
  $campi['attivita_dal'] = $acc_h->get_attivita_dal();
  $campi['attivita_fino_al'] = $acc_h->get_attivita_fino_al();
  $campi['email'] = $acc_h->get_email();
  $campi['password'] = $acc_h->get_password();
  $campi['record_id'] = $acc_h->get_record_id();
  $ret_acc=[];
  $ret_acc = $acc_h->modifica($campi);
  if (isset($ret_acc['error'])){
    $_SESSION['messaggio']="Si è verificato un errore nell'accesso "
    . "all'archivio dei consultatori.<br>Non proseguire e inviare "
    . "questa schermata al comitato di gestione."
    . "<br>" . $ret_acc['message'];
    require_once(ABSPATH.'aa-view/consultatori-modifica-view.php');
    exit(1);
  }
  $_SESSION['messaggio']="Aggiornamento effettuato, potete tornare all'elenco";
  // si espone
  http_response_code(200);
  header('Location: '.URLBASE.'consultatori.php/elenco/');
  exit(0);
} // modifica_consultatore()

/**
 * 
 */
function dettaglio_consultatore(int $consultatore_id){
  $dbh = new DatabaseHandler();
  $acc_h = new Consultatori($dbh);
  // verifica 
  $consultatore=[];
  $acc_h->set_record_id($consultatore_id);
  $campi=[];
  $campi['query'] = 'SELECT * FROM ' . Consultatori::nome_tabella
  . ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
  . ' AND record_id = :record_id ';
  $campi['record_cancellabile_dal'] = constant('FUTURO');
  $campi['record_id'] = $acc_h->get_record_id();
  $ret_acc = $acc_h->leggi($campi);
  if (isset($ret_acc['error'])){
    $_SESSION['messaggio']="Si è verificato un errore nell'accesso "
    . "all'archivio dei consultatori.<br>Non proseguire e inviare "
    . "questa schermata al comitato di gestione."
    . "<br>" . $ret_acc['message'];
    require_once(ABSPATH.'aa-view/consultatori-dettaglio-view.php');
    exit(1);
  }
  if ($ret_acc['numero'] == 0){
    $_SESSION['messaggio']="Si è verificato un errore nell'accesso "
    . "all'archivio dei consultatori.<br>Non proseguire e inviare "
    . "questa schermata al comitato di gestione."
    . "<br>". str_replace(';' , '; ', serialize($campi))
    . "<br> Record non trovato o scaduto.";
    require_once(ABSPATH.'aa-view/consultatori-dettaglio-view.php');
    exit(1);
  }
  $consultatore=$ret_acc['data'][0];
  require_once(ABSPATH.'aa-view/consultatori-dettaglio-view.php');
  exit(0);
} // dettaglio_consultatore()


/**
 * 
 */
function cancella_consultatore(int $consultatore_id){
  $dbh = new DatabaseHandler();
  $acc_h = new Consultatori($dbh);
  // verifica 
  $acc_h->set_record_id($consultatore_id);
  $campi=[];
  $campi['query'] = 'SELECT * FROM ' . Consultatori::nome_tabella
  . ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
  . ' AND record_id = :record_id ';
  $campi['record_cancellabile_dal'] = constant('FUTURO');
  $campi['record_id'] = $acc_h->get_record_id();
  $ret_acc = $acc_h->leggi($campi);
  if (isset($ret_acc['error'])){
    $_SESSION['messaggio']="Si è verificato un errore nell'accesso "
    . "all'archivio dei consultatori.<br>Non proseguire e inviare "
    . "questa schermata al comitato di gestione."
    . "<br>" . $ret_acc['message'];
    echo '<pre style="color: red;">'. $_SESSION['messaggio'] .'</pre>'."\n";
    exit(1);
  }
  if ($ret_acc['numero'] == 0){
    $_SESSION['messaggio']="Si è verificato un errore nell'accesso "
    . "all'archivio dei consultatori.<br>Non proseguire e inviare "
    . "questa schermata al comitato di gestione."
    . "<br>". str_replace(';' , '; ', serialize($campi))
    . "<br> Record non trovato o scaduto.";
    echo '<pre style="color: red;">'. $_SESSION['messaggio'] .'</pre>'."\n";
    exit(1);
  }
  $consultatore=$ret_acc['data'][0];
  // Aggiorno 
  $campi=[];
  $campi['update'] = 'UPDATE ' . Consultatori::nome_tabella
  . ' SET record_cancellabile_dal = :record_cancellabile_dal '
  . ' WHERE record_id = :record_id ';
  $campi['record_cancellabile_dal'] = $dbh->get_datetime_now();
  $campi['record_id'] = $acc_h->get_record_id();
  $ret_acc=[];
  $ret_acc = $acc_h->modifica($campi);
  if (isset($ret_acc['error'])){
    $_SESSION['messaggio']="Si è verificato un errore nell'accesso "
    . "all'archivio dei consultatori.<br>Non proseguire e inviare "
    . "questa schermata al comitato di gestione."
    . "<br>" . $ret_acc['message'];
    echo '<pre style="color: red;">' . $_SESSION['messaggio'] . '</pre>'."\n";
    exit(1);
  }
  $_SESSION['messaggio']= "Agenda consultatore cancellata";
  // si espone
  http_response_code(200);
  header('Location: '.URLBASE.'consultatori.php/elenco/');
  exit(0);
} // cancella_consultatore()
