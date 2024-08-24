<!DOCTYPE html>
<html lang="it">
<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Eseguito | Athesis Museo Veneto Fotografia</title>
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>
	<p class="h3">&Egrave; stata eseguita la cancellazione fisica dei record, 
		fino alla data dell'ultimo backup:  <?=$ultimo_backup; ?>. </p>
		<p class="text-alert">
			Se la cancellazione non fosse stata intenzionale occorre recuperare 
			i record cancellati dal backup ed eseguire un nuovo backup rima del ripristino dei dati.
	</p>
	<p class="text-center">
		La consultazione è consigliata a schermi di risoluzione FullHD<br>
		L'accesso è soggetto a <a href="<?=URLBASE; ?>man/termini-di-servizio-e-condizioni-duso/" >Termini e Condizioni</a>
	</p>  
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>