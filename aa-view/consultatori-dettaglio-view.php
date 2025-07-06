<!doctype html>
<html lang="it">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Agenda Calendario Consultatori | Dettaglio</title>
    <meta name='robots' content='noindex, nofollow' />
		<!-- jquery --><script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
		<!-- bootstrap --><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
		<!-- icone bootstrap  --><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css" rel="stylesheet" >
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
            <h4>Dettagli Consultatore e Calendario operatività
              <a href="<?=URLBASE; ?>consultatori.php/elenco/" class="btn btn-secondary float-end">Elenco Agende Consultatori</a>
            </h4>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <label for="cognome_nome"> Cognome, Nome</label>
              <p class="form-control"><?=$consultatore['cognome_nome']?></p>
            </div>
            <div class="mb-3">
              <label for="abilitazione"> Livello di abilitazione</label>
              <p class="form-control"><?=$consultatore['abilitazione']?></p>
            </div>
            <div class="mb-3">
              <label for="attivita_dal"> Data inizio attività aaaa-mm-gg</label>
              <p class="form-control"><?=$consultatore['attivita_dal']?></p>
            </div>
            <div class="mb-3">
              <label for="attivita_fino_al"> Data fine attività </label>
              <p class="form-control"><?=$consultatore['attivita_fino_al']?></p>
            </div>
            <div class="mb-3">
              <label for="email"> Indirizzo e-mail </label>
              <p class="form-control"><?=$consultatore['email']?></p>
              <small class="text-muted">Sarà usata per comunicazioni di servizio</small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  </body>
</html>