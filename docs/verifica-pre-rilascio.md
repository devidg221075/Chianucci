# Verifica pre-rilascio — sito istituzionale e catalogo

Data: 8 novembre 2026. Stato: verifiche automatiche e di browser eseguite su un'istanza locale con dati reali importati in parte
(l'importazione completa del catalogo è in corso: vedi "Da completare").

## Test automatici

- Suite completa (`php artisan test`): tutti i test verdi (pagine, catalogo, filtri, importer, redirect, SEO, sicurezza, modulo
  contatti, pacchetto, esportazione catalogo, verifica di tutti gli indirizzi della sitemap e dei vecchi indirizzi).
- Nessuna tabella, form, API o schermata contiene prezzi (test dedicati sul database, sul form admin, sulle schede e sul
  JSON-LD).

## Browser (Playwright, Chromium)

- Pagine: Home, Catalogo, scheda prodotto, Contatti, a 375, 768 e 1280 px; mega menu su computer (tessere con foto) e su
  telefono (elenco con miniature). Nessun errore JavaScript né richiesta a servizi esterni su pagine pubbliche e admin.
- Pannello `/admin`: accesso con password + codice a due passaggi, apertura dell'editor libero (GrapesJS) con 7 blocchi,
  aggiunta di un elemento, salvataggio: l'HTML viene ripulito (lo `<script>` inserito è stato rimosso) e il CSS viene
  salvato.
- Accessibilità automatica (axe-core, regole WCAG 2 A e AA) su 6 pagine pubbliche: 0 violazioni dopo la correzione del
  contrasto del testo oro (token `oro-scuro` #7A5F1F) e della paginazione.
- Velocità: circa 550 ms di caricamento completo in locale (senza cache né compressione del server); da rimisurare sulla VPS.

## Regole della tavola grafica (verifica automatica)

- `tests/Feature/DesignRulesTest.php` fa rispettare nel codice: spaziature a 8 punti (8, 16, 24, 32, 48, 64 px), griglia 12/8/4
  colonne (desktop 1200-1440, tablet 768-1199, mobile 320-767) con contenitore da 1200 px, scala dei titoli con righe multiple
  di 8, foto con proporzioni fisse, pulsanti primario/secondario con freccia come nella tavola.
- `scripts/audit-grafica.cjs` misura le stesse regole nel browser sulle pagine reali (padding, margini, gap, dimensioni dei
  titoli, larghezza del contenitore) a 390, 900 e 1280 px: esito **0 violazioni** su Home, Azienda, Catalogo, Contatti ed EN.
  Il controllo è stato provato anche al contrario (inserendo valori fuori regola le segnala).
- Limite: la tavola non dà misure numeriche per titoli e foto: la scala dei titoli e le proporzioni delle foto sono scelte di
  progetto coerenti con la tavola, non misure ricavate da essa.

## Pacchetto di pubblicazione

- Pacchetto reale creato con `php artisan site:package` (librerie di produzione senza strumenti di sviluppo, circa 46 MB
  senza le immagini complete del catalogo).
- Estratto in una cartella vuota e provato con `install.php`: migrazioni, pagine, catalogo e amministratore creati, il file
  si è cancellato da solo; Home, pagine, catalogo e login admin rispondono 200.

## Da completare prima del rilascio

1. **Importazione completa del catalogo**: `php artisan site:import --only=catalog --resume` (circa 378 prodotti per lingua).
   Alla fine: confronto dei numeri per categoria con il sito attuale e verifica a campione di 10 schede.
2. **Reindirizzamenti dei vecchi indirizzi**: `php artisan site:import --only=redirects`, poi ricreare il pacchetto
   (`php artisan site:package`) così che `catalog.json` li includa.
3. **Testi**: le pagine istituzionali sono bozze scritte dai mockup; i testi delle cinque lavorazioni e di Servizi vanno
   rivisti. L'informativa Privacy è un segnaposto da confermare dall'azienda. I testi del vecchio sito sono recuperabili con
   `php artisan site:import --only=pages` (salva i testi per la revisione senza sovrascrivere nulla).
4. **Grafica**: la verifica visiva contro i mockup Natural Luxury 03 è da fare insieme al cliente; le pagine usano il design
   system (colori del logo, Montserrat/Inter) ma non hanno ancora le fotografie aziendali né le immagini dei mockup.
5. **Editor**: i testi dell'interfaccia di GrapesJS (Device, Style Manager) sono in inglese.
6. **Posta**: impostare i dati SMTP (`MAIL_*`) nel `.env` della VPS e provare il modulo contatti.
7. **Dati societari**: confermare il "ISO 01744400514" del testo ricevuto (probabile refuso, non usato nel sito).
