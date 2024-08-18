<?php 
/**
 *	@source /museo.php
 *  @author Massimo Rainato <maxrainato@libero.it>
 * 
 * Questa è la terza pagina cui si accede all'archivio,
 * la prima è di benvenuto, la seconda di inserimento 
 * accredito, la terza questa che mostra la facciata con le "sale"
 * utilizzabili. Per chi può anche altri link alla parte amministrativa
 * e al disco online
 */
if (!defined('ABSPATH')){
  include_once('./_config.php');
}
// verifica abilitazioni o eventuale richiesta accredito 
include_once(ABSPATH . 'aa-controller/controllo-abilitazione.php');
//
// caricamento pagina e sostituzione link vuoti 
$ingresso = file_get_contents(ABSPATH."aa-view/museo-view.htm");
if ($ingresso === false){
	header('Content-Type: text/plain; charset=UTF-8');
	http_response_code(503);
	exit("La lettura del file non è andata a buon fine.");
}

// applicazione dei link in base al contenuto di _COOKIE['abilitazione']

// abilitazione lettura
$ingresso = str_replace('#originali_athesis',             'https://www.athesis77.it/', $ingresso);
// prima quello più lungo
$ingresso = str_replace('#consultazione_autori_fondi',    'cartelle.php/cartella/2AUTOF/',  $ingresso);
$ingresso = str_replace('#consultazione_autori',          'cartelle.php/cartella/1AUTORI/', $ingresso);
// il + sostituisce lo spazio per la cartella "fondi 3" > "fondi+3", no %20
$ingresso = str_replace('#consultazione_fondi',           'cartelle.php/cartella/3FONDI/',  $ingresso);
$ingresso = str_replace('#consultazione_libri',           'cartelle.php/cartella/4LIBRI/',  $ingresso);
$ingresso = str_replace('#consultazione_localita_abcss',  'cartelle.php/cartella/6LOCA/',   $ingresso);
$ingresso = str_replace('#consultazione_localita',        'cartelle.php/cartella/5LOCA/',   $ingresso);
$ingresso = str_replace('#consultazione_dati',            'cartelle.php/cartella/7DATI/',   $ingresso);
$ingresso = str_replace('#consultazione_scuola',          'cartelle.php/cartella/8SCUOLA/', $ingresso);
$ingresso = str_replace('#consultazione_terrisaurum',     'cartelle.php/cartella/9TERRI/',  $ingresso);
$ingresso = str_replace('#consultazione_video',           'cartelle.php/cartella/10VIDEO/', $ingresso);

$ingresso = str_replace('#consultazione_amuvefo',         'https://archivio.athesis77.it/man/', $ingresso);
$ingresso = str_replace('#consultazione_fiaf',            'https://fiaf.net/veneto/',  $ingresso);
$ingresso = str_replace('#consultazione_athesis',         'https://www.athesis77.it/', $ingresso);

// abilitazione modifica 
if ($_COOKIE['abilitazione'] > SOLALETTURA){
	$ingresso = str_replace('#laboratorio_prove',           'amministrazione.php', $ingresso);
}

// abilitazione modifica con aruba drive 
if ($_COOKIE['abilitazione'] > MODIFICA){
	$ingresso = str_replace('#laboratorio_prove',           'amministrazione.php', $ingresso);
	// TODO Definire dei link di condivisione per ciascuna cartella definitiva 
	// e associarli
	$ingresso = str_replace('#consultazione_athesis',         'https://www.athesis77.it/', $ingresso);
}

// abilitazione amministrazione
if ($_COOKIE['abilitazione'] > MODIFICAPLUS){
	$ingresso = str_replace('#laboratorio_prove',           'amministrazione.php', $ingresso);
}

// Esposizione pagina trattata
echo $ingresso;
exit(0);