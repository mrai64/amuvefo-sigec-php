<?php
/**
 * @source /aa-model/didascalie.php
 * @author Massimo Rainato <maxrainato@libero.it>
 * 
 * Classe Didascalie
 * 
 * dipendenze: DatabaseHandler connessione archivio PDO
 * dipendenze: album
 * dipendenze: fotografie
 * dipendenze: video
 * 
 */
Class Didascalie extends DatabaseHandler {
  public $conn;

	public const nome_tabella  = 'didascalie';
	public const tabelle_padre_validi = [
		'album',
		'fotografie',
		'video'
	];
	public const padre_album      = 'album';
	public const padre_fotografie = 'fotografie';
	public const padre_video      = 'video';

	// registrati in archivio 
	public $record_id; //        bigint(20) unsigned auto+ primary 
	public $didascalia; //       text 
	public $tabella_padre; //    quella del campo record_di_padre 
	public $record_id_padre; //  bigint(20) unsigned 
	public $ultima_modifica_record; //        datetime DEF CURRENT TIME
	public $record_cancellabile_dal; //       datetime DEF '9999-12-31 23:59:59'
	
	public function __construct(DatabaseHandler $dbh){
		$this->conn = $dbh;

		$this->record_id       = 0;
		$this->didascalia      = '';
		$this->tabella_padre   = '';
		$this->record_id_padre = 0;
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

	public function get_didascalia() : string {
		$didascalia = $this->didascalia;
		$didascalia = htmlspecialchars_decode($didascalia);
		return $didascalia;
	}

	public function get_tabella_padre() : string {
		return $this->tabella_padre;
	}

	public function get_record_id_padre() : int {
		return $this->record_id_padre;
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

	/**
	 * SETTER
	 */
	public function set_record_id(int $record_id){
		if (!is_int($record_id) || $record_id < 1){
			// crea eccezione
			throw new Exception( __CLASS__ . ' ' . __FUNCTION__ 
			. " non sembra un numero intero, vale: " 
			. $record_id, 1);
		}
		$this->record_id = $record_id;
	}
	
	public function set_didascalia(string $didascalia){
		// trasformazioni per limitare danni sql injection
		$didascalia=strip_tags($didascalia);
		$didascalia=htmlspecialchars($didascalia);
		$this->didascalia = $didascalia;
	}
	
	public function set_tabella_padre( string $tabella_padre){
		if (!in_array($tabella_padre, self::tabelle_padre_validi)){
			throw new Exception(__CLASS__ . ' ' . __FUNCTION__ 
			. ' inout seems an invalid value, out of set. ' );
		}
		$this->tabella_padre = $tabella_padre; 
	}
	
	public function set_record_id_padre(int $record_id_padre){
		if (!is_int($record_id_padre) || $record_id_padre < 1){
			// crea eccezione
			throw new Exception( __CLASS__ . ' ' . __FUNCTION__ 
			. " non sembra un numero intero, vale: " 
			. $record_id_padre, 2);
		}
		$this->record_id_padre = $record_id_padre;
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
			throw new Exception(__CLASS__ .' '. __FUNCTION__ . ' no for: '. $record_cancellabile_dal . '. Must be a valid datetime format yyyy-mm-dd hh:mm:ss ');
		}
		$this->record_cancellabile_dal = $record_cancellabile_dal;
	}

	/**
	 * CRUD Create   Read  Update   Delete
	 *      aggiungi leggi modifica elimina 
	 */

	/** 
	 * L'operazione di INSERT non è tanto variabile
	 * e ci sono campi assegnati dal sistema
	 * @param   array $campi 
	 * @return  array $ret ok + messaggio oppure error + messaggio 
	 */
	public function aggiungi( array $campi = []) : array {
		$dbh = $this->conn; // a PDO object thru Database class

		if (!isset($campi['didascalia'])) {
			$ret = [
				'error'   => true,
				'message' => __CLASS__ . ' ' . __FUNCTION__ 
				. ' Serve il campo didascalia: '
				. $dbh::esponi($campi)
			];
			return $ret;
		}
		$this->set_didascalia($campi['didascalia']);
		
		if (!isset($campi['tabella_padre'])) {
			$ret = [
				'error'   => true,
				'message' => __CLASS__ . ' ' . __FUNCTION__ 
				. ' Serve il campo tabella_padre: '
				. $dbh::esponi($campi)
			];
			return $ret;
		}
		$this->set_tabella_padre($campi['tabella_padre']);

		if (!isset($campi['record_id_padre'])) {
			$ret = [
				'error'   => true,
				'message' => __CLASS__ . ' ' . __FUNCTION__ 
				. ' Serve il campo record_id_padre: '
				. $dbh::esponi($campi)
			];
			return $ret;
		}
		$this->set_record_id_padre($campi['record_id_padre']);

		$create = ' INSERT INTO ' . self::nome_tabella 
		. ' (  didascalia,  tabella_padre,  record_id_padre ) VALUES '
		. ' ( :didascalia, :tabella_padre, :record_id_padre ) ';

		if (!$dbh->inTransaction()) { 
			$dbh->beginTransaction(); 
		}
		try {
			$aggiungi=$dbh->prepare($create);
			$aggiungi->bindValue('didascalia',      $this->didascalia);
			$aggiungi->bindValue('tabella_padre',   $this->tabella_padre);
			$aggiungi->bindValue('record_id_padre', $this->record_id_padre, PDO::PARAM_INT );
			$aggiungi->execute();

			$record_id = $dbh->lastInsertID();
			$dbh->commit();
			$ret = [
				'ok'        => true, 
				"record_id" => $record_id,
				"message"   => __CLASS__ . ' ' . __FUNCTION__ 
				. " Inserimento record effettuato, nuovo id: " 
				. $record_id 
			];
			return $ret;
	
		} catch (\Throwable $th) {
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
	} // aggiungi

	/**
	 * @param  array $campi
	 * @return array $ret ok + numero + data[] oppure error + message
	 */
public function leggi(array $campi) : array {
	$dbh = $this->conn; // a PDO object thru Database class

	if (!isset($campi['query'])){
		$ret = [
			"error"=> true, 
			"message" => __CLASS__ . ' ' . __FUNCTION__ 
			. "Deve essere definita l'istruzione SELECT in ['query']: " 
			. $dbh::esponi($campi)
		];
		return $ret;
	}
	// dati obbligatori

	$read = $campi['query'];
	if (isset($campi['record_id'])){
		$this->set_record_id($campi['record_id']);
	}
	if (isset($campi['didascalia'])){
		$this->set_didascalia($campi['didascalia']);
	}
	if (isset($campi['tabella_padre'])){
		$this->set_tabella_padre($campi['tabella_padre']);
	}
	if (isset($campi['record_id_padre'])){
		$this->set_record_id_padre($campi['record_id_padre']);
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
		if (isset($campi['didascalia'])){
			$lettura->bindValue('didascalia', $campi['didascalia']); 
		}
		if (isset($campi['tabella_padre'])){
			$lettura->bindValue('tabella_padre', $campi['tabella_padre']); 
		}
		if (isset($campi['record_id_padre'])){
			$lettura->bindValue('record_id_padre', $campi['record_id_padre'], PDO::PARAM_INT); 
		}
		if (isset($campi['ultima_modifica_record'])){
			$lettura->bindValue('ultima_modifica_record', $campi['ultima_modifica_record']); 
		}
		if (isset($campi['record_cancellabile_dal'])){
			$lettura->bindValue('record_cancellabile_dal', $campi['record_cancellabile_dal']); 
		}	
		$lettura->execute();
		$conteggio = 0;
		$dati_di_ritorno = [];
		// si potrebbe usare fetchAll(), però... 
		while ($record = $lettura->fetch(PDO::FETCH_ASSOC)) {
			$dati_di_ritorno[] = $record;
			$conteggio++;
		}    
		// Può dare ok anche per un risultato "vuoto"
		$ret = [
			'ok'     => true,
			'numero' => $conteggio,
			'data'   => $dati_di_ritorno 
		];
		return $ret;

	} catch (\Throwable $th) {
		$ret = [
			'error' => true,
			'message' => __CLASS__ . ' ' . __FUNCTION__ 
			. ' ' . $th->getMessage() 
			. ' istruzione SQL: ' . $read
			. ' campi: ' . $dbh::esponi($campi)
		];
		return $ret;
	} // try catch
} // leggi

/**
 * MODIFICA ma anche SOFT DELETE 
 * 
 * @param  array $campi 
 * @return array 'ok' + message | error + message 
 */
public function modifica(array $campi=[]) : array {
	$dbh = $this->conn; // a PDO object thru Database class

	if (!isset($campi['update'])){
		$ret = [
			"error"=> true, 
			"message" => __CLASS__ . ' ' . __FUNCTION__ 
			. " Aggiornamento record senza UPDATE: " 
			. $dbh::esponi($campi) 
		];
		return $ret;
	}
	$update = $campi['update'];
	if (isset($campi['record_id'])){
		$this->set_record_id($campi['record_id']);
	}
	if (isset($campi['didascalia'])){
		$this->set_didascalia($campi['didascalia']);
	}
	if (isset($campi['tabella_padre'])){
		$this->set_tabella_padre($campi['tabella_padre']);
	}
	if (isset($campi['record_id_padre'])){
		$this->set_record_id_padre($campi['record_id_padre']);
	}
	if (isset($campi['ultima_modifica_record'])){
		$this->set_ultima_modifica_record($campi['ultima_modifica_record']);
	}
	if (isset($campi['record_cancellabile_dal'])){
		$this->set_record_cancellabile_dal($campi['record_cancellabile_dal']);
	}
	if (!$dbh->inTransaction()) { 
		$dbh->beginTransaction(); 
	}
	try {
		$aggiorna = $dbh->prepare($update);
		if (isset($campi['record_id'])){
			$aggiorna->bindValue('record_id', $campi['record_id'], PDO::PARAM_INT); 
		}
		if (isset($campi['didascalia'])){
			$aggiorna->bindValue('didascalia', $campi['didascalia'] ); 
		}
		if (isset($campi['tabella_padre'])){
			$aggiorna->bindValue('tabella_padre', $campi['tabella_padre']); 
		}
		if (isset($campi['record_id_padre'])){
			$aggiorna->bindValue('record_id_padre', $campi['record_id_padre'], PDO::PARAM_INT); 
		}
		if (isset($campi['ultima_modifica_record'])){
			$aggiorna->bindValue('ultima_modifica_record', $campi['ultima_modifica_record']); 
		}
		if (isset($campi['record_cancellabile_dal'])){
			$aggiorna->bindValue('record_cancellabile_dal', $campi['record_cancellabile_dal']); 
		}

		$aggiorna->execute();
		$dbh->commit();
		$ret = [ 
			"ok" => true,
			"message" => "Aggiornamento eseguito"
		];
		return $ret;

	} catch (\Throwable $th) {
		$dbh->rollBack(); 
		$ret = [
			"error" => true,
			"message" => __CLASS__ . ' ' . __FUNCTION__ 
			. '<br>' . $th->getMessage() 
			. '<br>campi: ' . $dbh::esponi($campi)
			. '<br>istruzione SQL: ' . $update
		];
		return $ret;
	} // try catch
} // modifica

/**
 * DELETE - cancellazione FISICA, per il soft-delete 
 * aggiornare il campo record_cancellabile_dal 
 * con dbh->get_datetime_now()
 */
public function elimina(array $campi) : array {
	$dbh = $this->conn; // a PDO object thru Database class

	if (!isset($campi['delete'])){
		$ret = [
			"error"=> true, 
			"message" => __CLASS__ . ' ' . __FUNCTION__ 
			. "Deve essere definita l'istruzione DELETE in ['delete']: " 
			. $dbh::esponi($campi)
		];
		return $ret;
	}
	// dati obbligatori

	$delete = $campi['delete'];
	if (isset($campi['record_id'])){
		$this->set_record_id($campi['record_id']);
	}
	// didascalia no...
	if (isset($campi['tabella_padre'])){
		$this->set_tabella_padre($campi['tabella_padre']);
	}
	if (isset($campi['record_id_padre'])){
		$this->set_record_id_padre($campi['record_id_padre']);
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
		if (isset($campi['tabella_padre'])){
			$cancella->bindValue('tabella_padre', $campi['tabella_padre']); 
		}
		if (isset($campi['record_id_padre'])){
			$cancella->bindValue('record_id_padre', $campi['record_id_padre'], PDO::PARAM_INT); 
		}
		if (isset($campi['ultima_modifica_record'])){
			$cancella->bindValue('ultima_modifica_record', $campi['ultima_modifica_record']); 
		}
		if (isset($campi['record_cancellabile_dal'])){
			$cancella->bindValue('record_cancellabile_dal', $campi['record_cancellabile_dal']); 
		}
		$cancella->execute();
		$dbh->commit();
		$ret = [ 
			"ok" => true,
			"message" => "Cancellazione eseguita"
		];
		return $ret;

	} catch (\Throwable $th) {
		$dbh->rollBack(); 
		$ret = [
			"error" => true,
			"message" => __CLASS__ . ' ' . __FUNCTION__ 
			. '<br>' . $th->getMessage() 
			. '<br>campi: ' . $dbh::esponi($campi)
			. '<br>istruzione SQL: ' . $delete
		];
		return $ret;
	} // try catch
} // elimina


	/**
	 * ALTRE FUNZIONI 
	 * 
	 * backup vedi controller dedicato
	 */

	/**
	 * @param  string file_oggetto percorso completo 
	 * @return array  'ok' + dati | 'error' + message
	 * Avviso: anche quando i dati sono restituiti 'ok' il record_id
	 * e anche il record_id_padre sono impostati a zero
	 */
	public function recupera_didascalia(string $file_soggetto = ''): array {
		$didascalia = "";
		// devo vedere se il parametro passato è un file o una cartella
		if (str_contains($file_soggetto, ABSPATH) === false) {
			$file_soggetto = ABSPATH . $file_soggetto;
		}
		if (is_file($file_soggetto)){
			$file_didascalia = str_ireplace(['.psd', '.tif', '.jpg'], '.txt', $file_soggetto);
			if ($file_didascalia <> $file_soggetto && is_file($file_didascalia)){
				$didascalia = file_get_contents($file_didascalia);
				$dati_di_ritorno=[];
				$dati_di_ritorno[] = [
					'record_id' => 0,
					'tabella_padre' => 'fotografie',
					'record_id_padre' => 0,
					'didascalia' => $didascalia
				];
				$ret = [
					'ok'        => true,
					'numero'    => 1,
					'data'      => $dati_di_ritorno
				];
				return $ret;
			}
			$file_didascalia = str_ireplace(['.mov', '.mp4', '.mkv'], '.txt', $file_soggetto);
			if ($file_didascalia <> $file_soggetto && is_file($file_didascalia)){
				$didascalia = file_get_contents($file_didascalia);
				$dati_di_ritorno=[];
				$dati_di_ritorno[] = [
					'record_id' => 0,
					'tabella_padre' => 'video',
					'record_id_padre' => 0,
					'didascalia' => $didascalia
				];
				$ret = [
					'ok'        => true,
					'numero'    => 1,
					'data'      => $dati_di_ritorno
				];
				return $ret;
			}
		} else {
			// cartella 
			$file_didascalia = $file_soggetto .'_leggimi.txt';
			if (is_file($file_didascalia)){
				$didascalia = file_get_contents($file_didascalia);
				$dati_di_ritorno=[];
				$dati_di_ritorno[] = [
					'record_id' => 0,
					'tabella_padre' => 'album',
					'record_id_padre' => 0,
					'didascalia' => $didascalia
				];
				$ret = [
					'ok'        => true,
					'numero'    => 1,
					'data'      => $dati_di_ritorno
				];
				return $ret;
			}
		}
		// se arriva qui non è nessuno dei precedenti 
		$ret = [
			"error" => true,
			"message" => __CLASS__ . ' ' . __FUNCTION__ 
			. '<br>per il soggetto: ' . $file_soggetto 
			. "<br>non sono state trovate didascalie"
		];
		return $ret;
	} // recupera_didascalia

	/**
	 * Quando caricato, il file didascalia va eliminato 
	 */
	public function elimina_file_didascalia(string $file_soggetto = ''):bool{
		// devo vedere se il parametro passato è un file o una cartella
		if (str_contains($file_soggetto, ABSPATH) === false) {
			$file_soggetto = ABSPATH . $file_soggetto;
		}
		// fotografie
		$file_didascalia = str_ireplace(['.psd', '.tif', '.jpg'], '.txt', $file_soggetto);
		if ($file_didascalia <> $file_soggetto && is_file($file_didascalia)){
			return unlink($file_didascalia);
		}
		// video
		$file_didascalia = str_ireplace(['.mov', '.mp4', '.mkv'], '.txt', $file_soggetto);
		if ($file_didascalia <> $file_soggetto && is_file($file_didascalia)){
			return unlink($file_didascalia);
		}
		// cartella 
		$file_didascalia = $file_soggetto .'_leggimi.txt';
		if (is_file($file_didascalia)){
			return unlink($file_didascalia);
		}
		return false; // se arriva qui non ha cancellato niente 
	} // elimina_file_didascalia

} // Didascalie
