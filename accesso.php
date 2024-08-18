<?php
/**
 * @source /accesso.php 
 * @author Massimo Rainato <maxrainato@libero.it>
 * 
 * Richiama e presenta il modulo di accredito per visite
 * (nome Anonimo cognome Consultatore)
 */
if (!defined('ABSPATH')){
  include_once("./_config.php");
}
include_once(ABSPATH . 'aa-model/database-handler.php'); // $con non oop

// Valori predefiniti e sanificazione dati 
// !TODO rintracciare chi chiama /accesso.php e definire UNA e sempre UNA  
// p.es. return_to
$return_to = BASEURL . "museo.php"; // facciata museo con pulsantiera
if (isset($_POST['return_to'])){
  $return_to = mysqli_real_escape_string($con, $_POST['return_to']);
}
if (isset($_GET['r'])){
  $return_to = mysqli_real_escape_string($con, $_GET['r']);
}
if (isset($_GET['return_to'])){
  $return_to = mysqli_real_escape_string($con, $_GET['return_to']);
}
if (isset($_GET['return_to'])){
  $return_to = mysqli_real_escape_string($con, $_GET['return_to']);
}

$accesso_email = "";
if (isset($_COOKIE['accesso_email'])){
  $accesso_email = mysqli_real_escape_string($con, $_COOKIE['accesso_email']);
  $_SESSION['accesso_email'] = $accesso_email;
}
if ((!isset($accesso_email)) && isset($_SESSION['accesso_email'])){	
	$accesso_email = mysqli_real_escape_string($con, $_SESSION['accesso_email']);
}
if (!isset($_SESSION['messaggio'])){
  $_SESSION['messaggio'] = "<i>Chi siete? _ Dove andate? _ Un fiorino! (cit.)</i>";
}

// Si prende e si costruisce la pagina "VIEW"
require ABSPATH . "aa-view/accesso-view.php"; 
exit(0);