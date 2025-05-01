<!doctype html>
<html lang="it">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Ricerca semplice | AMUVEFO</title>
		<meta name='robots' content='noindex, nofollow' />
		<!-- jquery --><script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
		<!-- bootstrap --><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
		<!-- icone bootstrap  --><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" >
	</head>
	<body>
		<!-- TEST	https://archivio.athesis77.it/ricerca.php 	-->
		<!-- TEST	https://fotomuseoathesis.it/ricerca.php 	-->
		<div class="container pt-5">
			<div class="row">
				<div class="col-12">
					<h4 class="text-center">FotoMuseoAthesis</h4>
				</div>
			</div>
			<div class="row">
				<div class="col-11">
					<h4 class="">Ricerca n.<span id="ricerca_id"><?=$ricerca_id; ?></span> 
					effettuata con i termini</h4>
					<p class="" id="terminiRicerca"><?=$termini_ricerca; ?></p>
				</div>
				<div class="col-1">
				<a href="<?=URLBASE; ?>ricerche.php/ricerca" 
					title="Nuova ricerca"><i class="h2 bi bi-search"></i></a>
				</div>
			</div>
			<?php // debug only
			if (isset($debug_buffer)){
				echo "\n".'<div class="row">';
				echo "\n".'<div class="col-12">';
				for ($i=0; $i < count($debug_buffer); $i++) { 
					echo "\n".'<p>'.$debug_buffer[$i].'</p>';
				}
				echo "\n".'</div>';
				echo "\n".'</div>';
			} // debug only
			?>
			<div class="row" >
				<h4 class="">Album</h4>
				<div class="col-1"><?=$indietro_album; ?></div>
				<div class="col-10 vh-50 overflow-auto " id="listaRisultatiAlbum">
					<?=$html_album; ?>
				</div>
				<div class="col-1"><?=$avanti_album; ?></div>
			</div>
			<div class="row" >
				<h4 class="">Fotografie</h4>
				<div class="col-1"><?=$indietro_fotografie; ?></div>
				<div class="col-10 vh-50 grid clearfix overflow-auto " id="listaRisultatiFotografie">
					<?=$html_fotografie; ?>
				</div>
				<div class="col-1"><?=$avanti_fotografie; ?></div>
			</div>
			<div class="row" >
				<h4 class="">Video</h4>
				<div class="col-1"><?=$indietro_video; ?></div>
				<div class="col-10 vh-50 grid clearfix overflow-auto " id="listaRisultatiVideo">
					<?=$html_video; ?>	
				</div>
				<div class="col-1"><?=$avanti_video; ?></div>
			</div>
			<footer class="p-3">
				<p class="fw-light text-center text-body-secondary">&copy; 2024 Associazione Culturale Athesis APS - Boara Pisani PD</p>
				<p class="fw-light text-center text-body-secondary">
					Il materiale presente nell'archivio non è di pubblico dominio
					<br>e non è consentita la pubblicazione in altri luoghi
					<br>senza consenso scritto del Comitato di gestione 
					di Athesis Museo Veneto Fotografia.
					<br>Utilizzando la ricerca accettate esplicitamente i <a href="https://www.fotomuseoathesis.it/man/termini-di-servizio-e-condizioni-duso/" class=""> Termini d'Uso </a>.
				</p>
			</footer>
		</div>
		<script src="<?=URLBASE; ?>aa-view/ricerca.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
	</body>
</html>