<!doctype html>
<html lang="it">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title><?= $dettaglio['chiave']; ?> | DETTAGLIO VIDEO | Modifica </title>
		<meta name='robots' content='noindex, nofollow' />
		<!-- --><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
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
						<h4>Modifica Dettaglio Video
							<a href="<?=$leggi_video; ?>" class="btn btn-secondary float-end">Torna alla vista video</a>
						</h4>
					</div>
					<div class="card-body">
						<form action="<?=$aggiorna_dettaglio; ?>" method="POST">
							<input type="hidden" name="record_id"  value="<?=$dettaglio_id; ?>">
							<input type="hidden" name="video_id"   value="<?=$video_id; ?>">
							<div class="mb-3">
								<label class="h3" for="valore"><?=$dettaglio["chiave"]; ?></label>
								<input type="text" name="valore" value="<?= $dettaglio['valore']; ?>" class="form-control" >
							</div>
							<div class="mb-3">
								<button type="submit" name="aggiorna_dettaglio" class="btn btn-primary">Aggiorna dettaglio</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
	</body>
</html>