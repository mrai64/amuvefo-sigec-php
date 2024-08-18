<?php
/**
 * consultatori 
 * 
 * Pagina misto php+html che consente di gestire 
 * il calendario dei consultatori, ovvero user 
 * e-mail e password più data inizio consultazioni 
 * e data termine consultazioni
 * 
 * tabella: consultatori_calendario
 * @see https://archivio.athesis77.it/tech/3-archivi-tabelle/3-10-consultatori_calendario/ 
 * 
 *	TODO: convertire in oop (si complica, però)
 */
session_start();
require('aa-model/database-handler.php'); // valorizza $con 

?><!doctype html>
<html lang="it">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Elenco Consultatori</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  </head>
  <body>
    
  <div class="container pt-5">
    <?php
    /** gestine messaggio via SESSION */
    include('./aa-controller/mostra-messaggio-sessione.php');
    ?>
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h4>Elenco Consultatori
              <a href="/97-consultatori-aggiungi-mod.php" class="btn btn-secondary float-end">Aggiunta</a>
            </h4>
          </div>
          <div class="card-body">
            <!-- Calendario consultatori -->
          <table class="table table-bordered table-striped">
            <thead>
              <tr>
                <th>
                  #
                </th>
                <th>
                  Cognome, Nome
                </th>
                <th>
                  Livello abilitazione
                </th>
                <th>
                  A partire dal
                </th>
                <th>
                  Scade il
                </th>
                <th>
                  riservato
                </th>
                <th>
                  Azione
                </th>
              </tr>
            </thead>
            <tbody>
            <?php 
            // Soli record non cancellati 
            $leggi = "SELECT * FROM consultatori_calendario "
                   . "WHERE (record_cancellabile_dal = '" . RECORD_VIVO . "' ) "; 
            $record_letti = mysqli_query($con, $leggi);
            if (mysqli_num_rows($record_letti) < 1) { 
              ?>
              <tr><td colspan="7"><h5>Nessun record trovato</h5></td></tr>
              <?php 
            } else {
              foreach ($record_letti as $consultatore){
                ?>
              <tr>
              <td><?= $consultatore['record_id']; ?></td>
              <td><?= $consultatore['cognome_nome']; ?></td>
              <td><?= $consultatore['abilitazione']; ?></td>
              <td><?= $consultatore['attivita_dal']; ?></td>
              <td><?= $consultatore['attivita_fino_al']; ?></td>
              <td><?= $consultatore['record_creato_il']; ?></td>
              <td>
              <a href="97-consultatori-dettaglio.php?id=<?= $consultatore['record_id']; ?>"    class="btn btn-info btn-sm">Dettagli</a>
              <a href="97-consultatori-modifica-mod.php?id=<?= $consultatore['record_id']; ?>" class="btn btn-success btn-sm">Modifica</a>
              <a href="97-consultatori-cancella.php?id=<?= $consultatore['record_id']; ?>"     class="btn btn-danger btn-sm">Elimina</a>
              </td>
              </tr>
              <?php
              } // foreach
            } // else 
            ?>
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