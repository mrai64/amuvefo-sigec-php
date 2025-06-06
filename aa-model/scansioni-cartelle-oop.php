<?php
/**
 * @source /aa-model/scansioni-cartelle-oop.php 
 * @author Massimo Rainato <maxrainato@libero.it>
 * 
 * TODO sostituire scansioni_cartelle con cartelle_da_archiviare
 * TODO in quanto questa tabella viene popolata con le cartelle da inserire o aggiornare 
 * TODO in archivio. Aggiornare anche il manuale tecnico
 * TODO sostituire scansioni-cartelle cartelle-archiviare
 * 
 * La tabella scansioni_cartelle è un primo passo per l'archiviazione che,
 * dopo l'inserimento nel sito tramite ftp, raccoglie l'elenco delle cartelle 
 * che vanno lavorate, e caricare bella tabella del deposito cartelle e file in  
 * scansioni_disco. Dal deposito scansioni_disco in seguito si caricano album, 
 * fotografie video e i loro dettagli caricabili in automatico.
 * 
 * @see https://archivio.athesis77.it/tech/3-archivi-tabelle/3-12-scansioni_cartelle/ 
 *
 */

Class Cartelle extends DatabaseHandler {
	public $conn; // connessione 
	public const nome_tabella = 'scansioni_cartelle'; // self::nome_tabella oppure

	public const stato_da_fare      = '0 da fare';
	public const stato_in_corso     = '1 in corso';
	public const stato_completati   = '2 completati';
	public const stato_lavori_validi = [
		self::stato_da_fare,
		self::stato_in_corso,
		self::stato_completati
	];

	// elementi della tabella
	public $record_id; //                bigint(20) unsigned AUTO+ PRIMARY
	public $disco; //                    char(12) riferimento disco fisico MASTER
	public $percorso_completo; //        varchar(2000) - esagerato, max: 1506 = 250 * 6 + 6 '/'
	public $stato_lavori; //             enum 
	public $ultima_modifica_record; //   datetime data creazione record uso backup
	public $record_cancellabile_dal; //  datetime DEF '9999-12-31 23:59:59'
	
	/**
	* @param database PDO db connection 
	*/
	public function __construct(DatabaseHandler $dbh){
		$this->conn               = $dbh;

		$this->record_id          = 0; //  invalido 
		$this->disco              = ''; // invalido
		$this->percorso_completo  = ''; // invalido 
		$this->stato_lavori       = self::stato_da_fare; // da fare 
		$this->ultima_modifica_record   = $dbh->get_datetime_now();
		$this->record_cancellabile_dal  = $dbh->get_datetime_forever();
	} // __construct
		
	// GETTER 
	public function get_record_id() : int {
		return $this->record_id;
	}
	public function get_disco() : string {
		return $this->disco;
	}
	public function get_percorso_completo() : string {
		return $this->percorso_completo;
	}
	public function get_stato_lavori() : string {
		return $this->stato_lavori;
	}
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
	/**
	 * @param int unsigned int 
	 */
	public function set_record_id( int $record_id ){
		if ($record_id < 1){
			throw new Exception(__CLASS__ . ' ' . __FUNCTION__ 
			. ' Must be unsigned integer is : ' . $record_id );
		}
		$this->record_id = $record_id;
	}
	
	public function set_disco( string $disco ) {
		// validazione
		$chiave = htmlspecialchars(strip_tags($disco));
		$chiave = trim(mb_substr($chiave, 0, 12));
		$chiave = strtoupper($chiave);
		if ($chiave == ''){
			throw new Exception(__CLASS__ . ' ' . __FUNCTION__ 
			. ' disco Cannot be empty. ' );
		}
		$this->disco = $chiave;
	}
	
	public function set_percorso_completo( string $percorso_completo ) {
		// validazione
		$chiave = htmlspecialchars(strip_tags($percorso_completo));
		if (str_contains($chiave, URLBASE)){
			$chiave = str_ireplace(URLBASE , '/' , $chiave);
		}
		if (str_contains($chiave, ABSPATH)){
			$chiave = str_ireplace(ABSPATH , '/' , $chiave);
		}
		$chiave = trim(mb_substr($chiave, 0, 2000));
		if ($chiave == ''){
			throw new Exception(__CLASS__ . ' ' . __FUNCTION__ 
			. ' percorso_completo Cannot be empty. ' );
		}
		$this->percorso_completo = $chiave;
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
		$dbh = $this->conn;
		if (!($dbh->is_datetime($ultima_modifica_record))){
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
			. ' no for: '. $record_cancellabile_dal 
			. '. Must be a valid datetime format yyyy-mm-dd hh:mm:ss ');
		}
		$this->record_cancellabile_dal = $record_cancellabile_dal;
	}
	
	
	/**
	 * CREATE aggiungi 
	 * 
	 * @param  array $campi i campi da inserire
	 * @return array $ret 'ok' + record_id | 'error' + 'message' 
	 */
	public function aggiungi(array $campi) : array {
		// record_id               viene assegnato automaticamente 
		// stato_lavori            viene assegnato automaticamente
		// ultima_modifica_record  viene assegnato automaticamente 
		// record_cancellabile_dal viene assegnato automaticamente 
		// 
		$create = 'INSERT INTO ' . self::nome_tabella  
		. ' (  disco,  percorso_completo ) VALUES '
		. ' ( :disco, :percorso_completo )  ';

		// dati obbligatori
		$dbh = $this->conn; // a PDO object thru Database class

		if (!isset($campi['disco']) || $campi['disco'] == ''){
			$ret = [
				'error'=> true, 
				'message' => __CLASS__ . ' ' . __FUNCTION__ 
				. ' Inserimento non riuscito per campi mancanti : ' 
				. self::nome_tabella
				.' campi: ' . serialize($campi)
			];
			return $ret;
		}
		$this->set_disco($campi['disco']);

		if (!isset($campi['percorso_completo']) || $campi['percorso_completo'] == ''){
			$ret = [
				'error'=> true, 
				'message' => __CLASS__ . ' ' . __FUNCTION__ 
				. ' Inserimento non riuscito per campi mancanti : ' 
				. self::nome_tabella
				.' campi: ' . str_ireplace(';', '; ', serialize($campi))
			];
			return $ret;
		}
		$this->set_percorso_completo($campi['percorso_completo']);
		// Se viene inserito per il backup un record già cancellabile 
		// aggiungo il campo alla create
		if (isset($campi['record_cancellabile_dal'])){
			$create = str_ireplace(') V', '  record_cancellabile_dal) V', $create);
			$create = str_ireplace(')  ', ' :record_cancellabile_dal)  ', $create);
			$this->set_record_cancellabile_dal($campi['record_cancellabile_dal']);
		}

		if (!$dbh->inTransaction()) { $dbh->beginTransaction(); }		
		try {
			$aggiungi = $dbh->prepare($create);
			$aggiungi->bindValue('disco',             $this->get_disco());
			$aggiungi->bindValue('percorso_completo', $this->get_percorso_completo());	
			if (isset($campi['record_cancellabile_dal'])){
				$aggiungi->bindValue('record_cancellabile_dal', $this->get_record_cancellabile_dal());
			}	
			// eseguo insert 
			$aggiungi->execute();
			$record_id = $dbh->lastInsertId();
			$dbh->commit();
			$ret = [
				'ok'        => true, 
				'record_id' => $record_id,
				'message'   => __CLASS__ . ' ' . __FUNCTION__ 
				. ' Inserimento record effettuato, nuovo id: ' . $record_id 
			];
			return $ret;

		} catch (\Throwable $th) {
			$dbh->rollBack();

			$ret = [
				'record_id' => 0, 
				'error'     => true, 
				'message' => __CLASS__ . ' ' . __FUNCTION__ 
				. ' ' . $th->getMessage() 
				. ' campi: ' . str_ireplace(';', '; ', serialize($campi))
				. ' istruzione SQL: ' . $create 
			];
			return $ret;
		} // try catch
	} // aggiungi 
	
	
	/**
	 * READ leggi 
	 * 
	 * @param  array $campi - dev'essere presente un $campi['query'] con l'istruzione SQL
	 * @return array $ret 'ok' + 'numero' + 'data[]' | 'error' + 'message'
	 */
	public function leggi(array $campi) : array {
		// controllo parametri indispensabili 
		$dbh = $this->conn; // a PDO object thru Database class

		if (!isset($campi['query'])){
			$ret = [
				'error'=> true, 
				'message' => __CLASS__ . ' ' . __FUNCTION__ 
				. "Deve essere definita l'istruzione SELECT in ['query']: " 
				. ' campi: ' . str_ireplace(';', '; ', serialize($campi))
			];
			return $ret;
		}
		$read = $campi['query'];
		// convalida campi 
		if (isset($campi['record_id'])) {
			$this->set_record_id($campi['record_id']);
		}
		if (isset($campi['disco'])) {
			$this->set_disco($campi['disco']);
		}
		if (isset($campi['percorso_completo'])) {
			$this->set_percorso_completo($campi['percorso_completo']);
		}
		if (isset($campi['stato_lavori'])) {
			$this->set_stato_lavori($campi['stato_lavori']);
		}
		if (isset($campi['ultima_modifica_record'])) {
			$this->set_ultima_modifica_record($campi['ultima_modifica_record']);
		}
		if (isset($campi['record_cancellabile_dal'])){
			$this->set_record_cancellabile_dal($campi['record_cancellabile_dal']); 
		}

		try {
			$lettura=$dbh->prepare($read);
			if (isset($campi['record_id'])){
				$lettura->bindValue('record_id', $this->record_id, PDO::PARAM_INT); // gli altri campi sono tipo string 
			}
			if (isset($campi['disco'])){
				$lettura->bindValue('disco', $this->disco);  
			}
			if (isset($campi['percorso_completo'])){
				$lettura->bindValue('percorso_completo', $this->percorso_completo);  
			}
			if (isset($campi['stato_lavori'])){
				$lettura->bindValue('stato_lavori', $this->stato_lavori ); 
			}
			if (isset($campi['ultima_modifica_record'])){
				$lettura->bindValue('ultima_modifica_record', $this->ultima_modifica_record);  
			}
			if (isset($campi['record_cancellabile_dal'])){
				$lettura->bindValue('record_cancellabile_dal', $this->record_cancellabile_dal ); 
			}
			$lettura->execute();

		} catch (\Throwable $th) {
			//throw $th;
			$ret = [
				'error' => true,
				'message' => __CLASS__ . ' ' . __FUNCTION__ 
				. ' ' . $th->getMessage() 
				. ' campi: ' . str_ireplace(';', '; ', serialize($campi))
				. ' istruzione SQL: ' . $read
			];
			return $ret;
		}

		$numero = 0;
		$dati_di_ritorno = [];
		while($record = $lettura->fetch(PDO::FETCH_ASSOC)){
			$dati_di_ritorno[] = $record;
			$numero++;
		}

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
	public function modifica(array $campi ) : array {
		// dati obbligatori 
		$dbh = $this->conn; // a PDO object thru Database class

		if (!isset($campi['update'])){
			$ret = [
				'error'=> true, 
				'message' => __CLASS__ . ' ' . __FUNCTION__ 
				. ' Aggiornamento record senza UPDATE: ' 
				. str_ireplace(';', '; ', serialize($campi))
			];
			return $ret;
		}
		$update = $campi['update'];
		if (isset($campi['record_id'])) {
			$this->set_record_id($campi['record_id']);
		}
		if (isset($campi['disco'])) {
			$this->set_disco($campi['disco']);
		}
		if (isset($campi['percorso_completo'])) {
			$this->set_percorso_completo($campi['percorso_completo']);
		}
		if (isset($campi['stato_lavori'])){
			$this->set_stato_lavori($campi['stato_lavori']); 
		}
		if (isset($campi['ultima_modifica_record'])) {
			$this->set_ultima_modifica_record($campi['ultima_modifica_record']);
		}
		if (isset($campi['record_cancellabile_dal'])){
			$this->set_record_cancellabile_dal($campi['record_cancellabile_dal']); 
		}

		if (!$dbh->inTransaction()) { $dbh->beginTransaction(); }		
		try {
			$aggiorna=$dbh->prepare($update);
			if (isset($campi['record_id'])){
				$aggiorna->bindValue('record_id', $this->record_id, PDO::PARAM_INT); // gli altri campi sono tipo string 
			}
			if (isset($campi['disco'])){
				$aggiorna->bindValue('disco', $this->disco);  
			}
			if (isset($campi['percorso_completo'])){
				$aggiorna->bindValue('percorso_completo', $this->percorso_completo);  
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
			$dbh->rollBack(); 

			$ret = [
				'error' => true,
				'message' => __CLASS__ . ' ' . __FUNCTION__ 
				. ' Errore: ' . $th->getMessage()
				. ' campi: ' . str_ireplace(';', '; ', serialize($campi))
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
	 * DELETE - elimina 
	 * 
	 * $campi deve avere un campo DELETE che contiene una istruzione SQL 
	 * di cancellazione fisica - cancellazione soft-delete con modifica
	 * di record_cancellabile_dal 
	 * 
	 * @param  array  $campi 
	 * @return array  $ret 'ok' + message | 'error' + message 
	 */
	public function elimina(array $campi = []) : array {
		// campi obbligatori 
		$dbh = $this->conn; // a PDO object thru Database class

		if (!isset($campi['delete'])){
			$ret = [
				'error'   => true, 
				'message' => __CLASS__ . ' ' . __FUNCTION__ 
				. " Deve essere definita l'istruzione DELETE in ['delete']: " 
				. serialize($campi)
			];
			return $ret;
		}
		// verifiche campi passati, se ci sono
		$delete = $campi['delete'];
		if (isset($campi['record_id'])) {
			$this->set_record_id($campi['record_id']);
		}
		if (isset($campi['disco'])) {
			$this->set_disco($campi['disco']);
		}
		if (isset($campi['percorso_completo'])) {
			$this->set_percorso_completo($campi['percorso_completo']);
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
			//code...
			$cancella=$dbh->prepare($delete);
			if (isset($campi['record_id'])){
				$cancella->bindValue('record_id', $this->record_id, PDO::PARAM_INT); // gli altri campi sono tipo string 
			}
			if (isset($campi['disco'])){
				$cancella->bindValue('disco', $this->disco);  
			}
			if (isset($campi['percorso_completo'])){
				$cancella->bindValue('percorso_completo', $this->percorso_completo);  
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

		} catch (\Throwable $th) {
			//throw $th;
			$dbh->rollBack(); 

			$ret = [
				'error' => true,
				'message' => __CLASS__ . ' ' . __FUNCTION__ 
				. ' ' . $th->getMessage()
				. ' campi: ' . str_ireplace(';', '; ', serialize($campi))
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
	
} // class Cartelle
