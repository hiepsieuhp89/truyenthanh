<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */
    'id' => 'Id',
    'created_at' => 'Ngày tạo',
    'updated_at' => 'Cập nhật',
    'merchant' => [
        'name'   => 'Name',
        'manager' => 'Manager name',
        'username' => 'MerchantID',
        'password' => 'Merchant password',
        'country' => 'Country',
        'address' => 'Address',
        'phone' => 'Phone',
        'email' => 'Email',
        'office_country' => 'Office country',
        'office_address' => 'Office address',
        'office_phone' => 'Office phone',
        'office_person' => 'Office manager',
        'office_email' => 'Office email',
        'office_person_phone' => 'Office manager phone',
        'bank_name' => 'Bank name',
        'bank_swift' => 'Bank swift',
        'bank_branch' => 'Bank branch',
        'bank_account' => 'Bank account',
        'bank_account_name' => 'Bank account name',
        'system_name' => 'System name',
        'system_manager' => 'System manager name',
        'system_phone' => 'System phone',
        'system_email' => 'System email',
        'div_address' => 'Registed address',
        'div_office_address' => 'Registed Office address',
        'div_bank_info' => 'Bank information',
        'div_system_info' => 'System information'
    ],
    'site' => [
        'merchant_id' => 'Merchant',
        'name'   => 'Name',
        'username' => 'SiteID',
        'password' => 'Site password',
        'url' => 'Url',
        'monthly_fee' => 'Monthly fee',
        'payment_count' => 'Payment count',
        'transaction_fee' => 'Transaction fee',
        'cancellation_fee' => 'Cancellation fee',
        'charge_back_fee' => 'Charge back fee',
        'rolling_reserve_days' => 'Rolling reserve days',
        'rolling_reserve_percent' => 'Rolling reserve percent',
        'gateway_id' => 'Gateway',
        'currency' => 'Currency',
        'exchange_fee' => 'Exchange fee',
        'monthly_credit_limit' => 'Monthly credit limit',
        'transaction_credit_limit' => 'Transaction credit limit',
        'foreign_credit_permit' => 'Foreign credit permit',
        'primacy_agency' => 'Primacy agency',
        'secondary_agency' => 'Secondary agency',
        'notification_url' => 'Notification URL',
        'kickback_url' => 'Kickback URL',
        'kickback_resend' => 'Kickback resend',
        'success_transition_url' => 'Success transition URL',
        'failure_transition_url' => 'Failure transition URL',
        'cancel_transition_url' => 'Cancel transition URL',
        'result_notification_email' => 'Result notification email',
        'alert_email' => 'Alert email',
        'status' => 'Status',
        'note' => 'Note',
        'div_agency_info' => 'Agency information',
        'div_system_info' => 'System information'
    ],
    'gateway' => [
        'name'   => 'Name',
        'gateway_key'   => 'Gateway ID',
        'payment_fee' => 'Payment fee',
        'transaction_fee' => 'Transaction fee',
        'cancellation_fee' => 'Cancellation fee',
        'charge_back_fee' => 'Charge back fee',
        'rolling_reserve_days' => 'Rolling reserve days',
        'rolling_reserve_percent' => 'Rolling reserve percent'
    ],
    'blacklist' => [
        'name'   => 'Name',
        'card_number' => 'Card number',
        'email' => 'Email',
        'phone' => 'Phone',
        'ip' => 'IP Address',
        'note' => 'Note'
    ]
];
