<!doctype html>
<html lang="it">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vocabolario | Scheda di modifica </title>
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
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Vocabolario, coppia chiave-valore
              <a href="<?=URLBASE; ?>vocabolario.php/elenco-generale/" class="btn btn-secondary float-end">Vocabolario</a>
            </h4>
            <p class="text-bg-warning p-4">La modifica di questo record NON COMPORTA l'immediato allineamento di tutti i record 
              che contengono la coppia chiave-valore originaria e la loro 
              modifica nella nuova coppia. <br>
            Procedete tranquilli se sapete che finora questa coppia NON è stata usata, altrimenti
          va segnalata al super-amministratore dell'archivio che faccia l'aggiornamento per altra via.</p>
          </div>
          <div class="card-body">
            <form action="<?=URLBASE; ?>vocabolario.php/modifica/<?=$vocabolario_id; ?>" method="post">
              <div class="mb-3">
                <label for="chiave"> Chiave </label>
                <input type="text" name="chiave" class="form-control" value="<?=$chiave; ?>" disabled readonly aria-label="Valore non modificabile" >
              </div>
              <div class="mb-3">
                <label for="valore"> Valore</label>
                <input type="text" name="valore" class="form-control" value="<?=$valore; ?>" required>
              </div>
              <div class="mb-3">
                <button type="submit" name="modifica_vocabolario" class="btn btn-primary">Modifica nel vocabolario</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    <footer class="py-3 " style="z-index: -1;">
      <ul class="nav justify-content-center border-top pb-3 ">
        <li class="nav-item"><a href="<?=URLBASE; ?>ricerca.php" 
        class="nav-link px-2 text-body-secondary">Ricerca avanzata</a></li>
        <li class="nav-item"><a href="<?=URLBASE; ?>man/" 
        class="nav-link px-2 text-body-secondary" target="_blank">Manuale</a></li>
        <li class="nav-item"><a href="<?=URLBASE; ?>man/" 
        class="nav-link px-2 text-body-secondary">D&R FAQ</a></li>
        <li class="nav-item"><a href="https://athesis77.it/" 
        class="nav-link px-2 text-body-secondary">Associazione</a></li>
        <li class="nav-item"><a href="https://www.athesis77.it/associazione/presentazione/" 
        class="nav-link px-2 text-body-secondary">Chi siamo</a></li>
      </ul>
      <p class="text-center text-body-secondary">&copy; 2024 Associazione Culturale Athesis APS - Boara Pisani PD</p>
    </footer>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
  <script>

  </script>
  </body>
</html>