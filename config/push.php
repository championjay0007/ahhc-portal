<?php

use App\Models\PortalSetting;

return [
    'vapid' => [
        'public_key' => fn () => PortalSetting::where('key', 'vapid_public_key')->value('value') ?? env('VAPID_PUBLIC_KEY'),
        'private_key' => fn () => PortalSetting::where('key', 'vapid_private_key')->value('value') ?? env('VAPID_PRIVATE_KEY'),
        'subject' => fn () => PortalSetting::where('key', 'vapid_subject')->value('value') ?? env('VAPID_SUBJECT', 'mailto:hello@example.com'),
    ],
];
