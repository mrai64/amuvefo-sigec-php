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
 *	dipendenze: ScansioniDisco 
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
Class Fotografie {
	private $conn = false;
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
	public $record_id_in_album; //            bigint(20) unsigned external key su scansioni_disco
	public $record_id_in_scansioni_disco; //  bigint(20) unsigned external key su scansioni_disco
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
		$this->record_id_in_scansioni_disco = 0; // invalido 
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
	public function get_record_id_in_scansioni_disco() : int {
		return $this->record_id_in_scansioni_disco;
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
		$disco = htmlspecialchars(strip_tags($disco));
		$disco = mb_substr($disco, 0, 12);
		$this->disco = $disco;
	}
	
	public function set_percorso_completo( string $percorso_completo ){
		// ritaglio a misura 
		$percorso_completo = htmlspecialchars(strip_tags($percorso_completo));
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

	public function set_record_id_in_scansioni_disco( int $record_id_in_scansioni_disco){
		if ($record_id_in_scansioni_disco < 1){
			throw new Exception(__CLASS__ .' '. __FUNCTION__ 
			. ' Must be unsigned integer, is: ' . $record_id_in_scansioni_disco);
		}
		$this->record_id_in_scansioni_disco = $record_id_in_scansioni_disco;
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
		if (!($this->conn->is_datetime($record_cancellabile_dal))){
			throw new Exception(__CLASS__ .' '. __FUNCTION__ 
			. ' no for: '. $record_cancellabile_dal . '. Must be a valid datetime format yyyy-mm-dd hh:mm:ss ');
		}
		$this->record_cancellabile_dal = $record_cancellabile_dal;
	}
	
	// IS / CHECKER
	/**
	 * Il record è "valido" in che senso? 
	 * Sono i record che hanno il campo record_cancellabile_dal == '9999-12-31 23:59:59'
	 *
	 * @return bool 
	 */
	public function check_FUTURO(){
		return ($this->record_cancellabile_dal == $this->conn->get_datetime_forever() );
	}
	
	/**
	 * Only to check if present, nothing about valid rec / logically deleted rec 
	 * or other things 
	 *
	 * @param  int $record_id 
	 * @return bool 
	 */
	public function check_record_id( int $record_id ){
		$dbh = $this->conn; // a PDO object thru Database class
		if ($dbh === false){
			throw new Exception(__CLASS__ .' '. __FUNCTION__ 
			. " Non si può verificare la presenza senza connessione all'archivio. ");
		}
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
		// stato_lavori            viene assegnato automaticamente 
	
		$create = 'INSERT INTO ' . self::nome_tabella 
		. ' (  titolo_fotografia,   disco,  percorso_completo,'
		. '    record_id_in_album,  record_id_in_scansioni_disco ) VALUES '
		. ' ( :titolo_fotografia,  :disco, :percorso_completo, '
		. '   :record_id_in_album, :record_id_in_scansioni_disco ) ';

		// campi necessari 
		if (!isset($campi['titolo_fotografia'])){
			$ret = [
				"error"=> true, 
				"message" => __CLASS__ . ' ' . __FUNCTION__ 
				. " Serve campo titolo_fotografia: " . serialize($campi) 
			];
			return $ret;
		}
		$this->set_titolo_fotografia($campi['titolo_fotografia']);

		if (!isset($campi['disco'])){
			$ret = [
				"error"=> true, 
				"message" => __CLASS__ . ' ' . __FUNCTION__ 
				. " Serve campo disco: " . serialize($campi) 
			];
			return $ret;
		}
		$this->set_disco($campi['disco']);

		if (!isset($campi['percorso_completo'])){
			$ret = [
				"error"=> true, 
				"message" => __CLASS__ . ' ' . __FUNCTION__ 
				. " Serve campo percorso_completo: " . serialize($campi) 
			];
			return $ret;
		}
		$this->set_percorso_completo($campi['percorso_completo']);

		if (!isset($campi['record_id_in_album'])){
			$ret = [
				"error"=> true, 
				"message" => __CLASS__ . ' ' . __FUNCTION__ 
				. " Serve campo record_id_in_album: " . serialize($campi) 
			];
			return $ret;
		}
		$this->set_record_id_in_album($campi['record_id_in_album']);

		if (!isset($campi['record_id_in_scansioni_disco'])){
			$ret = [
				"error"=> true, 
				"message" => __CLASS__ . ' ' . __FUNCTION__ 
				. " Serve campo record_id_in_scansioni_disco: " . serialize($campi) 
			];
			return $ret;
		}
		$this->set_record_id_in_scansioni_disco($campi['record_id_in_scansioni_disco']);

		$dbh = $this->conn; // a PDO object thru Database class
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
			$aggiungi->bindValue('record_id_in_scansioni_disco', $this->record_id_in_scansioni_disco);
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
				. " campi: " . serialize($campi)
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
		if ($dbh === false){
			$ret = [
				"error"=> true, 
				"message" => __CLASS__ . ' ' . __FUNCTION__ . ' ' . __LINE__
				. "Deve essere attiva la connessione all'archivio per " . self::nome_tabella 
			];
			return $ret;
		}
		if (!isset($campi['query'])){
			$ret = [
				"error"=> true, 
				"message" => __CLASS__ . ' ' . __FUNCTION__ 
				. "Deve essere definita l'istruzione SELECT in ['query']: " 
				. 'campi: ' . serialize($campi)
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
		if (isset($campi['record_id_in_scansioni_disco'])){
			$this->set_record_id_in_scansioni_disco($campi['record_id_in_scansioni_disco']);
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
			if (isset($campi['record_id_in_scansioni_disco'])){
				$lettura->bindValue('record_id_in_scansioni_disco', $this->record_id_in_scansioni_disco, PDO::PARAM_INT); 
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
				. ' campi: ' . serialize($campi)
				. ' istruzione SQL: ' . $read
			];
			return $ret;
		}
		$numero = 0;
		$dati_di_ritorno = [];
		/* senza limitatore */ 
		while ($record = $lettura->fetch(PDO::FETCH_ASSOC)) {
			$dati_di_ritorno[] = $record;
			$numero++;
		}    
		/* con limitatore 
		$limite_record = isset($campi['limite']) ? $campi['limite'] : 100;
		$limite_record = 100; // TODO: fa parte della gestione paginazione
		while(($record = $lettura->fetch(PDO::FETCH_ASSOC)) && ($numero < $limite_record)){
			$dati_di_ritorno[] = $record;
			$numero++;
		}
		*/
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
		if ($dbh === false){
			$ret = [
				"error"=> true, 
				"message" => __CLASS__ . ' ' . __FUNCTION__ 
				. " Modifica senza connessione archivio per: " 
				. self::nome_tabella 
			];
			return $ret;
		}
		if (!isset($campi['update'])){
			$ret = [
				"error"=> true, 
				"message" => __CLASS__ . ' ' . __FUNCTION__ 
				. " Aggiornamento record senza UPDATE: " . serialize($campi) 
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
		if (isset($campi['record_id_in_scansioni_disco'])){
			$this->set_record_id_in_scansioni_disco($campi['record_id_in_scansioni_disco']); 
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
			if (isset($campi['record_id_in_scansioni_disco'])){
				$aggiorna->bindValue('record_id_in_scansioni_disco', $this->record_id_in_scansioni_disco, PDO::PARAM_INT); 
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
				. ' campi: ' . serialize($campi)
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
	 * $campi['nomecampo'] che hanno nell'istruzione SQL :nomecampo 
	 * 
	 * @param  array  $campi 
	 * @return array  $ret 'ok' + 'message' | 'error' + 'message' 
	 */
	public function elimina( array $campi = []) : array {
		// indispensabili 
		$dbh = $this->conn; // a PDO object thru Database class
		if ($dbh === false){
			$ret = [
				"error"=> true, 
				"message" => __CLASS__ . ' ' . __FUNCTION__ 
				. " Cancellazione senza connessione archivio per: " . self::nome_tabella 
			];
			return $ret;
		}
		if (!isset($campi['delete'])){
			$ret = [
				"error"=> true, 
				"message" => "Cancellazione record senza DELETE. " 
				. ' campi : ' . serialize($campi) 
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
		if (isset($campi['record_id_in_scansioni_disco'])){
			$this->set_record_id_in_scansioni_disco($campi['record_id_in_scansioni_disco']);
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
			if (isset($campi['record_id_in_scansioni_disco'])){
				$cancella->bindValue('record_id_in_scansioni_disco', $this->record_id_in_scansioni_disco, PDO::PARAM_INT); 
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
	
	/**
	 * Validi? e gli altri? 
	 * Estrae un elenco di record validi (record_cancellabile_dal = '9999-12-31 23:59:59')
	 * in formato istruzione SQL INSERT INTO per ripristinare i dati qualora servisse 
	 * ATTENZIONE: è presente una "chiave esterna", occorre essere sicuri che al 
	 * momento del caricamento o anche in seguito ma con certezza la chiave esterna sia valida. 
	 * 
	 */
	public function get_elenco_validi( array $campi = []) {
		$ret = [ 
			"error" => true,
			"message" => "La funzione non è stata realizzata"
		];
		return $ret;
	} // get_elenco_validi
	
	/**
	 *	Crea una lista di istruzioni SQL per il caricamento dei record cancellabili
	 *	prima della cancellazione fisica, poi questo elenco deve diventare un
	 *	file con estensione sql che va scaricato dalla pagina / controller. 
	 */
	public function get_elenco_cancellabili( array $campi = []) {
		$ret = [ 
			"error" => true,
			"message" => "La funzione non è stata realizzata"
		];
		return $ret;
	} // get_elenco_cancellabili

	/**
	 * SET_STATO_LAVORI 
	 * 
	 * @param  int    $record_id
	 * @param  string $stato_lavori 
	 */
	public function set_stato_lavori_in_fotografie(int $record_id, string $stato_lavori) : array {
		// campi obbligatori 
		$dbh = $this->conn; // a PDO object thru Database class
		if ($dbh === false){
			$ret = [
				'error'   => true, 
				'message' => "L'aggiornamento dello stato_lavori "
				. 'non si può fare senza connessione archivio '
				. 'per: ' . self::nome_tabella  
			];
			return $ret;
		}
		$this->set_record_id($record_id);
		$this->set_stato_lavori($stato_lavori);
		$update = ' UPDATE ' . self::nome_tabella
		. ' SET stato_lavori = :stato_lavori '
		. ' WHERE record_id = :record_id ';
		if (!$dbh->inTransaction()) { $dbh->beginTransaction(); }		
		try {
			//code...
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
		if ($dbh === false){
			$ret = [
				'error'   => true, 
				'message' => __CLASS__ . ' ' . __FUNCTION__ 
				. '<br>Serve una connessione attiva per leggere in '
				. self::nome_tabella 
			];
			return $ret;
		}
		// validazione
		$this->set_record_id($fotografia_id);
		$read = 'SELECT * FROM ' . self::nome_tabella
		. ' WHERE record_cancellabile_dal = :record_cancellabile_dal  '
		. ' AND record_id = :record_id '
		. ' LIMIT 1 ';
		try {
			$lettura=$dbh->prepare($read);
			$lettura->bindValue('record_cancellabile_dal', $dbh->get_datetime_forever() ); 
			$lettura->bindValue('record_id',               $fotografia_id, PDO::PARAM_INT); 
			$lettura->execute();

		} catch( \Throwable $th ){
			$ret = [
				'error'   => true,
				'message' => __CLASS__ . ' ' . __FUNCTION__ 
				. '<br>' . $th->getMessage() 
				. '<br>fotografia_id: ' . $fotografia_id
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
	} // get_fotografia_from_id
	
} // Fotografie