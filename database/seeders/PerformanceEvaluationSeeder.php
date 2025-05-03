<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\PerformanceEvaluation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PerformanceEvaluationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all employees
        $employees = Employee::all();
        
        // Review periods
        $reviewPeriods = [
            Carbon::now()->subMonths(6)->format('Y-m-d') . ' to ' . Carbon::now()->subMonths(3)->format('Y-m-d'),
            Carbon::now()->subMonths(12)->format('Y-m-d') . ' to ' . Carbon::now()->subMonths(9)->format('Y-m-d'),
            Carbon::now()->subMonths(18)->format('Y-m-d') . ' to ' . Carbon::now()->subMonths(15)->format('Y-m-d'),
        ];
        
        // Rating categories
        $ratingCategories = [
            'Communication Skills',
            'Technical Expertise',
            'Teamwork',
            'Leadership',
            'Problem Solving',
            'Time Management',
            'Initiative',
            'Adaptability',
        ];
        
        foreach ($employees as $employee) {
            // Skip some employees randomly
            if (rand(1, 10) > 7) {
                continue;
            }
            
            // Generate 1-2 performance evaluations per employee
            $evaluationCount = rand(1, 2);
            
            for ($i = 0; $i < $evaluationCount; $i++) {
                // Select review period
                $reviewPeriod = $reviewPeriods[$i % count($reviewPeriods)];
                
                // Evaluation date is a bit after the end of review period
                $reviewEndDate = substr($reviewPeriod, strpos($reviewPeriod, 'to ') + 3);
                $evaluationDate = Carbon::parse($reviewEndDate)->addDays(rand(5, 15))->format('Y-m-d');
                
                // Generate ratings for each category
                $ratings = [];
                $overallRating = 0;
                $categoryCount = 0;
                
                foreach ($ratingCategories as $category) {
                    // Rating from 1 to 5, with most being between 3-5
                    $rating = min(5, max(1, rand(2, 5) + rand(0, 1)));
                    $ratings[$category] = $rating;
                    $overallRating += $rating;
                    $categoryCount++;
                }
                
                // Calculate average overall rating
                $overallRating = round($overallRating / $categoryCount, 1);
                
                // Determine strengths and areas for improvement
                $strengths = [];
                $improvements = [];
                
                foreach ($ratings as $category => $rating) {
                    if ($rating >= 4) {
                        $strengths[] = $category;
                    } elseif ($rating <= 3) {
                        $improvements[] = $category;
                    }
                }
                
                // Limit to max 3 for each
                $strengths = array_slice($strengths, 0, 3);
                $improvements = array_slice($improvements, 0, 3);
                
                // Format strengths and improvements
                $strengthsText = count($strengths) > 0 
                    ? 'Demonstrates excellent ' . implode(', ', $strengths) 
                    : 'Generally meets expectations across all areas';
                
                $improvementsText = count($improvements) > 0 
                    ? 'Should focus on improving ' . implode(', ', $improvements) 
                    : 'No significant areas of concern';
                
                // Create comments based on overall rating
                $comments = '';
                if ($overallRating >= 4.5) {
                    $comments = 'Outstanding performer who consistently exceeds expectations. Demonstrates exceptional capabilities and is a valuable team member.';
                } elseif ($overallRating >= 3.5) {
                    $comments = 'Solid performer who meets all expectations and often exceeds them. Reliable and contributes positively to the team.';
                } elseif ($overallRating >= 2.5) {
                    $comments = 'Meets most expectations but has room for improvement in some areas. With additional support, can develop into a stronger contributor.';
                } else {
                    $comments = 'Currently not meeting expectations in several key areas. A performance improvement plan should be implemented.';
                }
                
                PerformanceEvaluation::create([
                    'id' => Str::uuid(),
                    'employee_id' => $employee->id,
                    'evaluator_id' => $employee->reporting_manager_id,
                    'evaluation_date' => $evaluationDate,
                    'review_period' => $reviewPeriod,
                    'ratings' => json_encode($ratings),
                    'overall_rating' => $overallRating,
                    'strengths' => $strengthsText,
                    'areas_for_improvement' => $improvementsText,
                    'comments' => $comments,
                    'goals' => 'Continue professional development and work toward improved performance metrics for the next review period.',
                    'acknowledged_by_employee' => rand(0, 1),
                    'acknowledgment_date' => rand(0, 1) ? Carbon::parse($evaluationDate)->addDays(rand(1, 5))->format('Y-m-d') : null,
                ]);
            }
        }
    }
}