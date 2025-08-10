<?php
/**
 *	@source /aa-controller/contatori-controller.php 
 *	@author Massimo Rainato <maxrainato@libero.it>
 *
 */
if (!defined('ABSPATH')){
  include_once('../_config.php');
}
include_once(ABSPATH.'aa-model/database-handler-oop.php'); //    Class DatabaseHandler

/**
 * 
 * @return string html_code 
 */
function lettura_contatori() : string {
  $album_presenti = 'album_presenti';
  $fotografie_presenti = 'fotografie_presenti';
  $video_presenti = 'video_presenti';

  $dbh = New DatabaseHandler();

  $query = 'SELECT numero_album as numero'
  . ' FROM ' . $album_presenti;
  try {
    $lettura = $dbh->prepare($query);
    $lettura->execute();
    $righe = $lettura->fetchAll();
    $ret_album_presenti = number_format($righe[0]['numero'], 0, ',', "'");

  } catch (\Throwable $th) {
    //throw $th;
    $ret = 'debug only: In ' . __FILE__ . ' ' 
    . __FUNCTION__ . ' errore ' . $th->getMessage();
    return $ret;
  }

  $query = 'SELECT numero_fotografie as numero'
  . ' FROM ' . $fotografie_presenti;
  try {
    $lettura = $dbh->prepare($query);
    $lettura->execute();
    $righe = $lettura->fetchAll();
    $ret_fotografie_presenti = number_format($righe[0]['numero'], 0, ',', "'");

  } catch (\Throwable $th) {
    //throw $th;
    $ret = 'debug only: In ' . __FILE__ . ' ' 
    . __FUNCTION__ . ' errore ' . $th->getMessage();
    return $ret;
  }

  $query = 'SELECT numero_video as numero'
  . ' FROM ' . $video_presenti;
  try {
    $lettura = $dbh->prepare($query);
    $lettura->execute();
    $righe = $lettura->fetchAll();
    $ret_video_presenti = number_format($righe[0]['numero'], 0, ',', "'");

  } catch (\Throwable $th) {
    //throw $th;
    $ret = 'debug only: In ' . __FILE__ . ' ' 
    . __FUNCTION__ . ' errore ' . $th->getMessage();
    return $ret;
  }

  $ret = 'Alla data del '. date('d M Y') 
  . ' sono consultabili ' . $ret_album_presenti . ' album, '
  . $ret_fotografie_presenti . ' fotografie e '
  . $ret_video_presenti . ' video. ';

  return $ret;
}