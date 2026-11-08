<?php

return [
    'menu' => [
        'azienda' => 'Company', 'manifattura' => 'Manufacturing', 'catalogo' => 'Catalogue',
        'qualita' => 'Quality', 'servizi' => 'Services', 'contatti' => 'Contact',
    ],
    'area_clienti' => 'Customer Area',
    'catalog_all' => 'Whole catalogue',
    'language' => 'Language',

    'catalog' => [
        'title' => 'Catalogue', 'search' => 'Search by code or name', 'category' => 'Category', 'all_categories' => 'All categories',
        'metal' => 'Metal title', 'all_metals' => 'All', 'link' => 'Link size (mm)', 'length' => 'Length (cm)', 'weight' => 'Weight (g)',
        'min' => 'from', 'max' => 'to', 'apply' => 'Filter', 'reset' => 'Reset filters', 'empty' => 'No products found with these filters.',
        'code' => 'Code', 'details' => 'Details', 'request_quote' => 'Request a quote', 'quote_note' => 'Quotes and orders are reserved for registered, approved customers.',
        'seo_description' => 'Catalogue of gold and silver chains, components and jewellery by CHIANUCCI MAURIZIO: browse the products and request a quote.',
        'product_description' => ":name (code :code): product from the CHIANUCCI MAURIZIO catalogue, goldsmith manufacturer in Arezzo.",
        'back' => 'Back to the catalogue', 'titles' => 'Available titles',
    ],

    'contact' => [
        'name' => 'Full name', 'company' => 'Company', 'email' => 'Email', 'phone' => 'Phone', 'message' => 'Message',
        'consent' => 'I accept the processing of my personal data according to the Privacy Policy.', 'send' => 'Send request',
        'sent' => 'Thank you, your request has been sent. We will reply as soon as possible.',
        'errors' => [
            'required' => 'Please fill in this field.', 'email' => 'Please enter a valid email address.',
            'accepted' => 'To send the request you must accept the data processing.',
            'max' => 'The text is too long.', 'regex' => 'The text contains invalid characters.',
            'string' => 'The value is not valid.',
        ],
    ],
];
