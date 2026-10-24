<?php

/*
|--------------------------------------------------------------------------
| Sheriff Security Services – company facts
|--------------------------------------------------------------------------
| Public copy that is not editable from the admin. Every fact here has a
| source (see 'sources' at the bottom); nothing is invented. Contact details
| come from the company's own 2021 website (sssgy.net, Wayback Machine) and are
| only DEFAULTS: the live values are admin-editable settings (Admin → Settings),
| because they must be confirmed with Sheriff before launch.
*/

return [
    // Concept preview on a temporary domain: banner + noindex. Turn off at launch.
    'preview' => (bool) env('SHERIFF_PREVIEW', true),

    'name' => 'Sheriff Security Services',
    'legal_name' => 'Sheriff Security Inc.',
    'group_name' => 'Sheriff Guyana',
    'founded' => 2015,

    // Defaults for the editable settings (sssgy.net/contact-us, archived 2021). CONFIRM.
    'defaults' => [
        'phone' => '+592 227 5691',
        'phone_alt' => '',
        'whatsapp' => '',
        'email' => 'sheriff.s.service@gmail.com',
        'proposals_email' => '',
        'address_line' => 'Lot 12 & 13 Sheriff Street',
        'address_city' => 'Georgetown',
        'office_hours' => 'Monday to Friday, 9 am to 5 pm',
        'command_centre_line' => '',
        'facebook' => 'https://www.facebook.com/sheriffssgy',
        'instagram' => 'https://www.instagram.com/sheriffssgy',
        'linkedin' => '',
        'rfp_response_hours' => '24',
        'vendor_link_days' => '7',
        'notify_email' => '',
    ],

    // Sheriff Street, Georgetown (approximate; street-level, not the exact lot).
    'geo' => ['lat' => 6.8133, 'lng' => -58.1385],

    /*
     * Services. Only lines of business a source names. 'points' quote or closely
     * paraphrase the source given in 'source' (keys of 'sources' below).
     */
    'services' => [
        'oil-and-gas' => [
            'title' => 'Oil & gas and energy security',
            'short' => 'Oil & gas',
            'icon' => 'flame',
            'photo' => 'fpso-helideck',
            'tag' => 'PTW-01',
            'summary' => 'Security for operators and contractors in Guyana’s energy sector, run under ISO 9001:2015 quality and ISO 45001:2018 occupational health and safety management systems.',
            'points' => [
                'Armed guarding and patrols for energy-sector sites and facilities',
                'Officers trained in oil and gas, HSE and ISPS at the Sheriff Training Academy',
                'Threat and risk assessments, crisis management and background checks',
                'A Bell 505 helicopter added to the fleet for offshore services',
            ],
            'source' => ['oilnow_2023', 'sssgy_services', 'stabroek_2023_heli'],
        ],
        'marine-and-port' => [
            'title' => 'Marine & port security',
            'short' => 'Marine & port',
            'icon' => 'anchor',
            'photo' => 'demerara-patrol',
            'tag' => 'PTW-02',
            'summary' => 'Marine patrols on the Demerara River, with the Berbice River announced next, and a dedicated port and marine company within the group.',
            'points' => [
                'Armed marine patrol on the Demerara River',
                'Sheriff Port & Marine, a group company formed after Sheriff Security',
                'ISPS (International Ship and Port Facility Security) training at the academy',
                'Named Best Maritime Services Provider at the 2024 GAPSO awards',
            ],
            'source' => ['oilnow_2023', 'linkedin', 'sssgy_home', 'gapso_2024'],
        ],
        'cash-in-transit' => [
            'title' => 'Cash in transit',
            'short' => 'Cash in transit',
            'icon' => 'truck',
            'photo' => 'georgetown-street',
            'tag' => 'PTW-03',
            'summary' => 'Armed cash-in-transit runs for businesses that move cash and valuables, backed by GPS fleet tracking.',
            'points' => [
                'Armed cash-in-transit and armed land patrol',
                'GPS fleet tracking on the vehicles',
                'Named Best Cash-in-Transit Services Provider at the 2024 GAPSO awards',
            ],
            'source' => ['kaieteur_2021', 'oilnow_2023', 'gapso_2024'],
        ],
        'alarms-and-monitoring' => [
            'title' => 'Alarms & monitoring',
            'short' => 'Alarms',
            'icon' => 'bell',
            'photo' => 'cctv-wall',
            'tag' => 'PTW-04',
            'summary' => 'Burglar alarms and intrusion systems from Sheriff Alarms, watched from a 24-hour command centre.',
            'points' => [
                'Burglar alarm and intrusion-detection systems',
                'A 24-hour command centre',
                'Best Alarm Monitoring Services Provider and Best Alarm Installation Team, GAPSO 2024',
            ],
            'source' => ['oilnow_2023', 'linkedin', 'sssgy_services', 'gapso_2024'],
        ],
        'guarding-and-patrols' => [
            'title' => 'Manned guarding & mobile patrols',
            'short' => 'Guarding',
            'icon' => 'shield',
            'photo' => 'georgetown-commerce',
            'tag' => 'PTW-05',
            'summary' => 'Uniformed and armed officers for commercial, corporate and diplomatic sites, with mobile patrols and electronic guard tracking.',
            'points' => [
                'Officers drawn from reputable army and police units',
                'Mobile patrols and armed land patrol',
                'Silvertrac guard-tracking software',
                'First aid and CPR team',
                'Best Commercial Security Services Provider, GAPSO 2024',
            ],
            'source' => ['sssgy_home', 'sssgy_services', 'kaieteur_2021', 'gapso_2024'],
        ],
        'air-patrol' => [
            'title' => 'Armed air patrol',
            'short' => 'Air patrol',
            'icon' => 'helicopter',
            'photo' => 'ogle-airport',
            'tag' => 'PTW-06',
            'summary' => 'Armed air patrol, including a Bell 505 helicopter that Stabroek News reported as the first of its type for private use in Guyana.',
            'points' => [
                'Armed air patrol as part of the service range',
                'Bell 505 helicopter, reported at US$1.9 million, added for offshore services',
            ],
            'source' => ['kaieteur_2021', 'stabroek_2023_heli', 'oilnow_2023'],
        ],
    ],

    // Who the services are built for. Neutral wording: these are sectors, not named clients.
    'industries' => [
        ['title' => 'Oil, gas & energy', 'icon' => 'flame', 'photo' => 'fpso-sunset', 'body' => 'Operators, contractors and suppliers working onshore, at shorebases and in support of offshore operations, where HSE management systems and documentation matter as much as the officers.', 'services' => ['oil-and-gas', 'marine-and-port', 'air-patrol']],
        ['title' => 'Maritime, port & logistics', 'icon' => 'anchor', 'photo' => 'stelling', 'body' => 'Wharves, terminals, vessels and river operations on the Demerara, with ISPS-trained officers.', 'services' => ['marine-and-port', 'guarding-and-patrols']],
        ['title' => 'Banking & cash-handling businesses', 'icon' => 'truck', 'photo' => 'georgetown-street', 'body' => 'Businesses that move cash or valuables between branches, tills and banks.', 'services' => ['cash-in-transit', 'alarms-and-monitoring']],
        ['title' => 'Commercial, retail & hospitality', 'icon' => 'building', 'photo' => 'georgetown-commerce', 'body' => 'Offices, shops, hotels and call centres that need a steady guard force and patrol cover.', 'services' => ['guarding-and-patrols', 'alarms-and-monitoring']],
        ['title' => 'Diplomatic & corporate', 'icon' => 'flag', 'photo' => 'cathedral', 'body' => 'Missions and corporate residences that need discreet, vetted officers and risk assessments.', 'services' => ['guarding-and-patrols']],
        ['title' => 'Homes & small businesses', 'icon' => 'home', 'photo' => 'georgetown-river', 'body' => 'Alarm installation, monitoring and response for households and owner-run businesses.', 'services' => ['alarms-and-monitoring']],
    ],

    // Management systems and registrations, as reported by OilNOW (Feb 2023).
    'certifications' => [
        ['code' => 'ISO 9001:2015', 'title' => 'Quality management system', 'status' => 'certified', 'source' => 'oilnow_2023'],
        ['code' => 'ISO 45001:2018', 'title' => 'Occupational health & safety management system', 'status' => 'certified', 'source' => 'oilnow_2023'],
        ['code' => 'Local Content', 'title' => 'Certified by Guyana’s Local Content Secretariat', 'status' => 'certified', 'source' => 'oilnow_2023'],
        ['code' => 'ISO/IEC 27001', 'title' => 'Information security management system', 'status' => 'pursuing', 'source' => 'oilnow_2023'],
    ],

    // GAPSO (Guyana Association of Private Security Organisations) awards, Dec 2024:
    // "13 of the 26 prestigious accolades" (Guyana Standard). Only named categories listed.
    'awards' => [
        'year' => 2024,
        'total' => 13,
        'of' => 26,
        'company' => [
            'Best Security Service Company',
            'Best Cash-in-Transit Services Provider',
            'Best Maritime Services Provider',
            'Best Alarm Monitoring Services Provider',
            'Best Commercial Security Services Provider',
            'Best New Security Product',
            'Best Alarm Installation Team',
        ],
        'people' => [
            ['name' => 'Ameir Ahmad', 'awards' => ['Leading Visionary in Private Security', 'Best Security Chairman']],
            ['name' => 'Radica Ramdeen', 'awards' => ['Most Inspirational Female CEO in Security', 'Best Female Security Entrepreneur']],
        ],
        'source' => 'gapso_2024',
    ],

    // Group companies (LinkedIn "Sheriff Guyana"; Stabroek News 2022–2025).
    'group' => [
        ['name' => 'Sheriff Security Services', 'legal' => 'Sheriff Security Inc.', 'role' => 'The core company, established in 2015: guarding, patrols, cash in transit, marine and air patrol.', 'icon' => 'shield', 'source' => 'linkedin', 'route' => 'services'],
        ['name' => 'Sheriff Alarms', 'legal' => null, 'role' => 'Burglar alarm and intrusion-system installation and monitoring.', 'icon' => 'bell', 'source' => 'linkedin', 'route' => 'alarm-service'],
        ['name' => 'Sheriff Port & Marine', 'legal' => null, 'role' => 'The group’s port and marine arm.', 'icon' => 'anchor', 'source' => 'linkedin', 'route' => null],
        ['name' => 'Sheriff Training Academy', 'legal' => null, 'role' => 'Oil and gas, HSE and ISPS training centre for the group’s officers.', 'icon' => 'graduation', 'source' => 'sssgy_home', 'route' => 'careers'],
        ['name' => 'Sheriff Construction', 'legal' => 'Sheriff Construction Inc.', 'role' => 'Construction company within Sheriff Holdings.', 'icon' => 'hard-hat', 'source' => 'stabroek_2024_construction', 'route' => null],
        ['name' => 'Sheriff Medical Centre', 'legal' => 'Sheriff Medical Centre Inc.', 'role' => 'Healthcare: the group’s hospital, reported as Sheriff General Hospital.', 'icon' => 'plus', 'source' => 'stabroek_2022_hospital', 'route' => null],
    ],

    // Leadership as last reported. Confirm titles before launch.
    'leadership' => [
        ['name' => 'Radica Ramdeen', 'title' => 'Chief Executive Officer', 'note' => 'Named CEO by OilNOW (Feb 2023) and Stabroek News (Dec 2023). GAPSO 2024: Most Inspirational Female CEO in Security.', 'source' => 'stabroek_2023_insurance'],
        ['name' => 'Ameir Ahmad', 'title' => 'Managing Director, Sheriff Group', 'note' => 'Named Managing Director by OilNOW (2023). GAPSO 2024: Leading Visionary in Private Security.', 'source' => 'oilnow_2023_nov'],
        ['name' => 'Divesh Ramjattan', 'title' => 'Deputy Chief Executive Officer', 'note' => 'As reported by OilNOW, November 2023.', 'source' => 'oilnow_2023_nov'],
        ['name' => 'Hayma Singh', 'title' => 'Director of Projects', 'note' => 'As reported by OilNOW, November 2023.', 'source' => 'oilnow_2023_nov'],
        ['name' => 'Janelle Persaud', 'title' => 'Director of Public Affairs', 'note' => 'As reported by OilNOW, November 2023.', 'source' => 'oilnow_2023_nov'],
    ],

    // Workforce welfare (Stabroek News, 31 Dec 2023).
    'welfare' => [
        'headline' => 'Health and life insurance at no cost to the workforce',
        'body' => 'From January 2024 Sheriff began offering a health and life insurance plan to its workforce at no cost to employees.',
        'quote' => 'At SSS, we firmly believe that a thriving, healthy workforce is inherently more productive and motivated.',
        'quote_by' => 'Radica Ramdeen, CEO',
        'source' => 'stabroek_2023_insurance',
    ],

    // Training topics listed on the company's 2021 website.
    'training' => ['Security and safety awareness', 'Contingency planning', 'Crisis communication', 'Data protection', 'Standard operating procedures', 'HSE', 'First aid and CPR', 'Oil and gas', 'ISPS (ship and port facility security)'],

    // First staff account, created by `php artisan db:seed` (or setup.php) when set.
    'admin' => ['name' => env('ADMIN_NAME', 'Administrator'), 'email' => env('ADMIN_EMAIL'), 'password' => env('ADMIN_PASSWORD')],

    // Who receives "new submission" emails when the setting is empty (comma separated).
    'notify' => env('SHERIFF_NOTIFY_EMAIL'),

    'photos' => [
        'fpso-sunset' => ['w' => 1920, 'h' => 1440],
        'fpso-helideck' => ['w' => 1920, 'h' => 1280],
        'osv-shorebase' => ['w' => 1920, 'h' => 1440],
        'demerara-patrol' => ['w' => 1920, 'h' => 1371],
        'offshore-patrol' => ['w' => 1920, 'h' => 1104],
        'georgetown-river' => ['w' => 1920, 'h' => 1278],
        'harbour-bridge' => ['w' => 1920, 'h' => 1280],
        'georgetown-coast' => ['w' => 1920, 'h' => 1280],
        'stelling' => ['w' => 1920, 'h' => 1440],
        'ogle-airport' => ['w' => 1920, 'h' => 1222],
        'cathedral' => ['w' => 1920, 'h' => 1280],
        'georgetown-commerce' => ['w' => 1920, 'h' => 1440],
        'georgetown-street' => ['w' => 1920, 'h' => 1280],
        'cctv-wall' => ['w' => 1920, 'h' => 789],
    ],

    'sources' => [
        'oilnow_2023' => ['label' => 'OilNOW, “Sheriff Security: the prototype for oil and gas security in Guyana”, Feb 2023 (promoted feature)', 'url' => 'https://oilnow.gy/featured/sheriff-security-the-prototype-for-oil-and-gas-security-in-guyana/'],
        'oilnow_2023_nov' => ['label' => 'OilNOW, November 2023', 'url' => 'https://oilnow.gy/'],
        'gapso_2024' => ['label' => 'Guyana Standard, “Sheriff Security cops 13 accolades at GAPSO awards ceremony”, 20 Dec 2024', 'url' => 'https://www.guyanastandard.com/2024/12/20/sheriff-security-cops-13-accolades-at-gapso-awards-ceremony/'],
        'kaieteur_2021' => ['label' => 'Kaieteur News, 23 Jul 2021', 'url' => 'https://kaieteurnewsonline.com/2021/07/23/eight-companies-apply-to-provide-security-services-for-gra/'],
        'stabroek_2023_heli' => ['label' => 'Stabroek News, Feb 2023', 'url' => 'https://www.stabroeknews.com/'],
        'stabroek_2023_insurance' => ['label' => 'Stabroek News, 31 Dec 2023', 'url' => 'https://www.stabroeknews.com/'],
        'stabroek_2024_construction' => ['label' => 'Stabroek News, 17 Mar 2024', 'url' => 'https://www.stabroeknews.com/'],
        'stabroek_2022_hospital' => ['label' => 'Stabroek News, 4 Dec 2022', 'url' => 'https://www.stabroeknews.com/'],
        'linkedin' => ['label' => 'Sheriff Guyana, LinkedIn company profile', 'url' => 'https://www.linkedin.com/'],
        'sssgy_home' => ['label' => 'sssgy.net home page (2021, Wayback Machine)', 'url' => 'https://web.archive.org/web/2021/https://sssgy.net/'],
        'sssgy_services' => ['label' => 'sssgy.net services page (2021, Wayback Machine)', 'url' => 'https://web.archive.org/web/2021/https://sssgy.net/services/'],
    ],
];
