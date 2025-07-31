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

Class Abilitazioni extends DatabaseHandler {
  public $conn;
  
	public  const nome_tabella      = 'abilitazioni_elenco';
  private const abilitazione_zero = '0 nessuna';
  private const abilitazione_massima = '7 amministrazione';
	private const abilitazione_set  = [
		'0 nessuna', 
    '1 lettura', 
    '3 modifica',
		'5 modifica originali', 
    '7 amministrazione'
	];
	private const operazione_set = [
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
    $this->abilitazione   = self::abilitazione_zero;
    $this->operazione     = '';
    $this->ultima_modifica_record  = $dbh->get_datetime_now();
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
  public function get_abilitazione() : string {
    return $this->abilitazione;
  }
  /**
   * @return string 
   */
  public function get_operazione() : string {
    return $this->operazione;
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
  public function set_record_id( int $record_id ) {
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
    // pagine https://archivio.athesis77.it/ no, ko
    $url_pagina = filter_var($url_pagina, FILTER_SANITIZE_URL);
    if ( !str_starts_with($url_pagina, '/')){
      throw new Exception( __CLASS__ . ' ' . __FUNCTION__ 
			. ' non risulta essere una URL: ' . $url_pagina );
    }
		preg_match("/\/[\w\-_]+\.php/ui",$url_pagina,$matches);
    if ( $matches[0] !== $url_pagina ){
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
      $operazione = self::operazione_set[0]; // la prima del set
    }    
    $this->operazione = $operazione;
  }
  
  public function set_abilitazione( string $abilitazione ) {
    // validazione
    if (!in_array($abilitazione, self::abilitazione_set)){
      $abilitazione = self::abilitazione_set[0]; // la prima del set
    }
    
    $this->abilitazione = $abilitazione;
  }
  
  public function set_ultima_modifica_record( string $ultima_modifica_record ) {
    // validazione
    if ( !$this->conn->is_datetime( $ultima_modifica_record )){
      throw new Exception( __CLASS__ . ' ' . __FUNCTION__ . ' deve essere una stringa nel formati datetime: ' . $ultima_modifica_record );
    }
    $this->ultima_modifica_record = $ultima_modifica_record;
  }
  
  public function set_record_cancellabile_dal( string $record_cancellabile_dal ) {
    if ( !$this->conn->is_datetime( $record_cancellabile_dal )){
      throw new Exception( __CLASS__ . ' ' . __FUNCTION__ . ' deve essere una stringa nel formati datetime: ' . $record_cancellabile_dal );
    }
    $this->record_cancellabile_dal = $record_cancellabile_dal;
  }

  // CRUD 
  /** 
   * @param  array campi 
   * @return array ret 
   */
  public function aggiungi( array $campi = []) : array {
    // record_id               viene assegnato automaticamente pertanto non è in elenco 
    // ultima_modifica_record        viene assegnato automaticamente 
    // record_cancellabile_dal viene assegnato automaticamente 
    $dbh = $this->conn; // a PDO object thru Database class
    $create = 'INSERT INTO ' . self::nome_tabella
    . ' (  url_pagina,  operazione,  abilitazione ) VALUES '
    . ' ( :url_pagina, :operazione, :abilitazione ) ';
    
    if (!isset($campi['url_pagina'])){
      $ret = [
        'error' => true,
        'message' => __CLASS__ . ' ' . __FUNCTION__ 
        . "Si è verificato un errore: manca campo url_pagina"
      ];
      return $ret;
    }
    $this->set_url_pagina($campi['url_pagina']);

    if (!isset($campi['operazione'])){
      $campi['operazione'] = '';
    }
    $this->set_operazione($campi['operazione']);

    if (!isset($campi['abilitazione'])){
      $campi['abilitazione'] = self::abilitazione_massima;
    }
    $this->set_abilitazione($campi['abilitazione']);

		if (!$dbh->inTransaction()) { $dbh->beginTransaction(); }
    try {
      $aggiungi = $dbh->prepare($create);
      $aggiungi->bindValue('url_pagina',   $this->get_url_pagina()); 
      $aggiungi->bindValue('operazione',   $this->get_operazione()); 
      $aggiungi->bindValue('abilitazione', $this->get_abilitazione()); 
    	$aggiungi->execute();
    	$record_id_assegnato = $dbh->lastInsertId();
			$dbh->commit();

    } catch( \Throwable $th ){
			$dbh->rollBack();
      $ret = [
        'record_id' => 0,
        'error' => true,
        'message' => __CLASS__ . ' ' . __FUNCTION__  
        . '<br>Si è verificato un errore: ' . $th->getMessage() 
        . '<br>campi: ' . $dbh::esponi($campi)
      ];
      return $ret;
    }

    $ret = [
      'ok' => true, 
      'record_id' => $record_id_assegnato,
      'message' => __CLASS__ . ' ' . __FUNCTION__ 
      . ' Inserimento record effettuato, nuovo id: ' 
      . $record_id_assegnato
    ];
    return $ret;
  } // aggiungi
  
  
  /** 
   * leggi - READ 
   * L'input deve contenere un campo query con l'istruzione sql completa
   * e parametrizzata, oltre ai campi su cui poggiano i parametri
   * solo tabella interessata.
   * 
   * @param  array campi  
   * @return array ret 'ok' + dati | 'error' + 'message'
   */
  public function leggi( array $campi = []) : array {
    $dbh = $this->conn; // a PDO object thru Database class

    if (!isset($campi['query'])){
      $ret = [
        'error'=> true, 
        'message' => 'Lettura record senza QUERY: ' 
        . $dbh::esponi($campi) 
      ];
      return $ret;
    }
    $read = $campi['query'];
    if (isset($campi['record_id'])){
      $this->set_record_id($campi['record_id']); 
    }
    if (isset($campi['url_pagina'])){
      $this->set_url_pagina($campi['url_pagina']); 
    }
    if (isset($campi['abilitazione'])){
      $this->set_abilitazione($campi['abilitazione']); 
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
      if (isset($campi['url_pagina'])){
        $lettura->bindValue('url_pagina', $campi['url_pagina']); 
      }
      if (isset($campi['abilitazione'])){
        $lettura->bindValue('abilitazione', $campi['abilitazione']); 
      }
      if (isset($campi['ultima_modifica_record'])){
        $lettura->bindValue('ultima_modifica_record', $campi['ultima_modifica_record']); 
      }
      if (isset($campi['record_cancellabile_dal'])){
        $lettura->bindValue('record_cancellabile_dal', $campi['record_cancellabile_dal']); 
      }
      $lettura->execute();

    } catch( \Throwable $th ){
      $ret = [
        'error' => true,
        'message' => __CLASS__ . ' ' . __FUNCTION__ 
        . ' § Si è verificato un errore: ' . $th->getMessage() 
        . ' § campi: ' . $dbh::esponi($campi)
        . ' § istruzione SQL: ' . $read
      ];
      return $ret;
    }
    $conteggio = 0;
    $dati_di_ritorno = [];
    while(($record = $lettura->fetch(PDO::FETCH_ASSOC))){
      $dati_di_ritorno[] = $record;
      $conteggio++;
    }
    // Si può dare ok anche per un risultato "vuoto"
    $ret = [
      'ok'     => true,
      'numero' => $conteggio,
      'data'   => $dati_di_ritorno 
    ];
    return $ret;
  } // leggi
  
  
  /**
   * modifica - UPDATE
   * modifica - DELETE soft
   *           
   * ATTENZIONE: La modifica del campo "record_cancellabile_dal" viene 
   *             gestita come cancellazione logica, in attesa di una fase
   *             di scarico e cancellazione fisica.
   *
   * @param  array $campi - uno deve essere "update" e contenere una istruzione sql 
   * @return array $ret 
   */
  public function modifica( array $campi = []) : array {
    $dbh = $this->conn; // a PDO object thru Database class
		if (!isset($campi['update'])){
			$ret = [
				"error"=> true,
				"message" => __CLASS__ . ' ' . __FUNCTION__
				. " Aggiornamento record senza UPDATE: "
				. $dbh::esponi( $campi)
			];
			return $ret;
		}
		$update = $campi['update'];

		if (isset($campi['record_id'])){
			$this->set_record_id($campi['record_id']);
		}
    if (isset($campi['url_pagina'])){
      $this->set_url_pagina( $campi['url_pagina']); 
    }
    if (isset($campi['operazione'])){
      $this->set_url_pagina( $campi['operazione']); 
    }
    if (isset($campi['abilitazione'])){
      $this->set_abilitazione($campi['abilitazione']); 
    }
    if (isset($campi['ultima_modifica_record'])){
      $this->set_ultima_modifica_record( $campi['ultima_modifica_record']); 
    }
    if (isset($campi['record_cancellabile_dal'])){
      $this->set_record_cancellabile_dal( $campi['record_cancellabile_dal']); 
    }
		if (!$dbh->inTransaction()) { $dbh->beginTransaction(); }

    try {
      $aggiorna = $dbh->prepare($update);
			if (isset($campi['record_id'])){
				$aggiorna->bindValue('record_id', $this->get_record_id(), PDO::PARAM_INT);
			}
			if (isset($campi['url_pagina'])){
				$aggiorna->bindValue('url_pagina', $this->get_url_pagina());
			}
			if (isset($campi['operazione'])){
				$aggiorna->bindValue('operazione', $this->get_operazione());
			}
			if (isset($campi['abilitazione'])){
				$aggiorna->bindValue('abilitazione', $this->get_abilitazione());
			}
			if (isset($campi['ultima_modifica_record'])){
				$aggiorna->bindValue('ultima_modifica_record', $this->get_ultima_modifica_record());
			}
			if (isset($campi['record_cancellabile_dal'])){
				$aggiorna->bindValue('record_cancellabile_dal', $this->get_record_cancellabile_dal());
			}
			$aggiorna->execute();
			$dbh->commit();

		} catch( \Throwable $th ){
			$dbh->rollBack();
			$ret = [
				"error" => true,
				"message" => __CLASS__ . ' ' . __FUNCTION__
				. '<br>' . $th->getMessage()
				. '<br>campi: ' . $dbh::esponi( $campi)
				. '<br>istruzione SQL: ' . $update
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
   * elimina - DELETE fisica
   * 
   * Esegue la cancellazione fisica del record, non la cancellazione logica
   * ATTENZIONE: Esiste la gestione del campo "record_cancellabile_dal"
   *             fatta apposta per consentire di "cancellare logicamente"
   *             i record, vedi manuale tecnico amministrativo.
   * @param  array  $campi 
   * @return array  $ret 
   */
  public function elimina( array $campi = []) : array {
    $dbh = $this->conn; // a PDO object thru Database class

		if (!isset($campi['delete'])){
			$ret = [
				'error'   => true,
				'message' => __CLASS__ . ' ' . __FUNCTION__
				. " Deve essere definita l'istruzione DELETE in ['delete']: "
				. $dbh::esponi( $campi)
			];
			return $ret;
		}
		$delete = $campi['delete'];
		if (isset($campi['record_id'])){
			$this->set_record_id($campi['record_id']);
		}
    if (isset($campi['url_pagina'])){
      $this->set_url_pagina( $campi['url_pagina']); 
    }
    if (isset($campi['operazione'])){
      $this->set_url_pagina( $campi['operazione']); 
    }
    if (isset($campi['abilitazione'])){
      $this->set_abilitazione($campi['abilitazione']); 
    }
    if (isset($campi['ultima_modifica_record'])){
      $this->set_ultima_modifica_record( $campi['ultima_modifica_record']); 
    }
    if (isset($campi['record_cancellabile_dal'])){
      $this->set_record_cancellabile_dal( $campi['record_cancellabile_dal']); 
    }
		if (!$dbh->inTransaction()) { $dbh->beginTransaction(); }

    try {
      $cancella = $dbh->prepare($delete);
      if (isset($campi['record_id'])){
        $cancella->bindValue('record_id', $this->get_record_id(), PDO::PARAM_INT); 
      }
      if (isset($campi['url_pagina'])){
        $cancella->bindValue('url_pagina', $this->get_url_pagina()); 
      }
      if (isset($campi['abilitazione'])){
        $cancella->bindValue('abilitazione', $this->get_abilitazione()); 
      }
      if (isset($campi['ultima_modifica_record'])){
        $cancella->bindValue('ultima_modifica_record', $this->get_ultima_modifica_record()); 
      }
      if (isset($campi['record_cancellabile_dal'])){
        $cancella->bindValue('record_cancellabile_dal', $this->get_record_cancellabile_dal()); 
      }
			$cancella->execute();
			$dbh->commit();

		} catch( \Throwable $th ){
			//throw $th;
			$dbh->rollBack();
			$ret = [
				'error' => true,
				'message' => __CLASS__ . ' ' . __FUNCTION__ . ' '
				. 'Si è verificato un errore: ' . $th->getMessage() 
        . ' campi: ' . $dbh::esponi( $campi)
				. ' istruzione SQL: ' . $delete
			];
			return $ret;
		}
		$ret = [
			'ok' => true,
			'message' => 'Cancellazione eseguita'
		];
		return $ret;
  } // elimina

} // class Abilitazioni
