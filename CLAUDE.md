# Preferenze di lavoro (dal cliente, valide per sempre)

- Fare **una sola domanda alla volta**.
- Ogni domanda va posta con **risposte suggerite a scelta singola (radio) o multipla (checkbox)**, usando lo strumento AskUserQuestion; il cliente può sempre scrivere una risposta libera.
- Se una domanda precedente non ha avuto una risposta esauriente, **ripeterla** prima di andare avanti.
- Comunicare in italiano.

# Contesto progetto

- Rifacimento di www.chianucci.it (manifattura orafa, Arezzo, marchio 340 AR, "Chianucci Maurizio").
- Riferimento grafico definitivo: Tavola Grafica Master "Natural Luxury 03" (logo CHI viola e oro; colori DECISI dal cliente = quelli del logo ufficiale: viola #2A1B6D, oro #BD9F69; neutri della tavola: avorio #F7F3E8, bianco #FFFFFF, antracite #2B2B2B; font Montserrat + Inter. I valori #3B2385 e #C9A96B della tavola NON si usano). I mockup precedenti valgono solo come guida per sezioni istituzionali, backoffice e area cliente.
- Catalogo pubblico senza prezzi per tutti; preventivi e ordini solo per account registrati e approvati.
- Hosting: VPS del cliente con cPanel e MySQL; root solo al datacenter.
- Dati aziendali ufficiali (forniti dal cliente): CHIANUCCI MAURIZIO s.r.l., Via Baracca, 7, 52100 Arezzo (Italy); tel. +39 0575 324.101 e +39 0575 355.365; email info@chianucci.it; PEC chianuccimauriziosrl@pec.it; Iscr. Reg. Ditte di Arezzo; C.F. e P.IVA 01744400514; REA n. 136050; Cap. Soc. i.v. € 25.000,00. (Il cliente ha scritto anche "ISO 01744400514": probabile refuso, da confermare.) Orari di apertura: NON mostrarli nel sito.
- Dati del catalogo: si importano dal sito pubblico attuale (letto con curl, che funziona; WebFetch no). Foto reali aziendali ancora da ricevere.
- Versione PHP (aggiornamento del cliente, 8/11/2026): va bene PHP 8.3 sul server attuale; sul nuovo server, tra circa due settimane, ci sarà PHP 8.5. Il pacchetto resta compatibile con 8.2+ (piattaforma composer 8.2.0); dopo la prova sul nuovo server valutare di alzare il minimo a 8.3 e passare a Laravel 13.
- Hosting: PHP 8.2+ disponibile; su cPanel non ci sono SSH né Composer. Stack scelto: Laravel + MySQL, pacchetto con librerie incluse (vedi Pubblicazione).
- Lingue (decisione aggiornata l'8/11/2026): il primo rilascio è bilingue IT + EN, migrando anche l'inglese già presente nel sito attuale (la decisione precedente "solo italiano" è SUPERATA). Selettore lingua IT/EN nel menu.
- Logo originali (non modificare) in design/logo/originals/: CHIANUCCI.svg (colori reali del logo: viola #2A1B6D, oro #BD9F69), CHIANUCCI_w.svg (versione bianca per sfondi scuri), CHIANUCCI.ai. Gli SVG contengono un <image> nascosto con JPEG incorporato: va tolto nelle copie per il web, mai negli originali.
- Menu principale: Azienda, Manifattura, Catalogo, Qualità, Servizi, Contatti, più Area Clienti come pulsante a parte; con selettore lingua IT/EN.
- Backoffice (decisione del cliente, versione 1): richieste di preventivo e ordini, approvazione clienti, gestione del catalogo, report e statistiche. Restano esclusi produzione, magazzino, spedizioni, contabilità.
- Flusso ordini (deciso): un ordine nasce solo da un preventivo accettato dal cliente; niente invio diretto di ordini dalla scheda prodotto.
- Catalogo: un solo livello di categorie, come oggi (23 categorie/famiglie elencate nell'audit sotto, tutte allo stesso livello), con filtri sui dati reali (categoria, titolo/materiale, misura maglia, dimensioni, peso) e ricerca testuale.
- Utenti: NON migrare gli utenti del sito attuale; i clienti si registrano di nuovo nel nuovo sito.
- Foto: immagini prodotto scaricate dal sito attuale (378 prodotti) + foto aziendali (sede, laboratorio, lavorazioni) che il cliente invierà.
- Modifica contenuti (richiesta del cliente): vuole modificare testi e pagine da solo, con editor LIBERO trascina-e-rilascia (non solo blocchi fissi). DECISO: piattaforma Laravel con editor libero trascina-e-rilascia integrato (page builder), non WordPress; più lavoro iniziale accettato dal cliente. Pannello admin Laravel (Filament) per contenuti, con IT/EN affiancati.
- Dati prodotto: importare SOLO dal sito pubblico (il cliente non vuole dipendere dal database vecchio, niente export phpMyAdmin). Scheda prodotto attuale (es. /it/prodotto/corda-100/): ARTICOLO, PESO (gr), DIMENSIONI (cm), MISURE MAGLIE (mm), PREZZO "Da definire" (soggetto al costo del metallo; NON importare né mostrare), TITOLO base Argento con opzioni oro AU 750 / AU 585 / AU 375 (min. 1 kg), info aggiuntive, quantità, pulsante preventivo YITH. NON esistono sezione/finitura come nei mockup: i filtri vanno basati sui dati reali (categoria, titolo/materiale, misura maglia, dimensioni, peso).
- Menu Catalogo: il cliente desidera un MEGA MENU anche grafico (categorie con immagini/anteprime) per il catalogo; DECISO: griglia di tessere con foto per le 23 categorie; su telefono elenco con miniature.
- Pubblicazione (DECISO): pacchetto .zip completo (librerie incluse) che il cliente carica da cPanel, con guida passo passo e procedura per tornare alla versione precedente; nessun accesso automatico esterno al server, nessuna dipendenza da GitHub per pubblicare. Prima prova su sottodominio di test. Backup (DECISO): si usano i backup di cPanel/datacenter già attivi; il progetto fornisce una guida su database e cartelle da includere (database MySQL, immagini caricate, file di configurazione); nessun backup automatico esterno.
- Sotto-progetti: 1) sito istituzionale e catalogo pubblico, 2) Area Clienti B2B, 3) backoffice.

# Audit del sito attuale (letto con curl l'8 novembre 2026)

- Piattaforma: WordPress 7.0.7 + WooCommerce 10.8.1, WPML (it/en), WPBakery, Slider Revolution, YITH Request a Quote e YITH Product Options (premium), Yoast SEO, tema "textron". Server dietro BitNinja WAF e Caddy.
- Il sito è GIÀ bilingue: /it/ e /en/ (la radice reindirizza a /it/). ~378 prodotti per lingua (756 URL nella sitemap prodotti).
- URL attuali da reindirizzare (301): /it/catalogo/, /it/catalogo/catalogo-pdf/, /it/lavorazioni/{fusione,galvanica,preparazione,pulitura-e-lucidatura,saldatura}/, /it/azienda-chi-siamo-maurizio-chianucci/, /it/contatti/, /it/prodotto/<slug>/, /it/categoria-prodotto/<slug>/, equivalenti /en/ (catalogue, processings, contacts, company-about-us-maurizio-chianucci, ...).
- Categorie/famiglie prodotto (IT): anelli, bizantina, calandra, colorati, collane, corda, doppia-tripla, elastico, elementi, fantasia, forzatina, grumetta, maglia-marina, molla, nonna-fantasia, palmie, pietre-zirconi, rettangolare, rolo, rosetta, schiava, scomparsa, traversino.
- Documenti PDF pubblici da conservare: certificato Synesgy (15/05/2024), rendiconto aiuti di Stato.
- Il sito attuale ha pagine di registrazione, my-account, carrello e checkout WooCommerce.
- Importazione completata (9/11/2026): 377 prodotti IT, 23 categorie con gli stessi numeri del sito vecchio. Solo 345 hanno traduzione EN: 31 pagine inglesi hanno un indirizzo diverso da quello italiano (es. oval-rolo-N, beat-forzatina-N, square-bizantina-N) e una è andata in timeout; per quei prodotti il sito mostra l'italiano. Da abbinare a mano o con una mappa dal backoffice. I testi delle vecchie pagine sono in storage/app/private/legacy-pages/ per la revisione.
- Nota tecnica: la rete della sessione lascia passare curl ma lo strumento WebFetch non risolve il dominio; le connessioni al sito a volte cadono, ritentare.
