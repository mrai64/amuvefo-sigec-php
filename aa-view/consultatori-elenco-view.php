<!doctype html>
<html lang="it">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Elenco Calendario Consultatori</title>
    <meta name='robots' content='noindex, nofollow' />
		<!-- jquery --><script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
		<!-- bootstrap --><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
		<!-- icone bootstrap  --><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css" rel="stylesheet" >
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
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>