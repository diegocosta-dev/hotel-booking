<?php

declare(strict_types=1);

namespace diegocostadev\hotelbooking;

use Craft;
use craft\base\Model;
use craft\base\Plugin as BasePlugin;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterUserPermissionsEvent;
use craft\services\UserPermissions;
use craft\web\UrlManager;
use diegocostadev\hotelbooking\models\Settings;
use yii\base\Event;

/**
 * Hotel Booking plugin
 *
 * @method static Plugin getInstance()
 * @method Settings getSettings()
 * @author Diego Costa
 * @copyright Diego Costa
 * @license https://craftcms.github.io/license/ Craft License
 */
class Plugin extends BasePlugin
{
    public const PERMISSION_VIEW_BOOKINGS = 'hotelBooking-viewBookings';
    public const PERMISSION_MANAGE_BOOKINGS = 'hotelBooking-manageBookings';
    public const PERMISSION_DELETE_BOOKINGS = 'hotelBooking-deleteBookings';
    public const PERMISSION_CHANGE_BOOKING_STATUS = 'hotelBooking-changeBookingStatus';
    public const PERMISSION_VIEW_CUSTOMERS = 'hotelBooking-viewCustomers';
    public const PERMISSION_MANAGE_CUSTOMERS = 'hotelBooking-manageCustomers';
    public const PERMISSION_DELETE_CUSTOMERS = 'hotelBooking-deleteCustomers';
    public const PERMISSION_MANAGE_SETTINGS = 'hotelBooking-manageSettings';

    public string $schemaVersion = '1.0.0';
    public bool $hasCpSettings = true;
    public bool $hasCpSection = true;

    /**
     * @return array<string, mixed>
     */
    public static function config(): array
    {
        return [
            'components' => [
                // Define component configs here...
            ],
        ];
    }

    public function init(): void
    {
        parent::init();

        $this->attachEventHandlers();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getCpNavItem(): ?array
    {
        $item = parent::getCpNavItem();
        if ($item === null) {
            return null;
        }

        $item['label'] = Craft::t('hotel-booking', 'Hotel Booking');

        $user = Craft::$app->getUser();
        $subnav = [];

        if ($user->checkPermission(self::PERMISSION_VIEW_BOOKINGS)) {
            $subnav['bookings'] = [
                'label' => Craft::t('hotel-booking', 'Bookings'),
                'url' => 'hotel-booking/bookings',
            ];
        }

        if ($user->checkPermission(self::PERMISSION_VIEW_CUSTOMERS)) {
            $subnav['customers'] = [
                'label' => Craft::t('hotel-booking', 'Customers'),
                'url' => 'hotel-booking/customers',
            ];
        }

        if ($user->getIsAdmin() && Craft::$app->getConfig()->getGeneral()->allowAdminChanges) {
            $subnav['settings'] = [
                'label' => Craft::t('hotel-booking', 'Settings'),
                'url' => 'settings/plugins/' . $this->handle,
            ];
        }

        $item['subnav'] = $subnav;

        return $item;
    }

    protected function createSettingsModel(): ?Model
    {
        return Craft::createObject(Settings::class);
    }

    protected function settingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate('hotel-booking/_settings.twig', [
            'plugin' => $this,
            'settings' => $this->getSettings(),
            'overrides' => array_keys(Craft::$app->getConfig()->getConfigFromFile($this->handle)),
        ]);
    }

    private function attachEventHandlers(): void
    {
        Event::on(
            UserPermissions::class,
            UserPermissions::EVENT_REGISTER_PERMISSIONS,
            function(RegisterUserPermissionsEvent $event) {
                $event->permissions[] = [
                    'heading' => Craft::t('hotel-booking', 'Hotel Booking'),
                    'permissions' => $this->permissions(),
                ];
            },
        );

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function(RegisterUrlRulesEvent $event) {
                // Placeholder screens until the Booking/Customer element indexes exist (phase 5).
                $event->rules['hotel-booking'] = ['template' => 'hotel-booking/_cp/index.twig'];
                $event->rules['hotel-booking/bookings'] = ['template' => 'hotel-booking/_cp/index.twig'];
                $event->rules['hotel-booking/customers'] = ['template' => 'hotel-booking/_cp/index.twig'];
            },
        );
    }

    /**
     * @return array<string, array{label: string, nested?: array<string, mixed>}>
     */
    private function permissions(): array
    {
        return [
            self::PERMISSION_VIEW_BOOKINGS => [
                'label' => Craft::t('hotel-booking', 'View bookings'),
                'nested' => [
                    self::PERMISSION_MANAGE_BOOKINGS => [
                        'label' => Craft::t('hotel-booking', 'Manage bookings'),
                        'nested' => [
                            self::PERMISSION_DELETE_BOOKINGS => [
                                'label' => Craft::t('hotel-booking', 'Delete bookings'),
                            ],
                        ],
                    ],
                    self::PERMISSION_CHANGE_BOOKING_STATUS => [
                        'label' => Craft::t('hotel-booking', 'Change booking status'),
                    ],
                ],
            ],
            self::PERMISSION_VIEW_CUSTOMERS => [
                'label' => Craft::t('hotel-booking', 'View customers'),
                'nested' => [
                    self::PERMISSION_MANAGE_CUSTOMERS => [
                        'label' => Craft::t('hotel-booking', 'Manage customers'),
                        'nested' => [
                            self::PERMISSION_DELETE_CUSTOMERS => [
                                'label' => Craft::t('hotel-booking', 'Delete customers'),
                            ],
                        ],
                    ],
                ],
            ],
            self::PERMISSION_MANAGE_SETTINGS => [
                'label' => Craft::t('hotel-booking', 'Manage Hotel Booking settings'),
            ],
        ];
    }
}
