<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnershipTest extends TestCase
{
    use RefreshDatabase;

    private User $studentUserA;

    private User $studentUserB;

    private Student $studentA;

    private Student $studentB;

    private string $tokenA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $department = $this->createDepartment();
        $teacher = $this->createUser('teacher');
        $class = $this->createClass($department, $teacher);
        $course = $this->createCourse($department);

        $this->studentUserA = $this->createUser('student');
        $this->studentA = $this->createStudent($this->studentUserA, $class);

        $this->studentUserB = $this->createUser('student');
        $this->studentB = $this->createStudent($this->studentUserB, $class);

        $enrollmentA = $this->createEnrollment($this->studentA, $course);
        $enrollmentB = $this->createEnrollment($this->studentB, $course);

        $this->gradeA = $this->createGrade($enrollmentA, 88);
        $this->gradeB = $this->createGrade($enrollmentB, 65);

        $this->attendanceA = $this->createAttendance($this->studentA, 'Present');
        $this->attendanceB = $this->createAttendance($this->studentB, 'Absent');

        $this->feeA = $this->createFee($this->studentA);
        $this->feeB = $this->createFee($this->studentB);

        $this->paymentA = $this->createPayment($this->studentA);
        $this->paymentB = $this->createPayment($this->studentB);

        $this->tokenA = $this->studentUserA->createToken('test')->plainTextToken;
    }

    public function test_student_can_only_view_their_own_grades(): void
    {
        $this->withToken($this->tokenA)->getJson('/api/student/grades')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment(['id' => $this->gradeA->id])
            ->assertJsonMissing(['id' => $this->gradeB->id]);
    }

    public function test_student_cannot_view_another_students_grades(): void
    {
        $this->withToken($this->tokenA)->getJson('/api/student/grades')
            ->assertOk()
            ->assertJsonMissing(['total' => $this->gradeB->total]);
    }

    public function test_student_can_only_view_their_own_attendance(): void
    {
        $this->withToken($this->tokenA)->getJson('/api/student/attendance')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment(['id' => $this->attendanceA->id])
            ->assertJsonMissing(['id' => $this->attendanceB->id]);
    }

    public function test_student_can_only_view_their_own_fees(): void
    {
        $this->withToken($this->tokenA)->getJson('/api/student/fees')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment(['id' => $this->feeA->id])
            ->assertJsonMissing(['id' => $this->feeB->id]);
    }

    public function test_student_can_only_view_their_own_payments(): void
    {
        $this->withToken($this->tokenA)->getJson('/api/student/payments')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment(['id' => $this->paymentA->id])
            ->assertJsonMissing(['id' => $this->paymentB->id]);
    }
}
