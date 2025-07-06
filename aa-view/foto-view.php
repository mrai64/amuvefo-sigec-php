<!doctype html>
<html lang="it">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?= $fotografia['titolo_fotografia']; ?> | Foto Singola | AMUVEFO</title>
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
		<div class="col-12">
			<a href='<?=URLBASE; ?>museo.php'><i class="h2 bi bi-house-up"></i></a>
			&nbsp; 
			<a href="<?= $torna_all_album; ?>" 
				title="[Torna all'album]" ><i class="h2 bi bi-arrow-left-square" ></i></a>
			&nbsp;|&nbsp; 
			<a href='/ricerca.php'><i class="h2 bi bi-search" ></i></a>
			<?php // per sola consultazione non appare
			if (get_set_abilitazione() > SOLALETTURA){
				echo "&nbsp;|&nbsp; "
				. '<a href="'. $richiesta_originali . '" '
				. 'title="[Richiesta foto]" ><i class="h2 bi bi-bookmark-check"></i></a>'."\n";
			} else {
				echo "&nbsp;|&nbsp; "
				. '<a href="#solalettura" '
				. 'title="[Richiesta foto]" ><i class="h2 bi bi-bookmark-check link-secondary"></i></a>'."\n";
			}
			?>
			&nbsp;|&nbsp; 
			<i class="h2 bi bi-geo-alt" ></i><?=$siete_in; ?>
		</div>
	</div>
	<div class="row">
		<div class="col-12 fs-4"><?php 
			echo $fotografia['titolo_fotografia'];
			if (get_set_abilitazione() > SOLALETTURA){
				echo '&nbsp;|&nbsp;<a href="'.URLBASE.'fotografie.php/modifica_titolo/'.$fotografia['record_id'].'"'
				. ' title="Modifica titolo"><i class="fs-2 bi bi-keyboard"></i></a>'."\n";
			}
		?>			
		</div>
	</div>
	<div class="row">
		<div class="col-8 dropdown">
			<a href="#" class="dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-expanded="false">
			<figure class="figure mh-50" >
				<img id="foto" class="d-block w-100" alt="..." src="<?= $fotografia_src; ?>" > 
				<figcaption class="figure-caption"><?=$fotografia['titolo_fotografia']; ?></figcaption>
			</figure></a>
			<ul class="dropdown-menu">
				<li><a href="<?=$torna_all_album;     ?>" class="dropdown-item">All'album</a></li>
				<li><a href="<?=$richiesta_originali; ?>" class="dropdown-item">Richiesta dell'originale</a></li>
				<li><hr class="dropdown-divider"></li>
				<li><a href="<?=URLBASE; ?>ingresso.php" class="dropdown-item">Accesso non anonimo</a></li>
			</ul>
			<br style="clear:both;"/>
			<a href="<?=$foto_precedente; ?>" title="[prev in album]"><i class="h2 bi bi-arrow-left-square-fill"></i></a>
			&nbsp;&nbsp;&nbsp;
			<a href="<?=$foto_seguente;   ?>" title="[next in album]"><i class="h2 bi bi-arrow-right-square-fill"></i></a>
			&nbsp;&nbsp;&nbsp;
			<?php // didascalia - 
			if (get_set_abilitazione() > SOLALETTURA){
				if ($didascalia_id > 0){
					// modifica
					echo "<a href='".URLBASE.'didascalie.php/aggiorna/'.$didascalia_id."' title='Modifica didascalia'>"
					. '<i class="h2 bi bi-pencil-square"></i></a>';
				} else {
					// si può aggiungere
					echo "<a href='".URLBASE.'didascalie.php/aggiungi/fotografie/'.$fotografia['record_id']."' title='Aggiungi didascalia'>"
					. '<i class="h2 bi bi-plus-square-fill"></i></a>';
				}
			} else {
				// aggiugni ma non funzionante
				echo "<a href='#solalettura' title='Gestione didascalia'>"
				. '<i class="h2 bi bi-pencil-square link-secondary"></i></a>';
			}
			// espongo la didascalia (ex _leggimi.txt della cartella e sidecar dei file)
			if ($leggimi>""){
				echo '<div class="">'.PHP_EOL;
				echo nl2br($leggimi);
				echo '</div>'.PHP_EOL;
			}
			?>
		</div>
		<div class="col-4">
				<table class="table table-striped border-secondary"> 
					<thead>
						<tr>
							<th scope="col">Chiave ricerca</th>
							<th scope="col">Valore</th>
							<th scope="col"><a href="<?=$aggiungi_dettaglio; ?>" 
							<?php echo (get_set_abilitazione() > SOLALETTURA)? '' : ' class="link-secondary" ' ;?>
							title="aggiungi dettaglio"><i class="h2 bi bi-pencil-square"></i></a></th>
						</tr>
					</thead>
					<tbody>
						<?php
						if (!isset($dettagli) || count($dettagli) == 0){
							//echo '<tr><td colspan="3">Immagine priva di dettagli in archivio</td></tr>';
							echo '<tr><td colspan="3">Nessun dettaglio aggiunto</td></tr>';
						} else{
							foreach($dettagli as $dettaglio){
								echo '<tr>'."\n";
								echo '<td class="fs-6" scope="row">'.$dettaglio['chiave'].'</td>'."\n";
								echo '<td class="fs-6">'.$dettaglio['valore'].'</td>'."\n";

								if (get_set_abilitazione() > SOLALETTURA ){
									echo '<td nowrap><a href="'.URLBASE.'fotografie.php/modifica-dettaglio/'.$dettaglio['record_id'].'?f='.$dettaglio['record_id_padre'].'" '
									. 'title="modifica dettaglio"><i class="h4 bi bi-pencil-square"></i></a>'
									. '<a href="'.URLBASE.'fotografie.php/elimina-dettaglio/'.$dettaglio['record_id'].'?f='.$dettaglio['record_id_padre'].'" '
									. 'title="elimina dettaglio"><i class="h4 bi bi-eraser-fill"></i></a></td>'."\n";
									
								} else {
									echo '<td nowrap><a class="link-secondary" href="#sololettura" '
									. 'title="modifica dettaglio"><i class="h4 bi bi-pencil-square"></i></a>'
									. '<a class="link-secondary" href="#sololettura" '
									. 'title="elimina dettaglio"><i class="h4 bi bi-eraser-fill"></i></a></td>'."\n";

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
		$("#foto").on('contextmenu', 
		function(e){e.preventDefault();
		}, false);
	});
</script>
</body>
</html>
