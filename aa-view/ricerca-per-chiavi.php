<!doctype html>
<html lang="it">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Ricerca avanzata | AMUVEFO</title>
		<meta name='robots' content='noindex, nofollow' />
		<!-- jquery --><script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
		<!-- bootstrap --><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
		<!-- icone bootstrap  --><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" >
		</head>
	<body>
		<!-- TEST	https://archivio.athesis77.it/aa-view/ricerca-per-chiavi.php 	-->
		<div class="container pt-5">
			<div class="row">
				<div class="col-12">
					<h4>Ricerca avanzata</h4>
				</div>
			</div>
			<form action="#" method="POST" id="moduloRicerca">
				<div class="row mt-3">
					<div class="col-3">
						<a href="#" class="btn btn-secondary float-start" id="addRicerche"><i class="bi bi-plus-square-fill"></i> aggiungi una selezione</a>
						<datalist id="elencoChiaviRicerca"></datalist>
						<datalist id="operatori">
							<option>comincia con</option>
							<option>contiene</option>
							<option>maggiore</option>
							<option>maggiore uguale</option>
							<option>uguale a</option>
							<option>minore uguale</option>
							<option>minore</option>
						</datalist>
					</div>
					<div class="col-9 mb-3">
						<div class="form-check">
							<label class="form-check-label" for="scelta_tutte"><i class="bi bi-check-all"></i>Tutte le selezioni seguenti</label>
							<input class="form-check-input" type="radio" name="scelta_tutte_o_almeno_una" value="tutte" id="scelta_tutte" checked required>
						</div>
						<div class="form-check">
							<label class="form-check-label" for="scelta_almeno_una"><i class="bi bi-check"></i>Almeno una delle seguenti</label>
							<input class="form-check-input" type="radio" name="scelta_tutte_o_almeno_una" value="almeno_una" id="scelta_almeno_una" >
						</div>
					</div>
				</div>
				<input type="text" name="esegui_ricerca" value="1" hidden aria-hidden="">
				<div class="row" id="elencoRicerche">
					<hr>
					<div class="col-3 mb-3">
						<label class="form-label" for="chiave[]"><em>Chiave di ricerca</em>
							<input class="form-control" type="text" name="chiave[]" 
							value="nome/manifestazione-soggetto" 
							list="elencoChiaviRicerca" aria-label="Selezione chiave di ricerca" required>
						</label>
					</div>
					<div class="col-2 mb-3">
						<label class="form-label" for="operatore[]"><em>operatore</em>
							<input class="form-control " type="text" name="operatore[]" 
							value="contiene"
							list="operatori" required>
						</label>
					</div>
					<div class="col-7 mb-3">
						<label class="form-label w-100" for="valore[]"><em>Valore / nome /aggettivo </em>
							<input class="form-control" type="text" name="valore[]" 
							value="?" required>
						</label>
					</div>
				</div>
				<div class="row mb-3">
					<hr>
					<button type="submit" name="esegui_ricerca_" class="btn btn-primary text-center">Registra ed esegui ricerca</button>
					<p class="h6">Le ricerche possono essere memorizzate e ripetute a distanza di tempo.</p>
				</div>
			</form>
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
				<ul class="nav justify-content-center border-top pb-3 ">
					<li class="nav-item"><a href="<?=URLBASE; ?>museo.php" class="nav-link px-2 text-body-secondary">Restart consultazione</a></li>
					<li class="nav-item"><a href="<?=URLBASE; ?>amministrazione.php" class="nav-link px-2 text-body-secondary">Amministrazione</a></li>
					<li class="nav-item"><a href="<?=URLBASE; ?>man/" class="nav-link px-2 text-body-secondary" target="_blank">Manuale</a></li>
					<li class="nav-item"><a href="<?=URLBASE; ?>man/" class="nav-link px-2 text-body-secondary">D&R FAQ</a></li>
					<li class="nav-item"><a href="https://athesis77.it/" class="nav-link px-2 text-body-secondary">Associazione</a></li>
					<li class="nav-item"><a href="https://www.athesis77.it/associazione/presentazione/" class="nav-link px-2 text-body-secondary">Chi siamo</a></li>
				</ul>
				<p class="text-center text-body-secondary">&copy; 2024 Associazione Culturale Athesis APS - Boara Pisani PD</p>
			</footer>
		</div>
		<script src="<?=URLBASE; ?>aa-view/ricerca-per-chiavi.js"></script>
		<script>
		</script>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
	</body>
</html>