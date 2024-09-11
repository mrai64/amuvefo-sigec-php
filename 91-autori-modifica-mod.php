<?php
/**
 *	91-autori-modifica-mod 
 *
 *	Modifica scheda sintetica autori, per un solo autore.
 *
 */
session_start();
require('aa-model/database-handler.php'); // $con usato più avanti 

?><!doctype html>
<html lang="it">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Autori | Scheda di modifica </title>
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
            <h4>Modifica scheda Autore
              <a href="<?=URLBASE; ?>91-autori-elenco.php" class="btn btn-secondary float-end">Elenco Autori</a>
            </h4>
          </div>
          <div class="card-body">
            <?php
            if  (isset($_GET['id'])){
                $record_id = mysqli_real_escape_string($con, $_GET['id']);
                $leggi  = "SELECT * FROM autori_elenco WHERE record_id = $record_id ";
                $record_letti = mysqli_query($con, $leggi);
                if (mysqli_num_rows($record_letti) > 0){
                    $autore = mysqli_fetch_array($record_letti);
                ?>
                <form action="<?=URLBASE; ?>91-autori-modifica.php" method="post">
                    <input type="hidden" name="record_id" value="<?= $record_id; ?>">
                    <div class="mb-3">
                       <label for="cognome_nome"> Cognome, Nome</label>
                        <input type="text" name="cognome_nome" value="<?=$autore['cognome_nome']; ?>" class="form-control">
                    </div>
                    <div class="mb-3">
                       <label for="detto"> Detto</label>
                        <input type="text" name="detto" value="<?=$autore['detto']; ?>" class="form-control">
                    </div>
                    <div class="mb-3">
                       <label for="sigla_6"> Sigla Athesis 6</label>
                        <input type="text" name="sigla_6" value="<?=$autore['sigla_6']; ?>" class="form-control">
                    </div>
                    <div class="mb-3">
                       <label for="fisica_giuridica"> F/G</label>
                        <input type="text" name="fisica_giuridica" value="<?=$autore['fisica_giuridica']; ?>" class="form-control">
                    </div>
                    <div class="mb-3">
                       <label for="url_autore"> URL bio autore</label>
                        <input type="text" name="url_autore" value="<?=$autore['url_autore']; ?>" class="form-control">
                    </div>
                    <div class="mb-3">
                        <button type="submit" name="aggiorna_autore" class="btn btn-primary">Aggiorna autore</button>
                    </div>
                </form>
                <?php
                } else {
                    echo "<h5>Nessun record trovato</h5>";
                } // record letti zero 

            } // get[id]
            // $_SESSION['ultimo_record_id'] = 0; // se resta cognome, nome l'elenco riparte da quello
            ?>
          </div>
        </div>
      </div>
    </div>
  </div>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  </body>
</html>