<?php 
/**
 * @source /aa-model/consultatori-oop.php 
 * @author Massimo Rainato <maxrainato@libero.it>
 * 
 * Classe Consultatori 
 * 
 * @see https://archivio.athesis77.it/tech/3-archivi-tabelle/3-10-consultatori_calendario/
 * 
 * E' presente un campo password che è cifrato sulla base dei campi 
 * SALT di wordpress. Il valore iniziale non è ricostruibile dal campo 
 * registrato in archivio.
 * 
 */
/*
 * 
CREATE TABLE `consultatori_calendario` (
	`record_id` bigint UNSIGNED NOT NULL COMMENT 'uso interno assegnato automaticamente',
	`cognome_nome` varchar(250) NOT NULL COMMENT 'Cognome, Nome',
	`email` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'chiave di accesso',
	`password` varchar(250) NOT NULL COMMENT 'cifrata',
	`abilitazione` enum('0 nessuna','1 lettura','3 modifica','5 modifica originali','7 amministrazione') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '0 nessuna' COMMENT 'uso interno',
	`attivita_dal` char(10) NOT NULL COMMENT 'aaaa-mm-gg, da modificare in tipo DATE',
	`attivita_fino_al` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'aaaa-mm-gg',
	`ultima_modifica_record` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'uso interno',
	`record_cancellabile_dal` datetime NOT NULL DEFAULT '9999-12-31 23:59:59' COMMENT 'data futura=non si cancella'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='operatori con calendario abilitazione';

 * 
 */
