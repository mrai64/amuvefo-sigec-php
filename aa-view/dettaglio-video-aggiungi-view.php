<!doctype html>
<html lang="it">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title> DETTAGLIO VIDEO | AGGIUNTA </title>
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
						<h4>aggiunta Dettaglio video
							<a href="<?=$leggi_video; ?>" class="btn btn-secondary float-end">Torna alla vista album</a>
						</h4>
					</div>
					<div class="card-body">
						<form action="<?=$aggiungi_dettaglio; ?>" method="POST">
							<input type="hidden" name="record_id"  value="<?= $record_id;     ?>">
							<input type="hidden" name="album_id"   value="<?= $album_id; ?>">
							<div class="mb-3">
								<label class="h3" for="chiave"> chiave di ricerca</label>
								<select name="chiave" class="form-select-lg" required>
									<?= $option_list_chiave; ?>
								</select>
							</div>
							<div class="mb-3">
								<label class="h3" for="valore"> valore </label>
								<input type="text" name="valore" value="" class="form-control" aria-describedby="valoreHelpInline" required>
								<span id="passwordHelpInline" class="form-text">
									Come compilare il campo? <br>
									(1) Dipende dalla 'chiave' scelta. Consultare il 
									<a href="https://archivio.athesis77.it/man/" target="_blank" rel="noopener noreferrer">manuale</a>.<br>
									(2) Avviso: se il dettaglio è condiviso con tutti i materiali dell'album, va assegnato all'album, non alla foto.<br>
								</span>
							</div>
							<div class="mb-3">
								<button type="submit" name="aggiungi_dettaglio" class="btn btn-primary">Aggiungi dettaglio</button>
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