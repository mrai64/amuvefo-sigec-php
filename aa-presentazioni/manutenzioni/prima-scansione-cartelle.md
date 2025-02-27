# Allineamento funzione di prima scansione cartelle

- urgenza: normale
- data inizio: 2025-02-27 

## Premessa 
Nel corso delle cose l'aggiornamento e l'aggiunta di materiale all'interno
dell'archivio parte da una lavorazione fatta offline.  
Segue un caricamento del materiale (cartelle album, immagini,
video, didascalie, altro) con il servizio ftp di Aruba.

Poi c'è una prima elaborazione dove, fornendo una posizione
fisica della cartella da esaminare p.es.  
*https://www.fotomuseoathesis.it/6LOCA/Boara/*  
il motorino scansiona la cartella e inserisce la stessa in una
tabella *scansione_cartelle*.

Un secondo passaggio parte dalla tabella *scansione_cartelle* per
riempire la tabella *scansioni_disco* dove gli stessi dati, ma
filtrati, consentono di preparare in seguito il vero e proprio
archivio consultabile strutturato in tabelle *album & album_dettagli*,
*fotografie & fotografie_dettagli*, *video & video_dettagli*.

## Da fare

Il router /cartelle.php richiama la funzione ~carica_cartelle_da_scansionare~,
ma il router e questa funzione non sono allineati alle altre
funzioni, che prevedono un input array con i campi del modulo e,
qualora i campi modulo non siano presenti, l'esposizione del modulo
*/aa-view/cartelle-da-scansionare.php*.

- aa-view/cartelle-da-scansionare.php deve essere rinominato come  
  aa-view/primo-caricamento.php
- la funzione carica_cartelle_da_scansionare deve essere rinominata come
  *primo_caricamento_cartelle*, 
- la funzione deve essere adeguata nella parte che riguarda la presenza
  di un array di input, come suindicato
- il router deve essere adattato per richiamare la funzione con l'array di input
  per gestire l'aggiornamento e con array vuoto per esporre il modulo

