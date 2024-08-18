<?php
 /**
	*	@source abilitazioni-elenco-oop.php
	*	@author Massimo Rainato <maxrainato@libero.it>
	*
	*  Classe Abilitazioni 
	* 
	*	Accesso CRUD alla tabella abilitazioni_elenco
	*  La classe prende il nome di Abilitazioni e non AbilitazioneElenco
	* 
	*	Ogni funzione deve avere in input un array di chiavi-valori e restituisce 
	*	un array con lo status, message e dati, almeno due dei tre.
	*	dipendenze: serve una connessione all'archivio Classe DatabaseHandler 
	*
	*	@see https://archivio.athesis77.it/tech/3-archivi-tabelle/3-11-abilitazioni_elenco/
	*
	*
	*/

Class Abilitazioni {
	private $conn    = false;
	// TODO: valutare "extend DatabaseHandler"

	private $tabella = 'abilitazioni_elenco';
	private static $abilitazione_zero = '0 nessuna';
	private static $abilitazione_set = [
		'0 nessuna', '1 lettura', '3 modifica',
		'5 modifica originali', '7 amministrazione'
	];
	// $abilitazione_set[0] < $abilitazione_set[1] < $abilitazione_set[2] ... 
	private static $operazione_set = [
		'', 'leggi', 'modifica', 'backup', 'cancella'
	];
	
  public $record_id;
  public $url_pagina; 
  public $operazione;
  public $abilitazione; 
  public $ultima_modifica_record; 
  public $record_cancellabile_dal; 
  
  public function __construct(DatabaseHandler $dbh){
		$this->conn = $dbh;		
		
    $this->record_id      = 0;
    $this->url_pagina     = '';
    $this->abilitazione   = $this->abilitazione_zero;
    $this->operazione     = '';
    $this->ultima_modifica_record        = $dbh->get_datetime_now();
    $this->record_cancellabile_dal = $dbh->get_datetime_forever();
  } // __construct()
  
  
  // GETTER 
  /**
   * @return int unsigned
   */
  public function get_record_id() : int {
    return $this->record_id;
  }
  /**
   * @return string 
   */
  public function get_url_pagina() : string {
    return $this->url_pagina;
  }
  /**
   * @return string 
   */
  public function get_operazione() : string {
    return $this->operazione;
  }
  /**
   * @return string 
   */
  public function get_abilitazione() : string {
    return $this->abilitazione;
  }
  /**
   * @return string datetime 
   */
  public function get_ultima_modifica_record() : string {
    return $this->ultima_modifica_record;
  }
  
  /**
   * @return string datetime 
   */
  public function get_record_cancellabile_dal() : string {
    return $this->record_cancellabile_dal;
  }
  
  // SETTER + VALIDATION     
  public function setRecordId( int $record_id ) {
    // validazione - se qualcosa va storto non si continua, check nei log e si vede.
    if ($record_id < 1){
      throw new Exception( __CLASS__ . ' ' . __FUNCTION__ 
			. ' deve essere intero positivo: ' . $record_id );
    }
    $this->record_id = $record_id;
  }
  
  public function set_url_pagina( string $url_pagina ) {
    // validazione
    // pagine "/index.php" ok
    // pagine https://archivio.athesis77.it/ ok
    $url_pagina = filter_var($url_pagina, FILTER_SANITIZE_URL);
    if ( !str_starts_with('/', $url_pagina) && 
         !filter_var($url_pagina, FILTER_VALIDATE_URL)){
      throw new Exception( __CLASS__ . ' ' . __FUNCTION__ 
			. ' non risulta essere una URL: ' . $url_pagina );
    }
    // ritaglio
    $url_pagina = mb_substr($url_pagina, 0, 250);
    $this->url_pagina = $url_pagina;
  }
  
  public function set_operazione( string $operazione ) {
    // validazione
    if (!in_array($operazione, self::operazione_set)){
      $operazione = $this->operazione_set[0]; // la prima del set
    }    
    $this->operazione = $operazione;
  }
  
  public function set_abilitazione( string $abilitazione ) {
    // validazione
    if (!in_array($abilitazione, $this->abilitazione_set)){
      $abilitazione = $this->abilitazione_set[0]; // la prima del set
    }
    
    $this->abilitazione = $abilitazione;
  }
  
  public function setRecordCreatoIl( $ultima_modifica_record ) {
    // validazione
    if (!is_string($ultima_modifica_record)){
      throw new Exception( __CLASS__ . ' ' . __FUNCTION__ . ' deve essere una stringa: ' . $ultima_modifica_record );
    }
    if ( !$this->conn->is_datetime( $ultima_modifica_record )){
      throw new Exception( __CLASS__ . ' ' . __FUNCTION__ . ' deve essere una stringa nel formati datetime: ' . $ultima_modifica_record );
    }
    $this->ultima_modifica_record = $ultima_modifica_record;
  }
  
  public function setRecordCancellabileDal( $record_cancellabile_dal ) {
    // validazione
    if (!is_string($record_cancellabile_dal)){
      throw new Exception( __CLASS__ . ' ' . __FUNCTION__ . ' deve essere una stringa: ' . $record_cancellabile_dal );
    }
    if ( !$this->conn->is_datetime( $record_cancellabile_dal )){
      throw new Exception( __CLASS__ . ' ' . __FUNCTION__ . ' deve essere una stringa nel formati datetime: ' . $record_cancellabile_dal );
    }
    $this->record_cancellabile_dal = $record_cancellabile_dal;
  }

  // CHECKER
  /** 
   * @return bool 
   */
  public function checkAbilitazione( $abilitazione ) {
    // validazione    
    return is_string($abilitazione) && in_array($abilitazione, $this->abilitazione_set);
  }

  // CRUD 
  /** 
   * @param  array campi 
   * @return array ret 
   */
  public function aggiungi( array $campi = []) {
    // record_id               viene assegnato automaticamente pertanto non è in elenco 
    // ultima_modifica_record        viene assegnato automaticamente 
    // record_cancellabile_dal viene assegnato automaticamente 
    static $create = 'INSERT INTO ' . $this->tabella 
    . ' (  url_pagina,  operazione,  abilitazione ) VALUES '
    . ' ( :url_pagina, :operazione, :abilitazione ) ';

    $dbc = $this->conn; // a PDO object thru Database class
    if ($dbc === false){
      $ret = [
        "error"=> true, 
        "message" => "Inserimento record senza connessione archivio per: " . $this->tabella 
      ];
      return $ret;
    }
    $url_pagina   = ( isset($campi["url_pagina"])) ? $campi["url_pagina"] : $this->url_pagina;
    $url_pagina   =  htmlspecialchars(strip_tags($url_pagina));
    if ($url_pagina == ""){
      $ret = [
        "error"=> true, 
        "message" => "URL_PAGINA non gestita: " . $url_pagina 
      ];
      return $ret;
    }
    $operazione = isset($campi["operazione"]) ? $campi["operazione"] : $this->abilitazione;
    $operazione = htmlspecialchars(strip_tags($operazione));
    if ( !in_array($operazione, Abilitazioni::$operazione_set )){
      $ret = [
        "error"=> true, 
        "message" => "Operazione non gestita: " . $operazione 
      ];
      return $ret;
    }
    $abilitazione = isset($campi["abilitazione"]) ? $campi["abilitazione"] : $this->abilitazione;
    $abilitazione = htmlspecialchars(strip_tags($abilitazione));
    if ( !Abilitazioni::checkAbilitazione($abilitazione) ){
      $ret = [
        "error"=> true, 
        "message" => "Abilitazione non gestita: " . $abilitazione 
      ];
      return $ret;
    }
    
    try {
      $aggiungi = $dbc->prepare($create);
      $aggiungi->bindValue("url_pagina",   $url_pagina); 
      $aggiungi->bindValue("operazione",   $operazione); 
      $aggiungi->bindValue("abilitazione", $abilitazione); 
    	$aggiungi->execute();
    	$record_id_assegnato = $this->conn->lastInsertId();
    } catch( \Throwable $th ){
      $ret = [
        "record_id" => 0,
        "error" => true,
        "message" => __CLASS__ . ' ' . __FUNCTION__ . ' ' 
        . $th->getMessage() . " campi: " . serialize($campi)
        . ' istruzione SQL: ' . $create 
      ];
      return $ret;
    }

    $ret = [
      "ok" => true, 
      "record_id" => $record_id_assegnato
    ];
    return $ret;
  } // aggiungi
  
  
  /** 
   * Non è prevista paginazione per questa tabella che dovrebbe contenere 
   * un numero limitato di record validi.
   * I record con campo record_cancellabile_dal < '9999-12-31 23:59:59' sono 
   * da tenere nascosti per la cosiddetta cancellazione logica.
   * @param  array campi - dev'essere presente un campi['query'] con la select 
   * e i parametri che sono usati nella query 
   * @return array ret 
   */
  public function leggi( array $campi = []) {
    $dbc = $this->conn; // a PDO object thru Database class
    if ($dbc === false){
      $ret = [
        "error"=> true, 
        "message" => "Lettura record senza connessione archivio per: " . $this->tabella 
      ];
      return $ret;
    }
    if (!isset($campi["query"])){
      $ret = [
        "error"=> true, 
        "message" => "Lettura record senza QUERY: " . serialize($campi) 
      ];
      return $ret;
    }
    $read = $campi["query"];
    $lettura = $this->conn->prepare($read);
    if (isset($campi["record_id"])){
      $lettura->bindValue('record_id', $campi["record_id"], PDO::PARAM_INT); 
    }
    if (isset($campi["url_pagina"])){
      $lettura->bindValue('url_pagina', $campi["url_pagina"]); 
    }
    if (isset($campi["abilitazione"])){
      $lettura->bindValue('abilitazione', $campi["abilitazione"]); 
    }
    if (isset($campi["ultima_modifica_record"])){
      $lettura->bindValue('ultima_modifica_record', $campi["ultima_modifica_record"]); 
    }
    if (isset($campi["record_cancellabile_dal"])){
      $lettura->bindValue('record_cancellabile_dal', $campi["record_cancellabile_dal"]); 
    }
    try {
      $lettura->execute();
    } catch( \Throwable $th ){
      $ret = [
        "error" => true,
        "message" => $th->getMessage() . " campi: " . serialize($campi)
        . ' istruzione SQL: ' . $read
      ];
      return $ret;
    }
    $conteggio = 0;
    $dati_di_ritorno = [];
    $limite_record = 100;
    while(($record = $lettura->fetch(PDO::FETCH_ASSOC)) && ($conteggio < $limite_record)){
      if ($record === false) {
        break;
      }
      $dati_di_ritorno[] = $record;
      $conteggio++;
    }

    $ret = [
      'ok'     => true,
      'numero' => $conteggio,
      'data'   => $dati_di_ritorno 
    ];
    return $ret;
  } // leggi
  
  
  /**
   * ATTENZIONE: La modifica del campo "record_cancellabile_dal" viene 
   *             gestita come cancellazione logica, in attesa di una fase
   *             di scarico e cancellazione fisica.
   *
   * @param  array $campi - uno deve essere "update" e contenere una istruzione sql 
   * @return array $ret 
   */
  public function modifica( array $campi = []) : array {
    $dbc = $this->conn; // a PDO object thru Database class
    if ($dbc === false){
      $ret = [
        "error"=> true, 
        "message" => "Lettura record senza connessione archivio per: " . $this->tabella 
      ];
      return $ret;
    }
    if (!isset($campi["update"])){
      $ret = [
        "error"=> true, 
        "message" => "Aggiornamento record senza UPDATE: " . serialize($campi) 
      ];
      return $ret;
    }
    $update = $campi["update"];
    $aggiorna = $this->conn->prepare($update);
    if (isset($campi["record_id"])){
      $aggiorna->bindValue('record_id', $campi["record_id"], PDO::PARAM_INT); 
    }
    if (isset($campi["url_pagina"])){
      $aggiorna->bindValue('url_pagina', $campi["url_pagina"]); 
    }
    if (isset($campi["abilitazione"])){
      $aggiorna->bindValue('abilitazione',            $campi["abilitazione"]); 
    }
    if (isset($campi["ultima_modifica_record"])){
      $aggiorna->bindValue('ultima_modifica_record',        $campi["ultima_modifica_record"]); 
    }
    if (isset($campi["record_cancellabile_dal"])){
      $aggiorna->bindValue('record_cancellabile_dal', $campi["record_cancellabile_dal"]); 
    }
    try {
      $aggiorna->execute();
    } catch( \Throwable $th ){
      $ret = [
        "error" => true,
        "message" => $th->getMessage() . " campi: " . serialize($campi)
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
   * @param  array  $campi 
   * @return array  $ret 
   */
  public function elimina( array $campi = []) {
    $dbc = $this->conn; // a PDO object thru Database class
    if ($dbc === false){
      $ret = [
        "error"=> true, 
        "message" => "Cancellazione record senza connessione archivio per: " . $this->tabella 
      ];
      return $ret;
    }
    if (!isset($campi["delete"])){
      $ret = [
        "error"=> true, 
        "message" => "Cancellazione record senza DELETE: " . serialize($campi) 
      ];
      return $ret;
    }
    $delete = $campi["delete"];
    $cancella = $this->conn->prepare($delete);
    if (isset($campi["record_id"])){
      $cancella->bindValue('record_id', $campi["record_id"], PDO::PARAM_INT); 
    }
    if (isset($campi["url_pagina"])){
      $cancella->bindValue('url_pagina', $campi["url_pagina"]); 
    }
    if (isset($campi["abilitazione"])){
      $cancella->bindValue('abilitazione', $campi["abilitazione"]); 
    }
    if (isset($campi["ultima_modifica_record"])){
      $cancella->bindValue('ultima_modifica_record', $campi["ultima_modifica_record"]); 
    }
    if (isset($campi["record_cancellabile_dal"])){
      $cancella->bindValue('record_cancellabile_dal', $campi["record_cancellabile_dal"]); 
    }
    try {
      $cancella->execute();
    } catch( \Throwable $th ){
      $ret = [
        "error" => true,
        "message" => $th->getMessage() . " campi: " . serialize($campi)
        . ' istruzione SQL: ' . $delete
      ];
      return $ret;
    }
    $ret = [ 
      "ok" => true,
      "message" => "Cancellazione eseguita"
    ];
    return $ret;
  } // elimina
  
  
  /**
   *	getter record validi "vivi"
   *	crea una lista di istruzioni SQL per il caricamento dei dati
   */
  public function getElencoVivi() {
    $ret = [ 
      "error" => true,
      "message" => __CLASS__ . ' ' . __FUNCTION__ . " La funzione non è stata realizzata"
    ];
    return $ret;
  } // getElencoVivi
  
  
  /**
   *	getter record cancellabili 
   *	Crea una lista di istruzioni SQL per il caricamento dei record cancellabili
   *	prima della cancellazione fisica, poi questo elenco deve diventare un
   *	file con estensione sql che va scaricato dalla pagina / controller. 
   */
  public function getElencoCancellabili( array $campi = []) {
    $ret = [ 
      "error" => true,
      "message" => "La funzione non è stata realizzata"
    ];
    return $ret;
  } // getElencoCancellabili

} // class Abilitazioni