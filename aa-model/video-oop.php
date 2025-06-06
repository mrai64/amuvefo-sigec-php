<?php 
/**
 *	@source //aa-model/video-oop.php
 *  @author Massimo Rainato <maxrainato@libero.it>
 * 
 *	Classe Video
 *
 *	dipendenze: DatabaseHandler connessione archivio PDO 
 *	dipendenze: Album 
 *	dipendenze: ScansioniDisco 
 *	dipendenze: VideoDettagli 
 *
 * @see https://archivio.athesis77.it/tech/3-archivi-tabelle/video/
 * @see https://archivio.athesis77.it/tech/3-archivi-tabelle/video-dettagli/
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
Class Video extends DatabaseHandler {
	public $conn;

	public const nome_tabella  = 'video';
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
	public $titolo_video; //                  varchar(250)
	public $disco; //                         char(12)
	public $percorso_completo; //             varchar(1500)
	public $record_id_in_album; //            bigint(20) unsigned external key su scansioni_disco
	public $record_id_in_scansioni_disco; //  bigint(20) unsigned external key su scansioni_disco
	public $stato_lavori; //                  enum     DEF '0 da fare'
	public $ultima_modifica_record; //        datetime DEF CURRENT TIME
	public $record_cancellabile_dal; //       datetime DEF '9999-12-31 23:59:59'

	
	public function __construct(DatabaseHandler $dbh){
		$this->conn = $dbh;
		
		$this->record_id = 0; // invalido
		$this->titolo_video = ''; // invalido
		$this->disco = '';
		$this->percorso_completo       = ''; // invalido 
		$this->record_id_in_album      = 0; // invalido 
		$this->record_id_in_scansioni_disco = 0; // invalido 
		$this->stato_lavori            = self::stato_da_fare;
		$this->ultima_modifica_record  = $dbh->get_datetime_now();
		$this->record_cancellabile_dal = $dbh->get_datetime_forever();

	} // __construct
	
	// GETTER 
	/**
	 * @return int unsigned
	 */
	public function get_record_id(){
		return $this->record_id;
	}
	
	public function get_titolo_video() {
		return $this->titolo_video;
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
	public function get_record_id_in_album(){
		return $this->record_id_in_album;
	}
	/**
	 * @return int unsigned 
	 */
	public function get_record_id_in_scansioni_disco(){
		return $this->record_id_in_scansioni_disco;
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
	
	/**
	 * @return string enum 
	 */
	public function get_stato_lavori() : string {
		return $this->stato_lavori;
	}
	
	// SETTER 
	public function set_record_id( int $record_id ){
		if ($record_id < 1){
			throw new Exception(__CLASS__ . ' ' . __FUNCTION__ 
			. ' Must be unsigned integer is : ' . $record_id );
		}
		$this->record_id = $record_id;
	}
		
	public function set_titolo_video( string $titolo_video ) {
		// ritaglio a misura
		$titolo_video = htmlspecialchars(strip_tags($titolo_video));
		$titolo_video = mb_substr($titolo_video, 0, 250);
		$this->titolo_video = $titolo_video;
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
	
	public function set_record_id_in_album( int $record_id_in_album){
		if ($record_id_in_album < 1){
			throw new Exception(__CLASS__ .' '. __FUNCTION__ 
			. ' Must be unsigned integer ' . $record_id_in_album);
		}
		$this->record_id_in_album = $record_id_in_album;
	}

	public function set_record_id_in_scansioni_disco( int $record_id_in_scansioni_disco){
		if ($record_id_in_scansioni_disco < 1){
			throw new Exception(__CLASS__ .' '. __FUNCTION__ 
			. ' Must be unsigned integer ' . $record_id_in_scansioni_disco);
		}
		$this->record_id_in_scansioni_disco = $record_id_in_scansioni_disco;
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

	public function set_stato_lavori(string $stato_lavori ){
		if ( !in_array( $stato_lavori, self::stato_lavori_validi)){
			$stato_lavori = self::stato_da_fare;
		}
		$this->stato_lavori = $stato_lavori;
	}
	
	/**
	 * Si distingue dal set_stato_lavori del campo stato_lavori 
	 */
	public function set_stato_lavori_in_video(int $record_id, string $stato_lavori) : array {
		$dbh =$this->conn; 
		if ($dbh===false){
			$ret=[
				'error'   => true,
				'message' => __CLASS__ . ' ' . __FUNCTION__ 
				. " L'aggiornamento dello stato_lavori non si può fare"
				. ' senza connessione al database '
				. ' per ' . self::nome_tabella
			];
			return $ret;
		}
		// verifiche 
		$this->set_stato_lavori($stato_lavori);
		$this->set_record_id($record_id);
		$update = ' UPDATE ' . self::nome_tabella
		. ' SET stato_lavori = :stato_lavori '
		. ' WHERE record_id = :record_id '; 
		if (!$dbh->inTransaction()) { $dbh->beginTransaction(); }		
		try {
			//code...
			$aggiorna=$dbh->prepare($update);		
			$aggiorna->bindValue('stato_lavori', $this->get_stato_lavori()); 
			$aggiorna->bindValue('record_id',    $this->get_record_id()); 
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

	} // set_stato_lavori_in_video

	
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

		$leggi = 'SELECT 1 FROM video '  
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
		// ultima_modifica_record        viene assegnato automaticamente 
		// record_cancellabile_dal viene assegnato automaticamente 
		// stato_lavori         viene assegnato automaticamente 
		$dbh = $this->conn; // a PDO object thru Database class
	
		$create = 'INSERT INTO video '  
		. ' (  titolo_video,   disco,  percorso_completo,'
		. '    record_id_in_album,  record_id_in_scansioni_disco ) VALUES '
		. ' ( :titolo_video,  :disco, :percorso_completo, '
		. '   :record_id_in_album, :record_id_in_scansioni_disco ) ';

		// campi necessari 
		if (!isset($campi['titolo_video'])){
			$ret = [
				"error"=> true, 
				"message" => __CLASS__ . ' ' . __FUNCTION__ 
				. " Serve campo titolo_video: " . $dbh::esponi($campi) 
			];
			return $ret;
		}
		$this->set_titolo_video($campi['titolo_video']);

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

		if (!isset($campi['record_id_in_scansioni_disco'])){
			$ret = [
				"error"=> true, 
				"message" => __CLASS__ . ' ' . __FUNCTION__ 
				. " Serve campo record_id_in_scansioni_disco: " . $dbh::esponi($campi) 
			];
			return $ret;
		}
		$this->set_record_id_in_scansioni_disco($campi['record_id_in_scansioni_disco']);


		try{
			$aggiungi = $dbh->prepare($create);
			$aggiungi->bindValue('titolo_video',            $this->titolo_video);
			$aggiungi->bindValue('disco',                        $this->disco);
			$aggiungi->bindValue('percorso_completo',            $this->percorso_completo);
			$aggiungi->bindValue('record_id_in_album',           $this->record_id_in_album);
			$aggiungi->bindValue('record_id_in_scansioni_disco', $this->record_id_in_scansioni_disco);
			$aggiungi->execute();
			$record_id = $dbh->lastInsertID();
		} catch(\Throwable $th ){
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
	 * 1. dev'essere presente un $campi[query] con istruzione sql 
	 * e tutti i campi che nell'istruzione sono marcati :nome_campo
	 * 2. sono gestite le casistiche semplici, senza lettura limitata 
	 * e senza lettura a 'partire da', qualora servano il suggerimento è di aggiungere
	 * in $campi[] un indicatore di direzione avanti/indietro e una serie 
	 * di campi "da_nome_campo", stabilito un criterio di ordinamento che può 
	 * per esempio basarsi su titolo_video 
	 * 
	 * @param array $campi  
	 * @return array ret 'ok' + 'numero' + 'data[]' | 'error' + 'message'
	 */
	public function leggi(array $campi = []) : array {
		// dati obbligatori
		$dbh = $this->conn; // a PDO object thru Database class

		if (!isset($campi["query"])){
			$ret = [
				"error"=> true, 
				"message" => __CLASS__ . ' ' . __FUNCTION__ 
				. ' Errore: ' . "Deve essere definita l'istruzione SELECT in ['query']: " 
				. $dbh::esponi($campi)
			];
			return $ret;
		}
		$read = $campi["query"];
		// convalide campi 
		if (isset($campi["record_id"])){
			$this->set_record_id($campi["record_id"]); 
		}
		if (isset($campi["titolo_video"])){
			$this->set_titolo_video($campi["titolo_video"]); 
		}
		if (isset($campi["disco"])){
			$this->set_disco($campi["disco"]); 
		}
		if (isset($campi["percorso_completo"])){
			$this->set_percorso_completo($campi["percorso_completo"]); 
		}
		if (isset($campi["record_id_in_album"])){
			$this->set_record_id_in_album($campi["record_id_in_album"]);
		}
		if (isset($campi["record_id_in_scansioni_disco"])){
			$this->set_record_id_in_scansioni_disco($campi["record_id_in_scansioni_disco"]);
		}
		if (isset($campi["ultima_modifica_record"])){
			$this->set_ultima_modifica_record($campi["ultima_modifica_record"]); 
		}
		if (isset($campi["record_cancellabile_dal"])){
			$this->set_record_cancellabile_dal($campi["record_cancellabile_dal"]); 
		}
		if (isset($campi["stato_lavori"])){
			$this->set_stato_lavori($campi["stato_lavori"]); 
		}
		try {
			$lettura = $dbh->prepare($read);
			if (isset($campi["record_id"])){
				$lettura->bindValue('record_id', $this->record_id, PDO::PARAM_INT); 
			}
			if (isset($campi["titolo_video"])){
				$lettura->bindValue('titolo_video', $this->titolo_video); 
			}
			if (isset($campi["disco"])){
				$lettura->bindValue('disco', $this->disco); 
			}
			if (isset($campi["percorso_completo"])){
				$lettura->bindValue('percorso_completo', $this->percorso_completo); 
			}
			if (isset($campi["record_id_in_album"])){
				$lettura->bindValue('record_id_in_album', $this->record_id_in_album, PDO::PARAM_INT); 
			}
			if (isset($campi["record_id_in_scansioni_disco"])){
				$lettura->bindValue('record_id_in_scansioni_disco', $this->record_id_in_scansioni_disco, PDO::PARAM_INT); 
			}
			if (isset($campi["ultima_modifica_record"])){
				$lettura->bindValue('ultima_modifica_record', $this->ultima_modifica_record ); 
			}
			if (isset($campi["record_cancellabile_dal"])){
				$lettura->bindValue('record_cancellabile_dal', $this->record_cancellabile_dal ); 
			}
			if (isset($campi["stato_lavori"])){
				$lettura->bindValue('stato_lavori', $this->stato_lavori ); 
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
		/* senza limitatore */ 
		while ($record = $lettura->fetch(PDO::FETCH_ASSOC)) {
			$dati_di_ritorno[] = $record;
			$numero++;
		}    
		/* con limitatore 
		$limite_record = isset($campi["limite"]) ? $campi["limite"] : 100;
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
	 * modifica: UPDATE + (logical) DELETE 
	 * ATTENZIONE: La modifica del campo "record_cancellabile_dal" viene 
	 *             gestita come cancellazione logica, in attesa di una fase
	 *             di scarico e cancellazione fisica.
	 * Deve essere presente un $campi["update] con istruzione SQL e tutti i $campi[nome_campo]
	 * che nell'istruzione SAL sono presenti come :nome_campo
	 *
	 * @param  array $campi 
	 * @return array $ret 'ok' + 'message' | 'error' + 'message'
	 */
	public function modifica( array $campi = []) {
		// campi indispensabili 
		$dbh = $this->conn; // a PDO object thru Database class

		if (!isset($campi["update"])){
			$ret = [
				"error"=> true, 
				"message" => __CLASS__ . ' ' . __FUNCTION__ 
				. " Aggiornamento record senza UPDATE: " . $dbh::esponi($campi) 
			];
			return $ret;
		}
		// convalide 
		if (isset($campi["record_id"])){
			$this->set_record_id($campi["record_id"]); 
		}
		if (isset($campi["titolo_video"])){
			$this->set_titolo_video($campi["titolo_video"]); 
		}
		if (isset($campi["disco"])){
			$this->set_disco($campi["disco"]); 
		}
		if (isset($campi["percorso_completo"])){
			$this->set_percorso_completo($campi["percorso_completo"]); 
		}
		if (isset($campi["record_id_in_album"])){
			$this->set_record_id_in_album($campi["record_id_in_album"]); 
		}
		if (isset($campi["record_id_in_scansioni_disco"])){
			$this->set_record_id_in_scansioni_disco($campi["record_id_in_scansioni_disco"]); 
		}
		if (isset($campi["ultima_modifica_record"])){
			$this->set_ultima_modifica_record($campi["ultima_modifica_record"]); 
		}
		if (isset($campi["record_cancellabile_dal"])){
			$this->set_record_cancellabile_dal($campi["record_cancellabile_dal"]); 
		}
		if (isset($campi["stato_lavori"])){
			$this->set_stato_lavori($campi["stato_lavori"]); 
		}
		$update = $campi["update"];
		// azione 
		try {
			$aggiorna = $dbh->prepare($update);
			if (isset($campi["record_id"])){
				$aggiorna->bindValue('record_id', $this->record_id, PDO::PARAM_INT); 
			}
			if (isset($campi["titolo_video"])){
				$aggiorna->bindValue('titolo_video', $this->titolo_video); 
			}
			if (isset($campi["disco"])){
				$aggiorna->bindValue('disco', $this->disco); 
			}
			if (isset($campi["percorso_completo"])){
				$aggiorna->bindValue('percorso_completo', $this->percorso_completo); 
			}
			if (isset($campi["record_id_in_album"])){
				$aggiorna->bindValue('record_id_in_album', $this->record_id_in_album, PDO::PARAM_INT); 
			}
			if (isset($campi["record_id_in_scansioni_disco"])){
				$aggiorna->bindValue('record_id_in_scansioni_disco', $this->record_id_in_scansioni_disco, PDO::PARAM_INT); 
			}
			if (isset($campi["ultima_modifica_record"])){
				$aggiorna->bindValue('ultima_modifica_record', $this->ultima_modifica_record); 
			}
			if (isset($campi["record_cancellabile_dal"])){
				$aggiorna->bindValue('record_cancellabile_dal', $this->record_cancellabile_dal); 
			}
			if (isset($campi["stato_lavori"])){
				$aggiorna->bindValue('stato_lavori', $this->stato_lavori); 
			}
			
			$aggiorna->execute();

		} catch( \Throwable $th ){
			$ret = [
				"error" => true,
				"message" => __CLASS__ . ' ' . __FUNCTION__ 
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
	 * Deve essere presente un $campi["delete"] con istruzione SQL e tutti i 
	 * $campi[nome_campo] che hanno nell'istruzione SQL :nome_campo 
	 * 
	 * @param  array  $campi 
	 * @return array  $ret 'ok' + 'message' | 'error' + 'message' 
	 */
	public function elimina( array $campi = []) : array {
		// indispensabili 
		$dbh = $this->conn; // a PDO object thru Database class

		if (!isset($campi["delete"])){
			$ret = [
				"error"=> true, 
				"message" => "Cancellazione record senza DELETE. " 
				. ' campi : ' . $dbh::esponi($campi) 
			];
			return $ret;
		}
		if (isset($campi["record_id"])){
			$this->set_record_id($campi["record_id"]);
		}
		if (isset($campi["titolo_video"])){
			$this->set_titolo_video($campi["titolo_video"]);
		}
		if (isset($campi["disco"])){
			$this->set_disco($campi["disco"]);
		}
		if (isset($campi["percorso_completo"])){
			$this->set_percorso_completo($campi["percorso_completo"]);
		}
		if (isset($campi["record_id_in_album"])){
			$this->set_record_id_in_album($campi["record_id_in_album"]);
		}
		if (isset($campi["record_id_in_scansioni_disco"])){
			$this->set_record_id_in_scansioni_disco($campi["record_id_in_scansioni_disco"]);
		}
		if (isset($campi["ultima_modifica_record"])){
			$this->set_ultima_modifica_record($campi["ultima_modifica_record"]);
		}
		if (isset($campi["record_cancellabile_dal"])){
			$this->set_record_cancellabile_dal($campi["record_cancellabile_dal"]);
		}
		$delete = $campi["delete"];
		try {
			$cancella = $dbh->prepare($delete);
			if (isset($campi["record_id"])){
				$cancella->bindValue('record_id', $this->record_id, PDO::PARAM_INT); 
			}
		if (isset($campi["titolo_video"])){
			$cancella->bindValue('titolo_video', $this->titolo_video); 
		}
		if (isset($campi["disco"])){
			$cancella->bindValue('disco', $this->disco); 
		}
		if (isset($campi["percorso_completo"])){
			$cancella->bindValue('percorso_completo', $this->percorso_completo); 
		}
		if (isset($campi["record_id_in_album"])){
			$cancella->bindValue('record_id_in_album', $this->record_id_in_album, PDO::PARAM_INT); 
		}
		if (isset($campi["record_id_in_scansioni_disco"])){
			$cancella->bindValue('record_id_in_scansioni_disco', $this->record_id_in_scansioni_disco, PDO::PARAM_INT); 
		}
		if (isset($campi["ultima_modifica_record"])){ 
			$cancella->bindValue('ultima_modifica_record', $this->ultima_modifica_record); 
		}
		if (isset($campi["record_cancellabile_dal"])){
			$cancella->bindValue('record_cancellabile_dal', $this->record_cancellabile_dal); 
		}
			$cancella->execute();
		} catch( \Throwable $th ){
			$ret = [
				"error" => true,
				"message" => __CLASS__ . ' ' . __FUNCTION__ 
				. ' ' . $th->getMessage() 
				. " campi: " . $dbh::esponi($campi)
				. ' istruzione SQL: ' . $delete
			];
			return $ret;
		}
		/*
		*/
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
	 * restituisce il risultato di $this->leggi 
	 * 
	 * @param  int   $video_id 
	 * @return array 'ok' + data[] | 'error' + 'message'
	 */
	public function get_video_from_id(int $video_id) : array {
		// dati obbligatori 
		$dbh = $this->conn; // a PDO object thru Database class

		// validazione
		$this->set_record_id($video_id);

		$read = 'SELECT * FROM ' . self::nome_tabella
		. ' WHERE record_cancellabile_dal = :record_cancellabile_dal  '
		. ' AND record_id = :record_id '
		. ' LIMIT 1 ';
		try {
			$lettura=$dbh->prepare($read);
			$lettura->bindValue('record_cancellabile_dal', $dbh->get_datetime_forever() ); 
			$lettura->bindValue('record_id',               $video_id, PDO::PARAM_INT); 
			$lettura->execute();

		} catch( \Throwable $th ){
			$ret = [
				'error'   => true,
				'message' => __CLASS__ . ' ' . __FUNCTION__ 
				. '<br>' . $th->getMessage() 
				. '<br>video_id: ' . $video_id
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
	} // get_video_from_id

} // Video