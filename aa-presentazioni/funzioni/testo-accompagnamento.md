# Funzioni/testo-accompagnamento

## Richiesta

Alcune immagini possono essere corredate di testo accompagnatorio,
pertanto serve trovare una soluzione per rilevare, archiviare e consultare
i testi accompagnatori per le cartelle come per i video e i documenti. Sinonimo: **DIDASCALIA**

Abbiamo un album o un video o una immagine con testo di accompagnamento,
questo testo può essere di lunghezza indefinita ma limitata, questo testo
può contenere elementi di formattazione (grassetto, corsivo italico, sottolineato),
può essere di origine nota o derivare da trascrizione di ricordi e testimonianze orali.

La tabella delle immagini e dei video che contiene le coppie
chiave:valore non è adeguata a contenere un testo libero oltre o 200 400 caratteri, e inoltre
un testo accompagnatorio per le immagini può essere soggetto a modifiche e
revisioni di cui tenere conto in futuro.

## Dati da gestire

- Il testo accompagnato da una
- data di scrittura, da
- un id operatore consultatore che ha scritto o modificato il testo,
- e dal riferimento a cosa (quale album, quale foto, quale video)
- la data di archiviazione e backup come per gli oggetti referenziati
  che fa parte dei dati dell'oggetto archiviato e se questo viene
  successivamente rimosso il dato diventa un inutile ingombro.

Soluzioni

1. tabella
2. dettaglio con URI/URL
3. file sidecar

## Soluzione 1 tabella dedicata

La struttura della tabella dedicata deve ricalcare quella già usata
per i dettagli di album fotografie e video finora.

- record_id numerico progressivo assegnato in automatico
- tabella_padre set limitato (album, fotografie, video) espandibile
- record_id_padre numerico chiave esterna
- testo con l'uso di marcatori html oppure un testo da blocco note
  oppure usando dei codici come il cosiddetto markdown
- consultatore_id
- ultima_modifica_record
- record_cancellabile_dal che svolge la funzione
  di marcatore per il soft-delete; quando è
  una data futura (9999-12-31 23:59:59) il record
  è valido ovvero quando è una data passata
  (es. 2024-07-12 12:40:23) il dato è cancellabile
  previo backup periodico di tutto

## Soluzione 2 dettaglio con URI/URL

Utilizzando il cosiddetto sidecar, ovvero un file associato
all'oggetto con lo stesso nome file ma estensione diversa,
può essere un txt, come anche shtml, oppure un altro genere di estensione
anche inventata per il sistema purché non in uso già ad altri programmi.
il dettaglio potrebbe avere chiave:

- 'testo' no, troppo generico
- 'testo/accompagnatorio' no, non è un documento di trasporto.
  Anche se nel concetto di sidecar c'è, solidale alla moto.
- 'testo/didascalia' più convincente, nelle mostre il
  testo a lato di un'opera che può fornire dati tecnici
  di misura, tecnica, data di realizzazione ma può anche
  fornire spiegazioni sul periodo, su altre versioni,
  sull'autore, ecc.

## Soluzione 3 terza via

Dato che attualmente ho preparato il file detto sidecar,
di fatto non serve memorizzare nulla in archivio. Testo
se il file sidecar con lo stesso nomefile esiste, e se
manca tira dritto, se c'è il dato viene esposto. La
modifica o costruzione del file sidecar collegata a
un pulsante presente nell'interfaccia.

## Pro e Contro soluzione 1

**PRO:** Viene seguita l'architettura finora adottata, di creare
un archivio per le cose da memorizzare, con una gestione coordinata
delle funzioni di scrittura, lettura, aggiornamento e cancellazione,
sia quella soft (modifica della data di cancellazione da futura a passata)
che quella fisica (dopo il backup).

**CONTRO:** Tempi lunghi, o medi. Va sviluppato del codice
relativo alla visione della didascalia nella scheda dell'oggetto,
alla modifica della didascalia associata all'oggetto,
un archivio che memorizzi le didascalie con relativo *model*,
il blocco delle funzioni del *controller* e il blocco dei *viewer*.

## Pro e Contro soluzione 2

**PRO:** Si tratta di gestire solo un dettaglio aggiuntivo, seguendo
l'architettura finora usata con le cosiddette tabelle verticali
in cui c'è una tabella 'padre' che identifica un elemento e
una tabella 'figlio' che raccoglie tutti i 'dettagli' associati
all'elemento della tabella 'padre'. A differenza dei dettagli
finora utilizzati, questo andrebbe elaborato aprendo e importando
il contenuto nel dato da visualizzare.

**CONTRO:** Va modificata la gestione finora fatta delle chiavi-valori,
inserendo un "caso a parte" per le didascalie. Finora la gestione
dei campi chiave-valore segue uno schema semplice di scelta
della chiave tra un elenco di chiavi disponibili e il testo
libero del campo valore. Per le didascalie e i file sidecar
il campo libero comporta anche una ricerca e verifica dell'esistenza
del file, che non è a testo libero ma a valore obbligato,
in quanto file con lo stesso nome file dell'album o della fotografia.

## Pro e Contro soluzione 3

**PRO:** Non serve memorizzare dettagli o tabelle la cui dimensione
nel tempo potrebbe diventare abnorme.

**CONTRO:** Si complica la gestione dell'interfaccia utente e
comunque il file sidecar *resta fuori* dalle tabelle di ricerca.
Quindi se il testo sidecar contiene dati interessanti, vanno
inseriti nei dettagli dell'album o della fotografia.

---

## Formato del file

- TXT solo testo senza immagini
- MD  testo con \* \*\* oppure \#
  per inserire una struttura all'interno del testo
  che consenta grassetto, corsivo, sottolineato ecc.
- SHTML che consente di inserire sempre degli elementi
  di grassetto, corsivo ecc ma anche lnk per immagini
  e video e musiche, come nelle normali pagine web.

Estensione TXT  
**PRO:** Per grandi quantità il formato più leggero.  
**CONTRO:** Solo testo senza neretto, corsivo, e altri
particolari che rendono la lettura più ricca di informazioni.
Niente immagini e niente tabelle, specie se viene visualizzato
con un carattere cosiddetto a spaziatura variabile come
Arial al posto di Courier a spaziatura fissa, cioè: La i e la m
occupano uno spazio diverso o uguale.

Estensione MD  
**PRO:** Consente di tenere con pochi codici un formato arricchito
di elementi come grassetto, corsivo ecc.  
**CONTRO:** Serve imparare a inserire qua e là asterischi, cancelletti,
e altri elementi che non sono intuitivi, ma si imparano.

Estensione SHTML  
**PRO:** Consentono di avere con il sistema di marcatura html
alla base delle pagine web il pieno controllo dell'impaginazione
come pagine web. Si possono quindi inserire paragrafi, testi in
neretto o corsivo, immagini, tabelle.  
**CONTRO:** Serve avere un editor di pagine web a livello
di wordpress
