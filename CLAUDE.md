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
- Dati del catalogo: si trovano nel sito attuale; il cliente sta abilitando il dominio www.chianucci.it nella rete della sessione per permettere a Claude di leggerlo (non ancora attivo; ritentare solo quando il cliente conferma). Restano da ottenere anche l'esportazione del database MySQL (phpMyAdmin) e le foto reali.
- Hosting: PHP 8.2+ disponibile; su cPanel non ci sono SSH né Composer. Stack scelto: Laravel + MySQL, pacchetto con librerie incluse, pubblicazione via Git di cPanel o upload.
- Lingue: primo rilascio solo italiano, ma sito predisposto al multilingua; inglese in una seconda fase.
- Logo originali (non modificare) in design/logo/originals/: CHIANUCCI.svg (colori reali del logo: viola #2A1B6D, oro #BD9F69), CHIANUCCI_w.svg (versione bianca per sfondi scuri), CHIANUCCI.ai. Gli SVG contengono un <image> nascosto con JPEG incorporato: va tolto nelle copie per il web, mai negli originali.
- Menu principale: Azienda, Manifattura, Catalogo, Qualità, Servizi, Contatti, più Area Clienti come pulsante a parte; selettore lingua IT/EN solo quando arriva l'inglese.
- Backoffice (decisione del cliente, versione 1): richieste di preventivo e ordini, approvazione clienti, gestione del catalogo, report e statistiche. Restano esclusi produzione, magazzino, spedizioni, contabilità.
- Flusso ordini (deciso): un ordine nasce solo da un preventivo accettato dal cliente; niente invio diretto di ordini dalla scheda prodotto.
- Categorie del catalogo: le stesse del sito attuale (da ricavare quando il dominio è raggiungibile).
- Sotto-progetti: 1) sito istituzionale e catalogo pubblico, 2) Area Clienti B2B, 3) backoffice.

# Audit del sito attuale (letto con curl l'8 novembre 2026)

- Piattaforma: WordPress 7.0.7 + WooCommerce 10.8.1, WPML (it/en), WPBakery, Slider Revolution, YITH Request a Quote e YITH Product Options (premium), Yoast SEO, tema "textron". Server dietro BitNinja WAF e Caddy.
- Il sito è GIÀ bilingue: /it/ e /en/ (la radice reindirizza a /it/). ~378 prodotti per lingua (756 URL nella sitemap prodotti).
- URL attuali da reindirizzare (301): /it/catalogo/, /it/catalogo/catalogo-pdf/, /it/lavorazioni/{fusione,galvanica,preparazione,pulitura-e-lucidatura,saldatura}/, /it/azienda-chi-siamo-maurizio-chianucci/, /it/contatti/, /it/prodotto/<slug>/, /it/categoria-prodotto/<slug>/, equivalenti /en/ (catalogue, processings, contacts, company-about-us-maurizio-chianucci, ...).
- Categorie/famiglie prodotto (IT): anelli, bizantina, calandra, colorati, collane, corda, doppia-tripla, elastico, elementi, fantasia, forzatina, grumetta, maglia-marina, molla, nonna-fantasia, palmie, pietre-zirconi, rettangolare, rolo, rosetta, schiava, scomparsa, traversino.
- Documenti PDF pubblici da conservare: certificato Synesgy (15/05/2024), rendiconto aiuti di Stato.
- Il sito attuale ha pagine di registrazione, my-account, carrello e checkout WooCommerce.
- Nota tecnica: la rete della sessione lascia passare curl ma lo strumento WebFetch non risolve il dominio; le connessioni al sito a volte cadono, ritentare.
