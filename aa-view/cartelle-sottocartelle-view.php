<!doctype html>
<html lang="it">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Esplora Disco | <?=$cartella; ?> </title>
		<!-- jquery --><script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
		<!-- bootstrap --><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" >
		<!-- icone --><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" >
	</head>
	<body>
		<!-- TODO Da revisionare, per la paginazione va aggiunto nel form un campo 
		 per la quantità di record da esporre, a multipli 10 20 50 -->
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<div class="card">
					<div class="card-header" style='color: #<?=$cartella_radice["tinta_rgb"]; ?>'>
						<div class="bi flex-shrink-0 me-3" width="1.75em" height="1.75em" style=style='color: #<?=$cartella_radice["tinta_rgb"]; ?>'><i class="bi bi-archive" style='font-size:1.75rem;color:<?=$cartella_radice["tinta_rgb"]; ?>' ></i>
						&nbsp; 
						<span style='color: #<?=$cartella_radice["tinta_rgb"]; ?> !important;'><?=$cartella; ?></span>
					</div>
				</div>
					<div class="card-body" style="overflow-y: scroll;">
					<?php
			    include(ABSPATH.'aa-controller/mostra-messaggio-sessione.php');
					// didascalia 
					if ($leggimi > ""){
					?>
						<details>
							<summary>Leggimi</summary>
							<p><?=$leggimi; ?></p>
						</details>
					<?php
					} // if leggimi
					?>
						<div id="paginaPronta"></div>
						<p class="">Sono presenti <?=count($sottocartelle); ?> sottocartelle</p>
						<table class="table table-striped table-hover">
							<thead>
								<tr>
									<th class='col-1' ><a id="vaiIndietro" class="btn btn-primary" href="#dafare" role="button"><i class="bi bi-rewind-btn-fill"></i></a></th>
									<th class='col-10'>Cartella</th>
									<th class='col-1' ><a id="vaiAvanti" class="btn btn-primary" href="#dafare" role="button"><i class="bi bi-fast-forward-btn-fill"></i></a></th>
								</tr>
								<tbody>
									<?php
									$primo = [];
									$ultimo = [];
									if (count($sottocartelle) < 1){
										$primo['record_id']=0;
										$primo['livello1']='';
										$primo['livello2']='';
										$primo['livello3']='';
										$primo['livello4']='';
										$primo['livello5']='';
										$primo['livello6']='';
										$ultimo['record_id']=0;
										$ultimo['livello1']='';
										$ultimo['livello2']='';
										$ultimo['livello3']='';
										$ultimo['livello4']='';
										$ultimo['livello5']='';
										$ultimo['livello6']='';
									?>
										<tr><td colspan="3"><p class="h5">Nessuna sottocartella registrata</p></td></tr>
									<?php
									} else {
										/*	caricamento dati cartelle 
										 *	+ memorizzazione primo dell'elenco 
										 *	+ memorizzazione ultimo dell'elenco
										 */
										foreach($sottocartelle as $sottocartella){
										// memorizzare il primo elemento della lista per il "torna indietro"
											if (!isset($primo["record_id"])){
												$primo["record_id"]  =$sottocartella["record_id"];
												$primo["livello1"]   =$sottocartella["livello1"];
												$primo['livello2'] = isset($sottocartella['livello2']) ? $sottocartella['livello2'] : '';
												$primo['livello3'] = isset($sottocartella['livello3']) ? $sottocartella['livello3'] : '';
												$primo['livello4'] = isset($sottocartella['livello4']) ? $sottocartella['livello4'] : '';
												$primo['livello5'] = isset($sottocartella['livello5']) ? $sottocartella['livello5'] : '';
												$primo['livello6'] = isset($sottocartella['livello6']) ? $sottocartella['livello6'] : '';
											} 
											// memorizzare l'ultimo elemento della lista per il "vai avanti"
											$ultimo["record_id"]  =$sottocartella["record_id"];
											$ultimo["livello1"]   =$sottocartella["livello1"];
											$ultimo['livello2'] = isset($sottocartella['livello2']) ? $sottocartella['livello2'] : '';
											$ultimo['livello3'] = isset($sottocartella['livello3']) ? $sottocartella['livello3'] : '';
											$ultimo['livello4'] = isset($sottocartella['livello4']) ? $sottocartella['livello4'] : '';
											$ultimo['livello5'] = isset($sottocartella['livello5']) ? $sottocartella['livello5'] : '';
											$ultimo['livello6'] = isset($sottocartella['livello6']) ? $sottocartella['livello6'] : '';

											$tinta_rgb = ($sottocartella["tinta_rgb"]) ? $sottocartella["tinta_rgb"] : "000000";
											$sottocartella_nome  =$sottocartella["livello1"] ."/";
											$sottocartella_nome .= ($sottocartella["livello2"] > "") ? $sottocartella["livello2"] ."/" : "";
											$sottocartella_nome .= ($sottocartella["livello3"] > "") ? $sottocartella["livello3"] ."/" : "";
											$sottocartella_nome .= ($sottocartella["livello4"] > "") ? $sottocartella["livello4"] ."/" : "";
											$sottocartella_nome .= ($sottocartella["livello5"] > "") ? $sottocartella["livello5"] ."/" : "";
											$sottocartella_nome .= ($sottocartella["livello6"] > "") ? $sottocartella["livello6"] ."/" : "";
											$url_sottocartella  =$_SERVER["REQUEST_URI"];
											$url_sottocartella  = str_replace(URLZERO, '', $url_sottocartella);
											$url_sottocartella  = str_replace(' ', '+', $url_sottocartella);
											$url_sottocartella  = str_replace('%20', '+', $url_sottocartella);
									?>
										<tr>
												<td><a href="<?=URLBASE; ?>deposito.php/leggi/<?=$sottocartella["record_id"]; ?>"><i class="bi bi-archive" style="font-size:1rem;color: #<?=$tinta_rgb; ?>"></i></a>&nbsp;</td>
												<td><a href="<?=URLBASE; ?>deposito.php/leggi/<?=$sottocartella["record_id"]; ?>" style="text-decoration: none;"><span style="font-size:1rem;color: #<?=$tinta_rgb; ?>"><?=$sottocartella_nome; ?></span></a></td>
												<td nowrap>
													<a href="<?=URLBASE; ?>deposito.php/cambio-tinta/<?=$sottocartella["record_id"]; ?>?t=scansioni_disco&back=<?=$url_sottocartella; ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-palette-fill"></i></a><!-- a href="<?=URLBASE; ?>deposito.php/richiesta<?=$sottocartella["record_id"]; ?>&t=scansioni_disco&back=<?=$url_sottocartella; ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-cart-plus-fill"></i></a -->
												</td>
											</tr>
										<?php 
										} // foreach
									} // if 
									?>
								</tbody>
							</thead>
						</table>
						<!-- Il form nascosto per dare a javascript i dati -->
						<form id="paginazione">
							<input type="hidden" value='<?=$primo["record_id"]  || "" ?>' id="indietroId" />
							<input type="hidden" value='<?=$primo["livello1"]   || "" ?>' id="indietroLivello1" />
							<input type="hidden" value='<?=$primo["livello2"]   || "" ?>' id="indietroLivello2" />
							<input type="hidden" value='<?=$primo["livello3"]   || "" ?>' id="indietroLivello3" />
							<input type="hidden" value='<?=$primo["livello4"]   || "" ?>' id="indietroLivello4" />
							<input type="hidden" value='<?=$primo["livello5"]   || "" ?>' id="indietroLivello5" />
							<input type="hidden" value='<?=$primo["livello6"]   || "" ?>' id="indietroLivello6" />
							<input type="hidden" value='<?=$ultimo["record_id"] || "" ?>' id="avantiId" />
							<input type="hidden" value='<?=$ultimo["livello1"]  || "" ?>' id="avantiLivello1" />
							<input type="hidden" value='<?=$ultimo["livello2"]  || "" ?>' id="avantiLivello2" />
							<input type="hidden" value='<?=$ultimo["livello3"]  || "" ?>' id="avantiLivello3" />
							<input type="hidden" value='<?=$ultimo["livello4"]  || "" ?>' id="avantiLivello4" />
							<input type="hidden" value='<?=$ultimo["livello5"]  || "" ?>' id="avantiLivello5" />
							<input type="hidden" value='<?=$ultimo["livello6"]  || "" ?>' id="avantiLivello6" />
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
	<footer class="py-3 " style="z-index: -1;">
		<ul class="nav justify-content-center border-top pb-3 ">
			<li class="nav-item"><a href="<?=URLBASE; ?>ingresso.php" class="nav-link px-2 text-body-secondary">Ingresso</a></li>
			<li class="nav-item"><a href="<?=URLBASE; ?>man/" class="nav-link px-2 text-body-secondary" target="_blank">Manuale</a></li>
			<li class="nav-item"><a href="<?=URLBASE; ?>man/" class="nav-link px-2 text-body-secondary">D&R FAQ</a></li>
			<li class="nav-item"><a href="https://athesis77.it/" class="nav-link px-2 text-body-secondary">Associazione</a></li>
			<li class="nav-item"><a href="https://www.athesis77.it/associazione/presentazione/" class="nav-link px-2 text-body-secondary">Chi siamo</a></li>
		</ul>
		<p class="text-center text-body-secondary">&copy; 2024 Associazione Culturale Athesis APS - Boara Pisani PD</p>
	</footer>
	<!-- bootstrap lib --><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
	</body>
</html>
