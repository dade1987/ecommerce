<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Carbon Emission Factors
    |--------------------------------------------------------------------------
    |
    | This file stores the emission factors for various activities,
    | used to calculate the carbon footprint.
    |
    */

    'transport_modes' => [
        // Emission factor in kg CO2 per km
        'camion' => 0.2,
        'treno' => 0.05,
        'aereo' => 0.5,
        'truck' => 0.2, // Alias for camion
        'train' => 0.05, // Alias for treno
        'plane' => 0.5,  // Alias for aereo
    ],
]; 