<?php 
/**
 * @source /aa-model/chiavi-oop.php
 * @author Massimo Rainato <maxrainato@libero.it>
 * 
 * Classe Chiavi 
 * 
 * dipendenze: DatabaseHandler connessione archivio PDO 
 * 
 * @see https://archivio.athesis77.it/tech/3-archivi-tabelle/3-8-chiavi_elenco/ 
 * 
 * - getter 
 * - get_chiavi_option_list 
 *   funzione che ritorna un pezzo di html con le option per le chiavi in tabella 
 *   da inserire sotto alle select 
 * - get_chiavi_datalist 
 *   alternativo alla option list non per input select 
 *   ma per input text con funzioni di autocomplete in carico al browser 
 *   che deve essere aggiornato 
 * 
 * - setter 
 * 
 * - aggiungi
 * - leggi 
 * - modifica (compresa cancellazione non fisica)
 * - elimina 
 */
Class Chiavi {
  private $conn = false;
  public const nome_tabella = 'chiavi_elenco';
  public const genere_chiave_validi = [
    'unico',
    'ripetibile'
  ];
  public const chiave_unica = 'unico';
  public const chiave_ripetibile = 'ripetibile';

  public $record_id; //         
  public $chiave; //            
  public $url_manuale; //            
  public $unico; // enum 
  public $ultima_modifica_record; // 
  public $record_cancellabile_dal; // se non vale 9999-12-31 23:59:59 è cancellabile

  public function __construct(DatabaseHandler $dbh){
    $this->conn = $dbh;
    
    $this->record_id = 0; // invalido 
    $this->chiave = ''; // invalido 
    $this->url_manuale = ''; // invalido 
    $this->unico = self::chiave_unica;
    $this->ultima_modifica_record = $dbh->get_datetime_now();
    $this->record_cancellabile_dal = $dbh->get_datetime_forever();
  } // __construct
  
  // GETTER
  public function get_record_id() : int {
    return $this->record_id;
  } 
  public function get_chiave() : string {
    return $this->chiave;
  } 
  public function get_url_manuale() : string {
    return $this->url_manuale;
  } 
  public function get_unico() : string{
    return $this->unico;
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

	/**
   * Restituisce una stringa contenente html per essere aggiunta a una 
   * postazione di <select>
	 * @return string html option-list
	 */
	public function get_chiavi_option_list() : string {
		$dbh = $this->conn; 
		if ($dbh === false){
			$ret = '<option value="--">--</option>'."\n";
			return $ret;
		}
		$campi=[];
		$campi['query']= 'SELECT chiave FROM ' . self::nome_tabella 
		. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
		. ' ORDER BY chiave, record_id ';
		$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
		$ret_list = $this->leggi($campi);
		if ( isset($ret_list['error']) || $ret_list['numero'] == 0){
			$ret = '<option value="--">--</option>'."\n";
			return $ret;
		}
		$ret = ''."\n";
		$option_list = $ret_list['data']; 
		for ($i=0; $i < count($option_list) ; $i++) { 
			$ret .= "\t". '<option value="'.$option_list[$i]['chiave'].'">' 
      . $option_list[$i]['chiave'] . '</option>'."\n";
		}
		return $ret; 
	}

  /**
   * Restituisce una stringa di html per affiancare campi input 
   * fornendo in automatico suggerimenti 
   * Viene esclusa <datalist>
   */
  public function get_chiavi_datalist() : string {
		$dbh = $this->conn; 
		if ($dbh === false){
      throw new Exception(__CLASS__ . ' ' . __FUNCTION__ 
      . ' To get table value must have a db connection.' );
		}
    // si può usare $this->leggi solo con $query 
    $campi=[];
    $campi['query'] = 'SELECT chiave FROM ' . self::nome_tabella
    . ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
    . ' ORDER BY chiave ';
    $campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
    $ret_list = $this->leggi($campi);
    if (isset($ret_list['error'])){
      $ret = '<!-- No data for: '. $ret_list['message'].' -->'."\n";
      return $ret;
    }
    if (isset($ret_list['numero']) && $ret_list['numero'] == 0){
      $ret = '<!-- zero records found -->'."\n";
      return $ret;
    }
    $ret = ''."\n";
    // $ret .= '<datalist id="elencoChiaviRicerca">'."\n";
    $elenco_chiavi = $ret_list['data'];
    for ($i=0; $i < count($elenco_chiavi); $i++) { 
      $ret .= "\t".'<option>'.$elenco_chiavi[$i]['chiave'].'</option>'."\n";
    }
    // $ret .= '</datalist>'."\n";
    return $ret;
  } // get_chiavi_datalist

  /**
   * get_chiave_record_per_chiave
   * Esegue la ricerca del record e ritorna il primo record valido 
   * oppure un messaggio di errore 
   *
   * @param  string $chiave_cercata 
   * @return array  'ok' + record | 'error' + message   
   */
  public function get_chiave_record_per_chiave( string $chiave_cercata = '') : array {
		$dbh = $this->conn; 
		if ($dbh === false){
      throw new Exception(__CLASS__ . ' ' . __FUNCTION__ 
      . ' To get table value "must have" a db connection.' );
		}
    $this->set_chiave($chiave_cercata);

    $campi = [];
    $campi['query'] = 'SELECT * FROM ' . self::nome_tabella 
    . ' WHERE record_cancellabile_da = :record_cancellabile_dal '
    . ' AND chiave = :chiave '
    . ' ORDER BY record_id ';
    $campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
    $campi['chiave'] = $this->get_chiave();
    $ret_chiave = $this->leggi($campi);
    if (isset($ret_chiave['error'])){
      return $ret_chiave;
    }
    if ($ret_chiave['numero'] < 1){
      $ret = [
        'error'   => true,
        'message' => "La chiave {$chiave_cercata} non è stata trovata."
      ];
      return $ret;
    }
    $ret = [
      'ok'     => true,
      'record' => $ret_chiave['data'][0]
    ];
    return $ret;
  } // get_chiave_record_per_chiave

  /**
   * get_chiave_record_per_id
   * Versione alternativa alla chiamata ->leggi()
   * per semplificare il codice 
   */
  public function get_chiave_record_per_id( int $chiave_id_cercata = 0) : array{
		$dbh = $this->conn; 
		if ($dbh === false){
      throw new Exception(__CLASS__ . ' ' . __FUNCTION__ 
      . ' To get table value "must have" a db connection.' );
		}
    $this->set_record_id($chiave_id_cercata);

    $campi = [];
    $campi['query'] = 'SELECT * FROM ' . self::nome_tabella 
    . ' WHERE record_cancellabile_da = :record_cancellabile_dal '
    . ' AND record_id = :record_id ';
    $campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
    $campi['record_id'] = $this->get_record_id();
    $ret_chiave = $this->leggi($campi);
    if (isset($ret_chiave['error'])){
      return $ret_chiave;
    }
    if ($ret_chiave['numero'] < 1){
      $ret = [
        'error'   => true,
        'message' => "La chiave con id {$chiave_id_cercata} non è stata trovata."
      ];
      return $ret;
    }
    $ret = [
      'ok'     => true,
      'record' => $ret_chiave['data'][0]
    ];
    return $ret;
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
  
  public function set_url_manuale( string $url_manuale ) {
    // validazione
    $url_manuale = htmlspecialchars(strip_tags($url_manuale));
    $url_manuale = mb_substr($url_manuale, 0, 250);
    $this->url_manuale = $url_manuale;
  }

  public function set_unico( string $unico) {
    $unico= strtolower($unico);
    if (in_array($unico, self::genere_chiave_validi) ){
      $this->unico = $unico;      
    }
  }

  /** 
   * @param  string datetime yyyy-mm-dd hh:mm:ss 
   */
  public function set_ultima_modifica_record( string $ultima_modifica_record ) {
    // validazione
    if ( !$this->conn->is_datetime( $ultima_modifica_record )){
      throw new Exception(__CLASS__ . ' ' . __FUNCTION__ 
      . ' Must be datetime is : ' . $ultima_modifica_record );
    }
    $this->ultima_modifica_record = $ultima_modifica_record;
  }
  
  /** 
   * @param  string datetime yyyy-mm-dd hh:mm:ss 
   */
  public function set_record_cancellabile_dal( string $record_cancellabile_dal ) {
    // validazione
    if ( !$this->conn->is_datetime( $record_cancellabile_dal )){
      throw new Exception(__CLASS__ . ' ' . __FUNCTION__ 
      . ' Must be datetime is : ' . $record_cancellabile_dal );
    }
    $this->record_cancellabile_dal = $record_cancellabile_dal;
  }

	// CRUD 
	/**
	 * CREATE aggiungi
	 * @param  array campi 
	 * @return array ret  ok + record_id | error + message 
	 */
	public function aggiungi( array $campi) : array {
    // record_id               viene assegnato automaticamente pertanto non è in elenco 
		// url_manuale             facoltativo ""
		// unico                   facoltativo "unico"
    // ultima_modifica_record  viene assegnato automaticamente 
    // record_cancellabile_dal viene assegnato automaticamente 
    $create = 'INSERT INTO ' . self::nome_tabella 
    . ' (   chiave,  url_manuale,  unico ) VALUES '
    . ' (  :chiave, :url_manuale, :unico ) '; // doppio spazio intenzionale

    // dati obbligatori
    $dbh = $this->conn; // a PDO object thru Database class
    if ($dbh === false){
      $ret = [
        "error"=> true, 
        "message" => __CLASS__ . ' ' . __FUNCTION__ 
        . " Inserimento record senza connessione archivio per: " 
        . self::nome_tabella 
      ];		
      return $ret;
    }	
		if (!isset($campi['chiave']) || $campi['chiave'] == ''){
      $ret = [
        "error"=> true, 
        "message" => __CLASS__ . ' ' . __FUNCTION__ 
        . " chiave deve essere valorizzato. " 
      ];	
      return $ret;
		}
    $this->set_chiave($campi['chiave']);
    
    $unico = (isset($campi['unico'])) ? $campi['unico'] : self::chiave_unica;
    $this->set_unico($unico);

		$url_manuale = isset($campi['url_manuale']) ? $campi['url_manuale'] : "";
    $this->set_url_manuale($url_manuale);

    if (!$dbh->inTransaction()) { $dbh->beginTransaction(); }		
    try{
      $aggiungi = $dbh->prepare($create);
      $aggiungi->bindValue('chiave', $this->chiave);
      $aggiungi->bindValue('url_manuale', $this->url_manuale);
      $aggiungi->bindValue('unico', $this->unico);
      $aggiungi->execute();
      $record_id = $dbh->lastInsertID();
			$dbh->commit();
      
		} catch(\Throwable $th ){
			$dbh->rollBack(); 
      $ret = [
        'error'      => true,
        'record_id' => 0,
        'message' => __CLASS__ . ' ' . __FUNCTION__ 
        . ' Si è verificato un errore: ' . $th->getMessage() 
				. ' campi: ' . serialize($campi)
        . ' istruzione SQL: ' . $create 
      ];	
      return $ret;
    } // try catch 	

    $ret = [
      'ok'        => true, 
      'record_id' => $record_id,
      'message'   => __CLASS__ . ' ' . __FUNCTION__ 
      . ' Inserimento record effettuato, nuovo id: ' . $record_id 
    ];	
    return $ret;
  } //aggiungi	

  /**   
   * READ - Leggi
   * @param   array $campi - deve contenere un $campi['query'] con una istruzione SQL SELECT
   * @return  array $ret   'ok'|'error' + 'message'| data
   */
  public function leggi(array $campi ) : array {
    // campi obbligatori 
    $dbh = $this->conn; // a PDO object thru Database class
    if ($dbh === false){
      $ret = [
        "error"=> true, 
        "message" => __CLASS__ . ' ' . __FUNCTION__ 
        . " lettura record senza connessione archivio per: " 
        . self::nome_tabella
      ];
      return $ret;
    }
    if (!isset($campi['query'])){
      $ret = [
        "error" => true, 
        "message" => __CLASS__ . ' ' . __FUNCTION__ 
        . "Deve essere definita l'istruzione SELECT in ['query']: " 
        . serialize($campi)
      ];
      return $ret;
    }
    
    if (isset($campi['record_id'])) {
      $this->set_record_id($campi['record_id']);
    }
    if (isset($campi['chiave'])){
      $this->set_chiave($campi['chiave']);
    }
    if (isset($campi['url_manuale'])){
      $this->set_url_manuale($campi['url_manuale']);
    }
    if (isset($campi['ultima_modifica_record'])){
      $this->set_ultima_modifica_record($campi['ultima_modifica_record']);
    }
    if (isset($campi['record_cancellabile_dal'])){
      $this->set_record_cancellabile_dal($campi['record_cancellabile_dal']);
    }
    $read = $campi['query'];    

    try {
      $lettura = $dbh->prepare($read);
      if (isset($campi['record_id'])){
        $lettura->bindValue('record_id', $this->record_id, PDO::PARAM_INT); // gli altri campi sono tipo string 
      }
      if (isset($campi['chiave'])){
        $lettura->bindValue('chiave', $this->chiave); 
      }
      if (isset($campi['url_manuale'])){
        $lettura->bindValue('url_manuale', $this->url_manuale); 
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
        'error'   => true,
        'message' => __CLASS__ . ' ' . __FUNCTION__ . ' ' 
        . ' Si è verificato un errore: ' . $th->getMessage() 
        . " campi: " . serialize($campi)
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
   * UPDATE 
   * DELETE 
   * ATTENZIONE: La modifica del campo "record_cancellabile_dal" viene 
   *             gestita come cancellazione logica, in attesa di una fase
   *             di scarico e cancellazione fisica.
   *
   * @param  array $campi - uno deve essere "update" e contenere una istruzione sql 
   * @return array $ret 
   */
  public function modifica(array $campi) : array {
    // dati obbligatori 
    $dbh = $this->conn; // a PDO object thru Database class
    if ($dbh === false){
      $ret = [
        'error'   => true, 
        'message' => __CLASS__ . ' ' . __FUNCTION__ 
        . ' Modifica senza connessione archivio per: ' 
        . self::nome_tabella 
      ];
      return $ret;
    }
    if (!isset($campi['update'])){
      $ret = [
        'error'   => true, 
        'message' => __CLASS__ . ' ' . __FUNCTION__ 
        . " Aggiornamento record senza UPDATE: " 
        . serialize($campi) 
      ];
      return $ret;
    }

    if (isset($campi['record_id'])){
      $this->set_record_id($campi['record_id']);
    }
    if (isset($campi['chiave'])){
      $this->set_chiave($campi['chiave']);
    }
    if (isset($campi['unico'])){
      $this->set_unico($campi['unico']);
    }
    if (isset($campi['url_manuale'])){
      $this->set_url_manuale($campi['url_manuale']);
    }
    if (isset($campi['ultima_modifica_record'])){
      $this->set_ultima_modifica_record($campi['ultima_modifica_record']);
    }
    if (isset($campi['record_cancellabile_dal'])){
      $this->set_record_cancellabile_dal($campi['record_cancellabile_dal']);
    }
    $update = $campi['update'];
    if (!$dbh->inTransaction()) { $dbh->beginTransaction(); }

    try {
      $aggiorna = $dbh->prepare($update);
      if (isset($campi['record_id'])){
        $aggiorna->bindValue('record_id', $this->record_id, PDO::PARAM_INT); 
      }
      if (isset($campi['chiave'])){
        $aggiorna->bindValue('chiave', $this->chiave); 
      }
      if (isset($campi['unico'])){
        $aggiorna->bindValue('unico', $this->unico); 
      }
      if (isset($campi['url_manuale'])){
        $aggiorna->bindValue('url_manuale', $this->url_manuale); 
      }
      if (isset($campi['ultima_modifica_record'])){
        $aggiorna->bindValue('ultima_modifica_record', $this->ultima_modifica_record); 
      }
      if (isset($campi['record_cancellabile_dal'])){
        $aggiorna->bindValue('record_cancellabile_dal', $this->record_cancellabile_dal); 
      }
      $aggiorna->execute();
			$dbh->commit();

    } catch( \Throwable $th ){
      $ret = [
        "error" => true,
        "message" => __CLASS__ . ' ' . __FUNCTION__ . ' ' 
        . $th->getMessage() 
        . ' campi: '          . serialize($campi)
        . ' istruzione SQL: ' . $update
      ];
      return $ret;
    }
    $ret = [ 
      'ok'      => true,
      'message' => 'Aggiornamento eseguito'
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
  public function elimina(array $campi) : array {
    // campi obbligatori 
    $dbh = $this->conn; // a PDO object thru Database class
    if ($dbh === false){
      $ret = [
        "error"=> true, 
        "message" => "La cancellazione di record "
        . "non si può fare senza connessione archivio "
        . "per: " . self::nome_tabella 
      ];
      return $ret;
    }
    if (!isset($campi['delete'])){
      $ret = [
        "error"=> true, 
        "message" => __CLASS__ . ' ' . __FUNCTION__ 
        . "Deve essere definita l'istruzione DELETE in ['delete']: " 
        . serialize($campi)
      ];
      return $ret;
    }
    // verifiche campi passati, se ci sono
    if (isset($campi['record_id'])){
      $this->set_record_id($campi['record_id']);
    }
    if (isset($campi['chiave'])){
      $this->set_chiave($campi['chiave']);
    }
    if (isset($campi['url_manuale'])){
      $this->set_url_manuale($campi['url_manuale']);
    }
    if (isset($campi['ultima_modifica_record'])){
      $this->set_ultima_modifica_record($campi['ultima_modifica_record']);
    }
    if (isset($campi['record_cancellabile_dal'])){
      $this->set_record_cancellabile_dal($campi['record_cancellabile_dal']);
    }
    //
    $cancellazione = $campi['delete'];
    try {
      $cancella = $dbh->prepare($cancellazione);
      if (isset($campi['record_id'])){
        $cancella->bindValue('record_id', $this->record_id, PDO::PARAM_INT); 
      }
      if (isset($campi['chiave'])){
        $cancella->bindValue('chiave', $this->chiave); 
      }
      if (isset($campi['url_manuale'])){
        $cancella->bindValue('url_manuale', $this->url_manuale); 
      }
      if (isset($campi['ultima_modifica_record'])){
        $cancella->bindValue('ultima_modifica_record', $this->ultima_modifica_record); 
      }
      if (isset($campi['record_cancellabile_dal'])){
        $cancella->bindValue('record_cancellabile_dal', $this->record_cancellabile_dal); 
      }
      $cancella->execute();
    } catch( Exception $e) {
      $ret = [
          'error' => true,
          'message' => __CLASS__ . ' ' . __FUNCTION__  
          . $e->getMessage() 
          . ' campi: ' . serialize($campi) 
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

} // Class Chiavi 