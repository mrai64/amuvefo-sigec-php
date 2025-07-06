<?php
/**
 * @source /aa-model/ricerche-oop.php
 * @author Massimo Rainato <maxrainato@libero.it>
 * 
 * Classe Ricerche
 * 
 * Le ricerche fatte in archivio da qualsiasi consultatore
 * anche quello anonimo e pubblico, sono registrate per consentire
 * in seguito di riprendere la consultazione dalla ricerca già fatta.
 * 
 * NOTA dato che richiesta e risultato possono o sono sempre array 
 * associativi e non, il text che li memorizza viene convertito nel formato
 * json - javascript object notation
 * 
 * __construct 
 * get_record_id
 * get_richiesta
 * get_richiesta_array 
 * get_risultato
 * get_risultato_array
 * get_consultatore_id 
 * get_ultimo_aggiornamento_record 
 * get_record_cancellabile_dal 
 * 
 * set_record_id
 * set_richiesta
 * set_richiesta_array 
 * set_risultato
 * set_risultato_array
 * set_consultatore_id 
 * set_ultimo_aggiornamento_record 
 * set_record_cancellabile_dal 
 * 
 * aggiungi   Create
 * leggi      Read
 * modifica   Update
 * elimina    Delete
 * 
 */
Class Ricerche extends DatabaseHandler {
  public $conn; // a Database Handler 

	public const nome_tabella  = 'ricerche';
  // in archivio 
	public $record_id; //        bigint(20) unsigned auto+ primary 
	public $richiesta; //        text - la ricerca richiesta
	public $risultato; //        text - i dati tornati 
	public $consultatore_id; //  bigint(20) unsigned chiave esterna di consultatori_calendario 
	public $ultima_modifica_record; //        datetime DEF CURRENT TIME
	public $record_cancellabile_dal; //       datetime DEF '9999-12-31 23:59:59'

  public function __construct(DatabaseHandler $dbh){
		$this->conn = $dbh;

		$this->record_id       = 0;   // invalido
		$this->richiesta       = '';  // invalido
		$this->risultato       = '';  // invalido
		$this->consultatore_id = 0;   // invalido
		$this->ultima_modifica_record  = $dbh->get_datetime_now();
		$this->record_cancellabile_dal = $dbh->get_datetime_forever();
	} // __construct()

  /**
   * GETTER - le funzioni di lettura dei campi in tabella ricerche
   */
	/**
	 * @return int unsigned
	 */
	public function get_record_id() : int {
		return $this->record_id;
	}
	
  public function get_richiesta() : string {
		$richiesta = $this->richiesta;
		$richiesta = htmlspecialchars_decode($richiesta);
		return $richiesta;
	}

  public function get_richiesta_array() : array {
		$richiesta = $this->richiesta;
		return json_decode($richiesta, true);
	}
  
  public function get_risultato() : string {
    $risultato = $this->risultato;
		$risultato = htmlspecialchars_decode($risultato);
		return $risultato;
	}

  public function get_risultato_array() : array {
    $risultato = $this->risultato;
    return json_decode($risultato, true);
  }
	/**
	 * @return int unsigned
	 */
	public function get_consultatore_id() : int {
		return $this->consultatore_id;
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
   * SETTER - le funzioni di filtro e scrittura dei campi in tabella ricerche
   */
	public function set_record_id(int $record_id){
		if (!is_int($record_id) || $record_id < 1){
			// crea eccezione
			throw new Exception( __CLASS__ . ' ' . __FUNCTION__ 
			. " non sembra un numero intero, vale: " 
			. $record_id, 1);
		}
		$this->record_id = $record_id;
	}
  /**
   * quella string e quella array > json > string
   */
  public function set_richiesta(string $richiesta){
    $richiesta = preg_replace('/[^\p{L}\p{N}\p{Zs}\p{P}]/u', '', $richiesta);
    $richiesta = htmlspecialchars($richiesta);
  	$richiesta = str_ireplace(['.', ':', ';', '#', "'", '"'], '', $richiesta); // punteggiatura deprecata 
  	$richiesta = str_ireplace(['select ', 'delete ', 'update '], '', $richiesta); // comandi per sql injection 
  	$richiesta = mb_substr($richiesta, 0, 2000); // ritaglio
    $this->richiesta = $richiesta;
  }
  public function set_richiesta_array(array $richiesta){
  	$richiesta = json_encode($richiesta);
  	$richiesta = mb_substr($richiesta, 0, 2000); // ritaglio
    $this->richiesta = $richiesta;
  }

  public function set_risultato(string $risultato){
    $risultato = preg_replace('/[^\p{L}\p{N}\p{Zs}\p{P}]/u', '', $risultato);
    $risultato = htmlspecialchars($risultato);
    $this->risultato = $risultato;
  }
  public function set_risultato_array(array $risultato){
    $this->risultato = json_encode($risultato);
  }

	public function set_consultatore_id(int $consultatore_id){
		if (!is_int($consultatore_id) || $consultatore_id < 1){
			// crea eccezione
			throw new Exception( __CLASS__ . ' ' . __FUNCTION__ 
			. " non sembra un numero intero, vale: " 
			. $consultatore_id, 5);
		}
		$this->consultatore_id = $consultatore_id;
	}
	
	/**
	 * @param string datetime yyyy-mm-dd hh:mm:ss
	 */
	public function set_ultima_modifica_record( string $ultima_modifica_record ){
		if (!($this->conn->is_datetime($ultima_modifica_record))){
			throw new Exception(__CLASS__ .' '. __FUNCTION__ 
			. ' no for: '. $ultima_modifica_record 
			. '. Must be a valid datetime format yyyy-mm-dd hh:mm:ss ');
		}
		$this->ultima_modifica_record = $ultima_modifica_record;
	}
	
	/**
	 * @param string datetime yyyy-mm-dd hh:mm:ss
	 */
	public function set_record_cancellabile_dal( string $record_cancellabile_dal ){
		if (!($this->conn->is_datetime($record_cancellabile_dal))){
			throw new Exception(__CLASS__ .' '. __FUNCTION__ . ' no for: '. $record_cancellabile_dal . '. Must be a valid datetime format yyyy-mm-dd hh:mm:ss ');
		}
		$this->record_cancellabile_dal = $record_cancellabile_dal;
	}

  /**
   * CREATE 
   */
  public function aggiungi(array $campi = [] ):array{
    $dbh = $this->conn; // a PDO object thru Database class
    /**
     * record_id
     * ultimo aggiornamento_record
     * record_cancellabile_dal
     *   sono assegnati dal gestore database 
     * consultatore_id
     *   è preso dai _COOKIE
     */
		if (!isset($campi['richiesta'])) {
			$ret = [
				'error'   => true,
				'message' => __CLASS__ . ' ' . __FUNCTION__ 
				. ' Serve il campo richiesta: '
				. $dbh::esponi($campi)
			];
			return $ret;
		}
    if (is_array($campi['richiesta'])){
      $this->set_richiesta_array($campi['richiesta']);
    } elseif (is_string($campi['richiesta'])){
      $this->set_richiesta($campi['richiesta']);      
    } else {
			$ret = [
				'error'   => true,
				'message' => __CLASS__ . ' ' . __FUNCTION__ 
				. ' Serve il campo richiesta: '
				. $dbh::esponi($campi)
			];
			return $ret;
    }
    if (isset($campi['risultato']) && is_array($campi['risultato'])){
      $this->set_risultato_array($campi['risultato']);
    }
    if (isset($campi['risultato']) && is_string($campi['risultato'])){
      $this->set_risultato($campi['risultato']);
    }
    if (isset($_COOKIE['consultatore_id'])){
      $this->set_consultatore_id($_COOKIE['consultatore_id']);
    }
		
		$create = ' INSERT INTO ' . self::nome_tabella 
		. ' (  richiesta,  risultato,  consultatore_id ) VALUES '
		. ' ( :richiesta, :risultato, :consultatore_id ) ';
		if (!$dbh->inTransaction()) { 
			$dbh->beginTransaction(); 
		}
    try {
			$aggiungi=$dbh->prepare($create);
			$aggiungi->bindValue('richiesta',       $this->richiesta);
			$aggiungi->bindValue('risultato',       $this->risultato);
			$aggiungi->bindValue('consultatore_id', $this->consultatore_id, PDO::PARAM_INT );
			$aggiungi->execute();
			$record_id = $dbh->lastInsertID();
			$dbh->commit();
			$ret = [
				'ok'        => true, 
				"record_id" => $record_id,
				"message"   => __CLASS__ . ' ' . __FUNCTION__ 
				. " Inserimento record effettuato, nuovo id: " 
				. $record_id 
			];
			return $ret;

    } catch (\Throwable $th) {
			$dbh->rollBack(); 
			$ret = [
				"record_id" => 0,
				"error"   => true,
				"message" => __CLASS__ . ' ' . __FUNCTION__ 
				. ' ' . $th->getMessage() 
				. " campi: " . serialize($campi)
				. ' istruzione SQL: ' . $create 
			];
			return $ret;      
    } // try catch

  } // aggiungi

  /**
   * @param  array $campi 
   * @return array 'ok' + numero + data[] | 'error' + message 
   */
  public function leggi(array $campi = []) : array{
    $dbh = $this->conn; // a PDO object thru Database class
    if (!isset($campi['query'])){
      $ret = [
        "error"=> true, 
        "message" => __CLASS__ . ' ' . __FUNCTION__ 
        . "Deve essere definita l'istruzione SELECT in ['query']: " 
        . $dbh::esponi($campi)
      ];
      return $ret;
    }
    // dati obbligatori

    $read = $campi['query'];
    if (isset($campi['record_id'])){
      $this->set_record_id($campi['record_id']);
    }
    if (isset($campi['richiesta'])){
      $this->set_richiesta($campi['richiesta']);
    }
    // risultato no
    if (isset($campi['consultatore_id'])){
      $this->set_consultatore_id($campi['consultatore_id']);
    }
    if (isset($campi['ultima_modifica_record'])){
      $this->set_ultima_modifica_record($campi['ultima_modifica_record']);
    }
    if (isset($campi['record_cancellabile_dal'])){
      $this->set_record_cancellabile_dal($campi['record_cancellabile_dal']);
    }

    try {
      $lettura = $dbh->prepare($read);
      if (isset($campi['record_id'])){
        $lettura->bindValue('record_id', $campi['record_id'], PDO::PARAM_INT); 
      }
      if (isset($campi['ricerca'])){
        $lettura->bindValue('ricerca', $campi['ricerca']); 
      }
      // risultato no, sol output 
      if (isset($campi['consultatore_id'])){
        $lettura->bindValue('consultatore_id', $campi['consultatore_id'], PDO::PARAM_INT); 
      }
      if (isset($campi['ultima_modifica_record'])){
        $lettura->bindValue('ultima_modifica_record', $campi['ultima_modifica_record']); 
      }
      if (isset($campi['record_cancellabile_dal'])){
        $lettura->bindValue('record_cancellabile_dal', $campi['record_cancellabile_dal']); 
      }	
      $lettura->execute();

      $conteggio = 0;
      $dati_di_ritorno = [];
      // si potrebbe usare fetchAll(), però... 
      while ($record = $lettura->fetch(PDO::FETCH_ASSOC)) {
        $dati_di_ritorno[] = $record;
        $conteggio++;
      }    
      // Può dare ok anche per un risultato "vuoto"
      $ret = [
        'ok'     => true,
        'numero' => $conteggio,
        'data'   => $dati_di_ritorno 
      ];
      return $ret;

    } catch (\Throwable $th) {
      $ret = [
        'error' => true,
        'message' => __CLASS__ . ' ' . __FUNCTION__ 
        . ' ' . $th->getMessage() 
        . ' istruzione SQL: ' . $read
        . ' campi: ' . $dbh::esponi($campi)
      ];
      return $ret;
    } // try catch
  } // leggi

  /**
   * MODIFICA ma anche SOFT DELETE
   */
  public function modifica(array $campi=[]) : array{
    $dbh = $this->conn; // a PDO object thru Database class

    if (!isset($campi['update'])){
      $ret = [
        "error"=> true, 
        "message" => __CLASS__ . ' ' . __FUNCTION__ 
        . " Aggiornamento record senza UPDATE: " 
        . serialize($campi) 
      ];
      return $ret;
    }
    $update = $campi['update'];
    if (isset($campi['record_id'])){
      $this->set_record_id($campi['record_id']);
    }
    if (isset($campi['richiesta']) && is_array($campi['richiesta'])){
      $this->set_richiesta_array($campi['richiesta']);
    }
    if (isset($campi['richiesta']) && is_string($campi['richiesta'])){
      $this->set_richiesta($campi['richiesta']);
    }
    if (isset($campi['risultato']) && is_array($campi['risultato'])){
      $this->set_risultato_array($campi['risultato']);
    }
    if (isset($campi['risultato']) && is_string($campi['risultato'])){
      $this->set_risultato($campi['risultato']);
    }
    if (isset($campi['consultatore_id'])){
      $this->set_consultatore_id($campi['consultatore_id']);
    }
    if (isset($campi['ultima_modifica_record'])){
      $this->set_ultima_modifica_record($campi['ultima_modifica_record']);
    }
    if (isset($campi['record_cancellabile_dal'])){
      $this->set_record_cancellabile_dal($campi['record_cancellabile_dal']);
    }
    if (!$dbh->inTransaction()) { 
      $dbh->beginTransaction(); 
    }
    try {
      $aggiorna = $dbh->prepare($update);
      if (isset($campi['record_id'])){
        $aggiorna->bindValue('record_id', $campi['record_id'], PDO::PARAM_INT); 
      }
      if (isset($campi['richiesta'])){
        $aggiorna->bindValue('richiesta', $this->get_richiesta() ); 
      }
      if (isset($campi['risultato'])){
        $aggiorna->bindValue('risultato', $this->get_risultato() ); 
      }
      if (isset($campi['consultatore_id'])){
        $aggiorna->bindValue('consultatore_id', $campi['consultatore_id'], PDO::PARAM_INT); 
      }
      if (isset($campi['ultima_modifica_record'])){
        $aggiorna->bindValue('ultima_modifica_record', $campi['ultima_modifica_record']); 
      }
      if (isset($campi['record_cancellabile_dal'])){
        $aggiorna->bindValue('record_cancellabile_dal', $campi['record_cancellabile_dal']); 
      }
  
      $aggiorna->execute();
      $dbh->commit();
      $ret = [ 
        "ok" => true,
        "message" => "Aggiornamento eseguito"
      ];
      return $ret;
  
    } catch (\Throwable $th) {
      $dbh->rollBack(); 
      $ret = [
        "error" => true,
        "message" => __CLASS__ . ' ' . __FUNCTION__ 
        . '<br>' . $th->getMessage() 
        . '<br>campi: ' . $dbh::esponi($campi)
        . '<br>istruzione SQL: ' . $update
      ];
      return $ret;
    } // try catch
  } // modifica

  /**
   * ELIMINA
   * @param  array $campi 
   * @return array 'ok' + message | 'error' + message
   */
  public function elimina(array $campi) : array {
    $dbh = $this->conn; // a PDO object thru Database class
    if (!isset($campi['delete'])){
      $ret = [
        "error"=> true, 
        "message" => __CLASS__ . ' ' . __FUNCTION__ 
        . "Deve essere definita l'istruzione DELETE in ['delete']: " 
        . $dbh::esponi($campi)
      ];
      return $ret;
    }
    // dati obbligatori

    $delete = $campi['delete'];
    if (isset($campi['record_id'])){
      $this->set_record_id($campi['record_id']);
    }
    // ricerca no no...
    // risultato no no...
    if (isset($campi['consultatore_id'])){
      $this->set_consultatore_id($campi['consultatore_id']);
    }
    if (isset($campi['ultima_modifica_record'])){
      $this->set_ultima_modifica_record($campi['ultima_modifica_record']);
    }
    if (isset($campi['record_cancellabile_dal'])){
      $this->set_record_cancellabile_dal($campi['record_cancellabile_dal']);
    }
    if (!$dbh->inTransaction()) { $dbh->beginTransaction(); }
  
    try {
      $cancella = $dbh->prepare($delete);
      if (isset($campi['record_id'])){
        $cancella->bindValue('record_id', $campi['record_id'], PDO::PARAM_INT); 
      }
      if (isset($campi['consultatore_id'])){
        $cancella->bindValue('consultatore_id', $campi['consultatore_id'], PDO::PARAM_INT); 
      }
      if (isset($campi['ultima_modifica_record'])){
        $cancella->bindValue('ultima_modifica_record', $campi['ultima_modifica_record']); 
      }
      if (isset($campi['record_cancellabile_dal'])){
        $cancella->bindValue('record_cancellabile_dal', $campi['record_cancellabile_dal']); 
      }
      $cancella->execute();
      $dbh->commit();
      $ret = [ 
        "ok" => true,
        "message" => "Cancellazione eseguita"
      ];
      return $ret;
  
    } catch (\Throwable $th) {
      $dbh->rollBack(); 
      $ret = [
        "error" => true,
        "message" => __CLASS__ . ' ' . __FUNCTION__ 
        . '<br>' . $th->getMessage() 
        . '<br>campi: ' . $dbh::esponi($campi)
        . '<br>istruzione SQL: ' . $delete
      ];
      return $ret;
    } // try catch
  } // elimina

} // Ricerche