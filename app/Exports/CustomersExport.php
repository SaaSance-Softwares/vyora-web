<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CustomersExport implements FromQuery, WithHeadings, WithMapping
{
    /**
     * Fetch the query used to export data.
     */
    public function query()
    {
        return User::query()
            ->where(function ($q) {
                $q->whereIn('role', ['customer', 'user'])
                  ->orWhereNull('role')
                  ->orWhere('role', '');
            })
            ->withSum('orders', 'total_amount')
            ->with(['addresses' => function ($query) {
                // Ensure we get addresses sorted or default ones prioritized
                $query->orderByDesc('is_default');
            }])
            ->latest();
    }

    /**
     * Define the headings for the export.
     */
    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Email',
            'Phone',
            'City',
            'State',
            'Country',
            'Total Spend',
            'Joined Date',
            'Social Login Provider',
            'Provider ID',
            'Consented To Terms',
            'Consented To Marketing',
            'Consent Timestamp',
            'Consent IP',
            'Registration IP',
            'Default Shipping Address',
            'Default Billing Address',
        ];
    }

    /**
     * Map each user row to the export columns.
     */
    public function map($user): array
    {
        return [
            $user->id,
            $user->name,
            $user->email,
            $user->phone,
            $user->city,
            $user->state,
            $user->country,
            $user->orders_sum_total_amount ?? 0,
            $user->created_at ? $user->created_at->format('Y-m-d H:i:s') : '',
            $user->provider,
            $user->provider_id,
            $user->has_consented_to_terms ? 'Yes' : 'No',
            $user->has_consented_to_marketing ? 'Yes' : 'No',
            $user->consent_timestamp ? $user->consent_timestamp->format('Y-m-d H:i:s') : '',
            $user->consent_ip_address,
            $user->registration_ip,
            $this->formatAddress($user->addresses->where('type', 'shipping')->first() ?? $user->addresses->first()),
            $this->formatAddress($user->addresses->where('type', 'billing')->first() ?? $user->addresses->first()),
        ];
    }

    /**
     * Format an address model into a single line string.
     */
    private function formatAddress($address)
    {
        if (!$address) {
            return '';
        }

        $parts = [];
        
        if ($address->name) $parts[] = $address->name;
        if ($address->phone) $parts[] = "Ph: {$address->phone}";
        if ($address->address_line1) $parts[] = $address->address_line1;
        if ($address->address_line2) $parts[] = $address->address_line2;
        
        $location = array_filter([$address->city, $address->state, $address->zip_code]);
        if (!empty($location)) {
            $parts[] = implode(' ', $location);
        }
        
        if ($address->country) $parts[] = $address->country;

        return implode(', ', $parts);
    }
}
