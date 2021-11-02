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
    'created_at' => '登録日',
    'updated_at' => '更新日',
    'merchant' => [
        'name'   => '会社名',
        'manager' => '代表者名',
        'country' => '国名',
        'address' => '住所',
        'phone' => '電話番号',
        'email' => '代表email',
        'office_country' => '運営国名',
        'office_address' => '運営住所',
        'office_phone' => '運営電話番号',
        'office_person' => '運営担当者名',
        'office_email' => '運営担当email',
        'office_person_phone' => '運営担当電話番号',
        'bank_name' => '銀行名',
        'bank_swift' => 'swift',
        'bank_branch' => '支店名',
        'bank_account' => '口座種別',
        'bank_account_name' => '口座名義',
        'system_name' => 'システム会社名',
        'system_manager' => 'システム会社 担当名',
        'system_phone' => 'システム会社 電話番号',
        'system_email' => 'システム会社 email',
        'div_address' => '登記住所',
        'div_office_address' => '運営住所',
        'div_bank_info' => '銀行情報',
        'div_system_info' => 'システム設定',      
    ],
    'site' => [
        'merchant_id' => '会社',
        'name'   => 'サイト名',
        'username' => 'サイトID',
        'password' => 'サイトPasswd',
        'url' => 'URL',
        'monthly_fee' => '月額利用料',
        'payment_count' => '支払回数',
        'transaction_fee' => 'トランザクション料',
        'cancellation_fee' => 'キャンセル手数料',
        'charge_back_fee' => 'チャージバック手数料',
        'rolling_reserve_days' => 'ローリングリザーブ',
        'rolling_reserve_percent' => 'ローリングリザーブpercent',
        'gateway_id' => '利用gateway',
        'currency' => '決済通貨',
        'exchange_fee' => '為替設定',
        'monthly_credit_limit' => '１カ月限度額',
        'transaction_credit_limit' => '１回限度額',
        'foreign_credit_permit' => '海外クレジットカード許可',
        'primacy_agency' => '1次代理店プルダウン',
        'secondary_agency' => '2次代理店',
        'notification_url' => '通知先URL',
        'kickback_url' => 'キックバックURL',
        'kickback_resend' => 'キックバック再送',
        'success_transition_url' => '完了時遷移URL',
        'failure_transition_url' => '失敗時遷移URL',
        'cancel_transition_url' => 'キャンセル時遷移URL',
        'result_notification_email' => '決済結果通知email',
        'alert_email' => 'アラートemail',
        'status' => 'ステータス',
        'note' => '備考',
        'div_agency_info' => '代理店設定',
        'div_system_info' => 'システム設定',
    ],
    'gateway' => [
        'name'   => 'Gateway名',
        'gateway_key'   => 'GatewayID',
        'payment_fee' => '決済手数料',
        'transaction_fee' => 'トランザクション料',
        'cancellation_fee' => 'キャンセル料',
        'charge_back_fee' => 'チャージバック手数料',
        'rolling_reserve_days' => 'ローリングリザーブ',
        'rolling_reserve_percent' => 'ローリングリザーブpercent',
    ],
    'blacklist' => [
        'name'   => '拒否設定名',
        'card_number' => 'クレジットカード番号',
        'email' => 'メールアドレス',
        'phone' => '電話番号',
        'ip' => 'IPアドレス',
        'note' => 'メモ',
    ]
];
