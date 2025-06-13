<?php
/**
 * @source /aa-model/vocabolario-oop.php
 * @author Massimo Rainato <maxrainato@libero.it>
 * 
 * Classe Vocabolario
 * 
 *	dipendenze: DatabaseHandler connessione archivio PDO 
 *	dipendenze: Chiavi tabella padre 
 * 
 * Per alcuni elementi chiave esistono dei limitati insiemi di 
 * valori buoni che cono elencati in questa tabella.
 * Da qui vengono estratti al volo gli insiemi che consentono 
 * la creazione di liste option per le VIEW 
 * la creazione di liste per la verifica nei controller 
 * 
 * @see https://archivio.athesis77.it/tech/3-archivi-tabelle/3-8-vocabolario/
 * 
 * metodi
 * GETTER
 *   get_record_id
 *   get_chiave
 *   get_valore 
 *   get_ultima_modifica_record 
 *   get_record_cancellabile_dal 
 * SETTER
 *   set_record_id
 *   set_chiave
 *   set_valore 
 *   set_ultima_modifica_record 
 *   set_record_cancellabile_dal 
 * CRUD
 *   aggiungi CREATE
 *   leggi    READ 
 *   modifica UPDATE
 *   elimina  DELETE 
 * OTHERS
 * 
 */
