<?php 
/**
 * @source /aa-controller/cancellazione-record-file.php
 * @author Massimo Rainato <maxrainato@libero.it>
 * 
 * Per le tabelle che hanno un campo 
 * ultima_modica_record (tutte)
 * si fa la estrazione e stampa su un file
 * dei dati in forma di istruzioni sql da usare 
 * per (eventualmente) ripristinare il contenuto alla data.
 * Se la data è convenzionalmente 1976-01-01 00:00:00
 * il backup della tabella è totale, altrimenti 
 * si parte dal datetime indicato per >= 
 * 
 */
// TEST localhost:8888/AMUVEFO-sigec-php/aa-controller/cancellazione-record-file.php 
if (!defined('ABSPATH')){
	include_once('../_config.php');
}
include_once(ABSPATH.'aa-model/database-handler-oop.php');
// verifica di abilitazione 7 

/**
 * Cancellazione fisica
 * @param string nome_tabella 
 * @param string ultimo_backup aaaa-mm-gg hh:mm:ss   
 */
function remove_record_from( string $nome_tabella, string $ultimo_backup) : array {
	$elenco_tabelle=[
		'abilitazioni_elenco',
		'album',
		'album_dettagli',
		'autori_elenco',
		'chiavi_elenco',
		'chiavi_valori_vocabolario',
		'consultatori_calendario',
		'fotografie',
		'fotografie_dettagli',
		'richieste',
		'scansioni_cartelle',
		'scansioni_disco',
		'video',
		'video_dettagli'
	];
	
	$dbh = new DatabaseHandler();
	if (!in_array($nome_tabella, $elenco_tabelle) ){
		$ret = [
			'error'   => true,
			'message' => __FUNCTION__ . ' ' 
			. '<br> Non è corretto'  
			. '<br> nome tabella: ' . $nome_tabella
		];
		return $ret;
	}
	if (! $dbh->is_datetime($ultimo_backup) ){
		$ret = [
			'error'   => true,
			'message' => __FUNCTION__ . ' ' 
			. '<br> Non è corretto'  
			. '<br> data: ' . $ultimo_backup
		];
		return $ret;
	}
	$delete = 'DELETE FROM ' . $nome_tabella
	. " WHERE record_cancellabile_dal < '$ultimo_backup' ";
	if (!$dbh->inTransaction()) { $dbh->beginTransaction(); }
	try {
		$cancella = $dbh->prepare($delete);
		$cancella->execute();
		$numero = $cancella->rowCount();
		$dbh->commit(); // per debug $dbh->rollBack();

	} catch( \Throwable $th ){
		//throw $th;
		$dbh->rollBack(); 
		$ret = [
			'error'   => true,
			'message' => __FUNCTION__ . ' ' 
			. '<br>' . $th->getMessage() 
			. '<br>Istruzione SQL: ' . $delete
		];
		return $ret;
	}
	$ret = [ 
		'ok'      => true,
		'message' => 'Cancellazione eseguita, sono stati '
		. 'eliminati dalla tabella '.$nome_tabella.' n.'
		. $numero . ' record. (zero non è un errore) '
	];
	return $ret;
} // remove_record_from()


/** 
 * Serve a resettare la data record_cancellabile_dal 
 * - album 
 *  +- album_dettagli 
 *  +- fotografie 
 *  +-- fotografie_dettagli 
 *  +- video 
 *  +-- video_dettagli 
 *  
 */
