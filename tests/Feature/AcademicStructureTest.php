<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\College;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicStructureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_super_admin_can_manage_academic_years(): void
    {
        $admin = User::role(User::SUPER_ADMIN_ROLE)->first();

        $response = $this->actingAs($admin)->get(route('academic-years.index'));
        $response->assertOk();

        $createResponse = $this->actingAs($admin)->post(route('academic-years.store'), [
            'code' => 'AY-2028-2029-1',
            'name' => 'Academic Year 2028-2029',
            'semester' => '1st Semester',
            'start_date' => '2028-08-01',
            'end_date' => '2028-12-31',
            'status' => 'active',
            'is_current' => 1,
        ]);
        $createResponse->assertRedirect(route('academic-years.index'));

        $ay = AcademicYear::where('code', 'AY-2028-2029-1')->first();
        $this->assertNotNull($ay);
        $this->assertTrue($ay->is_current);

        $oldAy = AcademicYear::where('code', 'AY-2026-2027-1')->first();
        $this->assertFalse($oldAy->is_current);

        $this->actingAs($admin)->post(route('academic-years.set-current', $oldAy));
        $this->assertTrue($oldAy->fresh()->is_current);
        $this->assertFalse($ay->fresh()->is_current);

        $deleteResponse = $this->actingAs($admin)->delete(route('academic-years.destroy', $ay));
        $deleteResponse->assertRedirect(route('academic-years.index'));
        $this->assertDatabaseMissing('academic_years', ['id' => $ay->id]);
    }

    public function test_super_admin_can_manage_colleges_and_programs(): void
    {
        $admin = User::role(User::SUPER_ADMIN_ROLE)->first();

        $collegeResponse = $this->actingAs($admin)->get(route('colleges.index'));
        $collegeResponse->assertOk();

        $createCollege = $this->actingAs($admin)->post(route('colleges.store'), [
            'code' => 'CTE',
            'name' => 'College of Teacher Education',
            'dean' => 'Dr. Maria Santos',
            'is_active' => 1,
        ]);
        $createCollege->assertRedirect(route('colleges.index'));

        $college = College::where('code', 'CTE')->first();
        $this->assertNotNull($college);

        $createProgram = $this->actingAs($admin)->post(route('programs.store'), [
            'college_id' => $college->id,
            'code' => 'BSED',
            'name' => 'Bachelor of Secondary Education',
            'degree_level' => 'Undergraduate',
            'years' => 4,
            'is_active' => 1,
        ]);
        $createProgram->assertRedirect(route('programs.index'));

        $program = Program::where('code', 'BSED')->first();
        $this->assertNotNull($program);
        $this->assertEquals($college->id, $program->college_id);

        $deleteAttempt = $this->actingAs($admin)->delete(route('colleges.destroy', $college));
        $this->assertDatabaseHas('colleges', ['id' => $college->id]);

        $this->actingAs($admin)->delete(route('programs.destroy', $program));
        $this->assertDatabaseMissing('programs', ['id' => $program->id]);

        $this->actingAs($admin)->delete(route('colleges.destroy', $college));
        $this->assertDatabaseMissing('colleges', ['id' => $college->id]);
    }
}
