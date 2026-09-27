<?php

declare(strict_types=1);

namespace diegocostadev\hotelbooking\models;

use Craft;
use craft\base\Model;
use Money\Currencies\ISOCurrencies;
use Money\Currency;

/**
 * Hotel Booking settings.
 *
 * Any of these can be overridden from `config/hotel-booking.php`.
 */
class Settings extends Model
{
    /**
     * @var string ISO 4217 currency code used for all prices and bookings.
     */
    public string $currency = 'USD';

    /**
     * @var string Default check-in time (24h `H:i`, hotel local time).
     */
    public string $checkInTime = '14:00';

    /**
     * @var string Default check-out time (24h `H:i`, hotel local time).
     */
    public string $checkOutTime = '11:00';

    /**
     * @var string Prefix for booking references, e.g. `HB` → `HB-20260926-0001`.
     */
    public string $referencePrefix = 'HB';

    /**
     * @var int Minutes after which an unconfirmed (`pending`) booking expires and frees its rooms.
     */
    public int $pendingExpiryMinutes = 60;

    /**
     * @param array<string, mixed> $values
     */
    public function setAttributes($values, $safeOnly = true): void
    {
        if (isset($values['currency']) && is_string($values['currency'])) {
            $values['currency'] = strtoupper(trim($values['currency']));
        }
        if (isset($values['referencePrefix']) && is_string($values['referencePrefix'])) {
            $values['referencePrefix'] = strtoupper(trim($values['referencePrefix']));
        }

        parent::setAttributes($values, $safeOnly);
    }

    /**
     * @return array<int, array<int|string, mixed>>
     */
    protected function defineRules(): array
    {
        return [
            [['currency', 'checkInTime', 'checkOutTime', 'referencePrefix', 'pendingExpiryMinutes'], 'required'],
            ['currency', 'validateCurrency'],
            [['checkInTime', 'checkOutTime'], 'match', 'pattern' => '/^([01]\d|2[0-3]):[0-5]\d$/'],
            ['referencePrefix', 'match', 'pattern' => '/^[A-Z0-9]{1,8}$/'],
            ['pendingExpiryMinutes', 'integer', 'min' => 5, 'max' => 10080],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributeLabels(): array
    {
        return [
            'currency' => Craft::t('hotel-booking', 'Currency'),
            'checkInTime' => Craft::t('hotel-booking', 'Default check-in time'),
            'checkOutTime' => Craft::t('hotel-booking', 'Default check-out time'),
            'referencePrefix' => Craft::t('hotel-booking', 'Booking reference prefix'),
            'pendingExpiryMinutes' => Craft::t('hotel-booking', 'Pending booking expiry (minutes)'),
        ];
    }

    public function validateCurrency(string $attribute): void
    {
        $code = $this->$attribute;

        if (!preg_match('/^[A-Z]{3}$/', $code) || !(new ISOCurrencies())->contains(new Currency($code))) {
            $this->addError($attribute, Craft::t('hotel-booking', '{value} is not a valid ISO 4217 currency code.', [
                'value' => $code,
            ]));
        }
    }
}
