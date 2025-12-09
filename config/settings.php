<?php

return [
    // Pagination settings
    'items_per_page' => env('ITEMS_PER_PAGE', 10),

    'date_format' => env('APP_DATE_FORMAT', 'M d, Y'),

    // Default pagination values for different sections
    'pagination' => [
        'delivery_requests' => env('ITEMS_PER_PAGE', 10),
        'trips' => env('ITEMS_PER_PAGE', 10),
        'drivers' => env('ITEMS_PER_PAGE', 10),
        'vehicles' => env('ITEMS_PER_PAGE', 10),
        'clients' => env('ITEMS_PER_PAGE', 10),
    ],
];
