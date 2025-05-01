<?php
/**
 * DIDASCALIE - controller
 * 
 * - get_didascalia
 *   ottiene un paio di parametri e cerca di recuperare se esiste la didascalia
 * 
 * - aggiorna_didascalia
 *   ottiene i dati del modulo e inserisce la didascalia nuova,
 *   mettendo quella presente in stato di cancellabile per mantenere storia
 *   del pregresso.
 * 
 * - aggiungi
 */
if (!defined('ABSPATH')){
	include_once('../_config.php');
}
include_once(ABSPATH . 'aa-model/database-handler-oop.php');
include_once(ABSPATH . 'aa-model/didascalie-oop.php');
include_once(ABSPATH . 'aa-model/album-oop.php');
include_once(ABSPATH . 'aa-model/fotografie-oop.php');
include_once(ABSPATH . 'aa-model/video-oop.php');

/**
 * @param  int   didascalia_id 
 * @param  array dati_input 
 */
function aggiorna_didascalia( int $didascalia_id, array $dati_input) {
  if ($didascalia_id < 1){
    $ret = [
      'error'   => true,
			'message' => __FUNCTION__ . ' ' . __LINE__
			. '<br>Il parametro id non è corretto '
			. '<br>id: ' . $didascalia_id 
			. '<br>campi: ' . str_ireplace(';', '; ', serialize($dati_input)) 
		];
    echo '<p style="font-family:monospace;color:red;">'
    .$ret['message'].'</p>';
    exit(1);
  }
  //dbg echo '<p style="font-family:monospace;color:red;">'
  //dbg . 'input id: '  . $didascalia_id 
  //dbg . '<br>input: ' . str_ireplace(';', '; ', serialize($dati_input))
  //dbg . '</p>';
// se i dati mancano passo a vedere i dati
  $dbh     = New DatabaseHandler();
  $dida_h  = New Didascalie($dbh);
  if (!isset($dati_input['aggiorna_didascalia'])){
    $campi=[];
    $campi['query'] = "SELECT record_id, tabella_padre, record_id_padre, "
    . "didascalia FROM " . Didascalie::nome_tabella
    . " WHERE record_cancellabile_dal = :record_cancellabile_dal "
    . "AND record_id = :record_id ";
    $campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
    $campi['record_id']               = $didascalia_id;
    $ret_dida = $dida_h->leggi($campi);
    if (isset($ret_dida['error'])){
      $ret = [
        'error'   => true,
        'message' => __FUNCTION__ . ' ' . __LINE__
        . '<br>Non è stato possibile leggere il record perché sembra '
        . 'mancare '
        . '<br>ret: '  . str_ireplace(';', '; ', serialize($ret_dida)) 
        . '<br>campi: '. str_ireplace(';', '; ', serialize($campi)) 
      ];
      echo '<p style="font-family:monospace;color:red;">'
      .$ret['message'].'</p>';
      exit(1);  
    }
    if (isset($ret_dida['numero']) && $ret_dida['numero'] == 0 ){
      $ret = [
        'error'   => true,
        'message' => __FUNCTION__ . ' ' . __LINE__
        . '<br>Non è stato possibile aggiornare il record perché sembra '
        . 'mancare, non trovato. '
        . '<br>campi: ' . str_ireplace(';', '; ', serialize($campi)) 
      ];
      echo '<p style="font-family:monospace;color:red;">'
      .$ret['message'].'</p>';
      exit(1);  
    }
    $didascalia_letta= $ret_dida['data'][0];
    $tabella_padre   = $didascalia_letta['tabella_padre'];
    $record_id_padre = $didascalia_letta['record_id_padre'];
    $didascalia_id   = $didascalia_letta['record_id'];
    $didascalia      = $didascalia_letta['didascalia'];
    $titolo_elemento_padre = "";

    $_SESSION['messaggio'] = "Modifica la didascalia, "
    . "e consulta il manuale in caso di dubbi.";
		require_once( ABSPATH . 'aa-view/didascalie-edit-view.php');
		exit(0); 
  } // non è il modulo 

  // passo a usare i dati del modulo
  if (!isset($dati_input['tabella_padre'])){
    $ret = [
      'error'   => true,
			'message' => __FUNCTION__ . ' ' . __LINE__
			. '<br>Il parametro tabella_padre non è corretto '
			. '<br>campi: ' . str_ireplace(';', '; ', serialize($campi)) 
		];
    echo '<p style="font-family:monospace;color:red;">'
    .$ret['message'].'</p>';
    exit(1);  
  } // tabella_padre
  if (!isset($dati_input['record_id_padre']) || $dati_input['record_id_padre'] < 1){
    $ret = [
      'error'   => true,
			'message' => __FUNCTION__ . ' ' . __LINE__
			. '<br>Il parametro record_id_padre non è corretto '
			. '<br>campi: ' . str_ireplace(';', '; ', serialize($campi)) 
		];
    echo '<p style="font-family:monospace;color:red;">'
    .$ret['message'].'</p>';
    exit(1);  
  } // record_id_padre 
	$alb_h   = New Album($dbh);
	$foto_h  = New Fotografie($dbh);
	$vid_h   = New Video($dbh);
  
  // check tabella_padre record_id_padre devono essere nella loro tabella
  $tabella_padre = strtolower($dati_input['tabella_padre']);
  $dida_h->set_tabella_padre($tabella_padre);
  $tabella_padre= $dida_h->get_tabella_padre();
  //dbg echo '<p style="font-family:monospace;color:red;">'
  //dbg . 'tabella_padre: ' . $tabella_padre
  //dbg . '</p>';
  
  $record_id_padre = $dati_input['record_id_padre'];
  $dida_h->set_record_id_padre($record_id_padre);
  $record_id_padre = $dida_h->get_record_id_padre();
  //dbg echo '<p style="font-family:monospace;color:red;">'
  //dbg . 'record_id_padre: ' . $record_id_padre
  //dbg . '</p>';

  if ($tabella_padre == Didascalie::padre_album){
    $campi=[];
    $campi['query'] = "SELECT percorso_completo, titolo_album as titolo"
    . " FROM " . Album::nome_tabella 
    . " WHERE record_id = :record_id "
    . " AND record_cancellabile_dal = :record_cancellabile_dal ";
    $campi['record_id'] = $dati_input['record_id_padre'];
    $campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();

    $ret_padre = $alb_h->leggi($campi);
  } // album

  if ($tabella_padre == Didascalie::padre_fotografie){
    $campi=[];
    $campi['query'] = "SELECT percorso_completo, titolo_fotografia as titolo"
    . " FROM " . Fotografie::nome_tabella 
    . " WHERE record_id = :record_id "
    . " AND record_cancellabile_dal = :record_cancellabile_dal ";
    $campi['record_id'] = $dati_input['record_id_padre'];
    $campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();

    $ret_padre = $foto_h->leggi($campi);
  } // fotografie

  if ($tabella_padre == Didascalie::padre_video){
    $campi=[];
    $campi['query'] = "SELECT percorso_completo, titolo_video as titolo"
    . " FROM " . Video::nome_tabella 
    . " WHERE record_id = :record_id "
    . " AND record_cancellabile_dal = :record_cancellabile_dal ";
    $campi['record_id'] = $dati_input['record_id_padre'];
    $campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();

    $ret_padre = $vid_h->leggi($campi);
  } // video
  //dbg echo '<p style="font-family:monospace;color:red;">'
  //dbg . '<br>ret_padre: ' . str_ireplace(';', '; ', serialize($ret_padre))
  //dbg . '</p>';

  if (isset($ret_padre['error'])){
    $ret = [
      'error'   => true,
      'message' => __FUNCTION__ . ' ' . __LINE__
      . '<br>Non è stato possibile aggiornare il record perché sembra '
      . 'mancare il soggetto della didascalia '
      . '<br>ret: ' . str_ireplace(';', '; ', serialize($ret_padre)) 
      . '<br>campi: ' . str_ireplace(';', '; ', serialize($campi)) 
    ];
    echo '<p style="font-family:monospace;color:red;">'
    .$ret['message'].'</p>';
    exit(1);  
  }
  if (isset($ret_padre['numero']) && $ret_padre['numero'] == 0 ){
    $ret = [
      'error'   => true,
      'message' => __FUNCTION__ . ' ' . __LINE__
      . '<br>Non è stato possibile aggiornare il record perché sembra '
      . 'mancare il soggetto della didascalia '
      . '<br>campi: ' . str_ireplace(';', '; ', serialize($campi)) 
    ];
    echo '<p style="font-family:monospace;color:red;">'
    .$ret['message'].'</p>';
    exit(1);  
  }
  // SOFT-DELETE vado a settare la vecchia didascalia come cancellabile
  $campi=[];
  $campi['update'] = "UPDATE " . Didascalie::nome_tabella
  . " SET record_cancellabile_dal = :record_cancellabile_dal "
  . " WHERE record_id = :record_id ";
  $campi['record_id'] = $didascalia_id;
  $campi['record_cancellabile_dal'] = $dbh->get_datetime_now();
  $ret_del = $dida_h->modifica($campi);
  //dbg echo '<p style="font-family:monospace;color:red;">'
  //dbg . '<br>ret_del: ' . str_ireplace(';', '; ', serialize($ret_del))
  //dbg . '</p>';

  if (isset($ret_del['error'])){
    $ret = [
      'error'   => true,
      'message' => __FUNCTION__ . ' ' . __LINE__
      . '<br>Non è stato possibile aggiornare il record per '
      . ' aggiornare la didascalia '
      . '<br>ret: ' . str_ireplace(';', '; ', serialize($ret_del)) 
      . '<br>campi: ' . str_ireplace(';', '; ', serialize($campi)) 
    ];
    echo '<p style="font-family:monospace;color:red;">'
    .$ret['message'].'</p>';
    exit(1);  
  } // soft-delete 

  // Se la nuova didascalia è vuota abbiamo finito qui
  if (!isset($dati_input['didascalia']) || trim($dati_input['didascalia']) == ""){
    $ret = [
      'ok'        => true,
      'record_id' => 0,
      'message'   => __FUNCTION__ . ' ' . __LINE__
      . '<br>Nessuna Didascalia da registrare. '
      . '<br>ret: ' . str_ireplace(';', '; ', serialize($dati_input)) 
    ];
    return $ret;
  }

  // Registra didascalia
  $campi=[];
  $campi['tabella_padre']    = $tabella_padre;
  $campi['record_id_padre']  = $record_id_padre;
  $campi['didascalia']       = $dati_input['didascalia'];
  $ret_ins = $dida_h->aggiungi($campi);

  if (isset($ret_ins['error'])){
    $ret = [
      'error'   => true,
      'message' => __FUNCTION__ . ' ' . __LINE__
      . '<br>La didascalia precedente è stata rimossa ma non è stato possibile '
      . ' inserire la didascalia aggiornata. '
      . '<br>ret: ' . str_ireplace(';', '; ', serialize($ret_ins)) 
      . '<br>campi: ' . str_ireplace(';', '; ', serialize($campi)) 
    ];
    echo '<p style="font-family:monospace;color:red;">'
    .$ret['message'].'</p>';
    exit(1);  
  } // registra didascalia

  // passa alla pagina che viene aggiornata 
  header('Location: '.URLBASE.$campi['tabella_padre'].'.php/leggi/'.$campi['record_id_padre']);
  exit(0);
} // aggiorna_didascalia

