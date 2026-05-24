<?php
use App\Models\Setting;
use App\Models\Currency;
use App\Events\NotificationEvent;
use App\Services\CurrencyService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;

if (!function_exists('slug_encode')) {
    function slug_encode($id)
    {
        return  md5($id).base64_encode((string)$id);
    }
}

if (!function_exists('slug_decode')) {
    function slug_decode($slug)
    {
        $id = substr($slug,32);
        return (int)base64_decode($id);
    }
}

if (!function_exists('safe_string')) {
    /**
     * Convert any value to a safe string for display
     * Handles arrays by joining them, null values return empty string
     */
    function safe_string($value, $separator = ', ')
    {
        if ($value === null) {
            return '';
        }

        if (is_array($value)) {
            return implode($separator, array_filter($value));
        }

        if (is_object($value)) {
            return method_exists($value, '__toString') ? (string)$value : '';
        }

        return (string)$value;
    }
}

if (!function_exists('uploadFile')) {
    /**
     * Handles file uploads for single or multiple files.
     *
     * @param mixed $files The file(s) to upload (single or array).
     * @param string $path The directory where the file(s) should be uploaded.
     * @param string|null $oldFile The old file path for deletion (optional).
     * @return array|string|null The uploaded file path(s) or null on failure.
     */
    function uploadFile($files, $path, $oldFile = null)
    {
        // Delete the old file if provided
        if ($oldFile && Storage::disk('public')->exists($oldFile)) {
            Storage::disk('public')->delete($oldFile);
        }

        // Handle multiple files
        if (is_array($files)) {
            $uploadedPaths = [];
            foreach ($files as $file) {
                if ($file instanceof UploadedFile) {
                    $uploadedPaths[] = $file->store($path, 'public');
                }
            }
            return $uploadedPaths;
        }

        // Handle single file
        if ($files instanceof UploadedFile) {
            return $files->store($path, 'public');
        }

        return null;
    }
}

// unlink file common function
function unlinkFile($filePath) {
    if (file_exists($filePath)) {
        unlink($filePath);
        return true;
    }
    return false;
}

if (!function_exists('user')) {

    /**
     * Get the authenticated user instance
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    function user()
    {
        return Auth::user();
    }
}

if (!function_exists('setting')) {
    /**
     * Get single setting by key, returns default if not found or inactive
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function setting(string $key, $default = null)
    {
        try {
            $row = Setting::where('key', $key)->where('is_active', true)->first();
            if (!$row) return $default;
            return $row->value;
        } catch (\Exception $e) {
            return $default;
        }
    }
}

if (!function_exists('company_info')) {
    /**
     * Get all active company group settings as key => value array
     * @return array
     */
    function company_info(): array
    {
        try {
            return \App\Models\Setting::getCompanyInfo();
        } catch (\Exception $e) {
            return [];
        }
    }
}


if (!function_exists('sendNotification')) {

    /**
     * Send a notification to multiple users.
     *
     * @param array  $users     An array of user instances or user IDs.
     * @param string $title     The title of the notification.
     * @param string $message   The message body of the notification (optional).
     * @param string $web_url   A web URL associated with the notification (optional).
     * @param string $app_url   An app URL associated with the notification (optional).
     * @param string $platform  The platform on which to send the notification ('all' by default).
     */
    function sendNotification(array $users, $title = null, $notify_app_title = null,  $message = null, $web_url = null, $app_url = null, $platform = 'all') {
        // foreach ($users as $user) {
        //     $notification = Notification::create([
        //         'user_id' => $user,
        //         'title' => $title,
        //         'app_title' => $notify_app_title,
        //         'message' => $message,
        //         'web_url' => $web_url,
        //         'app_url' => $app_url,
        //         'platform' => $platform,
        //         'created_by' => auth()->id(),
        //     ]);

        //     event(new NotificationEvent($notification));
        // }
    }
}

