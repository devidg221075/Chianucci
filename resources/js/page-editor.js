import grapesjs from 'grapesjs';
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
                avoidInlineStyle: true,
                components: this.html || '',
                style: this.css || '',
                blockManager: { appendTo: this.$refs.blocks },
                canvas: { styles: siteCss ? [siteCss] : [] },
            });

            const bm = this.editor.Blocks;
            bm.add('sezione', { label: 'Sezione', category: 'Struttura', content: '<section class="py-12 px-6"><div class="mx-auto max-w-5xl"><h2>Titolo sezione</h2><p>Testo della sezione.</p></div></section>' });
            bm.add('griglia', { label: 'Griglia 3 colonne', category: 'Struttura', content: '<div class="grid grid-cols-1 md:grid-cols-3 gap-6 py-8"><div>Colonna 1</div><div>Colonna 2</div><div>Colonna 3</div></div>' });
            bm.add('titolo', { label: 'Titolo', category: 'Testo', content: '<h2>Nuovo titolo</h2>' });
            bm.add('testo', { label: 'Testo', category: 'Testo', content: '<p>Scrivi qui il testo.</p>' });
            bm.add('immagine', { label: 'Immagine', category: 'Media', select: true, content: { type: 'image' }, activate: true });
            bm.add('tessera', { label: 'Tessera', category: 'Contenuti', content: '<div class="p-6 bg-white border border-oro/40"><h3>Titolo</h3><p>Descrizione.</p></div>' });
            bm.add('pulsante', { label: 'Pulsante', category: 'Contenuti', content: '<a href="#" class="btn-primario">Scopri</a>' });

            const sync = () => {
                this.html = this.editor.getHtml({ cleanId: true }).replace(/^<body[^>]*>|<\/body>$/g, '');
                this.css = this.editor.getCss();
            };
            this.editor.on('update', sync);
        },
    }));
});
