import grapesjs from 'grapesjs';
import it from 'grapesjs/locale/it';
import 'grapesjs/dist/css/grapes.min.css';
import 'font-awesome/css/font-awesome.min.css';

document.addEventListener('alpine:init', () => {
    window.Alpine.data('pageEditor', ({ html, css, siteCss }) => ({
        html,
        css,
        editor: null,
        init() {
            this.editor = grapesjs.init({
                container: this.$refs.canvas,
                fromElement: false,
                height: '75vh',
                storageManager: false,
                cssIcons: '', // niente Font Awesome da CDN: arriva dal pacchetto locale
                telemetry: false, // niente dati inviati a servizi esterni
                i18n: { locale: 'it', detectLocale: false, localeFallback: 'en', messages: { it } }, // comandi dell'editor in italiano
                avoidInlineStyle: true,
                components: this.html || '',
                style: this.css || '',
                blockManager: { appendTo: this.$refs.blocks },
                canvas: { styles: siteCss ? [siteCss] : [] },
            });

            const bm = this.editor.Blocks;
            bm.add('sezione', { label: 'Sezione', category: 'Struttura', content: '<section class="sezione"><div class="contenitore"><h2>Titolo sezione</h2><p>Testo della sezione.</p></div></section>' });
            bm.add('griglia', { label: 'Griglia 3 colonne', category: 'Struttura', content: '<div class="contenitore"><div class="griglia griglia-alto"><div class="col-terzo">Colonna 1</div><div class="col-terzo">Colonna 2</div><div class="col-terzo">Colonna 3</div></div></div>' });
            bm.add('titolo', { label: 'Titolo', category: 'Testo', content: '<h2>Nuovo titolo</h2>' });
            bm.add('testo', { label: 'Testo', category: 'Testo', content: '<p>Scrivi qui il testo.</p>' });
            bm.add('immagine', { label: 'Immagine', category: 'Media', select: true, content: { type: 'image' }, activate: true });
            bm.add('tessera', { label: 'Tessera', category: 'Contenuti', content: '<div class="scheda"><span class="scheda-testo">Titolo<span class="block testo-piccolo font-normal">Descrizione.</span></span></div>' });
            bm.add('pulsante', { label: 'Pulsante', category: 'Contenuti', content: '<a href="#" class="btn-primario">Scopri</a>' });

            const sync = () => {
                this.html = this.editor.getHtml({ cleanId: true }).replace(/^<body[^>]*>|<\/body>$/g, '');
                this.css = this.editor.getCss();
            };
            this.editor.on('update', sync);
        },
    }));
});
