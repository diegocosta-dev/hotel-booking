<?php

declare(strict_types=1);

namespace diegocostadev\hotelbooking\tests\integration;

use Codeception\Test\Unit;
use Craft;
use diegocostadev\hotelbooking\models\Settings;
use diegocostadev\hotelbooking\Plugin;

final class PluginInstallTest extends Unit
{
    public function testPluginIsInstalledAndEnabled(): void
    {
        $plugin = Craft::$app->getPlugins()->getPlugin('hotel-booking');

        $this->assertInstanceOf(Plugin::class, $plugin);
        $this->assertTrue(Craft::$app->getPlugins()->isPluginInstalled('hotel-booking'));
        $this->assertTrue(Craft::$app->getPlugins()->isPluginEnabled('hotel-booking'));
    }

    public function testDefaultSettingsAreValid(): void
    {
        $settings = Plugin::getInstance()->getSettings();

        $this->assertInstanceOf(Settings::class, $settings);
        $this->assertSame('USD', $settings->currency);
        $this->assertTrue($settings->validate(), print_r($settings->getErrors(), true));
    }

    public function testInvalidSettingsAreRejected(): void
    {
        $settings = new Settings();
        $settings->setAttributes([
            'currency' => 'xyz',
            'checkInTime' => '25:00',
            'referencePrefix' => 'hb-1',
            'pendingExpiryMinutes' => 1,
        ], false);

        $this->assertFalse($settings->validate());
        $this->assertArrayHasKey('currency', $settings->getErrors());
        $this->assertArrayHasKey('checkInTime', $settings->getErrors());
        $this->assertArrayHasKey('referencePrefix', $settings->getErrors());
        $this->assertArrayHasKey('pendingExpiryMinutes', $settings->getErrors());
    }

    public function testPermissionsAreRegistered(): void
    {
        $all = Craft::$app->getUserPermissions()->getAllPermissions();
        $group = array_values(array_filter($all, fn(array $g) => $g['heading'] === 'Hotel Booking'))[0] ?? null;

        $this->assertNotNull($group);
        $this->assertArrayHasKey(Plugin::PERMISSION_VIEW_BOOKINGS, $group['permissions']);
        $this->assertArrayHasKey(Plugin::PERMISSION_MANAGE_SETTINGS, $group['permissions']);
    }
}