Class Vocabolario extends DatabaseHandler {
  public $conn;
  public const nome_tabella = 'vocabolario';
  
  public $record_id; //         
  public $chiave; //            
  public $valore; //            
  public $ultima_modifica_record; // 
  public $record_cancellabile_dal; // se non vale 9999-12-31 23:59:59 è cancellabile
  
  public function __construct(DatabaseHandler $dbh){
    $this->conn = $dbh;
    
    $this->record_id = 0;
    $this->chiave = '';
    $this->valore = '';
    $this->ultima_modifica_record = $dbh->get_datetime_now();
    $this->record_cancellabile_dal = $dbh->get_datetime_forever();
  } // __construct
  
  // GETTER 
  public function get_record_id() {
    return $this->record_id;
  }
  
  public function get_chiave() {
    return $this->chiave;
  }
  
  public function get_valore() {
    return $this->valore;
  }
  
  /** 
   * @return string datetime
   */
  public function get_ultima_modifica_record(){
    return $this->ultima_modifica_record;
  }
  
  /** 
   * @return string datetime
   */
  public function get_record_cancellabile_dal(){
    return $this->record_cancellabile_dal;
  }
  
  
  // SETTER 
  public function set_record_id( int $record_id ){
    if ($record_id < 1){
      throw new Exception(__CLASS__ . ' ' . __FUNCTION__ 
      . ' Must be unsigned integer is : ' . $record_id );
    }
    $this->record_id = $record_id;
  }
  
  public function set_chiave( string $chiave ) {
    // validazione
    $chiave = htmlspecialchars(strip_tags($chiave));
    $chiave = mb_substr($chiave, 0, 250);
    if ($chiave == ""){
      throw new Exception(__CLASS__ . ' ' . __FUNCTION__ 
      . ' chiave Cannot be empty. ' );
    }
    $this->chiave = $chiave;
  }
  
  public function set_valore( string $valore ) {
    // validazione
    $valore = htmlspecialchars(strip_tags($valore));
    $valore = mb_substr($valore, 0, 250);
    if ($valore == ""){
      throw new Exception(__CLASS__ . ' ' . __FUNCTION__ 
      . ' valore Cannot be empty. ' );
    }
    $this->valore = $valore;
  }

  /** 
   * @param  string datetime yyyy-mm-dd hh:mm:ss 
   */
  public function set_ultima_modifica_record( string $ultima_modifica_record ) {
    $dbh = $this->conn;
    // validazione
    if ( !$dbh->is_datetime( $ultima_modifica_record )){
      throw new Exception(__CLASS__ . ' ' . __FUNCTION__ 
      . ' Must be datetime is : ' . $ultima_modifica_record );
    }
    $this->ultima_modifica_record = $ultima_modifica_record;
  }
  
  /** 
   * @param  string datetime yyyy-mm-dd hh:mm:ss 
   */
  public function set_record_cancellabile_dal( string $record_cancellabile_dal ) {
    $dbh = $this->conn;
    // validazione
    if ( !$dbh->is_datetime( $record_cancellabile_dal )){
      throw new Exception(__CLASS__ . ' ' . __FUNCTION__ 
      . ' Must be datetime is : ' . $record_cancellabile_dal );
    }
    $this->record_cancellabile_dal = $record_cancellabile_dal;
  }
  
  // CHECKER 
  
  // CRUD 
  /**
   * Aggiungi CREATE 
   * @param  array campi 
   * @return array ret  ok + record_id | error + message 
   */
  public function aggiungi( array $campi){
    // record_id               viene assegnato automaticamente pertanto non è in elenco 
    // ultima_modifica_record        viene assegnato automaticamente 
    // record_cancellabile_dal viene assegnato automaticamente 
    $create = 'INSERT INTO ' . self::nome_tabella 
    . ' ( chiave,  valore ) VALUES '
    . ' (:chiave, :valore ) ';

    // dati obbligatori
    $dbh = $this->conn; // a PDO object thru Database class
    
    if (!isset($campi['chiave']) || $campi['chiave'] === ''){
      $ret = [
        "error"=> true, 
        "message" => __CLASS__ . ' ' . __FUNCTION__ 
        . " chiave deve essere valorizzato. " 
      ];
      return $ret;
    }
    $this->set_chiave($campi['chiave']);

    if (!isset($campi['valore']) || $campi['valore'] === ''){
      $ret = [
        "error"=> true, 
        "message" => __CLASS__ . ' ' . __FUNCTION__ 
        . " valore deve essere valorizzato. O lo volete cancellare? " 
      ];
      return $ret;
    }
    $this->set_valore($campi['valore']);

		if (!$dbh->inTransaction()) { $dbh->beginTransaction(); }		
    try{
      $aggiungi = $dbh->prepare($create);
      $aggiungi->bindValue('chiave', $this->chiave);
      $aggiungi->bindValue('valore', $this->valore);
      $aggiungi->execute();
      $record_id = $dbh->lastInsertID();
			$dbh->commit();

    } catch(\Throwable $th ){
			$dbh->rollBack(); 

      $ret = [
        'error'   => true,
        'record_id' => 0,
        'message' => __CLASS__ . ' ' . __FUNCTION__ 
        .'<br>Errore: ' . $th->getMessage() 
        .'<br>Campi:'  . $dbh::esponi($campi)
        .'<br>istruzione SQL: ' . $create
      ];
      return $ret;
    } // try catch 

    $ret = [
      'ok'        => true, 
      'record_id' => $record_id,
      'message'   => __CLASS__ . ' ' . __FUNCTION__ 
      . " Inserimento record effettuato, nuovo id: " . $record_id 
    ];
    return $ret;
  } //aggiungi


  /**   
   * leggi READ
   * @param   array $campi - deve contenere un $campi["query"] con una istruzione SQL SELECT
   * @return  array $ret   'ok'|'error' + 'message'| data
   */
  public function leggi(array $campi = []){
    // campi obbligatori 
    $dbh = $this->conn; // a PDO object thru Database class
    if (!isset($campi["query"])){
      $ret = [
        "error"=> true, 
        "message" => __CLASS__ . ' ' . __FUNCTION__ 
        . "Deve essere definita l'istruzione SELECT in ['query']: " 
        . $dbh::esponi($campi)
      ];
      return $ret;
    }
    $read = $campi["query"];    
    
    /* Si suppone che se nella query c'è una clausola WHERE :nome-campo 
     * venga ragionevolmente aggiunto anche il campo in $campi[nome-campo]
     * L'alternativa è str_contains ma penso sia più pesante da gestire.
     * nuovi campi per query diverse vanno aggiungi man mano che 
     * le esigenze lo richiedono.
     */
    if (isset($campi["record_id"])) {
      $this->set_record_id($campi["record_id"]);
    }
    if (isset($campi["chiave"])){
      $this->set_chiave($campi["chiave"]);
    }
    if (isset($campi["valore"])){
      $this->set_valore($campi["valore"]);
    }
    if (isset($campi["ultima_modifica_record"])){
      $this->set_ultima_modifica_record($campi["ultima_modifica_record"]);
    }
    if (isset($campi["record_cancellabile_dal"])){
      $this->set_record_cancellabile_dal($campi["record_cancellabile_dal"]);
    }

    try {
      $lettura = $dbh->prepare($read);
      if (isset($campi['record_id'])){
        $lettura->bindValue('record_id', $this->record_id, PDO::PARAM_INT); // gli altri campi sono tipo string 
      }
      if (isset($campi['chiave'])){
        $lettura->bindValue('chiave', $this->chiave); 
      }
      if (isset($campi['valore'])){
        $lettura->bindValue('valore', $this->valore); 
      }
      if (isset($campi['ultima_modifica_record'])){
        $lettura->bindValue('ultima_modifica_record', $this->ultima_modifica_record); 
      }
      if (isset($campi['record_cancellabile_dal'])){
        $lettura->bindValue('record_cancellabile_dal', $this->record_cancellabile_dal); 
      }
      $lettura->execute();

    } catch( \Throwable $th ) {
      $ret = [
        "error" => true,
        "message" => __CLASS__ . ' ' . __FUNCTION__ . ' ' 
        . $th->getMessage() . " campi: " . $dbh::esponi($campi)
        . ' istruzione SQL: ' . $read
      ];
      return $ret;
    }
    $conteggio = 0; // può esserci un $limite
    $dati_di_ritorno = []; // è sempre un array
    while( $record = $lettura->fetch(PDO::FETCH_ASSOC) ){
      $dati_di_ritorno[] = $record;
      $conteggio++; 
    } // while
    $ret = [
      'ok'     => true,
      'numero' => $conteggio,
      'data'   => $dati_di_ritorno 
    ];
    return $ret;
  } // leggi
  

  /**
   * Modifica UPDATE 
   * SOFT DELETE aggiornando il campo record_cancellabile_dal 
   * ATTENZIONE: La modifica del campo "record_cancellabile_dal" viene 
   *             gestita come cancellazione logica, in attesa di una fase
   *             di scarico e cancellazione fisica.
   *
   * @param  array $campi - uno deve essere "update" e contenere una istruzione sql 
   * @return array $ret 
   */
  public function modifica(array $campi = []){
    // dati obbligatori 
    $dbh = $this->conn; // a PDO object thru Database class
    if (!isset($campi["update"])){
      $ret = [
        "error"=> true, 
        "message" => __CLASS__ . ' ' . __FUNCTION__ 
        . " Aggiornamento record senza UPDATE: " 
        . $dbh::esponi($campi) 
      ];
      return $ret;
    }
    if (isset($campi["record_id"])){
      $this->set_record_id($campi["record_id"]);
    }
    if (isset($campi["chiave"])){
      $this->set_chiave($campi["chiave"]);
    }
    if (isset($campi["valore"])){
      $this->set_valore($campi["valore"]);
    }
    if (isset($campi["ultima_modifica_record"])){
      $this->set_ultima_modifica_record($campi["ultima_modifica_record"]);
    }
    if (isset($campi["record_cancellabile_dal"])){
      $this->set_record_cancellabile_dal($campi["record_cancellabile_dal"]);
    }
    // fine dei controlli 
    $update = $campi["update"];
    try {
      $aggiorna = $dbh->prepare($update);
      if (isset($campi["record_id"])){
        $aggiorna->bindValue('record_id', $this->record_id, PDO::PARAM_INT); 
      }
      if (isset($campi["chiave"])){
        $aggiorna->bindValue('chiave', $this->chiave); 
      }
      if (isset($campi["valore"])){
        $aggiorna->bindValue('valore', $this->valore); 
      }
      if (isset($campi["ultima_modifica_record"])){
        $aggiorna->bindValue('ultima_modifica_record', $this->ultima_modifica_record); 
      }
      if (isset($campi["record_cancellabile_dal"])){
        $aggiorna->bindValue('record_cancellabile_dal', $this->record_cancellabile_dal); 
      }
      $aggiorna->execute();
    } catch( \Throwable $th ){
      $ret = [
        "error" => true,
        "message" => __CLASS__ . ' ' . __FUNCTION__ . ' ' 
        . $th->getMessage() 
        . ' campi: '          . $dbh::esponi($campi)
        . ' istruzione SQL: ' . $update
      ];
      return $ret;
    }
    $ret = [ 
      "ok" => true,
      "message" => "Aggiornamento eseguito"
    ];
    return $ret;
  } // modifica


  /**
   * Esegue la cancellazione fisica del record, non la cancellazione logica
   * ATTENZIONE: Esiste la gestione del campo "record_cancellabile_dal"
   *             fatta apposta per consentire di "cancellare logicamente"
   *             i record, vedi manuale tecnico amministrativo.
   * $campi deve un campo DELETE che contiene una istruzione SQL 
   * 
   * @param  array  $campi 
   * @return array  $ret 
   */
  public function elimina(array $campi = []){
    // campi obbligatori 
    $dbh = $this->conn; // a PDO object thru Database class
    if (!isset($campi["delete"])){
      $ret = [
        "error"=> true, 
        "message" => __CLASS__ . ' ' . __FUNCTION__ 
        . "<br>Deve essere definita l'istruzione DELETE in ['delete']: " 
        . $dbh::esponi($campi)
      ];
      return $ret;
    }
    // verifiche campi passati, se ci sono
    if (isset($campi["record_id"])){
      $this->set_record_id($campi["record_id"]);
    }
    if (isset($campi["chiave"])){
      $this->set_chiave($campi["chiave"]);
    }
    if (isset($campi["valore"])){
      $this->set_valore($campi["valore"]);
    }
    if (isset($campi["ultima_modifica_record"])){
      $this->set_ultima_modifica_record($campi["ultima_modifica_record"]);
    }
    if (isset($campi["record_cancellabile_dal"])){
      $this->set_record_cancellabile_dal($campi["record_cancellabile_dal"]);
    }
    //
    $cancellazione = $campi["delete"];
    try {
      $cancella = $dbh->prepare($cancellazione);
      if (isset($campi["record_id"])){
        $cancella->bindValue('record_id', $this->record_id, PDO::PARAM_INT); 
      }
      if (isset($campi["chiave"])){
        $cancella->bindValue('chiave', $this->chiave); 
      }
      if (isset($campi["valore"])){
        $cancella->bindValue('valore', $this->valore); 
      }
      if (isset($campi["ultima_modifica_record"])){
        $cancella->bindValue('ultima_modifica_record', $this->ultima_modifica_record); 
      }
      if (isset($campi["record_cancellabile_dal"])){
        $cancella->bindValue('record_cancellabile_dal', $this->record_cancellabile_dal); 
      }
      $cancella->execute();
    } catch( Exception $e) {
      $ret = [
          'error' => true,
          'message' => __CLASS__ . ' ' . __FUNCTION__  
          . $e->getMessage() 
          . ' campi: ' . $dbh::esponi($campi) 
          . ' istruzione SQL: ' . $cancellazione 
      ];
      return $ret;
    }
    
    $ret = [
      'ok' => true,
      'message' => "Sono stati cancellati " 
      . $cancella->rowCount() ." record(s)."
    ];
    return $ret;
  } // elimina


} // Vocabolario