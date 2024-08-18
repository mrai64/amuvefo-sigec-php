<?php
/**
 * @source /96-chiavi-ricerca.php
 * @author Massimo Rainato <maxrainato@libero.it>
 * 
 * Una delle strade per cercare i materiali è seguire la suddivisione 
 * per cartelle e sottocartelle, un'altra è quella di seguire delle
 * "chiavi di ricerca" e qui sono elencate. 
 * 
 */
if (!defined('ABSPATH')){
	include_once("./_config.php");
}
include_once(ABSPATH.'aa-model/database-handler.php'); // $con connessione archivio mysql
include_once(ABSPATH.'aa-controller/controllo-abilitazione.php'); // check & set cookie

/*
1. il codice qui sotto va in /aa-view/chiavi-ricerca-lista.php 
2. il controller va in /aa-controller/elenchi.php come elenco_chiavi_di_ricerca() : string
	 e restituisce il contenuto del tbody 
3. il link alla pagina diventa /elenchi.php/chiavi-di-ricerca/leggi/0
 */
?><!doctype html>
<html lang="it">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Elenco Chiavi di ricerca</title>
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
	</head>
	<body>
		
	<div class="container pt-5">
		<?php
		include(ABSPATH.'aa-controller/mostra-messaggio-sessione.php');
		?>
		<div class="row">
			<div class="col-md-12">
				<div class="card">
					<div class="card-header">
						<h4>Elenco Chiavi di ricerca 
							<a href="https://archivio.athesis77.it/man/2-chiavi-di-ricerca/" class="btn btn-info btn-sm">Manuale</a>
							<a href="/96-chiavi-ricerca-aggiungi-mod.php" class="btn btn-secondary float-end ">Aggiunta</a>
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
						<?php 
						/**
						 * L'elenco delle chiavi di ricerca è limitato e pertanto non 
						 *   necessita di paginazione
						 */
						$leggi = "SELECT * FROM chiavi_elenco "
						. "ORDER BY chiave, record_id "; 
						$record_letti = mysqli_query($con, $leggi);
						if (mysqli_num_rows($record_letti) < 1) { 
							?>
							<tr><td colspan="7"><h5>Nessun record trovato</h5></td></tr>
							<?php 
						} else {
							foreach ($record_letti as $chiave){
								?>
							<tr>
							<td class='text-secondary' style='text-align:right;'><?= $chiave['record_id']; ?></td>
							<td class='h5'><?= $chiave['chiave']; ?></td>
							<td><a href="<?=$chiave['url_manuale']; ?>" target="_blank"><?=$chiave['url_manuale']; ?></a>
							</td>
							<td>
							<!-- <a href="#" class="btn btn-info btn-sm">Dettagli</a> -->
							<a href="/96-chiavi-ricerca-modifica-mod.php?id=<?= $chiave['record_id'] ?>" class="btn btn-success btn-sm">Modifica</a>
							<!-- <a href="#" class="btn btn-danger btn-sm">Elimina</a> -->
							</td>
							</tr>
								<?php
							}
						}
						?>
						</tbody>
					</table>
					</div>
				</div>
			</div>
		</div>
	</div>

	<footer class="py-3 " >
		<ul class="nav justify-content-center border-top pb-3 ">
			<li class="nav-item"><a href="/amministrazione.php" class="nav-link px-2 text-body-secondary">Amministrazione</a></li>
			<li class="nav-item"><a href="/man/" class="nav-link px-2 text-body-secondary" target="_blank">Manuale</a></li>
			<li class="nav-item"><a href="/man/" class="nav-link px-2 text-body-secondary">D&R FAQ</a></li>
			<li class="nav-item"><a href="https://athesis77.it/" class="nav-link px-2 text-body-secondary">Associazione</a></li>
			<li class="nav-item"><a href="https://www.athesis77.it/associazione/presentazione/" class="nav-link px-2 text-body-secondary">Chi siamo</a></li>
		</ul>
		<p class="text-center text-body-secondary">&copy; 2024 Associazione Culturale Athesis APS - Boara Pisani PD</p>
	</footer>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
	</body>
</html>