if (!function_exists('number_format')) {
    function number_format($amount)
    {
        // dump($amount);
        if ($amount < 0) {
            $formattedAmount = rtrim(number_format(abs($amount), 4), '0');
            // Check if the formatted amount has decimal places
            if (strpos($formattedAmount, '.') !== false) {
                // Remove trailing zeros after the decimal point
                $formattedAmount = rtrim($formattedAmount, '.');
            }
            return '<span style="color: #d33333;">(' . $formattedAmount . ')</span>';
        } elseif ($amount > 0) {
            $formattedAmount = rtrim(number_format($amount, 4), '0');
            // Check if the formatted amount has decimal places
            if (strpos($formattedAmount, '.') !== false) {
                // Remove trailing zeros after the decimal point
                $formattedAmount = rtrim($formattedAmount, '.');
            }
            return $formattedAmount;
        } else {
            return '-';
        }
        // if ($amount<0) {
        //     return '<span style="color: #d33333;">(' . number_format(abs($amount), 2) . ')</span>';
        // } else {
        //     return number_format($amount, 2);
        // }
    }
}

function convertNumberToWords($num) {
    $ones = array(
        0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
        6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
        11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen',
        15 => 'Fifteen', 16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen'
    );
    $tens = array(
        0 => '', 2 => 'Twenty', 3 => 'Thirty', 4 => 'Forty', 5 => 'Fifty',
        6 => 'Sixty', 7 => 'Seventy', 8 => 'Eighty', 9 => 'Ninety'
    );

    $num = (int)$num;

    if ($num == 0) {
        return 'Zero';
    }

    $crores = floor($num / 10000000);
    $num -= $crores * 10000000;

    $lakhs = floor($num / 100000);
    $num -= $lakhs * 100000;

    $thousands = floor($num / 1000);
    $num -= $thousands * 1000;

    $hundreds = floor($num / 100);
    $num -= $hundreds * 100;

    $tens_ones = $num;

    $result = '';

    if ($crores) {
        $result .= convertNumberToWords($crores) . ' Crore ';
    }

    if ($lakhs) {
        $result .= convertNumberToWords($lakhs) . ' Lakh ';
    }

    if ($thousands) {
        $result .= convertNumberToWords($thousands) . ' Thousand ';
    }

    if ($hundreds) {
        $result .= convertNumberToWords($hundreds) . ' Hundred ';
    }

    if ($tens_ones) {
        if ($tens_ones < 20) {
            $result .= $ones[$tens_ones] . ' ';
        } else {
            $result .= $tens[floor($tens_ones / 10)] . ' ' . $ones[$tens_ones % 10] . ' ';
        }
    }

    return trim($result);
}

if (!function_exists('format_date')) {
    function format_date($date, $format = 'd M, Y')
    {
        if (empty($date)) return null;

        try {
            return \Carbon\Carbon::parse($date)->format($format);
        } catch (\Exception $e) {
            return $date;
        }
    }
}

if (!function_exists('format_datetime')) {
    function format_datetime($date, $format = 'd M Y h:i A')
    {
        if (empty($date)) return null;

        try {
            return \Carbon\Carbon::parse($date)->format($format);
        } catch (\Exception $e) {
            return $date;
        }
    }
}

if (!function_exists('format_time')) {
    function format_time($time, $format = 'h:i A')
    {
        if (empty($time)) return null;

        try {
            return \Carbon\Carbon::parse($time)->format($format);
        } catch (\Exception $e) {
            return $time;
        }
    }
}

if (!function_exists('total_price')) {
    function total_price_json_decode($prices): float
    {
        if (!is_array($prices)) {
            $prices = json_decode($prices, true);
        }

        return is_array($prices) ? array_sum($prices) : 0;
    }
}

if (!function_exists('json_decode_array')) {
    function json_decode_array($values): array
    {
        if (!is_array($values)) {
            $values = json_decode($values, true);
        }

        return is_array($values) ? $values : [];
    }
}

