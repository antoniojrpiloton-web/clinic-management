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

        $cba = College::firstOrCreate(
            ['code' => 'CBA'],
            [
                'name' => 'College of Business Administration and Accountancy',
                'dean' => 'Dr. Roberto Villanueva, CPA, MBA',
                'description' => 'Business Management, Accountancy, Financial Management, and Marketing',
                'is_active' => true,
            ]
        );

        $cte = College::firstOrCreate(
            ['code' => 'CTE'],
            [
                'name' => 'College of Teacher Education and Liberal Arts',
                'dean' => 'Dr. Marites Dela Cruz, LPT, EdD',
                'description' => 'Elementary Education, Secondary Education, and Liberal Arts Programs',
                'is_active' => true,
            ]
        );

        $ccje = College::firstOrCreate(
            ['code' => 'CCJE'],
            [
                'name' => 'College of Criminal Justice Education',
                'dean' => 'Dr. Ricardo Morales, RCrim, PhD',
                'description' => 'Criminology, Forensic Studies, and Public Safety Administration',
                'is_active' => true,
            ]
        );

        $chtm = College::firstOrCreate(
            ['code' => 'CHTM'],
            [
                'name' => 'College of Hospitality and Tourism Management',
                'dean' => 'Dr. Clarissa M. Tolentino, CHP, DBA',
                'description' => 'Hospitality Management, Culinary Arts, and International Tourism Programs',
                'is_active' => true,
            ]
        );

        $bed = College::firstOrCreate(
            ['code' => 'BED'],
            [
                'name' => 'Basic Education and Technical-Vocational Department',
                'dean' => 'Dr. Evelyn S. Bautista, LPT, EdD',
                'description' => 'Senior High School, Junior High School, Elementary, and TESDA TVET Programs',
                'is_active' => true,
            ]
        );

        $programs = [
            ['college_id' => $ccje->id, 'code' => 'BSCRIM', 'name' => 'Bachelor of Science in Criminology', 'degree_level' => 'Undergraduate', 'years' => 4],
            ['college_id' => $ccje->id, 'code' => 'BSFS', 'name' => 'Bachelor of Science in Forensic Science', 'degree_level' => 'Undergraduate', 'years' => 4],
            ['college_id' => $cte->id, 'code' => 'BACOMM', 'name' => 'Bachelor of Arts in Communication', 'degree_level' => 'Undergraduate', 'years' => 4],
            ['college_id' => $cba->id, 'code' => 'BSBA', 'name' => 'Bachelor of Science in Business Administration', 'degree_level' => 'Undergraduate', 'years' => 4],
            ['college_id' => $cba->id, 'code' => 'BSA', 'name' => 'Bachelor of Science in Accountancy', 'degree_level' => 'Undergraduate', 'years' => 4],
            ['college_id' => $ccis->id, 'code' => 'BSCS', 'name' => 'Bachelor of Science in Computer Science', 'degree_level' => 'Undergraduate', 'years' => 4],
            ['college_id' => $ccis->id, 'code' => 'BSIT', 'name' => 'Bachelor of Science in Information Technology', 'degree_level' => 'Undergraduate', 'years' => 4],
            ['college_id' => $chtm->id, 'code' => 'BSHM', 'name' => 'Bachelor of Science in Hospitality Management', 'degree_level' => 'Undergraduate', 'years' => 4],
            ['college_id' => $chtm->id, 'code' => 'BSTM', 'name' => 'Bachelor of Science in Tourism Management', 'degree_level' => 'Undergraduate', 'years' => 4],
            ['college_id' => $cte->id, 'code' => 'BSED', 'name' => 'Bachelor of Secondary Education', 'degree_level' => 'Undergraduate', 'years' => 4],
            ['college_id' => $cte->id, 'code' => 'BEED', 'name' => 'Bachelor of Elementary Education', 'degree_level' => 'Undergraduate', 'years' => 4],
            ['college_id' => $bed->id, 'code' => 'TESDA', 'name' => 'TESDA Technical-Vocational Programs', 'degree_level' => 'Certificate / Diploma', 'years' => 2],
            ['college_id' => $bed->id, 'code' => 'SHS', 'name' => 'Senior High School (Grades 11-12)', 'degree_level' => 'Basic Education', 'years' => 2],
            ['college_id' => $bed->id, 'code' => 'JHS', 'name' => 'Junior High School (Grades 7-10)', 'degree_level' => 'Basic Education', 'years' => 4],
            ['college_id' => $bed->id, 'code' => 'ELEM', 'name' => 'Elementary Education (Grades 1-6)', 'degree_level' => 'Basic Education', 'years' => 6],
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
