<!doctype html>
<html lang="it">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?=$album["titolo_album"]; ?> | Album | AMUVEFO</title>
	<meta name='robots' content='noindex, nofollow' />
	<!-- jquery --><script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
	<!-- bootstrap --><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" >
	<!-- icone --><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" >
</head>
<body>
<div class="container">
<div class="row">
		<?php // messaggio
			include(ABSPATH.'aa-controller/mostra-messaggio-sessione.php');
		?>
	</div>
  <div class="row">
		<div class="col-12">
			<a href="<?=URLBASE; ?>museo.php"><i class="fs-2 bi bi-house-up"></i></a>
			&nbsp; <a href='<?=$torna_sala; ?>'><i class="fs-2 bi bi-arrow-left-square"></i></a>
			&nbsp;|&nbsp; <a href='<?=URLBASE; ?>ricerca.php'><i class="fs-2 bi bi-search"></i></a>
			&nbsp;|&nbsp; 
			<?php
			if (get_set_abilitazione() > SOLALETTURA){
				echo '<a href="'. $richieste_originali .'" '
				. 'title="[Richiesta album]" ><i class="fs-2 bi bi-bookmark-check"></i></a>'."\n";
			} else {
				echo '<a href="#solalettura" '
				. 'title="[Richiesta album]" ><i class="fs-2 bi bi-bookmark-check link-secondary"></i></a>'."\n";
			}
			?>
			&nbsp;|&nbsp; <i class="fs-2 bi bi-geo-alt"></i><?=$siete_in; ?>
		</div>
  </div>
	<div class="row">
		<div class="col-9 fs-4"><?php
			echo $album['titolo_album'];
			if (get_set_abilitazione() > SOLALETTURA){
				echo '&nbsp;|&nbsp;<a href="'.URLBASE.'album.php/modifica_titolo/'.$album['record_id'].'"'
				. ' title="Modifica titolo"><i class="fs-2 bi bi-keyboard"></i></a>'."\n";
			}
		?>
		</div>
	</div>
	<div class="row">
		<div class="col-9">
			<?php // didascalia - 
				if (get_set_abilitazione() > SOLALETTURA){
					if ($didascalia_id > 0){
						// modifica
						echo "<a href='".URLBASE.'didascalie.php/aggiorna/'.$didascalia_id."' title='Modifica didascalia'>"
						. '<i class="fs-2 bi bi-pencil-square"></i></a>';
					} else {
						// si può aggiungere
						echo "<a href='".URLBASE.'didascalie.php/aggiungi/album/'.$album['record_id']."' title='Aggiungi didascalia'>"
						. '<i class="fs-2 bi bi-plus-square-fill"></i></a>';
					}
				} else {
					// aggiungi ma non funzionante
					echo "<a href='#solalettura' title='Gestione didascalia'>"
					. '<i class="fs-2 bi bi-pencil-square link-secondary"></i></a>';
				}
				echo " Didascalia <br>";
				// espongo la didascalia (ex _leggimi.txt della cartella e sidecar dei file)
				if ($leggimi>""){
					echo '<div class="">'.PHP_EOL;
					echo nl2br($leggimi);
					echo '</div>'.PHP_EOL;
				}
			?> 
		</div>
	</div>
	<div class="overflow-auto" style="max-height: 45vh;">
		<p class="fs-2">Fotografie
			<a data-bs-toggle="collapse" href="#fotoList" aria-expanded="false" aria-controls="Lista fotografie dell'album"><i class="fs-4 bi bi-eye-fill"></i></a>
		</p>
		<div id="fotoList" class="collapse.show grid clearfix">
			<?=$float_foto; ?> 
		</div><!-- griglia foto -->
	</div>
	<div class="overflow-auto" style="max-height: 30vh;">
		<p class="fs-2">Video
			<a data-bs-toggle="collapse" href="#videoList" aria-expanded="false" aria-controls="Lista video dell'album"><i class="fs-4 bi bi-eye-fill"></i></a>
		</p>
		<div id="videoList" class="grid clearfix overflow-visible">
			<?=$float_video; ?> 
		</div><!-- griglia video -->
	</div>
  <div  class="row">
		<p class="fs-2">Dati dettagli
			<a href="#dettagliList" data-bs-toggle="collapse" aria-expanded="false" aria-controls="Lista dettagli album"><i class="fs-4 bi bi-eye-fill"></i></a>	
		</p>
		<div id="dettagliList" class="collapse">
			<table class="table table-striped border-secondary"> 
				<thead>
					<tr>
						<th class="col-3" scope="col">Chiave ricerca</th>
						<th class="col-8" scope="col">Valore</th>
						<th class="col-1" scope="col">
							<a href="<?=$aggiungi_dettaglio; ?>" title="[Aggiungi dettaglio]" ><i class="h2 bi bi-pencil-square"></i></a>
						</th>
					</tr>
				</thead>
				<tbody>
					<?=$table_dettagli; ?>
				</tbody>
			</table>
		</div>
	</div><!-- dettagli album -->
	<div class="overflow-auto">
		<p class="fs-2">Carosello 
			<a data-bs-toggle="collapse" href="#carosello" aria-expanded="false" aria-controls="Carosello foto dell'album"><i class="fs-4 bi bi-eye-fill"></i></a>
		</p>
	</div>
	<div id="carosello" class="collapse">
		<?=$carousel_foto; ?>
	</div>
</div>
<footer class="py-3 " style="z-index: -1;">
	<ul class="nav justify-content-center border-top pb-3 ">
		<li class="nav-item"><a href="<?=URLBASE; ?>ricerca.php" 
		class="nav-link px-2 text-body-secondary">Ricerca</a></li>
		<li class="nav-item"><a href="<?=URLBASE; ?>man/" 
		class="nav-link px-2 text-body-secondary" target="_blank">Manuale</a></li>
		<li class="nav-item"><a href="<?=URLBASE; ?>man/" 
		class="nav-link px-2 text-body-secondary">D&R FAQ</a></li>
		<li class="nav-item"><a href="https://athesis77.it/" 
		class="nav-link px-2 text-body-secondary">Associazione</a></li>
		<li class="nav-item"><a href="https://www.athesis77.it/associazione/presentazione/" 
		class="nav-link px-2 text-body-secondary">Chi siamo</a></li>
	</ul>
	<p class="text-center text-body-secondary">&copy; 2024 Associazione Culturale Athesis APS - Boara Pisani PD</p>
</footer>
<!-- bootstrap no jQuery --> 
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
