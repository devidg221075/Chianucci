# Guida alla pubblicazione del sito CHIANUCCI

Questa guida spiega, passo per passo, come mettere online il sito dal pannello cPanel, come provarlo prima su un
sottodominio, come tornare alla versione precedente e cosa salvare nei backup. Non servono SSH né Composer.

## Cosa contiene il pacchetto

- `chianucci_app/`: l'applicazione (codice, librerie, catalogo). **Va fuori dalla cartella pubblica del dominio.**
- `public_html/`: i file pubblici (pagine, immagini, stili) e `install.php`.
- `GUIDA-PUBBLICAZIONE.md`: questa guida.

Il **codice di installazione** (token) non è nel pacchetto: ti viene consegnato a parte, una sola volta.

## 1. Prova su un sottodominio (consigliato)

1. In cPanel crea il sottodominio `nuovo.chianucci.it` (sezione **Domini**). Annota la sua cartella principale.
2. In **Database MySQL** crea un database di prova, un utente e assegna l'utente al database con tutti i privilegi.
3. In **Gestione file** carica lo .zip nella cartella principale dell'account (la home, sopra `public_html`) ed estrailo.
4. Sposta il contenuto della cartella `public_html` del pacchetto nella cartella principale del sottodominio.
   La cartella `chianucci_app` deve stare accanto alla cartella del sottodominio, non dentro.
5. Entra in `chianucci_app`, copia `.env.example` in `.env` e compila: `APP_URL`, `DB_DATABASE`, `DB_USERNAME`,
   `DB_PASSWORD` e i dati della posta in uscita (`MAIL_*`, quelli di un account email di cPanel).
6. Apri `https://nuovo.chianucci.it/install.php`, inserisci il codice di installazione, l'email e una password per
   l'amministratore e premi **Installa**. La pagina prepara il database, carica il catalogo e i testi e **si cancella da sola**.
7. Controlla il sito e accedi a `/admin`: al primo accesso attiva la verifica in due passaggi con un'app (Google
   Authenticator o simili) e conserva i codici di recupero.

## 2. Passaggio al dominio vero

1. Fai un backup di cPanel del sito attuale (vedi sotto) prima di toccare qualsiasi cosa.
2. Rinomina la cartella `public_html` attuale (ad esempio `public_html_vecchio`): è la tua copia per tornare indietro.
3. Crea una nuova cartella `public_html` e inserisci il contenuto del pacchetto, poi imposta `APP_URL=https://www.chianucci.it`
   nel `.env` e usa un database vuoto dedicato (o il database di prova, dopo averlo ripulito).
4. Apri `https://www.chianucci.it/install.php` e ripeti l'installazione.
5. Controlla: home, catalogo, una scheda prodotto, il modulo contatti, `/sitemap.xml` e qualche vecchio indirizzo (si deve
   aprire la pagina nuova, senza errori).

## 3. Come tornare alla versione precedente

- **Sito (file):** rinomina la cartella `public_html` nuova (ad esempio `public_html_nuovo`) e ridai il nome
  `public_html` alla cartella vecchia (`public_html_vecchio`). Il sito torna com'era in pochi secondi.
- **Aggiornamento del nuovo sito andato storto:** ripristina la cartella `chianucci_app` e la cartella pubblica dal
  backup precedente e importa il database salvato dallo stesso backup (phpMyAdmin → Importa).
- Prima di ogni aggiornamento conserva sempre lo .zip della versione che funziona.

## 4. Aggiornamenti futuri

1. Fai il backup (sezione sotto).
2. Carica il nuovo .zip, sostituisci le cartelle `chianucci_app` e `public_html` **tranne** `chianucci_app/.env` e
   `public_html/storage` (contiene le immagini caricate dal pannello).
3. Se il nuovo pacchetto include `install.php`, eseguilo come la prima volta.

## 5. Backup: cosa includere

Usa i backup di cPanel o del datacenter già attivi, assicurandoti che comprendano:

- il **database** MySQL del sito (tutte le tabelle);
- la cartella **`public_html/storage`** (immagini dei prodotti e delle categorie caricate dal pannello);
- il file **`chianucci_app/.env`** (password e chiave dell'applicazione: senza la chiave, le sessioni e i dati cifrati
  non si recuperano);
- la cartella `chianucci_app` completa, per poter ripristinare la versione in uso.

Controlla ogni mese che il backup più recente si riesca ad aprire e che il database sia leggibile.

## 6. Se qualcosa non funziona

- **Pagina bianca o errore 500:** apri `chianucci_app/storage/logs` e leggi l'ultimo file; controlla `.env`, i permessi di
  scrittura su `storage` e `bootstrap/cache` (755 o 775) e la versione di PHP (8.2 o superiore).
- **Il modulo contatti non invia email:** controlla le voci `MAIL_*` nel `.env`.
- **Immagini non visibili:** controlla che esista la cartella `public_html/storage/products`.