Class Consultatori extends DatabaseHandler {
	public $conn;

	public const nome_tabella = 'consultatori_calendario';

	public const abilitazione_nessuna = '0 nessuna';
	public const abilitazione_lettura = '1 lettura';
	public const abilitazione_modifica = '3 modifica';
	public const abilitazione_modifica_plus = '5 modifica originali';
	public const abilitazione_admin = '7 amministrazione';
	public const abilitazione_set =[
		self::abilitazione_nessuna,
		self::abilitazione_lettura,
		self::abilitazione_modifica,
		self::abilitazione_modifica_plus,
		self::abilitazione_admin
	];
	
	public $record_id; // 
	public $cognome_nome; //
	public $email; // 
	public $password; //
	public $abilitazione; //
	public $attivita_dal; // attivita non attività è intenzionale
	public $attivita_fino_al; //
	public $ultima_modifica_record;  //
	public $record_cancellabile_dal; //
	

	public function __construct(DatabaseHandler $dbh){
		$this->conn = $dbh;

		$this->record_id               =0; // invalido 
		$this->cognome_nome            ='';
		$this->email                   ='';
		$this->password                ='';
		$this->abilitazione            = self::abilitazione_nessuna;
		$this->attivita_dal            = substr($dbh->get_datetime_now(), 0, 10);
		$this->attivita_fino_al        = $this->attivita_dal;
		$this->ultima_modifica_record  = $dbh->get_datetime_now();
		$this->record_cancellabile_dal = $dbh->get_datetime_forever();
	} // __construct

	// GETTER 
	public function get_record_id() : int {
		return $this->record_id;
	} 

	public function get_cognome_nome() : string {
		return $this->cognome_nome;
	} 

	public function get_email() : string {
		return $this->email;
	} 

	public function get_password() : string {
		return $this->password;
	} 

	public function get_abilitazione() : string {
		return $this->abilitazione;
	} 

	public function get_attivita_dal() : string {
		return $this->attivita_dal;
	} 

	public function get_attivita_fino_al() : string {
		return $this->attivita_fino_al;
	} 

	public function get_ultima_modifica_record() : string {
		return $this->ultima_modifica_record;
	} 

	public function get_record_cancellabile_dal() : string {
		return $this->record_cancellabile_dal;
	} 

	public function get_consultatore_from_id(int $consultatore_id) : array{
		// necessari
		$dbh = $this->conn; // a PDO object thru Database class

		// validazione
		$this->set_record_id($consultatore_id);

		$read = 'SELECT * FROM ' . self::nome_tabella
		. ' WHERE record_cancellabile_dal = :record_cancellabile_dal  '
		. ' AND record_id = :record_id ';
		try {
			$lettura=$dbh->prepare($read);
			$lettura->bindValue('record_cancellabile_dal', $dbh->get_datetime_forever() ); 
			$lettura->bindValue('record_id',               $consultatore_id, PDO::PARAM_INT); 
			$lettura->execute();

		} catch( \Throwable $th ){
			$ret = [
				'error'   => true,
				'message' => __CLASS__ . ' ' . __FUNCTION__ 
				. '<br>' . $th->getMessage() 
				. '<br>consultatore_id: ' . $consultatore_id
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
	} // get_consultatore_from_id


	// Setter
	public function set_record_id( int $record_id ){
		if ($record_id < 1){
			throw new Exception( __CLASS__ . ' ' . __FUNCTION__ 
			. ' Must be unsigned integer, is : ' 
			. $record_id );
		}
		$this->record_id = $record_id;
	}

	public function set_cognome_nome( string $cognome_nome ){
		// ritaglio a misura 
		$cognome_nome = htmlspecialchars(strip_tags($cognome_nome));
		$cognome_nome = mb_substr($cognome_nome, 0, 250);
		$this->cognome_nome = $cognome_nome;
	}

	public function set_email( string $email ){
		// ritaglio a misura 
		$email = htmlspecialchars(strip_tags($email));
		$email = mb_substr($email, 0, 250);
		$this->email = $email;
	}

	public function set_password( string $password ){
		// ritaglio a misura 
		$password = htmlspecialchars(strip_tags($password));
		$password = mb_substr($password, 0, 250);
		$password_mescolata = hash_hmac('sha512', $password . AUTH_SALT, AUTH_KEY);
		$this->password = $password_mescolata;
	}

	public function set_abilitazione( string $abilitazione){
		if (!in_array($abilitazione, self::abilitazione_set)){
			throw new Exception(__CLASS__ . ' ' . __FUNCTION__ 
			. ' abilitazione invalid value, out of set. ' );
		}
		$this->abilitazione = $abilitazione; 
	}

	public function set_attivita_dal( string $attivita_dal ){
		// ritaglio a misura 
		$attivita_dal = htmlspecialchars(strip_tags($attivita_dal));
		$attivita_dal = mb_substr($attivita_dal, 0, 10);
		if (!($this->conn->is_datetime($attivita_dal.' 00:00:00'))){
			throw new Exception(__CLASS__ .' '. __FUNCTION__ 
			. ' no for: '. $attivita_dal 
			. '. Must be a valid datetime format yyyy-mm-dd ');
		}
		$this->attivita_dal = $attivita_dal;
	}

	public function set_attivita_fino_al( string $attivita_fino_al ){
		$dbh = $this->conn;
		// ritaglio a misura 
		$attivita_fino_al = htmlspecialchars(strip_tags($attivita_fino_al));
		$attivita_fino_al = mb_substr($attivita_fino_al, 0, 10);
		if (!($dbh->is_datetime($attivita_fino_al.' 00:00:00'))){
			throw new Exception(__CLASS__ .' '. __FUNCTION__ 
			. ' no for: '. $attivita_fino_al 
			. '. Must be a valid datetime format yyyy-mm-dd ');
		}
		$this->attivita_fino_al = $attivita_fino_al;
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
	 * CRUD | CREATE READ UPDATE DELETE
	 */
	public function aggiungi(array $campi) : array{
		$create = 'INSERT INTO ' . self::nome_tabella 
		. ' ( cognome_nome,  email,  password,  abilitazione,  attivita_dal,  attivita_fino_al ) VALUES ' 
		. ' (:cognome_nome, :email, :password, :abilitazione, :attivita_dal,  :attivita_fino_al ) ';
		// necessari
		// dati obbligatori
		$dbh = $this->conn; // a PDO object thru Database class

		if ( !isset($campi['cognome_nome'])){
			$ret=[
				'error'   => true,
				'message' => __CLASS__ .' '. __FUNCTION__ 
				. ' Deve essere valorizzato cognome_nome '
			];
			return $ret;
		}
		$this->set_cognome_nome($campi['cognome_nome']);
		
		if ( !isset($campi['email'])){
			$ret=[
				'error'   => true,
				'message' => __CLASS__ .' '. __FUNCTION__ 
				. ' Deve essere valorizzato email '
			];
			return $ret;
		}
		$this->set_email($campi['email']);
		
		if ( !isset($campi['password'])){
			$ret=[
				'error'   => true,
				'message' => __CLASS__ .' '. __FUNCTION__ 
				. ' Deve essere valorizzato password '
			];
			return $ret;
		}
		$this->set_password($campi['password']);

		if ( !isset($campi['abilitazione'])){
			$ret=[
				'error'   => true,
				'message' => __CLASS__ .' '. __FUNCTION__ 
				. ' Deve essere valorizzato abilitazione '
			];
			return $ret;
		}
		$this->set_abilitazione($campi['abilitazione']);

		if ( !isset($campi['attivita_dal'])){
			$ret=[
				'error'   => true,
				'message' => __CLASS__ .' '. __FUNCTION__ 
				. ' Deve essere valorizzato attivita_dal '
			];
			return $ret;
		}
		$this->set_attivita_dal($campi['attivita_dal']);

		if ( !isset($campi['attivita_fino_al'])){
			$ret=[
				'error'   => true,
				'message' => __CLASS__ .' '. __FUNCTION__ 
				. ' Deve essere valorizzato attivita_fino_al '
			];
			return $ret;
		}
		$this->set_attivita_fino_al($campi['attivita_fino_al']);

		// azione
		if (!$dbh->inTransaction()) { $dbh->beginTransaction(); }
		try{
			$aggiungi = $dbh->prepare($create);
			$aggiungi->bindValue('cognome_nome',     $this->cognome_nome);
			$aggiungi->bindValue('email',            $this->email);
			$aggiungi->bindValue('password',         $this->password);
			$aggiungi->bindValue('abilitazione',     $this->abilitazione);
			$aggiungi->bindValue('attivita_dal',     $this->attivita_dal);
			$aggiungi->bindValue('attivita_fino_al', $this->attivita_fino_al);
			$aggiungi->execute();
			$record_id = $dbh->lastInsertID();
			$dbh->commit();

		} catch(\Throwable $th ){
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
		} // try catch

		$ret = [
			'ok'        => true,
			'record_id' => $record_id,
			'message'   => __CLASS__ . ' ' . __FUNCTION__
			. " Inserimento record effettuato, nuovo id: "
			. $record_id
		];
		return $ret;
	} // aggiungi CREATE

	/**
	 * Leggi READ
	 */
	public function leggi(array $campi) : array{
		// necessari 
		$dbh = $this->conn; // a PDO object thru Database class

		if (!isset($campi['query'])){
			$ret = [
				'error'   => true,
				'message' => __CLASS__ . ' ' . __FUNCTION__
				. "Deve essere definita l'istruzione SELECT in ['query']: "
				. $dbh::esponi($campi)
			];
			return $ret;
		}
		// validazione 
		if (isset($campi['record_id'])){
			$this->set_record_id($campi['record_id']);
		}
		if (isset($campi['cognome_nome'])){
			$this->set_cognome_nome($campi['cognome_nome']);
		}
		if (isset($campi['email'])){
			$this->set_email($campi['email']);
		}
		if (isset($campi['password'])){
			$this->set_password($campi['password']);
		}
		if (isset($campi['abilitazione'])){
			$this->set_abilitazione($campi['abilitazione']);
		}
		if (isset($campi['attivita_dal'])){
			$this->set_attivita_dal($campi['attivita_dal']);
		}
		if (isset($campi['attivita_fino_al'])){
			$this->set_attivita_fino_al($campi['attivita_fino_al']);
		}
		if (isset($campi['ultima_modifica_record'])){
			$this->set_ultima_modifica_record($campi['ultima_modifica_record']);
		}
		if (isset($campi['record_cancellabile_dal'])){
			$this->set_record_cancellabile_dal($campi['record_cancellabile_dal']);
		}
		// azione
		$read = $campi['query'];
		try {
			$lettura = $dbh->prepare($read);
			if (isset($campi['record_id'])){
				$lettura->bindValue('record_id', $this->record_id , PDO::PARAM_INT);
			}
			if (isset($campi['cognome_nome'])){
				$lettura->bindValue('cognome_nome', $this->cognome_nome);
			}
			if (isset($campi['email'])){
				$lettura->bindValue('email', $this->email);
			}
			if (isset($campi['password'])){
				$lettura->bindValue('password', $this->password);
			}
			if (isset($campi['abilitazione'])){
				$lettura->bindValue('abilitazione', $this->abilitazione);
			}
			if (isset($campi['attivita_dal'])){
				$lettura->bindValue('attivita_dal', $this->attivita_dal);
			}
			if (isset($campi['attivita_fino_al'])){
				$lettura->bindValue('attivita_fino_al', $this->attivita_fino_al);
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
				'error'    => true,
				'message'  => __CLASS__ . ' ' . __FUNCTION__
				. '<br>' . $th->getMessage()
				. "<br>campi: " . $dbh::esponi($campi)
				. '<br>istruzione SQL: ' . $read
			];
			return $ret;
		}
		$numero = 0; // può esserci un $limite
		$dati_di_ritorno = []; // è sempre un array
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
	} // leggi READ

	function modifica(array $campi) : array{
		// necessari 
		$dbh = $this->conn; // a PDO object thru Database class

		if (!isset($campi['update'])){
			$ret = [
				'error'   => true,
				'message' => __CLASS__ . ' ' . __FUNCTION__
				. "Deve essere definita l'istruzione UPDATE in ['query']: "
				. $dbh::esponi($campi)
			];
			return $ret;
		}
		// validazione 
		if (isset($campi['record_id'])){
			$this->set_record_id($campi['record_id']);
		}
		if (isset($campi['cognome_nome'])){
			$this->set_cognome_nome($campi['cognome_nome']);
		}
		if (isset($campi['email'])){
			$this->set_email($campi['email']);
		}
		if (isset($campi['password'])){
			$this->set_password($campi['password']);
		}
		if (isset($campi['abilitazione'])){
			$this->set_abilitazione($campi['abilitazione']);
		}
		if (isset($campi['attivita_dal'])){
			$this->set_attivita_dal($campi['attivita_dal']);
		}
		if (isset($campi['attivita_fino_al'])){
			$this->set_attivita_fino_al($campi['attivita_fino_al']);
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
				$aggiorna->bindValue('record_id', $this->record_id , PDO::PARAM_INT);
			}
			if (isset($campi['cognome_nome'])){
				$aggiorna->bindValue('cognome_nome', $this->cognome_nome);
			}
			if (isset($campi['email'])){
				$aggiorna->bindValue('email', $this->email);
			}
			if (isset($campi['password'])){
				$aggiorna->bindValue('password', $this->password);
			}
			if (isset($campi['abilitazione'])){
				$aggiorna->bindValue('abilitazione', $this->abilitazione);
			}
			if (isset($campi['attivita_dal'])){
				$aggiorna->bindValue('attivita_dal', $this->attivita_dal);
			}
			if (isset($campi['attivita_fino_al'])){
				$aggiorna->bindValue('attivita_fino_al', $this->attivita_fino_al);
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
				'error'    => true,
				'message'  => __CLASS__ . ' ' . __FUNCTION__
				. ' ' . $th->getMessage()
				. ' campi: '          . $dbh::esponi($campi)
				. ' istruzione SQL: ' . $update
			];
			return $ret;
		}
		$ret = [
			'ok'      => true,
			'message' => 'Aggiornamento eseguito'
		];
		return $ret;
	} // modifica UPDATE
	
	public function elimina(array $campi) : array {
		// necessari 
		$dbh = $this->conn; // a PDO object thru Database class

		if (!isset($campi['delete'])){
			$ret = [
				"error"   => true,
				"message" => __CLASS__ . ' ' . __FUNCTION__
				. "Deve essere definita l'istruzione DELETE in ['delete']: "
				. $dbh::esponi($campi)
			];
			return $ret;
		}
		// validazione 
		if (isset($campi['record_id'])){
			$this->set_record_id($campi['record_id']);
		}
		if (isset($campi['cognome_nome'])){
			$this->set_cognome_nome($campi['cognome_nome']);
		}
		if (isset($campi['email'])){
			$this->set_email($campi['email']);
		}
		// password no
		if (isset($campi['abilitazione'])){
			$this->set_abilitazione($campi['abilitazione']);
		}
		if (isset($campi['attivita_dal'])){
			$this->set_attivita_dal($campi['attivita_dal']);
		}
		if (isset($campi['attivita_fino_al'])){
			$this->set_attivita_fino_al($campi['attivita_fino_al']);
		}
		if (isset($campi['ultima_modifica_record'])){
			$this->set_ultima_modifica_record($campi['ultima_modifica_record']);
		}
		if (isset($campi['record_cancellabile_dal'])){
			$this->set_record_cancellabile_dal($campi['record_cancellabile_dal']);
		}
		$cancellazione = $campi['delete'];

		// azione
		if (!$dbh->inTransaction()) { $dbh->beginTransaction(); }
		try {
			$cancella = $dbh->prepare($$cancellazione);
			if (isset($campi['record_id'])){
				$cancella->bindValue('record_id', $this->record_id , PDO::PARAM_INT);
			}
			if (isset($campi['cognome_nome'])){
				$cancella->bindValue('cognome_nome', $this->cognome_nome);
			}
			if (isset($campi['email'])){
				$cancella->bindValue('email', $this->email);
			}
			if (isset($campi['password'])){
				$cancella->bindValue('password', $this->password);
			}
			if (isset($campi['abilitazione'])){
				$cancella->bindValue('abilitazione', $this->abilitazione);
			}
			if (isset($campi['attivita_dal'])){
				$cancella->bindValue('attivita_dal', $this->attivita_dal);
			}
			if (isset($campi['attivita_fino_al'])){
				$cancella->bindValue('attivita_fino_al', $this->attivita_fino_al);
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
			$dbh->rollBack();
			$ret = [
				'error'    => true,
				'message'  => __CLASS__ . ' ' . __FUNCTION__
				. ' ' . $th->getMessage()
				. ' campi: '          . $dbh::esponi($campi)
				. ' istruzione SQL: ' . $cancellazione
			];
			return $ret;
		}
		$ret = [
			'ok'      => true,
			'message' => 'Cancellazione eseguita'
		];
		return $ret;
	} // elimina DELETE 
		
} // Consultatori