if (!function_exists('total_price_in_words')) {
    function total_price_in_words($price, $currency = 'EGP', $locale = null)
    {
        $total = is_array($price) ? array_sum($price) : (float) $price;

        // Get current locale if not provided
        if (!$locale) {
            $locale = app()->getLocale();
        }

        // Map locales to NumberFormatter locales
        $numberFormatterLocale = 'en_US';
        if ($locale === 'ar') {
            $numberFormatterLocale = 'ar_EG';
        } elseif ($locale === 'en') {
            $numberFormatterLocale = 'en_US';
        }

        // Currency names in different languages
        $currencyNames = [
            'en' => [
                'EGP' => 'Egyptian Pounds',
                'USD' => 'US Dollars',
                'EUR' => 'Euros',
                'GBP' => 'British Pounds',
                'SAR' => 'Saudi Riyals',
                'AED' => 'UAE Dirhams',
                'BDT' => 'Bangladeshi Taka',
                'INR' => 'Indian Rupees',
            ],
            'ar' => [
                'EGP' => 'جنيه مصري',
                'USD' => 'دولار أمريكي',
                'EUR' => 'يورو',
                'GBP' => 'جنيه إسترليني',
                'SAR' => 'ريال سعودي',
                'AED' => 'درهم إماراتي',
                'BDT' => 'تاكا بنغلاديشي',
                'INR' => 'روبية هندية',
            ]
        ];

        $currencyName = $currencyNames[$locale][$currency] ?? $currencyNames['en'][$currency] ?? $currency;

        if ($total == 0) {
            $zeroText = __('payroll.zero');
            $onlyText = __('payroll.only');
            return "{$zeroText} {$currencyName} {$onlyText}";
        }

        try {
            $f = new \NumberFormatter($numberFormatterLocale, \NumberFormatter::SPELLOUT);
            $in_words = ucfirst($f->format($total));

            $onlyText = __('payroll.only');
            return "{$in_words} {$currencyName} {$onlyText}";

        } catch (\Exception $e) {
            // Fallback to English if locale not supported
            $f = new \NumberFormatter('en_US', \NumberFormatter::SPELLOUT);
            $in_words = ucfirst($f->format($total));
            $currencyName = $currencyNames['en'][$currency] ?? $currency;
            return "{$in_words} {$currencyName} Only";
        }
    }
}

if (!function_exists('getLanguageLevelPercentage')) {
    function getLanguageLevelPercentage($level) {
        $levels = [
            'beginner' => '30%',
            'intermediate' => '60%',
            'advanced' => '90%',
            'native' => '100%'
        ];
        return $levels[strtolower($level)] ?? '0%';
    }
}

if (!function_exists('getCurrencySymbol')) {
    /**
     * Get currency symbol by currency code
     *
     * @param string $currencyCode
     * @return string
     */
    function getCurrencySymbol($currencyCode = 'USD') {
        $currencies = [
            'USD' => '$',      // US Dollar
            'EUR' => '€',      // Euro
            'GBP' => '£',      // British Pound
            'EGP' => 'E£',     // Egyptian Pound
            'SAR' => '﷼',      // Saudi Riyal
            'AED' => 'د.إ',     // UAE Dirham
            'BDT' => '৳',      // Bangladeshi Taka
            'INR' => '₹',      // Indian Rupee
            'JPY' => '¥',      // Japanese Yen
            'CNY' => '¥',      // Chinese Yuan
            'CHF' => 'Fr',     // Swiss Franc
            'CAD' => 'C$',     // Canadian Dollar
            'AUD' => 'A$',     // Australian Dollar
            'PKR' => '₨',      // Pakistani Rupee
            'LKR' => 'Rs',     // Sri Lankan Rupee
            'MYR' => 'RM',     // Malaysian Ringgit
            'SGD' => 'S$',     // Singapore Dollar
            'THB' => '฿',      // Thai Baht
            'VND' => '₫',      // Vietnamese Dong
        ];

        return $currencies[strtoupper($currencyCode)] ?? $currencyCode;
    }
}

if (!function_exists('formatCurrency')) {
    /**
     * Format amount with currency symbol - Hospital System Enhanced
     *
     * @param float $amount
     * @param string|null $currencyCode
     * @param int $decimals
     * @param array $options
     * @return string
     */
    function formatCurrency($amount, $currencyCode = null, $decimals = 2, $options = []) {
        try {
            $currencyService = app(CurrencyService::class);

            if ($currencyCode) {
                // Try to get currency from database first
                $currency = Currency::where('code', $currencyCode)
                    ->where('is_active', true)
                    ->first();

                if ($currency) {
                    return $currency->formatAmount($amount, array_merge(['decimals' => $decimals], $options));
                }

                // Fallback for specific currency code
                $symbol = getCurrencySymbol($currencyCode);
                $showSymbol = $options['show_symbol'] ?? true;
                $showCode = $options['show_code'] ?? false;

                $formatted = number_format($amount, $decimals);

                if ($showSymbol) {
                    $formatted = $symbol . $formatted;
                }

                if ($showCode) {
                    $formatted .= ' ' . $currencyCode;
                }

                return $formatted;
            }

            // Use business currency
            return $currencyService->format($amount, array_merge(['decimals' => $decimals], $options));

        } catch (\Exception $e) {
            // Enhanced fallback formatting
            $symbol = $currencyCode ? getCurrencySymbol($currencyCode) : '$';
            $showSymbol = $options['show_symbol'] ?? true;
            $formatted = number_format($amount, $decimals);

            return $showSymbol ? $symbol . $formatted : $formatted;
        }
    }
}