function reset_record_cancellabile_dal(int $album_id) : array {
	$dbh = new DatabaseHandler();
	$record_cancellabile_dal = $dbh->get_datetime_now();
	if (!$dbh->inTransaction()) { $dbh->beginTransaction(); }
	// album 
	$update = "UPDATE album "
	. " SET record_cancellabile_dal = '".$record_cancellabile_dal."' "
	. " WHERE record_id = " . $album_id;
	try {
		$modifica = $dbh->prepare($update);
		$modifica->execute();
		$numero_album = $modifica->rowCount();
		// se non si può fare un commit alla fine, farlo man mano
		$dbh->commit(); // per debug $dbh->rollBack();

	} catch( \Throwable $th ){
		//throw $th;
		$dbh->rollBack(); 
		$ret = [
			'error'   => true,
			'message' => __FUNCTION__ . ' ' 
			. '<br>' . $th->getMessage() 
			. '<br>Istruzione SQL: ' . $update
		];
		return $ret;
	}
	// album_dettagli 
	$update = "UPDATE album_dettagli "
	. " SET record_cancellabile_dal = '".$record_cancellabile_dal."' "
	. " WHERE record_id_padre = " . $album_id;
	try {
		$modifica = $dbh->prepare($update);
		$modifica->execute();
		$numero_album_dettagli = $modifica->rowCount();
		$dbh->commit(); // per debug $dbh->rollBack();

	} catch( \Throwable $th ){
		//throw $th;
		$dbh->rollBack(); 
		$ret = [
			'error'   => true,
			'message' => __FUNCTION__ . ' ' 
			. '<br>' . $th->getMessage() 
			. '<br>Istruzione SQL: ' . $update
		];
		return $ret;
	}
	// fotografie
	$update = "UPDATE fotografie "
	. " SET record_cancellabile_dal = '".$record_cancellabile_dal."' "
	. " WHERE record_id_in_album = " . $album_id;
	try {
		$modifica = $dbh->prepare($update);
		$modifica->execute();
		$numero_fotografie = $modifica->rowCount();
		$dbh->commit(); // per debug $dbh->rollBack();

	} catch( \Throwable $th ){
		//throw $th;
		$dbh->rollBack(); 
		$ret = [
			'error'   => true,
			'message' => __FUNCTION__ . ' ' 
			. '<br>' . $th->getMessage() 
			. '<br>Istruzione SQL: ' . $update
		];
		return $ret;
	}
	// fotografie_dettagli
	$update = "UPDATE fotografie_dettagli fd "
	. " SET fd.record_cancellabile_dal = '".$record_cancellabile_dal."' "
	. " WHERE fd.record_id_padre IN "
		. "( SELECT f.record_id FROM fotografie f "
		. " WHERE f.record_id_in_album = $album_id ) ";
	try {
		$modifica = $dbh->prepare($update);
		$modifica->execute();
		$numero_fotografie_dettagli = $modifica->rowCount();
		$dbh->commit(); // per debug $dbh->rollBack();

	} catch( \Throwable $th ){
		//throw $th;
		$dbh->rollBack(); 
		$ret = [
			'error'   => true,
			'message' => __FUNCTION__ . ' ' 
			. '<br>' . $th->getMessage() 
			. '<br>Istruzione SQL: ' . $update
		];
		return $ret;
	}
	// video
	$update = "UPDATE video "
	. " SET record_cancellabile_dal = '".$record_cancellabile_dal."' "
	. " WHERE record_id_in_album = " . $album_id;
	try {
		$modifica = $dbh->prepare($update);
		$modifica->execute();
		$numero_video = $modifica->rowCount();
		$dbh->commit(); // per debug $dbh->rollBack();

	} catch( \Throwable $th ){
		//throw $th;
		$dbh->rollBack(); 
		$ret = [
			'error'   => true,
			'message' => __FUNCTION__ . ' ' 
			. '<br>' . $th->getMessage() 
			. '<br>Istruzione SQL: ' . $update
		];
		return $ret;
	}
	// video_dettagli
	$update = "UPDATE video_dettagli vd "
	. " SET vd.record_cancellabile_dal = '".$record_cancellabile_dal."' "
	. " WHERE vd.record_id_padre IN "
		. "( SELECT v.record_id FROM video v "
		. " WHERE v.record_id_in_album = $album_id ) ";
	try {
		$modifica = $dbh->prepare($update);
		$modifica->execute();
		$numero_video_dettagli = $modifica->rowCount();
		$dbh->commit(); // per debug $dbh->rollBack();

	} catch( \Throwable $th ){
		//throw $th;
		$dbh->rollBack(); 
		$ret = [
			'error'   => true,
			'message' => __FUNCTION__ . ' ' 
			. '<br>' . $th->getMessage() 
			. '<br>Istruzione SQL: ' . $update
		];
		return $ret;
	}
	// se si può fare un solo commit alla fine farlo qui 
	// $dbh->commit();
	$ret = [
		'ok'      => true,
		'message' => __FUNCTION__ . ' ' 
		. '<br>Sono stati aggiornati: ' 
		. '<br>- ' . $numero_album . ' album; ' 
		. '<br>- ' . $numero_album_dettagli. ' dettagli album ' 
		. '<br>- ' . $numero_fotografie . ' fotografie ' 
		. '<br>- ' . $numero_fotografie_dettagli . ' dettagli fotografie ' 
		. '<br>- ' . $numero_video . ' video ' 
		. '<br>- ' . $numero_video_dettagli . ' dettagli video ' 
		. '<hr>' 
	];
	return $ret;
} // reset_record_cancellabile_dal()

