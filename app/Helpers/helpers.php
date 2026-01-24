<?php

use Illuminate\Support\Carbon;
use App\ValueObjects\Money;

/**
 * Helper Functions for NightmareMart
 * 
 * Episode 7: Timezone handling helpers
 * Episode 10: Money formatting helpers
 */

if (!function_exists('userTime')) {
    /**
     * Convert datetime to user's timezone
     * 
     * Episode 7: Proper timezone handling
     * Always store in UTC, display in user's timezone
     *
     * @param Carbon|string $datetime
     * @param string|null $timezone Optional override
     * @return Carbon
     */
    function userTime($datetime, ?string $timezone = null): Carbon
    {
        if (is_string($datetime)) {
            $datetime = Carbon::parse($datetime);
        }

        $tz = $timezone ?? auth()->user()?->timezone ?? 'UTC';

        return $datetime->timezone($tz);
    }
}

if (!function_exists('formatUserTime')) {
    /**
     * Format datetime for display in user's timezone
     *
     * @param Carbon|string $datetime
     * @param string $format
     * @param string|null $timezone
     * @return string
     */
    function formatUserTime($datetime, string $format = 'M j, Y g:i A', ?string $timezone = null): string
    {
        return userTime($datetime, $timezone)->format($format);
    }
}

if (!function_exists('formatMoney')) {
    /**
     * Format cents as money display
     * 
     * Episode 10: Proper money formatting
     *
     * @param int $cents
     * @param string $symbol
     * @return string
     */
    function formatMoney(int $cents, string $symbol = '$'): string
    {
        return $symbol . number_format($cents / 100, 2);
    }
}

if (!function_exists('money')) {
    /**
     * Create Money object from cents
     * 
     * @param int $cents
     * @return Money
     */
    function money(int $cents): Money
    {
        return Money::fromCents($cents);
    }
}

if (!function_exists('moneyFromDollars')) {
    /**
     * Create Money object from dollars
     * 
     * @param float $dollars
     * @return Money
     */
    function moneyFromDollars(float $dollars): Money
    {
        return Money::fromDollars($dollars);
    }
}

if (!function_exists('getTimezoneList')) {
    /**
     * Get list of timezones for user selection
     *
     * @return array
     */
    function getTimezoneList(): array
    {
        return [
            'UTC' => 'UTC (Coordinated Universal Time)',
            'America/New_York' => 'Eastern Time (US & Canada)',
            'America/Chicago' => 'Central Time (US & Canada)',
            'America/Denver' => 'Mountain Time (US & Canada)',
            'America/Los_Angeles' => 'Pacific Time (US & Canada)',
            'America/Anchorage' => 'Alaska',
            'Pacific/Honolulu' => 'Hawaii',
            'Europe/London' => 'London',
            'Europe/Paris' => 'Paris',
            'Europe/Berlin' => 'Berlin',
            'Europe/Moscow' => 'Moscow',
            'Asia/Dubai' => 'Dubai',
            'Asia/Kolkata' => 'India',
            'Asia/Bangkok' => 'Bangkok',
            'Asia/Singapore' => 'Singapore',
            'Asia/Hong_Kong' => 'Hong Kong',
            'Asia/Tokyo' => 'Tokyo',
            'Asia/Seoul' => 'Seoul',
            'Australia/Sydney' => 'Sydney',
            'Australia/Melbourne' => 'Melbourne',
            'Pacific/Auckland' => 'Auckland',
        ];
    }
}
