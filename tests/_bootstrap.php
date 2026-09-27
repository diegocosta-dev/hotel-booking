<?php

declare(strict_types=1);

/**
 * Global Codeception bootstrap.
 *
 * Defines the constants Craft's test module needs. It only boots the Craft application
 * (and touches the database) in suites that enable the `\craft\test\Craft` module, i.e.
 * the integration suite. The unit suite never starts Craft.
 *
 * Environment variables come from the real environment (CI) or from tests/.env (local).
 */

use craft\test\TestSetup;
use Dotenv\Dotenv;

ini_set('date.timezone', 'UTC');
date_default_timezone_set('UTC');

$testsPath = __DIR__;
$pluginRoot = dirname($testsPath);

if (file_exists($testsPath . '/.env')) {
    Dotenv::createUnsafeImmutable($testsPath)->safeLoad();
}

// Safety guard: the Craft test module wipes the database, so refuse anything that
// doesn't look like a dedicated test database.
$dsn = (string)getenv('CRAFT_DB_DSN');
if ($dsn !== '' && (!preg_match('/dbname=([^;]+)/', $dsn, $matches) || !str_ends_with($matches[1], '_test'))) {
    fwrite(STDERR, "Refusing to run integration tests: CRAFT_DB_DSN must point at a database whose name ends in \"_test\" (got \"$dsn\").\n");
    exit(1);
}

define('CRAFT_TESTS_PATH', $testsPath);
define('CRAFT_ROOT_PATH', $pluginRoot);
define('CRAFT_STORAGE_PATH', $testsPath . '/_craft/storage');
define('CRAFT_TEMPLATES_PATH', $testsPath . '/_craft/templates');
define('CRAFT_CONFIG_PATH', $testsPath . '/_craft/config');
define('CRAFT_MIGRATIONS_PATH', $testsPath . '/_craft/migrations');
define('CRAFT_TRANSLATIONS_PATH', $testsPath . '/_craft/translations');
define('CRAFT_VENDOR_PATH', $pluginRoot . '/vendor');

TestSetup::configureCraft();
