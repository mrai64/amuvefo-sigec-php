<?php
/**
 * @source /aa-model/scansioni-cartelle-oop.php 
 * @author Massimo Rainato <maxrainato@libero.it>
 * 
 * La tabella scansioni_cartelle è un primo passo per l'archiviazione 
 * e dopo l'inserimento nel sito ftp raccoglie l'elenco delle carelle 
 * che vanno lavorate per caricar i record di cartelle e file nel deposito 
 * scansioni_disco. Da questo poi si caricano album, fotografie video e i loro dettagli 
 * caricabili in automatico
 * 
 * @see https://archivio.athesis77.it/tech/3-archivi-tabelle/3-12-scansioni_cartelle/ 
 *
 */

Class Cartelle {
	private $conn = false; // connessione 
	public const nomeTabella = 'scansioni_cartelle'; // self::nomeTabella oppure
	public const statoDaFare        = 0; //             Cartelle::nomeTabella
	public const statoLavoriInCorso = 1;
	public const statoCompletato    = 2;
	public const statiValidi = [
		self::statoDaFare,
		self::statoLavoriInCorso,
		self::statoCompletato
	];

	// 
	public $record_id; //                bigint 20 assegnato primary key
	public $disco; //                    char(12) riferimento disco fisico
	public $percorso_completo; //        varchar(2000) - esagerato, max: 1506 = 250 * 6 + 6 '/'
	public $stato_scansione; //          tinyint codice stato
	public $ultima_modifica_record; //         datetime data creazione record uso backup
	
	/**
	* @param database PDO db connection 
	*/
	public function __construct(DatabaseHandler $dbh){
		$this->conn               = $dbh;

		$this->record_id          = 0; // invalido 
		$this->disco              = ''; // invalido
		$this->percorso_completo  = ''; // invalido 
		$this->stato_scansione    = 0; // da fare 
		$this->ultima_modifica_record   = $dbh->get_datetime_now();
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
	public function get_stato_scansione() : int {
		return $this->stato_scansione;
	}
	public function get_ultima_modifica_record() : string {
		return $this->ultima_modifica_record;
	}
	
	// SETTER
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
	
	public function set_stato_scansione( int $stato_scansione ) {
		// validazione
		if (!in_array($stato_scansione, self::statiValidi)){
			throw new Exception(__CLASS__ . ' ' . __FUNCTION__ 
			. ' stato_scansione Cannot be out of valid set status. ' );
		}
		$this->stato_scansione = $stato_scansione;
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
	 * CREATE aggiungi 
	 * 
	 * @param  array $campi i campi da inserire
	 * @return array $ret 'ok' + record_id | 'error' + 'message' 
	 */
	public function aggiungi(array $campi) : array {
		// record_id               viene assegnato automaticamente 
		// stato_scansione         viene assegnato automaticamenteo
		// ultima_modifica_record        viene assegnato automaticamente 
		$create = 'INSERT INTO ' . self::nomeTabella  
		. ' (  disco,  percorso_completo ) VALUES '
		. ' ( :disco, :percorso_completo ) ';

		// dati obbligatori
		$dbh = $this->conn; // a PDO object thru Database class
		if ($dbh === false){
			$ret = [
				'error'=> true, 
				'message' => __CLASS__ . ' ' . __FUNCTION__ 
				. ' Inserimento record senza connessione archivio per: ' 
				. self::nomeTabella
			];
			return $ret;
		}

		if (!isset($campi['disco']) || $campi['disco'] == ''){
			$ret = [
				'error'=> true, 
				'message' => __CLASS__ . ' ' . __FUNCTION__ 
				. ' Inserimento non riuscito per campi mancanti : ' 
				. self::nomeTabella
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
				. self::nomeTabella
				.' campi: ' . serialize($campi)
			];
			return $ret;
		}
		$this->set_percorso_completo($campi['percorso_completo']);
		$dbh->beginTransaction();
		try {
			$aggiungi = $dbh->prepare($create);
			$aggiungi->bindValue('disco',             $this->get_disco());
			$aggiungi->bindValue('percorso_completo', $this->get_percorso_completo());
			// eseguo insert 
			$aggiungi->execute();
			$record_id = $dbh->lastInsertId();
			$dbh->commit();
		} catch (\Throwable $th) {
			$dbh->rollBack();
			$ret = [
				'record_id' => 0, 
				'error'     => true, 
				'message' => __CLASS__ . ' ' . __FUNCTION__ 
				. ' ' . $th->getMessage() 
				. ' campi: ' . serialize($campi)
				. ' istruzione SQL: ' . $create 
			];
			return $ret;
		}

		$ret = [
			'ok' => true, 
			'record_id' => $record_id
		];
		return $ret;
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
		if ($dbh === false){
			$ret = [
				'error'=> true, 
				'message' => 'Lettura record senza connessione archivio per: ' 
				. self::nomeTabella 
			];
			return $ret;
		}
		if (!isset($campi['query'])){
			$ret = [
				'error'=> true, 
				'message' => __CLASS__ . ' ' . __FUNCTION__ 
				. "Deve essere definita l'istruzione SELECT in ['query']: " 
				. serialize($campi)
			];
			return $ret;
		}
		$read = $campi['query'];
		if (isset($campi['record_id'])) {
			$this->set_record_id($campi['record_id']);
		}
		if (isset($campi['disco'])) {
			$this->set_disco($campi['disco']);
		}
		if (isset($campi['percorso_completo'])) {
			$this->set_percorso_completo($campi['percorso_completo']);
		}
		if (isset($campi['stato_scansione'])) {
			$this->set_stato_scansione($campi['stato_scansione']);
		}
		if (isset($campi['ultima_modifica_record'])) {
			$this->set_ultima_modifica_record($campi['ultima_modifica_record']);
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
			if (isset($campi['stato_scansione'])){
				$lettura->bindValue('stato_scansione', $this->stato_scansione, PDO::PARAM_INT);  
			}
			if (isset($campi['record_id'])){
				$lettura->bindValue('record_id', $this->record_id, PDO::PARAM_INT); // gli altri campi sono tipo string 
			}
			if (isset($campi['ultima_modifica_record'])){
				$lettura->bindValue('ultima_modifica_record', $this->ultima_modifica_record);  
			}
			$lettura->execute();

		} catch (\Throwable $th) {
			//throw $th;
			$ret = [
				'error' => true,
				'message' => __CLASS__ . ' ' . __FUNCTION__ 
				. ' ' . $th->getMessage() . ' campi: ' . serialize($campi)
				. ' istruzione SQL: ' . $read
			];
			return $ret;
		}
		$conteggio = 0;
		$dati_di_ritorno = [];
		while($record = $lettura->fetch(PDO::FETCH_ASSOC)){
			$dati_di_ritorno[] = $record;
			$conteggio++;
		}
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
	public function modifica(array $campi ) : array {
		// dati obbligatori 
		$dbh = $this->conn; // a PDO object thru Database class
		if ($dbh === false){
			$ret = [
				'error'=> true, 
				'message' => __CLASS__ . ' ' . __FUNCTION__ 
				. ' Modifica senza connessione archivio per: ' 
				. self::nomeTabella 
			];
			return $ret;
		}
		if (!isset($campi['update'])){
			$ret = [
				'error'=> true, 
				'message' => __CLASS__ . ' ' . __FUNCTION__ 
				. ' Aggiornamento record senza UPDATE: ' 
				. serialize($campi) 
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
		if (isset($campi['stato_scansione'])) {
			$this->set_stato_scansione($campi['stato_scansione']);
		}
		if (isset($campi['ultima_modifica_record'])) {
			$this->set_ultima_modifica_record($campi['ultima_modifica_record']);
		}
		$dbh->beginTransaction();
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
			if (isset($campi['stato_scansione'])){
				$aggiorna->bindValue('stato_scansione', $this->stato_scansione, PDO::PARAM_INT); // gli altri campi sono tipo string 
			}
			if (isset($campi['ultima_modifica_record'])){
				$aggiorna->bindValue('ultima_modifica_record', $this->ultima_modifica_record);  
			}
			$aggiorna->execute();
			$dbh->commit();

		} catch( \Throwable $th ){
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
			'ok' => true,
			'message' => 'Aggiornamento eseguito'
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
	public function elimina(array $campi = []) : array {
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
		if (isset($campi['stato_scansione'])) {
			$this->set_stato_scansione($campi['stato_scansione']);
		}
		if (isset($campi['ultima_modifica_record'])) {
			$this->set_ultima_modifica_record($campi['ultima_modifica_record']);
		}
		$dbh->beginTransaction();
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
			if (isset($campi['stato_scansione'])){
				$cancella->bindValue('stato_scansione', $this->stato_scansione, PDO::PARAM_INT); // gli altri campi sono tipo string 
			}
			if (isset($campi['ultima_modifica_record'])){
				$cancella->bindValue('ultima_modifica_record', $this->ultima_modifica_record);  
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
				. ' campi: ' . serialize($campi)
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
