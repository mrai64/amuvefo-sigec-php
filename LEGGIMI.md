# AMUVEFO-sigec-php
English version of that doc is on [README.md](./README.md). 

AMUVEFO sta per Athesis MUseo Veneto FOtografia.
SIGEC sta per SIstema GEnerale di archiviazione e Catalogazione
PHP sta per ..si sa.

AMVEFO-SIGEC è stato realizzato per il mio photo club,
su misura per le nostre necessità. Ma dovendo essere il più
possibile flessibile (a ogni incontro le richieste CAMBIANO),
ho adottato un sistema chiavi-valori.

La nostra Biblioteca virtuale ha delle Sale tematiche, contenenti armadi,
ripiani, Faldoni, Scatole che forniscono un ordine gerarchico bibliotecario
di accesso. Ma ogni elemento può avere un proprio mazzetto di
chiavi-valori che consentono delle ricerche mirate.

Si tratta di un progetto partito ancora nel lontano 2010 e che
si è sviluppato in diversi ambienti, ed è stato rimesso su da zero
per la 5a o 6a volta. I progetti precedenti prevedevano un appoggio
sul sito associativo basato s wordpress, quindi la precedente
versione era basata tutta su javascript e nodejs, peccato che poi
non era possibile come su localhost intervenire nello
spazio web per gestirlo.

Dovendo farlo funzionare per dei giovanotti d'un tempo,
e farlo mantenere a loro, all'interno del sistema molti
elementi sono scritti in italiano. Misto all'inglese del php
può creare confuzione ma si è preferito questo.

## Generalità

Fondamentalmente il nostro *patrimonio* è fatto di immagini e/o video
raccolti in album, così ho creato degli archivi chiamati *tabella album*,
*tabella fotografie*, *tabella video*. Ho aggiunto degli altri archivi per memorizzare,
abbinati agli elementi delle tabelle principali, le coppie di 
chiave-valore: *album_dettagli*, *fotografie_dettagli*, *video_dettagli*.
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
