<!doctype html>
<html lang="it">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Agenda Accessi Consultatori | Modulo di aggiunta</title>
    <meta name='robots' content='noindex, nofollow' />
		<!-- jquery --><script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
		<!-- bootstrap --><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
		<!-- icone bootstrap  --><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" >
    </head>
  <body>    
  <div class="container pt-5">
    <?php
    include(ABSPATH .'aa-controller/mostra-messaggio-sessione.php');
    ?>
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h4>Aggiungere Agenda Consultatori
              <a href="<?=URLBASE; ?>consultatori.php/elenco/" class="btn btn-secondary float-end">Elenco Agende Consultatori</a>
            </h4>
          </div>
          <div class="card-body">
            <form action="<?=URLBASE; ?>consultatori.php/aggiungi/" method="POST">
              <!-- record_id                : assegnato in automatico -->
              <div class="mb-3">
                <label for="cognome_nome"> Cognome, Nome</label>
                <input type="text" name="cognome_nome" class="form-control" required>
              </div>
              <div class="mb-3">
                <label for="abilitazione"> Livello di abilitazione</label>
                <select class="form-select" name="abilitazione" required aria-label="Selezione livelli abilitazione">
                  <option >Una delle voci</option>
                  <option value="0 nessuna">Nessuna</option>
                  <option value="1 lettura">Lettura</option>
                  <option value="3 modifica">Modifica</option>
                  <option value="5 modifica originali">Originali</option>
                  <option value="7 amministrazione">Amministrazione</option>
                </select>
              </div>
              <div class="mb-3">
                <label for="attivita_dal"> Data inizio attività aaaa-mm-gg</label>
                <input type="date" name="attivita_dal" class="form-control" required>
              </div>
              <div class="mb-3">
                <label for="attivita_fino_al"> Data fine attività </label>
                <input type="date" dateformat="yyyy-mm-dd" name="attivita_fino_al" class="form-control" required>
              </div>
              <div class="mb-3">
                <label for="email"> indirizzo e-mail </label>
                <input type="email" name="email" class="form-control" required>
                <small class="text-muted">Controllare la casella di posta per comunicazioni di servizio</small>
              </div>
              <div class="mb-3">
                <label for="password1"> Password 1 di 2</label>
                <input type="password" name="password1" class="form-control" required>
              </div>
              <div class="mb-3">
                <label for="password2"> Password 2 di 2</label>
                <input type="password" name="password2" class="form-control" required>
                <small class="text-muted">Deve essere uguale a quella del campo precedente</small>
              </div>
              <!-- ultima_modifica_record   : assegnato in automatico -->
              <!-- record_cancellabile_dal  : assegnato in automatico -->
              <div class="mb-3">
                <button type="submit" name="aggiungi_agenda_consultatore" class="btn btn-primary">Aggiungi Agenda Consultatore</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  </body>
</html>