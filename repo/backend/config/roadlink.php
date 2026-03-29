<?php

return [
    'channels' => array_values(array_filter(array_map(
        static fn (string $value): string => trim($value),
        explode(',', env('ROADLINK_NOTIFICATION_CHANNELS', 'in_app'))
    ))),
];
