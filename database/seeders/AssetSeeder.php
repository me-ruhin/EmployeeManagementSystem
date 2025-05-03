<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\Employee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AssetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all employees
        $employees = Employee::all();
        
        // Asset types
        $assetTypes = [
            'Laptop' => [
                'makes' => ['Dell', 'HP', 'Lenovo', 'MacBook Pro', 'MacBook Air', 'Asus', 'Microsoft Surface'],
                'cost_range' => [800, 2500]
            ],
            'Desktop' => [
                'makes' => ['Dell', 'HP', 'Lenovo', 'Apple iMac', 'Custom Build'],
                'cost_range' => [600, 2000]
            ],
            'Mobile Phone' => [
                'makes' => ['iPhone', 'Samsung Galaxy', 'Google Pixel', 'OnePlus'],
                'cost_range' => [400, 1200]
            ],
            'Tablet' => [
                'makes' => ['iPad', 'Samsung Galaxy Tab', 'Microsoft Surface', 'Amazon Fire'],
                'cost_range' => [200, 1000]
            ],
            'Monitor' => [
                'makes' => ['Dell', 'LG', 'Samsung', 'BenQ', 'Acer', 'ASUS'],
                'cost_range' => [150, 800]
            ],
            'Docking Station' => [
                'makes' => ['Dell', 'HP', 'Lenovo', 'Kensington', 'Anker'],
                'cost_range' => [100, 350]
            ],
            'Keyboard' => [
                'makes' => ['Logitech', 'Microsoft', 'Apple', 'Corsair', 'Razer'],
                'cost_range' => [30, 150]
            ],
            'Mouse' => [
                'makes' => ['Logitech', 'Microsoft', 'Apple', 'Corsair', 'Razer'],
                'cost_range' => [20, 100]
            ],
            'Headset' => [
                'makes' => ['Jabra', 'Plantronics', 'Logitech', 'Sony', 'Bose'],
                'cost_range' => [50, 300]
            ],
            'Printer' => [
                'makes' => ['HP', 'Canon', 'Epson', 'Brother', 'Xerox'],
                'cost_range' => [200, 900]
            ]
        ];
        
        // Statuses
        $statuses = ['Available', 'Assigned', 'Under Maintenance', 'Retired'];
        
        // Create assets
        foreach ($employees as $employee) {
            // Most employees get a laptop
            $assignLaptop = rand(1, 100) <= 90; // 90% of employees get laptops
            if ($assignLaptop) {
                $this->createAsset('Laptop', $assetTypes, $employee, $statuses);
            }
            
            // Some employees get additional assets
            $additionalAssets = rand(0, 4); // 0-4 additional assets per employee
            
            for ($i = 0; $i < $additionalAssets; $i++) {
                $assetType = array_rand($assetTypes);
                
                // Skip laptop if already assigned one
                if ($assetType === 'Laptop' && $assignLaptop) {
                    $assetType = 'Monitor'; // Default fallback
                }
                
                $this->createAsset($assetType, $assetTypes, $employee, $statuses);
            }
            
            // Create some unassigned assets
            for ($i = 0; $i < 20; $i++) {
                $assetType = array_rand($assetTypes);
                $this->createAsset($assetType, $assetTypes, null, $statuses);
            }
        }
    }
    
    /**
     * Create an asset of specified type
     */
    private function createAsset($assetType, $assetTypes, $employee = null, $statuses = [])
    {
        // Get asset details
        $make = $assetTypes[$assetType]['makes'][array_rand($assetTypes[$assetType]['makes'])];
        $costRange = $assetTypes[$assetType]['cost_range'];
        $cost = rand($costRange[0], $costRange[1]);
        
        // Serial number
        $serial = strtoupper(substr($make, 0, 3)) . '-' . rand(100000, 999999);
        
        // Purchase date (0-4 years ago)
        $purchaseDate = Carbon::now()->subDays(rand(0, 4 * 365))->format('Y-m-d');
        
        // Calculate warranty end date (typically 3 years from purchase)
        $warrantyEndDate = Carbon::parse($purchaseDate)->addYears(3)->format('Y-m-d');
        
        // Determine status
        $status = $employee ? 'Assigned' : $statuses[array_rand($statuses)];
        
        // If retired, ensure purchase date makes sense
        if ($status === 'Retired') {
            $purchaseDate = Carbon::now()->subYears(rand(4, 6))->format('Y-m-d');
            $warrantyEndDate = Carbon::parse($purchaseDate)->addYears(3)->format('Y-m-d');
        }
        
        // Assignment date
        $assignmentDate = null;
        if ($status === 'Assigned') {
            $assignmentDate = Carbon::parse($purchaseDate)->addDays(rand(5, 30))->format('Y-m-d');
        }
        
        Asset::create([
            'id' => Str::uuid(),
            'asset_type' => $assetType,
            'make' => $make,
            'model' => $make . ' ' . rand(1000, 9999),
            'serial_number' => $serial,
            'purchase_date' => $purchaseDate,
            'purchase_cost' => $cost,
            'warranty_end_date' => $warrantyEndDate,
            'assigned_to' => $employee ? $employee->id : null,
            'assignment_date' => $assignmentDate,
            'condition' => $status === 'Retired' ? 'Poor' : ['Excellent', 'Good', 'Fair'][array_rand(['Excellent', 'Good', 'Fair'])],
            'notes' => $status === 'Under Maintenance' ? 'Sent for repair' : null,
            'status' => $status,
        ]);
    }
}