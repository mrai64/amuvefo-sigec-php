<?php
/**
 * @source /aa-view/dettaglio-view.php
 * @author Massimo Rainato <maxrainato@libero.it>
 * 
 * Modifica dettaglio e torna alla vista album 
 */
if (!defined('ABSPATH')){
  include_once("../_config.php");
}
include_once(ABSPATH.'aa-model-database-handler.php'); // $con usato più avanti 
?><!doctype html>
<html lang="it">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DETTAGLIO ALBUM | modifica </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  </head>
  <body>    
  <div class="container pt-5">
    <?php
    include(ABSPATH.'aa-controller/mostra-messaggio-sessione.php');
    ?>
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h4>Modifica Dettaglio Album
              <a href="<?=URLBASE; ?>album.php/leggi/<?= $_GET["album_id"]; ?>" class="btn btn-secondary float-end">Torna all'Album</a>
            </h4>
          </div>
          <div class="card-body">
            <?php
            if  (isset($_GET['id'])){
                $record_id = (int) mysqli_real_escape_string($con, $_GET['id']);
                $album_id  = (int) mysqli_real_escape_string($con, $_GET['album_id']);
                $leggi  = "SELECT * FROM album_dettaglio WHERE record_id = $record_id ";
                $record_letti = mysqli_query($con, $leggi);
                if (mysqli_num_rows($record_letti) > 0){
                    $dettaglio = mysqli_fetch_array($record_letti);
                ?>
                <form action="aa-controller/aggiorna-dettaglio-album.php" method="post">
                  <input type="hidden" name="record_id" value="<?= $record_id; ?>">
                  <input type="hidden" name="album_id"  value="<?= $album_id;  ?>">
                  <div class="mb-3">
                       <label for="valore"> <?=$dettaglio["chiave"] ?></label>
                        <input type="text" name="valore" value="<?=$dettaglio['valore']; ?>" class="form-control">
                    </div>
                    <div class="mb-3">
                        <button type="submit" name="aggiorna_dettaglio" class="btn btn-primary">Aggiorna dettaglio</button>
                    </div>
                </form>
                <?php
                } else {
                    echo "<h5>Nessun record trovato</h5>";
                } // record letti zero 

            } // get[id]
            ?>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  </body>
</html>