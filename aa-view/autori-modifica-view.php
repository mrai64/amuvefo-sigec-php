<!doctype html>
<html lang="it">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Autori | Scheda di modifica </title>
    <meta name='robots' content='noindex, nofollow' />
    <!-- jquery --><script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
		<!-- bootstrap --><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
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
            <h4>Modifica scheda Autore
              <a href="<?=URLBASE; ?>autori.php/elenco-autori/" class="btn btn-secondary float-end">Elenco Autori</a>
            </h4>
            <p class="fs-6"><i>I campi Cognome, nome - Sigla Athesis - Persona fisica / gruppo sono obbligatori.</i></p>
          </div>
          <div class="card-body">
            <form action="<?=URLBASE; ?>autori.php/modifica/<?=$autore['record_id']; ?>" method="POST">
                <input type="hidden" name="record_id" value="<?=$autore['record_id']; ?>" id="record_id">
                <div class="mb-3">
                    <label for="cognome_nome"><strong>Cognome, Nome</strong> </label>
                    <input type="text" name="cognome_nome" value="<?=$autore['cognome_nome']; ?>" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="detto"> Detto</label>
                    <input type="text" name="detto" value="<?=$autore['detto']; ?>" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="sigla_6"><strong>Sigla Athesis 6</strong>
                      | <a href="<?=URLBASE; ?>man/2-chiavi-di-ricerca/2-1-autori/2-1-1-codice-autore-athesis/" target="_blank" rel="noopener noreferrer"><i class="bi bi-info-square"></i></a>
                    </label>
                    <input type="text" name="sigla_6" value="<?=$autore['sigla_6']; ?>" placeholder="ABCABC" maxlength="6" id="sigla_autore" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="fisica_giuridica"><strong>Persona fisica o gruppo</strong></label>
                    <input type="text" name="fisica_giuridica" value="<?=$autore['fisica_giuridica']; ?>" maxlength="1" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="url_autore"> URL bio autore</label>
                    <input type="text" name="url_autore" value="<?=$autore['url_autore']; ?>" class="form-control">
              </div>
                <div class="mb-3">
                    <button type="submit" name="aggiorna_autore" class="btn btn-primary">Aggiorna autore</button>
                </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  <script>
    function urlbase(){
      var protocol = window.location.protocol;
      var domain   = window.location.hostname;
      var urlzero  = (domain.includes("localhost")) ? ":8888/AMUVEFO-sigec-php/" : "/";
      var urlbase  = protocol + '//' + domain + urlzero;
      return urlbase;
    }
    // jQuery 
    $( function(){
      // verifica server-side del campo sigla_6 quando si esce dal focus 
      $("#sigla_autore").on('focusout', function(){
        $("#sigla_autore").removeClass("is-valid").removeClass("is-invalid");
        $.post(
          urlbase()+'autori.php/verifica',
          { sigla_6 : $("#sigla_autore").val(), 
            record_id: $("#record_id").val()
          }
        )
        .done(function(risultato){
          if (risultato == 'present') {
            $("#sigla_autore").addClass('is-invalid');
          } else {
            $("#sigla_autore").addClass('is-valid');
          }
        })
        .fail(function(response){
          console.log('Verifica sigla ko, response', response);
          $("#sigla_autore").empty().addClass('is-invalid');
    		});
      }); // sigla_autore on 

    }); // jQuery document ready
  </script>
  </body>
</html>