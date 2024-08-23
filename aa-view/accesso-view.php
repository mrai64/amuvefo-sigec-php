<!doctype html>
<html lang="it">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Accesso Archivio| AMUVEFO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- per modulo di accredito -->
    <style>
      html,
      body {
        height: 100%;
      }
      
      .form-signin {
        max-width: 330px;
        padding: 1rem;
      }

      .form-signin .form-floating:focus-within {
        z-index: 2;
      }

      .form-signin input[type="email"] {
        margin-bottom: -1px;
        border-bottom-right-radius: 0;
        border-bottom-left-radius: 0;
      }

      .form-signin input[type="password"] {
        margin-bottom: 10px;
        border-top-left-radius: 0;
        border-top-right-radius: 0;
      }
    </style>
  </head>
  <body>
  <div class="container pt-5">
    <?php
    include(ABSPATH.'aa-controller/mostra-messaggio-sessione.php');
    ?>
    <main class="form-signin w-100 m-auto">
      <form action="<?=URLBASE; ?>aa-controller/accesso-checkpoint.php" method="POST">
        <img class="mb-4" src="/aa-img/aa-login.png" alt="" width="325" height="75">

        <h1 class="h3 mb-3 fw-normal">Accreditatevi</h1>

        <div class="form-floating">
          <input type="email" class="form-control" name="accessoEmail" id="accessoEmail" placeholder="name@example.com" value="info@athesis77.it" required>
          <label for="accessoEmail">Indirizzo di posta elettronica</label>
        </div>

        <div class="form-floating">
          <input type="password" class="form-control" name="accessoPassword" id="accessoPassword" placeholder="Password" value="info@athesis77.it" required>
          <label for="accessoPassword">Password</label>
        </div>
        
        <div class="form-check text-start my-3">
          <input class="form-check-input" type="checkbox" value="okCookie" name="accessoCookie" id="accessoCookie" required>
          <label class="form-check-label" for="accessoCookie">
            Accetto la memorizzazione di cookie sul mio browser per motivi tecnici. 
          </label>
        </div>
        <div class="form-check text-start my-3">
          <input class="form-check-input" type="checkbox" value="termof" name="accessoTermini" id="accessoTermini" required>
          <label class="form-check-label" for="accessoTermini">
            Accetto i <a href="<?=URLBASE; ?>man/termini-di-servizio-e-condizioni-duso/" target="_blank">Termini di servizio e le Condizioni d'uso</a> 
          </label>
        </div>
        <p>Lasciando i valori preimpostati potete visionare i materiali nella persona di Anonimo Consultatore.</p>
        <p>La risposta errata è intenzionalmente ritardata.</p>

        <input type="hidden" name="return_to" value="<?=$return_to?>">

        <button type="submit" name="accesso_archivio" class="btn btn-primary w-100 py-2" >Accesso</button>
      </form>
    </main>
  </div>
  <footer class="py-3 " >
    <ul class="nav justify-content-center border-top pb-3 ">
      <li class="nav-item"><a href="<?=URLBASE; ?>man/" class="nav-link px-2 text-body-secondary" target="_blank">Manuale</a></li>
      <li class="nav-item"><a href="<?=URLBASE; ?>man/" class="nav-link px-2 text-body-secondary">D&R FAQ</a></li>
      <li class="nav-item"><a href="https://athesis77.it/" class="nav-link px-2 text-body-secondary">Associazione</a></li>
      <li class="nav-item"><a href="https://www.athesis77.it/associazione/presentazione/" class="nav-link px-2 text-body-secondary">Chi siamo</a></li>
    </ul>
    <p class="text-center text-body-secondary">&copy; 2024 Associazione Culturale Athesis APS - Boara Pisani PD</p>
  </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  </body>
</html>
