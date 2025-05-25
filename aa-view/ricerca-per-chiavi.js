// jQuery ready to user
function urlbase(){
	var protocol = window.location.protocol;
	var domain   = window.location.hostname;
	var urlzero  = (domain.includes("localhost")) ? ":8888/AMUVEFO-sigec-php/" : "/";
	var urlbase  = protocol + '//' + domain + urlzero;
	return urlbase;
}

$( function(){
	// carica la datalist delle chiavi di ricerca
	$.ajax({
		url: urlbase() + 'elenchi.php/elenco_chiavi/',
		type: 'GET', // The type of request
		success: function(res) {
			// console.log(res); // Log the response to the console
			$("#elencoChiaviRicerca").html("").html(res);
		} // success
	}); // ajax

	// associa al bottone la chiamata alla funzione
	$("#addRicerche").on('click', function(event){
		event.preventDefault();
		aggiungi_selezione();
	});

	$("#moduloRicerca").on('submit', function(event){
		event.preventDefault();
		// ajax ricerca album      con listaRisultatiAlbum
		var modulo = $("#moduloRicerca").serializeArray();

		$.post(
			urlbase() + 'ricerche.php/album',
			modulo
		)
		.done(function(html){
			$("#listaRisultatiAlbum").empty().append(html);
		})
		.fail(function(response){
			$("#listaRisultatiAlbum").empty().append(response.responseText);
		});

		$.post(
			urlbase() + 'ricerche.php/fotografie',
			modulo
		)
		.done(function(response){
			$("#listaRisultatiFotografie").empty().append(response);
		})
		.fail(function(response){
			$("#listaRisultatiFotografie").empty().append(response.responseText);
		});

	}); // moduloRicerca submit 
}); // document ready

// altra funzione richiamabile da button
function aggiungi_selezione(){
	const ricerca_item = '<hr />'
	+'<div class="col-3 mb-3">'
	+'<label class="form-label" for="chiave[]"><em>Chiave di ricerca</em>'
	+'<input class="form-control" type="text" name="chiave[]" value="" list="elencoChiaviRicerca" aria-label="Selezione chiave di ricerca" required>'
	+'</label>'
	+'</div>'
	+'<div class="col-2 mb-3">'
	+'<label class="form-label" for="operatore[]"><em>operatore</em>'
	+'<input class="form-control " type="text" name="operatore[]" list="operatori" required>'
	+'</label>'
	+'</div>'
	+'<div class="col-7 mb-3">'
	+'<label class="form-label w-100" for="valore[]"><em>Valore / nome /aggettivo </em>'
	+'<input class="form-control" type="text" name="valore[]" value="" required>'
	+'</label>'
	+'</div>';
	$("#elencoRicerche").append(ricerca_item);
	return false;
}
