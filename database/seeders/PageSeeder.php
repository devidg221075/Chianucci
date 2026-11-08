<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Pagine istituzionali iniziali (bozze basate sui mockup Natural Luxury 03).
 * I testi definitivi si modificano dal pannello; l'importer può sostituirli con quelli del sito attuale.
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

    private function section(string $eyebrow, string $title, string $body, string $extra = ''): string
    {
        return <<<HTML
<section class="py-14 px-6"><div class="mx-auto max-w-5xl">
<p class="text-xs font-semibold uppercase tracking-widest text-oro">{$eyebrow}</p>
<h1 class="mt-2 text-4xl font-bold">{$title}</h1>
<p class="mt-4 text-lg max-w-3xl">{$body}</p>{$extra}
</div></section>
HTML;
    }

    private function block(string $title, string $body): string
    {
        return '<section class="py-10 px-6"><div class="mx-auto max-w-5xl"><h2 class="text-2xl font-bold">'.$title.'</h2><p class="mt-3 max-w-3xl">'.$body.'</p></div></section>';
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
            ? '<p class="mt-8 flex gap-4"><a href="'.$cat.'" class="btn-primario">Sfoglia il catalogo</a><a href="'.$contact.'" class="btn-secondario">Contattaci</a></p>'
            : '<p class="mt-8 flex gap-4"><a href="'.$cat.'" class="btn-primario">Browse the catalogue</a><a href="'.$contact.'" class="btn-secondario">Contact us</a></p>';

        $processes = [
            'fusione' => $it ? ['Fusione', 'La fase in cui il metallo prezioso prende forma, con controllo della lega e della qualità fin dall\'inizio.'] : ['Fusion', 'The stage where the precious metal takes shape, with control of the alloy and quality from the very start.'],
            'preparazione' => $it ? ['Preparazione', 'La preparazione dei semilavorati e dei componenti che daranno origine alle catene.'] : ['Preparation', 'Preparation of the semi-finished parts and components that will become the chains.'],
            'saldatura' => $it ? ['Saldatura', 'La saldatura delle maglie, eseguita con precisione per garantire continuità e resistenza.'] : ['Welding', 'Welding of the links, carried out with precision to ensure continuity and strength.'],
            'pulitura' => $it ? ['Pulitura e lucidatura', 'La finitura che dà alla catena la sua brillantezza, con cura artigianale.'] : ['Cleaning and polishing', 'The finishing step that gives the chain its brilliance, with artisanal care.'],
            'galvanica' => $it ? ['Galvanica', 'I trattamenti galvanici che completano il prodotto con le finiture richieste.'] : ['Galvanic treatment', 'Galvanic treatments that complete the product with the required finishes.'],
        ];

        $pages = [];

        $pages['home'] = [
            'title' => $it ? 'Manifattura orafa dal 1973' : 'Goldsmith manufacturer since 1973',
            'meta' => $it ? 'CHIANUCCI MAURIZIO: catene, componenti e gioielli in oro e argento per professionisti del settore orafo, ad Arezzo dal 1973.' : 'CHIANUCCI MAURIZIO: gold and silver chains, components and jewellery for goldsmith professionals, in Arezzo since 1973.',
            'html' => $this->section(
                $it ? 'Manifattura orafa dal 1973' : 'Goldsmith manufacturer since 1973',
                $it ? 'L\'eccellenza prende forma.' : 'Excellence takes shape.',
                $it ? 'Produzione di catene in oro e argento. Tradizione artigianale, qualità e competenza al servizio dei professionisti del settore.' : 'Production of gold and silver chains. Artisan tradition, quality and expertise at the service of industry professionals.',
                $cta
            )
            .'<section class="py-10 px-6 bg-bianco"><div class="mx-auto max-w-5xl grid gap-6 md:grid-cols-4 text-center">'
            .'<div><h3 class="text-base font-semibold">'.($it ? 'Manifattura italiana' : 'Italian manufacturing').'</h3></div>'
            .'<div><h3 class="text-base font-semibold">'.($it ? 'Competenza produttiva' : 'Production expertise').'</h3></div>'
            .'<div><h3 class="text-base font-semibold">'.($it ? 'Qualità certificata' : 'Certified quality').'</h3></div>'
            .'<div><h3 class="text-base font-semibold">'.($it ? 'Partner B2B' : 'B2B partners').'</h3></div></div></section>'
            .$this->block($it ? 'Dal 1973, ad Arezzo' : 'Since 1973, in Arezzo', $it ? 'Oltre cinquant\'anni di esperienza nella manifattura orafa italiana, con uno sguardo rivolto al futuro, ai nuovi materiali e alle esigenze dei nostri partner.' : 'Over fifty years of experience in Italian goldsmith manufacturing, with an eye on the future, new materials and our partners\' needs.')
            .'<section class="py-10 px-6 bg-viola text-avorio"><div class="mx-auto max-w-5xl"><h2 class="text-2xl font-bold !text-avorio">'.($it ? 'Area Clienti' : 'Customer Area').'</h2><p class="mt-3 max-w-3xl">'.($it ? 'Richiedi l\'accesso per inviare richieste di preventivo e consultare il loro stato.' : 'Request access to send quote requests and follow their status.').'</p><p class="mt-4"><a href="'.$area.'" class="btn-secondario !text-avorio">'.($it ? 'Scopri l\'Area Clienti' : 'Discover the Customer Area').'</a></p></div></section>',
        ];

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
            $links .= '<li><a class="underline text-viola" href="'.($it ? '/it/' : '/en/').config("site.pages.$k.$l").'">'.$processes[$k][0].'</a></li>';
        }
        $pages['manifattura'] = [
            'title' => $it ? 'Manifattura e lavorazioni' : 'Manufacturing and processes',
            'meta' => $it ? 'Dalla materia alla forma perfetta: le lavorazioni di CHIANUCCI MAURIZIO, dalla fusione alla galvanica.' : 'From raw material to perfect shape: the processes at CHIANUCCI MAURIZIO, from fusion to galvanic treatment.',
            'html' => $this->section($it ? 'Manifattura e lavorazioni' : 'Manufacturing and processes', $it ? 'Dalla materia alla forma perfetta' : 'From raw material to perfect shape',
                $it ? 'Tecnologia, esperienza e cura artigianale per la produzione di catene e componenti di alta qualità.' : 'Technology, experience and artisan care for the production of high-quality chains and components.',
                '<ul class="mt-6 list-disc pl-6 space-y-1">'.$links.'</ul>'),
        ];

        foreach ($processes as $k => [$name, $text]) {
            $pages[$k] = [
                'title' => $name,
                'meta' => $text,
                'html' => $this->section($it ? 'Le lavorazioni' : 'Our processes', $name, $text,
                    '<p class="mt-6"><a class="btn-secondario" href="'.($it ? '/it/manifattura' : '/en/manufacturing').'">'.($it ? 'Tutte le lavorazioni' : 'All processes').'</a></p>'),
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
                '<div class="mt-8 grid gap-6 md:grid-cols-2"><div><h2 class="text-xl font-bold">'.$c['name'].'</h2><p class="mt-2">'.$c['address'].'</p><p>'.$phones.'</p><p>'.$c['email'].'</p><p>PEC: '.$c['pec'].'</p><p class="mt-2">P.IVA '.$c['vat'].' - REA '.$c['rea'].'</p></div></div>'),
        ];

        $pages['area-clienti'] = [
            'title' => $it ? 'Area Clienti' : 'Customer Area',
            'meta' => $it ? 'Area riservata ai clienti professionali di CHIANUCCI MAURIZIO: preventivi, storico e stato delle richieste.' : 'Reserved area for the professional customers of CHIANUCCI MAURIZIO: quotes, history and status of requests.',
            'html' => $this->section($it ? 'Area riservata' : 'Reserved area', $it ? 'Area Clienti professionali' : 'Professional Customer Area',
                $it ? 'Il catalogo è consultabile da tutti. Le richieste di preventivo e gli ordini sono riservati ai clienti registrati e approvati da Chianucci. L\'accesso online è in arrivo: nel frattempo contattaci per richiedere un preventivo.' : 'The catalogue is open to everyone. Quote requests and orders are reserved for customers registered and approved by Chianucci. Online access is coming soon: in the meantime, contact us to request a quote.',
                '<p class="mt-8"><a href="'.$contact.'" class="btn-primario">'.($it ? 'Contattaci' : 'Contact us').'</a></p>'),
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
