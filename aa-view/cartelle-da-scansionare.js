/* relativo all'elenco in pagina html */
function urlbase(){
	var protocol = window.location.protocol;
	var domain   = window.location.hostname;
	var urlzero  = (domain.includes("localhost")) ? ":8888/AMUVEFO-sigec-php/" : "/";
	var urlbase  = protocol + '//' + domain + urlzero;
	return urlbase;
}

function aggiornaCartelleDaScansionare(){
    $.ajax({
        url: urlbase() + "zona_intro.php/lista-cartelle-sospese/0",
        data: "",
        dataType: "html",
        method: "POST",
        async: false,
        success: function (response) {
            $("#cartelleDaScansionare").html("").html(response);
        }
    });
} // aggiornaCartelleDaScansionare()

$(document).ready(function () {
	// Caricamento lista al caricamento pagina
    $.ajax({
        url: urlbase()+ "zona_intro.php/lista-cartelle-sospese/0",
        data: "",
        dataType: "html",
        method: "POST",
        async: false,
        success: function (response) {
            $("#cartelleDaScansionare").html("").html(response);
        }
    });
});
