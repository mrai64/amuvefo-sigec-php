<?php
/**
 * 01-scansioni-tinta-tinta-mod
 * 
 * per il record di scansioni_tinta che viene passato 
 * in parametro si chiede "solo" il cambio di colore.
 * Poi viene aggiornato il dato e si torna 
 * alla visualizzazione della cartella aggiornata
 * 
 */
if (!defined('ABSPATH')){
  include_once("./_config.php");
}
//session_start();
include ABSPATH.'aa-model/database-handler.php'; // $con 

$record_id = (isset($_GET["id"])) ? mysqli_real_escape_string($con, $_GET["id"]) : 0;
// ! TODO /ark/ è stato sostituito dai router + archivio.athesis77.it vale solo online
$back_to_page = (isset($_GET["back"])) ? URLBASE."ark/".$_GET["back"] : "#";
$back_to_page = str_replace('%20', '+', $back_to_page);
$back_to_page = str_replace(' ', '+', $back_to_page);
$tabella = (isset($_GET["t"])) ? ($_GET["t"]) : "";
if ($record_id == 0 || $back_to_page == "#" || $tabella == ""){
  http_response_code(500);
  exit("Richiamo della funzione invalido.");
}

?><!doctype html>
<html lang="it">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cambio colore</title>
    <!-- bootstrap --><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" >
    <!-- icone bootstrap  --><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" >
    </head>
  <body>
    <div class="container">
    <?php
    include(ABSPATH.'aa-controller/mostra-messaggio-sessione.php');
    ?>
      <div class="row">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header">
              <h4>Modifica colore</h4>
            </div>
            <div class="card-body">
              <form action="<?=URLBASE; ?>01-scansioni-disco-tinta.php" method="POST">
                <div class="mb-3">
                <input type="hidden" name="record_id" value="<?=$record_id; ?>"> 
                <input type="hidden" name="tabella" value="<?=$tabella; ?>"> 
                <input type="hidden" name="back" value="<?=$back_to_page; ?>"> 
                <label for="tinta" class="form-label">Cambia tinta</label>
                  <input
                    type="color"
                    name="tinta"
                    id="tinta"
                    class="form-control form-control-color"
                    aria-describedby="helpTinta"
                    value="#<?= "000099"; ?>"
                  />
                  <small id="helpTinta" class="form-text text-muted" style="color: #<?= "000099"; ?>" >Selezionare un colore</small>
                </div>
                <div class="mb-3">
                  <button type="submit" name="aggiorna_scansioni_tinta" class="btn btn-primary text-center">Aggiorna colore</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- jquery --><script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <!-- bootstrap --><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" ></script>
    <script>
      $( function() {

      });
    </script>  
  </body>
</html>