if (!function_exists('formatBusinessCurrency')) {
    /**
     * Format amount with current business currency - Hospital System Enhanced
     *
     * @param float $amount
     * @param array $options
     * @return string
     */
    function formatBusinessCurrency($amount, $options = []) {
        try {
            $currencyService = app(CurrencyService::class);
            return $currencyService->format($amount, $options);
        } catch (\Exception $e) {
            $decimals = $options['decimals'] ?? 2;
            $showSymbol = $options['show_symbol'] ?? true;
            $formatted = number_format($amount, $decimals);

            return $showSymbol ? '$' . $formatted : $formatted;
        }
    }
}

if (!function_exists('getBusinessCurrencySymbol')) {
    /**
     * Get current business currency symbol
     *
     * @return string
     */
    function getBusinessCurrencySymbol() {
        try {
            $currencyService = app(CurrencyService::class);
            return $currencyService->getSymbol();
        } catch (\Exception $e) {
            return '$';
        }
    }
}

if (!function_exists('getBusinessCurrencyCode')) {
    /**
     * Get current business currency code
     *
     * @return string
     */
    function getBusinessCurrencyCode() {
        try {
            $currencyService = app(CurrencyService::class);
            return $currencyService->getCode();
        } catch (\Exception $e) {
            return 'USD';
        }
    }
}

if (!function_exists('getBusinessCurrencyName')) {
    /**
     * Get current business currency name
     *
     * @return string
     */
    function getBusinessCurrencyName() {
        try {
            $currencyService = app(CurrencyService::class);
            return $currencyService->getName();
        } catch (\Exception $e) {
            return 'US Dollar';
        }
    }
}

if (!function_exists('getCurrencyOptions')) {
    /**
     * Get available currency options for dropdowns - Hospital System Enhanced
     *
     * @param bool $includeDatabase Include currencies from database
     * @return array
     */
    function getCurrencyOptions($includeDatabase = false) {
        $staticOptions = [
            'USD' => 'US Dollar ($)',
            'EUR' => 'Euro (€)',
            'GBP' => 'British Pound (£)',
            'EGP' => 'Egyptian Pound (E£)',
            'SAR' => 'Saudi Riyal (﷼)',
            'AED' => 'UAE Dirham (د.إ)',
            'BDT' => 'Bangladeshi Taka (৳)',
            'INR' => 'Indian Rupee (₹)',
            'JPY' => 'Japanese Yen (¥)',
            'CNY' => 'Chinese Yuan (¥)',
            'CHF' => 'Swiss Franc (Fr)',
            'CAD' => 'Canadian Dollar (C$)',
            'AUD' => 'Australian Dollar (A$)',
            'PKR' => 'Pakistani Rupee (₨)',
            'LKR' => 'Sri Lankan Rupee (Rs)',
            'MYR' => 'Malaysian Ringgit (RM)',
            'SGD' => 'Singapore Dollar (S$)',
            'THB' => 'Thai Baht (฿)',
            'VND' => 'Vietnamese Dong (₫)',
        ];

        if ($includeDatabase) {
            try {
                $currencyService = app(CurrencyService::class);
                $dbOptions = $currencyService->getCurrencyOptions();

                // Merge database currencies with static options, giving preference to database
                return array_merge($staticOptions, $dbOptions);
            } catch (\Exception $e) {
                return $staticOptions;
            }
        }

        return $staticOptions;
    }
}