/**
 * La tabella album è legata alla tabella album_dettagli e 
 * viceversa. Quando un album viene marcato come cancellabile 
 * dovrebbero essere marcati come cancellabili anche tutti i suoi dettagli 
 * se non è così tutto, album dettagli e file collegati 
 * vengono marcati come cancellabili da ora (si presume data > $ultimo_backup)
 */
function remove_record_album(string $ultimo_backup) : array{
	$dbh = new DatabaseHandler();
	if (! $dbh->is_datetime($ultimo_backup) ){
		$ret = [
			'error'   => true,
			'message' => __FUNCTION__ . ' ' 
			. '<br> Non è corretto'  
			. '<br> data: ' . $ultimo_backup
		];
		return $ret;
	}

	// 1. rintracciare i cancellabili 
	$leggi = 'SELECT count(*) AS numero FROM album '
	. " WHERE record_cancellabile_dal < '$ultimo_backup' ";
	$lettura = $dbh->prepare($leggi);
	$ret_letti = $lettura->execute();
	$numero = $lettura->fetchColumn(0);
	if ($numero == 0 && $ultimo_backup==''){ // per continuare elaborazione ma 
		$ret = [ 
			'ok'      => true,
			'message' => 'Cancellazione eseguita, sono stati '
			. 'eliminati dalla tabella album_dettagli n.'
			. $numero . ' record. (zero non è un errore) '
		];
		return $ret;
	}
	echo '<br> album cancellabili: ' . $numero;
	// 1. cercare tutti gli album cancellabili - loop
		// 2. Se si trova un album con dettagli ancora validi 
		// 2.1. aggiornare la data cancellabile_dal dell'album "a oggi"
		// 2.2. aggiornare la data cancellabile_dal dei dettagli "a oggi"
		// 3. proseguire 

	$leggi = 'SELECT * FROM album '
	. " WHERE record_cancellabile_dal < '$ultimo_backup' ";
	try {
		$lettura = $dbh->prepare($leggi);
		$lettura->execute();

	} catch( \Throwable $th ){
		//throw $th;
		$ret = [
			'error'   => true,
			'message' => __FUNCTION__ . ' ' 
			. '<br>' . $th->getMessage() 
			. '<br>Istruzione SQL: ' . $leggi
		];
		return $ret;
	}
	while ($album = $lettura->fetch(PDO::FETCH_ASSOC)) {
		$aggiornare_album=false;
		$leggi_dettagli = 'SELECT * FROM album_dettagli '
		. " WHERE record_id_padre = " . $album['record_id']
		. " AND record_cancellabile_dal > '".$album['record_cancellabile_dal']."' "; 
		try {
			$lettura_dettagli = $dbh->prepare($leggi_dettagli);
			$lettura_dettagli->execute();
	
		} catch( \Throwable $th ){
			//throw $th;
			$ret = [
				'error'   => true,
				'message' => __FUNCTION__ . ' ' 
				. '<br>' . $th->getMessage() 
				. '<br>Istruzione SQL: ' . $leggi_dettagli
			];
			return $ret;
		}
		while ($record = $lettura_dettagli->fetch(PDO::FETCH_ASSOC)) {
			$aggiornare_album = true;
			break;
		} // while

		if ($aggiornare_album){
			$record_cancellabile_dal = $dbh->get_datetime_now();
			$update = ' UPDATE album '
			. " SET record_cancellabile_dal = '".$record_cancellabile_dal."' "
			. " WHERE record_id = " . $album['record_id'];
			if (!$dbh->inTransaction()) { $dbh->beginTransaction(); }
			try {
				$modifica = $dbh->prepare($update);
				$modifica->execute();
				$numero = $modifica->rowCount();
				$dbh->commit(); // per debug $dbh->rollBack();
		
			} catch( \Throwable $th ){
				//throw $th;
				$dbh->rollBack(); 
				$ret = [
					'error'   => true,
					'message' => __FUNCTION__ . ' ' 
					. '<br>' . $th->getMessage() 
					. '<br>Istruzione SQL: ' . $update
				];
				return $ret;
			}
			// dettagli 
			$update = ' UPDATE album_dettagli '
			. " SET record_cancellabile_dal = '".$record_cancellabile_dal."' "
			. " WHERE record_id_padre = " . $album['record_id'];
			if (!$dbh->inTransaction()) { $dbh->beginTransaction(); }
			try {
				$modifica = $dbh->prepare($update);
				$modifica->execute();
				$numero = $modifica->rowCount();
				$dbh->commit(); // per debug $dbh->rollBack();
		
			} catch( \Throwable $th ){
				//throw $th;
				$dbh->rollBack(); 
				$ret = [
					'error'   => true,
					'message' => __FUNCTION__ . ' ' 
					. '<br>' . $th->getMessage() 
					. '<br>Istruzione SQL: ' . $update
				];
				return $ret;
			}		
		}
	} // while
	// 4. cancellazione degli album che sono (ancora) cancellabili 
	//    con la data passata precedente alla data del backup
	$ret = remove_record_from('album', $ultimo_backup);
	return $ret;
} // remove_record_album()



