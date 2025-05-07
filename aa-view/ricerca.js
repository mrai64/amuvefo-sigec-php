/**
 * gestione ricerca semplice
 * Alcuni elementi della pagina web sono utilizzabili come contenitori di dati
 * per gestire la paginazione avanti / indietro negli elenchi anche di migliaia
 * di elementi rintracciati con la ricerca.
 */
// jQuery classical onload
$(function(){
  // A questo punto una ricerca è già stata fatta, serve associare
  // alcuni button alla funzione con l'evento onclick()
  // avantiAlbum.click()
  // avantiFoto.click()
  // avantiVideo.click()
  // indietroAlbum.click()
  // indietroFoto.click()
  // indietroVideo.click()
  //

  $("#indietroAlbum").click(function(){
    var ricerca_id  = $("#ricerca_id").html();
    var primo_album = parseInt($("#albumPrimo").html());
    var ultimo_album= parseInt($("#albumUltimo").html());
    var tot_album   = parseInt($("#totAlbum").html()) | 12;
    var album_trovati=parseInt($("#albumTrovati").html());
    var protocol    = window.location.protocol;
    var domain      = window.location.hostname;
    var urlzero     = (domain.includes("localhost")) ? ":8888/AMUVEFO-sigec-php/" : "/";


    if (primo_album < 2){
      console.log('indietroAlbum', 'prima del primo');
      return false;
    }
    if (primo_album <= tot_album){
      primo_album = 1;
    } else {
      primo_album = primo_album - tot_album;
    }
    var url = protocol + '//' + domain + urlzero + 'ricerche.php/indietro/' + ricerca_id + '/album/' + primo_album + '/' + tot_album
    $.get( url )
    .done(function(html_ret){
      console.log('indietroAlbum click()');
      console.log('ret:', html_ret);
      $("#listaRisultatiAlbum").empty().append(html_ret);
    })
    .fail(function(response){
      console.log('indietroAlbum click()');
      console.log('Errore in ricerca url: ', url, response);
      $("#listaRisultatiAlbum").empty().append(response.responseText);
    });
  }); // indietroAlbum click()

  $("#avantiAlbum").click(function(){
    var ricerca_id    = $("#ricerca_id").html();
    var primo_album   = $("#albumPrimo").html();
    var ultimo_album  = $("#albumUltimo").html();
    var tot_album     = $("#totAlbum").html() | 6; // se non trova definito il primo usa il secondo
    var album_trovati = $("#albumTrovati").html();
    var protocol    = window.location.protocol;
    var domain      = window.location.hostname;
    var urlzero     = (domain.includes("localhost")) ? ":8888/AMUVEFO-sigec-php/" : "/";
    
    if (album_trovati <= (primo_album + tot_album) ){
      return false;
    }
    
    var url = protocol + '//' + domain + urlzero + 'ricerche.php/avanti/' + ricerca_id + '/album/' + ultimo_album + '/' + tot_album ;
    // per l'accademia la lettura dati si fa usando GET
    $.get( url )
    .done(function(html_ret){
      console.log('funzione avantiAlbum');
      console.log('tof ' + (typeof html_ret));
      console.log('html_ret ' + html_ret);
      $("#listaRisultatiAlbum").empty().append(html_ret);
    })
    .fail(function(response){
      console.log('funzione avantiAlbum');
      console.log('Errore in ricerca url: ', url, response);
      $("#listaRisultatiAlbum").empty().append(response.responseText);
    });
    return false;
  }); // avantiAlbum click()

  $("#indietroFoto").click(function(){
    var ricerca_id  = $("#ricerca_id").html();
    var prima_foto  = parseInt($("#fotoPrima").html());
    var ultima_foto = parseInt($("#fotoUltima").html());
    var tot_foto    = parseInt($("#totFoto").html());
    var foto_trovate= parseInt($("#fotoTrovate").html().toString());
    var protocol    = window.location.protocol;
    var domain      = window.location.hostname;
    var urlzero     = (domain.includes("localhost")) ? ":8888/AMUVEFO-sigec-php/" : "/";

    if (prima_foto < 2){
      console.log('indietroFoto', 'prima della prima');
      return false;
    }
    if (prima_foto <= tot_foto){
      prima_foto = 1;
    } else {
      prima_foto = prima_foto - tot_foto;
    }

    var url = protocol + '//' + domain + urlzero + 'ricerche.php/indietro/' + ricerca_id + '/fotografie/' + prima_foto + '/' + tot_foto
    $.get( url )
    .done(function(html_ret){
      console.log('indietroFoto click()');
      console.log('ret:', html_ret);
      $("#listaRisultatiFotografie").empty().append(html_ret);
    })
    .fail(function(response){
      console.log('indietroFoto click()');
      console.log('Errore in ricerca url: ', url, response);
      $("#listaRisultatiFotografie").empty().append(response.responseText);
    });
  }); // indietroFoto click()

  $("#avantiFoto").click(function(){
    var ricerca_id  = $("#ricerca_id").html();
    var prima_foto  = parseInt($("#fotoPrima").html());
    var ultima_foto = parseInt($("#fotoUltima").html());
    var tot_foto    = parseInt($("#totFoto").html());
    var foto_trovate= parseInt($("#fotoTrovate").html().toString());
    var protocol    = window.location.protocol;
    var domain      = window.location.hostname;
    var urlzero     = (domain.includes("localhost")) ? ":8888/AMUVEFO-sigec-php/" : "/";

    console.log('location', location);
    
    if (foto_trovate <= (prima_foto + tot_foto) ){
      console.log( 'avantiFoto ', 'foto_trovate', foto_trovate, 'prima_foto ', prima_foto, 'tot_foto '. tot_foto );
      return false;
    }
    if ((ultima_foto + tot_foto) > foto_trovate){
      tot_foto = foto_trovate - ultima_foto;
    }
    
    var url = protocol + '//' + domain + urlzero + 'ricerche.php/avanti/' + ricerca_id + '/fotografie/' + ultima_foto + '/' + tot_foto
    //dbg alert('url: [' + url + ']' );
    $.get( url )
    .done(function(html_ret){
      console.log('avantiFoto click() done');
      console.log('ret:', html_ret);
      $("#listaRisultatiFotografie").empty().append(html_ret);
    })
    .fail(function(response){
      console.log('avantiFoto click() fail');
      console.log('Errore in ricerca url: ', url, response);
      $("#listaRisultatiFotografie").empty().append(response.responseText);
    });
  }); // avantiFoto click()

  $("#indietroVideo").click(function(){
    var ricerca_id  = $("#ricerca_id").html();
    var primo_video = parseInt($("#videoPrimo").html());
    var ultimo_video= parseInt($("#videoUltimo").html());
    var tot_video   = parseInt($("#totvideo").html()) | 12;
    var video_trovati=parseInt($("#videoTrovati").html());
    var protocol    = window.location.protocol;
    var domain      = window.location.hostname;
    var urlzero     = (domain.includes("localhost")) ? ":8888/AMUVEFO-sigec-php/" : "/";

    if (primo_video < 2){
      console.log('indietroVideo', 'prima del primo');
      return false;
    }
    if (primo_video <= tot_video){
      primo_video = 1;
    } else {
      primo_video = primo_video - tot_video;
    }
    var url = protocol + '//' + domain + urlzero + 'ricerche.php/indietro/' + ricerca_id + '/video/' + primo_video + '/' + tot_video
    $.get( url )
    .done(function(html_ret){
      console.log('indietroVideo click()');
      console.log('ret:', html_ret);
      $("#listaRisultativideo").empty().append(html_ret);
    })
    .fail(function(response){
      console.log('indietroVideo click()');
      console.log('Errore in ricerca url: ', url, response);
      $("#listaRisultativideo").empty().append(response.responseText);
    });
  }); // indietrovideo click()

  $("#avantiVideo").click(function(){
    var ricerca_id     = $("#ricerca_id").html();
    var primo_video    = parseInt($("#videoPrimo").html());
    var ultimo_video   = parseInt($("#videoUltimo").html());
    var tot_video      = parseInt($("#totVideo").html()) | 12;
    var video_trovati  = parseInt($("#videoTrovati").html());
    var protocol    = window.location.protocol;
    var domain      = window.location.hostname;
    var urlzero     = (domain.includes("localhost")) ? ":8888/AMUVEFO-sigec-php/" : "/";

    if (video_trovati <= (primo_video + tot_video) ){
      return false;
    }
    
    var url = protocol + '//' + domain + urlzero + 'ricerche.php/avanti/' + ricerca_id + '/video/' + ultimo_video + '/' + tot_video
    //dbg alert('url: [' + url + ']' );
    $.post(
      url,
      {}
    )
    .done(function(html_ret){
      $("#listaRisultatiVideo").empty().append(html_ret);
    })
    .fail(function(response){
      console.log('Errore in ricerca url: ', url, response);
      $("#listaRisultatiVideo").empty().append(response.responseText);
    });
  }); // avantiVideo click()

}); // document ready