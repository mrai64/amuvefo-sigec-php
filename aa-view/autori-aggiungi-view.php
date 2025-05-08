<!doctype html>
<html lang="it">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Autori | Scheda di modifica </title>
    <meta name='robots' content='noindex, nofollow' />
    <!-- jquery --><script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
		<!-- bootstrap --><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
		<!-- icone bootstrap  --><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" >
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
            <h4>Aggiunta scheda Autore
              <a href="<?=URLBASE; ?>autori.php/elenco-autori/" class="btn btn-secondary float-end">Elenco Autori</a>
            </h4>
          </div>
          <div class="card-body">
            <form action="<?=URLBASE; ?>autori.php/aggiungi/" method="POST">
                <div class="mb-3">
                    <label for="cognome_nome"><strong>Cognome, Nome</strong></label>
                    <input type="text" name="cognome_nome" value="" class="form-control" placeholder="Cognome, Nome" required>
                </div>
                <div class="mb-3">
                    <label for="detto"> Detto</label>
                    <input type="text" name="detto" value="" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="sigla_6">Sigla Athesis 6</label>
                    <input type="text" name="sigla_6" value="" placeholder="ABCABC" maxlength="6" class="form-control">
                </div>
                <div class="mb-3">
                  <label for="fisica_giuridica"><strong>persona fisica o gruppo</strong></label>
                  <br style="clear:both;">
                  <!-- input type="text" name="fisica_giuridica" value="F" class="form-control" -->
                  <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="inlineCheckbox1" name="fisica_giuridica" value="F" checked>
                    <label class="form-check-label" for="inlineCheckbox1">Fisica</label>
                  </div>
                  <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="inlineCheckbox2" name="fisica_giuridica" value="G">
                    <label class="form-check-label" for="inlineCheckbox2">Gruppo o Ente</label>
                  </div>
                </div>
                <div class="mb-3">
                  <label for="url_autore"> URL bio autore</label>
                  <input type="text" name="url_autore" value="" class="form-control" placeholder="https://sito.local/url_biografia">
                </div>                  
                <div class="mb-3">
                  <button type="submit" name="aggiungi_autore" class="btn btn-primary">Aggiungi autore</button>
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