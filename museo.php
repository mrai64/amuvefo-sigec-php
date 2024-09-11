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
$ingresso = file_get_contents(ABSPATH."aa-view/museo-view.php");
if ($ingresso === false){
	header('Content-Type: text/plain; charset=UTF-8');
	http_response_code(503);
	exit("La lettura del file non è andata a buon fine.");
}
// conversione a prescindere 
$ingresso = str_replace('<?=URLBASE; ?>', URLBASE, $ingresso);

// applicazione dei link in base al contenuto di _COOKIE['abilitazione']
// abilitazione lettura
$ingresso = str_replace('#originali_athesis',             'https://www.athesis77.it/', $ingresso);
// quello più lungo ha la precedenza
$ingresso = str_replace('#consultazione_autori_fondi',    URLBASE.'deposito.php/cartella/2AUTOF/',  $ingresso);
$ingresso = str_replace('#consultazione_autori',          URLBASE.'deposito.php/cartella/1AUTORI/', $ingresso);
$ingresso = str_replace('#consultazione_fondi',           URLBASE.'deposito.php/cartella/3FONDI/',  $ingresso);
$ingresso = str_replace('#consultazione_libri',           URLBASE.'deposito.php/cartella/4LIBRI/',  $ingresso);
$ingresso = str_replace('#consultazione_localita_abcss',  URLBASE.'deposito.php/cartella/6LOCA/',   $ingresso);
$ingresso = str_replace('#consultazione_localita',        URLBASE.'deposito.php/cartella/5LOCA/',   $ingresso);
$ingresso = str_replace('#consultazione_dati',            URLBASE.'deposito.php/cartella/7DATI/',   $ingresso);
$ingresso = str_replace('#consultazione_scuola',          URLBASE.'deposito.php/cartella/8SCUOLA/', $ingresso);
$ingresso = str_replace('#consultazione_terrisaurum',     URLBASE.'deposito.php/cartella/9TERRI/',  $ingresso);
$ingresso = str_replace('#consultazione_video',           URLBASE.'deposito.php/cartella/10VIDEO/', $ingresso);

$ingresso = str_replace('#consultazione_amuvefo',         URLBASE.'man/', $ingresso);
$ingresso = str_replace('#consultazione_fiaf',            'https://fiaf.net/veneto/',  $ingresso);
$ingresso = str_replace('#consultazione_athesis',         'https://www.athesis77.it/', $ingresso);

// applicazione dei link in base al contenuto di _COOKIE['abilitazione']
// abilitazione modifica 
$cookie_abilitazione      = str_replace("'", '', $_COOKIE['abilitazione']);
$abilitazione_solalettura = str_replace("'", '', constant('SOLALETTURA'));
$abilitazione_modifica    = str_replace("'", '', constant('MODIFICA'));
$abilitazione_modificaplus= str_replace("'", '', constant('MODIFICAPLUS'));

if (strncmp($cookie_abilitazione, $abilitazione_solalettura, 2) > 0){ // A > B 
	$ingresso = str_replace('#laboratorio_prove',           URLBASE.'amministrazione.php', $ingresso);
}

// applicazione dei link in base al contenuto di _COOKIE['abilitazione']
// abilitazione modifica con aruba drive 
if (strncmp($cookie_abilitazione, $abilitazione_modifica, 2) > 0){ // A > B 
	$ingresso = str_replace('#laboratorio_prove',           URLBASE.'amministrazione.php', $ingresso);
	// TODO Definire dei link di condivisione per ciascuna cartella definitiva 
	// e associarli
	$ingresso = str_replace('#consultazione_athesis',       'https://www.athesis77.it/', $ingresso);
}

// applicazione dei link in base al contenuto di _COOKIE['abilitazione']
// abilitazione amministrazione
if (strncmp($cookie_abilitazione, $abilitazione_modificaplus, 2) > 0){ // A > B 
	$ingresso = str_replace('#laboratorio_prove',           URLBASE.'amministrazione.php', $ingresso);
}

// tutto pronto, si espone
echo $ingresso;
exit(0);