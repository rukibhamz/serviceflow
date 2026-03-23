<?php

return [
    'items' => [
        [
            'id'          => 'new-laptop-request',
            'name'        => 'New Laptop Request',
            'description' => 'Request a new laptop or workstation.',
            'type'        => 'service_request',
            'priority'    => 'medium',
            'fields'      => [
                ['name' => 'preferred_model', 'label' => 'Preferred Model', 'type' => 'text', 'required' => false],
                ['name' => 'justification',   'label' => 'Business Justification', 'type' => 'textarea', 'required' => true],
            ],
        ],
        [
            'id'          => 'software-access',
            'name'        => 'Software Access Request',
            'description' => 'Request access to a software application or system.',
            'type'        => 'service_request',
            'priority'    => 'low',
            'fields'      => [
                ['name' => 'software_name', 'label' => 'Software / System Name', 'type' => 'text', 'required' => true],
                [
                    'name'     => 'access_level',
                    'label'    => 'Access Level',
                    'type'     => 'select',
                    'required' => true,
                    'options'  => ['Read-only', 'Standard', 'Admin'],
                ],
            ],
        ],
        [
            'id'          => 'report-an-incident',
            'name'        => 'Report an Incident',
            'description' => 'Report a system outage, error, or unexpected behaviour.',
            'type'        => 'incident',
            'priority'    => 'high',
            'fields'      => [
                ['name' => 'affected_system', 'label' => 'Affected System', 'type' => 'text',     'required' => true],
                ['name' => 'impact',          'label' => 'Business Impact',  'type' => 'textarea', 'required' => true],
            ],
        ],
        [
            'id'          => 'password-reset',
            'name'        => 'Password Reset',
            'description' => 'Request a password reset for a system or account.',
            'type'        => 'service_request',
            'priority'    => 'low',
            'fields'      => [
                ['name' => 'account_email', 'label' => 'Account Email', 'type' => 'text', 'required' => true],
            ],
        ],
    ],
];
