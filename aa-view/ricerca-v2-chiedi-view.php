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
		<!-- TEST	https://archivio.athesis77.it/ricerca-v2.php 	-->
		<!-- TEST	https://fotomuseoathesis.it/ricerca-v2.php 	-->
			<div class="container pt-5">
				<div class="row">
					<div class="col-12">
						<h4 class="text-center">FotoMuseoAthesis</h4>
					</div>
				</div>
				<form action="<?=URLBASE; ?>ricerche-v2.php/ricerca/" method="POST" id="moduloRicerca">
					<input type="text" name="ricerca_id"     value="0" hidden aria-hidden="">
					<input type="text" name="esegui_ricerca" value="1" hidden aria-hidden="">
					<input type="text" name="semplice"       value="1" hidden aria-hidden="">
					<input class="form-control" type="text" name="valore" 
						placeholder="Cliccate qui e scrivere cosa cercate."
						value="" required>
					<div class="col-12 mt-2 text-center">
						<button type="submit" name="esegui_ricerca" 
						class="btn btn-primary mx-auto">Nuova Ricerca</button>
					</div>
				</form>
				<div class="row">
					<div class="col-12 text-center">
						<a aria-label="Chiedi aiuto a Massimo, usa una chat whatsapp."  target='_blank' 
						href="https://wa.me/393485117782?text=Mi%20serve%20una%20mano%20per%20fotomuseoathesis." >
						<span class="fs-6 me-4">Chiedi a Massimo </span><img src="https://www.fotomuseoathesis.it/aa-img/WhatsAppButtonWhiteSmall.svg" alt="Chiedi a massimo. Chat whatsapp">
						</a>
					</div>
				</div>
			<footer class="py-3">
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
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
	</body>
</html>