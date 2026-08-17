<?php

namespace Tests;

use App\Models\Attendance;
use App\Models\Course;
use App\Models\Department;
use App\Models\Enrollment;
use App\Models\Fee;
use App\Models\Grade;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function createUser(string $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    protected function createDepartment(string $name = 'Information Technology'): Department
    {
        return Department::create(['name' => $name]);
    }

    protected function createTeacher(User $user, Department $department): Teacher
    {
        return Teacher::create([
            'department_id' => $department->id,
            'user_id' => $user->id,
            'teacher_code' => 'TCH-'.fake()->unique()->numerify('####'),
            'first_name' => 'Ms',
            'last_name' => 'Teacher',
            'gender' => 'Female',
            'dob' => '1985-01-01',
            'email' => fake()->unique()->safeEmail(),
        ]);
    }

    protected function createClass(Department $department, User $teacher): SchoolClass
    {
        return SchoolClass::create([
            'department_id' => $department->id,
            'teacher_id' => $teacher->id,
            'class_name' => 'Class '.fake()->unique()->numerify('##'),
            'academic_year' => '2026',
            'semester' => '1',
        ]);
    }

    protected function createCourse(Department $department): Course
    {
        return Course::create([
            'department_id' => $department->id,
            'course_code' => fake()->unique()->numerify('CS####'),
            'course_name' => 'Mathematics',
            'credit' => 3,
        ]);
    }

    protected function createStudent(User $user, SchoolClass $class): Student
    {
        return Student::create([
            'user_id' => $user->id,
            'class_id' => $class->id,
            'student_code' => fake()->unique()->numerify('STU#####'),
            'first_name' => 'Test',
            'last_name' => 'Student',
            'gender' => 'Male',
            'dob' => '2006-01-01',
            'email' => fake()->unique()->safeEmail(),
        ]);
    }

    protected function createEnrollment(Student $student, Course $course): Enrollment
    {
        return Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'semester' => '1',
            'academic_year' => '2026',
            'status' => 'Active',
        ]);
    }

    protected function createGrade(Enrollment $enrollment, int $total = 85): Grade
    {
        return Grade::create([
            'enrollment_id' => $enrollment->id,
            'assignment' => 80,
            'midterm' => 75,
            'final' => 90,
            'total' => $total,
            'grade' => 'B',
        ]);
    }

    protected function createAttendance(Student $student, string $status = 'Present'): Attendance
    {
        return Attendance::create([
            'student_id' => $student->id,
            'date' => now()->toDateString(),
            'status' => $status,
        ]);
    }

    protected function createFee(Student $student): Fee
    {
        return Fee::create([
            'student_id' => $student->id,
            'fee_type' => 'Tuition',
            'amount' => 500,
            'due_date' => now()->addMonth()->toDateString(),
            'status' => 'Unpaid',
        ]);
    }

    protected function createPayment(Student $student): Payment
    {
        return Payment::create([
            'student_id' => $student->id,
            'amount' => 200,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'Cash',
        ]);
    }
}