function remove_record_fotografie(string $ultimo_backup) : array {
	$dbh = new DatabaseHandler();
	if (! $dbh->is_datetime($ultimo_backup) ){
		$ret = [
			'error'   => true,
			'message' => __FUNCTION__ . ' ' 
			. '<br> Non è corretto'  
			. '<br> data: ' . $ultimo_backup
		];
		return $ret;
	}
	// 1. cercare tutte le foto cancellabili - loop
	// 2. Se si trova una foto con dettagli ancora validi 
	// 2.1. aggiornare la data cancellabile_dal della foto "a oggi"
	// 2.2. aggiornare la data cancellabile_dal dei dettagli "a oggi"
	// 3. proseguire 
	$leggi_foto = "SELECT * FROM fotografie " 
	. " WHERE record_cancellabile_dal < '$ultimo_backup' ";
	try {
		$lettura_foto = $dbh->prepare($leggi_foto);
		$lettura_foto->execute();

	} catch( \Throwable $th ){
		//throw $th;
		$ret = [
			'error'   => true,
			'message' => __FUNCTION__ . ' ' 
			. '<br>' . $th->getMessage() 
			. '<br>Istruzione SQL: ' . $leggi_foto
		];
		return $ret;
	}
	while ($foto = $lettura_foto->fetch(PDO::FETCH_ASSOC)) {
		$aggiorna = false;
		$leggi_dettagli = "SELECT * from fotografie_dettagli "
		. " WHERE record_id_padre = " .$foto['record_id'] 
		. " AND record_cancellabile_dal > '".$foto['record_cancellabile_dal']."' "; 
		try {
			$lettura_dettagli = $dbh->prepare($leggi_dettagli);
			$lettura_dettagli->execute();
	
		} catch( \Throwable $th ){
			//throw $th;
			$ret = [
				'error'   => true,
				'message' => __FUNCTION__ . ' ' 
				. '<br>' . $th->getMessage() 
				. '<br>Istruzione SQL: ' . $leggi_dettagli 
			];
			return $ret;
		}
		while ($record = $lettura_dettagli->fetch(PDO::FETCH_ASSOC)) {
			$aggiornare = true;
			break;
		} // while loop dettagli

		// se ci sono dettagli ancora validi o cancellati dopo $ultimo backup 
		// si rinvia la cancellazione al data futura 
		if ($aggiornare){
			// aggiorno foto record_cancellabile_dal 
			// aggiorno dettagli foto record_cancellabile_dal 
			$record_cancellabile_dal = $dbh->get_datetime_now();
			$update = " UPDATE fotografie "
			. " SET record_cancellabile_dal = '".$record_cancellabile_dal."' "
			. " WHERE record_id = " . $foto['record_id'];
			if (!$dbh->inTransaction()) { $dbh->beginTransaction(); }
			try {
				$modifica = $dbh->prepare($update);
				$modifica->execute();
				$numero = $modifica->rowCount();
				$dbh->commit(); // per debug $dbh->rollBack();
		
			} catch( \Throwable $th ){
				//throw $th;
				$dbh->rollBack(); 
				$ret = [
					'error'   => true,
					'message' => __FUNCTION__ . ' ' 
					. '<br>' . $th->getMessage() 
					. '<br>Istruzione SQL: ' . $update
				];
				return $ret;
			} // update fotografie

			// dettagli 
			$update = "UPDATE fotografie_dettagli "
			. " SET record_cancellabile_dal = '".$record_cancellabile_dal."' "
			. " WHERE record_id_padre = " . $foto['record_id'];
			if (!$dbh->inTransaction()) { $dbh->beginTransaction(); }
			try {
				$modifica = $dbh->prepare($update);
				$modifica->execute();
				$numero = $modifica->rowCount();
				$dbh->commit(); // per debug $dbh->rollBack();
		
			} catch( \Throwable $th ){
				//throw $th;
				$dbh->rollBack(); 
				$ret = [
					'error'   => true,
					'message' => __FUNCTION__ . ' ' 
					. '<br>' . $th->getMessage() 
					. '<br>Istruzione SQL: ' . $update
				];
				return $ret;
			} // update fotografie_dettagli 

		} // aggiornare 

	} // while loop foto 

	// cercare le fotografie non cancellate degli album cancellabili 
	$leggi = "SELECT f.record_id, f.record_id_in_album FROM fotografie f, album a "
	. " WHERE a.record_cancellabile_dal < '9999-12-31 23:59:59' "
	. " AND f.record_cancellabile_dal > a.record_cancellabile_dal "
	. " AND f.record_id_in_album = a.record_id ";
	try {
		$lettura = $dbh->prepare($leggi);
		$lettura->execute();

	} catch( \Throwable $th ){
		//throw $th;
		$ret = [
			'error'   => true,
			'message' => __FUNCTION__ . ' ' 
			. '<br>' . $th->getMessage() 
			. '<br>Istruzione SQL: ' . $leggi
		];
		return $ret;
	}
	$album_aggiornati=[];
	while ($foto = $lettura_foto->fetch(PDO::FETCH_ASSOC)) {
		$album_id = $foto['record_id_in_album'];
		if (!in_array($album_id, $album_aggiornati)){
			$album_aggiornati[]=$album_id;
			$ret = reset_record_cancellabile_dal($album_id);
		}
	}	// while foto 
	// 4. cancellazione delle fotografie che sono (ancora) cancellabili 
	//    con la data passata precedente alla data del backup
	$ret = remove_record_from('fotografie', $ultimo_backup);
	return $ret;
} // remove_record_fotografie()



