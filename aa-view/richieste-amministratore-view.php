<!doctype html>
<html lang="it">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Richieste in sospeso | AMUVEFO</title>
		<meta name='robots' content='noindex, nofollow' />
		<!-- bootstrap --><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
		<!-- icone --><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css" rel="stylesheet" >
	</head>
	<body>
		<div class="container pt-5">
<!-- 
<div class="row">
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th class="col-11 h4" >Elenco richieste di [consultatore]</th>
				<th class="col-1">Rinuncia</th>
			</tr>
		</thead>
		<tbody>
			[elenco richieste di consultatore]
		</tbody>
	</table>
</div>
-->
			<?= $tabelle_richieste; ?>
		</div>
		<footer class="py-3">
			<ul class="nav justify-content-center border-top pb-3 ">
				<li class="nav-item"><a href="<?=URLBASE; ?>ingresso.php" class="nav-link px-2 text-body-secondary">Ingresso</a></li>
				<li class="nav-item"><a href="<?=URLBASE; ?>man/" class="nav-link px-2 text-body-secondary" target="_blank">Manuale</a></li>
				<li class="nav-item"><a href="<?=URLBASE; ?>man/" class="nav-link px-2 text-body-secondary">D&R FAQ</a></li>
				<li class="nav-item"><a href="https://athesis77.it/" class="nav-link px-2 text-body-secondary">Associazione</a></li>
				<li class="nav-item"><a href="https://www.athesis77.it/associazione/presentazione/" class="nav-link px-2 text-body-secondary">Chi siamo</a></li>
			</ul>
			<p class="text-center text-body-secondary">&copy; 2024 Associazione Culturale Athesis APS - Boara Pisani PD</p>
		</footer>
		<!-- bootstrap js --><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
	</body>
</html>