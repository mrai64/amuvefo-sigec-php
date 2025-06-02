<?php
/**
 * @source /aa-model/album-dettagli-oop.php
 * @author Massimo Rainato <maxrainato@libero.it>
 * 
 * Classe AlbumDettagli 
 * 
 *	dipendenze: DatabaseHandler connessione archivio PDO 
 *	dipendenze: Album tabella padre 
 *	dipendenze: Descrizioni tabella di lunghi testi 
 * 
 * Per ogni album i dati associati non dovrebbero essere 
 * così numerosi da giustificare una gestione paginazione in 
 * funzione leggi(). nel caso l'ordinamento andrà fatto 
 * su chiave + record_id
 * 
 * La struttura è identica per le classi fotografie_dettagli e 
 * video_dettagli, si può creare una classe superiore Dettagli 
 * di cui album_dettagli, fotografie_dettagli, video_dettagli sono 
 * estensioni con la variante del nome tabella.
 * 
 * @see https://archivio.athesis77.it/tech/3-archivi-tabelle/album_dettagli/ 
 * 
 * metodi
 * GETTER
 *   get_record_id
 *   get_record_id_padre
 *   get_chiave
 *   get_valore 
 *   get_consultatore_id 
 *   get_ultima_modifica_record 
 *   get_record_cancellabile_dal 
 * SETTER
 *   set_record_id
 *   set_record_id_padre
 *   set_chiave
 *   set_valore 
 *   set_consultatore_id
 *   set_ultima_modifica_record 
 *   set_record_cancellabile_dal 
 * CHECKER 
 *   is_datetime 
 * CRUD
 *   aggiungi CREATE
 *   leggi    READ 
 *   modifica UPDATE
 *   elimina  DELETE 
 * OTHERS
 * 
 */
