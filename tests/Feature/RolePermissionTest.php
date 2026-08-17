<?php

namespace Tests\Feature;

use App\Models\Enrollment;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_user_belongs_to_role_through_role_id(): void
    {
        $user = $this->createUser('admin');

        $this->assertNotNull($user->role_id);
        $this->assertInstanceOf(Role::class, $user->role()->first());
        $this->assertEquals('admin', $user->role()->first()->name);
    }

    public function test_admin_has_admin_role(): void
    {
        $this->assertTrue($this->createUser('admin')->hasRole('admin'));
    }

    public function test_teacher_has_teacher_role(): void
    {
        $this->assertTrue($this->createUser('teacher')->hasRole('teacher'));
    }

    public function test_student_has_student_role(): void
    {
        $this->assertTrue($this->createUser('student')->hasRole('student'));
    }

    public function test_admin_role_holds_every_permission(): void
    {
        $admin = $this->createUser('admin');

        $this->assertTrue($admin->hasPermission('manage_users'));
        $this->assertTrue($admin->hasPermission('manage_students'));
        $this->assertTrue($admin->hasPermission('manage_teachers'));
        $this->assertTrue($admin->hasPermission('manage_fees'));
        $this->assertTrue($admin->hasPermission('manage_payments'));
        $this->assertTrue($admin->hasPermission('view_reports'));

        $this->assertEquals(
            Permission::count(),
            $admin->role()->first()->permissions()->count()
        );
    }

    public function test_admin_can_access_admin_protected_endpoint(): void
    {
        $admin = $this->createUser('admin');
        $token = $admin->createToken('test')->plainTextToken;

        $this->withToken($token)->getJson('/api/users')->assertOk();
    }

    public function test_teacher_can_manage_grades(): void
    {
        $fixture = $this->teacherClassFixture();
        $token = $fixture['teacher']->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson('/api/grades', [
            'enrollment_id' => $fixture['enrollment']->id,
            'assignment' => 80,
            'midterm' => 75,
            'final' => 90,
            'total' => 82,
            'grade' => 'B',
        ])->assertStatus(201);
    }

    public function test_teacher_can_manage_exams(): void
    {
        $department = $this->createDepartment();
        $teacher = $this->createUser('teacher');
        $this->createTeacher($teacher, $department);
        $course = $this->createCourse($department);

        $token = $teacher->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson('/api/exams', [
            'course_id' => $course->id,
            'exam_name' => 'Midterm',
            'exam_date' => '2026-07-01',
            'total_marks' => 100,
            'weight' => 50,
        ])->assertStatus(201);
    }

    public function test_teacher_can_mark_attendance(): void
    {
        $fixture = $this->teacherClassFixture();
        $token = $fixture['teacher']->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson('/api/attendances', [
            'student_id' => $fixture['studentProfile']->id,
            'date' => '2026-07-01',
            'status' => 'Present',
        ])->assertStatus(201);
    }

    public function test_teacher_cannot_manage_users(): void
    {
        $teacher = $this->createUser('teacher');
        $token = $teacher->createToken('test')->plainTextToken;

        $this->withToken($token)->getJson('/api/users')->assertStatus(403);
    }

    public function test_teacher_cannot_manage_fees(): void
    {
        $teacher = $this->createUser('teacher');
        $token = $teacher->createToken('test')->plainTextToken;

        $this->withToken($token)->getJson('/api/fees')->assertStatus(403);
    }

    public function test_student_can_view_grades(): void
    {
        $fixture = $this->teacherClassFixture();
        $this->createGrade($fixture['enrollment']);
        $token = $fixture['student']->createToken('test')->plainTextToken;

        $this->withToken($token)->getJson('/api/student/grades')->assertOk();
    }

    public function test_student_cannot_manage_grades(): void
    {
        $student = $this->createUser('student');
        $token = $student->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson('/api/grades', [
            'enrollment_id' => 1,
            'assignment' => 80,
            'midterm' => 75,
            'final' => 90,
            'total' => 82,
            'grade' => 'B',
        ])->assertStatus(403);
    }

    public function test_student_cannot_create_exams(): void
    {
        $student = $this->createUser('student');
        $token = $student->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson('/api/exams', [
            'course_id' => 1,
            'exam_name' => 'Midterm',
            'exam_date' => '2026-07-01',
            'total_marks' => 100,
        ])->assertStatus(403);
    }

    public function test_student_cannot_mark_attendance(): void
    {
        $student = $this->createUser('student');
        $token = $student->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson('/api/attendances', [
            'student_id' => 1,
            'date' => '2026-07-01',
            'status' => 'Present',
        ])->assertStatus(403);
    }

    public function test_student_cannot_manage_users(): void
    {
        $student = $this->createUser('student');
        $token = $student->createToken('test')->plainTextToken;

        $this->withToken($token)->getJson('/api/users')->assertStatus(403);
    }

    /**
     * @return array{teacher: User, student: User, studentProfile: Student, enrollment: Enrollment}
     */
    private function teacherClassFixture(): array
    {
        $department = $this->createDepartment();
        $teacher = $this->createUser('teacher');
        $this->createTeacher($teacher, $department);
        $class = $this->createClass($department, $teacher);
        $student = $this->createUser('student');
        $studentProfile = $this->createStudent($student, $class);
        $course = $this->createCourse($department);

        return [
            'teacher' => $teacher,
            'student' => $student,
            'studentProfile' => $studentProfile,
            'enrollment' => $this->createEnrollment($studentProfile, $course),
        ];
    }
}
