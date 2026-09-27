# Hotel Booking

Accommodation booking for Craft CMS: rooms, availability, seasonal rates, bookings and customers.

> **Status:** in development. Not yet released.

## Requirements

- Craft CMS 5.0.0 or later
- PHP 8.2 or later
- MySQL 8.0+ or PostgreSQL 13+

## Installation

You can install this plugin from the Plugin Store or with Composer.

#### From the Plugin Store

Go to the Plugin Store in your project’s Control Panel and search for “Hotel Booking”. Then press “Install”.

#### With Composer

```bash
composer require diegocosta-dev/craft-hotel-booking
./craft plugin/install hotel-booking
```

## Configuration

Settings can be managed in **Settings → Plugins → Hotel Booking**, or overridden per environment by creating `config/hotel-booking.php`:

```php
<?php

return [
    'currency' => 'EUR',             // ISO 4217 code used for all prices and bookings
    'checkInTime' => '15:00',        // 24h, hotel local time
    'checkOutTime' => '11:00',       // 24h, hotel local time
    'referencePrefix' => 'HB',       // references look like HB-20260926-0001
    'pendingExpiryMinutes' => 60,    // unconfirmed bookings expire and release their rooms
];
```

Settings defined in this file are shown as read-only in the control panel.

## Permissions

| Permission | Allows |
| --- | --- |
| View bookings | See bookings in the control panel |
| ↳ Manage bookings | Edit booking notes and custom fields |
| ↳↳ Delete bookings | Delete bookings |
| ↳ Change booking status | Confirm, cancel and complete bookings |
| View customers | See customers (personal data) |
| ↳ Manage customers | Edit customers |
| ↳↳ Delete customers | Delete customers |
| Manage Hotel Booking settings | Plugin settings, field layouts, health check |

Catalog content (accommodation types, rooms, rates…) uses Craft’s native section permissions.

## Development

The plugin ships with its own test suite, PHPStan and ECS configuration. From a DDEV project where the plugin
lives in `plugins/hotel-booking` (loaded through a Composer `path` repository):

```bash
# once: install the plugin's dev dependencies and create the test database
ddev composer install --working-dir=plugins/hotel-booking
ddev mysql -uroot -proot -e "CREATE DATABASE IF NOT EXISTS hotelbooking_test; GRANT ALL ON hotelbooking_test.* TO 'db'@'%';"
cp plugins/hotel-booking/tests/.env.example plugins/hotel-booking/tests/.env

# run
ddev composer --working-dir=plugins/hotel-booking test              # all suites
ddev composer --working-dir=plugins/hotel-booking test:unit         # pure PHP, no Craft, no database
ddev composer --working-dir=plugins/hotel-booking test:integration  # boots Craft against the test database
ddev composer --working-dir=plugins/hotel-booking phpstan
ddev composer --working-dir=plugins/hotel-booking check-cs          # fix-cs to auto-fix
```

- **Unit tests** (`tests/unit`) cover pure domain logic in `src/domain`, which must not depend on Craft.
- **Integration tests** (`tests/integration`) use Craft’s official Codeception module. Every run wipes and reinstalls
  Craft + the plugin in the database from `tests/.env`. As a safety guard, the suite refuses to run unless the database
  name ends in `_test`. **Never point it at a real database.**
