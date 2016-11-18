<?php

return [
    'icon' => 'Icon',
    'sphere' => 'Sphere of influence',
    'name' => 'Sphere',
    'group' => 'Group',
    'status' => 'Status',
    'values' => 'Lead deals range',
    'action' => 'Action',
    'settings' => 'Settings',
    'lead_form' => 'Lead form',
    'agent_form' => 'Agent form',
    'statuses' => 'Statuses',
    'finish' => 'Finish',
    'price' => 'Price',
    'active_mask' => 'Active mask',
    'inactive_mask' => 'Inactive mask',
    'status_not_changed' => 'Status not changed!',
    'status_changed' => 'Status changed!',

    'maskAll' => 'All masks',
    'mask' => 'Mask',
    'reprice' => 'Masks Reprice',
    'agent' => 'Agent',

    'month' => 'Month',
    'days' => 'Days',
    'hours' => 'Hours',
    'minutes' => 'Minutes',
    'lead_auction_expiration_interval' => 'Lead auction expiration interval',
    'lead_bad_status_interval' => 'Lead bad status interval',
    'max_range' => 'Agent max range',
    'range_show_lead_interval' => 'Range show lead interval',
    'max_lead' => 'Max lead',
    'price_call_center' => 'Price call center',
    'sphere_name' => 'Form name',
    'button_add_field' => 'Add field',

    'errors' => [
        'not_activated' => 'Sphere can not be activated for the following reasons:',

        'required' => [
            'name'                              => 'Field "Form name" required!',
            'openLead'                          => 'Field "Max lead" required!',
            'minLead'                           => 'Field "Minimum lead to close 1 deal" required!',
            'price_call_center'                 => 'Field "Price call center" required!',
            'lead_auction_expiration_interval' => 'Field "Lead auction expiration interval" required!',
            'lead_bad_status_interval'          => 'Field "Lead bad status interval" required!',
            'range_show_lead_interval'          => 'Field "Range show lead interval" required!',
            'max_range'                         => 'Field "Agent max range" required!'
        ],

        'agentForm' =>
            [
                'min_attributes'   => 'Sphere "Agent form" min attributes: '.config('sphere.agentForm.min_attributes'),
                'min_options' => 'Sphere "Agent form" min attribute options: '.config('sphere.agentForm.min_options'),
            ],
    ]
];