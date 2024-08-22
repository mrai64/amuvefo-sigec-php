# AMUVEFO-sigec-php

Un "sistema" "generale" di catalogazione per un foto club

## Mezzo italiano mezzo inglese

Per qualche motivo dovrò consegnare il codice sorgente
al mio foto club perché sia usato e sviluppato nei
prossimi anni. Pertanto alcuni termini in mezzo al sorgente
sono in italiano, come album (in inglese uguale), fotografia/fotografie
video, consultatore.
La maggior parte è codice sorgente php così ho dovuto
mescolare italiano e inglese.

Nel corso degli ultimi 40anni il foto club ha raccolto
una mole impressionante di immagini e documenti. La stima
ammonta a circa 24TB di dati[^1]. Divisi in una dozzina+dozzina di HDD,
una copia di tutto online, ma si sa, i dischi sono come gli scaffali
di una biblioteca, se un libro è su uno scaffale dello Sport
non può essere allo stesso momento in un altro scaffale.
Così i bibliotecari si sono creati le schede, e pure io ho
sviluppato l'acqua calda usando per archiviare file e cartelle
un sistema di etichette basato su dozzine di termini come
nome/autore, luogo/comune ecc.

[^1]: Alla data di maggio 2024

## Generalità

Fondamentalmente il nostro patrimonio è fatto di immagini e/o video
raccolti in album, così ho creato degli archivi chiamati album,
fotografie, video. Ho aggiunto degli altri archivi per memorizzare,
abbinati agli elementi delle tabelle principali, le coppie di etichette
chiave-valore: album_dettagli, fotografie_dettagli, video_dettagli.
Non tutto quello che è nei dischi finisce in archivio a far parte
dell'AMUVEFO-SIGEC, così devo prima fare un elenco delle cartelle,
farle esaminare per avere un (enorme) elenco del contenuto disco
escludendo file p.es come .DS_Store, Thumbs.db e altri (entrano: jpg,
tif, mp4, mov, ecc.). Da questo riempio le tabelle album fotografie,
video e le loro tabelle dei dettagli con quello che si può.

Per facilitare la ricerca oltre che la compilazione dei moduli
ho anche creato un elenco Autori, un elenco Chiavi di ricerca e
per le chiavi che hanno un insieme limitato di valori da utilizzare
un elenco vocabolario delle chiavi-valori.

## Sistema adottato

Lo sblocco del lavoro è stato grazie ad alcuni tutorial
dove si vedeva un semplice mescolamento 
di codice [php](https://www.php.net) 
e [html](https://it.wikipedia.org/wiki/HTML) 
e [bootstrap](https://getbootstrap.com)
per creare una lista di voci e aggiornarla. Da quella son partito a
creare una serie di pagine semplici, per poi separare le parti tra
quella che vede l'utente, i dati che voglio archiviare e in mezzo
il codice php per gestire la presentazione dell'interfaccia e
i moduli con i dati. ho quindi cercato di usare il 
sistema [MVC](https://it.wikipedia.org/wiki/Model-view-controller),
dove 
M sta per il modello dei dati, 
V per le pagine vista
C comanda il traffico tra le V ed M, andata e ritorno, o solo andata.

Perché non usare il moderno e più popolare nodejs? Per usarlo
avrei dovuto adottare una piattaforma cloud e i costi potevano
non essere controllabili o sostenibili per un foto club.
