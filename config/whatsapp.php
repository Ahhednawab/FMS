<?php

return [

    /*
    |--------------------------------------------------------------------------
    | WhatsApp Cloud API credentials
    |--------------------------------------------------------------------------
    |
    | Every value comes from the Meta app dashboard (WhatsApp > API Setup) and
    | belongs in .env only - nothing here is ever committed. While app_secret
    | is empty the webhook refuses every call, so a half-finished setup cannot
    | let unsigned requests through.
    |
    */

    'phone_number_id' => env('WHATSAPP_PHONE_ID'),

    'business_account_id' => env('WHATSAPP_WABA_ID'),

    'token' => env('WHATSAPP_TOKEN'),

    'app_secret' => env('WHATSAPP_APP_SECRET'),

    // Any random string of your own choosing; Meta echoes it back once, while
    // the webhook URL is being verified.
    'verify_token' => env('WHATSAPP_VERIFY_TOKEN'),

    // The Graph API version the dashboard shows for your app.
    'api_version' => env('WHATSAPP_API_VERSION', 'v21.0'),

    /*
    |--------------------------------------------------------------------------
    | Behaviour
    |--------------------------------------------------------------------------
    */

    // Send the supervisor a short "received N lines" reply. Costs a message,
    // so it stays off until it is wanted.
    'reply_on_receipt' => (bool) env('WHATSAPP_REPLY_ON_RECEIPT', false),

    // Only numbers listed in whatsapp_senders may post attendance. Anything
    // else is stored and flagged, never turned into a draft.
    'accept_unknown_senders' => (bool) env('WHATSAPP_ACCEPT_UNKNOWN_SENDERS', false),

    // Where the parsing happens. False (the default) runs it straight after
    // the webhook response in the same process, so no queue worker is needed.
    // True puts it on the database queue, which needs `php artisan queue:work`
    // running - better once there is a worker, since it retries on failure.
    'parse_queue' => (bool) env('WHATSAPP_PARSE_QUEUE', false),

];
