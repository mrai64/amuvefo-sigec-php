<?php 
  $url_amministrazione_richieste = '#';
  $url_aggiorna_richieste = '#';
  $richiesta_id = 1;
  $richiedente= 'Rainato, Massimo';
  $oggetto_richiesta = 'Album. titolo album ___ che si trova in  ___';

?><!doctype html>
<html lang="it">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CONFERMA Richiesta evasa | modifica </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  </head>
  <body>
    <div class="container pt-5">
    <?php
      // include(ABSPATH.'aa-controller/mostra-messaggio-sessione.php');
    ?>
    <div class="row">
      <div class="col-md-12">
        <h4>Conferma richiesta
          <a href="<?= $url_amministrazione_richieste; ?>" class="btn btn-secondary float-end">Torna all'elenco richieste in sospeso</a>
        </h4>
        <hr>
        <form action="<?=$url_aggiorna_richiesta; ?>" method="POST">
          <p class="h4">Richiesta</p>
          <input type="hidden" name="record_id" value="<?=$richiesta_id; ?>">
          <div class="mb-3">
            <label for="richiedente" class="h4 form-label">Richiedente 
            </label>
            <input type="text" name="richiedente" value="<?=$richiedente; ?>" class="form-control" readonly>
          </div>
          <div class="mb-3">
            <label for="oggetto" class="h4 form-label">oggetto della richiesta 
            </label>
            <textarea name="oggetto" rows="5" class="form-control w-100" readonly><?=$oggetto_richiesta; ?></textarea>
          </div>
          <div class="mb-3">
            <label for="motivazione" class="form-label">Confermiamo la concessione di quanto richiesto per: 
              </label>
            <textarea name="motivazione" rows="10" class="form-control w-100"></textarea>
          </div>
          <div class="mb-3">
            <button type="submit" name="conferma_richiesta" class="btn btn-primary">Visto, si conferma</button>
          </div>
        </form>        
      </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  </body>
</html>