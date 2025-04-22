// jQuery ready to user
$( function(){
	$("#moduloRicerca").on('submit', function(event){
		event.preventDefault(); // no si va, si resta qui.
		// ajax ricerca album      con listaRisultatiAlbum
		modulo = $("#moduloRicerca").serializeArray();

		// ricerca in album - rifare con async await
		$.post(
			'/ricerche.php/album-semplice',
			modulo
		)
		.done(function(json_ret){
			var json_in = $.parseJSON(json_ret);
			// errore senza fail
			if (typeof json_in['error'] !== 'undefined'){
				var html_ret = '<p class="alert">'+json_in['message']+'</p>';
				$("#listaRisultatiAlbum").empty().append(html_ret);
			} // errore senza fail
			else{
				//dbg console.log('ok', json_in);
				album_html_item = '<table class="table table-sm"><tbody>'
				+'';
				$("#listaRisultatiAlbum").empty().append(album_html_item);
				//dbg console.log('table', album_html_item);
				json_in['data'].forEach(element => {
					//dbg console.log('element', element);
					album_html_item = '<tr>'
					+'<td><a href="/album.php/leggi/'+element.record_id+'" target="_blank" ><i class="bi bi-archive" style="font-size:1rem;color: #'+element.tinta_rgb+'"></i></a>&nbsp;</td>'
					+'<td><a href="/album.php/leggi/'+element.record_id+'" target="_blank" style="text-decoration: none;"><span style="font-size:1rem;color: #'+element.tinta_rgb+'">'+element.titolo_album+'</span></a><br>'
					+'<span class="h6 small text-secondary text-wrap"><em>Siete in: </em>'+element.percorso_completo.replace('/', ' / ')+'</span></td>'
					+'</tr>';
					//dbg console.log('tr', album_html_item);
					$("#listaRisultatiAlbum").append(album_html_item);
				});
				album_html_item = '</tbody></table>'
				+'';
				//dbg console.log('/table', album_html_item);
				$("#listaRisultatiAlbum").append(album_html_item);
			}
		})
		.fail(function(response){
			console.log('Album ko response', response);
			$("#listaRisultatiAlbum").empty().append(response.responseText);
		});

		// ricerca in fotografie 
		$.post(
			'/ricerche.php/fotografie-semplice',
			modulo
		)
		.done(function(json_ret){
			var json_in = $.parseJSON(json_ret);
			// errore senza fail
			if (typeof json_in['error'] !== 'undefined'){
				var html_ret = '<p class="alert">'+json_in['message']+'</p>';
				$("#listaRisultatiFotografie").empty().append(html_ret);
			} // errore senza fail
			else{
				$("#listaRisultatiFotografie").empty();
				//dbg console.log('ok', json_in);
				var foto_html_item = '';
				json_in['data'].forEach(element => {
					//dbg console.log('element', element);

					foto_html_item = '<div style="display:block;width:200px;height:200px;" class="float-start">'
					+ '<a href="/fotografie.php/leggi/'+element.record_id+'" '
					+ 'title="'+element.titolo_fotografia+' " target="_blank" >'
					+ '<img src="https://fotomuseoathesis.it'+element.percorso_completo+'" '
					+ 'style="max-width:200px; max-height:200px;" '
					+ 'loading="lazy"  class="d-block w-100" />'
					+ '</a>'
					+ '</div>';
					//dbg console.log('tr', foto_html_item);
					$("#listaRisultatiFotografie").append(foto_html_item);
				});
			}
		})
		.fail(function(response){
			$("#listaRisultatiFotografie").empty().append(response.responseText);
		});

		// TODO ricerca in video 
		// TODO ricerca in didascalie 
	}); // moduloRicerca submit 
}); // document ready
