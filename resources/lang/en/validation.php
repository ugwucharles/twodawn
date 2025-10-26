<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Custom Validation Messages
    |--------------------------------------------------------------------------
    |
    | Custom validation messages for better user experience
    |
    */

    'custom' => [
        'title' => [
            'required' => 'Event title is required.',
            'max' => 'Event title cannot be longer than 255 characters.',
        ],
        'description' => [
            'string' => 'Description must be text.',
        ],
        'venue' => [
            'max' => 'Venue name cannot be longer than 255 characters.',
        ],
        'mood' => [
            'required' => 'Please select an event mood.',
            'in' => 'Please select a valid event mood.',
        ],
        'starts_at' => [
            'required' => 'Event start date and time is required.',
            'date' => 'Please enter a valid start date and time.',
        ],
        'ends_at' => [
            'date' => 'Please enter a valid end date and time.',
            'after_or_equal' => 'End time must be after or equal to start time.',
        ],
        'price' => [
            'numeric' => 'Price must be a valid number.',
            'min' => 'Price cannot be negative.',
        ],
        'early_bird_price' => [
            'numeric' => 'Early bird price must be a valid number.',
            'min' => 'Early bird price cannot be negative.',
        ],
        'early_bird_ends_at' => [
            'date' => 'Please enter a valid early bird end date.',
        ],
        'capacity' => [
            'integer' => 'Capacity must be a whole number.',
            'min' => 'Capacity must be at least 1.',
        ],
        'image' => [
            'image' => 'Please upload a valid image file.',
            'mimes' => 'Image must be a JPG, PNG, or WebP file.',
            'max' => 'Image size cannot exceed 2MB.',
        ],
        'buyer_name' => [
            'required' => 'Your name is required.',
            'max' => 'Name cannot be longer than 255 characters.',
        ],
        'buyer_email' => [
            'required' => 'Your email is required.',
            'email' => 'Please enter a valid email address.',
            'max' => 'Email cannot be longer than 255 characters.',
        ],
        'buyer_phone' => [
            'max' => 'Phone number cannot be longer than 50 characters.',
        ],
        'quantity' => [
            'required' => 'Please select the number of tickets.',
            'integer' => 'Quantity must be a whole number.',
            'min' => 'You must order at least 1 ticket.',
        ],
        'coupon' => [
            'max' => 'Coupon code cannot be longer than 50 characters.',
        ],
        'name' => [
            'required' => 'Name is required.',
            'max' => 'Name cannot be longer than :max characters.',
        ],
        'email' => [
            'required' => 'Email is required.',
            'email' => 'Please enter a valid email address.',
            'max' => 'Email cannot be longer than :max characters.',
        ],
        'content' => [
            'required' => 'Comment content is required.',
            'max' => 'Comment cannot be longer than :max characters.',
        ],
        'phone' => [
            'max' => 'Phone number cannot be longer than :max characters.',
        ],
        'event_title' => [
            'required' => 'Event title is required.',
            'max' => 'Event title cannot be longer than :max characters.',
        ],
        'event_date' => [
            'date' => 'Please enter a valid event date.',
        ],
        'expected_attendees' => [
            'integer' => 'Expected attendees must be a whole number.',
            'min' => 'Expected attendees must be at least 1.',
        ],
        'budget' => [
            'numeric' => 'Budget must be a valid number.',
            'min' => 'Budget cannot be negative.',
        ],
        'message' => [
            'max' => 'Message cannot be longer than :max characters.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | Custom attribute names for validation messages
    |
    */

    'attributes' => [
        'buyer_name' => 'name',
        'buyer_email' => 'email',
        'buyer_phone' => 'phone number',
        'starts_at' => 'start date',
        'ends_at' => 'end date',
        'early_bird_price' => 'early bird price',
        'early_bird_ends_at' => 'early bird end date',
        'is_published' => 'published status',
        'event_title' => 'event title',
        'expected_attendees' => 'expected attendees',
    ],
];