if (!function_exists('formatHospitalAmount')) {
    /**
     * Format amount specifically for hospital billing - includes context
     *
     * @param float $amount
     * @param string $context (invoice, payment, pharmacy, consultation, lab)
     * @param array $options
     * @return string
     */
    function formatHospitalAmount($amount, $context = 'general', $options = []) {
        $defaults = [
            'decimals' => 2,
            'show_symbol' => true,
            'show_code' => false,
            'add_context' => false
        ];

        $options = array_merge($defaults, $options);

        $formatted = formatBusinessCurrency($amount, $options);

        if ($options['add_context']) {
            $contextLabels = [
                'invoice' => 'Invoice Amount',
                'payment' => 'Payment',
                'pharmacy' => 'Pharmacy Bill',
                'consultation' => 'Consultation Fee',
                'lab' => 'Lab Charges',
                'admission' => 'Admission Fee',
                'room' => 'Room Charges',
                'procedure' => 'Procedure Cost'
            ];

            $label = $contextLabels[$context] ?? 'Amount';
            $formatted = $label . ': ' . $formatted;
        }

        return $formatted;
    }
}

if (!function_exists('convertCurrency')) {
    /**
     * Convert amount between currencies using exchange rates
     *
     * @param float $amount
     * @param string $fromCurrency
     * @param string $toCurrency
     * @return float
     */
    function convertCurrency($amount, $fromCurrency, $toCurrency) {
        try {
            $currencyService = app(CurrencyService::class);

            $fromCurrencyModel = Currency::where('code', $fromCurrency)->where('is_active', true)->first();
            $toCurrencyModel = Currency::where('code', $toCurrency)->where('is_active', true)->first();

            if ($fromCurrencyModel && $toCurrencyModel) {
                return $currencyService->convert($amount, $fromCurrencyModel, $toCurrencyModel);
            }

            return $amount; // If currencies not found, return original amount
        } catch (\Exception $e) {
            return $amount;
        }
    }
}

if (!function_exists('formatPatientBill')) {
    /**
     * Format complete patient bill with breakdown
     *
     * @param array $billData
     * @return array
     */
    function formatPatientBill($billData) {
        $formatted = [
            'subtotal' => formatBusinessCurrency($billData['subtotal'] ?? 0),
            'tax_amount' => formatBusinessCurrency($billData['tax_amount'] ?? 0),
            'discount_amount' => formatBusinessCurrency($billData['discount_amount'] ?? 0),
            'total_amount' => formatBusinessCurrency($billData['total_amount'] ?? 0),
            'paid_amount' => formatBusinessCurrency($billData['paid_amount'] ?? 0),
            'balance_amount' => formatBusinessCurrency($billData['balance_amount'] ?? 0),
        ];

        // Add context for display
        $formatted['currency_code'] = getBusinessCurrencyCode();
        $formatted['currency_symbol'] = getBusinessCurrencySymbol();
        $formatted['currency_name'] = getBusinessCurrencyName();

        return $formatted;
    }
}

if (!function_exists('getCurrencyForJs')) {
    /**
     * Get currency configuration for JavaScript/frontend
     *
     * @return array
     */
    function getCurrencyForJs() {
        try {
            $currencyService = app(CurrencyService::class);
            return $currencyService->getJsConfig();
        } catch (\Exception $e) {
            return [
                'symbol' => '$',
                'code' => 'USD',
                'name' => 'US Dollar',
                'decimals' => 2,
                'exchange_rate' => 1.0000
            ];
        }
    }
}

if (!function_exists('generate_barcode_base64')) {
    /**
     * Generate a base64 PNG barcode image for a given code.
     *
     * @param string $code The content to encode in the barcode (e.g., SKU or barcode value)
     * @param string $type One of: code128, code39, ean13, ean8, upc, itf
     * @param int $scale Horizontal scale multiplier (bar width)
     * @param int $height Barcode image height in pixels
     * @return string data URI suitable for <img src="...">
     */
    function generate_barcode_base64(string $code, string $type = 'code128', int $scale = 2, int $height = 60): string
    {
        try {
            // Lazy load Picqer only when needed
            $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();

            // Map friendly types to Picqer constants
            $map = [
                'code128' => $generator::TYPE_CODE_128,
                'code39'  => $generator::TYPE_CODE_39,
                'ean13'   => $generator::TYPE_EAN_13,
                'ean8'    => $generator::TYPE_EAN_8,
                'upc'     => $generator::TYPE_UPC_A,
                'itf'     => $generator::TYPE_ITF_14,
            ];

            $const = $map[strtolower($type)] ?? $generator::TYPE_CODE_128;

            $png = $generator->getBarcode($code, $const, $scale, $height);
            return 'data:image/png;base64,' . base64_encode($png);
        } catch (\Throwable $e) {
            // Fallback: return empty transparent PNG if generation fails
            $empty = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVQIW2P8z8DwHwAFngJ1bq0p9gAAAABJRU5ErkJggg==');
            return 'data:image/png;base64,' . base64_encode($empty);
        }
    }
}

