<!doctype html>
<html lang="it">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Elenco Autori | AMUVEFO</title>
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
            <h4>Elenco AUTORI</h4>
            <p>in ordine alfabetico, tutti.
              <a href="<?=URLBASE; ?>autori.php/aggiungi/0" class="btn btn-secondary float-end">Aggiungi</a>
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
                  <?=$elenco_autori; ?>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>