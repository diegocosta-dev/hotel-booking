<?php

use craft\helpers\App;

return [
    'devMode' => true,
    'allowAdminChanges' => true,
    'securityKey' => App::env('CRAFT_SECURITY_KEY'),
];
