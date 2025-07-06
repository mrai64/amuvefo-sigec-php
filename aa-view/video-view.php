<!doctype html>
<html lang="it">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?= $video['titolo_video']; ?> | Video Singolo | AMUVEFO</title>
	<meta name='robots' content='noindex, nofollow' />
	<!-- jquery --><script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
	<!-- bootstrap --><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" >
	<!-- icone --><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css" rel="stylesheet" >
</head>
<body>
<div class="container">
	<div class="row">
		<?php
			include(ABSPATH.'aa-controller/mostra-messaggio-sessione.php');
		?>
	</div>
	<div class="row">
		<div class="col-2">
			<a href="<?= $torna_all_album; ?>" title="[Torna all'album]" ><i class="h2 bi bi-arrow-up-left-square" ></i></a>
			<?php
			if (get_set_abilitazione() > SOLALETTURA){
				echo '<a href="'. $richiesta_originali . '" title="[Richiesta foto]" ><i class="h2 bi bi-bookmark-check"></i></a>'."\n";
			}
			?>
		&nbsp;|&nbsp; 
		<a href='/ricerca.php'><i class="bi bi-search"></i></a>
		</div>
		<div class="col-10 h3">
			Siete in: <?= $siete_in; ?>
		</div>
	</div>
	<div class="row">
		<div class="col-6 dropdown">
			<a href="#" class="dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-expanded="false">
			<figure class="figure mh-50" >
				<video id="video" class="d-block w-100" src="<?=$video_src; ?>" controls preload="none"></video>
				<figcaption class="figure-caption"><?=$video['titolo_video']; ?> durata: <?=$durata_video; ?></figcaption>
			</figure></a>
			<ul class="dropdown-menu">
				<li><a href="<?=$torna_all_album;     ?>" class="dropdown-item">All'album</a></li>
				<li><a href="<?=$richiesta_originali; ?>" class="dropdown-item">Richiesta dell'originale</a></li>
				<li><hr class="dropdown-divider"></li>
				<li><a href="<?=URLBASE; ?>ingresso.php" class="dropdown-item">Accesso non anonimo</a></li>
			</ul>
			<a href="<?= $video_precedente; ?>" title="[prev in album]"><i class="h2 bi bi-arrow-left-square-fill"></i></a>
			<a href="<?= $video_seguente;   ?>" title="[next in album]"><i class="h2 bi bi-arrow-right-square-fill"></i></a>
		</div>
		<div class="col-5">
				<table class="table table-striped border-secondary"> 
					<thead>
						<tr>
							<th scope="col">Chiave ricerca</th>
							<th scope="col">Valore</th>
							<th scope="col"><a href="<?=$aggiungi_dettaglio; ?>" title="aggiungi dettaglio"><i class="h2 bi bi-pencil-square"></i></a></th>
						</tr>
					</thead>
					<tbody>
						<?php
						if (!isset($dettagli) || count($dettagli) == 0){
							echo '<tr><td colspan="3">Nessun dettaglio aggiunto</td></tr>';
						} else{
							foreach($dettagli as $dettaglio){
								echo '<tr>'."\n";
								echo '<td scope="row">'.$dettaglio['chiave'].'</td>'."\n";
								echo '<td>'.$dettaglio['valore'].'</td>'."\n";
								if (get_set_abilitazione() > SOLALETTURA ){
									echo '<td><a href="'.URLBASE.'video.php/modifica-dettaglio/'.$dettaglio['record_id'].'?video='.$dettaglio['record_id_padre'].'" '
									. 'title="modifica dettaglio"><i class="h2 bi bi-pencil-square"></i></a>'
									. '<a href="'.URLBASE.'video.php/elimina-dettaglio/'.$dettaglio['record_id'].'?video='.$dettaglio['record_id_padre'].'" '
									. 'title="elimina dettaglio"><i class="h2 bi bi-eraser-fill"></i></a></td>'."\n";
									
								} else {
									echo '<td><a href="#sololettura" '
									. 'title="modifica dettaglio"><i class="h2 bi bi-pencil-square"></i></a>'
									. '<a href="#sololettura" '
									. 'title="elimina dettaglio"><i class="h2 bi bi-eraser-fill"></i></a></td>'."\n";

								}
								echo '</tr>'."\n";
							}
						}
						?>
					</tbody>
				</table>
		</div>
	</div>
	<footer class="py-3 " style="z-index: -1;">
		<ul class="nav justify-content-center border-top pb-3 ">
			<li class="nav-item"><a href="<?=URLBASE; ?>ricerca.php" 
			class="nav-link px-2 text-body-secondary">Ricerca avanzata</a></li>
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
</div>
<!-- bootstrap+popper jQuery(sopra) --> 
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script>
	$(document).ready(function(){
		$("#video").on('contextmenu', 
		function(e){e.preventDefault();
		}, false);
	});
</script>
</body>
</html>
