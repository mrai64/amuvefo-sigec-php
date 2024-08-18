/* relativo all'elenco in pagina html */
function aggiornaCartelleDaScansionare(){
    $.ajax({
        url: "https://archivio.athesis77.it/cartelle.php/lista-cartelle-sospese/0",
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
        url: "https://archivio.athesis77.it/cartelle.php/lista-cartelle-sospese/0",
        data: "",
        dataType: "html",
        method: "POST",
        async: false,
        success: function (response) {
            $("#cartelleDaScansionare").html("").html(response);
        }
    });
});

