<!doctype html>
<html lang="it">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TITOLO ALBUM | modifica </title>
    <meta name='robots' content='noindex, nofollow' />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
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
            <h4>Modifica Titolo Album
              <a href="<?=$leggi_album; ?>" class="btn btn-secondary float-end">Torna all'Album</a>
            </h4>
          </div>
          <div class="card-body">
            <form action="<?=$aggiorna_titolo; ?>" method="POST">
              <input type="hidden" name="album_id"  value="<?=$album_id;  ?>">
              <div class="mb-3">
                    <label class="h3" for="titolo">Titolo dell'album</label>
                    <input type="text" name="titolo" value="<?=$titolo_originale; ?>" class="form-control" aria-describedby="valoreHelpInline" required>
                  <span id="valoreHelpInline" class="form-text">
                    Come compilare il campo? <br />
                    Il titolo dell'album, a differenza del nome della cartella che lo contiene, PUÒ 
                    utilizzare lettere accentate, dieresi, umlaut e anche emoji. Ma non esagerate.<br />
                    Può anche essere un titolo GIÀ usato in altri album, vuol dire solo 
                    che quando farete le ricerche li troverete entrambi.
                  </span>
                </div>
                <div class="mb-3">
                  <button type="submit" name="aggiorna_titolo" class="btn btn-primary">Aggiorna titolo</button>
                </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>