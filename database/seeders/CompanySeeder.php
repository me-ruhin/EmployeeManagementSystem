<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a few sample companies
        Company::create([
            'id' => Str::uuid(),
            'name' => 'Acme Corporation',
            'address' => '123 Main Street, Suite 100, New York, NY 10001',
            'contact_email' => 'info@acme.com',
            'contact_phone' => '+1-555-123-4567',
        ]);

        Company::create([
            'id' => Str::uuid(),
            'name' => 'TechInnovate Inc.',
            'address' => '456 Tech Avenue, San Francisco, CA 94107',
            'contact_email' => 'contact@techinnovate.com',
            'contact_phone' => '+1-555-987-6543',
        ]);

        Company::create([
            'id' => Str::uuid(),
            'name' => 'Global Solutions Ltd.',
            'address' => '789 Business Park, London, UK SW1A 1AA',
            'contact_email' => 'hello@globalsolutions.com',
            'contact_phone' => '+44-20-1234-5678',
        ]);
    }
}