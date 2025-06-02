<!doctype html>
<html lang="it">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Amministrazione archivio | AMUVEFO</title>
		<meta name='robots' content='noindex, nofollow' />	
		<!-- bootstrap --><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" >
		<!-- icone --><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" >
  </head>
  <body>
	<div class="container pt-5">
		<div class="row">
			<h4>Amministrazione archivio</h4>
			<p>in ordine come viene</p>
		</div>
		<div class="row">
			<div class="col-4">
				<!-- /man/ -->
				<div class="list-group">
					<a href="<?=URLBASE; ?>man/" class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true">
						<div class="d-flex gap-2 w-100 justify-content-between">
							<div>
								<h4 class="mb-0">Manuale d'uso e manutenzione</h4>
							</div>
						</div>
					</a>
				<!-- /elenchi.php/chiavi-elenco/0 -->
					<a href="<?=URLBASE; ?>chiavi.php/elenco/" class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true">
						<div class="d-flex gap-2 w-100 justify-content-between">
							<div>
								<h6 class="mb-0">Chiavi di ricerca: elenco delle -</h6>
								<p class="mb-0 opacity-75">Vedi manuale </p>
							</div>
						</div>
					</a>
				<!-- /elenchi.php/autori-elenco/0  -->	
					<a href="<?=URLBASE; ?>autori.php/elenco-autori/" class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true">
						<div class="d-flex gap-2 w-100 justify-content-between">
							<div>
								<h6 class="mb-0">Autori. Elenco delle / degli -</h6>
							</div>
						</div>
					</a>
				<!-- /consultatori.php/elenco/ -->	
					<a href="<?=URLBASE; ?>consultatori.php/elenco/" class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true">
						<div class="d-flex gap-2 w-100 justify-content-between">
							<div>
								<h6 class="mb-0">Consultatori. Elenco Agenda Accessi -</h6>
								<p class="mb-0 opacity-75">Accrediti per consultazione o modifica e loro calendario</p>
							</div>
						</div>
					</a>
				<!-- /vocabolario.php/elenco-generale/ -->	
					<a href="<?=URLBASE; ?>vocabolario.php/elenco-generale/" class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true">
						<div class="d-flex gap-2 w-100 justify-content-between">
							<div>
								<h6 class="mb-0">Chiavi - Valori - Elenco alfabetico completo </h6>
								<p class="mb-0 opacity-75">Nota: per alcune chiavi di ricerca l'elenco non può essere esaustivo, ma riguarda le eccezioni alla regola ammesse; per esempio alle date aaaa-mm-gg.</p>
							</div>
						</div>
					</a>
				</div>
			</div>
			<div class="col-4">
				<div class="list-group">
					<!-- /cartelle.php/aggiungi-cartella/0 -->
					<a href="<?=URLBASE; ?>cartelle.php/aggiungi-cartella/0" 
						target="_blank" 
						class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true">
						<div class="d-flex gap-2 w-100 justify-content-between">
							<div>
								<h6 class="mb-0"><i class="h6 bi bi-1-square"></i> Cartelle. Aggiunta nuove o aggiorna -</h6>
								<p class="mb-0 opacity-75">Attività popolamento archivio
									<br />Dopo aver caricato le cartelle con il protocollo ftp 
									serve indicare al sistema qual'è la nuova cartella disponibile
									alle operazioni di scansione e archiviazione.
								</p>
							</div>
						</div>
					</a>
					<!-- /cartelle.php/archivia-cartella/0 -->
					<a href="<?=URLBASE; ?>cartelle.php/archivia-cartella/0" 
					  target="_blank" 
						class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true">
						<div class="d-flex gap-2 w-100 justify-content-between">
							<div>
								<h6 class="mb-0"><i class="h6 bi bi-2-square"></i> Cartelle. inizio dei lavori sulle cartelle aggiunte</h6>
								<p class="mb-0 opacity-75">Attività popolamento archivio 
									<br>Le cartelle inserite allo step precedente 
									vengono scansionate per identificare immagini e video, 
									e aggiungere il tutto nel deposito. </p>
							</div>
						</div>
					</a>
					<!-- /album.php/aggiungi-album/0 -->
					<a href="<?=URLBASE; ?>album.php/aggiungi-album/0" 
						target="_blank" 
						class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true">
						<div class="d-flex gap-2 w-100 justify-content-between">
							<div>
								<h6 class="mb-0"><i class="h6 bi bi-3-square"></i> Archiviazione Album </h6>
								<p class="mb-0 opacity-75">Attività popolamento archivio
									<br /> Qui dal deposito si "crea l'album", archiviando solo l'elenco 
									delle fotografie e video che contiene</p>
							</div>
						</div>
					</a>
					<a href="<?=URLBASE; ?>fotografie.php/carica-dettagli-da-fotografia/0" 
						target="_blank" 
						class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true">
						<div class="d-flex gap-2 w-100 justify-content-between">
							<div>
								<h6 class="mb-0"><i class="h6 bi bi-4-square"></i> Archiviazione fotografie con dettagli </h6>
								<p class="mb-0 opacity-75">Attività popolamento archivio
									<br>Pesca le fotografie non ancora lavorate dal deposito 
									ed estrae per ciascuna 
									in automatico alcuni dettagli dal nome file o dai dati exif.</p>
							</div>
						</div>
					</a>
					<a href="<?=URLBASE; ?>video.php/carica_dettagli_da_video/0" 
						target="_blank" 
						class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true">
						<div class="d-flex gap-2 w-100 justify-content-between">
							<div>
								<h6 class="mb-0"><i class="h6 bi bi-5-square"></i> Archiviazione video con dettagli</h6>
								<p class="mb-0 opacity-75">Attività popolamento archivio
									<br />Pesca i video in sospeso 
									ed estrae in automatico alcuni dettagli dal nome file o da dati interni</p>
							</div>
						</div>
					</a>
				</div>
			</div>
			<div class="col-4">
				<div class="list-group">
					<a href="<?=URLBASE; ?>richieste.php/elenco-amministratore/"
						target="_blank" 
						class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true">
						<div class="d-flex gap-2 w-100 justify-content-between">
							<div>
								<h6 class="mb-0">Richieste accesso originali, elenco</h6>
								<p class="mb-0 opacity-75">Vedi manuale</p>
							</div>
						</div>
					</a>
					<a href="<?=URLBASE; ?>elenchi.php/backup" 
						target="_blank" 
						class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true">
						<div class="d-flex gap-2 w-100 justify-content-between">
							<div>
								<h6 class="mb-0">Scarico record dalla data del </h6>
								<p class="mb-0 opacity-75">Scarica un file SQL da usare per ripristinare 
									l'archivio. Lo scarico parte dalla data dello scarico precedente e 
								va conservato come parte dell'archivio stesso nella sezione DATI.</p>
							</div>
						</div>
					</a>
					<a href="<?=URLBASE; ?>elenchi.php/elimina/" 
						target="_blank" 
						class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true">
						<div class="d-flex gap-2 w-100 justify-content-between">
							<div>
								<h6 class="mb-0">&#x1F6A8; Eliminazione record cancellabili </h6>
								<p class="mb-0 opacity-75">&#x1F6A8; &#x1F6A8; Cancellazione fisica <strong>irrevocabile</strong>&#x1F6A8; &#x1F6A8; . 
									Eseguire SEMPRE prima almeno una copia di salvataggio dei dati, e conservarla.
								</p>
							</div>
						</div>
					</a>
					<a href="https://mysql.aruba.it/" 
						target="_blank" 
						class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true">
						<div class="d-flex gap-2 w-100 justify-content-between">
							<div>
								<h6 class="mb-0">Accesso pannello mySqlAdmin Aruba</h6>
								<p class="mb-0 opacity-75">Passaggio al sito Aruba, servono user e password del database acquistato.
								</p>
							</div>
						</div>
					</a>
					<a href="https://admin.aruba.it/PannelloAdmin/" target="_blank" class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true">
						<div class="d-flex gap-2 w-100 justify-content-between">
							<div>
								<h6 class="mb-0">Accesso pannello admin Aruba</h6>
								<p class="mb-0 opacity-75">Passaggio al sito Aruba, servono user e password del dominio acquistato.
									<br>Aruba > Area clienti > accesso user & password di dominio > pannello di controllo > 
									Hosting Linux > Strumenti e impostazioni > Mostra log (registro) degli errori
								</p>
							</div>
						</div>
					</a>
				</div>
			</div>
		</div>
  </div>
  <footer class="py-3 fixed-bottom" style="z-index: -1000;" >
    <ul class="nav justify-content-center border-top pb-3 ">
      <li class="nav-item"><a href="<?=URLBASE; ?>museo.php" class="nav-link px-2 text-body-secondary">Museo</a></li>
      <li class="nav-item"><a href="<?=URLBASE; ?>man/" class="nav-link px-2 text-body-secondary" target="_blank">Manuale</a></li>
      <li class="nav-item"><a href="<?=URLBASE; ?>man/" class="nav-link px-2 text-body-secondary">D&R FAQ</a></li>
      <li class="nav-item"><a href="https://athesis77.it/" class="nav-link px-2 text-body-secondary">Associazione</a></li>
      <li class="nav-item"><a href="https://www.athesis77.it/associazione/presentazione/" class="nav-link px-2 text-body-secondary">Chi siamo</a></li>
    </ul>
    <p class="text-center text-body-secondary">&copy; 2024 Associazione Culturale Athesis APS - Boara Pisani PD</p>
  </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  </body>
</html>