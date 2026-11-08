<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Pagine istituzionali iniziali (bozze basate sui mockup Natural Luxury 03).
 * I testi definitivi si modificano dal pannello; l'importer può sostituirli con quelli del sito attuale.
 * Il layout usa solo le classi del design system (contenitore, griglia, sezione, occhiello, lead...).
 */
class PageSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['it', 'en'] as $locale) {
            foreach ($this->pages($locale) as $key => $data) {
                $slug = config("site.pages.$key.$locale");
                Page::updateOrCreate(
                    ['slug' => $slug, 'locale' => $locale],
                    [
                        'title' => $data['title'],
                        'meta_title' => $data['title'],
                        'meta_description' => $data['meta'],
                        'html' => $data['html'],
                        'css' => '',
                    ]
                );
            }
        }
    }

    /** Intestazione di pagina: occhiello, H1, testo introduttivo ed eventuale contenuto extra. */
    private function section(string $eyebrow, string $title, string $body, string $extra = ''): string
    {
        return '<section class="sezione"><div class="contenitore"><div class="griglia griglia-alto"><div class="col-testo">'
            .'<p class="occhiello">'.$eyebrow.'</p><h1>'.$title.'</h1><p class="lead">'.$body.'</p>'.$extra
            .'</div></div></div></section>';
    }

    private function block(string $title, string $body): string
    {
        return '<section class="sezione sezione-chiara"><div class="contenitore"><div class="griglia griglia-alto"><div class="col-testo">'
            .'<h2>'.$title.'</h2><p>'.$body.'</p></div></div></div></section>';
    }

    private function actions(string $primaryLabel, string $primaryHref, string $secondaryLabel, string $secondaryHref): string
    {
        return '<p class="azioni"><a href="'.$primaryHref.'" class="btn-primario">'.$primaryLabel.'</a><a href="'.$secondaryHref.'" class="btn-secondario">'.$secondaryLabel.'</a></p>';
    }

    /** Home sul modello della tavola Natural Luxury 03; le foto sono quelle del vecchio sito (segnaposto da confermare). */
    private function home(string $l): array
    {
        $it = $l === 'it';
        $u = fn (string $key) => $key === 'catalogo' ? ($it ? '/it/catalogo' : '/en/catalogue') : "/$l/".config("site.pages.$key.$l");
        $t = fn (string $a, string $b) => $it ? $a : $b;

        $tile = fn (string $img, string $alt, string $name, string $href) => '<a href="'.$href.'" class="scheda col-terzo">'
            .'<img src="/images/home/'.$img.'.jpg" alt="'.$alt.'" class="foto-card"><span class="scheda-testo">'.$name.'</span></a>';

        $value = fn (string $icon, string $title, string $text) => '<div class="col-quarto-testo flex items-center gap-4"><img src="/images/icons/'.$icon.'.svg" alt="" class="h-12 w-12 shrink-0">'
            .'<div><h3>'.$title.'</h3><p class="testo-piccolo">'.$text.'</p></div></div>';

        $html = '<section class="hero">'
            .'<img src="/images/home/hero.jpg" alt="'.$t('Catene d\'oro in lavorazione presso Chianucci', 'Gold chains being made at Chianucci').'" class="foto-hero">'
            .'<div class="contenitore"><div class="griglia"><div class="col-testo">'
            .'<p class="occhiello">'.$t('Manifattura orafa dal 1973', 'Goldsmith manufacturer since 1973').'</p>'
            .'<h1>'.$t('L\'arte della manifattura orafa.', 'The art of goldsmith manufacturing.').'</h1>'
            .'<p class="lead">'.$t('Produzione di catene in oro e argento per aziende, grossisti e professionisti del settore. Qualità, competenza artigianale e affidabilità, dal 1973.', 'Gold and silver chains for companies, wholesalers and industry professionals. Quality, artisan expertise and reliability, since 1973.').'</p>'
            .'<p class="azioni"><a href="'.$u('area-clienti').'" class="btn-oro">'.$t('Richiedi un preventivo', 'Request a quote').'</a>'
            .'<a href="'.$u('catalogo').'" class="btn-chiaro">'.$t('Catalogo professionale', 'Professional catalogue').'</a></p>'
            .'</div></div></div></section>'

            .'<section class="sezione sezione-chiara"><div class="contenitore"><div class="griglia griglia-alto">'
            .$value('diamante', 'Made in Italy', $t('Produzione interamente italiana', 'Entirely Italian production'))
            .$value('ingranaggio', $t('Competenza artigianale', 'Artisan expertise'), $t('Know-how orafo e tecnologie evolute', 'Goldsmith know-how and advanced technology'))
            .$value('medaglia', $t('Qualità certificata', 'Certified quality'), $t('Controlli rigorosi in ogni fase', 'Rigorous checks at every stage'))
            .$value('persone', 'Partner B2B', $t('Soluzioni su misura per aziende, grossisti e marchi', 'Tailor-made solutions for companies, wholesalers and brands'))
            .'</div></div></section>'

            .'<section class="sezione"><div class="contenitore"><div class="griglia">'
            .'<img src="/images/home/atelier.jpg" alt="'.$t('Mani di un\'orafa al lavoro su una catena d\'argento', 'A goldsmith\'s hands working on a silver chain').'" class="foto-sezione col-meta">'
            .'<div class="col-meta"><p class="occhiello">'.$t('L\'atelier Chianucci', 'The Chianucci atelier').'</p>'
            .'<h2>'.$t('Competenza artigianale, precisione industriale.', 'Artisan expertise, industrial precision.').'</h2>'
            .'<p>'.$t('Un atelier italiano dove l\'esperienza orafa si unisce a tecnologie avanzate per realizzare catene e componenti di alta qualità, con la flessibilità e l\'affidabilità richieste dal mercato internazionale.', 'An Italian atelier where goldsmith experience meets advanced technology to make high-quality chains and components, with the flexibility and reliability the international market requires.').'</p>'
            .'<p class="azioni"><a href="'.$u('manifattura').'" class="btn-secondario">'.$t('Scopri la manifattura', 'Discover our manufacturing').'</a></p></div></div></div></section>'

            .'<section class="sezione sezione-chiara"><div class="contenitore"><div class="griglia griglia-alto">'
            .'<div class="col-intero"><p class="occhiello">'.$t('Le lavorazioni', 'Our processes').'</p>'
            .'<h2>'.$t('Un processo completo, una qualità senza compromessi.', 'A complete process, uncompromising quality.').'</h2></div>'
            .$tile('preparazione', $t('Macchinario per la preparazione dei componenti', 'Machinery preparing components'), $t('Preparazione', 'Preparation'), $u('preparazione'))
            .$tile('saldatura', $t('Saldatura a fiamma di una catena', 'Flame welding of a chain'), $t('Saldatura', 'Welding'), $u('saldatura'))
            .$tile('pulitura', $t('Pulitura e lucidatura di un gioiello', 'Cleaning and polishing a piece'), $t('Pulitura e lucidatura', 'Cleaning and polishing'), $u('pulitura'))
            .'</div></div></section>'

            .'<section class="sezione"><div class="contenitore"><div class="griglia">'
            .'<div class="col-meta"><p class="occhiello">'.$t('La nostra storia', 'Our story').'</p>'
            .'<h2>'.$t('Dal 1973, tradizione, innovazione e affidabilità.', 'Since 1973, tradition, innovation and reliability.').'</h2>'
            .'<p>'.$t('Oltre cinquant\'anni di esperienza nella manifattura orafa italiana, con uno sguardo rivolto al futuro, ai nuovi materiali, alla sostenibilità e alle esigenze dei nostri partner. Siamo ad Arezzo, cuore di una storica tradizione orafa.', 'Over fifty years of experience in Italian goldsmith manufacturing, with an eye on the future, new materials, sustainability and our partners\' needs. We are in Arezzo, the heart of a historic goldsmith tradition.').'</p>'
            .'<p class="azioni"><a href="'.$u('azienda').'" class="btn-secondario">'.$t('Scopri la nostra storia', 'Discover our story').'</a></p></div>'
            .'<img src="/images/home/squadra.jpg" alt="'.$t('Il team di Chianucci in laboratorio', 'The Chianucci team in the workshop').'" class="foto-sezione col-meta"></div></div></section>'

            .'<div id="categorie-home"></div>'

            .'<section class="sezione sezione-scura"><div class="contenitore"><div class="griglia">'
            .'<div class="col-testo"><p class="occhiello">'.$t('Area riservata B2B', 'Reserved B2B area').'</p>'
            .'<h2>'.$t('Catalogo professionale', 'Professional catalogue').'</h2>'
            .'<p>'.$t('Accedi alla nostra selezione completa di prodotti, schede tecniche e soluzioni personalizzate. Preventivi e ordini sono riservati ai clienti registrati e approvati.', 'Access our full selection of products, technical sheets and tailored solutions. Quotes and orders are reserved for registered, approved customers.').'</p></div>'
            .'<p class="col-quarto-testo"><a href="'.$u('area-clienti').'" class="btn-oro">'.$t('Richiedi l\'accesso', 'Request access').'</a></p>'
            .'</div></div></section>';

        return [
            'title' => $t('Manifattura orafa dal 1973', 'Goldsmith manufacturer since 1973'),
            'meta' => $t('CHIANUCCI MAURIZIO: catene, componenti e gioielli in oro e argento per professionisti del settore orafo, ad Arezzo dal 1973.', 'CHIANUCCI MAURIZIO: gold and silver chains, components and jewellery for goldsmith professionals, in Arezzo since 1973.'),
            'html' => $html,
        ];
    }

    private function pages(string $l): array
    {
        $it = $l === 'it';
        $cat = $it ? '/it/catalogo' : '/en/catalogue';
        $contact = $it ? '/it/contatti' : '/en/contacts';
        $area = $it ? '/it/area-clienti' : '/en/customer-area';
        $c = config('site.company');
        $phones = implode(' - ', $c['phones']);

        $cta = $it
            ? $this->actions('Sfoglia il catalogo', $cat, 'Contattaci', $contact)
            : $this->actions('Browse the catalogue', $cat, 'Contact us', $contact);

        $processes = [
            'fusione' => $it ? ['Fusione', 'La fase in cui il metallo prezioso prende forma, con controllo della lega e della qualità fin dall\'inizio.'] : ['Fusion', 'The stage where the precious metal takes shape, with control of the alloy and quality from the very start.'],
            'preparazione' => $it ? ['Preparazione', 'La preparazione dei semilavorati e dei componenti che daranno origine alle catene.'] : ['Preparation', 'Preparation of the semi-finished parts and components that will become the chains.'],
            'saldatura' => $it ? ['Saldatura', 'La saldatura delle maglie, eseguita con precisione per garantire continuità e resistenza.'] : ['Welding', 'Welding of the links, carried out with precision to ensure continuity and strength.'],
            'pulitura' => $it ? ['Pulitura e lucidatura', 'La finitura che dà alla catena la sua brillantezza, con cura artigianale.'] : ['Cleaning and polishing', 'The finishing step that gives the chain its brilliance, with artisanal care.'],
            'galvanica' => $it ? ['Galvanica', 'I trattamenti galvanici che completano il prodotto con le finiture richieste.'] : ['Galvanic treatment', 'Galvanic treatments that complete the product with the required finishes.'],
        ];

        $pages = [];

        $pages['home'] = $this->home($l);

        $pages['azienda'] = [
            'title' => $it ? 'Azienda' : 'Company',
            'meta' => $it ? 'La storia, i valori e la competenza di CHIANUCCI MAURIZIO, manifattura orafa di Arezzo.' : 'The history, values and expertise of CHIANUCCI MAURIZIO, goldsmith manufacturer in Arezzo.',
            'html' => $this->section($it ? 'L\'azienda' : 'The company', $it ? 'Tradizione orafa, innovazione italiana' : 'Goldsmith tradition, Italian innovation',
                $it ? 'Da oltre cinquant\'anni realizziamo catene e componenti per la gioielleria di alta gamma, unendo la maestria della tradizione orafa italiana alle più avanzate tecnologie di produzione.' : 'For over fifty years we have made chains and components for high-end jewellery, combining the mastery of the Italian goldsmith tradition with the most advanced production technologies.')
                .$this->block($it ? 'La nostra storia' : 'Our story', $it ? 'Nata come realtà artigianale, Chianucci si è evoluta in un\'azienda moderna e strutturata, punto di riferimento per la produzione di catene e componenti destinati alle principali aziende del settore orafo, in Italia e all\'estero.' : 'Born as an artisan business, Chianucci has grown into a modern, structured company, a reference for the production of chains and components for the main companies in the goldsmith sector, in Italy and abroad.')
                .$this->block($it ? 'I nostri valori' : 'Our values', $it ? 'Competenza, affidabilità, collaborazione: lavoriamo al fianco dei clienti con un approccio professionale, trasparente e orientato alla qualità.' : 'Expertise, reliability, collaboration: we work alongside our customers with a professional, transparent, quality-oriented approach.'),
        ];

        $links = '';
        foreach (['fusione', 'preparazione', 'saldatura', 'pulitura', 'galvanica'] as $k) {
            $links .= '<li><a class="link-testo" href="'.($it ? '/it/' : '/en/').config("site.pages.$k.$l").'">'.$processes[$k][0].'</a></li>';
        }
        $pages['manifattura'] = [
            'title' => $it ? 'Manifattura e lavorazioni' : 'Manufacturing and processes',
            'meta' => $it ? 'Dalla materia alla forma perfetta: le lavorazioni di CHIANUCCI MAURIZIO, dalla fusione alla galvanica.' : 'From raw material to perfect shape: the processes at CHIANUCCI MAURIZIO, from fusion to galvanic treatment.',
            'html' => $this->section($it ? 'Manifattura e lavorazioni' : 'Manufacturing and processes', $it ? 'Dalla materia alla forma perfetta' : 'From raw material to perfect shape',
                $it ? 'Tecnologia, esperienza e cura artigianale per la produzione di catene e componenti di alta qualità.' : 'Technology, experience and artisan care for the production of high-quality chains and components.',
                '<ul class="mt-6 list-disc pl-6">'.$links.'</ul>'),
        ];

        foreach ($processes as $k => [$name, $text]) {
            $pages[$k] = [
                'title' => $name,
                'meta' => $text,
                'html' => $this->section($it ? 'Le lavorazioni' : 'Our processes', $name, $text,
                    '<p class="azioni"><a class="btn-secondario" href="'.($it ? '/it/manifattura' : '/en/manufacturing').'">'.($it ? 'Tutte le lavorazioni' : 'All processes').'</a></p>'),
            ];
        }

        $pages['qualita'] = [
            'title' => $it ? 'Qualità' : 'Quality',
            'meta' => $it ? 'Controllo, precisione e affidabilità: l\'impegno di CHIANUCCI MAURIZIO nella cura di ogni dettaglio.' : 'Control, precision and reliability: the commitment of CHIANUCCI MAURIZIO to every detail.',
            'html' => $this->section($it ? 'Qualità' : 'Quality', $it ? 'Controllo, precisione e affidabilità' : 'Control, precision and reliability',
                $it ? 'Un impegno costante nella cura di ogni dettaglio, per garantire catene e componenti con elevati standard qualitativi.' : 'A constant commitment to every detail, to guarantee chains and components of high quality standards.')
                .$this->block($it ? 'Verifica e cura del dettaglio' : 'Verification and attention to detail', $it ? 'Controlli dimensionali e visivi, realizzati con strumenti di precisione, per assicurare la conformità e la qualità di ogni produzione.' : 'Dimensional and visual checks, carried out with precision instruments, to ensure compliance and quality in every production run.'),
        ];

        $pages['servizi'] = [
            'title' => $it ? 'Servizi' : 'Services',
            'meta' => $it ? 'Lavorazioni su misura, supporto B2B dedicato e soluzioni personalizzate per aziende e professionisti del settore orafo.' : 'Custom processes, dedicated B2B support and tailored solutions for companies and professionals in the goldsmith sector.',
            'html' => $this->section($it ? 'Servizi' : 'Services', $it ? 'Soluzioni su misura per il tuo settore' : 'Tailor-made solutions for your sector',
                $it ? 'Lavorazioni su misura, supporto dedicato ai partner B2B e personalizzazioni pensate per aziende, grossisti e marchi.' : 'Custom processes, dedicated support for B2B partners and customisations designed for companies, wholesalers and brands.', $cta),
        ];

        $pages['contatti'] = [
            'title' => $it ? 'Contatti' : 'Contact',
            'meta' => $it ? 'Contatta CHIANUCCI MAURIZIO a Arezzo: telefono, email e richiesta di informazioni.' : 'Contact CHIANUCCI MAURIZIO in Arezzo: phone, email and information request.',
            'html' => $this->section($it ? 'Contatti' : 'Contact', $it ? 'Siamo a tua disposizione' : 'We are at your disposal',
                $it ? 'Per informazioni, richieste o collaborazioni contattaci: il nostro team è pronto a supportarti.' : 'For information, requests or collaborations, contact us: our team is ready to support you.',
                '<div class="mt-8"><h2>'.$c['name'].'</h2><p>'.$c['address'].'</p><p>'.$phones.'</p><p>'.$c['email'].'</p><p>PEC: '.$c['pec'].'</p><p>P.IVA '.$c['vat'].' - REA '.$c['rea'].'</p></div>'),
        ];

        $pages['area-clienti'] = [
            'title' => $it ? 'Area Clienti' : 'Customer Area',
            'meta' => $it ? 'Area riservata ai clienti professionali di CHIANUCCI MAURIZIO: preventivi, storico e stato delle richieste.' : 'Reserved area for the professional customers of CHIANUCCI MAURIZIO: quotes, history and status of requests.',
            'html' => $this->section($it ? 'Area riservata' : 'Reserved area', $it ? 'Area Clienti professionali' : 'Professional Customer Area',
                $it ? 'Il catalogo è consultabile da tutti. Le richieste di preventivo e gli ordini sono riservati ai clienti registrati e approvati da Chianucci. L\'accesso online è in arrivo: nel frattempo contattaci per richiedere un preventivo.' : 'The catalogue is open to everyone. Quote requests and orders are reserved for customers registered and approved by Chianucci. Online access is coming soon: in the meantime, contact us to request a quote.',
                '<p class="azioni"><a href="'.$contact.'" class="btn-primario">'.($it ? 'Contattaci' : 'Contact us').'</a></p>'),
        ];

        $pages['privacy'] = [
            'title' => 'Privacy Policy',
            'meta' => $it ? 'Informativa sul trattamento dei dati personali di CHIANUCCI MAURIZIO s.r.l.' : 'Personal data processing notice of CHIANUCCI MAURIZIO s.r.l.',
            'html' => $this->section('Privacy', 'Privacy Policy',
                $it ? 'Titolare del trattamento: '.$c['name'].', '.$c['address'].', '.$c['email'].'. Il testo completo dell\'informativa è in corso di redazione e sarà confermato dall\'azienda prima della pubblicazione.' : 'Data controller: '.$c['name'].', '.$c['address'].', '.$c['email'].'. The full notice is being drafted and will be confirmed by the company before publication.'),
        ];

        return $pages;
    }
}
