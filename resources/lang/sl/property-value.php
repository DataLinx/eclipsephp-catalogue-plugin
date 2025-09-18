<?php

return [
    'singular' => 'Vrednost lastnosti',
    'plural' => 'Vrednosti lastnosti',

    'fields' => [
        'value' => 'Vrednost',
        'info_url' => 'URL informacij',
        'image' => 'Slika',
        'sort' => 'Vrstni red',
    ],

    'sections' => [
        'value_information' => 'Informacije o vrednosti',
    ],

    'placeholders' => [
        'value' => 'Vnesite vrednost lastnosti',
        'info_url' => 'Vnesite neobvezno povezavo "več informacij"',
        'sort' => 'Vnesite vrstni red (nižje številke se prikažejo prve)',
    ],

    'help_text' => [
        'info_url' => 'Neobvezna povezava "več informacij"',
        'image' => 'Neobvezna slika za to vrednost (npr. logotip blagovne znamke)',
        'sort' => 'Nižje številke se prikažejo prve',
    ],

    'table' => [
        'columns' => [
            'value' => 'Vrednost',
            'group' => 'Skupina',
            'aliases' => 'Aliasi',
            'image' => 'Slika',
            'info_url' => 'URL informacij',
            'sort' => 'Vrstni red',
            'products_count' => 'Proizvodi',
            'created_at' => 'Ustvarjeno',
        ],
        'filters' => [
            'property' => 'Lastnost',
        ],
        'actions' => [
            'edit' => 'Uredi',
            'delete' => 'Izbriši',
            'merge' => 'Združi…',
            'group_aliases' => 'Združi (aliase)',
            'remove_from_group' => 'Odstrani iz skupine',
        ],
    ],

    'modal' => [
        'create_heading' => 'Ustvari vrednost lastnosti',
        'edit_heading' => 'Uredi vrednost lastnosti',
        'merge_heading' => 'Združi vrednost',
        'merge_from_label' => 'Združi vrednost…',
        'merge_to_label' => 'Z vrednostjo…*',
        'merge_helper' => 'To dejanje bo vsem izdelkom zamenjalo trenutno lastnost s to, označeno zgoraj. Nato bo trenutna lastnost izbrisana.',
        'merge_submit_label' => 'Združi',
        'cancel_label' => 'Prekliči',
        'merge_confirm_title' => 'Ste prepričani, da želite združiti?',
        'merge_confirm_body' => 'Vsi izdelki s trenutno vrednostjo bodo posodobljeni na izbrano vrednost. Dejanja ni mogoče razveljaviti.',
    ],

    'messages' => [
        'created' => 'Vrednost lastnosti je bila uspešno ustvarjena.',
        'updated' => 'Vrednost lastnosti je bila uspešno posodobljena.',
        'deleted' => 'Vrednost lastnosti je bila uspešno izbrisana.',
        'merged_title' => 'Vrednosti so združene',
        'merged_body' => 'Posodobili smo :affected izdelkov. Izbrana vrednost je ostala, druga je bila odstranjena.',
        'merged_error_title' => 'Združevanje ni uspelo',
        'merged_error_body' => 'Vrednosti trenutno ni mogoče združiti. Poskusite znova.',
    ],

    'grouping' => [
        'success_grouped_title' => 'Združeno',
        'success_grouped_body' => ':count vrednost(i) združene pod ":target".',
        'success_ungrouped_title' => 'Posodobljeno',
        'success_ungrouped_body' => ':count vrednost(i) odstranjene iz skupine.',
        'error_title' => 'Združevanje ni uspelo',
        'selected_values' => 'Izbrane vrednosti',
        'helper_target' => 'Izberite ciljno vrednost, v katero bodo združene izbrane vrednosti.',
        'errors' => [
            'target_in_sources' => 'Cilj ne sme biti med izbranimi vrednostmi.',
            'target_is_member' => 'Izbrani cilj že pripada drugi skupini.',
            'different_property' => 'Vse izbrane vrednosti in cilj morajo pripadati isti lastnosti.',
        ],
    ],

    'pages' => [
        'title' => [
            'with_property' => 'Vrednosti za: :property',
            'default' => 'Vrednosti lastnosti',
        ],
        'breadcrumbs' => [
            'properties' => 'Lastnosti',
            'list' => 'Seznam',
        ],
    ],

    'ui' => [
        'group_badge' => 'Skupina',
    ],

    'modal_grouping' => [
        'target_label' => 'Ciljna vrednost',
    ],
];