/**
 * @param  array dati_input
 * 
 */
function aggiungi_didascalia(array $dati_input=[]){
  // se i dati mancano passo a vedere i dati
  $dbh     = New DatabaseHandler();
  $dida_h  = New Didascalie($dbh);
  
  // check parametri indispensabili 
  if (!isset($dati_input['tabella_padre']) || !in_array($dati_input['tabella_padre'], Didascalie::tabelle_padre_validi)){
    $ret = [
      'error'   => true,
			'message' => __FUNCTION__ . ' ' . __LINE__
			. '<br>Il parametro tabella_padre non è corretto '
			. '<br>campi: ' . str_ireplace(';', '; ', serialize($dati_input)) 
		];
    echo '<p style="font-family:monospace;color:red;">'
    .$ret['message'].'</p>';
    exit(1);
  }
  if (!isset($dati_input['record_id_padre']) || $dati_input['record_id_padre'] < 1 ){
    $ret = [
      'error'   => true,
			'message' => __FUNCTION__ . ' ' . __LINE__
			. '<br>Il parametro record_id_padre non è corretto '
			. '<br>campi: ' . str_ireplace(';', '; ', serialize($dati_input)) 
		];
    echo '<p style="font-family:monospace;color:red;">'
    .$ret['message'].'</p>';
    exit(1);
  }
  // check record_id_padre presente e valido in tabella_padre 
  // check assenza record didascala in didascalie con tabella_padre e record_id_padre 

  // propone modulo
  if (!isset($dati_input['aggiorna_didascalia'])){
    $tabella_padre   = $dati_input['tabella_padre'];
    $record_id_padre = $dati_input['record_id_padre'];
    $didascalia_id   = 0;
    $didascalia      = "Seleziona e sostituisci questo testo con la didascalia che vuoi inserire. Solo testo.";
    $titolo_elemento_padre = "";

    $_SESSION['messaggio'] = "Scrivi la didascalia, "
    . "e consulta il manuale in caso di dubbi.";
		require_once( ABSPATH . 'aa-view/didascalie-edit-view.php');
		exit(0); 
  }

  // inserisce dati modulo 
  $campi=[];
  $campi['tabella_padre']   = $dati_input['tabella_padre'];
  $campi['record_id_padre'] = $dati_input['record_id_padre'];
  $campi['didascalia']      = $dati_input['didascalia'];
  $ret_ins = $dida_h->aggiungi($campi);

  if (isset($ret_ins['error'])){
    $ret = [
      'error'   => true,
      'message' => __FUNCTION__ . ' ' . __LINE__
      . '<br>La didascalia non è stata inserita. '
      . '<br>ret: ' . str_ireplace(';', '; ', serialize($ret_ins)) 
      . '<br>campi: ' . str_ireplace(';', '; ', serialize($campi)) 
    ];
    echo '<p style="font-family:monospace;color:red;">'
    .$ret['message'].'</p>';
    exit(1);  
  } // registra didascalia

  // passa alla pagina che viene aggiornata 
  header('Location: '.URLBASE.$campi['tabella_padre'].'.php/leggi/'.$campi['record_id_padre']);
  exit(0);

} // aggiungi_didascalia

/**
 * TEST
if (isset($_GET['test']) && $_GET['test']='didascalia'){
  echo 'TEST didascalia ';
  $ret = aggiorna_didascalia($_GET['id'], []);
  echo 'ret: ' . str_ireplace(';', '; ', serialize($ret));
}
 *
 */
