<?php 
/**
 *	@source /aa-model/fotografie-oop.php
 *  @author Massimo Rainato <maxrainato@libero.it>
 * 
 *	Classe Fotografie
 *
 *	dipendenze: DatabaseHandler connessione archivio PDO 
 *	dipendenze: Album 
 *	dipendenze: Descrizioni tabella di lunghi testi TODO
 *	dipendenze: Deposito 
 *	dipendenze: FotografieDettagli 
 *
 * @see https://archivio.athesis77.it/tech/3-archivi-tabelle/fotografie/
 * @see https://archivio.athesis77.it/tech/3-archivi-tabelle/fotografie-dettagli/
 *
 * metodi
 * GETTER
 * SETTER
 * CHECKER 
 * CRUD
 *   aggiungi CREATE
 *   leggi    READ 
 *   modifica UPDATE
 *   elimina  DELETE 
 * OTHERS
 * 
 * 
 */
Class Fotografie extends DatabaseHandler {
	public $conn;

	public const nome_tabella  = 'fotografie';
	public const stato_da_fare    = '0 da fare';
	public const stato_in_corso   = '1 in corso';
	public const stato_completati = '2 completati';
	public const stato_lavori_validi = [
		self::stato_da_fare,
		self::stato_in_corso,
		self::stato_completati
	];

	//
	public $record_id; //                     bigint(20) unsigned AUTO+ PRIMARY 
	public $titolo_fotografia; //             varchar(250)
	public $disco; //                         char(12)
	public $percorso_completo; //             varchar(1500)
	public $record_id_in_album; //            bigint(20) unsigned external key su deposito
	public $record_id_in_deposito; //  bigint(20) unsigned external key su deposito
	public $stato_lavori; //                  enum 
	public $ultima_modifica_record; //              datetime DEF CURRENT TIME
	public $record_cancellabile_dal; //       datetime DEF '9999-12-31 23:59:59'
	
	public function __construct(DatabaseHandler $dbh){
		$this->conn = $dbh;
		
		$this->record_id                = 0; // invalido
		$this->titolo_fotografia        = ''; // invalido
		$this->disco                    = '';
		$this->percorso_completo        = '';
		$this->record_id_in_album       = 0; // invalido 
		$this->record_id_in_deposito = 0; // invalido 
		$this->stato_lavori             = self::stato_da_fare;
		$this->ultima_modifica_record   = $dbh->get_datetime_now();
		$this->record_cancellabile_dal  = $dbh->get_datetime_forever();
	} // __construct
	
	// GETTER 
	/**
	 * @return int unsigned
	 */
	public function get_record_id() : int {
		return $this->record_id;
	}
	
	public function get_titolo_fotografia() : string {
		return $this->titolo_fotografia;
	}
	
	public function get_disco() : string {
		return $this->disco;
	}
	
	public function get_percorso_completo() : string {
		return $this->percorso_completo;
	}
	
	/**
	 * @return int unsigned 
	 */
	public function get_record_id_in_album() : int {
		return $this->record_id_in_album;
	}
	/**
	 * @return int unsigned 
	 */
	public function get_record_id_in_deposito() : int {
		return $this->record_id_in_deposito;
	}
	public function get_stato_lavori() : string {
		return $this->stato_lavori;
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
	


	// SETTER 
	public function set_record_id( int $record_id ){
		if ($record_id < 1){
			throw new Exception(__CLASS__ . ' ' . __FUNCTION__ 
			. ' Must be unsigned integer, is : ' . $record_id );
		}
		$this->record_id = $record_id;
	}
		
	public function set_titolo_fotografia( string $titolo_fotografia ) {
		// ritaglio a misura
		$titolo_fotografia = htmlspecialchars(strip_tags($titolo_fotografia));
		$titolo_fotografia = mb_substr($titolo_fotografia, 0, 250);
		$this->titolo_fotografia = $titolo_fotografia;
	}
	
	public function set_disco( string $disco ){
		// ritaglio a misura 
		$disco = (strip_tags($disco));
		$disco = mb_substr($disco, 0, 12);
		$this->disco = $disco;
	}
	
	public function set_percorso_completo( string $percorso_completo ){
		// ritaglio a misura 
		$percorso_completo = (strip_tags($percorso_completo));
		if ($percorso_completo[0] != '/') { 
			$percorso_completo = '/'.$percorso_completo; 
		}
		$percorso_completo = mb_substr($percorso_completo, 0, 1500);
		$this->percorso_completo = $percorso_completo;
	}
	
	public function set_record_id_in_album( int $record_id_in_album){
		if ($record_id_in_album < 1){
			throw new Exception(__CLASS__ .' '. __FUNCTION__ 
			. ' Must be unsigned integer, is: ' . $record_id_in_album);
		}
		$this->record_id_in_album = $record_id_in_album;
	}

	public function set_record_id_in_deposito( int $record_id_in_deposito){
		if ($record_id_in_deposito < 1){
			throw new Exception(__CLASS__ .' '. __FUNCTION__ 
			. ' Must be unsigned integer, is: ' . $record_id_in_deposito);
		}
		$this->record_id_in_deposito = $record_id_in_deposito;
	}

	public function set_stato_lavori(string $stato_lavori ){
		if ( !in_array( $stato_lavori, self::stato_lavori_validi)){
			throw new Exception(__CLASS__ .' '. __FUNCTION__ 
			. ' stato lavori must be in the valid set, is: '. $stato_lavori );
		}
		$this->stato_lavori = $stato_lavori;
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
		$dbh = $this->conn;
		if (!($dbh->is_datetime($record_cancellabile_dal))){
			throw new Exception(__CLASS__ .' '. __FUNCTION__ 
			. ' no for: '. $record_cancellabile_dal . '. Must be a valid datetime format yyyy-mm-dd hh:mm:ss ');
		}
		$this->record_cancellabile_dal = $record_cancellabile_dal;
	}
	
	// IS / CHECKER

	/**
	 * Only to check if present, nothing about valid rec / logically deleted rec 
	 * or other things 
	 *
	 * @param  int $record_id 
	 * @return bool 
	 */
	public function check_record_id( int $record_id ){
		$dbh = $this->conn; // a PDO object thru Database class

		$leggi = 'SELECT 1 FROM ' . self::nome_tabella   
		. ' WHERE record_id = :record_id ';
		try {
			$verifica = $dbh->prepare($leggi);
			$verifica->bindValue('record_id', $record_id, PDO::PARAM_INT);
			return $verifica->execute();
			} catch (\Throwable $th) {
				throw new Exception(__CLASS__ .' '. __FUNCTION__ 
				. ' verifica ko per: '. $record_id );
			}
	}
	
	// CRUD: CREATE READ UPDATE DELETE 
 
	/**
	 * Servono i campi da inserire in tabella 
	 * @param  array $campi 
	 * @return array $ret ok + record_id | error + message
	 */
	
	public function aggiungi( array $campi = [] ) : array { 
		// record_id               viene assegnato automaticamente pertanto non è in elenco 
		// ultima_modifica_record  viene assegnato automaticamente 
		// record_cancellabile_dal viene assegnato automaticamente 
		//                         ma non sempre 
		// stato_lavori            viene assegnato automaticamente 
		
		$dbh = $this->conn; // a PDO object thru Database class
		$create = 'INSERT INTO ' . self::nome_tabella 
		. ' (  titolo_fotografia,   disco,  percorso_completo,'
		. '    record_id_in_album,  record_id_in_deposito ) VALUES '
		. ' ( :titolo_fotografia,  :disco, :percorso_completo, '
		. '   :record_id_in_album, :record_id_in_deposito ) ';

		// campi necessari 
		if (!isset($campi['titolo_fotografia'])){
			$ret = [
				"error"=> true, 
				"message" => __CLASS__ . ' ' . __FUNCTION__ 
				. " Serve campo titolo_fotografia: " . $dbh::esponi($campi) 
			];
			return $ret;
		}
		$this->set_titolo_fotografia($campi['titolo_fotografia']);

		if (!isset($campi['disco'])){
			$ret = [
				"error"=> true, 
				"message" => __CLASS__ . ' ' . __FUNCTION__ 
				. " Serve campo disco: " . $dbh::esponi($campi) 
			];
			return $ret;
		}
		$this->set_disco($campi['disco']);

		if (!isset($campi['percorso_completo'])){
			$ret = [
				"error"=> true, 
				"message" => __CLASS__ . ' ' . __FUNCTION__ 
				. " Serve campo percorso_completo: " . $dbh::esponi($campi) 
			];
			return $ret;
		}
		$this->set_percorso_completo($campi['percorso_completo']);

		if (!isset($campi['record_id_in_album'])){
			$ret = [
				"error"=> true, 
				"message" => __CLASS__ . ' ' . __FUNCTION__ 
				. " Serve campo record_id_in_album: " . $dbh::esponi($campi) 
			];
			return $ret;
		}
		$this->set_record_id_in_album($campi['record_id_in_album']);

		if (!isset($campi['record_id_in_deposito'])){
			$ret = [
				"error"=> true, 
				"message" => __CLASS__ . ' ' . __FUNCTION__ 
				. " Serve campo record_id_in_deposito: " . $dbh::esponi($campi) 
			];
			return $ret;
		}
		$this->set_record_id_in_deposito($campi['record_id_in_deposito']);

		if ($dbh === false){
			$ret = [
				"error"=> true, 
				"message" => __CLASS__ . ' ' . __FUNCTION__ 
				. " Inserimento record senza connessione archivio per: " . self::nome_tabella 
			];
			return $ret;
		}
		if (!$dbh->inTransaction()) { $dbh->beginTransaction(); }		
		try{
			$aggiungi = $dbh->prepare($create);
			$aggiungi->bindValue('titolo_fotografia',            $this->titolo_fotografia);
			$aggiungi->bindValue('disco',                        $this->disco);
			$aggiungi->bindValue('percorso_completo',            $this->percorso_completo);
			$aggiungi->bindValue('record_id_in_album',           $this->record_id_in_album);
			$aggiungi->bindValue('record_id_in_deposito', $this->record_id_in_deposito);
			$aggiungi->execute();
			$record_id = $dbh->lastInsertID();
			$dbh->commit();

		} catch(\Throwable $th ){
			//throw $th;
			$dbh->rollBack(); 

			$ret = [
				"record_id" => 0,
				"error"   => true,
				"message" => __CLASS__ . ' ' . __FUNCTION__ 
				. ' ' . $th->getMessage() 
				. " campi: " . $dbh::esponi($campi)
				. ' istruzione SQL: ' . $create 
			];
			return $ret;      
		} // try catch 

		$ret = [
			"ok"=> true, 
			"record_id" => $record_id,
			"message" => __CLASS__ . ' ' . __FUNCTION__ 
			. " Inserimento record effettuato, nuovo id: " . $record_id 
		];
		return $ret;

	} // aggiungi

	/**
	 * READ - leggi 
	 * 
	 */
	public function leggi(array $campi) : array {
		// dati obbligatori
		$dbh = $this->conn; // a PDO object thru Database class

		if (!isset($campi['query'])){
			$ret = [
				"error"=> true, 
				"message" => __CLASS__ . ' ' . __FUNCTION__ 
				. "Deve essere definita l'istruzione SELECT in ['query']: " 
				. 'campi: ' . $dbh::esponi($campi)
			];
			return $ret;
		}
		$read = $campi['query'];
		// convalide campi 
		if (isset($campi['record_id'])){
			$this->set_record_id($campi['record_id']); 
		}
		if (isset($campi['titolo_fotografia'])){
			$this->set_titolo_fotografia($campi['titolo_fotografia']); 
		}
		if (isset($campi['disco'])){
			$this->set_disco($campi['disco']); 
		}
		if (isset($campi['percorso_completo'])){
			$this->set_percorso_completo($campi['percorso_completo']); 
		}
		if (isset($campi['record_id_in_album'])){
			$this->set_record_id_in_album($campi['record_id_in_album']);
		}
		if (isset($campi['record_id_in_deposito'])){
			$this->set_record_id_in_deposito($campi['record_id_in_deposito']);
		}
		if (isset($campi['stato_lavori'])){
			$this->set_stato_lavori($campi['stato_lavori']); 
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
				$lettura->bindValue('record_id', $this->record_id, PDO::PARAM_INT); 
			}
			if (isset($campi['titolo_fotografia'])){
				$lettura->bindValue('titolo_fotografia', $this->titolo_fotografia); 
			}
			if (isset($campi['disco'])){
				$lettura->bindValue('disco', $this->disco); 
			}
			if (isset($campi['percorso_completo'])){
				$lettura->bindValue('percorso_completo', $this->percorso_completo); 
			}
			if (isset($campi['record_id_in_album'])){
				$lettura->bindValue('record_id_in_album', $this->record_id_in_album, PDO::PARAM_INT); 
			}
			if (isset($campi['record_id_in_deposito'])){
				$lettura->bindValue('record_id_in_deposito', $this->record_id_in_deposito, PDO::PARAM_INT); 
			}
			if (isset($campi['stato_lavori'])){
				$lettura->bindValue('stato_lavori', $this->stato_lavori ); 
			}
			if (isset($campi['ultima_modifica_record'])){
				$lettura->bindValue('ultima_modifica_record', $this->ultima_modifica_record ); 
			}
			if (isset($campi['record_cancellabile_dal'])){
				$lettura->bindValue('record_cancellabile_dal', $this->record_cancellabile_dal ); 
			}
			$lettura->execute();
		} catch( \Throwable $th ){
			$ret = [
				"error" => true,
				"message" => __CLASS__ . ' ' . __FUNCTION__ 
				. ' ' . $th->getMessage() 
				. ' campi: ' . $dbh::esponi($campi)
				. ' istruzione SQL: ' . $read
			];
			return $ret;
		}
		$numero = 0;
		$dati_di_ritorno = [];

		while ($record = $lettura->fetch(PDO::FETCH_ASSOC)) {
			$dati_di_ritorno[] = $record;
			$numero++;
		}    

		// Può dare ok anche per un risultato "vuoto", è compito del chiamante valutare se sia un errore
		$ret = [
			'ok'     => true,
			'numero' => $numero,
			'data'   => $dati_di_ritorno 
		];
		return $ret;
	} // leggi 



	/**
	 * UPDATE - modifica 
	 * ATTENZIONE: La modifica del campo "record_cancellabile_dal" viene 
	 *             gestita come cancellazione logica, in attesa di una fase
	 *             di scarico e cancellazione fisica.
	 *
	 * @param  array $campi - uno deve essere "update" e contenere una istruzione sql 
	 * @return array $ret 'ok' + message | error + message 
	 */
	public function modifica( array $campi = []) {
		// campi indispensabili 
		$dbh = $this->conn; // a PDO object thru Database class

		if (!isset($campi['update'])){
			$ret = [
				"error"=> true, 
				"message" => __CLASS__ . ' ' . __FUNCTION__ 
				. " Aggiornamento record senza UPDATE: " . $dbh::esponi($campi) 
			];
			return $ret;
		}
		// convalide 
		if (isset($campi['record_id'])){
			$this->set_record_id($campi['record_id']); 
		}
		if (isset($campi['titolo_fotografia'])){
			$this->set_titolo_fotografia($campi['titolo_fotografia']); 
		}
		if (isset($campi['disco'])){
			$this->set_disco($campi['disco']); 
		}
		if (isset($campi['percorso_completo'])){
			$this->set_percorso_completo($campi['percorso_completo']); 
		}
		if (isset($campi['record_id_in_album'])){
			$this->set_record_id_in_album($campi['record_id_in_album']); 
		}
		if (isset($campi['record_id_in_deposito'])){
			$this->set_record_id_in_deposito($campi['record_id_in_deposito']); 
		}
		if (isset($campi['stato_lavori'])){
			$this->set_stato_lavori($campi['stato_lavori']); 
		}
		if (isset($campi['ultima_modifica_record'])){
			$this->set_ultima_modifica_record($campi['ultima_modifica_record']); 
		}
		if (isset($campi['record_cancellabile_dal'])){
			$this->set_record_cancellabile_dal($campi['record_cancellabile_dal']); 
		}
		$update = $campi['update'];
		if (!$dbh->inTransaction()) { $dbh->beginTransaction(); }
		// azione 
		try {
			$aggiorna = $dbh->prepare($update);
			if (isset($campi['record_id'])){
				$aggiorna->bindValue('record_id', $this->record_id, PDO::PARAM_INT); 
			}
			if (isset($campi['titolo_fotografia'])){
				$aggiorna->bindValue('titolo_fotografia', $this->titolo_fotografia); 
			}
			if (isset($campi['disco'])){
				$aggiorna->bindValue('disco', $this->disco); 
			}
			if (isset($campi['percorso_completo'])){
				$aggiorna->bindValue('percorso_completo', $this->percorso_completo); 
			}
			if (isset($campi['record_id_in_album'])){
				$aggiorna->bindValue('record_id_in_album', $this->record_id_in_album, PDO::PARAM_INT); 
			}
			if (isset($campi['record_id_in_deposito'])){
				$aggiorna->bindValue('record_id_in_deposito', $this->record_id_in_deposito, PDO::PARAM_INT); 
			}
			if (isset($campi['stato_lavori'])){
				$aggiorna->bindValue('stato_lavori', $this->stato_lavori); 
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
			//throw $th;
			$dbh->rollBack(); 

			$ret = [
				'error' => true,
				'message' => __CLASS__ . ' ' . __FUNCTION__ 
				. ' ' . $th->getMessage()
				. ' campi: ' . $dbh::esponi($campi)
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
	 * Deve essere presente un $campi['delete'] con istruzione SQL e tutti i 
	 * $campi['nome_campo'] che hanno nell'istruzione SQL :nome_campo 
	 * 
	 * @param  array  $campi 
	 * @return array  $ret 'ok' + 'message' | 'error' + 'message' 
	 */
	public function elimina( array $campi = []) : array {
		// indispensabili 
		$dbh = $this->conn; // a PDO object thru Database class

		if (!isset($campi['delete'])){
			$ret = [
				"error"=> true, 
				"message" => "Cancellazione record senza DELETE. " 
				. ' campi : ' . $dbh::esponi($campi) 
			];
			return $ret;
		}
		if (isset($campi['record_id'])){
			$this->set_record_id($campi['record_id']);
		}
		if (isset($campi['titolo_fotografia'])){
			$this->set_titolo_fotografia($campi['titolo_fotografia']);
		}
		if (isset($campi['disco'])){
			$this->set_disco($campi['disco']);
		}
		if (isset($campi['percorso_completo'])){
			$this->set_percorso_completo($campi['percorso_completo']);
		}
		if (isset($campi['record_id_in_album'])){
			$this->set_record_id_in_album($campi['record_id_in_album']);
		}
		if (isset($campi['record_id_in_deposito'])){
			$this->set_record_id_in_deposito($campi['record_id_in_deposito']);
		}
		if (isset($campi['stato_lavori'])){
			$this->set_stato_lavori($campi['stato_lavori']); 
		}
		if (isset($campi['ultima_modifica_record'])){
			$this->set_ultima_modifica_record($campi['ultima_modifica_record']);
		}
		if (isset($campi['record_cancellabile_dal'])){
			$this->set_record_cancellabile_dal($campi['record_cancellabile_dal']);
		}
		$delete = $campi['delete'];
		if (!$dbh->inTransaction()) { $dbh->beginTransaction(); }
		try {
			$cancella = $dbh->prepare($delete);
			if (isset($campi['record_id'])){
				$cancella->bindValue('record_id', $this->record_id, PDO::PARAM_INT); 
			}
			if (isset($campi['titolo_fotografia'])){
				$cancella->bindValue('titolo_fotografia', $this->titolo_fotografia); 
			}
			if (isset($campi['disco'])){
				$cancella->bindValue('disco', $this->disco); 
			}
			if (isset($campi['percorso_completo'])){
				$cancella->bindValue('percorso_completo', $this->percorso_completo); 
			}
			if (isset($campi['record_id_in_album'])){
				$cancella->bindValue('record_id_in_album', $this->record_id_in_album, PDO::PARAM_INT); 
			}
			if (isset($campi['record_id_in_deposito'])){
				$cancella->bindValue('record_id_in_deposito', $this->record_id_in_deposito, PDO::PARAM_INT); 
			}
			if (isset($campi['stato_lavori'])){
				$cancella->bindValue('stato_lavori', $this->stato_lavori); 
			}
			if (isset($campi['ultima_modifica_record'])){ 
				$cancella->bindValue('ultima_modifica_record', $this->ultima_modifica_record); 
			}
			if (isset($campi['record_cancellabile_dal'])){
				$cancella->bindValue('record_cancellabile_dal', $this->record_cancellabile_dal); 
			}
			$cancella->execute();
			$dbh->commit();

		} catch( \Throwable $th ){
			//throw $th;
			$dbh->rollBack(); 

			$ret = [
				'error' => true,
				'message' => __CLASS__ . ' ' . __FUNCTION__ 
				. ' ' . $th->getMessage()
				. ' campi: ' . $dbh::esponi($campi)
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
	 * SET_STATO_LAVORI 
	 * 
	 * @param  int    $record_id
	 * @param  string $stato_lavori 
	 */
	public function set_stato_lavori_in_fotografie(int $record_id, string $stato_lavori) : array {
		// campi obbligatori 
		$dbh = $this->conn; // a PDO object thru Database class

		$this->set_record_id($record_id);
		$this->set_stato_lavori($stato_lavori);
		$update = ' UPDATE ' . self::nome_tabella
		. ' SET stato_lavori = :stato_lavori '
		. ' WHERE record_id = :record_id ';
		if (!$dbh->inTransaction()) { $dbh->beginTransaction(); }		
		try {
			$aggiorna=$dbh->prepare($update);		
			$aggiorna->bindValue('stato_lavori', $this->get_stato_lavori()); 
			$aggiorna->bindValue('record_id', $this->get_record_id()); 
			$aggiorna->execute();
			$dbh->commit();

		} catch (\Throwable $th) {
			//throw $th;
			$dbh->rollBack(); 

			$ret = [
				'error' => true,
				'message' => __CLASS__ . ' ' . __FUNCTION__ 
				. ' ' . $th->getMessage()
				. ' campi: record_id=' . $record_id . ' stato: ' . $stato_lavori  
				. ' istruzione SQL: ' . $update
			];
			return $ret;
		}
		$ret = [
			'ok' => true,
			'message' => 'Aggiornamento eseguito'
		];
		return $ret;
	} // set_stato_lavori_in_fotografie


	/**
	 * restituisce il risultato di $this->leggi 
	 * 
	 * @param  in    $video_id 
	 * @return array 'ok' + data[] | 'error' + 'message'
	 */
	public function get_fotografia_from_id(int $fotografia_id) : array{
		// dati obbligatori 
		$dbh = $this->conn; // a PDO object thru Database class

		// validazione
		$this->set_record_id($fotografia_id);
		$campi=[];
		$campi['query'] = 'SELECT * FROM ' . self::nome_tabella
		. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
		. ' AND record_id = :record_id ';
		$campi['record_cancellabile_dal']=$dbh->get_datetime_forever();
		$campi['record_id']=$this->get_record_id();
		$ret_foto = $this->leggi($campi);
		if (isset($ret_foto['error'])){
			return $ret_foto;
		}
		if ($ret_foto['numero'] < 1){
      $ret = [
        'error'   => true,
        'message' => "La fotografa {$fotografia_id } non è stata trovata."
      ];
      return $ret;
		}
		$ret = [
			'ok'    => true,
			'record'=> $ret_foto['data'][0]
		];
		return $ret;
	} // get_fotografia_from_id

	public function get_fotografia_da_fare() : array {
		// dati obbligatori 
		$dbh = $this->conn; // a PDO object thru Database class

		// validazione
		$campi=[];
		$campi['query'] = 'SELECT * FROM ' . self::nome_tabella
		. ' WHERE record_cancellabile_dal = :record_cancellabile_dal  '
		. ' AND stato_lavori = :stato_lavori '
		. ' ORDER BY record_id ';
		$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
		$campi['record_id'] = $this->get_record_id();
		$ret_foto = $this->leggi($campi);
		if (isset($ret_foto['error'])){
			return $ret_foto;
		}
		if ($ret_foto['numero'] < 1){
      $ret = [
        'error'   => true,
        'message' => "Una fotografia 'da fare' non è stata trovata."
      ];
      return $ret;
		}
		$ret = [
			'ok'    => true,
			'record'=> $ret_foto['data'][0]
		];
		return $ret;
	} // get_fotografia_da_fare

	/**
	 * Verifica quante fotografie sono memorizzate con lo stesso deposito_id
	 * In fotografie 
	 * 
	 * @param   int $deposito_id
	 * @return array 'ok' + data | 'error' + message
	 */
	public function get_fotografia_from_deposito_id( int $deposito_id) : array {
		// dati obbligatori 
		$dbh = $this->conn; // a PDO object thru Database class

		// validazione
		$this->set_record_id_in_deposito($deposito_id);
		$campi=[];
		$campi['query'] = 'SELECT * FROM ' . self::nome_tabella
		. ' WHERE record_cancellabile_dal = :record_cancellabile_dal  '
		. ' AND record_id_in_deposito = :record_id_in_deposito '
		. ' ORDER BY record_id ';
		$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
		$campi['record_id_in_deposito'] = $this->get_record_id_in_deposito();
		$ret_foto = $this->leggi($campi);
		if (isset($ret_foto['error'])){
			return $ret_foto;
		}
		if ($ret_foto['numero'] < 1){
      $ret = [
        'error'   => true,
				'numero'  => 0,
        'message' => "Non è stata trovata nessuna fotografia "
				. "con deposito_id ". $deposito_id
      ];
      return $ret;
		}
		return $ret_foto;

	} // get_fotografia_from_deposito_id
	
} // Fotografie