if (!function_exists('bool_label')) {
    /**
     * Returns simple Yes/No label for booleans
     */
    function bool_label($val): string
    {
        return $val ? 'Yes' : 'No';
    }
}

// Toast notification helper functions
if (!function_exists('toast_success')) {
    /**
     * Flash a success toast message to session
     */
    function toast_success($message)
    {
        session()->flash('toast_success', $message);
    }
}

if (!function_exists('toast_error')) {
    /**
     * Flash an error toast message to session
     */
    function toast_error($message)
    {
        session()->flash('toast_error', $message);
    }
}

if (!function_exists('toast_warning')) {
    /**
     * Flash a warning toast message to session
     */
    function toast_warning($message)
    {
        session()->flash('toast_warning', $message);
    }
}

if (!function_exists('toast_info')) {
    /**
     * Flash an info toast message to session
     */
    function toast_info($message)
    {
        session()->flash('toast_info', $message);
    }
}

// Hospital-specific currency helper functions
if (!function_exists('formatInvoiceAmount')) {
    /**
     * Format invoice amounts with proper currency and context
     *
     * @param \App\Models\Invoice $invoice
     * @param string $field
     * @return string
     */
    function formatInvoiceAmount($invoice, $field = 'total_amount') {
        if (!$invoice || !isset($invoice->$field)) {
            return formatBusinessCurrency(0);
        }

        return formatBusinessCurrency($invoice->$field);
    }
}

if (!function_exists('formatPaymentAmount')) {
    /**
     * Format payment amounts with context
     *
     * @param \App\Models\Payment $payment
     * @return string
     */
    function formatPaymentAmount($payment) {
        if (!$payment) {
            return formatBusinessCurrency(0);
        }

        return formatBusinessCurrency($payment->amount);
    }
}

if (!function_exists('formatAmountInWords')) {
    /**
     * Format amount in words using business currency
     *
     * @param float $amount
     * @param string|null $locale
     * @return string
     */
    function formatAmountInWords($amount, $locale = null) {
        try {
            $currencyService = app(CurrencyService::class);
            return $currencyService->formatInWords($amount, $locale);
        } catch (\Exception $e) {
            return total_price_in_words($amount, getBusinessCurrencyCode(), $locale);
        }
    }
}

if (!function_exists('isRouteSection')) {
    /**
     * Check if the current route belongs to a specific section
     *
     * @param string $sectionPrefix The route prefix to check (e.g., 'users.', 'modules.', 'roles.')
     * @return bool
     */
    function isRouteSection($sectionPrefix)
    {
        // Safe-get current route name (avoid getName on null)
        $current = optional(request()->route())->getName();
        if (!$current) return false;

        // If the prefix ends with '.' assume a simple section prefix like 'users.'
        if (str_ends_with($sectionPrefix, '.')) {
            return \Str::startsWith($current, $sectionPrefix);
        }

        // Support both 'users' and 'users.' or 'users.*' patterns
        if (\Str::startsWith($current, $sectionPrefix . '.')) {
            return true;
        }

        // Fallback: exact match or pattern match
        return $current === $sectionPrefix || request()->routeIs($sectionPrefix);
    }
}

if (!function_exists('isMultiRoute')) {
    /**
     * Check if the current route belongs to any of the specified sections
     *
     * @param array $routePrefixes Array of route prefixes to check (e.g., ['users.', 'modules.', 'roles.'])
     * @return bool
     */
    function isMultiRoute($routePrefixes = [])
    {
        foreach ($routePrefixes as $prefix) {
            if (isRouteSection($prefix)) {
                return true;
            }
        }
        return false;
    }
}

    // Age calculation from date of birth
    if (!function_exists('calculateAgeFromDob')) {
        function calculateAgeFromDob($dob): string
        {
            if (empty($dob)) {
                return 'N/A';
            }

            try {
                $birthDate = Carbon::parse($dob)->startOfDay();
                $today = Carbon::now()->startOfDay();

                if ($birthDate->gt($today)) {
                    return 'N/A';
                }

                $age = $birthDate->diff($today);

                return "{$age->y}Y {$age->m}M {$age->d}D";
            } catch (\Exception $e) {
                return 'N/A';
            }
        }
    }
