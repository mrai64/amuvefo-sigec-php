<!doctype html>
<html lang="it">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Accesso Foto Museo Athesis | Associazione Culturale Athesis APS - Boara Pisani PD</title>
    <meta name='robots' content='noindex, nofollow' />
		<!-- jquery --><script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
		<!-- bootstrap --><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
		<!-- icone bootstrap  --><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css" rel="stylesheet" >
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
      <form action="<?=URLBASE; ?>consultatori.php/accesso/" method="POST">
        <img class="mb-4" src="<?=URLBASE; ?>aa-img/aa-login.png" alt="" width="325" height="75">

        <h1 class="h3 mb-3 fw-normal">Accreditatevi</h1>

        <div class="form-floating">
          <input type="email" class="form-control" name="accesso_email" id="accessoEmail" placeholder="name@example.com" value="info@athesis77.it" required>
          <label for="accesso_email">Indirizzo di posta elettronica</label>
        </div>

        <div class="form-floating">
          <input type="password" class="form-control" name="accesso_password" id="accessoPassword" placeholder="Password" value="info@athesis77.it" required>
          <label for="accesso_password">Password</label>
        </div>
        <p>La piattaforma è nata per una consultazione nominale, accademica.
          <br />Per consentire una visione pubblica mantenendo la struttura
          attuale, si è deciso di inserire del valori nel modulo che consentano
          di procedere senza modifiche cliccando sul pulsante di accesso.
          <br />Per altre esigenze consultate il manuale 
          e contattate il comitato di gestione presso Associazione Culturale Athesis APS.<br />
          Avvisi legali<br >
          Le opere sono riprodotte a fini culturali e divulgativi secondo
          l'art.70 L. 633/1941 s.m.i.<br />
          I contenuti diffusi dal sito non possono essere utilizzati al fine
          di addestrare sistemi di intelligenza artificiale.</p>
        <div class="form-check text-start my-3">
          <input class="form-check-input" type="checkbox" 
          value="okCookie" checked 
          name="accessoCookie" id="accessoCookie" required>
          <label class="form-check-label" for="accessoCookie">
            Accetto la memorizzazione di cookie sul mio browser 
            per motivi tecnici. 
          </label>
        </div>
        <div class="form-check text-start my-3">
          <input class="form-check-input" type="checkbox" 
          value="termof" checked 
          name="accessoTermini" id="accessoTermini" required>
          <label class="form-check-label" for="accessoTermini">
            Accetto i <a href="<?=URLBASE; ?>man/termini-di-servizio-e-condizioni-duso/" target="_blank">Termini 
              di servizio e le Condizioni d'uso</a> 
          </label>
        </div>
        <p>La risposta errata è intenzionalmente ritardata.</p>

        <input type="hidden" name="return_to" value="<?=$return_to; ?>">
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
    <p class="text-center text-body-secondary">&copy; 2024-<?= date('Y'); ?> Associazione Culturale Athesis APS - Boara Pisani PD</p>
  </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
