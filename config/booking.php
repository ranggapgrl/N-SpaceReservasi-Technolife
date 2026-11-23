<?php

return [
    'open_space_capacity' => (int) env('OPEN_SPACE_CAPACITY', 50),
    'venue_service_charge_percent' => 5,
    'venue_minimum_attendees' => [
        'meeting' => 5,
        'training' => 20,
    ],
    'venue_packages' => [
        'per_jam' => [
            'label' => 'Perjam',
            'duration_hours' => 1,
            'price_per_person' => 25000,
        ],
        'halfday' => [
            'label' => 'Halfday',
            'duration_hours' => 4,
            'price_per_person' => 40000,
        ],
        'fullday' => [
            'label' => 'Fullday',
            'duration_hours' => 8,
            'price_per_person' => 55000,
        ],
    ],
];
