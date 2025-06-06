<?php
/**
 *	@source /aa-model/database-handler.php 
 *  @author Massimo Rainato <maxrainato@libero.it>
 * 
 *  classe DatabaseHandler estende classe PDO PHP Data Objects
 *
 *	Fornisce una connessione all'archivio dbh e i seguenti metodi 
 *	close                               
 *	get_datetime_format()               
 *	get_datetime_forever()              
 *	get_datetime_now()                  
 *	is_datetime()                        
 *	I parametri per la connessione sono forniti attraverso _config.php
 *	
 */
if (!defined('ABSPATH')) {
  include_once("../_config.php");
}

class DatabaseHandler extends PDO {
	public  $conn; // connessione all'archivio

	/**
	 * construct 
	 * 
	 * @param string $host   database access ip host 
	 * @param string $user   database access user 
	 * @param string $pass   database access password 
	 * @param string $dbname database access name
	 * @return PDO active database connection
	 */
	public function __construct($host='',$user='',$pass='',$dbname=''){
		// Si possono inserire dei parametri diversi
		$db_host    = ($host)   ? $host   : getenv('DB_HOST'); // ip server  localhost:3306
		$db_user    = ($user)   ? $user   : getenv('DB_USER');
		$db_pass    = ($pass)   ? $pass   : getenv('DB_PASSWORD');
		$db_name    = ($dbname) ? $dbname : getenv('DB_NAME'); // archivio
	//$db_name    = 'Sql1515403_1'; // wordpress venetus.eu dal 2021
	//$db_name    = 'Sql1515403_2'; // wordpress /man e /tech
	//$db_name    = 'Sql1515403_3'; // a disposizione (abilitazioni + registro)
	//$db_name    = 'Sql1515403_4'; // archivio
	//$db_name    = 'Sql1515403_5'; // a disposizione
	//	https://mysql.aruba.it

		// PDO connessione ad archivio mysql - se non va bene viene scatenata una "eccezione"
		try {
			$dsn = 'mysql:host=' . $db_host . ';dbname=' . $db_name . ';charset=utf8mb4';
			parent::__construct( $dsn, $db_user, $db_pass);

		} catch(PDOException $PEx) {
			throw new Exception("Non è stato possibile collegarsi all'archivio {$db_name} per un errore " . $PEx->getMessage() );
		}
	}

	/**
	 * @param  void 
	 * @return bool
	 */
	public function close(){
		$this->conn = null;
		return true;
	}

	/**
	 * @param void 
	 * @return string datetime format 
	 */
	public function get_datetime_format() : string {
		return "Y-m-d H:i:s";
	}

	/**
	 * @param void 
	 * @return string datetime format const
	 */
	public function get_datetime_forever() : string {
		return "9999-12-31 23:59:59";
	}

	/**
	 * @param void 
	 * @return string datetime formatted "now"
	 */
	public function get_datetime_now() : string {
		date_default_timezone_set('Europe/Rome');
		return date("Y-m-d H:i:s");
	}

	/**
	 * Check if a string is valid for a datetime mysql field
	 * 
	 * @param  string $check_me  
	 * @return bool 
	 *  
	 * ToDo: An alternative should be a dummy table with a datetime field 
	 *       and try catch to update field with input parm. 
	 *       try (update) 
	 *       catch return false 
	 *       return true 
	 */
	public function is_datetime(string $check_me) : bool {
		$datetime_pattern = '/^(\d{4})(-)(\d{2})(-)(\d{2})(\s)(\d{2})(:)(\d{2})(:)(\d{2})$/';
	
		if (!('string' === gettype($check_me))){
			return false;
		}

		// aaaa-mm-gg hh:mm:ss
		if (0 == preg_match( $datetime_pattern, $check_me, $datetime)){
			return false;
		}

		$anno    = $datetime[1]; // string 0000-9999
		$mese    = $datetime[3]; // string 01-12
		$giorno  = $datetime[5]; // string 01-31 dipende da $mese e $anno
		$ora     = $datetime[7]; // string 00-23
		$minuti  = $datetime[9]; // string 00-59
		$secondi = $datetime[11]; //string 00-59

		if ($ora > '23' || $minuti > '59' || $secondi > '59'){
			return false;
		}

		if ($anno > '9999' || $mese > '12' || $giorno > '31' ){
			return false;
		}

		if (($giorno > '30') && ($mese == '11' || $mese == '04' || $mese == '06' || $mese == '09')){
			return false;
		}

		if ( $giorno > '29' && $mese == '02'){
			return false;
		}
		
		if ( $giorno == '29' && $mese == '02'){
			if ($anno % 4){
				return false;
			}
			if (($anno % 400) && (!($anno % 100))){
				return false;
			}
		}
		return true;
	} // is_datetime( string )

	/**
	 * @param  int   record_id
	 * @return bool
	 */
	public function is_unsigned_int(int $record_id) : bool {
		return ($record_id < 1) ? false : true;
	} // is_unsigned_int

	/**
	 * serialize, in versione un po' più leggibile
	 * @return string html 
	 */
	public static function esponi($a){
		$s = str_ireplace(';', '; ', serialize($a));
		$s = str_ireplace('{', '{ '."\n", $s);
		$s = str_ireplace('}', ' }'."\n", $s);
		return $s;		
	}

} // class DatabaseHandler

