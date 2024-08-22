<!doctype html>
<html lang="it">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>RESPINTA Richiesta evasa | modifica </title>
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
	</head>
	<body>
		<div class="container pt-5">
		<?php
			include(ABSPATH.'aa-controller/mostra-messaggio-sessione.php');
		?>
		<div class="row">
			<div class="col-md-12">
				<h4><span class="text-danger">RESPINTA richiesta</span>
					<a href="<?=URLBASE; ?>richieste.php/elenco-amministratore/" class="btn btn-secondary float-end">Torna all'elenco richieste in sospeso</a>
				</h4>
				<hr>
				<form action="<?=URLBASE; ?>richieste.php/rifiuta-richiesta/<?=$richiesta_id; ?>" method="POST">
					<p class="h4">Richiesta</p>
					<input type="hidden" name="record_id" value="<?=$richiesta_id; ?>">
					<p class="h4">Richiedente: <?=$richiedente; ?></p>
					<div class="mb-3">
						<p class="h5">oggetto della richiesta</p>
						<p class="h5"><?=$oggetto_richiesta; ?></p>
					</div>
					<div class="mb-3">
						<label for="motivazione" class="form-label">Spiacenti, rifiutiamo la concessione di quanto richiesto perché: 
							</label>
						<textarea name="motivazione" rows="10" class="form-control w-100"></textarea>
					</div>
					<div class="mb-3">
						<button type="submit" name="rifiuto_richiesta" class="btn btn-danger">Visto, si rifiuta</button>
					</div>
				</form>        
			</div>
		</div>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
	</body>
</html>