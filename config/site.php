<?php

return [
    'locales' => ['it', 'en'],

    /** chiave pagina => slug per lingua ("home" è la radice della lingua). */
    'pages' => [
        'home' => ['it' => 'home', 'en' => 'home'],
        'azienda' => ['it' => 'azienda', 'en' => 'company'],
        'manifattura' => ['it' => 'manifattura', 'en' => 'manufacturing'],
        'qualita' => ['it' => 'qualita', 'en' => 'quality'],
        'servizi' => ['it' => 'servizi', 'en' => 'services'],
        'contatti' => ['it' => 'contatti', 'en' => 'contacts'],
        'area-clienti' => ['it' => 'area-clienti', 'en' => 'customer-area'],
        'privacy' => ['it' => 'privacy-policy', 'en' => 'privacy-policy'],
    ],

    'catalog_slug' => ['it' => 'catalogo', 'en' => 'catalogue'],

    /** Voci del menu principale, nell'ordine in cui compaiono. */
    'menu' => ['azienda', 'manifattura', 'catalogo', 'qualita', 'servizi', 'contatti'],

    'company' => [
        'name' => 'CHIANUCCI MAURIZIO s.r.l.',
        'address' => 'Via Baracca, 7 - 52100 Arezzo (Italy)',
        'phones' => ['+39 0575 324.101', '+39 0575 355.365'],
        'email' => 'info@chianucci.it',
        'pec' => 'chianuccimauriziosrl@pec.it',
        'vat' => '01744400514',
        'rea' => '136050',
        'capital' => '€ 25.000,00 i.v.',
        'register' => 'Iscr. Reg. Ditte di Arezzo',
    ],
];
