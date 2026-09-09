<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ValidationMacroServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Request::macro('strictValidate', function (array $rules, ...$args) {
            $allowedKeys = collect($rules)->keys()->map(function ($key) {
                return explode('.', $key)[0];
            })->unique()->toArray();
            
            // Allow confirmation fields for rules that use 'confirmed'
            foreach ($rules as $field => $ruleStr) {
                if (is_string($ruleStr) && str_contains($ruleStr, 'confirmed')) {
                    $allowedKeys[] = explode('.', $field)[0] . '_confirmation';
                } elseif (is_array($ruleStr) && in_array('confirmed', $ruleStr)) {
                    $allowedKeys[] = explode('.', $field)[0] . '_confirmation';
                }
            }

            // Explicitly allow Laravel's standard hidden fields that are passed implicitly
            $allowedKeys = array_merge($allowedKeys, ['_token', '_method']);
            
            // Get keys from the incoming request
            $requestKeys = array_keys($this->all());
            
            // Find extra keys that are not allowed
            $extraKeys = array_diff($requestKeys, $allowedKeys);
            
            if (!empty($extraKeys)) {
                throw ValidationException::withMessages([
                    'request' => ['Invalid request payload: the following fields are not recognized or allowed (' . implode(', ', $extraKeys) . ').']
                ]);
            }
            
            // Proceed with standard validation, which will handle type, length, and format
            return $this->validate($rules, ...$args);
        });
    }
}
