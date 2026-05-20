<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class CurrencyService
{
    /**
     * Get current business currency (adapted for hospital system)
     */
    public function getCurrentBusinessCurrency(): ?Currency
    {
        // For hospital system, check if there's a configured currency in settings
        $configuredCurrency = $this->getConfiguredCurrency();
        if ($configuredCurrency) {
            return $configuredCurrency;
        }

        // Fallback to default currency
        return $this->getDefaultCurrency();
    }

    /**
     * Get configured currency from hospital settings
     */
    public function getConfiguredCurrency(): ?Currency
    {
        try {
            $currencyCode = Setting::where('key', 'default_currency')->value('value');
            if ($currencyCode) {
                return Cache::remember("configured_currency_{$currencyCode}", 60, function () use ($currencyCode) {
                    return Currency::where('code', $currencyCode)
                        ->where('is_active', true)
                        ->first();
                });
            }
        } catch (\Exception $e) {
            // Settings table might not exist yet
        }

        return null;
    }

    /**
     * Get default system currency
     */
    public function getDefaultCurrency(): ?Currency
    {
        return Cache::remember('default_currency', 60, function () {
            return Currency::where('is_default', true)->where('is_active', true)->first()
                ?? Currency::where('is_active', true)->first();
        });
    }

    /**
     * Get current location currency (removed - not applicable for hospital)
     */
    public function getCurrentLocationCurrency(): ?Currency
    {
        return null; // Not applicable for hospital system
    }

    /**
     * Format amount with current business currency
     */
    public function format($amount, $options = []): string
    {
        $currency = $this->getCurrentBusinessCurrency();
        
        if ($currency) {
            return $currency->formatAmount($amount, $options);
        }
        
        // Fallback formatting
        $decimals = $options['decimals'] ?? 2;
        return '$' . number_format($amount, $decimals);
    }

    /**
     * Format amount with specific currency
     */
    public function formatWith(Currency $currency, $amount, $options = []): string
    {
        return $currency->formatAmount($amount, $options);
    }

    /**
     * Get current business currency symbol
     */
    public function getSymbol(): string
    {
        $currency = $this->getCurrentBusinessCurrency();
        return $currency ? $currency->symbol : '$';
    }

    /**
     * Get current business currency code
     */
    public function getCode(): string
    {
        $currency = $this->getCurrentBusinessCurrency();
        return $currency ? $currency->code : 'USD';
    }

    /**
     * Get current business currency name
     */
    public function getName(): string
    {
        $currency = $this->getCurrentBusinessCurrency();
        return $currency ? $currency->name : 'US Dollar';
    }

    /**
     * Convert amount between currencies
     */
    public function convert($amount, Currency $from, Currency $to): float
    {
        if ($from->id === $to->id) {
            return $amount;
        }
        
        return $from->convertTo($amount, $to);
    }

    /**
     * Convert amount to current business currency
     */
    public function convertToBusinessCurrency($amount, Currency $fromCurrency): float
    {
        $businessCurrency = $this->getCurrentBusinessCurrency();
        
        if (!$businessCurrency || $fromCurrency->id === $businessCurrency->id) {
            return $amount;
        }
        
        return $fromCurrency->convertTo($amount, $businessCurrency);
    }

    /**
     * Get all active currencies
     */
    public function getActiveCurrencies()
    {
        return Cache::remember('active_currencies', 300, function () {
            return Currency::where('is_active', true)->orderBy('name')->get();
        });
    }

    /**
     * Get currency options for dropdowns
     */
    public function getCurrencyOptions(): array
    {
        return $this->getActiveCurrencies()
            ->pluck('display_name', 'id')
            ->toArray();
    }

    /**
     * Clear currency cache
     */
    public function clearCache(): void
    {
        Cache::forget('default_currency');
        Cache::forget('active_currencies');

        // Clear configured currency cache
        try {
            $currencyCode = Setting::where('key', 'default_currency')->value('value');
            if ($currencyCode) {
                Cache::forget("configured_currency_{$currencyCode}");
            }
        } catch (\Exception $e) {
            // Settings table might not exist yet
        }
    }

    /**
     * Format amount for JavaScript
     */
    public function formatForJs($amount): array
    {
        $currency = $this->getCurrentBusinessCurrency();
        
        return [
            'formatted' => $this->format($amount),
            'amount' => $amount,
            'symbol' => $currency ? $currency->symbol : '$',
            'code' => $currency ? $currency->code : 'USD',
            'decimals' => 2
        ];
    }

    /**
     * Get currency configuration for frontend
     */
    public function getJsConfig(): array
    {
        $currency = $this->getCurrentBusinessCurrency();
        
        return [
            'symbol' => $currency ? $currency->symbol : '$',
            'code' => $currency ? $currency->code : 'USD',
            'name' => $currency ? $currency->name : 'US Dollar',
            'decimals' => 2,
            'exchange_rate' => $currency ? $currency->exchange_rate : 1.0000
        ];
    }

    /**
     * Format amount in words
     */
    public function formatInWords($amount, $locale = null): string
    {
        $currency = $this->getCurrentBusinessCurrency();
        $currencyCode = $currency ? $currency->code : 'USD';
        
        return total_price_in_words($amount, $currencyCode, $locale);
    }

    /**
     * Validate currency exists and is active
     */
    public function isValidCurrency($currencyId): bool
    {
        return Currency::where('id', $currencyId)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Set default currency
     */
    public function setDefaultCurrency(Currency $currency): bool
    {
        try {
            // Remove default from all currencies
            Currency::where('is_default', true)->update(['is_default' => false]);
            
            // Set new default
            $currency->update(['is_default' => true, 'is_active' => true]);
            
            $this->clearCache();
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
