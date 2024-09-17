<!doctype html>
<html lang="it">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Elenco Chiavi di ricerca</title>
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
						<h4>Elenco Chiavi di ricerca 
							<a href="<?=constant('URLBASE'); ?>man/2-chiavi-di-ricerca/" class="btn btn-info btn-sm">Manuale</a>
							<a href="<?=constant('URLBASE'); ?>chiavi.php/aggiungi/" class="btn btn-secondary float-end ">Aggiunta</a>
						</h4>
						<p>In ordine alfabetico, con link al manuale che ne descrive caratteristiche e vocabolari.</p>
					</div>
					<div class="card-body">				
					<table class="table table-bordered table-striped">
						<thead>
							<tr>
								<th style='text-align:right;'>
									#
								</th>
								<th>
									Chiave di ricerca
								</th>
								<th>
									Link alla pagina del manuale
								</th>
								<th>
									Azione
								</th>
							</tr>
						</thead>
						<tbody>
						<?=$elenco_chiavi; ?> 
						</tbody>
					</table>
					</div>
				</div>
			</div>
		</div>
	</div>
	<footer class="py-3 " >
		<ul class="nav justify-content-center border-top pb-3 ">
			<li class="nav-item"><a href="<?=constant('URLBASE'); ?>amministrazione.php" class="nav-link px-2 text-body-secondary">Amministrazione</a></li>
			<li class="nav-item"><a href="<?=constant('URLBASE'); ?>man/" class="nav-link px-2 text-body-secondary" target="_blank">Manuale</a></li>
			<li class="nav-item"><a href="<?=constant('URLBASE'); ?>man/" class="nav-link px-2 text-body-secondary">D&R FAQ</a></li>
			<li class="nav-item"><a href="https://athesis77.it/" class="nav-link px-2 text-body-secondary">Associazione</a></li>
			<li class="nav-item"><a href="https://www.athesis77.it/associazione/presentazione/" class="nav-link px-2 text-body-secondary">Chi siamo</a></li>
		</ul>
		<p class="text-center text-body-secondary">&copy; 2024 Associazione Culturale Athesis APS - Boara Pisani PD</p>
	</footer>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
	</body>
</html>