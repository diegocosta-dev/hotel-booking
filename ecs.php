<?php

declare(strict_types=1);

use craft\ecs\SetList;
use Symplify\EasyCodingStandard\Config\ECSConfig;

// craftcms/ecs pins an older ECS whose bundled vendors emit PHP 8.4 deprecation notices.
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

return static function(ECSConfig $ecsConfig): void {
    $ecsConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
        __FILE__,
    ]);

    $ecsConfig->skip([
        __DIR__ . '/tests/_craft',
        __DIR__ . '/tests/_support/_generated',
    ]);

    $ecsConfig->sets([
        SetList::CRAFT_CMS_4,
    ]);
};
