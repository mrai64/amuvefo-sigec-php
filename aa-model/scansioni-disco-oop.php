<?php
/**
 * @source /aa-model/scansioni-disco-oop.php 
 * @author Massimo Rainato <maxrainato@libero.it>
 * 
 * Accesso CRUD alla tabella scansioni_disco.
 * scansioni_disco contiene un insieme delimitato di file 
 * e cartelle, ricavati dalla scansione del disco online.
 * Non tutti i file che sono nella cartella vanno a finire 
 * in questo archivio, per esempio 
 * non ci vanno i file Thumbs.db per l'estensione db 
 * o i .DS_Store i ._nomefile.jpg perché iniziano con il '.'  
 * 
 * @see https://archivio.athesis77.it/tech/3-archivi-tabelle/3-13-scansioni_disco/ Documentation for table scansioni_disco and class ScansioniDisco
 * 
 * dipendenze: Classe DatabaseHandler / PDO (mysql) 
 *
 * SELECT * FROM `scansioni_disco` 
 * WHERE record_cancellabile_dal = '9999-12-31 23:59:59' 
 * order by `disco`,`livello1`,`livello2`,`livello3`,
 * `livello4`,`livello5`,`livello6`,`nome_file`,
 * `record_id` DESC
 *
 */

Class ScansioniDisco {
	private $conn = false; // connessione 
	public const nome_tabella     = 'scansioni_disco';
	public const stato_da_fare    = '0 da fare';
	public const stato_in_corso   = '1 in corso';
	public const stato_completati = '2 completati';
	public const stato_lavori_validi = [
		self::stato_da_fare,
		self::stato_in_corso,
		self::stato_completati
	];
	private $limite_record = 20; // TODO predefinito per paginazione 

	// 
	public $record_id; //                bigint 20 assegnato primary key
	public $disco; //                    char(12) riferimento disco fisico
	public $livello1; //                 varchar(250) cartelle primo   livello - Sala
	public $livello2; //                 varchar(250) cartelle secondo livello
	public $livello3; //                 varchar(250) cartelle terzo   livello
	public $livello4; //                 varchar(250) cartelle quarto  livello
	public $livello5; //                 varchar(250) cartelle quinto  livello
	public $livello6; //                 varchar(250) cartelle sesto   livello
	public $nome_file; //                varchar(250) per le cartelle '/'
	public $estensione; //               char(6) per le cartelle ''
	public $modificato_il; //            datetime data ora ultima modifica 
	public $codice_verifica; //          char(32) md5 del contenuto a uso verifiche future
	public $tinta_rgb; //                char(6) codice colore personalizzabile
	public $stato_lavori; //             enum uso interno
	public $ultima_modifica_record;         // datetime data creazione record uso backup
	public $record_da_esaminare;      // datetime per esaminare il record  
	public $record_cancellabile_dal;  // datetime per selezionare e cancellare 
	
	/**
	* @param database PDO db connection 
	*/
	public function __construct(DatabaseHandler $dbh){
		$this->conn             = $dbh;
		$this->limite_record    = 20; 

		$this->record_id        = 0;  // invalido 
		$this->disco            = '';
		$this->livello1         = '';
		$this->livello2         = '';
		$this->livello3         = '';
		$this->livello4         = '';
		$this->livello5         = '';
		$this->livello6         = '';
		$this->nome_file        = '';
		$this->estensione       = '';
		$this->modificato_il    = $dbh->get_datetime_now();
		$this->codice_verifica  = '';
		$this->tinta_rgb        = '';
		$this->stato_lavori     = self::stato_da_fare;
		$this->ultima_modifica_record        = $dbh->get_datetime_now();
		$this->record_da_esaminare     = $dbh->get_datetime_forever(); // futuro - da esaminare
		$this->record_cancellabile_dal = $dbh->get_datetime_forever(); // futuro - valido 
	} // __construct
		
	// GETTER 
	public function get_record_id() : int {
		return $this->record_id;
	}
	public function get_disco() : string {
		return $this->disco;
	}
	public function get_livello1() : string {
		return $this->livello1;
	}
	public function get_livello2() : string {
		return $this->livello2;
	}
	public function get_livello3() : string {
		return $this->livello3;
	}
	public function get_livello4() : string {
		return $this->livello4;
	}
	public function get_livello5() : string {
		return $this->livello5;
	}
	public function get_livello6() : string {
		return $this->livello6;
	}
	public function get_nome_file() : string {
		return $this->nome_file;
	}
	public function get_estensione() : string {
		return $this->estensione;
	}
	public function get_modificato_il() : string {
		return $this->modificato_il;
	}
	public function get_codice_verifica() : string {
		return $this->codice_verifica;
	}
	public function get_tinta_rgb() : string {
		return $this->tinta_rgb;
	}
	public function get_stato_lavori() : string {
		return $this->stato_lavori;
	}
	public function get_ultima_modifica_record() : string {
		return $this->ultima_modifica_record;
	}
	public function get_record_da_esaminare() : string {
		return $this->record_da_esaminare;
	}
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
	
	public function set_livello1( string $livello1 ) {
		// validazione
		$chiave = htmlspecialchars(strip_tags($livello1));
		$chiave = trim(mb_substr($chiave, 0, 250));
		if ($chiave == ''){
			throw new Exception(__CLASS__ . ' ' . __FUNCTION__ 
			. ' livello1 Cannot be empty. ' );
		}
		$this->livello1 = $chiave;
	}
	
	public function set_livello2( string $livello2 ) {
		// validazione
		$chiave = htmlspecialchars(strip_tags($livello2));
		$chiave = trim(mb_substr($chiave, 0, 250));
		$this->livello2 = $chiave;
	}
	
	public function set_livello3( string $livello3 ) {
		// validazione
		$chiave = htmlspecialchars(strip_tags($livello3));
		$chiave = trim(mb_substr($chiave, 0, 250));
		$this->livello3 = $chiave;
	}
	
	public function set_livello4( string $livello4 ) {
		// validazione
		$chiave = htmlspecialchars(strip_tags($livello4));
		$chiave = trim(mb_substr($chiave, 0, 250));
		$this->livello4 = $chiave;
	}
	
	public function set_livello5( string $livello5 ) {
		// validazione
		$chiave = htmlspecialchars(strip_tags($livello5));
		$chiave = trim(mb_substr($chiave, 0, 250));
		$this->livello5 = $chiave;
	}
	
	public function set_livello6( string $livello6 ) {
		// validazione
		$chiave = htmlspecialchars(strip_tags($livello6));
		$chiave = trim(mb_substr($chiave, 0, 250));
		$this->livello6 = $chiave;
	}
	
	public function set_nome_file( string $nome_file ) {
		// validazione
		$chiave = htmlspecialchars(strip_tags($nome_file));
		$chiave = trim(mb_substr($chiave, 0, 250));
		if ($chiave == ""){
			throw new Exception(__CLASS__ . ' ' . __FUNCTION__ 
			. ' nome_file Cannot be empty. ' );
		}
		$this->nome_file = $chiave;
	}
	
	public function set_estensione( string $estensione ) {
		// validazione
		$chiave = htmlspecialchars(strip_tags($estensione));
		$chiave = trim(mb_substr($chiave, 0, 6));
		$chiave = strtolower($chiave);
		$this->estensione = $chiave;
	}
	
	/**
	 * @param string datetime yyyy-mm-dd hh:mm:ss
	 */
	public function set_modificato_il( string $modificato_il ){
		if (!($this->conn->is_datetime($modificato_il))){
			throw new Exception(__CLASS__ .' '. __FUNCTION__ 
			. ' no for: '. $modificato_il 
			. '. Must be a valid datetime format yyyy-mm-dd hh:mm:ss ');
		}
		$this->modificato_il = $modificato_il;
	}
	
	public function set_codice_verifica( string $codice_verifica ) {
		// validazione
		$chiave = htmlspecialchars(strip_tags($codice_verifica));
		$chiave = trim(mb_substr($codice_verifica, 0, 32));
		if ($chiave == ""){
			throw new Exception(__CLASS__ . ' ' . __FUNCTION__ 
			. ' codice_verifica Cannot be empty. ' );
		}
		$this->codice_verifica = $chiave;
	}
	
	public function set_tinta_rgb( string $tinta_rgb ) {
		// validazione
		if (preg_match('/[0-9a-fA-F]{6}/', $tinta_rgb, $match) === 1){
			$tinta_rgb = $match[0];
		} else {
			throw new Exception(__CLASS__ . ' ' . __FUNCTION__ 
			. ' tinta_rgb Cannot be empty. ' );
		}
		$this->tinta_rgb = $tinta_rgb;
	}
	
	function set_stato_lavori( string $stato_lavori) {
		//
		$chiave = strtolower($stato_lavori);
		if (!in_array($chiave, self::stato_lavori_validi)){
			throw new Exception(__CLASS__ . ' ' . __FUNCTION__ 
			. ' stato_lavori cannot be '.$stato_lavori.'. ' );
		}
		$this->stato_lavori = $chiave;
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
	public function set_record_da_esaminare( string $record_da_esaminare ){
		if (!($this->conn->is_datetime($record_da_esaminare))){
			throw new Exception(__CLASS__ .' '. __FUNCTION__ 
			. ' no for: '. $record_da_esaminare 
			. '. Must be a valid datetime format yyyy-mm-dd hh:mm:ss ');
		}
		$this->record_da_esaminare = $record_da_esaminare;
	}
	/**
	 * @param string datetime yyyy-mm-dd hh:mm:ss
	 */
	public function set_record_cancellabile_dal( string $record_cancellabile_dal ){
		if (!($this->conn->is_datetime($record_cancellabile_dal))){
			throw new Exception(__CLASS__ .' '. __FUNCTION__ 
			. ' no for: '. $record_cancellabile_dal 
			. '. Must be a valid datetime format yyyy-mm-dd hh:mm:ss ');
		}
		$this->record_cancellabile_dal = $record_cancellabile_dal;
	}
	
	
	
	/**
	 * CREATE aggiungi 
	 * @param  array $campi i campi da inserire
	 * @return array $ret 'ok' + record_id | 'error' + 'message' 
	 */
	public function aggiungi(array $campi = []) : array {
		/* 
		record_id               viene assegnato automaticamente 
		stato_lavori            viene assegnato automaticamente 
		ultima_modifica_record  viene assegnato automaticamente 
		record_cancellabile_dal viene assegnato automaticamente
		*/
		$create = 'INSERT INTO ' . self::nome_tabella 
		. ' (  disco,  livello1,  livello2,  livello3,  livello4,  livello5,  livello6,'
		. '  nome_file,  estensione,  modificato_il,  codice_verifica,  tinta_rgb ) VALUE '
		. " ( :disco, :livello1, :livello2, :livello3, :livello4, :livello5, :livello6,"
		. " :nome_file, :estensione, :modificato_il, :codice_verifica, :tinta_rgb ) ";
		// campi necessari
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
		if (!isset($campi['disco']) || $campi['disco'] == ''){
			$ret = [
				'error'=> true, 
				'message' => __CLASS__ . ' ' . __FUNCTION__ 
				. ' Inserimento non riuscito per campi mancanti : ' 
				. ' istruzione sql: ' . $create
				. ' campi: ' . serialize($campi)
			];
			return $ret;
		}
		$this->set_disco($campi['disco']);
		if (!isset($campi['livello1']) || $campi['livello1'] == ''){
			$ret = [
				'error'=> true, 
				'message' => __CLASS__ . ' ' . __FUNCTION__ 
				. ' Inserimento non riuscito per campi mancanti : ' 
				. ' istruzione sql: ' . $create
				. ' campi: ' . serialize($campi)
			];
			return $ret;
		}
		$this->set_livello1($campi['livello1']);
		if (isset($campi['livello2'])){
			$this->set_livello2($campi['livello2']);
		}
		if (isset($campi['livello3'])){
			$this->set_livello3($campi['livello3']);
		}
		if (isset($campi['livello4'])){
			$this->set_livello4($campi['livello4']);
		}
		if (isset($campi['livello5'])){
			$this->set_livello5($campi['livello5']);
		}
		if (isset($campi['livello6'])){
			$this->set_livello6($campi['livello6']);
		}
		if (isset($campi['nome_file'])){
			$this->set_nome_file($campi['nome_file']);
		}
		if (isset($campi['estensione'])){
			$this->set_estensione($campi['estensione']);
		}
		if (isset($campi['modificato_il'])){
			$this->set_modificato_il($campi['modificato_il']);
		}
		if (isset($campi['codice_verifica'])){
			$this->set_codice_verifica($campi['codice_verifica']);
		}
		if (isset($campi['tinta_rgb'])){
			$this->set_tinta_rgb($campi['tinta_rgb']);
		}
		if (!$dbh->inTransaction()) { $dbh->beginTransaction(); }
		try {
			$aggiungi = $this->conn->prepare($create); 
			$aggiungi->bindValue('disco',           $this->disco           ); 
			$aggiungi->bindValue('livello1',        $this->livello1        ); 
			$aggiungi->bindValue('livello2',        $this->livello2        );
			$aggiungi->bindValue('livello3',        $this->livello3        ); 
			$aggiungi->bindValue('livello4',        $this->livello4        );
			$aggiungi->bindValue('livello5',        $this->livello5        ); 
			$aggiungi->bindValue('livello6',        $this->livello6        );
			$aggiungi->bindValue('nome_file',       $this->nome_file       ); 
			$aggiungi->bindValue('estensione',      $this->estensione      );
			$aggiungi->bindValue('modificato_il',   $this->modificato_il   );
			$aggiungi->bindValue('codice_verifica', $this->codice_verifica );
			$aggiungi->bindValue('tinta_rgb',       $this->tinta_rgb       );
		// eseguo insert 
			$aggiungi->execute();
			$record_id = $this->conn->lastInsertId();
			$dbh->commit();

		} catch (\Throwable $th) {
			$dbh->rollBack(); 

			$ret = [
				"record_id" => 0, 
				'error'     => true, 
				'message' => __CLASS__ . ' ' . __FUNCTION__ 
				. ' ' . $th->getMessage() 
				. ' istruzione SQL: ' . $create 
				. ' campi: ' . serialize($campi)
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
	 * READ 
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
				. self::nome_tabella
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
		if (isset($campi['record_id'])){
			$this->set_record_id($campi['record_id']);
		}
		if (isset($campi['disco'])){
			$this->set_disco($campi['disco']);
		}
		if (isset($campi['livello1'])){
			$this->set_livello1($campi['livello1']);
		}
		if (isset($campi['livello2'])){
			$this->set_livello2($campi['livello2']);
		}
		if (isset($campi['livello3'])){
			$this->set_livello3($campi['livello3']);
		}
		if (isset($campi['livello4'])){
			$this->set_livello4($campi['livello4']);
		}
		if (isset($campi['livello5'])){
			$this->set_livello5($campi['livello5']);
		}
		if (isset($campi['livello6'])){
			$this->set_livello6($campi['livello6']);
		}
		if (isset($campi['nome_file'])){
			$this->set_nome_file($campi['nome_file']);
		}
		if (isset($campi['estensione'])){
			$this->set_estensione($campi['estensione']);
		}
		if (isset($campi['modificato_il'])){
			$this->set_modificato_il($campi['modificato_il']);
		}
		if (isset($campi['codice_verifica'])){
			$this->set_codice_verifica($campi['codice_verifica']);
		}
		if (isset($campi['tinta_rgb'])){
			$this->set_tinta_rgb($campi['tinta_rgb']);
		}
		if (isset($campi['stato_lavori'])){
			$this->set_stato_lavori($campi['stato_lavori']);
		}
		if (isset($campi['ultima_modifica_record'])){
			$this->set_ultima_modifica_record($campi['ultima_modifica_record']);
		}
		if (isset($campi['record_da_esaminare'])){
			$this->set_record_da_esaminare($campi['record_da_esaminare']);
		}
		if (isset($campi['record_cancellabile_dal'])){
			$this->set_record_cancellabile_dal($campi['record_cancellabile_dal']);
		}
		// paginazione
		try {
			//code...
			$lettura = $dbh->prepare($read);
			if (isset($campi['record_id'])){
				$lettura->bindValue('record_id', $this->get_record_id(), PDO::PARAM_INT); // gli altri campi sono tipo string 
			}
			if (isset($campi['disco'])){
				$lettura->bindValue('disco', $this->get_disco());  
			}
			if (isset($campi['livello1'])){
				$lettura->bindValue('livello1', $this->get_livello1()); 
			}
			if (isset($campi['livello2'])){
				$lettura->bindValue('livello2', $this->get_livello2() ); 
			}
			if (isset($campi['livello3'])){
				$lettura->bindValue('livello3', $this->get_livello3() ); 
			}
			if (isset($campi['livello4'])){
				$lettura->bindValue('livello4', $this->get_livello4() ); 
			}
			if (isset($campi['livello5'])){
				$lettura->bindValue('livello5', $this->get_livello5() ); 
			}
			if (isset($campi['livello6'])){
				$lettura->bindValue('livello6', $this->get_livello6() ); 
			}
			if (isset($campi['nome_file'])){
				$lettura->bindValue('nome_file', $this->get_nome_file() ); 
			}
			if (isset($campi['estensione'])){
				$lettura->bindValue('estensione', $this->get_estensione() ); 
			}
			if (isset($campi['modificato_il'])){
				$lettura->bindValue('modificato_il', $this->get_modificato_il() ); 
			}
			// codice_verifica - manca perché non va mai aggiornato
			if (isset($campi['tinta_rgb'])){
				$lettura->bindValue('tinta_rgb', $this->get_tinta_rgb() ); 
			}
			if (isset($campi['stato_lavori'])){
				$lettura->bindValue('stato_lavori', $this->get_stato_lavori()); 
			}
			if (isset($campi['ultima_modifica_record'])){
				$lettura->bindValue('ultima_modifica_record', $this->get_ultima_modifica_record()); 
			}
			if (isset($campi['record_cancellabile_dal'])){
				$lettura->bindValue('record_cancellabile_dal', $this->get_record_cancellabile_dal()); 
			}
			// campi per paginazione 
			$lettura->execute();
			
		} catch (\Throwable $th) {
			//throw $th;
			$ret = [
				'error' => true,
				'message' => __CLASS__ . ' ' . __FUNCTION__ 
				. ' ' . $th->getMessage() 
				. ' campi: ' . serialize($campi) . '<br>'
				. ' istruzione SQL: ' . $read
			];
			return $ret;
		} // catch 
		
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
		
		/*  paginazione 
		
		
				$conteggio = 0;
				$dati_di_ritorno = [];
				while(($record = $lettura->fetch(PDO::FETCH_ASSOC)) && ($conteggio < $limite_record)){
					if ($record === false) {
						break;
					}
					$dati_di_ritorno[] = $record;
					$conteggio++;
				}
				$ret = [
				'ok'     => true,
				'numero' => $conteggio,
				'data'   => $dati_di_ritorno 
				];
				// Se il verso è "indietro" fare un loop a gambero per girare 
				if ($direzione == 'indietro'){
					$ret['data'] = array_reverse($ret['data'], true);
				}
				return $ret;
			
			*/

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
	public function modifica(array $campi = []) : array {
		// dati obbligatori 
		$dbh = $this->conn; // a PDO object thru Database class
		if ($dbh === false){
			$ret = [
				'error'=> true, 
				'message' => __CLASS__ . ' ' . __FUNCTION__ 
				. ' Modifica senza connessione archivio per: ' 
				. self::nome_tabella
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
		if (isset($campi['livello1'])) {
			$this->set_livello1($campi['livello1']);
		}
		if (isset($campi['livello2'])) {
			$this->set_livello2($campi['livello2']);
		}
		if (isset($campi['livello3'])) {
			$this->set_livello3($campi['livello3']);
		}
		if (isset($campi['livello4'])) {
			$this->set_livello4($campi['livello4']);
		}
		if (isset($campi['livello5'])) {
			$this->set_livello5($campi['livello5']);
		}
		if (isset($campi['livello6'])) {
			$this->set_livello6($campi['livello6']);
		}
		if (isset($campi['nome_file'])) {
			$this->set_nome_file($campi['nome_file']);
		}
		if (isset($campi['estensione'])) {
			$this->set_estensione($campi['estensione']);
		}
		if (isset($campi['modificato_il'])) {
			$this->set_modificato_il($campi['modificato_il']);
		}
		if (isset($campi['codice_verifica'])) {
			$this->set_codice_verifica($campi['codice_verifica']);
		}
		if (isset($campi['tinta_rgb'])) {
			$this->set_tinta_rgb($campi['tinta_rgb']);
		}
		if (isset($campi['stato_lavori'])) {
			$this->set_stato_lavori($campi['stato_lavori']);
		}
		if (isset($campi['ultima_modifica_record'])) {
			$this->set_ultima_modifica_record($campi['ultima_modifica_record']);
		}
		if (isset($campi['record_da_esaminare'])) {
			$this->set_record_da_esaminare($campi['record_da_esaminare']);
		}
		if (isset($campi['record_cancellabile_dal'])) {
			$this->set_record_cancellabile_dal($campi['record_cancellabile_dal']);
		}
		if (!$dbh->inTransaction()) { $dbh->beginTransaction(); }
		
		try {
			//code...
			$aggiorna=$dbh->prepare($update);		
			if (isset($campi['record_id'])) { 
				$aggiorna->bindValue('record_id', $campi['record_id']); 
			}
			if (isset($campi['disco']))     { $aggiorna->bindValue('disco', $campi['disco']); }
			if (isset($campi['livello1']))  { $aggiorna->bindValue('livello1', $campi['livello1']); }
			if (isset($campi['livello2']))  { $aggiorna->bindValue('livello2', $campi['livello2']); }
			if (isset($campi['livello3']))  { $aggiorna->bindValue('livello3', $campi['livello3']); }
			if (isset($campi['livello4']))  { $aggiorna->bindValue('livello4', $campi['livello4']); }
			if (isset($campi['livello5']))  { $aggiorna->bindValue('livello5', $campi['livello5']); }
			if (isset($campi['livello6']))  { $aggiorna->bindValue('livello6', $campi['livello6']); }
			if (isset($campi['nome_file']))       { $aggiorna->bindValue('nome_file', $campi['nome_file']); }
			if (isset($campi['estensione']))      { $aggiorna->bindValue('estensione', $campi['estensione']); }
			if (isset($campi['modificato_il']))   { $aggiorna->bindValue('modificato_il', $campi['modificato_il']); }
			if (isset($campi['codice_verifica'])) { $aggiorna->bindValue('codice_verifica', $campi['codice_verifica']); }
			if (isset($campi['tinta_rgb']))               { $aggiorna->bindValue('tinta_rgb', $campi['tinta_rgb']); }
			if (isset($campi['stato_lavori']))            { $aggiorna->bindValue('stato_lavori', $campi['stato_lavori']); }
			if (isset($campi['ultima_modifica_record']))        { $aggiorna->bindValue('ultima_modifica_record', $campi['ultima_modifica_record']); }
			if (isset($campi['record_da_esaminare']))     { $aggiorna->bindValue('record_da_esaminare', $campi['record_da_esaminare']); }
			if (isset($campi['record_cancellabile_dal'])) { 
				$aggiorna->bindValue('record_cancellabile_dal', $campi['record_cancellabile_dal']); 
			}
			$aggiorna->execute();
			$dbh->commit();

		} catch (\Throwable $th) {
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
	public function elimina(array $campi) : array {
		// campi obbligatori 
		$dbh = $this->conn; // a PDO object thru Database class
		if ($dbh === false){
			$ret = [
				'error'   => true, 
				'message' => 'La cancellazione di record '
				. 'non si può fare senza connessione archivio '
				. 'per: ' . self::nome_tabella  
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
		if (isset($campi['livello1'])) {
			$this->set_livello1($campi['livello1']);
		}
		if (isset($campi['livello2'])) {
			$this->set_livello2($campi['livello2']);
		}
		if (isset($campi['livello3'])) {
			$this->set_livello3($campi['livello3']);
		}
		if (isset($campi['livello4'])) {
			$this->set_livello4($campi['livello4']);
		}
		if (isset($campi['livello5'])) {
			$this->set_livello5($campi['livello5']);
		}
		if (isset($campi['livello6'])) {
			$this->set_livello6($campi['livello6']);
		}
		if (isset($campi['nome_file'])) {
			$this->set_nome_file($campi['nome_file']);
		}
		if (isset($campi['estensione'])) {
			$this->set_estensione($campi['estensione']);
		}
		if (isset($campi['modificato_il'])) {
			$this->set_modificato_il($campi['modificato_il']);
		}
		if (isset($campi['codice_verifica'])) {
			$this->set_codice_verifica($campi['codice_verifica']);
		}
		if (isset($campi['tinta_rgb'])) {
			$this->set_tinta_rgb($campi['tinta_rgb']);
		}
		if (isset($campi['stato_lavori'])) {
			$this->set_stato_lavori($campi['stato_lavori']);
		}
		if (isset($campi['ultima_modifica_record'])) {
			$this->set_ultima_modifica_record($campi['ultima_modifica_record']);
		}
		if (isset($campi['record_da_esaminare'])) {
			$this->set_record_da_esaminare($campi['record_da_esaminare']);
		}
		if (isset($campi['record_cancellabile_dal'])) {
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
			if (isset($campi['livello1'])){	$cancella->bindValue('livello1', $this->get_livello1());	}
			if (isset($campi['livello2'])){	$cancella->bindValue('livello2', $this->get_livello2());	}
			if (isset($campi['livello3'])){	$cancella->bindValue('livello3', $this->get_livello3());	}
			if (isset($campi['livello4'])){	$cancella->bindValue('livello4', $this->get_livello4());	}
			if (isset($campi['livello5'])){	$cancella->bindValue('livello5', $this->get_livello5());	}
			if (isset($campi['livello6'])){	$cancella->bindValue('livello6', $this->get_livello6());	}
			if (isset($campi['nome_file'])){	$cancella->bindValue('nome_file', $this->get_nome_file());	}
			if (isset($campi['estensione'])){	$cancella->bindValue('estensione', $this->get_estensione());	}
			if (isset($campi['modificato_il'])){	$cancella->bindValue('modificato_il', $this->get_modificato_il());	}
			if (isset($campi['codice_verifica'])){	$cancella->bindValue('codice_verifica', $this->get_codice_verifica());	}
			if (isset($campi['tinta_rgb'])){	$cancella->bindValue('tinta_rgb', $this->get_tinta_rgb());	}
			if (isset($campi['stato_lavori'])){	$cancella->bindValue('stato_lavori', $this->get_stato_lavori());	}
			if (isset($campi['ultima_modifica_record'])){	$cancella->bindValue('ultima_modifica_record', $this->get_ultima_modifica_record());	}
			if (isset($campi['record_da_esaminare'])){	$cancella->bindValue('record_da_esaminare', $this->get_record_da_esaminare());	}
			if (isset($campi['record_cancellabile_dal'])){	$cancella->bindValue('record_cancellabile_dal', $this->get_record_cancellabile_dal());	}
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
			'message' => "Sono stati cancellati " 
			. $cancella->rowCount() ." record(s)"
		];
		return $ret;
	} // elimina	
	


	/**
	 * SET_STATO_LAVORI 
	 * 
	 * @param  int   $record_id
	 * @param  string $stato_lavori 
	 */
	public function set_stato_lavori_in_scansioni_disco(int $record_id, string $stato_lavori) : array {
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
	} // set_stato_lavori_in_scansioni_disco

	/**
	 * @param  string 'folder/folder/folder/' 
	 * @return int    $record_id | 0 = not found 
	 */
	
	public function get_record_id_da_percorso(string $percorso) : int {
		$dbh    = $this->conn;
		if ($dbh === false){
			throw new Exception(__CLASS__ .' '. __FUNCTION__ 
			. ' no connection. ');
		}

		if ($percorso == ''){
			return 0;
		}

		if (substr_count($percorso, "/") == 0 ||
		    substr_count($percorso, '/') >  6){
			return 0;
		}
		
		@list($livello1, $livello2, $livello3, $livello4, $livello5, $livello6 ) = explode('/', $percorso);
		$campi=[];
		$campi['record_cancellabile_dal'] = $dbh->get_datetime_forever();
		if ($livello1 > ''){
			$campi['query'] = 'SELECT * FROM scansioni_disco '
			. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
			. " AND nome_file = '/' "
			. ' AND livello1 = :livello1 '
			. " AND livello2 = '' ";
			$campi['livello1'] = $livello1;
		}
		if ($livello2 > ''){
			$campi['query'] = 'SELECT * FROM scansioni_disco '
			. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
			. " AND nome_file = '/' "
			. ' AND livello1 = :livello1 '
			. '   AND livello2 = :livello2 '
			. "   AND livello3 = '' ";
			$campi['livello2'] = $livello2;
		}
		if ($livello3 > ''){
			$campi['query'] = 'SELECT * FROM scansioni_disco '
			. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
			. " AND nome_file = '/' "
			. ' AND livello1 = :livello1 '
			. '   AND livello2 = :livello2 '
			. '   AND livello3 = :livello3 '
			. "   AND livello4 = '' ";
			$campi['livello3'] = $livello3;
		}
		if ($livello4 > ''){
			$campi['query'] = 'SELECT * FROM scansioni_disco '
			. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
			. " AND nome_file = '/' "
			. ' AND livello1 = :livello1 '
			. '   AND livello2 = :livello2 '
			. '   AND livello3 = :livello3 '
			. '   AND livello4 = :livello4 '
			. "   AND livello5 = '' ";
			$campi['livello4'] = $livello4;
		}
		if ($livello5 > ''){
			$campi['query'] = 'SELECT * FROM scansioni_disco '
			. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
			. " AND nome_file = '/' "
			. ' AND livello1 = :livello1 '
			. '   AND livello2 = :livello2 '
			. '   AND livello3 = :livello3 '
			. '   AND livello4 = :livello4 '
			. '   AND livello5 = :livello5 '
			. "   AND livello6 = '' ";
			$campi['livello5'] = $livello5;
		}
		if ($livello6 > ''){
			$campi['query'] = 'SELECT * FROM scansioni_disco '
			. ' WHERE record_cancellabile_dal = :record_cancellabile_dal '
			. " AND nome_file = '/' "
			. ' AND livello1 = :livello1 '
			. '   AND livello2 = :livello2 '
			. '   AND livello3 = :livello3 '
			. '   AND livello4 = :livello4 '
			. '   AND livello5 = :livello5 '
			. '   AND livello6 = :livello6 ';
			$campi['livello6'] = $livello6;
		}
		$ret_scan = $this->leggi($campi);
		if (isset($ret_scan['error'])){
			echo __CLASS__ . ' ' . __FUNCTION__ . ' '; 
			echo '<br>';
			echo var_dump($campi);
			echo '<br><br>';
			echo var_dump($ret_scan);
			exit(1);
		}
		if (isset($ret_scan['numero']) && $ret_scan['numero'] > 0){
			return $ret_scan['data'][0]['record_id'];
		}
		return 0;
	} // get_record_id_da_percorso


} // class ScansioniDisco