Class AlbumDettagli {
	private $conn = false;
	public const nome_tabella  = 'album_dettagli';	

	public $record_id; //         
	public $record_id_padre; //   
	public $chiave; //            
	public $valore; //            
	public $consultatore_id; //            
	public $ultima_modifica_record; // 
	public $record_cancellabile_dal; // se non vale 9999-12-31 23:59:59 è cancellabile
	
	public function __construct(DatabaseHandler $dbh){
		$this->conn = $dbh;
		
		$this->record_id                = 0;  // invalido
		$this->record_id_padre          = 0; // chiave esterna - 0 non è valido
		$this->chiave                   = ''; // invalido 
		$this->valore                   = ''; // invalido
		$this->consultatore_id          = 0;  // 0 | $_COOKIE['id_calendario']
		$this->ultima_modifica_record   = $dbh->get_datetime_now();
		$this->record_cancellabile_dal  = $dbh->get_datetime_forever();
	} // __construct
	
	// GETTER 
	public function get_record_id() : int {
		return $this->record_id;
	}
	
	public function get_record_id_padre() : int {
		return $this->record_id_padre;
	}
	
	public function get_chiave() : string {
		return $this->chiave;
	}
	
	public function get_valore() : string {
		return $this->valore;
	}

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
	
	
	// SETTER 
	public function set_record_id( int $record_id ){
		if ($record_id < 1){
			throw new Exception(__CLASS__ . ' ' . __FUNCTION__ 
			. ' Must be unsigned integer is : ' . $record_id );
		}
		$this->record_id = $record_id;
	}
	
	public function set_record_id_padre( int $record_id_padre ){
		if ($record_id_padre < 1){
			throw new Exception(__CLASS__ . ' ' . __FUNCTION__ 
			. ' Must be unsigned integer is : ' . $record_id_padre );
		}
		$this->record_id_padre = $record_id_padre;
		// TODO: si può aggiungere un check se presente in tabella_padre Album
	}
	
	public function set_chiave( string $chiave ) {
		// validazione
		$chiave = htmlspecialchars(strip_tags($chiave));
		$chiave = trim(mb_substr($chiave, 0, 250));
		if ($chiave == ""){
			throw new Exception(__CLASS__ . ' ' . __FUNCTION__ 
			. ' chiave Cannot be empty. ' );
		}
		$this->chiave = $chiave;
	}
	
	public function set_valore( string $valore ) {
		// validazione
		$valore = htmlspecialchars(strip_tags($valore));
		$valore = trim(mb_substr($valore, 0, 250));
		if ($valore == ""){
			throw new Exception(__CLASS__ . ' ' . __FUNCTION__ 
			. ' valore Cannot be empty. ' );
		}
		$this->valore = $valore;
	}

	public function set_consultatore_id( int $consultatore_id ){
		if ($consultatore_id < 0){
			throw new Exception(__CLASS__ . ' ' . __FUNCTION__ 
			. ' Must be unsigned integer, is : ' . $consultatore_id );
		}
		$this->consultatore_id = $consultatore_id;
		// TODO: si può aggiungere un check se presente in consultatori_calendario e se è in periodo "valido"
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
			. ' Must be datetime, instead of: ' . $record_cancellabile_dal );
		}
		$this->record_cancellabile_dal = $record_cancellabile_dal;
	}
	
	// CHECKER 
	/** 
	 *	@param  string datetime yyyy-mm-dd hh:mm:ss 
	 *	@return bool 
	 */
	public function is_datetime( $datetime ){
		return $this->conn->is_datetime($datetime);
	}
	
	// CRUD 
	/**
	 * @param  array campi 
	 * @param  array global $_COOKIE
	 * @return array ret  'ok' + 'record_id' | 'error' + 'message' 
	 */
	public function aggiungi( array $campi = []){
		// record_id               viene assegnato automaticamente pertanto non è in elenco 
		// consultatore_id         viene assegnato automaticamente 
		// ultima_modifica_record  viene assegnato automaticamente 
		// record_cancellabile_dal viene assegnato automaticamente, quasi sempre
		$create = 'INSERT INTO ' . self::nome_tabella
		. ' (  record_id_padre,  chiave,  valore ) VALUES '
		. ' ( :record_id_padre, :chiave, :valore )  '; // due spazi intenzionali
		if (isset($_COOKIE['consultatore_id'])){
			// si allunga la create
			$create = str_ireplace( ') VALUE', ',  consultatore_id ) VALUE', $create);
			$create = str_ireplace( ')  ', ', :consultatore_id )  ', $create);
		}
		if (isset($campi['record_cancellabile_dal'])){
			// si allunga la create
			$create = str_ireplace( ') VALUE', ',  record_cancellabile_dal ) VALUE', $create);
			$create = str_ireplace( ')  ', ', :record_cancellabile_dal )  ', $create);
		}

		// dati obbligatori
		$dbh = $this->conn; // a PDO object thru Database class
		if ($dbh === false){
			$ret = [
				'error'   => true, 
				'message' => __CLASS__ . ' ' . __FUNCTION__ 
				. ' Errore: Inserimento record senza connessione archivio per: ' 
				. self::nome_tabella 
			];
			return $ret;
		}
		// validazione 
		if (!isset($campi['record_id_padre'])){
			$ret = [
				"error"=> true, 
				"message" => __CLASS__ . ' ' . __FUNCTION__ 
				. " AlbumDettagli::record_id_padre deve essere intero unsigned. " 
			];
			return $ret;
		}
		$this->set_record_id_padre($campi['record_id_padre']);
		
		if (!isset($campi['chiave'])){
			$ret = [
				"error"=> true, 
				"message" => __CLASS__ . ' ' . __FUNCTION__ 
				. " AlbumDettagli::chiave deve essere valorizzato. " 
			];
			return $ret;
		}
		$this->set_chiave($campi['chiave']);

		if (!isset($campi['valore'])){
			$ret = [
				"error"=> true, 
				"message" => __CLASS__ . ' ' . __FUNCTION__ 
				. " AlbumDettagli::valore deve essere valorizzato. O lo volete cancellare? " 
			];
			return $ret;
		}
		$this->set_valore($campi['valore']);

		if (isset($_COOKIE['consultatore_id'])){
			$this->set_consultatore_id($_COOKIE['consultatore_id']);
		}
		if (isset($campi['record_cancellabile_dal'])){
			$this->set_record_cancellabile_dal($campi['record_cancellabile_dal']);
		}

		// azione
		if (!$dbh->inTransaction()) { $dbh->beginTransaction(); }
		try{
			$aggiungi = $dbh->prepare($create);
			$aggiungi->bindValue('record_id_padre', $this->record_id_padre);
			$aggiungi->bindValue('chiave',          $this->chiave);
			$aggiungi->bindValue('valore',          $this->valore);
			if (isset($_COOKIE['consultatore_id'])){
				$aggiungi->bindValue('consultatore_id', $this->consultatore_id);
			}
			if (isset($campi['record_cancellabile_dal'])){
				$aggiungi->bindValue('record_cancellabile_dal', $this->record_cancellabile_dal);
			}
			$aggiungi->execute();
			$record_id = $dbh->lastInsertID();
			$dbh->commit();

		} catch(\Throwable $th ){
			$dbh->rollBack();
			$ret = [
				'record_id' => 0,
				'error'   => true,
				'message' => __CLASS__ . ' ' . __FUNCTION__ 
				. ' ' . $th->getMessage() 
				. ' campi: ' . serialize($campi)
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
	} //aggiungi


	/**   
	 * @param   array $campi - deve contenere un $campi['query'] con una istruzione SQL SELECT
	 * @return  array $ret   'ok'|'error' + 'message'| data
	 */
	public function leggi(array $campi) : array {
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
				"error"=> true, 
				"message" => __CLASS__ . ' ' . __FUNCTION__ 
				. "Deve essere definita l'istruzione SELECT in ['query']: " 
				. serialize($campi)
			];
			return $ret;
		}
		// validazione
		if (isset($campi['record_id'])){
			$this->set_record_id($campi['record_id']);
		}
		if (isset($campi['record_id_padre'])){
			$this->set_record_id_padre($campi['record_id_padre']);
		}
		if (isset($campi['chiave'])){
			$this->set_chiave($campi['chiave']);
		}
		if (isset($campi['valore'])){
			$this->set_valore($campi['valore']);
		}
		// consultatore_id se presente è un campo come gli altri 
		if (isset($campi['consultatore_id'])){
			$this->set_consultatore_id($campi['consultatore_id']);
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
				$lettura->bindValue('record_id', $this->record_id , PDO::PARAM_INT); 
			}
			if (isset($campi['record_id_padre'])){
				$lettura->bindValue('record_id_padre', $this->record_id_padre , PDO::PARAM_INT); // gli altri campi sono tipo string 
			}
			if (isset($campi['chiave'])){
				$lettura->bindValue('chiave', $this->chiave); 
			}
			if (isset($campi['valore'])){
				$lettura->bindValue('valore', $this->valore); 
			}
			if (isset($campi['consultatore_id'])){
				$lettura->bindValue('consultatore_id', $this->consultatore_id, PDO::PARAM_INT); 
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
				"message" => __CLASS__ . ' ' . __FUNCTION__ 
				. ' ' . $th->getMessage() 
				. " campi: " . serialize($campi)
				. ' istruzione SQL: ' . $read
			];
			return $ret;
		}
		$numero = 0; // può esserci un $limite
		$dati_di_ritorno = []; // è sempre un array
		/*while( $record = $lettura->fetch() && ($conteggio < $limite) ){
			if ( $record === false ){
				break;
				}
		*/     
		while( $record = $lettura->fetch(PDO::FETCH_ASSOC) ){
			$dati_di_ritorno[] = $record;
			$numero++; 
		} // while
		$ret = [
			'ok'     => true,
			'numero' => $numero,
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
	public function modifica(array $campi) : array {
		// dati obbligatori 
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
		if (isset($campi['record_id'])){
			$this->set_record_id($campi['record_id']);
		}
		if (isset($campi['record_id_padre'])){
			$this->set_record_id_padre($campi['record_id_padre']);
		}
		if (isset($campi['chiave'])){
			$this->set_chiave($campi['chiave']);
		}
		if (isset($campi['valore'])){
			$this->set_valore($campi['valore']);
		}
		// non posso modificare d'ufficio il campo 
		// $campi['update'] per aggiungere consultatore_id
		// lasciando libera la scrittura esterna
		if (isset($campi['consultatore_id'])){
			$this->set_consultatore_id($campi['consultatore_id']);
		}
		if (isset($campi['ultima_modifica_record'])){
			$this->set_ultima_modifica_record($campi['ultima_modifica_record']);
		}
		if (isset($campi['record_cancellabile_dal'])){
			$this->set_record_cancellabile_dal($campi['record_cancellabile_dal']);
		}
		// fine dei controlli 
		$update = $campi['update'];

		// azione
		if (!$dbh->inTransaction()) { $dbh->beginTransaction(); }
		try {
			$aggiorna = $dbh->prepare($update);
			if (isset($campi['record_id'])){
				$aggiorna->bindValue('record_id', $this->record_id, PDO::PARAM_INT); 
			}
			if (isset($campi['record_id_padre'])){
				$aggiorna->bindValue('record_id_padre', $this->record_id_padre, PDO::PARAM_INT); 
			}
			if (isset($campi['chiave'])){
				$aggiorna->bindValue('chiave', $this->chiave); 
			}
			if (isset($campi['valore'])){
				$aggiorna->bindValue('valore', $this->valore); 
			}
			if (isset($campi['consultatore_id'])){
				$aggiorna->bindValue('consultatore_id', $this->consultatore_id, PDO::PARAM_INT); 
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
		// validazione
		if (isset($campi['record_id'])){
			$this->set_record_id($campi['record_id']);
		}
		if (isset($campi['record_id_padre'])){
			$this->set_record_id_padre($campi['record_id_padre']);
		}
		if (isset($campi['chiave'])){
			$this->set_chiave($campi['chiave']);
		}
		if (isset($campi['valore'])){
			$this->set_valore($campi['valore']);
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
		//
		$cancellazione = $campi['delete'];

		// azione
		if (!$dbh->inTransaction()) { $dbh->beginTransaction(); }
		try {
			$cancella = $dbh->prepare($cancellazione);
			if (isset($campi['record_id'])){
				$cancella->bindValue('record_id', $this->record_id, PDO::PARAM_INT); 
			}
			if (isset($campi['record_id_padre'])){
				$cancella->bindValue('record_id_padre', $this->record_id_padre, PDO::PARAM_INT); 
			}
			if (isset($campi['chiave'])){
				$cancella->bindValue('chiave', $this->chiave); 
			}
			if (isset($campi['valore'])){
				$cancella->bindValue('valore', $this->valore); 
			}
			if (isset($campi['consultatore_id'])){
				$cancella->bindValue('consultatore_id', $this->consultatore_id, PDO::PARAM_INT); 
			}
			if (isset($campi['ultima_modifica_record'])){
				$cancella->bindValue('ultima_modifica_record', $this->ultima_modifica_record); 
			}
			if (isset($campi['record_cancellabile_dal'])){
				$cancella->bindValue('record_cancellabile_dal', $this->record_cancellabile_dal); 
			}
			$cancella->execute();
			$dbh->commit();

		} catch( Exception $e) {
			$dbh->rollBack();

			$ret = [
					'error' => true,
					'message' => __CLASS__ . ' ' . __FUNCTION__  
					. ' ' . $e->getMessage() 
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


} // AlbumDettagli