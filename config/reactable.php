<?php

// config for TrueFans/LaravelReactable
return [

    /*
    |--------------------------------------------------------------------------
    | Reaction Types
    |--------------------------------------------------------------------------
    |
    | Define the available reaction types for your application.
    | Each reaction type should have an icon (emoji), label, and color.
    | You can add, remove, or modify these as needed.
    |
    */

    'reaction_types' => [
        'like' => [
            'icon' => '👍',
            'label' => 'Like',
            'color' => 'blue',
        ],
        'love' => [
            'icon' => '❤️',
            'label' => 'Love',
            'color' => 'red',
        ],
        'laugh' => [
            'icon' => '😂',
            'label' => 'Laugh',
            'color' => 'yellow',
        ],
        'wow' => [
            'icon' => '😮',
            'label' => 'Wow',
            'color' => 'purple',
        ],
        'sad' => [
            'icon' => '😢',
            'label' => 'Sad',
            'color' => 'blue',
        ],
        'angry' => [
            'icon' => '😠',
            'label' => 'Angry',
            'color' => 'orange',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Display Settings
    |--------------------------------------------------------------------------
    |
    | Configure how reactions are displayed in your application.
    |
    */

    'display' => [
        // Show detailed breakdown of reactions
        'show_breakdown' => true,

        // Show total reactions count
        'show_total' => true,

        // Show tooltips on hover
        'show_tooltips' => true,
    ],
    /*
   |--------------------------------------------------------------------------
   | Avatar Field
   |--------------------------------------------------------------------------
   |
   | Configure avatar path related to user model. THis will be displayed in users list.
   |
   */
    'avatar_field' => 'profile.image'

];
