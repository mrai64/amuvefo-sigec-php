<?php
/** 
 *	97-consultatori-dettaglio.php
 *
 */
session_start();
require('aa-model/database-handler.php');


?><!doctype html>
<html lang="it">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bootstrap demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  </head>
  <body>
    
  <div class="container pt-5">
    <?php
    include('./aa-controller/mostra-messaggio-sessione.php');
    ?>
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h4>Dettagli Consultatore e Calendario operatività
              <a href="/97-consultatori.php" class="btn btn-secondary float-end">Elenco operatori</a>
            </h4>
          </div>
          <div class="card-body">
			<?php
			if (isset($_GET['id']))
			{
				$record_id = mysqli_real_escape_string($con, $_GET['id']);
				$leggi = "SELECT * FROM consultatori_calendario WHERE record_id = $record_id";
				$record_letti = mysqli_query($con, $leggi);

				if (mysqli_num_rows($record_letti) > 0 )
				{
                  $consultatore = mysqli_fetch_array($record_letti);
			?>
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

<?php
				}
				else 
				{
					echo "<h5>Nessun record trovato</h5>";
				}
			}
			?>
          </div>
        </div>
      </div>
    </div>
  </div>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  </body>
</html>