function remove_record_video(string $ultimo_backup) : array {
	$dbh = new DatabaseHandler();
	if (! $dbh->is_datetime($ultimo_backup) ){
		$ret = [
			'error'   => true,
			'message' => __FUNCTION__ . ' ' 
			. '<br> Non è corretto'  
			. '<br> data: ' . $ultimo_backup
		];
		return $ret;
	}
	// 1. cercare tutti i video cancellabili - loop
	// 2. Se si trova un video con dettagli ancora validi 
	// 2.1. aggiornare la data cancellabile_dal del video "a oggi"
	// 2.2. aggiornare la data cancellabile_dal dei dettagli "a oggi"
	// 3. proseguire 
	$leggi_video = "SELECT * FROM video " 
	. " WHERE record_cancellabile_dal < '$ultimo_backup' ";
	try {
		$lettura_video = $dbh->prepare($leggi_video);
		$lettura_video->execute();

	} catch( \Throwable $th ){
		//throw $th;
		$ret = [
			'error'   => true,
			'message' => __FUNCTION__ . ' ' 
			. '<br>' . $th->getMessage() 
			. '<br>Istruzione SQL: ' . $leggi_video
		];
		return $ret;
	}
	while ($video = $lettura_video->fetch(PDO::FETCH_ASSOC)) {
		$aggiornare=false;
		$leggi_dettagli = "SELECT * from video_dettagli "
		. " WHERE record_id_padre = " .$video['record_id'] 
		. " AND record_cancellabile_dal > '".$video['record_cancellabile_dal']."' "; 
		try {
			$lettura_dettagli = $dbh->prepare($leggi_dettagli);
			$lettura_dettagli->execute();
			
		} catch( \Throwable $th ){
			//throw $th;
			$ret = [
				'error'   => true,
				'message' => __FUNCTION__ . ' ' 
				. '<br>' . $th->getMessage() 
				. '<br>Istruzione SQL: ' . $leggi_dettagli 
			];
			return $ret;
		}
		while ($record = $lettura_dettagli->fetch(PDO::FETCH_ASSOC)) {
			$aggiornare = true;
			break;
		} // while loop dettagli

		// se ci sono dettagli ancora validi o cancellati dopo $ultimo backup 
		// si rinvia la cancellazione al data futura 
		if ($aggiornare){
			// aggiorno foto record_cancellabile_dal 
			// aggiorno dettagli foto record_cancellabile_dal 
			$record_cancellabile_dal = $dbh->get_datetime_now();
			$update = " UPDATE video "
			. " SET record_cancellabile_dal = '".$record_cancellabile_dal."' "
			. " WHERE record_id = " . $video['record_id'];
			if (!$dbh->inTransaction()) { $dbh->beginTransaction(); }
			try {
				$modifica = $dbh->prepare($update);
				$modifica->execute();
				$numero = $modifica->rowCount();
				$dbh->commit(); // per debug $dbh->rollBack();
		
			} catch( \Throwable $th ){
				//throw $th;
				$dbh->rollBack(); 
				$ret = [
					'error'   => true,
					'message' => __FUNCTION__ . ' ' 
					. '<br>' . $th->getMessage() 
					. '<br>Istruzione SQL: ' . $update
				];
				return $ret;
			} // update video

			// dettagli 
			$update = "UPDATE video_dettagli "
			. " SET record_cancellabile_dal = '".$record_cancellabile_dal."' "
			. " WHERE record_id_padre = " . $video['record_id'];
			if (!$dbh->inTransaction()) { $dbh->beginTransaction(); }
			try {
				$modifica = $dbh->prepare($update);
				$modifica->execute();
				$numero = $modifica->rowCount();
				$dbh->commit(); // per debug $dbh->rollBack();
		
			} catch( \Throwable $th ){
				//throw $th;
				$dbh->rollBack(); 
				$ret = [
					'error'   => true,
					'message' => __FUNCTION__ . ' ' 
					. '<br>' . $th->getMessage() 
					. '<br>Istruzione SQL: ' . $update
				];
				return $ret;
			} // update fotografie_dettagli 

		} // aggiornare 

	} // while loop foto 

	// cercare le fotografie non cancellate degli album cancellabili 
	$leggi = "SELECT v.record_id, v.record_id_in_album FROM video v, album a "
	. " WHERE a.record_cancellabile_dal < '9999-12-31 23:59:59' "
	. " AND v.record_cancellabile_dal > a.record_cancellabile_dal "
	. " AND v.record_id_in_album = a.record_id ";
	try {
		$lettura = $dbh->prepare($leggi);
		$lettura->execute();

	} catch( \Throwable $th ){
		//throw $th;
		$ret = [
			'error'   => true,
			'message' => __FUNCTION__ . ' ' 
			. '<br>' . $th->getMessage() 
			. '<br>Istruzione SQL: ' . $leggi
		];
		return $ret;
	}
	$album_aggiornati=[];
	while ($video = $lettura_video->fetch(PDO::FETCH_ASSOC)) {
		$album_id = $video['record_id_in_album'];
		if (!in_array($album_id, $album_aggiornati)){
			$album_aggiornati[]=$album_id;
			$ret = reset_record_cancellabile_dal($album_id);
		}
	}	// while video 
	// 4. cancellazione dei video che sono (ancora) cancellabili 
	//    con la data passata precedente alla data del backup
	$ret = remove_record_from('video', $ultimo_backup);
	return $ret;
} // remove_record_video()



