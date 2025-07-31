<!doctype html>
<html lang="it">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Ricerca semplice | AMUVEFO</title>
		<meta name='robots' content='noindex, nofollow' />
		<!-- jquery --><script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
		<!-- bootstrap --><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
		<!-- icone bootstrap  --><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css" rel="stylesheet" >
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
			<form action="#" method="POST" id="moduloRicerca">
				<input type="text" name="ricerca_id"     value="0" hidden aria-hidden="">
				<input type="text" name="esegui_ricerca" value="1" hidden aria-hidden="">
				<input type="text" name="semplice"       value="1" hidden aria-hidden="">
				<input class="form-control" type="text" name="valore" 
					placeholder="Cliccate qui e scrivere cosa cercate."
					value="" required>
				<div class="col-12 mt-2 text-center">
					<button type="submit" name="esegui_ricerca" 
					class="btn btn-primary mx-auto">Ricerca</button>
				</div>
			</form>
			<div class="row">
				<div class="col-12 text-center">
					<a aria-label="chiedi aiuto a massimo con una chat whatsapp."  target='_blank' 
					href="https://wa.me/393485117782?text=Mi%20serve%20una%20mano%20per%20fotomuseoathesis." >
					<span class="fs-6 me-4">Chiedi a Massimo </span><img src="https://www.fotomuseoathesis.it/aa-img/WhatsAppButtonWhiteSmall.svg" alt="Chiedi a massimo. Chat whatsapp">
				</a>
				</div>
			</div>

			<div class="row" >
				<div class="col-12 vh-50 overflow-auto " id="listaRisultatiAlbum">
					<p> Elenco album </p>	
				</div>
			</div>

			<div class="row" >
				<div class="col-12 vh-50 grid clearfix overflow-auto " id="listaRisultatiFotografie">
					<p> Griglia immagini </p>	
				</div>
			</div>

			<footer class="py-3">
				<p class="fw-light text-center text-body-secondary">&copy; 2024 Associazione Culturale Athesis APS - Boara Pisani PD</p>
				<p class="fw-light text-center text-body-secondary">
					Il materiale presente nell'archivio non è di pubblico dominio<br>e non è consentita la pubblicazione in altri luoghi<br>senza consenso scritto del comitato di gestione AMUVEFO.</p>
			</footer>
		</div>
		<script src="<?=URLBASE; ?>aa-view/ricerca-semplice.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
	</body>
</html>