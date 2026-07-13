<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;

abstract class BaseService
{
    /**
     * Handle service exceptions consistently
     */
    protected function handleException(Exception $e, string $context = '')
    {
        $message = $context ? "{$context}: {$e->getMessage()}" : $e->getMessage();
        
        Log::error($message, [
            'exception' => $e,
            'context' => $context,
            'trace' => $e->getTraceAsString()
        ]);
        
        throw new Exception($message);
    }

    /**
     * Log service activity
     */
    protected function logActivity(string $activity, array $data = [])
    {
        Log::info($activity, $data);
    }

    /**
     * Validate required parameters
     */
    protected function validateRequired(array $data, array $required)
    {
        $missing = [];
        
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $missing[] = $field;
            }
        }
        
        if (!empty($missing)) {
            throw new Exception('Missing required fields: ' . implode(', ', $missing));
        }
    }
}
