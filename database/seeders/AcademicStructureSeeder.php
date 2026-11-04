<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\College;
use App\Models\Program;
use Illuminate\Database\Seeder;

class AcademicStructureSeeder extends Seeder
{
    public function run(): void
    {
        $ay = AcademicYear::firstOrCreate(
            ['code' => 'AY-2026-2027-1'],
            [
                'name' => 'Academic Year 2026-2027',
                'semester' => '1st Semester',
                'start_date' => '2026-08-01',
                'end_date' => '2026-12-31',
                'is_current' => true,
                'status' => 'active',
            ]
        );

        AcademicYear::firstOrCreate(
            ['code' => 'AY-2025-2026-2'],
            [
                'name' => 'Academic Year 2025-2026',
                'semester' => '2nd Semester',
                'start_date' => '2026-01-15',
                'end_date' => '2026-05-30',
                'is_current' => false,
                'status' => 'closed',
            ]
        );

        $ccis = College::firstOrCreate(
            ['code' => 'CCIS'],
            [
                'name' => 'College of Computer and Information Sciences',
                'dean' => 'Dr. Eleanor Vance',
                'description' => 'Department of Computing, Information Systems, and Cyber Security',
                'is_active' => true,
            ]
        );

        $ceat = College::firstOrCreate(
            ['code' => 'CEAT'],
            [
                'name' => 'College of Engineering and Architecture Technology',
                'dean' => 'Engr. Manuel Santos',
                'description' => 'Engineering, Architecture, and Applied Industrial Sciences',
                'is_active' => true,
            ]
        );

        $con = College::firstOrCreate(
            ['code' => 'CHS'],
            [
                'name' => 'College of Health Sciences and Nursing',
                'dean' => 'Dr. Patricia Ramos, RN, MD',
                'description' => 'Healthcare programs, Nursing, and Allied Clinical Services',
                'is_active' => true,
            ]
        );

        $programs = [
            ['college_id' => $ccis->id, 'code' => 'BSIT', 'name' => 'Bachelor of Science in Information Technology', 'degree_level' => 'Undergraduate', 'years' => 4],
            ['college_id' => $ccis->id, 'code' => 'BSCS', 'name' => 'Bachelor of Science in Computer Science', 'degree_level' => 'Undergraduate', 'years' => 4],
            ['college_id' => $ceat->id, 'code' => 'BSCE', 'name' => 'Bachelor of Science in Civil Engineering', 'degree_level' => 'Undergraduate', 'years' => 4],
            ['college_id' => $ceat->id, 'code' => 'BSEE', 'name' => 'Bachelor of Science in Electrical Engineering', 'degree_level' => 'Undergraduate', 'years' => 4],
            ['college_id' => $con->id, 'code' => 'BSN', 'name' => 'Bachelor of Science in Nursing', 'degree_level' => 'Undergraduate', 'years' => 4],
        ];

        foreach ($programs as $program) {
            Program::firstOrCreate(
                ['code' => $program['code']],
                $program
            );
        }
    }
}
