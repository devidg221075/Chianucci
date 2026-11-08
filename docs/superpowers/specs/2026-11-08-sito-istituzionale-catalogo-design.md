# Chianucci — Sito istituzionale e catalogo pubblico (sotto-progetto 1)

Data: 8 novembre 2026. Stato: bozza da approvare.

## 1. Obiettivo

Rifare www.chianucci.it per CHIANUCCI MAURIZIO s.r.l. (manifattura orafa, Arezzo, marchio 340 AR) con una veste premium coerente con il logo reale. Il sito presenta l'azienda, mostra il catalogo senza prezzi e porta i clienti professionali a chiedere preventivi. Questa specifica copre solo il sotto-progetto 1; Area Clienti B2B (2) e backoffice (3) avranno specifiche proprie.

## 2. Ambito del sotto-progetto 1

Incluso:
- design system con il brand reale;
- pagine istituzionali modificabili con editor libero;
- catalogo pubblico con mega menu, categorie, schede e filtri;
- italiano e inglese;
- importazione di contenuti e prodotti dal sito attuale;
- SEO e redirect;
- pacchetto di pubblicazione e guida;
- predisposizione di utenti e ruoli, senza funzioni cliente.

Escluso (sotto-progetti 2 e 3): registrazione e login clienti, preventivi, ordini, approvazione clienti, report. Il pulsante "Richiedi preventivo" nel sotto-progetto 1 rimanda alla pagina Area Clienti, che finché non esiste mostra un messaggio e il modulo contatti.

## 3. Brand e design

- Riferimento: Tavola Grafica Master Natural Luxury 03 (layout, componenti, mood). I mockup precedenti valgono solo come guida per le sezioni istituzionali, il backoffice e l'area cliente.
- Logo: file originali in `design/logo/originals/` (`CHIANUCCI.svg`, `CHIANUCCI_w.svg` per sfondi scuri, `CHIANUCCI.ai`), mai modificati; copie per il web senza l'`<image>` nascosto con JPEG incorporato.
- Colori: viola #2A1B6D e oro #BD9F69 (dal logo); avorio #F7F3E8, bianco #FFFFFF, antracite #2B2B2B (dalla tavola). I valori #3B2385 e #C9A96B della tavola non si usano.
- Font: Montserrat per titoli e navigazione, Inter per testi. Mobile first.
- Menu principale: Azienda, Manifattura, Catalogo, Qualità, Servizi, Contatti; pulsante Area Clienti separato; selettore IT/EN.
- Mega menu Catalogo: griglia di tessere con foto e nome per le 23 categorie; su telefono, elenco con miniature.

## 4. Architettura

- Applicazione Laravel (PHP 8.2 o superiore) con MySQL, sulla VPS del cliente con cPanel. Stesso dominio e stessa posta di oggi.
- Pannello di amministrazione Filament con italiano e inglese affiancati.
- Page builder con editor libero trascina-e-rilascia per le pagine istituzionali (scelta del cliente; più lavoro iniziale accettato). Per il catalogo, schede strutturate.
- Utenti, ruoli e permessi predisposti per le fasi 2 e 3.
- Nessun SSH né Composer sul server: le librerie sono incluse nel pacchetto.

## 5. Pagine

Home, Azienda, Manifattura (con Fusione, Preparazione, Saldatura, Pulitura e lucidatura, Galvanica), Qualità, Servizi, Catalogo (elenco, categoria, scheda), Contatti, Privacy Policy e cookie. PDF pubblici da conservare: certificato Synesgy (15/05/2024), rendiconto aiuti di Stato, catalogo PDF.

Contatti: CHIANUCCI MAURIZIO s.r.l., Via Baracca 7, 52100 Arezzo; tel. +39 0575 324.101 e +39 0575 355.365; info@chianucci.it; PEC chianuccimauriziosrl@pec.it; P.IVA e C.F. 01744400514; REA 136050; capitale sociale € 25.000,00 i.v.; Iscr. Reg. Ditte di Arezzo. Gli orari non si mostrano. Il modulo contatti ha protezione antispam e consenso privacy.

## 6. Catalogo e dati

- Struttura: un solo livello, 23 categorie (anelli, bizantina, calandra, colorati, collane, corda, doppia-tripla, elastico, elementi, fantasia, forzatina, grumetta, maglia-marina, molla, nonna-fantasia, palmie, pietre-zirconi, rettangolare, rolo, rosetta, schiava, scomparsa, traversino).
- Prodotto: codice/articolo, nome, descrizione, categoria, peso (g), dimensioni (cm), misura maglia (mm), titolo (argento di base; opzioni oro AU 750, 585, 375, minimo 1 kg), immagini, testi IT/EN. Nessun campo prezzo, né pubblico né nel backoffice.
- Filtri sui dati reali: categoria, titolo/materiale, misura maglia, dimensioni, peso; ricerca testuale. Non esistono sezione e finitura (erano nei mockup, non nei dati).
- Pulsante "Richiedi preventivo" su scheda ed elenco; nel sotto-progetto 1 porta alla pagina Area Clienti.
- Importazione: solo dal sito pubblico attuale (circa 378 prodotti per lingua, IT e EN), senza database vecchio né esportazioni phpMyAdmin. Prezzo "Da definire" non importato. Immagini scaricate dal sito. Verifica prima della pubblicazione: confronto numeri per categoria e campione di schede.
- Utenti del sito attuale: non migrati.
- Foto aziendali (sede, laboratorio, lavorazioni): le invia il cliente.

## 7. Lingue

Italiano e inglese al primo rilascio, migrando anche l'inglese esistente. URL `/it/` e `/en/`, hreflang tra le versioni, traduzione di eventuali testi nuovi come bozza da far rivedere.

## 8. SEO e redirect

Mappa di redirect 301 da tutti i vecchi URL (pagine, lavorazioni, azienda, contatti, categorie, prodotti, in IT e EN) ai nuovi. Sitemap XML, meta titolo e descrizione per pagina e prodotto, dati strutturati (azienda, prodotti), controllo post-rilascio dei 404.

## 9. Sicurezza

Pannello con password robusta e verifica in due passaggi, protezione dei moduli e degli upload, limite ai tentativi di accesso, informativa privacy e gestione cookie (testo giuridico da confermare dall'azienda).

## 10. Pubblicazione e backup

- Pubblicazione: pacchetto .zip completo, caricato dal cliente da cPanel con guida passo passo e procedura per tornare alla versione precedente. Nessun accesso automatico esterno al server e nessuna dipendenza da GitHub per pubblicare. Prima prova su sottodominio di test, poi cambio della cartella principale del dominio.
- Backup: si usano i backup di cPanel/datacenter già attivi; il progetto fornisce una guida con database, immagini caricate e file di configurazione da includere.

## 11. Test

Verifica su telefono, tablet e computer, velocità, accessibilità di base, link e redirect, e confronto con il sito attuale prima del rilascio.

## 12. Punti aperti

- Conferma di "ISO 01744400514" nei dati societari (probabile refuso).
- Foto aziendali da ricevere.
- Testi legali (privacy e cookie) da confermare.
- Se l'importazione dal sito pubblico lascia lacune nei dati (peso, dimensioni, misura maglia), si completano dal backoffice.
- Specifiche di Area Clienti B2B e backoffice: sotto-progetti 2 e 3.
