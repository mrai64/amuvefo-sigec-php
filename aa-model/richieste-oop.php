<?php 
/**
 * @source /aa-model/richieste-oop.php
 * @author Massimo Rainato <maxrainato@libero.it>
 * 
 * Classe Richieste 
 * 
 * @see https://archivio.athesis77.it/tech/3-archivi-tabelle/3-14-richieste_elenco/ 
 * 
 */
Class Richieste extends DatabaseHandler {
	public $conn;

	public const nome_tabella = 'richieste';
	private	$oggetto_set = [
		'album',
		'fotografie',
		'video'
	];
	// 
	public $record_id;             // uso interno 
	public $record_id_richiedente; // chiave esterna calendario_consultatori.record_id
	public $oggetto_richiesta;     // ENUM 
	public $record_id_richiesta;   // chiave esterna 
	public $richiesta_evasa_il;    // datetime aaaa-mm-gg hh:mm:ss 
	public $record_id_amministratore; // chiave esterna calendario_consultatori.record_id
	public $motivazione;              // testo libero
	public $ultima_modifica_record;   // datetime aaaa-mm-gg hh:mm:ss
	public $record_cancellabile_dal;  // datetime aaaa-mm-gg hh:mm:ss

	// __construct()
	public function __construct(DatabaseHandler $dbh){
		$this->conn = $dbh;

		$this->record_id							 = 0;	 // invalido 
		$this->record_id_richiedente   = 0;  // invalido 
		$this->oggetto_richiesta			 = ''; // invalido
		$this->record_id_richiesta		 = 0;	 // invalido 
		$this->richiesta_evasa_il			 = $dbh->get_datetime_forever();
		$this->record_id_amministratore= 0;  //
		$this->motivazione             = '';
		$this->ultima_modifica_record  = $dbh->get_datetime_now();
		$this->record_cancellabile_dal = $dbh->get_datetime_forever();
	} // __construct

	// GETTER 
	public function get_record_id() : int {
		return $this->record_id;
	} 

	public function get_record_id_richiedente() : int {
		return $this->record_id_richiedente;
	} 

	public function get_oggetto_richiesta() : string {
		return $this->oggetto_richiesta;
	} 

	public function get_record_id_richiesta() : int {
		return $this->record_id_richiesta;
	} 

	public function get_richiesta_evasa_il() : string {
		return $this->richiesta_evasa_il;
	} 

	public function get_record_id_amministratore() : int {
		return $this->record_id_amministratore;
	}

	public function get_motivazione() : string {
		return $this->motivazione;
	}

	public function get_ultima_modifica_record() : string {
		return $this->ultima_modifica_record;
	} 

	public function get_record_cancellabile_dal() : string {
		return $this->record_cancellabile_dal;
	} 



	// SETTER e VERIFICA
	public function set_record_id( int $record_id ){
		if ($record_id < 1){
			throw new Exception(__CLASS__ . ' ' . __FUNCTION__ 
			. ' Must be unsigned integer is : ' . $record_id );
		}
		$this->record_id = $record_id;
	}

	public function set_record_id_richiedente( int $record_id_richiedente ){
		if ($record_id_richiedente < 1){
			throw new Exception(__CLASS__ . ' ' . __FUNCTION__ 
			. ' Must be unsigned integer is : ' . $record_id_richiedente );
		}
		$this->record_id_richiedente = $record_id_richiedente;
	}

	public function set_oggetto_richiesta(string $oggetto_richiesta){
		if ( !in_array( $oggetto_richiesta, $this->oggetto_set)){
			throw new Exception(__CLASS__ . ' ' . __FUNCTION__ 
			. " Not in set : " . $oggetto_richiesta );
		}
		$this->oggetto_richiesta = $oggetto_richiesta;
	}

	public function set_record_id_richiesta( int $record_id_richiesta ){
		if ($record_id_richiesta < 1){
			throw new Exception(__CLASS__ . ' ' . __FUNCTION__ 
			. ' Must be unsigned integer is : ' . $record_id_richiesta );
		}
		if (!$this->check_record_id_in_consultazioni_calendario($record_id_richiesta)){
			throw new Exception(__CLASS__ . ' ' . __FUNCTION__ 
			. ' Valid but missing in reference table : ' 
			. $record_id_richiesta );
		}
		$this->record_id_richiesta = $record_id_richiesta;
	}

	public function set_richiesta_evasa_il( string $richiesta_evasa_il ) {
		// validazione
		if ( !$this->conn->is_datetime( $richiesta_evasa_il )){
			throw new Exception( __CLASS__ . ' ' . __FUNCTION__ 
			. ' Deve essere una stringa nel formati datetime e non: ' 
			. $richiesta_evasa_il );
		}
		$this->richiesta_evasa_il = $richiesta_evasa_il;
	}

	public function set_record_id_amministratore( int $record_id_amministratore ){
		if ($record_id_amministratore < 1){
			throw new Exception(__CLASS__ . ' ' . __FUNCTION__ 
			. ' Must be unsigned integer is : ' . $record_id_amministratore );
		}
		$this->record_id_amministratore = $record_id_amministratore;
	}

	public function set_motivazione( string $motivazione){
		$motivazione = htmlspecialchars($motivazione);
		$motivazione = trim($motivazione);
		$motivazione = mb_substr($motivazione, 0, 1500);
		$this->motivazione = $motivazione; 
	}

	public function set_ultima_modifica_record( string $ultima_modifica_record ) {
		$dbh = $this->conn;
		// validazione
		if ( !$dbh->is_datetime( $ultima_modifica_record )){
			throw new Exception( __CLASS__ . ' ' . __FUNCTION__ 
			. ' Deve essere una stringa nel formati datetime e non: ' 
			. $ultima_modifica_record );
		}
		$this->ultima_modifica_record = $ultima_modifica_record;
	}

	public function set_record_cancellabile_dal( string $record_cancellabile_dal ) {
		$dbh = $this->conn;
		// validazione
		if ( !$dbh->is_datetime( $record_cancellabile_dal )){
			throw new Exception( __CLASS__ . ' ' . __FUNCTION__ 
			. ' Deve essere una stringa nel formati datetime e non: ' 
			. $record_cancellabile_dal );
		}
		$this->record_cancellabile_dal = $record_cancellabile_dal;
	}

	// CHECKER 
	function check_record_id_in_consultazioni_calendario(int $record_id){
		$dbh = $this->conn;
		if ($record_id < 1 ){
			throw new Exception(__CLASS__ . ' ' . __FUNCTION__ 
			. ' Must be unsigned integer, not that : ' . $record_id );
		}
		// lettura "dritta" senza passare per la classe Consultazioni
		$campi['query'] = 'SELECT 1 FROM consultatori_calendario '
		. 'WHERE record_cancellabile_dal = :record_cancellabile_dal '
		. 'AND record_id = :record_id ';
		try {
			$leggi = $dbh->prepare($campi['query']);
			$leggi->bindValue('record_cancellabile_dal', $dbh->get_datetime_forever());
			$leggi->bindValue('record_id', $record_id, PDO::PARAM_INT);
			return $leggi->execute();

		} catch (\Throwable $th) {
			throw new Exception(__CLASS__ .' '. __FUNCTION__ 
			. ' Check ko per: '. $record_id );
		}
	} // check_record_id_in_consultazioni_calendario

	// CRUD 

	/**
	 * CREATE aggiungi 
	 * 
	 * Servono i campi da inserire in tabella 
	 * @param	array $campi 
	 * @return array $ret ok + record_id | error + message
	 */
	public function aggiungi( array $campi)	:array {
		// sono aggiunti in automatico oppure DEVONO essere aggiunti in seguito  
		// . record_id 
		// . richiesta_evasa_il 
		// . record_id_amministratore 
		// . motivazione 
		// . ultima_modifica_record 
		// . record cancellabile_dal 
		$create = 'INSERT INTO ' . self::nome_tabella 
		. ' (	record_id_richiedente, '
		.   ' oggetto_richiesta,	record_id_richiesta, '
		.   ' motivazione ) VALUES '
		. ' (:record_id_richiedente, '
		.  ' :oggetto_richiesta, :record_id_richiesta, '
		.  ' :motivazione ) ';

		$dbh = $this->conn; // a PDO object thru Database class
		if ($dbh === false){
			$ret = [
				'error'   => true, 
				'message' => __CLASS__ .' '. __FUNCTION__ 
				. " Per aggiungere il record serve la connessione all'archivio." 
			];
			return $ret;
		}

		if ( !isset($campi['record_id_richiedente']) || 
		     !isset($campi['oggetto_richiesta'])     || 
		     !isset($campi['record_id_richiesta'])   ){
			$ret = [
				'error'   => true,
				'message' => __CLASS__ .' '. __FUNCTION__ 
				. " Servono 3 parametri 3. " 
				. self::nome_tabella 
			];
			return $ret;
		}
		// verifiche 
		$this->set_record_id_richiedente($campi['record_id_richiedente']);
		$this->set_oggetto_richiesta($campi['oggetto_richiesta']);
		$this->set_record_id_richiesta($campi['record_id_richiesta']);
		// azione
		if (!$dbh->inTransaction()) { $dbh->beginTransaction(); }
		try {
			$aggiungi = $dbh->prepare($create);
			$aggiungi->bindValue('record_id_richiedente',	 $this->record_id_richiedente); 
			$aggiungi->bindValue('oggetto_richiesta',	     $this->oggetto_richiesta); 
			$aggiungi->bindValue('record_id_richiesta',    $this->record_id_richiesta); 
			$aggiungi->bindValue('motivazione',    'Richiesta inoltrata'); 
			$aggiungi->execute();
			$record_id_assegnato = $dbh->lastInsertId();
			$dbh->commit();

		} catch (\Throwable $th) {
			$dbh->rollBack();
			$ret = [
				'record_id' => 0,
				'error'     => true,
				'message'   => __CLASS__ . ' ' . __FUNCTION__ 
				. '<br>' . $th->getMessage() 
				. '<br>Campi: ' . $dbh::esponi($campi)
				. '<br>istruzione SQL: ' . $create
			];
			return $ret;
		}
		$ret = [
			'ok'        => true,
			'record_id' => $record_id_assegnato
		];
		return $ret;
	} // aggiungi


	/**
	 * READ leggi 
	 * Non è prevista la paginazione
	 * 
	 * @param	array campi 
	 * Dev'essere presente un campo query e tutti i campi che nella
	 * query sono presenti come :nome_campo 
	 * @return array ret 
	 */
	public function leggi( array $campi ) : array {
		// necessari 
		$dbh = $this->conn; // a PDO object thru Database class

		if (!isset($campi["query"])){
			$ret = [
				"error"=> true,
				"message" => __CLASS__ . ' ' . __FUNCTION__ 
				. ' Lettura record senza QUERY. '
				. ' campi: ' . $dbh::esponi($campi) 
			];
			return $ret;
		}
		$read = $campi["query"];
		// validazioni 
		if (isset($campi['record_id'])){
			$this->set_record_id($campi['record_id']);
		}
		if (isset($campi['record_id_richiedente'])){
			$this->set_record_id_richiedente($campi['record_id_richiedente']);
		}
		if (isset($campi['oggetto_richiesta'])){
			$this->set_oggetto_richiesta($campi['oggetto_richiesta']);
		}
		if (isset($campi['record_id_richiesta'])){
			$this->set_record_id_richiesta($campi['record_id_richiesta']);
		}
		if (isset($campi['richiesta_evasa_il'])){
			$this->set_richiesta_evasa_il($campi['richiesta_evasa_il']);
		}
		if (isset($campi['record_id_amministratore'])){
			$this->set_record_id_amministratore($campi['record_id_amministratore']);
		}
		if (isset($campi['motivazione'])){
			$this->set_motivazione($campi['motivazione']);
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
				$lettura->bindValue('record_id', $this->record_id, PDO::PARAM_INT );
			}
			if (isset($campi['record_id_richiedente'])){
				$lettura->bindValue('record_id_richiedente', $this->record_id_richiedente, PDO::PARAM_INT );
			}
			if (isset($campi['oggetto_richiesta'])){
				$lettura->bindValue('oggetto_richiesta', $this->oggetto_richiesta );
			}
			if (isset($campi['record_id_richiesta'])){
				$lettura->bindValue('record_id_richiesta', $this->record_id_richiesta, PDO::PARAM_INT );
			}
			if (isset($campi['richiesta_evasa_il'])){
				$lettura->bindValue('richiesta_evasa_il', $this->richiesta_evasa_il );
			}
			if (isset($campi['record_id_amministratore'])){
				$lettura->bindValue('record_id_amministratore', $this->record_id_amministratore, PDO::PARAM_INT );
			}
			if (isset($campi['motivazione'])){
				$lettura->bindValue('motivazione', $this->motivazione );
			}
			if (isset($campi['ultima_modifica_record'])){
				$lettura->bindValue('ultima_modifica_record', $this->ultima_modifica_record );
			}
			if (isset($campi['record_cancellabile_dal'])){
				$lettura->bindValue('record_cancellabile_dal', $this->record_cancellabile_dal );
			}
			$lettura->execute();

		} catch (\Throwable $th) {
			//throw $th;
			$ret = [
				'error'   => true,
				'message' => __CLASS__ . ' ' . __FUNCTION__ 
				. '<br>' . $th->getMessage() 
				. '<br>Campi: ' . $dbh::esponi($campi)
				. '<br>Istruzione SQL: ' . $read
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
			'ok'		 => true,
			'numero' => $conteggio,
			'data'	 => $dati_di_ritorno 
		];
		return $ret;
	} // leggi


	/**
	 * UPDATE modifica 
	 * ATTENZIONE: La modifica del campo "record_cancellabile_dal" viene 
	 *						 gestita come cancellazione logica, in attesa di una fase
	 *						 di scarico e cancellazione fisica.
	 *
	 * @param	 array $campi - uno deve essere "update" e contenere una istruzione sql 
	 * @return array $ret 
	 */
	public function modifica(array $campi) : array {
		// necessari 
		$dbh = $this->conn; // a PDO object thru Database class

		if ( !isset($campi['update'])){
			$ret = [
				"error"=> true, 
				"message" => __CLASS__ . ' ' . __FUNCTION__ 
				. ' Serve un campo update. ' 
				. self::nome_tabella 
			];
			return $ret;
		}
		$update = $campi['update'];
		// verifiche 
		if (isset($campi['record_id'])){
			$this->set_record_id($campi['record_id']);
		}
		if (isset($campi['record_id_richiedente'])){
			$this->set_record_id_richiedente($campi['record_id_richiedente']);
		}
		if (isset($campi['oggetto_richiesta'])){
			$this->set_oggetto_richiesta($campi['oggetto_richiesta']);
		}
		if (isset($campi['record_id_richiesta'])){
			$this->set_record_id_richiesta($campi['record_id_richiesta']);
		}
		if (isset($campi['richiesta_evasa_il'])){
			$this->set_richiesta_evasa_il($campi['richiesta_evasa_il']);
		}
		if (isset($campi['record_id_amministratore'])){
			$this->set_record_id_amministratore($campi['record_id_amministratore']);
		}
		if (isset($campi['motivazione'])){
			$this->set_motivazione($campi['motivazione']);
		}
		if (isset($campi['ultima_modifica_record'])){
			$this->set_ultima_modifica_record($campi['ultima_modifica_record']);
		}
		if (isset($campi['record_cancellabile_dal'])){
			$this->set_record_cancellabile_dal($campi['record_cancellabile_dal']);
		}
		// azione
		if (!$dbh->inTransaction()) { $dbh->beginTransaction(); }
		try {
			$aggiorna = $dbh->prepare($update);
			if (isset($campi['record_id'])){
				$aggiorna->bindValue('record_id', $this->record_id, PDO::PARAM_INT);
			}
			if (isset($campi['record_id_richiedente'])){
				$aggiorna->bindValue('record_id_richiedente', $this->record_id_richiedente, PDO::PARAM_INT);
			}
			if (isset($campi['oggetto_richiesta'])){
				$aggiorna->bindValue('oggetto_richiesta', $this->oggetto_richiesta);
			}
			if (isset($campi['record_id_richiesta'])){
				$aggiorna->bindValue('record_id_richiesta', $this->record_id_richiesta, PDO::PARAM_INT);
			}
			if (isset($campi['richiesta_evasa_il'])){
				$aggiorna->bindValue('richiesta_evasa_il', $this->richiesta_evasa_il);
			}
			if (isset($campi['record_id_amministratore'])){
				$aggiorna->bindValue('record_id_amministratore', $this->record_id_amministratore, PDO::PARAM_INT);
			}
			if (isset($campi['motivazione'])){
				$aggiorna->bindValue('motivazione', $this->motivazione);
			}
			if (isset($campi['ultima_modifica_record'])){
				$aggiorna->bindValue('ultima_modifica_record', $this->ultima_modifica_record);
			}
			if (isset($campi['record_cancellabile_dal'])){
				$aggiorna->bindValue('record_cancellabile_dal', $this->record_cancellabile_dal);
			}
			$aggiorna->execute();
			$dbh->commit();

		} catch (\Throwable $th) {
			$dbh->rollBack();
			//throw $th;
			$ret = [
        'error' => true,
        'message' => __CLASS__ . ' ' . __FUNCTION__ 
				. '<br>' . $th->getMessage() 
				. '<br>Campi: ' . $dbh::esponi($campi)
        . '<br>Istruzione SQL: ' . $update
      ];
      return $ret;
		}
		$ret = [
			'ok' => true, 
			'message' => 'Aggiornamento eseguito'
		];
		return $ret;
	} // modifica


  /**
   * DELETE elimina 
	 * 
	 * Esegue la cancellazione fisica del record, non la cancellazione logica
   * ATTENZIONE: Esiste la gestione del campo "record_cancellabile_dal"
   *             fatta apposta per consentire di "cancellare logicamente"
   *             i record, vedi manuale tecnico amministrativo.
	 * 
	 * @param  array  $campi 
   * @return array  $ret 
   */
	public function elimina( array $campi ){
		// necessari
		$dbh = $this->conn;

		if ( !isset($campi['delete'])){
			$ret = [
				'error' => true,
				'message' => __CLASS__ . ' ' . __FUNCTION__ 
				. ' Non è presente il campo delete '
				. ' campi: ' . $dbh::esponi($campi)
			];
			return $ret;
		}
		// verifiche 
		$delete = $campi['delete'];
		if (isset($campi['record_id'])){
			$this->set_record_id($campi['record_id']);
		}
		if (isset($campi['record_id_richiedente'])){
			$this->set_record_id_richiedente($campi['record_id_richiedente']);
		}
		if (isset($campi['oggetto_richiesta'])){
			$this->set_oggetto_richiesta($campi['oggetto_richiesta']);
		}
		if (isset($campi['record_id_richiesta'])){
			$this->set_record_id_richiesta($campi['record_id_richiesta']);
		}
		if (isset($campi['richiesta_evasa_il'])){
			$this->set_richiesta_evasa_il($campi['richiesta_evasa_il']);
		}
		if (isset($campi['record_id_amministratore'])){
			$this->set_record_id_amministratore($campi['record_id_amministratore']);
		}
		// motivazione no
		if (isset($campi['ultima_modifica_record'])){
			$this->set_ultima_modifica_record($campi['ultima_modifica_record']);
		}
		if (isset($campi['record_cancellabile_dal'])){
			$this->set_record_cancellabile_dal($campi['record_cancellabile_dal']);
		}
		// azione
		if (!$dbh->inTransaction()) { $dbh->beginTransaction(); }
		try {
			//code...
			$cancella = $dbh->prepare($delete);
			if (isset($campi['record_id'])){
				$cancella->bindValue('record_id', $this->record_id, PDO::PARAM_INT);
			}
			if (isset($campi['record_id_richiedente'])){
				$cancella->bindValue('record_id_richiedente', $this->record_id_richiedente, PDO::PARAM_INT);
			}
			if (isset($campi['oggetto_richiesta'])){
				$cancella->bindValue('oggetto_richiesta', $this->oggetto_richiesta);
			}
			if (isset($campi['record_id_richiesta'])){
				$cancella->bindValue('record_id_richiesta', $this->record_id_richiesta, PDO::PARAM_INT);
			}
			if (isset($campi['richiesta_evasa_il'])){
				$cancella->bindValue('richiesta_evasa_il', $this->richiesta_evasa_il);
			}
			if (isset($campi['record_id_amministratore'])){
				$cancella->bindValue('record_id_amministratore', $this->record_id_amministratore, PDO::PARAM_INT);
			}
			// motivazione no 
			if (isset($campi['ultima_modifica_record'])){
				$cancella->bindValue('ultima_modifica_record', $this->ultima_modifica_record);
			}
			if (isset($campi['record_cancellabile_dal'])){
				$cancella->bindValue('record_cancellabile_dal', $this->record_cancellabile_dal);
			}
			$cancella->execute();
			$dbh->commit();

		} catch (\Throwable $th) {
			//throw $th;
			$ret = [
        'error'   => true,
        'message' => __CLASS__ . ' ' . __FUNCTION__ 
				. '<br>' . $th->getMessage() 
				. '<br>Campi: ' . $dbh::esponi($campi)
        . '<br>Istruzione SQL: ' . $delete
      ];
      return $ret;
		}
		$ret = [
			"ok" => true,
			"message" => "Cancellazione eseguita"
		];
		return $ret;
	} // elimina


	public function get_richiesta_from_id(int $richiesta_id): array{
		// dati obbligatori 
		$dbh = $this->conn; // a PDO object thru Database class

		// validazione
		$this->set_record_id($richiesta_id);

		$read = 'SELECT * FROM ' . self::nome_tabella
		. ' WHERE record_cancellabile_dal = :record_cancellabile_dal  '
		. ' AND record_id = :record_id '
		. ' LIMIT 1 ';
		try {
			$lettura=$dbh->prepare($read);
			$lettura->bindValue('record_cancellabile_dal', $dbh->get_datetime_forever() ); 
			$lettura->bindValue('record_id',               $richiesta_id, PDO::PARAM_INT); 
			$lettura->execute();

		} catch( \Throwable $th ){
			$ret = [
				'error'   => true,
				'message' => __CLASS__ . ' ' . __FUNCTION__ 
				. '<br>' . $th->getMessage() 
				. '<br>richiesta_id: ' . $richiesta_id
				. '<br>istruzione SQL: ' . $read
			];
			return $ret;
		}
		$numero = 0;
		$dati_di_ritorno = [];
		while ($record = $lettura->fetch(PDO::FETCH_ASSOC)) {
			$dati_di_ritorno[] = $record;
			$numero++;
		}    
		$ret = [
			'ok'     => true,
			'numero' => $numero,
			'data'   => $dati_di_ritorno 
		];
		return $ret;
	} // get_richiesta_from_id

} // Class Richieste