<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\Employee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all employees
        $employees = Employee::all();
        
        // Expense categories
        $expenseCategories = [
            'Travel' => [
                'descriptions' => [
                    'Flight to {location} for client meeting',
                    'Hotel stay in {location} for conference',
                    'Taxi/Uber rides during business trip',
                    'Car rental for client visit',
                    'Parking fees during business trip'
                ],
                'amount_range' => [50, 1500]
            ],
            'Meals' => [
                'descriptions' => [
                    'Business lunch with client',
                    'Team dinner meeting',
                    'Catering for department meeting',
                    'Dinner during business trip',
                    'Lunch during conference'
                ],
                'amount_range' => [20, 300]
            ],
            'Office Supplies' => [
                'descriptions' => [
                    'Stationery and desk organizers',
                    'Printer paper and ink cartridges',
                    'Office furniture accessories',
                    'Notebooks and planners',
                    'Whiteboards and markers'
                ],
                'amount_range' => [10, 200]
            ],
            'Equipment' => [
                'descriptions' => [
                    'External hard drive',
                    'Webcam for virtual meetings',
                    'Ergonomic keyboard',
                    'Wireless mouse',
                    'USB hub and adapters'
                ],
                'amount_range' => [30, 400]
            ],
            'Software' => [
                'descriptions' => [
                    'Software license renewal',
                    'Subscription to design tool',
                    'Project management software subscription',
                    'Video conferencing premium account',
                    'Cloud storage upgrade'
                ],
                'amount_range' => [15, 500]
            ],
            'Training' => [
                'descriptions' => [
                    'Online course on {subject}',
                    'Professional certification fee',
                    'Workshop registration',
                    'Webinar series access',
                    'Training materials and books'
                ],
                'amount_range' => [50, 1000]
            ],
            'Miscellaneous' => [
                'descriptions' => [
                    'Team building activity costs',
                    'Professional membership dues',
                    'Shipping and courier charges',
                    'Client gifts',
                    'Office decoration for events'
                ],
                'amount_range' => [20, 300]
            ]
        ];
        
        // Locations for travel expenses
        $locations = ['New York', 'Chicago', 'San Francisco', 'Dallas', 'Miami', 'Seattle', 'Boston', 'Los Angeles', 'Denver', 'Atlanta'];
        
        // Training subjects
        $subjects = ['Leadership', 'Project Management', 'Data Analysis', 'Programming', 'Communication Skills', 'Design Thinking', 'Marketing Strategies'];
        
        // Statuses
        $statuses = ['Pending', 'Approved', 'Rejected', 'Reimbursed'];
        
        foreach ($employees as $employee) {
            // Generate 0-5 expenses per employee
            $expenseCount = rand(0, 5);
            
            for ($i = 0; $i < $expenseCount; $i++) {
                // Select random category
                $category = array_rand($expenseCategories);
                $categoryData = $expenseCategories[$category];
                
                // Get random description
                $description = $categoryData['descriptions'][array_rand($categoryData['descriptions'])];
                
                // Replace placeholders in description
                if (strpos($description, '{location}') !== false) {
                    $description = str_replace('{location}', $locations[array_rand($locations)], $description);
                }
                
                if (strpos($description, '{subject}') !== false) {
                    $description = str_replace('{subject}', $subjects[array_rand($subjects)], $description);
                }
                
                // Determine expense date (last 3 months)
                $expenseDate = Carbon::now()->subDays(rand(1, 90))->format('Y-m-d');
                
                // Submission date (usually a few days after expense date)
                $submissionDate = Carbon::parse($expenseDate)->addDays(rand(1, 7))->format('Y-m-d');
                
                // Amount within range for category
                $amount = rand($categoryData['amount_range'][0], $categoryData['amount_range'][1]);
                if ($amount > 100) {
                    // Add cents for realism
                    $amount += rand(0, 99) / 100;
                }
                
                // Status based on date
                $status = '';
                $approvalDate = null;
                $reimbursementDate = null;
                
                $daysSinceSubmission = Carbon::parse($submissionDate)->diffInDays(Carbon::now());
                
                if ($daysSinceSubmission < 5) {
                    // Recent submission
                    $status = 'Pending';
                } else {
                    // Older submission
                    $statusRoll = rand(1, 100);
                    
                    if ($statusRoll <= 5) {
                        // 5% rejected
                        $status = 'Rejected';
                        $approvalDate = Carbon::parse($submissionDate)->addDays(rand(2, 5))->format('Y-m-d');
                    } elseif ($statusRoll <= 20) {
                        // 15% still pending
                        $status = 'Pending';
                    } else {
                        // 80% approved
                        $approvalDate = Carbon::parse($submissionDate)->addDays(rand(2, 5))->format('Y-m-d');
                        
                        // 70% of approved are reimbursed
                        if (rand(1, 100) <= 70) {
                            $status = 'Reimbursed';
                            $reimbursementDate = Carbon::parse($approvalDate)->addDays(rand(3, 10))->format('Y-m-d');
                        } else {
                            $status = 'Approved';
                        }
                    }
                }
                
                // Approved by (usually a manager)
                $approvedById = null;
                if (in_array($status, ['Approved', 'Reimbursed', 'Rejected'])) {
                    $approvedById = $employee->reporting_manager_id;
                }
                
                Expense::create([
                    'id' => Str::uuid(),
                    'employee_id' => $employee->id,
                    'expense_date' => $expenseDate,
                    'category' => $category,
                    'description' => $description,
                    'amount' => $amount,
                    'receipt_url' => null, // Would be a file path in production
                    'status' => $status,
                    'submitted_at' => $submissionDate,
                    'approved_by_id' => $approvedById,
                    'approved_at' => $approvalDate,
                    'reimbursed_at' => $reimbursementDate,
                ]);
            }
        }
    }
}