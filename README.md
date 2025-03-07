# AMUVEFO-sigec-php

AMUVEFO is for *Athesis MUseo VEneto FOtografia*, Athesis' photographic venetian museum
SIGEC is for *Sistema Generale di Catalogazione*, Generic archiving system 
PHP is for ...you know.
Italian version of readme is on [LEGGIMI.md](./LEGGIMI.md)

AMUVEFO-SIGEC was built for my photo social club, tailored on our needs.
But to be flexible as we need (anytime requirements CHANGE), i apply
a key-value system.

Our virtual Library had tematic Rooms, Wardrobes, Shelves, folders, boxes
that represent a hierarchic order of library. But every single part
should had its lot of K-V which consent a fine research.

It is a project that started back in 2010 and has already been carried
out in different environments, and has now been rebuilt from scratch
after the 5th, or 6th version. The previous projects provided for a "support"
from the wordpress structure of the associative site, then a first version
was made with nodejs locally and then it was seen that in the web space
it was not possible to install nodejs and manage it as in localhost.

To be used by the elderly, and maintained by them, all messages,
the interface, the names of the archives and more are strictly in Italian.
And this can also cause some confusion.

## ABOUT

Basically *our treasure* are photo and/or video folded in
albums, so i build an *album table*, a *fotografie table*
and an *album table*.
As sidecar, other 3 tables for a list of referenced
key-value: *album_dettagli*, *fotografie_dettagli*,
*video_dettagli*. Not all file n folders stored in HDD
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

Writer' block was overcome with some simple tutorial that
mixed [html](https://en.wikipedia.org/wiki/HTML), [bootstrap](https://getbootstrap.com) n [php](https://www.php.net) to perform CRUD operations.
After some simple page to manage list i left to an [MVC](https://en.wikipedia.org/wiki/Model–view–controller) to separate how is user interface user experience, data storage, and in between the php code to manage both.

Why not use the trendy nodejs? well, to apply i need use a
cloud-based platform to create a PWA, but cost should be incontrollable.

