# AMUVEFO-sigec-php

A "simple" n "general" catalogue-portfolio for a photo club

## Half italian half english

For some reason i will release that source code to
my photo club to use and develop in next years.
So some terms are in italian as album (really),
fotografia/fotografie (photo/photos), video
(really), consultatore (user, visitor).
But mostly are php code so i mix italian italian
verbs, subject,and so on...

During last 40years in my photo club a huge amount
of data was stored. We think at now 24TB of data.
A dozen+dozen HDD, an online copy, but as you know
HDD are like a biblioteque index, if a volume is in
Sport section cannot be at same time in another section.
So biblio use cards, and also I develop warm water
using for file n folders (directory) a tag system
based on dozen of terms like name/author, place/common,
etc.

Thats a generic/general cataloguin' system,
so i called it italian: SIstema GEnerale di Catalogazione
SI GE C applied to our Athesis' Museo Veneto Fotografia
(Museum of Venetian Photographic Art, MuVePh?).

## ABOUT

Basically _our treasure_ are photo and/or video folded in
albums, so i build an album table, a fotografie table
and an album table.
As sidecar, other 3 tables for a list of referenced
key-value: album_dettagli, fotografie_dettagli,
video_dettagli. Not all file n folders stored in HDD
become part of AMUVEFO-SIGEC, so i need first elencate
a list of folders (cartelle) and every folder cartella is readd
to build automatically an huge disk-list (disco)
excluding .DS_Store, Thumbs.db and so on, the others (in are: jpg,
tif, mp4, mov, etc. ) fill album, fotografie, video and
album_dettagli, fotografie_dettagli, video_dettagli.

To facilitate user exploring and cataloguin' I also
realized an Author' list, a Key' list, and for some
keys with a limited set of values another table.

## Adopted Style

Writer' block was broked with some simple tutoria that
mixed [html](https://en.wikipedia.org/wiki/HTML), [bootstrap](https://getbootstrap.com) n [php](https://www.php.net) to perform CRUD operations.
After some simple page to manage list i left to an [MVC](https://en.wikipedia.org/wiki/Model–view–controller) to separate how is user interface user experience, data storage, and in between the php code to manage both.

Why not use the trendy nodejs? well, to apply i need use a
cloud-based platform to create a PWA, but cost should be incontrollable.
