<!doctype html>
<html lang="it">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Didascalie | Scheda di inserimento o aggiornamento </title>
    <meta name='robots' content='noindex, nofollow' />
    <!-- jquery --><script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
		<!-- bootstrap --><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" 
     rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
		<!-- icone bootstrap  --><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css" 
     rel="stylesheet" >
  </head>
  <body>
    <div class="container">
      <?php // $_SESSION['message']
      include(ABSPATH.'aa-controller/mostra-messaggio-sessione.php');
      ?>
      <div class="row">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header">
              <h4>Didascalia</h4>
              <p class="h4"><?=$tabella_padre?> | <?=$record_id_padre; ?></p>
              <p class="h4"><?=$titolo_elemento_padre; ?></p>
            </div>
            <div class="card-body">
              <form action="<?=URLBASE;?>didascalie.php/aggiorna/<?=$didascalia_id;?>" method="post">
                <input type="hidden" name="aggiorna_didascalia" value="1">
                <input type="hidden" name="record_id" value="<?=$didascalia_id?>">
                <input type="hidden" name="tabella_padre" value="<?=$tabella_padre;?>">
                <input type="hidden" name="record_id_padre" value="<?=$record_id_padre;?>">
                <textarea class="form-control" name="didascalia" 
                aria-label="didascalia da compilare solo testo"><?=$didascalia;?></textarea>
                <div class="mb-3">
                  <button type="submit" name="aggiorna_didascalia" 
                  class="btn btn-primary">Aggiorna didascalia</button>
                </div>
              </form>
              
              <p>Nota: La didascalia per il momento viene memorizzata
                di solo testo, non sono previsti 
                <i>corsivo</i>, 
                <strong>neretto</strong>, 
                <u>sottolineato</u>.</p>
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
    </div class="container">
    <!-- bootstrap+popper jQuery(sopra) --> 
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>