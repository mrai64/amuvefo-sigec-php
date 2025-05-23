<?php 
/**
 * @source /aa-controller/scrivi-backup-file.php
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
 * Nota: Accesso diretto alle tabelle, non si passa per i model.
 * I model ci son per tanti ma non per tutti, serve realizzare un 
 * generatore di php che scriva il model in automatico partendo
 * da una tabella bidimensionale di dati - definizioni campi
 */
if (!defined('ABSPATH')){
	include_once('../_config.php');
}
include_once(ABSPATH.'aa-model/database-handler-oop.php');
// verifica di abilitazione 7 


$elenco_tabelle=[
	'abilitazioni_elenco',
	'appunti_sql_elenco',
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

function set_ultimo_backup(string $ultimo_backup){
	$config_file = ABSPATH.'aa-backup/.config';
	$dbh = New DatabaseHandler(); 
	// verifica formato 
	if (!$dbh->is_datetime($ultimo_backup) ){
		$ultimo_backup = '1980-01-01 00:00:00';
	}

	$config_txt = '# Ultimo backup del '. "\n"
	. "ULTIMO_BACKUP='$ultimo_backup'\n\n";
	file_put_contents($config_file, $config_txt);

} // set_ultimo_backup

function get_backup_header(string $ultimo_backup) : string {
	$ret = '-- Archivio: '. URLBASE ."\n"
	. '-- Backup del: '. date("d/m/Y"). ' alle '. date("H:i")."\n" 
	. '-- Ultimo backup: '. $ultimo_backup . "\n".'-- '."\n";
	return $ret;
} // get_backup_header

function get_backup_abilitazioni(string $ultimo_backup) : string { 
	$dbh = New DatabaseHandler(); 
	$ret = '';
	$riempire = 'INSERT INTO `abilitazioni_elenco` (`record_id`,'
	. ' `url_pagina`, `operazione`, `abilitazione`, '
	. ' `ultima_modifica_record`, `record_cancellabile_dal`) VALUES('
	. "§1, '§2', '§3', '§4', '§5', '§6');";

	$leggi = 'SELECT * FROM abilitazioni_elenco ' 
	. " WHERE ultima_modifica_record >= '$ultimo_backup' ";
	try{
	$lettura = $dbh->prepare($leggi);
	$record  = $lettura->execute(); 

	} catch( \Throwable $th ){
	    $ret = "\n".'Spiacenti, in '. __FUNCTION__ 
	    . ' si è verificato un errore '
	    . "\n" . $th->getMessage() 
	    . "\n".'istruzione: '. $leggi;
	    return $ret;
	}
	// loop 
	$ret .= "\n\n".'--'
	. "\n". '-- Dump dei dati per la tabella `abilitazioni_elenco`'
	. "\n". '--'."\n";
	while ($record = $lettura->fetch(PDO::FETCH_ASSOC)) {
		$rigo = str_ireplace('§1', $record['record_id'], $riempire);
		$rigo = str_ireplace('§2', $record['url_pagina'], $rigo);
		$rigo = str_ireplace('§3', $record['operazione'], $rigo);
		$rigo = str_ireplace('§4', $record['abilitazione'], $rigo);
		$rigo = str_ireplace('§5', $record['ultima_modifica_record'], $rigo);
		$rigo = str_ireplace('§6', $record['record_cancellabile_dal'], $rigo);
		$ret .= "\n".$rigo;
	}
    return $ret;
} // get_backup_abilitazioni

function get_backup_album(string $ultimo_backup) : string { 
	$dbh = New DatabaseHandler(); 
	$ret = '';
	$riempire = 'INSERT INTO `album` (`record_id`, `titolo_album`, '
	. '`disco`, `percorso_completo`, `record_id_in_scansioni_disco`, '
	. ' `stato_lavori`, `ultima_modifica_record`, `record_cancellabile_dal`)'
	. " VALUES(§1, '§2', '§3', '§4', §5, '§6', '§7', '§8');";

	$leggi = 'SELECT * FROM album ' 
	. " WHERE ultima_modifica_record >= '$ultimo_backup' ";
	try{
	$lettura = $dbh->prepare($leggi);
	$record  = $lettura->execute(); 

	} catch( \Throwable $th ){
	    $ret = "\n".'Spiacenti, in '. __FUNCTION__ 
	    . ' si è verificato un errore '
	    . "\n" . $th->getMessage() 
	    . "\n".'istruzione: '. $leggi;
	    return $ret;
	}
	// loop 
	$ret .= "\n\n".'--'
	. "\n". '-- Dump dei dati per la tabella `album`'
	. "\n". '--'."\n";
	while ($record = $lettura->fetch(PDO::FETCH_ASSOC)) {
		$rigo = str_ireplace('§1', $record['record_id'], $riempire);
		$rigo = str_ireplace('§2', $record['titolo_album'], $rigo);
		$rigo = str_ireplace('§3', $record['disco'], $rigo);
		$rigo = str_ireplace('§4', $record['percorso_completo'], $rigo);
		$rigo = str_ireplace('§5', $record['record_id_in_scansioni_disco'], $rigo);
		$rigo = str_ireplace('§6', $record['stato_lavori'], $rigo);
		$rigo = str_ireplace('§7', $record['ultima_modifica_record'], $rigo);
		$rigo = str_ireplace('§8', $record['record_cancellabile_dal'], $rigo);
		$ret .= "\n".$rigo;
	}
    return $ret;
    
} // get_backup_album

// aggiornato con consultatore_id 
function get_backup_album_dettagli(string $ultimo_backup) : string {
	$dbh = New DatabaseHandler(); 
	$ret = '';
	$riempire = "INSERT INTO `album_dettagli` (`record_id`, `record_id_padre`, "
	. "`chiave`, `valore`, `consultatore_id`, `ultima_modifica_record`, `record_cancellabile_dal`) "
	. "VALUES(§1, §2, '§3', '§4', §5, '§6', '§7');";

	$leggi = 'SELECT * FROM album_dettagli ' 
	. " WHERE ultima_modifica_record >= '$ultimo_backup' ";
	try{
	$lettura = $dbh->prepare($leggi);
	$record  = $lettura->execute(); 

	} catch( \Throwable $th ){
	    $ret = "\n".'Spiacenti, in '. __FUNCTION__ 
	    . ' si è verificato un errore '
	    . "\n" . $th->getMessage() 
	    . "\n".'istruzione: '. $leggi;
	    return $ret;
	}
	// loop 
	$ret .= "\n\n".'--'
	. "\n". '-- Dump dei dati per la tabella `album_dettagli`'
	. "\n". '--'."\n";
	while ($record = $lettura->fetch(PDO::FETCH_ASSOC)) {
		$rigo = str_ireplace('§1', $record['record_id'], $riempire);
		$rigo = str_ireplace('§2', $record['record_id_padre'], $rigo);
		$rigo = str_ireplace('§3', $record['chiave'], $rigo);
		$rigo = str_ireplace('§4', $record['valore'], $rigo);
		$rigo = str_ireplace('§5', $record['consultatore_id'], $rigo);
		$rigo = str_ireplace('§6', $record['ultima_modifica_record'], $rigo);
		$rigo = str_ireplace('§7', $record['record_cancellabile_dal'], $rigo);
		$ret .= "\n".$rigo;
	}
    return $ret;
	
} // get_backup_album_dettagli


function get_backup_appunti_sql(string $ultimo_backup) : string{
	$dbh = New DatabaseHandler(); 
	$ret = '';
	$riempire = "INSERT INTO `appunti_sql_elenco` (`record_id`, `sinossi`, "
	. "`appunto_sql`, "
	. "`ultima_modifica_record`) "
	. "VALUES(§1, '§2', '§3', '§4');";
	
	// il parametro di input viene ignorato 
	$leggi = 'SELECT * FROM appunti_sql_elenco ' 
	. " WHERE ultima_modifica_record >= '0001-01-01 01:01:01' ";
	try{
		$lettura = $dbh->prepare($leggi);
		$record  = $lettura->execute(); 

	} catch( \Throwable $th ){
		$ret = "\n".'Spiacenti, in '. __FUNCTION__ 
		. ' si è verificato un errore '
		. "\n" . $th->getMessage() 
		. "\n".'istruzione: '. $leggi;
		return $ret;
	}

	// loop 
	$ret .= "\n\n".'--'
	. "\n". '-- Dump dei dati per la tabella `appunti_sql_elenco`'
	. "\n". '--'."\n";
	while ($record = $lettura->fetch(PDO::FETCH_ASSOC)) {
		$rigo = str_ireplace('§1', $record['record_id'], $riempire);
		$sinossi = str_ireplace("\r\n", '\r\n', $record['sinossi']);
		$sinossi = str_ireplace("\r", '\r', $sinossi);
		$sinossi = str_ireplace("\n", '\n', $sinossi);
		$rigo = str_ireplace('§2', $sinossi, $rigo);
		$appunto_sql = $record['appunto_sql'];
		$appunto_sql = str_ireplace("\r\n", '\r\n', $appunto_sql);
		$appunto_sql = str_ireplace("\r", '\r', $appunto_sql);
		$appunto_sql = str_ireplace("\n", '\n', $appunto_sql);
		$rigo = str_ireplace('§3', $appunto_sql, $rigo);
		$rigo = str_ireplace('§4', $record['ultima_modifica_record'], $rigo);
		// $rigo = str_ireplace('§5', $record['record_cancellabile_dal'], $rigo);
		$ret .= "\n".$rigo;
	}
  return $ret;
} // get_backup_appunti_sql

function get_backup_autori(string $ultimo_backup) : string {
	$dbh = New DatabaseHandler(); 
	$ret = '';
	$riempire = "INSERT INTO `autori_elenco` (`record_id`, `cognome_nome`, "
	. "`detto`, `sigla_6`, `fisica_giuridica`, `url_autore`, "
	. "`ultima_modifica_record`) "
	. "VALUES(§1, '§2', '§3', '§4', '§5', '§6', '§7');";

	$leggi = 'SELECT * FROM autori_elenco ' 
	. " WHERE ultima_modifica_record >= '$ultimo_backup' ";
	try{
	$lettura = $dbh->prepare($leggi);
	$record  = $lettura->execute(); 

	} catch( \Throwable $th ){
	    $ret = "\n".'Spiacenti, in '. __FUNCTION__ 
	    . ' si è verificato un errore '
	    . "\n" . $th->getMessage() 
	    . "\n".'istruzione: '. $leggi;
	    return $ret;
	}
	// loop 
	$ret .= "\n\n".'--'
	. "\n". '-- Dump dei dati per la tabella `autori_elenco`'
	. "\n". '--'."\n";
	while ($record = $lettura->fetch(PDO::FETCH_ASSOC)) {
		$rigo = str_ireplace('§1', $record['record_id'], $riempire);
		$rigo = str_ireplace('§2', $record['cognome_nome'], $rigo);
		$rigo = str_ireplace('§3', $record['detto'], $rigo);
		$rigo = str_ireplace('§4', $record['sigla_6'], $rigo);
		$rigo = str_ireplace('§5', $record['fisica_giuridica'], $rigo);
		$rigo = str_ireplace('§6', $record['url_autore'], $rigo);
		$rigo = str_ireplace('§7', $record['ultima_modifica_record'], $rigo);
		// $rigo = str_ireplace('§6', $record['record_cancellabile_dal'], $rigo);
		$ret .= "\n".$rigo;
	}
    return $ret;
	
} // get_backup_autori


function get_backup_chiavi(string $ultimo_backup) : string {
	$dbh = New DatabaseHandler(); 
	$ret = '';
	$riempire = "INSERT INTO `chiavi_elenco` (`record_id`, `chiave`, "
	. "`url_manuale`, `unico`, `ultima_modifica_record`, `record_cancellabile_dal`) "
	. "VALUES(§1, '§2', '§3', '§4', '§5', '§6');";

	$leggi = 'SELECT * FROM chiavi_elenco ' 
	. " WHERE ultima_modifica_record >= '$ultimo_backup' ";
	try{
	$lettura = $dbh->prepare($leggi);
	$record  = $lettura->execute(); 

	} catch( \Throwable $th ){
	    $ret = "\n".'Spiacenti, in '. __FUNCTION__ 
	    . ' si è verificato un errore '
	    . "\n" . $th->getMessage() 
	    . "\n".'istruzione: '. $leggi;
	    return $ret;
	}
	// loop 
	$ret .= "\n\n".'--'
	. "\n". '-- Dump dei dati per la tabella `chiavi_elenco`'
	. "\n". '--'."\n";
	while ($record = $lettura->fetch(PDO::FETCH_ASSOC)) {
		$rigo = str_ireplace('§1', $record['record_id'], $riempire);
		$rigo = str_ireplace('§2', $record['chiave'],                 $rigo);
		$rigo = str_ireplace('§3', $record['url_manuale'],            $rigo);
		$rigo = str_ireplace('§4', $record['unico'],                  $rigo);
		$rigo = str_ireplace('§5', $record['ultima_modifica_record'], $rigo);
		$rigo = str_ireplace('§6', $record['record_cancellabile_dal'],$rigo);
		$ret .= "\n".$rigo;
	}
    return $ret;
	
} // get_backup_chiavi


function get_backup_vocabolari(string $ultimo_backup) : string {
	$dbh = New DatabaseHandler(); 
	$ret = '';
	$riempire = "INSERT INTO `chiavi_valori_vocabolario` (`record_id`, "
	. "`chiave`, `valore`, `ultima_modifica_record`, `record_cancellabile_dal`) "
	. "VALUES(§1, '§2', '§3', '§4', '§5');";

	$leggi = 'SELECT * FROM chiavi_valori_vocabolario ' 
	. " WHERE ultima_modifica_record >= '$ultimo_backup' ";
	try{
	$lettura = $dbh->prepare($leggi);
	$record  = $lettura->execute(); 

	} catch( \Throwable $th ){
	    $ret = "\n".'Spiacenti, in '. __FUNCTION__ 
	    . ' si è verificato un errore '
	    . "\n" . $th->getMessage() 
	    . "\n".'istruzione: '. $leggi;
	    return $ret;
	}
	// loop 
	$ret .= "\n\n".'--'
	. "\n". '-- Dump dei dati per la tabella `chiavi_valori_vocabolario`'
	. "\n". '--'."\n";
	while ($record = $lettura->fetch(PDO::FETCH_ASSOC)) {
		$rigo = str_ireplace('§1', $record['record_id'], $riempire);
		$rigo = str_ireplace('§2', $record['chiave'], $rigo);
		$rigo = str_ireplace('§3', $record['valore'], $rigo);
		$rigo = str_ireplace('§4', $record['ultima_modifica_record'], $rigo);
		$rigo = str_ireplace('§5', $record['record_cancellabile_dal'], $rigo);
		$ret .= "\n".$rigo;
	}
    return $ret;
	
} // get_backup_vocabolari


function get_backup_consultatori(string $ultimo_backup) : string {
	$dbh = New DatabaseHandler(); 
	$ret = '';
	$riempire = "INSERT INTO `consultatori_calendario` (`record_id`, "
	. "`cognome_nome`, `email`, `password`, `abilitazione`, `attivita_dal`, "
	. "`attivita_fino_al`, `ultima_modifica_record`, `record_cancellabile_dal`) "
	. "VALUES(§1, '§2', '§3', '§4', '§5', '§6', '§7', '§8', '§9');";

	$leggi = 'SELECT * FROM consultatori_calendario ' 
	. " WHERE ultima_modifica_record >= '$ultimo_backup' ";
	try{
	$lettura = $dbh->prepare($leggi);
	$record  = $lettura->execute(); 

	} catch( \Throwable $th ){
	    $ret = "\n".'Spiacenti, in '. __FUNCTION__ 
	    . ' si è verificato un errore '
	    . "\n" . $th->getMessage() 
	    . "\n".'istruzione: '. $leggi;
	    return $ret;
	}
	// loop 
	$ret .= "\n\n".'--'
	. "\n". '-- Dump dei dati per la tabella `chiavi_valori_vocabolario`'
	. "\n". '--'."\n";
	while ($record = $lettura->fetch(PDO::FETCH_ASSOC)) {
		$rigo = str_ireplace('§1', $record['record_id'], $riempire);
		$rigo = str_ireplace('§2', $record['cognome_nome'], $rigo);
		$rigo = str_ireplace('§3', $record['email'], $rigo);
		$rigo = str_ireplace('§4', $record['password'], $rigo);
		$rigo = str_ireplace('§5', $record['abilitazione'], $rigo);
		$rigo = str_ireplace('§6', $record['attivita_dal'], $rigo);
		$rigo = str_ireplace('§7', $record['attivita_fino_al'], $rigo);
		$rigo = str_ireplace('§8', $record['ultima_modifica_record'], $rigo);
		$rigo = str_ireplace('§9', $record['record_cancellabile_dal'], $rigo);
		$ret .= "\n".$rigo;
	}
    return $ret;
	
} // get_backup_vocabolari


function get_backup_fotografie(string $ultimo_backup) : string {
	$dbh = New DatabaseHandler(); 
	$ret = '';
	$riempire = "INSERT INTO `fotografie` (`record_id`, `titolo_fotografia`, "
	. "`disco`, `percorso_completo`, `record_id_in_album`, "
	. "`record_id_in_scansioni_disco`, `stato_lavori`, `ultima_modifica_record`, "
	. "`record_cancellabile_dal`) "
	. "VALUES(§1, '§2', '§3', '§4', §5, §6, '§7', '§8', '§9');";

	$leggi = 'SELECT * FROM fotografie ' 
	. " WHERE ultima_modifica_record >= '$ultimo_backup' ";
	try{
	$lettura = $dbh->prepare($leggi);
	$record  = $lettura->execute(); 

	} catch( \Throwable $th ){
	    $ret = "\n".'Spiacenti, in '. __FUNCTION__ 
	    . ' si è verificato un errore '
	    . "\n" . $th->getMessage() 
	    . "\n".'istruzione: '. $leggi;
	    return $ret;
	}
	// loop 
	$ret .= "\n\n".'--'
	. "\n". '-- Dump dei dati per la tabella `fotografie`'
	. "\n". '--'."\n";
	while ($record = $lettura->fetch(PDO::FETCH_ASSOC)) {
		$rigo = str_ireplace('§1', $record['record_id'], $riempire);
		$rigo = str_ireplace('§2', $record['titolo_fotografia'], $rigo);
		$rigo = str_ireplace('§3', $record['disco'], $rigo);
		$rigo = str_ireplace('§4', $record['percorso_completo'], $rigo);
		$rigo = str_ireplace('§5', $record['record_id_in_album'], $rigo);
		$rigo = str_ireplace('§6', $record['record_id_in_scansioni_disco'], $rigo);
		$rigo = str_ireplace('§7', $record['stato_lavori'], $rigo);
		$rigo = str_ireplace('§8', $record['ultima_modifica_record'], $rigo);
		$rigo = str_ireplace('§9', $record['record_cancellabile_dal'], $rigo);
		$ret .= "\n".$rigo;
	}
    return $ret;
	
} // get_backup_fotografie


function get_backup_fotografie_dettagli(string $ultimo_backup) : string {
	$dbh = New DatabaseHandler(); 
	$ret = '';
	$riempire = "INSERT INTO `fotografie_dettagli` (`record_id`, `record_id_padre`, "
	. "`chiave`, `valore`, `consultatore_id`, `ultima_modifica_record`, `record_cancellabile_dal`) "
	. "VALUES(§1, §2, '§3', '§4', §5, '§6', '§7');";

	$leggi = 'SELECT * FROM fotografie_dettagli ' 
	. " WHERE ultima_modifica_record >= '$ultimo_backup' ";
	try{
	$lettura = $dbh->prepare($leggi);
	$record  = $lettura->execute(); 

	} catch( \Throwable $th ){
	    $ret = "\n".'Spiacenti, in '. __FUNCTION__ 
	    . ' si è verificato un errore '
	    . "\n" . $th->getMessage() 
	    . "\n".'istruzione: '. $leggi;
	    return $ret;
	}
	// loop 
	$ret .= "\n\n".'--'
	. "\n". '-- Dump dei dati per la tabella `fotografie_dettagli`'
	. "\n". '--'."\n";
	while ($record = $lettura->fetch(PDO::FETCH_ASSOC)) {
		$rigo = str_ireplace('§1', $record['record_id'], $riempire);
		$rigo = str_ireplace('§2', $record['record_id_padre'], $rigo);
		$rigo = str_ireplace('§3', $record['chiave'], $rigo);
		$rigo = str_ireplace('§4', $record['valore'], $rigo);
		$rigo = str_ireplace('§5', $record['consultatore_id'], $rigo);
		$rigo = str_ireplace('§6', $record['ultima_modifica_record'], $rigo);
		$rigo = str_ireplace('§7', $record['record_cancellabile_dal'], $rigo);
		$ret .= "\n".$rigo;
	}
    return $ret;
	
} // get_backup_fotografie_dettagli


function get_backup_richieste(string $ultimo_backup) : string {
	$dbh = New DatabaseHandler(); 
	$ret = '';
	$riempire = "INSERT INTO `richieste` (`record_id`, "
	. "`record_id_richiedente`, `oggetto_richiesta`, "
	. "`record_id_richiesta`, `richiesta_evasa_il`, "
	. "`record_id_amministratore`, `motivazione`, "
	. "`ultima_modifica_record`, `record_cancellabile_dal`) "
	. "VALUES(§1, §2, '§3', §4, '§5', §6, '§7', '§8', '§9');";

	$leggi = 'SELECT * FROM richieste ' 
	. " WHERE ultima_modifica_record >= '$ultimo_backup' ";
	try{
	$lettura = $dbh->prepare($leggi);
	$record  = $lettura->execute(); 

	} catch( \Throwable $th ){
	    $ret = "\n".'Spiacenti, in '. __FUNCTION__ 
	    . ' si è verificato un errore '
	    . "\n" . $th->getMessage() 
	    . "\n".'istruzione: '. $leggi;
	    return $ret;
	}
	// loop 
	$ret .= "\n\n".'--'
	. "\n". '-- Dump dei dati per la tabella `richieste`'
	. "\n". '--'."\n";
	while ($record = $lettura->fetch(PDO::FETCH_ASSOC)) {
		$rigo = str_ireplace('§1', $record['record_id'], $riempire);
		$rigo = str_ireplace('§2', $record['record_id_richiedente'], $rigo);
		$rigo = str_ireplace('§3', $record['oggetto_richiesta'], $rigo);
		$rigo = str_ireplace('§4', $record['record_id_richiesta'], $rigo);
		$rigo = str_ireplace('§5', $record['richiesta_evasa_il'], $rigo);
		$rigo = str_ireplace('§6', $record['record_id_amministratore'], $rigo);
		$rigo = str_ireplace('§7', $record['motivazione'], $rigo);
		$rigo = str_ireplace('§8', $record['ultima_modifica_record'], $rigo);
		$rigo = str_ireplace('§9', $record['record_cancellabile_dal'], $rigo);
		$ret .= "\n".$rigo;
	}
    return $ret;
	
} // get_backup_richieste


function get_backup_cartelle(string $ultimo_backup) : string {
	$dbh = New DatabaseHandler(); 
	$ret = '';
	$riempire = "INSERT INTO `scansioni_cartelle` (`record_id`, `disco`, "
	. "`percorso_completo`, `stato_lavori`, "
	. " `ultima_modifica_record`, `record_cancellabile_dal` ) "
	. "VALUES(§1, '§2', '§3', '§4', '§5', '§6' );";

	$leggi = 'SELECT * FROM scansioni_cartelle ' 
	. " WHERE ultima_modifica_record >= '$ultimo_backup' ";
	try{
	$lettura = $dbh->prepare($leggi);
	$record  = $lettura->execute(); 

	} catch( \Throwable $th ){
	    $ret = "\n".'Spiacenti, in '. __FUNCTION__ 
	    . ' si è verificato un errore '
	    . "\n" . $th->getMessage() 
	    . "\n".'istruzione: '. $leggi;
	    return $ret;
	}
	// loop 
	$ret .= "\n\n".'--'
	. "\n". '-- Dump dei dati per la tabella `scansioni_cartelle`'
	. "\n". '--'."\n";
	while ($record = $lettura->fetch(PDO::FETCH_ASSOC)) {
		$rigo = str_ireplace('§1', $record['record_id'], $riempire);
		$rigo = str_ireplace('§2', $record['disco'], $rigo);
		$rigo = str_ireplace('§3', $record['percorso_completo'], $rigo);
		$rigo = str_ireplace('§4', $record['stato_lavori'], $rigo);
		$rigo = str_ireplace('§5', $record['ultima_modifica_record'], $rigo);
		$rigo = str_ireplace('§6', $record['record_cancellabile_dal'], $rigo);
		$ret .= "\n".$rigo;
	}
    return $ret;
	
} // get_backup_scansioni_cartelle


function get_backup_deposito(string $ultimo_backup) : string {
	$dbh = New DatabaseHandler(); 
	$ret = '';
	$riempire = "INSERT INTO `scansioni_disco` (`record_id`, `disco`, "
	. "`livello1`, `livello2`, `livello3`, `livello4`, `livello5`, `livello6`, "
	. "`nome_file`, `estensione`, `modificato_il`, `codice_verifica`, "
	. "`tinta_rgb`, `stato_lavori`, `ultima_modifica_record`, "
	. "`record_da_esaminare`, `record_cancellabile_dal`) "
	. "VALUES(§01, '§02', '§03', '§04', '§05', '§06', '§07', '§08', '§09', "
	. "'§10', '§11', '§12', '§13', '§14', '§15', '§16', '§17');";

	$leggi = 'SELECT * FROM scansioni_disco ' 
	. " WHERE ultima_modifica_record >= '$ultimo_backup' ";
	try{
	$lettura = $dbh->prepare($leggi);
	$record  = $lettura->execute(); 

	} catch( \Throwable $th ){
	    $ret = "\n".'Spiacenti, in '. __FUNCTION__ 
	    . ' si è verificato un errore '
	    . "\n" . $th->getMessage() 
	    . "\n".'istruzione: '. $leggi;
	    return $ret;
	}
	// loop 
	$ret .= "\n\n".'--'
	. "\n". '-- Dump dei dati per la tabella `scansioni_disco`'
	. "\n". '--'."\n";
	while ($record = $lettura->fetch(PDO::FETCH_ASSOC)) {
		$rigo = str_ireplace('§01', $record['record_id'], $riempire);
		$rigo = str_ireplace('§02', $record['disco'], $rigo);
		$rigo = str_ireplace('§03', $record['livello1'], $rigo);
		$rigo = str_ireplace('§04', $record['livello2'], $rigo);
		$rigo = str_ireplace('§05', $record['livello3'], $rigo);
		$rigo = str_ireplace('§06', $record['livello4'], $rigo);
		$rigo = str_ireplace('§07', $record['livello5'], $rigo);
		$rigo = str_ireplace('§08', $record['livello6'], $rigo);
		$rigo = str_ireplace('§09', $record['nome_file'], $rigo);
		$rigo = str_ireplace('§10', $record['estensione'], $rigo);
		$rigo = str_ireplace('§11', $record['modificato_il'], $rigo);
		$rigo = str_ireplace('§12', $record['codice_verifica'], $rigo);
		$rigo = str_ireplace('§13', $record['tinta_rgb'], $rigo);
		$rigo = str_ireplace('§14', $record['stato_lavori'], $rigo);
		$rigo = str_ireplace('§15', $record['ultima_modifica_record'], $rigo);
		$rigo = str_ireplace('§16', $record['record_da_esaminare'], $rigo);
		$rigo = str_ireplace('§17', $record['record_cancellabile_dal'], $rigo);
		$ret .= "\n".$rigo;
	}
    return $ret;
	
} // get_backup_deposito


function get_backup_video(string $ultimo_backup) : string {
	$dbh = New DatabaseHandler(); 
	$ret = '';
	$riempire = "INSERT INTO `video` (`record_id`, `titolo_video`, "
	. "`disco`, `percorso_completo`, `record_id_in_album`, "
	. "`record_id_in_scansioni_disco`, `stato_lavori`, `ultima_modifica_record`, "
	. "`record_cancellabile_dal`) "
	. "VALUES(§1, '§2', '§3', '§4', §5, §6, '§7', '§8', '§9');";

	$leggi = 'SELECT * FROM video ' 
	. " WHERE ultima_modifica_record >= '$ultimo_backup' ";
	try{
	$lettura = $dbh->prepare($leggi);
	$record  = $lettura->execute(); 

	} catch( \Throwable $th ){
	    $ret = "\n".'Spiacenti, in '. __FUNCTION__ 
	    . ' si è verificato un errore '
	    . "\n" . $th->getMessage() 
	    . "\n".'istruzione: '. $leggi;
	    return $ret;
	}
	// loop 
	$ret .= "\n\n".'--'
	. "\n". '-- Dump dei dati per la tabella `video`'
	. "\n". '--'."\n";
	while ($record = $lettura->fetch(PDO::FETCH_ASSOC)) {
		$rigo = str_ireplace('§1', $record['record_id'], $riempire);
		$rigo = str_ireplace('§2', $record['titolo_video'], $rigo);
		$rigo = str_ireplace('§3', $record['disco'], $rigo);
		$rigo = str_ireplace('§4', $record['percorso_completo'], $rigo);
		$rigo = str_ireplace('§5', $record['record_id_in_album'], $rigo);
		$rigo = str_ireplace('§6', $record['record_id_in_scansioni_disco'], $rigo);
		$rigo = str_ireplace('§7', $record['stato_lavori'], $rigo);
		$rigo = str_ireplace('§8', $record['ultima_modifica_record'], $rigo);
		$rigo = str_ireplace('§9', $record['record_cancellabile_dal'], $rigo);
		$ret .= "\n".$rigo;
	}
    return $ret;
	
} // get_backup_video 


function get_backup_video_dettagli(string $ultimo_backup) : string {
	$dbh = New DatabaseHandler(); 
	$ret = '';
	$riempire = "INSERT INTO `video_dettagli` (`record_id`, `record_id_padre`, "
	. "`chiave`, `valore`, `consultatore_id`, `ultima_modifica_record`, `record_cancellabile_dal`) "
	. "VALUES(§1, §2, '§3', '§4', §5, '§6', '§7');";

	$leggi = 'SELECT * FROM video_dettagli ' 
	. " WHERE ultima_modifica_record >= '$ultimo_backup' ";
	try{
	$lettura = $dbh->prepare($leggi);
	$record  = $lettura->execute(); 

	} catch( \Throwable $th ){
	    $ret = "\n".'Spiacenti, in '. __FUNCTION__ 
	    . ' si è verificato un errore '
	    . "\n" . $th->getMessage() 
	    . "\n".'istruzione: '. $leggi;
	    return $ret;
	}
	// loop 
	$ret .= "\n\n".'--'
	. "\n". '-- Dump dei dati per la tabella `video_dettagli`'
	. "\n". '--'."\n";
	while ($record = $lettura->fetch(PDO::FETCH_ASSOC)) {
		$rigo = str_ireplace('§1', $record['record_id'], $riempire);
		$rigo = str_ireplace('§2', $record['record_id_padre'], $rigo);
		$rigo = str_ireplace('§3', $record['chiave'], $rigo);
		$rigo = str_ireplace('§4', $record['valore'], $rigo);
		$rigo = str_ireplace('§5', $record['consultatore_id'], $rigo);
		$rigo = str_ireplace('§6', $record['ultima_modifica_record'], $rigo);
		$rigo = str_ireplace('§7', $record['record_cancellabile_dal'], $rigo);
		$ret .= "\n".$rigo;
	}
    return $ret;
	
} // get_backup_video_dettagli


function get_file_backup(){ 
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
	$backup_time = $dbh->get_datetime_now();
	$backup_file = str_ireplace(' ', 'T', $backup_time);
	$backup_file = str_ireplace(':', '-', $backup_file);
	$backup_file = ABSPATH.'aa-backup/backup_'.$backup_file.'.sql';

	$ultimo_backup = getenv('ULTIMO_BACKUP');
	$ultimo_backup = str_ireplace("'", '', $ultimo_backup);

	file_put_contents($backup_file, get_backup_header($ultimo_backup));
	file_put_contents($backup_file, get_backup_abilitazioni($ultimo_backup), FILE_APPEND);
	file_put_contents($backup_file, get_backup_album($ultimo_backup), FILE_APPEND);
	file_put_contents($backup_file, get_backup_album_dettagli($ultimo_backup), FILE_APPEND);
	file_put_contents($backup_file, get_backup_appunti_sql($ultimo_backup), FILE_APPEND);
	file_put_contents($backup_file, get_backup_autori($ultimo_backup), FILE_APPEND);
	file_put_contents($backup_file, get_backup_chiavi($ultimo_backup), FILE_APPEND);
	file_put_contents($backup_file, get_backup_vocabolari($ultimo_backup), FILE_APPEND);
	file_put_contents($backup_file, get_backup_consultatori($ultimo_backup), FILE_APPEND);
	file_put_contents($backup_file, get_backup_fotografie($ultimo_backup), FILE_APPEND);
	file_put_contents($backup_file, get_backup_fotografie_dettagli($ultimo_backup), FILE_APPEND);
	file_put_contents($backup_file, get_backup_richieste($ultimo_backup), FILE_APPEND);
	file_put_contents($backup_file, get_backup_cartelle($ultimo_backup), FILE_APPEND);
	file_put_contents($backup_file, get_backup_deposito($ultimo_backup), FILE_APPEND);
	file_put_contents($backup_file, get_backup_video($ultimo_backup), FILE_APPEND);
	file_put_contents($backup_file, get_backup_video_dettagli($ultimo_backup), FILE_APPEND);
	$fine_elenco = "\n--\n-- Fine file backup \n--\n";
	file_put_contents($backup_file, $fine_elenco, FILE_APPEND);

	$file_name = str_ireplace(' ', 'T', $backup_time);
	$file_name = 'backup_archivio_athesis_'.str_ireplace(':', '-', $file_name).'.sql';

	header("Cache-Control: public");
	header("Content-Description: File Transfer");
	header("Content-Disposition: attachment; filename=$file_name");
	header("Content-Type: text/plain");
	header("Content-Transfer-Encoding: binary");
	readfile($backup_file);

	set_ultimo_backup($backup_time); 

	require_once(ABSPATH.'aa-view/backup-eseguito.php');
	exit(0); 
					
} // get_file_backup

/**
 * test 
 * https://archivio.athesis77.it/aa-controller/scrivi-backup-file.php
 * 
 */
