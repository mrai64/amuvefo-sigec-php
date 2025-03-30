<!doctype html>
<html lang="it">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Aggiunta Cartella | AMUVEFO </title>
		<meta name='robots' content='noindex, nofollow' />
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
	</head>
	<body>
		<div class="container">
		<?php
		include('./aa-controller/mostra-messaggio-sessione.php');
		?>
			<div class="row">
				<div class="col-md-12">
					<div class="card">
						<div class="card-header">
							<h4>Aggiunta cartella nuova/modificata</h4>
							<p>Questo modulo serve a inserire o aggiornare i dati 
								di una sola cartella, qualora si riscontrino 
								delle sotto-cartelle saranno accodate a questo stesso elenco.</p>
							<p>La cartella si intende già caricata in archivio.athesis77.it,
								e questo è il primo step, poi alcuni automatismi scansionano 
								la cartella compilando un elenco di carelle e sottocartelle 
								nel cosiddetto disco di archiviazione. A seguire altri automatismi 
								si occuperanno di caricare album, fotografie, video e i loro 
								dettagli (almeno quelli rilevabili in automatico).
							</p>
						</div>
						<div class="card-body">
							<form action="<?=URLBASE; ?>cartelle.php/aggiungi-cartella/0" method="POST">
								<!-- era /95-archiviazione-cartella-aggiungi.php -->
								<div class="mb-3">
									<label for="disco" class="form-label">Disco</label>
									<input
										type="text"
										class="form-control form-control-sm"
										name="disco"
										id="disco"
										aria-describedby="helpDisco"
										placeholder="DISKNAME123"
										required
									/>
									<small id="helpDisco" class="form-text text-muted">Inserire il nome disco 
										a lettere maiuscole, senza spazi - fino a 12 caratteri</small>
								</div>
								<div class="mb-3">
									<label for="cartella" class="form-label">Cartella da aggiungere</label>
									<input
										type="text"
										class="form-control"
										name="cartella"
										id="cartella"
										aria-describedby="helpCartella"
										placeholder="/cartella/sottocartella/"
										required
									/>
									<small id="helpCartella" class="form-text text-muted">Copiare e incollare 
										il percorso di accesso alla cartella o in alternativa l'indirizzo url 
										che comincia con https://www.fotomuseoathesis.it/ (compreso).</small>
								</div>
								<div class="mb-3">
									<button type="submit" name="aggiungi_cartella" class="btn btn-primary text-center">Aggiungi cartella in elenco sospesi</button>
								</div>
							</form>
						</div>
					</div>
					<div class="card">
						<div class="card-header">
							<h4>Elenco delle cartelle da lavorare</h4>
							<small>L'automatismo viene avviato ogni tot e segue un suo ordine, se volete anticiparlo cliccate sul pulsante 
								e poi rinfrescate la pagina.
							</small>
						</div>
						<div class="card-body" id="cartelleDaScansionare">
						<!-- riempito al caricamento della pagina da jQuery -->
						</div>
					</div>
				</div>
			</div>
		</div>
		<footer class="py-3" >
		<ul class="nav justify-content-center border-top pb-3 ">
			<li class="nav-item"><a href="<?=URLBASE; ?>amministrazione.php" class="nav-link px-2 text-body-secondary">Amministrazione</a></li>
			<li class="nav-item"><a href="<?=URLBASE; ?>ingresso.php" class="nav-link px-2 text-body-secondary">Ingresso</a></li>
			<li class="nav-item"><a href="<?=URLBASE; ?>man/" class="nav-link px-2 text-body-secondary" target="_blank">Manuale</a></li>
			<li class="nav-item"><a href="<?=URLBASE; ?>man/" class="nav-link px-2 text-body-secondary">D&R FAQ</a></li>
			<li class="nav-item"><a href="https://athesis77.it/" class="nav-link px-2 text-body-secondary">Associazione</a></li>
			<li class="nav-item"><a href="https://www.athesis77.it/associazione/presentazione/" class="nav-link px-2 text-body-secondary">Chi siamo</a></li>
		</ul>
		<p class="text-center text-body-secondary">&copy; 2024 Associazione Culturale Athesis APS - Boara Pisani PD</p>
	</footer>
		<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
		<!-- Carica -->
		<script src="<?=URLBASE; ?>aa-view/cartelle-da-scansionare.js"></script>
	</body>
</html>