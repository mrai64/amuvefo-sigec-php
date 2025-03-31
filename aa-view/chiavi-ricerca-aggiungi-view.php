<!doctype html>
<html lang="it">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Chiavi di ricerca | Modulo di aggiunta</title>
    <meta name='robots' content='noindex, nofollow' />
		<!-- jquery --><script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
		<!-- bootstrap --><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
		<!-- icone bootstrap  --><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" >
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
            <h4>Aggiungere Chiavi di ricerca
              <a href="<?=URLBASE; ?>chiavi.php/elenco/" class="btn btn-secondary float-end">Elenco </a>
            </h4>
          </div>
          <div class="card-body">
            <form action="<?=URLBASE; ?>chiavi.php/aggiungi/" method="POST">
              <!-- record_id bigint no: assegnato in automatico -->
              <div class="mb-3">
                <label for="chiave" class="col-form-label"> <strong>Chiave di ricerca</strong></label>
                <input type="text" name="chiave" class="form-control" aria-describedby="chiaveHelDiv" required>
                <div id="chiaveHelpDiv" class="form-text">Il formato delle chiavi di ricerca è in <code>lettere minuscole</code>, partendo da un soggetto sostantivo singolare (data, luogo, nome ecc. e non date, luoghi, nomi), sostituendo eventuali spazi con il trattino <code>'-'</code>; <br />per le sotto-categorie separarle da una barra <code>'/'</code>. <br />Esempi: <code>nome/autore</code> ["Lasalandra, Mario", "Monti, Paolo", "Berengo Gardin, Gianni"], nome/scansionatore, nome/ente [Consorzio di Bonifica, Provincia di Padova, CSV Padova Rovigo], data/evento, data/stampa, luogo/riprese [Scuole medie, Festa dell'Unità, Patronato], luogo/comune [Stanghella, Montagnana, Cittadella, Treviso, Venezia], luogo/area-geografica [Polesine, Bassa Padovana, Provincia di Padova, Città metropolitana di Venezia]. <br /><strong>RICHIESTA</strong> Inserite una nuova chiave di ricerca solo se concordata con il comitato di gestione. Poche chiavi comporta una limitata ricercabilità, troppe chiavi creano una inutile confusione. </div>
              </div>
              <div class="mb-3">
                <label for="url_manuale" class="col-form-label"> <strong>Pagina del Manuale</strong></label>
                <input type="url" name="url_manuale" class="form-control" pattern="https://www.fotomuseoathesis.it/man/.*"   aria-describedby="urlManualeHelpDiv">
                <div id="urlManualeHelpDiv" class="form-text">Campo facoltativo ma se inserito fate copia incolla dall'indirizzo della pagina cresta per spiegare la chiave di ricerca</div>
              </div>
              <!-- record_creato_il datetime no: assegnato in automatico -->
              <!-- record_cancellabile_dal no: assegnato in automatico -->
              <!-- fine campi-->
              <div class="mb-3">
                <button type="submit" name="aggiungi_chiave_ricerca" class="btn btn-primary">Aggiungi chiave di ricerca</button>
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