<!doctype html>
<html lang="it">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Elenco Calendario Consultatori</title>
		<!-- jquery --><script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
		<!-- bootstrap --><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
		<!-- icone bootstrap  --><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" >
  </head>
  <body>
  <div class="container pt-5">
    <?php
    /** gestione messaggio via SESSION */
    include(ABSPATH.'aa-controller/mostra-messaggio-sessione.php');
    ?>
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h4>Elenco Agende Consultatori
              <a href="<?=URLBASE; ?>consultatori.php/aggiungi/" class="btn btn-secondary float-end">Aggiunta</a>
            </h4>
          </div>
          <div class="card-body">
            <!-- Calendario consultatori -->
          <table class="table table-bordered table-striped">
            <thead>
              <tr>
                <th> #</th>
                <th> Cognome, Nome</th>
                <th> Livello abilitazione</th>
                <th> A partire dal</th>
                <th> Scade il</th>
                <th> riservato</th>
                <th> Azione</th>
              </tr>
            </thead>
            <tbody>
              <?=$calendario_consultatori; ?>
            </tbody>
          </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  </body>
</html>