<!DOCTYPE html>
<html lang="it">
<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>&#x1F6A8;&#x1F6A8;&#x1F6A8; CANCELLAZIONE FISICA DATI&#x1F6A8;&#x1F6A8;&#x1F6A8; | Athesis Museo Veneto Fotografia</title>
		<meta name='robots' content='noindex, nofollow' />
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>
	<div class="container">
		<div class="row">
			<p class="h4">&#x1F6A8;&#x1F6A8;&#x1F6A8; &Egrave; stata eseguita &#x1F6A8;&#x1F6A8;&#x1F6A8; la cancellazione fisica &#x1F6A8;&#x1F6A8;&#x1F6A8; dei record,<br> 
				fino alla data dell'ultimo backup:  <?=$ultimo_backup; ?>. </p>
				<p class="text-danger">
					Se la cancellazione <strong>non fosse stata intenzionale</strong>, occorre:<br> 
					1) bloccare ogni attività, <br>
					2) recuperare i record cancellati dal backup ed <br>
					3) eseguire un nuovo backup 
					4) prima del ripristino dei dati.
				</p>
				<p><?=$attivita_eseguita; ?></p>
				<p class="text-center">
					La consultazione è consigliata a schermi di risoluzione FullHD<br>
					L'accesso è soggetto a <a href="<?=URLBASE; ?>man/termini-di-servizio-e-condizioni-duso/" >Termini e Condizioni</a>
				</p>  
			</div>
		</div>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>		
</body>
</html>