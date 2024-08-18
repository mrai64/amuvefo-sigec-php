<?php 
/**
 * 01-scansioni-disco-tinta.php
 *
 * accetta 4 parametri in input tramite $_POST
 * - tinta text char(7) #000000 ... #ffffff
 * - tabella dove c'è il campo tinta_rgb da modificare 
 *   (per ora solo in scansioni_disco)
 * - record_id la chiave primaria del record da aggiornare 
 * 
 * utente deve essere abilitato a modifica 
 */
session_start();
include "aa-model/database-handler.php"; // crea una connessione per usare mysqli_real_escape_string()
$record_id    = (isset($_POST["record_id"])) ? mysqli_real_escape_string($con, $_POST["record_id"]) : 0;
$back_to_page = (isset($_POST["back"]))      ? mysqli_real_escape_string($con, $_POST["back"])      : "";
$tabella      = (isset($_POST["tabella"]))   ? mysqli_real_escape_string($con, $_POST["tabella"])   : "";
$tinta        = (isset($_POST["tinta"]))     ? mysqli_real_escape_string($con, $_POST["tinta"])     : "";
if ($tinta   == "" ||
    $tabella == "" ||
    $back_to_page == "" || 
    $record_id    == 0 ){
  http_response_code(500);
  exit("Richiamo della funzione invalido.");
}

$tinta = substr( str_replace("#", "", $tinta."000000"), 0, 6);
if (isset($_POST["aggiorna_scansioni_tinta"])){
  // aggiornamento dato 
  $aggiorna = "UPDATE scansioni_disco "
  . " SET tinta_rgb = '$tinta' WHERE "
  . " record_cancellabile_dal = '9999-12-31 23:59:59' "
  . " AND record_id = $record_id";
  $esegui_aggiorna = mysqli_query($con, $aggiorna);
  if ($esegui_aggiorna){
    $_SESSION['messaggio'] = 'Tinta aggiornata';
    header("Location: ".$back_to_page);
    exit(0);
  }

  // se passa qui è come un'else ma senza else
  $_SESSION['messaggio'] = 'Tinta non aggiornata';
  header("Location: ".$back_to_page);
  exit(0);
}
