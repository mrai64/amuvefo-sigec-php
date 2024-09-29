<!doctype html>
<html lang="it">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?=$album["titolo_album"] ?> | Album | AMUVEFO</title>
	<!-- jquery --><script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
	<!-- bootstrap --><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" >
	<!-- icone --><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" >
</head>
<body>
<div class="container">
<div class="row">
		<?php
			include(ABSPATH.'aa-controller/mostra-messaggio-sessione.php');
		?>
	</div>
  <div class="row">
    <div class="col-1">
		<a href="<?=$torna_su; ?>" title="[Torna all'album]" ><i class="h2 bi bi-arrow-up-left-square" ></i></a>
			<?php
			if ($_COOKIE['abilitazione'] > SOLALETTURA){
				echo '<a href="'. $richieste_originali .'" title="[Richiesta album]" ><i class="h2 bi bi-bookmark-check"></i></a>'."\n";
			} else {
				echo '<a href="#solalettura" title="[Richiesta album]" ><i class="h2 bi bi-bookmark-check"></i></a>'."\n";
			}
			?>
    </div>
    <div class="col-11 h3">
			Siete in: <?=$siete_in; ?><br>
    </div>
  </div>
	<div class="grid clearfix overflow-auto">
		<?=$float_foto; ?> 
		<?=$float_video; ?> 
  </div><!-- griglia grid foto video -->
  <div class="row">
		<div class="col-12">
			<table class="table table-striped border-secondary"> 
				<thead>
					<tr>
						<th class="col-3" scope="col">Chiave ricerca</th>
						<th class="col-8" scope="col">Valore</th>
						<th class="col-1" scope="col">
							<?php
								echo '<a href="'.$aggiungi_dettaglio.'" title="[Aggiungi dettaglio]" ><i class="h2 bi bi-pencil-square"></i></a>'."\n";
							?>
						</th>
					</tr>
				</thead>
				<tbody>
					<?=$table_dettagli; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>
<footer class="py-3 bg-light">
	<ul class="nav justify-content-center border-top pb-3 ">
		<li class="nav-item"><a href="<?=URLBASE; ?>ingresso.php" class="nav-link px-2 text-body-secondary">Ingresso</a></li>
		<li class="nav-item"><a href="<?=URLBASE; ?>man/" class="nav-link px-2 text-body-secondary" target="_blank">Manuale</a></li>
		<li class="nav-item"><a href="<?=URLBASE; ?>man/" class="nav-link px-2 text-body-secondary">D&R FAQ</a></li>
		<li class="nav-item"><a href="https://athesis77.it/" class="nav-link px-2 text-body-secondary">Associazione</a></li>
		<li class="nav-item"><a href="https://www.athesis77.it/associazione/presentazione/" class="nav-link px-2 text-body-secondary">Chi siamo</a></li>
	</ul>
	<p class="text-center text-body-secondary">
		&copy; 2024 Associazione Culturale Athesis APS - Boara Pisani PD
	</p>
</footer>
<!-- bootstrap no jQuery --> 
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
