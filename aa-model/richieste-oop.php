<?php 
/**
 * @source richieste-oop.php
 * @author Massimo Rainato <maxrainato@libero.it>
 * 
 * Classe Richieste 
 * 
 * @see https://archivio.athesis77.it/tech/3-archivi-tabelle/3-14-richieste_elenco/ 
 * 
 */
Class Richieste {
	private $conn = false;
	private	$tabella = 'richieste'; 
	private	$oggetto_set = [
		'album',
		'fotografie',
		'video'
	];
	// 
	public $record_id;
	public $record_id_in_consultatori_calendario; 
	public $oggetto_richiesta;
	public $record_id_richiesta;

	public $richiesta_evasa_il; 
	public $ultima_modifica_record;
	public $record_cancellabile_dal; 

	// __construct()
	public function __construct(DatabaseHandler $dbh){
		$this->conn = $dbh;

		$this->record_id							 = 0;	// invalido 
		$this->record_id_in_consultatori_calendario = 0; // invalido 
		$this->oggetto_richiesta			 = ''; // invalido
		$this->record_id_richiesta		 = 0;	// invalido 
		$this->richiesta_evasa_il			= $dbh->get_datetime_forever();
		$this->ultima_modifica_record				= $dbh->get_datetime_now();
		$this->record_cancellabile_dal = $dbh->get_datetime_forever();
	} // __construct

	// GETTER 
	public function get_record_id(){
		return $this->record_id;
	} 

	public function get_record_id_in_consultatori_calendario(){
		return $this->record_id_in_consultatori_calendario;
	} 

	public function get_oggetto_richiesta(){
		return $this->oggetto_richiesta;
	} 

	public function get_record_id_richiesta(){
		return $this->record_id_richiesta;
	} 

	public function get_richiesta_evasa_il(){
		return $this->richiesta_evasa_il;
	} 

	public function get_ultima_modifica_record(){
		return $this->ultima_modifica_record;
	} 

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


	public function set_record_id_in_consultatori_calendario( int $record_id_in_consultatori_calendario ){
		if ($record_id_in_consultatori_calendario < 1){
			throw new Exception(__CLASS__ . ' ' . __FUNCTION__ 
			. ' Must be unsigned integer is : ' . $record_id_in_consultatori_calendario );
		}
		$this->record_id_in_consultatori_calendario = $record_id_in_consultatori_calendario;
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

	public function set_ultima_modifica_record( string $ultima_modifica_record ) {
		// validazione
		if ( !$this->conn->is_datetime( $ultima_modifica_record )){
			throw new Exception( __CLASS__ . ' ' . __FUNCTION__ 
			. ' Deve essere una stringa nel formati datetime e non: ' 
			. $ultima_modifica_record );
		}
		$this->ultima_modifica_record = $ultima_modifica_record;
	}

	public function set_record_cancellabile_dal( string $record_cancellabile_dal ) {
		// validazione
		if ( !$this->conn->is_datetime( $record_cancellabile_dal )){
			throw new Exception( __CLASS__ . ' ' . __FUNCTION__ 
			. ' Deve essere una stringa nel formati datetime e non: ' 
			. $record_cancellabile_dal );
		}
		$this->record_cancellabile_dal = $record_cancellabile_dal;
	}

	// CHECKER 
	function check_record_id_in_consultazioni_calendario(int $record_id){
		if ($record_id < 1 ){
			throw new Exception(__CLASS__ . ' ' . __FUNCTION__ 
			. ' Must be unsigned integer, not that : ' . $record_id );
		}
		// lettura "dritta" senza passare per la classe Consultazioni
		$campi['query'] = 'SELECT 1 FROM consultatori_calendario '
		. 'WHERE record_cancellabile_dal = :record_cancellabile_dal '
		. 'AND record_id = :record_id ';
		try {
			$leggi = $this->conn->prepare($campi['query']);
			$leggi->bindValue('record_cancellabile_dal', $this->conn->get_datetime_forever());
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
		// record_id							 aggiunto in automatico 
		// richiesta_evasa_il			aggiunto in automatico 
		// ultima_modifica_record				aggiunto in automatico 
		// record_cancellabile_dal aggiunto in automatico 
		static $create = 'INSERT INTO ' . $this->tabella 
		. ' (	record_id_in_consultatori_calendario, '
		. '		oggetto_richiesta,	record_id_richiesta ) VALUES '
		. ' ( :record_id_in_consultatori_calendario, '
		. '	 :oggetto_richiesta, :record_id_richiesta ) ';

		$dbh = $this->conn; // a PDO object thru Database class
		if ($dbh === false){
			$ret = [
				"error"=> true, 
				"message" => "Inserimento record senza connessione archivio per: " 
				. $this->tabella 
			];
			return $ret;
		}

		if (!isset($campi['record_id_in_consultatori_calendario']) || 
		!isset($campi['oggetto_richiesta']) || 
		!isset($campi['record_id_richiesta']) ){
			$ret = [
				"error"=> true,
				"message" => "Servono 3 parametri 3. " 
				. $this->tabella 
			];
			return $ret;
		}
		$this->set_record_id_in_consultatori_calendario($campi['record_id_in_consultatori_calendario']);
		$this->set_oggetto_richiesta($campi['oggetto_richiesta']);
		$this->set_record_id_richiesta($campi['record_id_richiesta']);
		try {
			$aggiungi = $dbh->prepare($create);
			$aggiungi->bindValue("record_id_in_consultatori_calendario",	 $this->record_id_in_consultatori_calendario); 
			$aggiungi->bindValue("oggetto_richiesta",	 $this->oggetto_richiesta); 
			$aggiungi->bindValue("record_id_richiesta", $this->record_id_richiesta); 
			$aggiungi->execute();
			$record_id_assegnato = $this->conn->lastInsertId();
		} catch (\Throwable $th) {
			//throw $th;
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
			'ok' => true,
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
	 * query sono presenti come :nomecampo 
	 * @return array ret 
	 */
	public function leggi( array $campi ) : array {
		$dbh = $this->conn; // a PDO object thru Database class
		if ($dbh === false){
			$ret = [
				"error"=> true,
				"message" => __CLASS__ . ' ' . __FUNCTION__ 
				. "Lettura record senza connessione archivio per: " 
				. $this->tabella
			];
			return $ret;
		}
		if (!isset($campi["query"])){
			$ret = [
				"error"=> true,
				"message" => __CLASS__ . ' ' . __FUNCTION__ 
				. ' Lettura record senza QUERY. '
				. ' campi: ' . serialize($campi) 
			];
			return $ret;
		}
		$read = $campi["query"];
		if (isset($campi['record_id'])){
			$this->set_record_id($campi['record_id']);
		}
		if (isset($campi['record_id_in_consultatori_calendario'])){
			$this->set_record_id_in_consultatori_calendario($campi['record_id_in_consultatori_calendario']);
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
			if (isset($campi['record_id_in_consultatori_calendario'])){
				$lettura->bindValue('record_id_in_consultatori_calendario', $this->record_id_in_consultatori_calendario, PDO::PARAM_INT );
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
			if (isset($campi['ultima_modifica_record'])){
				$lettura->bindValue('ultima_modifica_record', $this->ultima_modifica_record );
			}
			if (isset($campi['record_cancellabile_dal'])){
				$lettura->bindValue('record_cancellabile_dal', $this->record_cancellabile_dal );
			}

		} catch (\Throwable $th) {
			//throw $th;
			$ret = [
				"error" => true,
				"message" => __CLASS__ . ' ' . __FUNCTION__ 
				. ' ' . $th->getMessage() 
				. ' campi: ' . serialize($campi)
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
		$dbh = $this->conn; // a PDO object thru Database class
		if ($dbh === false){
			$ret = [
				"error"=> true, 
				"message" => __CLASS__ . ' ' . __FUNCTION__ 
				. ' Lettura record senza connessione archivio per: ' 
				. $this->tabella 
			];
			return $ret;
		}
		if ( !isset($campi['update'])){
			$ret = [
				"error"=> true, 
				"message" => __CLASS__ . ' ' . __FUNCTION__ 
				. ' Serve un campo update. ' 
				. $this->tabella 
			];
			return $ret;
		}
		$update = $campi['update'];
		if (isset($campi['record_id'])){
			$this->set_record_id($campi['record_id']);
		}
		if (isset($campi['record_id_in_consultatori_calendario'])){
			$this->set_record_id_in_consultatori_calendario($campi['record_id_in_consultatori_calendario']);
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
		if (isset($campi['ultima_modifica_record'])){
			$this->set_ultima_modifica_record($campi['ultima_modifica_record']);
		}
		if (isset($campi['record_cancellabile_dal'])){
			$this->set_record_cancellabile_dal($campi['record_cancellabile_dal']);
		}
		try {
			$aggiorna = $this->conn->prepare($update);
			if (isset($campi['record_id'])){
				$aggiorna->bindValue('record_id', $this->record_id, PDO::PARAM_INT);
			}
			if (isset($campi['record_id_in_consultatori_calendario'])){
				$aggiorna->bindValue('record_id_in_consultatori_calendario', $this->record_id_in_consultatori_calendario, PDO::PARAM_INT);
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
			if (isset($campi['ultima_modifica_record'])){
				$aggiorna->bindValue('ultima_modifica_record', $this->ultima_modifica_record);
			}
			if (isset($campi['record_cancellabile_dal'])){
				$aggiorna->bindValue('record_cancellabile_dal', $this->record_cancellabile_dal);
			}
			$aggiorna->execute();

		} catch (\Throwable $th) {
			//throw $th;
			$ret = [
        "error" => true,
        "message" => __CLASS__ . ' ' . __FUNCTION__ 
				. ' ' . $th->getMessage() 
				. ' campi: ' . serialize($campi)
        . ' istruzione SQL: ' . $update
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
		$dbh = $this->conn;
		if ($dbh == false){
			$ret = [
				'error' => true,
				'message' => __CLASS__ . ' ' . __FUNCTION__ 
				. ' Non è presente la connessione per la tabella '
				. $this->tabella
			];
			return $ret;
		}
		if ( !isset($campi['delete'])){
			$ret = [
				'error' => true,
				'message' => __CLASS__ . ' ' . __FUNCTION__ 
				. ' Non è presente il campo delete '
				. ' campi: ' . serialize($campi)
			];
			return $ret;
		}
		$delete = $campi['delete'];

		if (isset($campi['record_id'])){
			$this->set_record_id($campi['record_id']);
		}
		if (isset($campi['record_id_in_consultatori_calendario'])){
			$this->set_record_id_in_consultatori_calendario($campi['record_id_in_consultatori_calendario']);
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
		if (isset($campi['ultima_modifica_record'])){
			$this->set_ultima_modifica_record($campi['ultima_modifica_record']);
		}
		if (isset($campi['record_cancellabile_dal'])){
			$this->set_record_cancellabile_dal($campi['record_cancellabile_dal']);
		}

		try {
			//code...
			$cancella = $dbh->prepare($delete);
			if (isset($campi['record_id'])){
				$cancella->bindValue('record_id', $this->record_id, PDO::PARAM_INT);
			}
			if (isset($campi['record_id_in_consultatori_calendario'])){
				$cancella->bindValue('record_id_in_consultatori_calendario', $this->record_id_in_consultatori_calendario, PDO::PARAM_INT);
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
			if (isset($campi['ultima_modifica_record'])){
				$cancella->bindValue('ultima_modifica_record', $this->ultima_modifica_record);
			}
			if (isset($campi['record_cancellabile_dal'])){
				$cancella->bindValue('record_cancellabile_dal', $this->record_cancellabile_dal);
			}
			$cancella->execute();

		} catch (\Throwable $th) {
			//throw $th;
			$ret = [
        "error" => true,
        "message" => __CLASS__ . ' ' . __FUNCTION__ 
				. ' ' . $th->getMessage() 
				. ' campi: ' . serialize($campi)
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

} // Class Richieste