# Chianucci — Sito istituzionale e catalogo: piano di realizzazione

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Costruire il nuovo sito istituzionale con catalogo pubblico senza prezzi di CHIANUCCI MAURIZIO s.r.l., bilingue IT/EN, con editor libero delle pagine, importando i contenuti dal sito WordPress attuale.

**Architecture:** Un'applicazione Laravel con MySQL e pannello Filament. Le pagine istituzionali sono documenti HTML/CSS per lingua, modificati con un editor trascina-e-rilascia (GrapesJS) dentro Filament; il catalogo è strutturato (categorie, prodotti, traduzioni). Un comando Artisan importa dal sito pubblico. Un comando di build produce uno .zip con le librerie incluse, da caricare da cPanel.

**Tech Stack:** PHP >= 8.2, Laravel (ultima stabile al momento dell'implementazione), MySQL, Filament, GrapesJS, Tailwind CSS, Pest, Playwright (verifica visiva).

**Spec:** `docs/superpowers/specs/2026-11-08-sito-istituzionale-catalogo-design.md` (preferenze di lavoro e contesto in `CLAUDE.md`).

## Global Constraints

- PHP 8.2 o superiore; sul server NON ci sono SSH né Composer: `vendor/` e gli asset compilati vanno nel pacchetto.
- Brand: viola `#2A1B6D`, oro `#BD9F69`, avorio `#F7F3E8`, bianco `#FFFFFF`, antracite `#2B2B2B`. NON usare `#3B2385` né `#C9A96B`.
- Font: Montserrat (titoli e navigazione), Inter (testi). Mobile first.
- Logo: originali in `design/logo/originals/` mai modificati; copie web senza l'`<image>` nascosto con JPEG incorporato.
- Nessun prezzo, in nessuna tabella, form, API o schermata, pubblica o admin.
- Lingue IT e EN al primo rilascio; URL `/it/...` e `/en/...`; la radice `/` reindirizza a `/it/`.
- Menu: Azienda, Manifattura, Catalogo, Qualità, Servizi, Contatti; pulsante Area Clienti separato; selettore IT/EN.
- Mega menu Catalogo: griglia di tessere con foto per le 23 categorie; su telefono, elenco con miniature.
- Orari di apertura mai mostrati. Dati societari: vedi spec §5.
- Importazione solo dal sito pubblico `https://www.chianucci.it` (HTTP con ritentativi: le connessioni cadono a volte); nessun database vecchio, nessun utente migrato.
- Pubblicazione solo tramite .zip caricato dal cliente da cPanel; nessun deploy automatico, nessuna dipendenza da GitHub per pubblicare.
- Comunicazioni e testi dell'interfaccia in italiano.

## Review Focus

- Prodotto senza traduzione EN (o senza immagine): la scheda EN non deve dare errore 500; mostra il testo IT e l'hreflang non punta a una pagina vuota.
- Filtri combinati senza risultati o con valori fuori range: pagina vuota con messaggio, mai errore.
- URL vecchio con maiuscole, slash finale o parametri (`?utm=`): il redirect 301 deve comunque scattare, senza catene.
- Contenuto HTML dell'editor libero con `<script>`, `onerror` o iframe: va sanitizzato prima del salvataggio e della resa.
- Importer rilanciato due volte o interrotto a metà: nessun duplicato, riprende dove si era fermato, nessuna perdita di dati già importati.

---

## File Structure

- `app/Models/{Category,Product,ProductTranslation,Page,Redirect}.php`: dati. Una responsabilità ciascuno.
- `app/Support/HtmlSanitizer.php`: pulizia dell'HTML dell'editor.
- `app/Http/Controllers/{PageController,CatalogController,ContactController,SitemapController}.php`
- `app/Http/Middleware/{SetLocale,LegacyRedirect,SecurityHeaders}.php`
- `app/Filament/Resources/{PageResource,CategoryResource,ProductResource,RedirectResource}.php` e il campo `app/Filament/Forms/PageEditor.php` (GrapesJS).
- `app/Console/Commands/{ImportSite,BuildPackage}.php`
- `app/Importer/{SiteClient,CatalogParser,PageParser,ImportRun}.php`
- `resources/views/layouts/site.blade.php`, `resources/views/components/{mega-menu,language-switch,footer}.blade.php`, `resources/views/{pages,catalog}/*.blade.php`
- `resources/css/site.css` (token del brand), `lang/{it,en}/site.php`
- `docs/guida-pubblicazione.md` (caricamento, ritorno alla versione precedente, backup)
- `tests/Feature/*`, `tests/Unit/*`

---

### Task 1: Scheletro Laravel e design system

**Files:**
- Create: progetto Laravel nella radice, `resources/css/site.css`, `resources/views/layouts/site.blade.php`, `public/brand/` (copie web del logo), `tests/Feature/BrandTest.php`

**Interfaces:**
- Produces: layout Blade `layouts.site` con `@yield('content')`, variabili CSS `--viola`, `--oro`, `--avorio`, `--antracite`, classi `.btn-primario`, `.btn-secondario`; `public/brand/chianucci.svg` e `chianucci-bianco.svg`.

- [ ] **Step 1: Test `test_brand_tokens_are_the_logo_colors`**: legge `resources/css/site.css` e asserisce che contenga `#2A1B6D` e `#BD9F69` e NON contenga `#3B2385` né `#C9A96B`.
- [ ] **Step 2: Test `test_web_logos_have_no_hidden_embedded_image`**: asserisce che `public/brand/chianucci.svg` non contenga `<image` né `base64`.
- [ ] **Step 3:** Esegui `./vendor/bin/pest --filter=BrandTest`; atteso: FAIL.
- [ ] **Step 4:** Crea il progetto Laravel, installa Tailwind e Pest, definisci i token in `site.css` (font Montserrat/Inter serviti in locale), crea il layout. Le copie web dei logo si ricavano dagli originali con un piccolo script che rimuove l'elemento `<image>`; gli originali restano intatti.
- [ ] **Step 5:** Esegui `./vendor/bin/pest --filter=BrandTest`; atteso: PASS.
- [ ] **Step 6: Commit** `feat: scheletro Laravel, design system e logo web`.

### Task 2: Modello dati del catalogo (senza prezzi)

**Files:**
- Create: migrazioni e modelli `Category`, `Product`, `ProductTranslation`; `tests/Feature/CatalogModelTest.php`

**Interfaces:**
- Produces: `Category{id, slug, image_path, sort}` con traduzioni `name` per `it|en` in tabella `category_translations`; `Product{id, code, category_id, weight_g decimal(8,2) null, length_cm decimal(8,2) null, link_mm decimal(8,2) null, titles json}`; `ProductTranslation{product_id, locale, name, slug, description null}`; `Product::images()` (tabella `product_images{product_id, path, sort}`); scope `Product::visibleIn(string $locale)`.

- [ ] **Step 1: Test `test_no_table_has_a_price_column`**: scorre le tabelle dello schema e asserisce che nessuna colonna si chiami `price`, `prezzo` o simili.
- [ ] **Step 2: Test `test_product_falls_back_to_italian_when_english_is_missing`**: prodotto con sola traduzione IT; `Product::visibleIn('en')` lo include e il nome restituito è quello IT.
- [ ] **Step 3:** Esegui i test; atteso: FAIL.
- [ ] **Step 4:** Implementa migrazioni, modelli e scope con `visibleIn` che applica il fallback IT.
- [ ] **Step 5:** Esegui i test; atteso: PASS.
- [ ] **Step 6: Commit** `feat: modello dati catalogo senza prezzi`.

### Task 3: Pannello Filament, ruoli e verifica in due passaggi

**Files:**
- Create: risorse Filament `CategoryResource`, `ProductResource`; migrazione `users.role`; `tests/Feature/AdminAccessTest.php`

**Interfaces:**
- Consumes: modelli del Task 2.
- Produces: ruoli `admin` e `cliente` (il secondo solo predisposto, senza funzioni); accesso a `/admin` consentito solo ad `admin` con 2FA attiva.

- [ ] **Step 1: Test `test_guests_and_clients_cannot_open_admin`**: ospite → redirect al login; utente `cliente` → 403.
- [ ] **Step 2: Test `test_admin_without_two_factor_is_forced_to_set_it_up`**.
- [ ] **Step 3: Test `test_product_form_has_no_price_field`**: renderizza il form prodotto e asserisce l'assenza di campi `price`.
- [ ] **Step 4:** Esegui; atteso: FAIL.
- [ ] **Step 5:** Installa Filament, abilita l'autenticazione a due fattori (TOTP), restringi `canAccessPanel` al ruolo `admin`, crea le risorse con IT/EN affiancati.
- [ ] **Step 6:** Esegui; atteso: PASS.
- [ ] **Step 7: Commit** `feat: pannello admin con ruoli e 2FA`.

### Task 4: Editor libero delle pagine (GrapesJS) con HTML sanificato

**Files:**
- Create: `app/Models/Page.php` (`slug`, `locale`, `title`, `html`, `css`, `meta_title`, `meta_description`), `app/Support/HtmlSanitizer.php`, `app/Filament/Forms/PageEditor.php`, `app/Filament/Resources/PageResource.php`, `tests/Unit/HtmlSanitizerTest.php`

**Interfaces:**
- Produces: `HtmlSanitizer::clean(string $html): string`; il modello `Page` lo applica in un mutator su `html`; campo Filament `PageEditor::make('html')` che salva anche `css`.

- [ ] **Step 1: Test `test_sanitizer_removes_scripts_handlers_and_iframes`**: `clean('<p onclick="x()">a</p><script>alert(1)</script><iframe src="//evil"></iframe><img src=x onerror=y>')` non contiene `script`, `onclick`, `iframe`, `onerror` ma contiene `<p>a</p>`.
- [ ] **Step 2: Test `test_sanitizer_keeps_links_images_and_classes`**: `<a href="/it/catalogo/" class="btn-primario">Vai</a>` e `<img src="/storage/a.jpg" alt="x">` restano invariati.
- [ ] **Step 3: Test `test_page_html_is_sanitized_on_save`**.
- [ ] **Step 4:** Esegui; atteso: FAIL.
- [ ] **Step 5:** Implementa il sanificatore con una libreria con lista bianca (es. HTMLPurifier), il mutator, il campo GrapesJS (blocchi: sezione, titolo, testo, immagine, tessere, pulsante, griglia) caricato come asset locale nel pacchetto, e `PageResource` con IT/EN affiancati.
- [ ] **Step 6:** Esegui; atteso: PASS.
- [ ] **Step 7: Commit** `feat: editor libero pagine con HTML sanificato`.

### Task 5: Layout pubblico, mega menu e routing bilingue

**Files:**
- Create: `app/Http/Middleware/SetLocale.php`, `resources/views/components/{mega-menu,language-switch,footer}.blade.php`, `app/Http/Controllers/PageController.php`, `routes/web.php`, `lang/{it,en}/site.php`, `tests/Feature/NavigationTest.php`

**Interfaces:**
- Consumes: `Page`, `Category` (con immagine e nome tradotto).
- Produces: rotte `GET /{locale}/{slug?}` con `locale ∈ {it,en}`, nome `page`; helper `localized_route(string $name, array $params, string $locale)`; `GET /` → 301 a `/it/`.

- [ ] **Step 1: Test `test_root_redirects_to_italian_home`**.
- [ ] **Step 2: Test `test_menu_has_the_six_entries_and_area_clienti_button`** per IT e per EN (etichette tradotte).
- [ ] **Step 3: Test `test_mega_menu_lists_all_23_categories_with_images`** con 23 categorie di prova.
- [ ] **Step 4: Test `test_language_switch_links_to_the_same_page_in_the_other_language`**.
- [ ] **Step 5:** Esegui; atteso: FAIL.
- [ ] **Step 6:** Implementa middleware, rotte, layout con intestazione sticky, mega menu a tessere (telefono: elenco con miniature), selettore lingua e piè di pagina con i dati societari (spec §5, nessun orario).
- [ ] **Step 7:** Esegui; atteso: PASS.
- [ ] **Step 8: Commit** `feat: layout pubblico, mega menu e routing bilingue`.

### Task 6: Catalogo pubblico: elenco, filtri, ricerca e scheda

**Files:**
- Create: `app/Http/Controllers/CatalogController.php`, `resources/views/catalog/{index,show}.blade.php`, `tests/Feature/CatalogPagesTest.php`

**Interfaces:**
- Consumes: `Product::visibleIn`, `Category`.
- Produces: rotte `/{locale}/catalogo` (it) e `/{locale}/catalogue` (en) con query `categoria`, `titolo`, `maglia_min`, `maglia_max`, `lunghezza_min`, `lunghezza_max`, `peso_min`, `peso_max`, `q`; scheda `/{locale}/prodotto/{slug}`; pulsante "Richiedi preventivo" verso la pagina `area-clienti`.

- [ ] **Step 1: Test `test_filters_combine_and_return_only_matching_products`**.
- [ ] **Step 2: Test `test_empty_filter_result_shows_message_not_error`** con valori assurdi (`peso_min=999999`, `maglia_min=abc`).
- [ ] **Step 3: Test `test_text_search_matches_code_and_name_in_current_language`**.
- [ ] **Step 4: Test `test_product_page_shows_attributes_titles_and_no_price`**: asserisce peso, dimensioni, misura maglia, opzioni AU 750/585/375 e l'assenza di `€` e "prezzo".
- [ ] **Step 5: Test `test_english_page_for_untranslated_product_renders_italian_text`**.
- [ ] **Step 6:** Esegui; atteso: FAIL.
- [ ] **Step 7:** Implementa controller con validazione dei parametri (valori non numerici ignorati), viste e filtri; paginazione a 24.
- [ ] **Step 8:** Esegui; atteso: PASS.
- [ ] **Step 9: Commit** `feat: catalogo pubblico con filtri e schede`.

### Task 7: Pagine istituzionali iniziali

**Files:**
- Create: `database/seeders/PageSeeder.php`, `resources/views/pages/show.blade.php`, `tests/Feature/InstitutionalPagesTest.php`

**Interfaces:**
- Consumes: `Page`, layout del Task 5.
- Produces: pagine IT/EN con slug `home`, `azienda`, `manifattura`, `fusione`, `preparazione`, `saldatura`, `pulitura-e-lucidatura`, `galvanica`, `qualita`, `servizi`, `contatti`, `area-clienti`, `privacy-policy`.

- [ ] **Step 1: Test `test_all_institutional_pages_exist_in_both_languages_and_return_200`**.
- [ ] **Step 2: Test `test_area_clienti_page_explains_access_and_links_to_contacts`**.
- [ ] **Step 3: Test `test_footer_and_contact_page_show_company_data_without_opening_hours`**: contiene "Via Baracca" e "01744400514"; non contiene "orari" né "Lun".
- [ ] **Step 4:** Esegui; atteso: FAIL.
- [ ] **Step 5:** Scrivi il seeder con i testi del sito attuale (rilevati dall'importer del Task 8 dove disponibili) e l'impaginazione Natural Luxury 03 usando i componenti del design system.
- [ ] **Step 6:** Esegui; atteso: PASS.
- [ ] **Step 7: Commit** `feat: pagine istituzionali iniziali`.

### Task 8: Importer dal sito pubblico

**Files:**
- Create: `app/Importer/{SiteClient,CatalogParser,PageParser,ImportRun}.php`, `app/Console/Commands/ImportSite.php`, `tests/Fixtures/legacy/*.html` (campioni salvati: scheda prodotto, categoria, sitemap), `tests/Feature/ImporterTest.php`

**Interfaces:**
- Consumes: modelli del Task 2, `Page` del Task 4.
- Produces: `php artisan site:import {--only=catalog|pages|redirects} {--resume}`; `SiteClient::get(string $url): string` (ritentativi con attesa crescente, timeout 40 s, massimo 4 tentativi); `CatalogParser::parseProduct(string $html): array{code,name,category_slug,weight_g,length_cm,link_mm,titles:array,images:array}`; tabella `import_runs{url, status, error}` per riprendere.

- [ ] **Step 1: Test `test_parse_product_extracts_corda_100_values`**: sul campione di `/it/prodotto/corda-100/`: nome "Corda 100", peso 8.00, lunghezza 19.00, maglia 5.40, titoli `['AG','AU 750','AU 585','AU 375']`.
- [ ] **Step 2: Test `test_price_is_never_imported`**: nessun valore o testo "Da definire" salvato.
- [ ] **Step 3: Test `test_import_is_idempotent`**: due esecuzioni → stesso numero di prodotti e immagini.
- [ ] **Step 4: Test `test_import_resumes_after_failure`**: la richiesta del 3° URL fallisce; rilancio con `--resume` completa gli altri senza rifare il 1° e il 2°.
- [ ] **Step 5: Test `test_client_retries_dropped_connections`**: due errori di connessione poi successo → ritorna il corpo.
- [ ] **Step 6:** Esegui; atteso: FAIL.
- [ ] **Step 7:** Implementa. Fonti: `wp-sitemap.xml` (sitemap `product-sitemap.xml`, `page-sitemap.xml`), pagine `/it/prodotto/<slug>/` e `/en/prodotto/<slug>/`, categorie `/it/categoria-prodotto/<slug>/`. Salva le immagini in `storage/app/public/products/`. Produce un rapporto: conteggi per categoria e lingua, prodotti con campi mancanti.
- [ ] **Step 8:** Esegui; atteso: PASS.
- [ ] **Step 9: Commit** `feat: importer dal sito pubblico`.
- [ ] **Step 10: Esecuzione reale e verifica:** `php artisan site:import`; atteso: circa 378 prodotti per lingua e 23 categorie; mostra al cliente il rapporto e 10 schede a campione.

### Task 9: Modulo contatti

**Files:**
- Create: `app/Http/Controllers/ContactController.php`, `app/Mail/ContactMessage.php`, `tests/Feature/ContactFormTest.php`

**Interfaces:**
- Produces: `POST /{locale}/contatti` con `nome`, `azienda`, `email`, `telefono`, `messaggio`, `consenso`, campo trappola `sito_web`; invio a `info@chianucci.it`.

- [ ] **Step 1: Test `test_valid_submission_sends_mail_to_info`**.
- [ ] **Step 2: Test `test_missing_consent_or_invalid_email_is_rejected`**.
- [ ] **Step 3: Test `test_honeypot_filled_is_silently_discarded`** (risposta 200, nessuna email).
- [ ] **Step 4: Test `test_form_is_rate_limited`**: la 6ª richiesta in un minuto riceve 429.
- [ ] **Step 5:** Esegui; atteso: FAIL.
- [ ] **Step 6:** Implementa con validazione, trappola, limite di frequenza e mail.
- [ ] **Step 7:** Esegui; atteso: PASS.
- [ ] **Step 8: Commit** `feat: modulo contatti`.

### Task 10: SEO, redirect e sitemap

**Files:**
- Create: `app/Models/Redirect.php`, `app/Http/Middleware/LegacyRedirect.php`, `app/Http/Controllers/SitemapController.php`, `app/Filament/Resources/RedirectResource.php`, `tests/Feature/{RedirectTest,SeoTest}.php`

**Interfaces:**
- Consumes: importer (Task 8, opzione `--only=redirects`), rotte dei Task 5, 6.
- Produces: tabella `redirects{from_path unique, to_path}`; middleware che normalizza (minuscole, senza slash finale, senza query) prima del confronto e risponde 301 diretto; `GET /sitemap.xml`; `<link rel="alternate" hreflang>` in ogni pagina; dati strutturati JSON-LD `Organization` e `Product`.

- [ ] **Step 1: Test `test_legacy_url_with_trailing_slash_uppercase_and_utm_redirects_once`**: `/IT/Catalogo/?utm=x` → un solo 301 verso la destinazione finale.
- [ ] **Step 2: Test `test_no_redirect_chains_exist`**: nessuna destinazione è a sua volta un `from_path`.
- [ ] **Step 3: Test `test_unknown_url_returns_404_not_500`**.
- [ ] **Step 4: Test `test_sitemap_lists_pages_categories_and_products_in_both_languages`**.
- [ ] **Step 5: Test `test_pages_have_title_description_hreflang_and_canonical`**.
- [ ] **Step 6: Test `test_product_json_ld_has_no_offer_or_price`**.
- [ ] **Step 7:** Esegui; atteso: FAIL.
- [ ] **Step 8:** Implementa; `site:import --only=redirects` genera la mappa dai vecchi URL (pagine, lavorazioni, azienda, contatti, categorie, prodotti in IT ed EN) ai nuovi.
- [ ] **Step 9:** Esegui; atteso: PASS.
- [ ] **Step 10: Commit** `feat: SEO, redirect e sitemap`.

### Task 11: Sicurezza, privacy e cookie

**Files:**
- Create: `app/Http/Middleware/SecurityHeaders.php`, `resources/views/components/cookie-banner.blade.php`, `tests/Feature/SecurityTest.php`

**Interfaces:**
- Produces: intestazioni `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Content-Security-Policy` (senza script di terze parti non dichiarati); limite ai tentativi di accesso admin (5 al minuto); banner cookie con consenso salvato.

- [ ] **Step 1: Test `test_security_headers_are_present_on_public_pages`**.
- [ ] **Step 2: Test `test_admin_login_is_throttled_after_five_attempts`**.
- [ ] **Step 3: Test `test_no_third_party_scripts_load_before_cookie_consent`**.
- [ ] **Step 4:** Esegui; atteso: FAIL.
- [ ] **Step 5:** Implementa middleware, limite e banner (testo giuridico segnaposto, da confermare dall'azienda).
- [ ] **Step 6:** Esegui; atteso: PASS.
- [ ] **Step 7: Commit** `feat: sicurezza, privacy e cookie`.

### Task 12: Pacchetto di pubblicazione e guida

**Files:**
- Create: `app/Console/Commands/BuildPackage.php`, `docs/guida-pubblicazione.md`, `tests/Feature/PackageTest.php`

**Interfaces:**
- Produces: `php artisan site:package` → `dist/chianucci-<data>.zip` con `app/` (codice e `vendor/`), `public_html/` (contenuto di `public/` con `index.php` che punta a `../app`), `.env.example`, `install.php` monouso che esegue le migrazioni e si cancella; guida con: caricamento da cPanel, impostazione cartella principale del dominio, prova su sottodominio, ritorno alla versione precedente, elenco da includere nei backup (database, `storage/app/public`, `.env`).

- [ ] **Step 1: Test `test_package_contains_vendor_built_assets_and_no_dev_files`**: lo zip ha `vendor/autoload.php` e `public_html/build/`, non ha `tests/`, `.git/`, `.env`.
- [ ] **Step 2: Test `test_package_does_not_contain_original_logo_with_embedded_image`**.
- [ ] **Step 3:** Esegui; atteso: FAIL.
- [ ] **Step 4:** Implementa il comando, compila gli asset in produzione, scrivi la guida passo passo in italiano.
- [ ] **Step 5:** Esegui; atteso: PASS.
- [ ] **Step 6: Commit** `feat: pacchetto di pubblicazione e guida`.

### Task 13: Verifica finale

**Files:**
- Create: `tests/Feature/SmokeTest.php`, `docs/verifica-pre-rilascio.md`

- [ ] **Step 1: Test `test_every_sitemap_url_returns_200`** sul database importato.
- [ ] **Step 2: Test `test_every_legacy_url_resolves_with_a_single_301_to_a_200`**.
- [ ] **Step 3:** Esegui l'intera suite: `./vendor/bin/pest`; atteso: tutto verde.
- [ ] **Step 4:** Con Playwright, screenshot di Home, Catalogo, scheda e mega menu a 375, 768 e 1280 px; confrontali con i mockup e con la tavola Natural Luxury 03 e mostrali al cliente.
- [ ] **Step 5:** Compila `docs/verifica-pre-rilascio.md` (velocità, accessibilità di base, link, redirect) con i risultati.
- [ ] **Step 6: Commit** `chore: verifica pre-rilascio`.
