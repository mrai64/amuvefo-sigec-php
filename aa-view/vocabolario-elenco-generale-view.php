<!doctype html>
<html lang="it">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Elenco generale | Vocabolario chiavi-valori </title>
    <meta name='robots' content='noindex, nofollow' />
    <!-- jquery --><script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
		<!-- bootstrap --><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
		<!-- icone bootstrap  --><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css" rel="stylesheet" >
  </head>
  <body>
    <div class="container">
    <?php
    include(ABSPATH.'aa-controller/mostra-messaggio-sessione.php');
    ?>
      <div class="row">
        <h2>Elenco vocabolario
          <a href="<?=URLBASE; ?>chiavi.php/elenco/" class="btn btn-secondary float-end ">Elenco chiavi</a>
        </h2>
        <p class="fs-6">Album, fotografie e video vengono archiviati e catalogati 
          con l'uso di coppie chiave-valore in cui sono accoppiati 
          il <i>"cos'è"</i> e il <i>"vale"</i>.
        </p>
      </div>
      <div class="row">
        <!-- elenco chiavi -->
        <?=$elenco_generale; ?>
      </div>
    </div>
    <!-- jquery --><script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <!-- bootstrap --><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" ></script>
  </body>
</html>