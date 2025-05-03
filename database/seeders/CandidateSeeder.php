<?php

namespace Database\Seeders;

use App\Models\Candidate;
use App\Models\Recruitment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CandidateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all active job openings
        $jobOpenings = Recruitment::whereIn('status', ['Open', 'Interviewing'])->get();
        
        // Candidate statuses
        $statuses = [
            'Applied',
            'Initial Screening',
            'Interview Scheduled',
            'Technical Assessment',
            'Final Interview',
            'Reference Check',
            'Offer Extended',
            'Offer Accepted',
            'Rejected'
        ];
        
        // First names and last names for random candidate generation
        $firstNames = [
            'James', 'Mary', 'John', 'Patricia', 'Robert', 'Jennifer', 'Michael', 'Linda', 
            'William', 'Elizabeth', 'David', 'Susan', 'Richard', 'Jessica', 'Joseph', 'Sarah',
            'Thomas', 'Karen', 'Charles', 'Nancy', 'Christopher', 'Lisa', 'Daniel', 'Margaret',
            'Matthew', 'Betty', 'Anthony', 'Sandra', 'Mark', 'Ashley', 'Donald', 'Kimberly',
            'Steven', 'Emily', 'Paul', 'Donna', 'Andrew', 'Michelle', 'Joshua', 'Carol'
        ];
        
        $lastNames = [
            'Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis',
            'Rodriguez', 'Martinez', 'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson',
            'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin', 'Lee', 'Perez', 'Thompson',
            'White', 'Harris', 'Sanchez', 'Clark', 'Ramirez', 'Lewis', 'Robinson', 'Walker',
            'Young', 'Allen', 'King', 'Wright', 'Scott', 'Torres', 'Nguyen', 'Hill', 'Adams'
        ];
        
        foreach ($jobOpenings as $jobOpening) {
            // Generate 3-8 candidates per job opening
            $candidateCount = rand(3, 8);
            
            for ($i = 0; $i < $candidateCount; $i++) {
                // Generate random candidate info
                $firstName = $firstNames[array_rand($firstNames)];
                $lastName = $lastNames[array_rand($lastNames)];
                $fullName = "{$firstName} {$lastName}";
                
                // Generate application date (after job was posted)
                $jobPostedDate = Carbon::parse($jobOpening->posted_date);
                $applicationDate = $jobPostedDate->copy()->addDays(rand(1, 30))->format('Y-m-d');
                
                // Choose a status based on application date relative to now
                $daysSinceApplication = Carbon::parse($applicationDate)->diffInDays(Carbon::now());
                $statusIndex = min(floor($daysSinceApplication / 5), count($statuses) - 1);
                
                // Add some randomness to the status
                $statusIndex = max(0, min(count($statuses) - 1, $statusIndex + rand(-1, 1)));
                $status = $statuses[$statusIndex];
                
                // Determine interview date if applicable
                $interviewDate = null;
                if (in_array($status, ['Interview Scheduled', 'Technical Assessment', 'Final Interview', 'Reference Check', 'Offer Extended', 'Offer Accepted', 'Rejected'])) {
                    $interviewDate = Carbon::parse($applicationDate)->addDays(rand(5, 15))->format('Y-m-d');
                }
                
                // Skills based on job
                $skills = [];
                if (stripos($jobOpening->job_title, 'developer') !== false || stripos($jobOpening->job_title, 'engineer') !== false) {
                    $skills = ['Programming', 'Software Design', 'Problem Solving', 'Teamwork', 'Agile Methodology'];
                } elseif (stripos($jobOpening->job_title, 'manager') !== false) {
                    $skills = ['Leadership', 'Project Management', 'Communication', 'Strategic Planning', 'Team Building'];
                } elseif (stripos($jobOpening->job_title, 'analyst') !== false) {
                    $skills = ['Data Analysis', 'Reporting', 'Excel', 'Problem Solving', 'Attention to Detail'];
                } elseif (stripos($jobOpening->job_title, 'marketing') !== false) {
                    $skills = ['Digital Marketing', 'Social Media', 'Content Creation', 'SEO', 'Campaign Management'];
                } else {
                    $skills = ['Communication', 'Organization', 'Time Management', 'Problem Solving', 'Adaptability'];
                }
                
                // Create candidate record
                Candidate::create([
                    'id' => Str::uuid(),
                    'recruitment_id' => $jobOpening->id,
                    'name' => $fullName,
                    'email' => strtolower(str_replace(' ', '.', $fullName)) . '@example.com',
                    'phone' => '+1-' . rand(200, 999) . '-' . rand(100, 999) . '-' . rand(1000, 9999),
                    'resume_url' => null, // Would be a file path in production
                    'application_date' => $applicationDate,
                    'status' => $status,
                    'notes' => "Candidate applied for {$jobOpening->job_title} position. " . 
                              ($status === 'Rejected' ? "Not a good fit at this time." : "Shows potential for the role."),
                    'skills' => implode(', ', $skills),
                    'experience' => rand(1, 10) . ' years in ' . $jobOpening->job_title . ' or related field',
                    'interview_date' => $interviewDate,
                    'interview_feedback' => $interviewDate ? "Candidate demonstrated " . ($status === 'Rejected' ? "insufficient" : "strong") . " knowledge in key areas." : null,
                ]);
            }
        }
    }
}