<?php

use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;
use NotificationChannels\WebPush\WebPushServiceProvider;

return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,
    WebPushServiceProvider::class,
];
