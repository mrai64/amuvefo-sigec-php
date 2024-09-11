<?php
/** 
 * @source /91-autori_elenco.php
 * @author Massimo Rainato <maxrainato@libero.it>
 * 
 * Per consentire un censimento omogeneo degli autori in archivio 
 * si è creata la tabella autori_elenco, questa pagina php 
 * !TODO va ristrutturata spezzandola in 4:
 * /autori.php router 
 *   /autori.php/elenco/0  porta a questa pagina (0 è strumentale per distinguerlo dagli id)
 *   /autori.php/elenco/0?parte=cognome  porta a questa pagina con una selezione 'comincia per'
 *   /autori.php/ricerca-per-cognome-nome/cognome,+nome
 *   /autori.php/ricerca/autori_id
 * /aa-model/autori-oop.php 
 * /aa-controller/autori.php 
 * /aa-view/autori-lista.php 
 * 
 * Inoltre 
 * 1. L'elenco autori deve essere filtrabile 
 * 2. Deve essere disponibile un link che porta alla selezione 
 *    di album, fotografie e video associati all'autore 
 * 3. deve essere presente pure un "autore ignoto"
 * 4. e deve essere presente un "autori vari"
 * 5. deve essere gestita la paginazione avanti e indietro,
 *    il numero di elementi in lista deve essere un campo editabile
 *  
 */
if (!defined('ABSPATH')){
  include_once('./_config.php');
}
include(ABSPATH.'aa-controller/controllo-abilitazione.php');

$last_cognome_nome = (isset($_SESSION['ultimo_cognome_nome'])) ? $_SESSION['ultimo_cognome_nome'] : "";
$last_record_id    = (isset($_SESSION['ultimo_record_id']))    ? $_SESSION['ultimo_record_id']    : 0;
$tot_record        = (isset($_SESSION['tot_record']))          ? $_SESSION['tot_record']          : 12;

?><!doctype html>
<html lang="it">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Elenco Autori | AMUVEFO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  </head>
  <body>
  <div class="container pt-5">
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h4>Elenco AUTORI</h4>
            <p>in ordine alfabetico, ricaricando la pagina si va alla pagina successiva. All'ultima pagina ricaricando si riparte dall'inizio.
              <br>La lista si interrompe ogni <?= $tot_record; ?> record.
            </p>
          </div>
          <div class="card-body">
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
                            Scheda biografica
                        </th>
                        <th>
                            Codice Athesis6
                        </th>
                        <th>
                            Azione
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $leggi  = "SELECT * FROM autori_elenco";
                    if ($last_cognome_nome != ''){
                        $leggi .= " WHERE cognome_nome >= '$last_cognome_nome' ";
                        $leggi .= "   AND record_id > $last_record_id ";
                    }
                    $leggi .= " ORDER BY cognome_nome, record_id ";
                    $leggi .= " LIMIT " . $tot_record;
                    $record_letti = mysqli_query($con, $leggi);
                    if ( mysqli_num_rows($record_letti) < 1){
                        unset($_SESSION['ultimo_cognome_nome']);
                        unset($_SESSION['ultimo_record_id']);
                        ?>
                    <tr><td colspan="5"><h5>Nessun record trovato</h5></td></tr>
                    <?php
                    } else { 
                        foreach ($record_letti as $autore) {
                    ?>
                    <tr>
                    <td><?= $autore['record_id'];    ?></td>
                    <td><?php 
                        echo $autore['cognome_nome']; 
                        if ($autore['detto'] !== ""){
                          echo ' / <i> detto "'. $autore['detto'] .'" </i>';
                        }
                    ?></td>
                    <td>
                        <?php
                        if ($autore['url_autore'] === ""){
                            echo '--';
                        } else {
                            echo '<a target="_blank" href="'.$autore['url_autore'].'">scheda bio</a>';
                        }
                        ?>
                    </td>
                    <td><?= $autore['sigla_6']; ?></td>
                    <td>
                       <a href="<?=URLBASE; ?>91-autori-modifica-mod.php?id=<?= $autore['record_id']; ?>" class="btn btn-success btn-sm">Modifica</a>
                    </td>
                    </tr>
                    <?php
                            $_SESSION['ultimo_cognome_nome'] = $autore['cognome_nome'];
                            $_SESSION['ultimo_record_id']    = $autore['record_id'];
                        } // foreach
                    } // record letti >
                    ?>
                </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <footer class="py-3 ">
    <ul class="nav justify-content-center border-top pb-3 ">
      <li class="nav-item"><a href="<?=URLBASE; ?>amministrazione.php" class="nav-link px-2 text-body-secondary">Amministrazione</a></li>
      <li class="nav-item"><a href="<?=URLBASE; ?>man/" class="nav-link px-2 text-body-secondary" target="_blank">Manuale</a></li>
      <li class="nav-item"><a href="<?=URLBASE; ?>man/" class="nav-link px-2 text-body-secondary">D&R FAQ</a></li>
      <li class="nav-item"><a href="https://athesis77.it/" class="nav-link px-2 text-body-secondary">Associazione</a></li>
      <li class="nav-item"><a href="https://www.athesis77.it/associazione/presentazione/" class="nav-link px-2 text-body-secondary">Chi siamo</a></li>
    </ul>
    <p class="text-center text-body-secondary">&copy; 2024 Associazione Culturale Athesis APS - Boara Pisani PD</p>
  </footer>
  </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  </body>
</html>