function remove_record_tutti(){ 
	// Legge il file contenente il precedente datetime di backup
	$config_file = ABSPATH.'aa-backup/.config';
	if (!is_file($config_file)){
		$ret = set_ultimo_backup('1980-01-01 00:00:00');
	}
	$env = file_get_contents($config_file); 
	$lines = explode("\n",$env);
	foreach($lines as $line){
		preg_match("/([^#]+)\=(.*)/",$line,$matches);
		if(isset($matches[2])){
			putenv(trim($line));
		}
	} 
	$dbh = New DatabaseHandler(); // connessione archivio

	$ultimo_backup = getenv('ULTIMO_BACKUP'); //esce con gli apici 
	$ultimo_backup = str_ireplace("'", '', $ultimo_backup);

	if ($ultimo_backup >= $dbh->get_datetime_now()){
		echo '<p style="color:red;font-family:monospace;">'
		. 'Eseguire almeno un backup, cancellazione non consentita.</p>';
		exit(1); 
	}

	echo '<p>Avvio cancellazione fino al: ' . $ultimo_backup;

	$abilitazioni    = remove_record_from('abilitazioni_elenco', $ultimo_backup);
	echo '<p>Abilitazioni: '.$abilitazioni['message'].'</p>';
	
	// album_dettagli sono da cancellare prima di album, tuttavia 
	// album deve essere cancellato dopo che sono cancellati 
	//   gli elenchi e tabelle che fanno riferimento alla tabella album.

	// autori_elenco non cancellabile con questo processo 	
	// chiavi_valori sono cancellabili solo se non sono usati in altre 6 tabelle
	// chiavi_valori_vocabolario come sopra

	$consultatori_calendario=remove_record_from('consultatori_calendario', $ultimo_backup);
	echo '<p>Consultatori_calendario: '.$consultatori_calendario['message'].'</p>';

	// si rimuovono i dettagli già cancellati 
	$fotografie_dettagli=remove_record_from('fotografie_dettagli', $ultimo_backup);
	echo '<p>Fotografie_dettagli: '.$fotografie_dettagli['message'].'</p>';
	// si rimuovono le fotografie già cancellabili e si aggiornano 
	$fotografie=remove_record_fotografie($ultimo_backup);
	echo '<p>Fotografie: '.$fotografie['message'].'</p>';
	
	$video_dettagli = remove_record_from('video_dettagli', $ultimo_backup);
	echo '<p>video_dettagli: '.$video_dettagli['message'].'</p>';
	$video = remove_record_video($ultimo_backup);
	echo '<p>video: '.$video['message'].'</p>';

	$album_dettagli  = remove_record_from('album_dettagli', $ultimo_backup);
	echo '<p>Dettagli Album: '.$album_dettagli['message'].'</p>';
	$album           = remove_record_album($ultimo_backup);
	echo '<p>Album: '.$album['message'].'</p>';

	$scansioni_cartelle = remove_record_from('scansioni_cartelle', $ultimo_backup);
	echo '<p>Cartelle: '. $scansioni_cartelle['message'] .'</p>';

	$scansioni_disco    = remove_record_from('scansioni_disco', $ultimo_backup);
	echo '<p>Deposito: '. $scansioni_disco['message'] .'</p>';

	require_once(ABSPATH.'aa-view/cancellazione-eseguita.php');
	exit(0); 					
} // remove_record_tutti

/** TEST 
 * https://archivio.athesis77.it/aa-controller/cancellazione-record-file.php
 * https://www.fotomuseoathesis.it/aa-controller/cancellazione-record-file.php
 * 
 */

remove_record_tutti();
exit(0); 
