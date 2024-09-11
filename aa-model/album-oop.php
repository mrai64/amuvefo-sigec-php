<?php 
/**
 *	@source /aa-model/album-oop.php 
 *  @author Massimo Rainato <maxrainato@libero.it>
 * 
 *	Classe Album
 *
 *	dipendenze: DatabaseHandler connessione archivio PDO 
 *	dipendenze: AlbumDettagli tabella figlio 
 *	dipendenze: Descrizioni tabella di lunghi testi 
 *	dipendenze: ScansioniDisco 
 *
 * @see https://archivio.athesis77.it/tech/3-archivi-tabelle/album/
 * @see https://archivio.athesis77.it/tech/3-archivi-tabelle/album_dettagli/
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
Class Album {
	private $conn = false;
	public const nome_tabella  = 'album'; // qui self::nome_tabella da fuori Album::nome_tabella
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
	public $titolo_album; //                  varchar(250)
	public $disco; //                         char(12)
	public $percorso_completo; //             varchar(1500)
	public $record_id_in_scansioni_disco; //  bigint(20) unsigned external key su scansioni_disco
	public $stato_lavori; //                  string enum df '0 da fare'
	public $ultima_modifica_record; //        datetime DEF CURRENT TIME
	public $record_cancellabile_dal; //       datetime DEF '9999-12-31 23:59:59'
	
	public function __construct(DatabaseHandler $dbh){
		$this->conn = $dbh;
		
		$this->record_id = 0; //                     invalido
		$this->titolo_album = ''; //                 invalido 
		$this->disco = ''; //                        invalido 
		$this->percorso_completo = ''; //            invalido 
		$this->record_id_in_scansioni_disco = 0; //  invalido 
		$this->stato_lavori = self::stato_da_fare; 
		$this->ultima_modifica_record = $dbh->get_datetime_now();
		$this->record_cancellabile_dal = $dbh->get_datetime_forever();
	} // __construct
	
	// GETTER 
	/**
	 * @return int unsigned
	 */
	public function get_record_id(){
		return $this->record_id;
	}
	
	public function get_titolo_album() {
		return $this->titolo_album;
	}
	
	public function get_disco(){
		return $this->disco;
	}
	
	public function get_percorso_completo(){
		return $this->percorso_completo;
	}
	
	/**
	 * @return int unsigned 
	 */
	public function get_record_id_in_scansioni_disco(){
		return $this->record_id_in_scansioni_disco;
	}
	public function get_stato_lavori(){
		return $this->stato_lavori;
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
			throw new Exception(__CLASS__ . ' ' . __FUNCTION__ . ' Must be unsigned integer is : ' . $record_id );
		}
		$this->record_id = $record_id;
	}
		
	public function set_titolo_album( string $titolo_album ) {
		// ritaglio a misura
		$titolo_album = htmlspecialchars(strip_tags($titolo_album));
		$titolo_album = mb_substr($titolo_album, 0, 250);
		$this->titolo_album = $titolo_album;
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
		$percorso_completo = mb_substr($percorso_completo, 0, 1500);
		$this->percorso_completo = $percorso_completo;
	}
	
	public function set_record_id_in_scansioni_disco( int $record_id_in_scansioni_disco){
		if ($record_id_in_scansioni_disco < 1){
			throw new Exception(__CLASS__ .' '. __FUNCTION__ . ' Must be unsigned integer ' . $record_id_in_scansioni_disco);
		}
		$this->record_id_in_scansioni_disco = $record_id_in_scansioni_disco;
	}
	
	public function set_stato_lavori( string $stato_lavori){
		if (!in_array($stato_lavori, self::stato_lavori_validi)){
			throw new Exception(__CLASS__ . ' ' . __FUNCTION__ 
			. ' stato_lavori invalid value, out of set. ' );
			$this->stato_lavori = $stato_lavori; 
		}
	}

	// se cercate set_stato_valori_album è più avanti

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
	 * Verifica se sia presente un record nella tabella album 
	 * Solo che sia presente, non che sia un record valido 
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
		$verifica = $this->conn->prepare($leggi);
		$verifica->bindValue('record_id', $record_id, PDO::PARAM_INT);
		return $verifica->execute();
	}
	
	// CRUD: CREATE READ UPDATE DELETE 
 
	/**
	 * Servono i campi da inserire in tabella 
	 * @param  array $campi 
	 * @return array $ret ok + record_id | error + message
	 */
	
	public function aggiungi( array $campi = [] ){ 
		// record_id               viene assegnato automaticamente pertanto non è in elenco 
		// stato_lavori            viene assegnato automaticamente 
		// ultima_modifica_record  viene assegnato automaticamente 
		// record_cancellabile_dal viene assegnato automaticamente 
	
		$create = 'INSERT INTO ' . self::nome_tabella 
		. ' (  titolo_album,  disco,  percorso_completo,  record_id_in_scansioni_disco ) VALUES '
		. ' ( :titolo_album, :disco, :percorso_completo, :record_id_in_scansioni_disco ) ';

		// campi necessari 
		if (!isset($campi['titolo_album'])){
			$ret = [
				"error"=> true, 
				"message" => __CLASS__ . ' ' . __FUNCTION__ 
				. " Serve campo titolo_album: " . serialize($campi) 
			];
			return $ret;
		}
		$this->set_titolo_album($campi['titolo_album']);

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
				. " Inserimento record senza connessione archivio per: " 
				. self::nome_tabella 
			];
			return $ret;
		}
		if (!$dbh->inTransaction()) { $dbh->beginTransaction(); }
		try{
			$aggiungi = $dbh->prepare($create);
			$aggiungi->bindValue('titolo_album', $this->titolo_album);
			$aggiungi->bindValue('disco', $this->disco);
			$aggiungi->bindValue('percorso_completo', $this->percorso_completo);
			$aggiungi->bindValue('record_id_in_scansioni_disco', $this->record_id_in_scansioni_disco);
			$aggiungi->execute();
			$record_id = $dbh->lastInsertID();
			$dbh->commit();

		} catch(\Throwable $th ){
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
			. " Inserimento record effettuato, nuovo id: " 
			. $record_id 
		];
		return $ret;

	} // aggiungi

	/**
	 * READ leggi 
	 * L'array di input deve contenere un campo query con l'istruzione select 
	 * e tutti i campi "presenti" nella query.
	 * Si possono usare per ogni campo 'campo' 
	 * $campi['campo'] per una ricerca puntuale (uguale, diverso, ecc.)
	 * $campi['campo_min'] + $campi['campo_max'] 
	 * Se viene usata nella query la between :campo_min and :campo_max
	 * $campi['da_campo'] se viene usata una query con la ripartenza
	 * TODO: la gestione delle paginazioni va fatta, però su cosa ci si basa per dare un ordine? si fa l'ordinamento?
	 * 
	 * @param  array $campi - $campi['query'] con istruzione SQL 
	 * @return array $ret 'ok' + numero + data[] | 'error' + 'message' 
	 */  
	public function leggi(array $campi) : array {
		// dati obbligatori
		$dbh = $this->conn; // a PDO object thru Database class
		if ($dbh === false){
			$ret = [
				"error"=> true, 
				"message" => __CLASS__ . ' ' . __FUNCTION__ 
				. "Deve essere attiva la connessione all'archivio per: " 
				. self::nome_tabella 
			];
			return $ret;
		}
		if (!isset($campi['query'])){
			$ret = [
				"error"=> true, 
				"message" => __CLASS__ . ' ' . __FUNCTION__ 
				. "Deve essere definita l'istruzione SELECT in ['query']: " 
				. serialize($campi)
			];
			return $ret;
		}
		$read = $campi['query'];
		if (isset($campi['record_id'])){
			$this->set_record_id($campi['record_id']);
		}
		if (isset($campi['titolo_album'])){
			$this->set_titolo_album($campi['titolo_album']);
		}
		if (isset($campi['disco'])){
			$this->set_disco($campi['disco']);
		}
		if (isset($campi['percorso_completo'])){
			$this->set_percorso_completo($campi['percorso_completo']);
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
				$lettura->bindValue('record_id', $campi['record_id'], PDO::PARAM_INT); 
			}
			if (isset($campi['titolo_album'])){
				$lettura->bindValue('titolo_album', $campi['titolo_album']); 
			}
			if (isset($campi['disco'])){
				$lettura->bindValue('disco', $campi['disco']); 
			}
			if (isset($campi['percorso_completo'])){
				$lettura->bindValue('percorso_completo', $campi['percorso_completo']); 
			}
			if (isset($campi['record_id_in_scansioni_disco'])){
				$lettura->bindValue('record_id_in_scansioni_disco', $campi['record_id_in_scansioni_disco']); 
			}
			if (isset($campi['stato_lavori'])){
				$lettura->bindValue('stato_lavori', $campi['stato_lavori']); 
			}
			if (isset($campi['ultima_modifica_record'])){
				$lettura->bindValue('ultima_modifica_record', $campi['ultima_modifica_record']); 
			}
			if (isset($campi['record_cancellabile_dal'])){
				$lettura->bindValue('record_cancellabile_dal', $campi['record_cancellabile_dal']); 
			}	
			$lettura->execute();

		} catch( \Throwable $th ){
			//throw $th;
			$ret = [
				'error' => true,
				'message' => __CLASS__ . ' ' . __FUNCTION__ 
				. ' ' . $th->getMessage() 
				. ' campi: ' . serialize($campi)
				. ' istruzione SQL: ' . $read
			];
			return $ret;
		}
		$conteggio = 0;
		$dati_di_ritorno = [];
		// si potrebbe usare fetchAll(), però... 
		while ($record = $lettura->fetch(PDO::FETCH_ASSOC)) {
			$dati_di_ritorno[] = $record;
			$conteggio++;
		}    
		/*
		$limite_record = isset($campi['limite']) ? $campi['limite'] : 100;
		$limite_record = 100; // TODO: fa parte della gestione paginazione
		while(($record = $lettura->fetch(PDO::FETCH_ASSOC)) && ($conteggio < $limite_record)){
			$dati_di_ritorno[] = $record;
			$conteggio++;
		}
		*/
		// Può dare ok anche per un risultato "vuoto"
		$ret = [
			'ok'     => true,
			'numero' => $conteggio,
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
	public function modifica( array $campi) : array {
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
				. " Aggiornamento record senza UPDATE: " 
				. serialize($campi) 
			];
			return $ret;
		}
		$update = $campi['update'];
		if (isset($campi['record_id'])){
			$this->set_record_id($campi['record_id']);
		}
		if (isset($campi['titolo_album'])){
			$this->set_titolo_album($campi['titolo_album']);
		}
		if (isset($campi['disco'])){
			$this->set_disco($campi['disco']);
		}
		if (isset($campi['percorso_completo'])){
			$this->set_percorso_completo($campi['percorso_completo']);
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
		if (!$dbh->inTransaction()) { $dbh->beginTransaction(); }

		try {
			$aggiorna = $dbh->prepare($update);
			if (isset($campi['record_id'])){
				$aggiorna->bindValue('record_id', $campi['record_id'], PDO::PARAM_INT); 
			}
			if (isset($campi['titolo_album'])){
				$aggiorna->bindValue('titolo_album', $campi['titolo_album']); 
			}
			if (isset($campi['disco'])){
				$aggiorna->bindValue('disco', $campi['disco']); 
			}
			if (isset($campi['percorso_completo'])){
				$aggiorna->bindValue('percorso_completo', $campi['percorso_completo']); 
			}
			if (isset($campi['record_id_in_scansioni_disco'])){
				$aggiorna->bindValue('record_id_in_scansioni_disco', $campi['record_id_in_scansioni_disco'], PDO::PARAM_INT); 
			}
			if (isset($campi['stato_lavori'])){
				$aggiorna->bindValue('stato_lavori', $campi['stato_lavori']); 
			}
			if (isset($campi['ultima_modifica_record'])){
				$aggiorna->bindValue('ultima_modifica_record', $campi['ultima_modifica_record']); 
			}
			if (isset($campi['record_cancellabile_dal'])){
				$aggiorna->bindValue('record_cancellabile_dal', $campi['record_cancellabile_dal']); 
			}
			$aggiorna->execute();
			$dbh->commit();

		} catch( \Throwable $th ){
			$dbh->rollBack(); 
			$ret = [
				"error" => true,
				"message" => __CLASS__ . ' ' . __FUNCTION__ 
				. '<br>' . $th->getMessage() 
				. '<br>campi: ' . serialize($campi)
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
	 * DELETE - elimina 
	 * 
	 * $campi deve avere un campo DELETE che contiene una istruzione SQL 
	 * di cancellazione fisica 
	 * 
	 * @param  array  $campi 
	 * @return array  $ret 'ok' + message | 'error' + message 
	 */
	public function elimina( array $campi = []) {
		// campi obbligatori 
		$dbh = $this->conn; // a PDO object thru Database class
		if ($dbh === false){
			$ret = [
				'error'   => true, 
				'message' => 'La cancellazione di record '
				. 'non si può fare senza connessione archivio '
				. 'per: ' . self::nomeTabella 
			];
			return $ret;
		}
		if (!isset($campi['delete'])){
			$ret = [
				'error'   => true, 
				'message' => __CLASS__ . ' ' . __FUNCTION__ 
				. " Deve essere definita l'istruzione DELETE in ['delete']: " 
				. serialize($campi)
			];
			return $ret;
		}
		$delete = $campi['delete'];
		if (isset($campi['record_id'])){
			$this->set_record_id($campi['record_id']);
		}
		if (isset($campi['titolo_album'])){
			$this->set_titolo_album($campi['titolo_album']);
		}
		if (isset($campi['disco'])){
			$this->set_disco($campi['disco']);
		}
		if (isset($campi['percorso_completo'])){
			$this->set_percorso_completo($campi['percorso_completo']);
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
		if (!$dbh->inTransaction()) { $dbh->beginTransaction(); }

		try {
			$cancella = $dbh->prepare($delete);
			if (isset($campi['record_id'])){
				$cancella->bindValue('record_id', $campi['record_id'], PDO::PARAM_INT); 
			}
			if (isset($campi['titolo_album'])){
				$cancella->bindValue('titolo_album', $campi['titolo_album']); 
			}
			if (isset($campi['disco'])){
				$cancella->bindValue('disco', $campi['disco']); 
			}
			if (isset($campi['percorso_completo'])){
				$cancella->bindValue('percorso_completo', $campi['percorso_completo']); 
			}
			if (isset($campi['record_id_in_scansioni_disco'])){
				$cancella->bindValue('record_id_in_scansioni_disco', $campi['record_id_in_scansioni_disco'], PDO::PARAM_INT); 
			}
			if (isset($campi['stato_lavori'])){
				$cancella->bindValue('stato_lavori', $campi['stato_lavori']); 
			}
			if (isset($campi['ultima_modifica_record'])){
				$cancella->bindValue('ultima_modifica_record', $campi['ultima_modifica_record']); 
			}
			if (isset($campi['record_cancellabile_dal'])){
				$cancella->bindValue('record_cancellabile_dal', $campi['record_cancellabile_dal']); 
			}
			$cancella->execute();
			$dbh->commit();

		} catch( \Throwable $th ){
			//throw $th;
			$dbh->rollBack(); 
			$ret = [
				"error" => true,
				"message" => __CLASS__ . ' ' . __FUNCTION__ . ' ' 
				. $th->getMessage() . " campi: " . serialize($campi)
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
	 * UPDATE 
	 */
	public function set_stato_lavori_album( int $album_id, string $stato_lavori) : array {
		$dbh = $this->conn; // a PDO object thru Database class
		if ($dbh === false){
			$ret = [
				"error"=> true, 
				'message' => __CLASS__ . ' ' . __FUNCTION__ . ' ' . __LINE__  
				. " Modifica senza connessione archivio per: " 
				. self::nome_tabella 
			];
			return $ret;
		}
		$this->set_record_id($album_id);
		$this->set_stato_lavori($stato_lavori);
		$update = 'UPDATE ' . self::nome_tabella
		. ' SET stato_lavori = :stato_lavori '
		. ' WHERE record_id = :record_id ';
		if (!$dbh->inTransaction()) { $dbh->beginTransaction(); }
		try {
			$aggiorna = $dbh->prepare($update);
			$aggiorna->bindValue('stato_lavori', $this->stato_lavori ); 
			$aggiorna->bindValue('record_id', $this->record_id, PDO::PARAM_INT); 
			$aggiorna->execute();
			$dbh->commit();

		} catch (\Throwable $th) {
			$dbh->rollBack();
			$ret = [
				'error'   => true,
				'message' => __CLASS__ . ' ' . __FUNCTION__ . ' ' . __LINE__  
				. '<br>' . $th->getMessage() 
				. '<br>Campi: ' . $album_id . ', ' . $stato_lavori
				. '<br>Istruzione SQL: ' . $update
			]; 
			return $ret;
		} // try-catch
		$ret = [
			'ok'      => true,
			'message' => 'Aggiornamento eseguito'
		]; 
	} // set_stato_lavori_album()


	/**
	 * restituisce il risultato di $this->leggi 
	 * 
	 * @param  in    $album_id 
	 * @return array 'ok' + data[] | 'error' + 'message'
	 */
	public function get_album_from_id(int $album_id) : array {
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
		$this->set_record_id($album_id);

		$read = 'SELECT * FROM ' . self::nome_tabella
		. ' WHERE record_cancellabile_dal = :record_cancellabile_dal  '
		. ' AND record_id = :record_id '
		. ' LIMIT 1 ';
		try {
			$lettura=$dbh->prepare($read);
			$lettura->bindValue('record_cancellabile_dal', $dbh->get_datetime_forever() ); 
			$lettura->bindValue('record_id',               $album_id, PDO::PARAM_INT); 
			$lettura->execute();

		} catch( \Throwable $th ){
			$ret = [
				'error'   => true,
				'message' => __CLASS__ . ' ' . __FUNCTION__ 
				. '<br>' . $th->getMessage() 
				. '<br>album_id: ' . $album_id
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
	} // get_album_from_id


} // Album