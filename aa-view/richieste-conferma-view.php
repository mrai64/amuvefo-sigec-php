<!doctype html>
<html lang="it">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CONFERMA Richiesta evasa | modifica </title>
    <meta name='robots' content='noindex, nofollow' />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  </head>
  <body>
    <div class="container pt-5">
    <?php
      include(ABSPATH.'aa-controller/mostra-messaggio-sessione.php');
    ?>
    <div class="row">
      <div class="col-md-12">
        <h4>Conferma richiesta
          <a href="<?=URLBASE; ?>richieste.php/elenco-amministratore/'" class="btn btn-secondary float-end">Torna all'elenco richieste in sospeso</a>
        </h4>
        <hr>
        <form action="<?=URLBASE; ?>richieste.php/conferma-richiesta/<?=$richiesta_id; ?>" method="POST">
          <p class="h4">Richiesta</p>
          <input type="hidden" name="record_id" value="<?=$richiesta_id; ?>">
          <p class="h4">Richiedente: <?=$richiedente; ?></p>
          <div class="mb-3">
            <p class="h5">oggetto della richiesta</p>
            <p class="h5"><?=$oggetto_richiesta; ?></p>
          </div>
          <div class="mb-3">
            <label for="motivazione" class="form-label">Confermiamo la <strong>concessione</strong> di quanto richiesto per: 
              </label>
            <textarea name="motivazione" maxlength="1500" rows="10" class="form-control w-100"></textarea>
          </div>
          <div class="mb-3">
            <button type="submit" name="conferma_richiesta" class="btn btn-success">Visto, si conferma</button>
          </div>
        </form>        
      </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>