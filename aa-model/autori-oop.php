<?php
/**
 * @source /aa-model/autori-oop.php 
 * @author Massimo Rainato <maxrainato@libero.it>
 * 
 * Classe Autori 
 * 
 * dipendenze: DatabaseHandler connessione archivio PDO 
 * 
 * @see https://archivio.athesis77.it/tech/3-archivi-tabelle/3-7-autori_elenco/
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
 * OTHERs
 */
Class Autori extends DatabaseHandler {
  public $conn; // PDO connection database 
  public const nome_tabella = 'autori_elenco'; 

  private $tabella = 'autori_elenco';
  
  public $record_id; //         bigint(20) unsigned Auto+ PRIMARY
  public $cognome_nome; //      varchar(250) cognome, nome 
  public $detto; //             varchar(100) alias oppure cognome, nome
  public $sigla_6; //           char(6) sigla simile al codice fiscale 
  public $fisica_giuridica; //  enum() 'F', 'G'
  public $url_autore; //        varchar(250) pagina biografia
  public $ultima_modifica_record; //  datetime a uso backup 
  // record_cancellabile_dal non è prevista la cancellazione di autori, per ora 
  private static $fisica_giuridica_set = [
    'F', 'G'
  ];
  
  public function __construct(DatabaseHandler $dbh) {
    $this->conn = $dbh;

    $this->record_id        = 0; //  invalido 
    $this->cognome_nome     = ""; // invalido 
    $this->detto            = "";
    $this->sigla_6          = "";
    $this->fisica_giuridica = "F"; 
    $this->url_autore       = "";
    $this->ultima_modifica_record = $dbh->get_datetime_now();
  } // __construct

  // GETTER 
  public function get_record_id() : int {
    return $this->record_id;
  }
  public function get_cognome_nome() : string {
    return $this->cognome_nome;
  }
  public function get_detto() : string {
    return $this->detto;
  }
  public function get_sigla_6() : string {
    $sigla_6 = $this->sigla_6; 
    $sigla_6 = html_entity_decode($sigla_6, ENT_QUOTES, "UTF-8" );
    return $sigla_6;
  }
  public function get_fisica_giuridica() : string {
    return $this->fisica_giuridica;
  }
  public function get_url_autore() : string {
    return $this->url_autore;
  }
  public function get_ultima_modifica_record() : string{
    return $this->ultima_modifica_record;
  }
  // SETTER 
  public function set_record_id( int $record_id ){
    if ($record_id < 1){
      throw new Exception( __CLASS__ . ' ' . __FUNCTION__ 
      . ' Must be unsigned integer, is : ' 
      . $record_id );
    }
    $this->record_id = $record_id;
  }
  public function set_cognome_nome(string $cognome_nome){
    // ritaglio a misura
    $cognome_nome = htmlspecialchars(strip_tags($cognome_nome));
    $cognome_nome = mb_substr($cognome_nome, 0, 250);
    $this->cognome_nome = $cognome_nome;
  }
  public function set_detto(string $detto){
    // ritaglio a misura
    $detto = htmlspecialchars(strip_tags($detto));
    $detto = mb_substr($detto, 0, 100);
    $this->detto = $detto;
  }
  /**
   * @param  string $sigla_6 
   * @return void
   * 
   * Validazione:
   * 1. "" (vuoto)
   * 2. "AAA001".."AAA010"
   * 3. 6 caratteri maiuscoli 
   *    "/[A-Z]{6}/"
   * Se non conforme viene avviata una eccezione? No, viene forzato a ""
   */
  public function set_sigla_6(string $sigla_6){
    // regola base - 6 caratteri maiuscoli
    $regola_base = '/[A-Z]{6}/u';
    // lista eccezioni 
    $eccezioni_accettate = [
      '',
      'AAA001', 'AAA002', 'AAA003', 'AAA004', 'AAA005',
      'AAA006', 'AAA007', 'AAA008', 'AAA009', 'AAA010'
    ];

    // sanificazione e ritaglio a misura
    $sigla_6 = htmlspecialchars(strip_tags($sigla_6));
    $sigla_6 = mb_substr($sigla_6, 0, 6);
    $sigla_6 = strtoupper($sigla_6);
    if (in_array($sigla_6, $eccezioni_accettate)) {
      $this->sigla_6 = $sigla_6;
    } elseif (preg_match($regola_base, $sigla_6) === 1){
      $this->sigla_6 = $sigla_6;
    } else {
      $this->sigla_6 = "";
    }
  }
  
  public function set_fisica_giuridica(string $fisica_giuridica){
    // ritaglio a misura
    $fisica_giuridica = htmlspecialchars(strip_tags($fisica_giuridica));
    $fisica_giuridica = mb_substr($fisica_giuridica, 0, 1);
    if ($fisica_giuridica != 'G'){
      $fisica_giuridica = 'F';
    }
    $this->fisica_giuridica = $fisica_giuridica;
  }

  public function set_url_autore(string $url_autore){
    // ritaglio a misura
    $url_autore = htmlspecialchars(strip_tags($url_autore));
    $url_autore = mb_substr($url_autore, 0, 250);
    $this->url_autore = $url_autore;
  }
  /**
   * @param string datetime yyyy-mm-dd hh:mm:ss
   */
  public function set_ultima_modifica_record( string $ultima_modifica_record ){
  	$dbh = $this->conn; // a PDO object thru Database class
    if (!($dbh->is_datetime($ultima_modifica_record))){
      throw new Exception(__CLASS__ .' '. __FUNCTION__ 
        . '. Must be a valid datetime format yyyy-mm-dd hh:mm:ss '
        . ' it is: '. $ultima_modifica_record 
      );
    }
    $this->ultima_modifica_record = $ultima_modifica_record;
  }

  // CRUD 
  /**
   * CREATE 
   * Servono i campi da inserire in tabella
   * 
   * @param array $campi 
   * @return array $ret: ok + record_id | error + message 
   * 
   */
  public function aggiungi(array $campi =[]) : array {
    $dbh = $this->conn;

    // record_id viene assegnato 
    // ultima_modifica_record viene assegnato 
    // tutti gli altri campi sono obbligatori 
    if (!isset($campi["cognome_nome"]) || $campi["cognome_nome"] == ""){
      $ret =[
        'error' => true,
        'message' => __CLASS__ . ' ' . __FUNCTION__ 
        . '<br>Serva il campo cognome_nome'
      ];
      return $ret;
    }
    $this->set_cognome_nome($campi["cognome_nome"]);

    if (isset($campi["detto"])){
      $this->set_detto($campi["detto"]);
    }

    if (isset($campi["sigla_6"])){
      $this->set_sigla_6($campi["sigla_6"]);
    }

    if (isset($campi["fisica_giuridica"])){
      $this->set_fisica_giuridica($campi["fisica_giuridica"]);
    }

    if (isset($campi["url_autore"])){
      $this->set_url_autore($campi["url_autore"]);
    }

    $create = 'INSERT INTO ' . $this->tabella
    . ' (  cognome_nome,  detto,  sigla_6,  fisica_giuridica,  url_autore  ) VALUES '
    . ' ( :cognome_nome, :detto, :sigla_6, :fisica_giuridica, :url_autore  ) ';

    try{
      $aggiungi = $dbh->prepare($create);
      $aggiungi->bindValue('cognome_nome',     $this->cognome_nome);
      $aggiungi->bindValue('detto',            $this->detto);
      $aggiungi->bindValue('sigla_6',          $this->sigla_6);
      $aggiungi->bindValue('fisica_giuridica', $this->fisica_giuridica);
      $aggiungi->bindValue('url_autore',       $this->url_autore);
      $aggiungi->execute();
      $record_id = $dbh->lastInsertID();

    } catch( \Throwable $th ){
      $ret = [
        "record_id" => 0,
        "error"   => true,
        "message" => __CLASS__ . ' ' . __FUNCTION__ 
        . '<br>' . $th->getMessage() 
        . "<br> campi: " . $dbh::esponi($campi)
        . '<br> istruzione SQL: ' . $create 
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
   * READ
   * Oltre ai campi serve un $campi["query"] con l'istruzione da eseguire 
   * l'elenco degli autori può richiedere una paginazione 
   * ma al momento non è fornita
   * 
   * @param array $campi 
   * @return array $ret 'ok' + 'data[]' | 'error' + message 
   */
  public function leggi(array $campi = []) : array {
    $dbh = $this->conn;

    if (!isset($campi["query"])){
      $ret =[
        'error' => true,
        'message' => __CLASS__ . ' ' . __FUNCTION__ 
        . "<br>Serve l'istruzione SELECT da eseguire" 
      ];
      return $ret;
    }
    
    if(isset($campi["record_id"])){
      $this->set_record_id($campi["record_id"]);
    }
    if(isset($campi["cognome_nome"])){
      $this->set_cognome_nome($campi["cognome_nome"]);
    }
    if(isset($campi["detto"])){
      $this->set_detto($campi["detto"]);
    }
    if(isset($campi["sigla_6"])){
      $this->set_sigla_6($campi["sigla_6"]);
    }
    if(isset($campi["fisica_giuridica"])){
      $this->set_fisica_giuridica($campi["fisica_giuridica"]);
    }
    if(isset($campi["url_autore"])){
      $this->set_url_autore($campi["url_autore"]);
    }
    if(isset($campi["ultima_modifica_record"])){
      $this->set_ultima_modifica_record($campi["ultima_modifica_record"]);
    }
    $read = $campi["query"];
    try {
      $lettura=$dbh->prepare($read);
      if(isset($campi["record_id"])){
        $lettura->bindValue('record_id', $this->record_id);
      }
      if(isset($campi["cognome_nome"])){
        $lettura->bindValue('cognome_nome', $this->cognome_nome);
      }
      if(isset($campi["detto"])){
        $lettura->bindValue('detto', $this->detto);
      }
      if(isset($campi["sigla_6"])){
        $lettura->bindValue('sigla_6', $this->sigla_6);
      }
      if(isset($campi["fisica_giuridica"])){
        $lettura->bindValue('fisica_giuridica', $this->fisica_giuridica);
      }
      if(isset($campi["url_autore"])){
        $lettura->bindValue('url_autore', $this->url_autore);
      }
      if(isset($campi["ultima_modifica_record"])){
        $lettura->bindValue('ultima_modifica_record', $this->ultima_modifica_record);
      }
      $lettura->execute();

    } catch (\Throwable $th) {
      $ret = [
        "error"   => true,
        "message" => __CLASS__ . ' ' . __FUNCTION__ 
        . '<br>' . $th->getMessage() 
        . "<br> campi: " . $dbh::esponi($campi)
        . '<br> istruzione SQL: ' . $read 
      ];
      return $ret;      
    }
    $conteggio = 0;
    $lista_autori = [];
    while($record = $lettura->fetch(PDO::FETCH_ASSOC) ){
      $lista_autori[] =$record;
      $conteggio++;
    }
    $ret=[
      'ok'     => true,
      'numero' => $conteggio,
      'data'   => $lista_autori // è sempre un array
    ];
    return $ret;
  }

  /**
   * modifica
   * 
   * @param array $campi Deve contenere un campo
   */
  public function modifica(array $campi=[]) : array {
    $dbh = $this->conn; // a PDO object thru Database class

    if (!isset($campi["update"])){
      $ret = [
        "error"=> true, 
        "message" => __CLASS__ . ' ' . __FUNCTION__ 
        . "<br> Aggiornamento record senza UPDATE: " 
        . $dbh::esponi($campi) 
      ];
      return $ret;
    }
    if(isset($campi["record_id"])){
      $this->set_record_id($campi["record_id"]);
    }
    if(isset($campi["cognome_nome"])){
      $this->set_cognome_nome($campi["cognome_nome"]);
    }
    if(isset($campi["detto"])){
      $this->set_detto($campi["detto"]);
    }
    if(isset($campi["sigla_6"])){
      $this->set_sigla_6($campi["sigla_6"]);
    }
    if(isset($campi["fisica_giuridica"])){
      $this->set_fisica_giuridica($campi["fisica_giuridica"]);
    }
    if(isset($campi["url_autore"])){
      $this->set_url_autore($campi["url_autore"]);
    }
    if(isset($campi["ultima_modifica_record"])){
      $this->set_ultima_modifica_record($campi["ultima_modifica_record"]);
    }
    $update = $campi["update"];

    if (!$dbh->inTransaction()) { $dbh->beginTransaction(); }		
    try {
      $aggiorna = $dbh->prepare($update);
      if (isset($campi["record_id"])){
        $aggiorna->bindValue('record_id', $this->record_id, PDO::PARAM_INT); 
      }
      if (isset($campi["cognome_nome"])){
        $aggiorna->bindValue('cognome_nome', $this->cognome_nome); 
      }
      if (isset($campi["detto"])){
        $aggiorna->bindValue('detto', $this->detto); 
      }
      if (isset($campi["sigla_6"])){
        $aggiorna->bindValue('sigla_6', $this->sigla_6); 
      }
      if (isset($campi["fisica_giuridica"])){
        $aggiorna->bindValue('fisica_giuridica', $this->fisica_giuridica); 
      }
      if (isset($campi["url_autore"])){
        $aggiorna->bindValue('url_autore', $this->url_autore); 
      }
      if (isset($campi["ultima_modifica_record"])){
        $aggiorna->bindValue('ultima_modifica_record', $this->ultima_modifica_record); 
      }

      $aggiorna->execute();
			$dbh->commit();

    } catch (\Throwable $th) {
 			//throw $th;
			$dbh->rollBack(); 

      $ret = [
        "error" => true,
        "message" => __CLASS__ . ' ' . __FUNCTION__ . ' ' 
        . $th->getMessage() . ' campi: ' . $dbh::esponi($campi)
        . ' istruzione SQL: ' . $update
      ];
      return $ret;
    }
    $ret = [ 
      "ok"      => true,
      "message" => "Aggiornamento eseguito"
    ];
    return $ret;
  } // modifica 

  /**
   * Esegue la cancellazione fisica del record, non la cancellazione logica
   * ATTENZIONE: Esiste la gestione del campo "record_cancellabile_dal"
   *             fatta apposta per consentire di "cancellare logicamente"
   *             i record, vedi manuale tecnico amministrativo.
   * @param  array  $campi 
   * @return array  $ret 
   */
  public function elimina( array $campi = []) : array {
    $dbh = $this->conn; // a PDO object thru Database class

    if (!isset($campi["delete"])){
      $ret = [
        "error"=> true, 
        "message" => "Cancellazione record senza DELETE: " 
        . $dbh::esponi($campi) 
      ];
      return $ret;
    }
    if(isset($campi["record_id"])){
      $this->set_record_id($campi["record_id"]);
    }
    if(isset($campi["cognome_nome"])){
      $this->set_cognome_nome($campi["cognome_nome"]);
    }
    if(isset($campi["detto"])){
      $this->set_detto($campi["detto"]);
    }
    if(isset($campi["sigla_6"])){
      $this->set_sigla_6($campi["sigla_6"]);
    }
    if(isset($campi["fisica_giuridica"])){
      $this->set_fisica_giuridica($campi["fisica_giuridica"]);
    }
    if(isset($campi["url_autore"])){
      $this->set_url_autore($campi["url_autore"]);
    }
    if(isset($campi["ultima_modifica_record"])){
      $this->set_ultima_modifica_record($campi["ultima_modifica_record"]);
    }
    $delete = $campi["delete"];
    try {
      $cancella = $dbh->prepare($delete);
      if (isset($campi["record_id"])){
        $cancella->bindValue('record_id', $this->record_id, PDO::PARAM_INT); 
      }
      if (isset($campi["cognome_nome"])){
        $cancella->bindValue('cognome_nome', $this->cognome_nome); 
      }
      if (isset($campi["detto"])){
        $cancella->bindValue('detto', $this->detto); 
      }
      if (isset($campi["sigla_6"])){
        $cancella->bindValue('sigla_6', $this->sigla_6); 
      }
      if (isset($campi["fisica_giuridica"])){
        $cancella->bindValue('fisica_giuridica', $this->fisica_giuridica); 
      }
      if (isset($campi["url_autore"])){
        $cancella->bindValue('url_autore', $this->url_autore); 
      }
      if (isset($campi["ultima_modifica_record"])){
        $cancella->bindValue('ultima_modifica_record', $this->ultima_modifica_record); 
      }
      $cancella->execute();
    } catch (\Throwable $th) {
      $ret = [
        "error" => true,
        "message" => __CLASS__ . ' ' . __FUNCTION__ 
        . '<br>' . $th->getMessage() 
        . '<br>campi: ' . $dbh::esponi($campi)
        . '<br>istruzione SQL: ' . $delete
      ];
      return $ret;
    }
    $ret = [ 
      "ok" => true,
      "message" => "Cancellazione eseguita"
    ];
    return $ret;
  } // elimina 

} // Autori 
