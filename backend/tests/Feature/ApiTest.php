<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Resource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for the CampusConnect REST API.
 *
 * Each group is self-contained thanks to RefreshDatabase: every test starts
 * with a clean in-memory SQLite schema so there are no cross-test fixtures.
 */
class ApiTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function createUser(string $role = 'student', array $overrides = []): User
    {
        return User::factory()->create(array_merge(['role' => $role], $overrides));
    }

    private function createCourse(User $lecturer, array $overrides = []): Course
    {
        return Course::create(array_merge([
            'title' => 'Test Course',
            'course_code' => 'TC' . fake()->unique()->numerify('###'),
            'description' => 'A test course.',
            'lecturer_id' => $lecturer->id,
        ], $overrides));
    }

    private function createAnnouncement(User $admin, array $overrides = []): Announcement
    {
        return Announcement::create(array_merge([
            'admin_id' => $admin->id,
            'title' => 'Test Announcement',
            'content' => 'Some announcement content.',
            'published_at' => now()->subMinute(),
        ], $overrides));
    }

    private function authHeader(User $user): array
    {
        $token = $user->createToken('test')->plainTextToken;
        return ['Authorization' => 'Bearer ' . $token];
    }

    // -------------------------------------------------------------------------
    // Authentication
    // -------------------------------------------------------------------------

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'password' => 'password1',
            'password_confirmation' => 'password1',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'user', 'token'])
            ->assertJsonPath('user.role', 'student');
    }

    public function test_registration_fails_with_invalid_data(): void
    {
        // Missing password_confirmation
        $response = $this->postJson('/api/register', [
            'name' => 'Bob',
            'email' => 'bob@example.com',
            'password' => 'password1',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_registration_ignores_role_input(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Eve',
            'email' => 'eve@example.com',
            'password' => 'password1',
            'password_confirmation' => 'password1',
            'role' => 'admin',              // should be ignored
        ]);

        $response->assertStatus(201);
        // The user must still be a student, regardless of the submitted role.
        $response->assertJsonPath('user.role', 'student');
        $this->assertDatabaseHas('users', ['email' => 'eve@example.com', 'role' => 'student']);
    }

    public function test_user_can_login(): void
    {
        $user = $this->createUser('student', [
            'email' => 'student@example.com',
            'password' => 'password1',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'student@example.com',
            'password' => 'password1',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['token', 'user']);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $this->createUser('student', ['email' => 'student2@example.com', 'password' => 'password1']);

        $response = $this->postJson('/api/login', [
            'email' => 'student2@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
    }

    public function test_user_can_logout(): void
    {
        $user = $this->createUser();
        $response = $this->postJson('/api/logout', [], $this->authHeader($user));
        $response->assertStatus(200);
    }

    public function test_logout_requires_authentication(): void
    {
        $this->postJson('/api/logout')->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // Courses – listing & showing
    // -------------------------------------------------------------------------

    public function test_authenticated_user_can_list_courses(): void
    {
        $student = $this->createUser('student');
        $lecturer = $this->createUser('lecturer');
        $this->createCourse($lecturer);

        $this->getJson('/api/courses', $this->authHeader($student))
            ->assertStatus(200)
            ->assertJsonCount(1);
    }

    public function test_course_show_returns_not_found_for_unknown_id(): void
    {
        $student = $this->createUser();
        $this->getJson('/api/courses/99999', $this->authHeader($student))
            ->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // Courses – lecturer creation rules
    // -------------------------------------------------------------------------

    public function test_lecturer_can_create_course_for_themselves(): void
    {
        $lecturer = $this->createUser('lecturer');

        $response = $this->postJson('/api/courses', [
            'title' => 'My Course',
            'course_code' => 'MC001',
            'description' => 'A course.',
            // lecturer_id is intentionally not sent; the controller forces the user's own id
        ], $this->authHeader($lecturer));

        $response->assertStatus(201)
            ->assertJsonPath('course.lecturer_id', $lecturer->id);
    }

    public function test_lecturer_cannot_create_course_for_another_lecturer(): void
    {
        $lecturerA = $this->createUser('lecturer');
        $lecturerB = $this->createUser('lecturer');

        // Even if lecturerA sends lecturerB's id, the course must be owned by lecturerA.
        $response = $this->postJson('/api/courses', [
            'title' => 'Hijacked Course',
            'course_code' => 'HC001',
            'lecturer_id' => $lecturerB->id,
        ], $this->authHeader($lecturerA));

        $response->assertStatus(201);
        // The controller ignores the submitted lecturer_id for lecturers.
        $response->assertJsonPath('course.lecturer_id', $lecturerA->id);
    }

    public function test_admin_can_create_course_and_assign_any_lecturer(): void
    {
        $admin = $this->createUser('admin');
        $lecturer = $this->createUser('lecturer');

        $response = $this->postJson('/api/courses', [
            'title' => 'Admin-Created Course',
            'course_code' => 'AC001',
            'lecturer_id' => $lecturer->id,
        ], $this->authHeader($admin));

        $response->assertStatus(201)
            ->assertJsonPath('course.lecturer_id', $lecturer->id);
    }

    public function test_student_cannot_create_course(): void
    {
        $student = $this->createUser('student');

        $this->postJson('/api/courses', [
            'title' => 'Bad Course',
            'course_code' => 'BC001',
        ], $this->authHeader($student))->assertStatus(403);
    }

    public function test_lecturer_can_update_own_course(): void
    {
        $lecturer = $this->createUser('lecturer');
        $course = $this->createCourse($lecturer);

        $this->putJson("/api/courses/{$course->id}", [
            'title' => 'Updated Title',
        ], $this->authHeader($lecturer))
            ->assertStatus(200)
            ->assertJsonPath('course.title', 'Updated Title');
    }

    public function test_lecturer_cannot_update_another_lecturers_course(): void
    {
        $lecturerA = $this->createUser('lecturer');
        $lecturerB = $this->createUser('lecturer');
        $course = $this->createCourse($lecturerA);

        $this->putJson("/api/courses/{$course->id}", [
            'title' => 'Hijacked Update',
        ], $this->authHeader($lecturerB))
            ->assertStatus(403);
    }

    public function test_lecturer_cannot_reassign_course_to_another_lecturer(): void
    {
        $lecturerA = $this->createUser('lecturer');
        $lecturerB = $this->createUser('lecturer');
        $course = $this->createCourse($lecturerA);

        // Even if the lecturer sends a different lecturer_id it must be ignored.
        $this->putJson("/api/courses/{$course->id}", [
            'lecturer_id' => $lecturerB->id,
        ], $this->authHeader($lecturerA))
            ->assertStatus(200);

        // Course ownership must be unchanged.
        $this->assertDatabaseHas('courses', ['id' => $course->id, 'lecturer_id' => $lecturerA->id]);
    }

    public function test_admin_can_update_any_course(): void
    {
        $admin = $this->createUser('admin');
        $lecturer = $this->createUser('lecturer');
        $course = $this->createCourse($lecturer);

        $this->putJson("/api/courses/{$course->id}", [
            'title' => 'Admin Changed Title',
        ], $this->authHeader($admin))
            ->assertStatus(200)
            ->assertJsonPath('course.title', 'Admin Changed Title');
    }

    public function test_lecturer_can_delete_own_course(): void
    {
        $lecturer = $this->createUser('lecturer');
        $course = $this->createCourse($lecturer);

        $this->deleteJson("/api/courses/{$course->id}", [], $this->authHeader($lecturer))
            ->assertStatus(200);

        $this->assertDatabaseMissing('courses', ['id' => $course->id]);
    }

    public function test_lecturer_cannot_delete_another_lecturers_course(): void
    {
        $lecturerA = $this->createUser('lecturer');
        $lecturerB = $this->createUser('lecturer');
        $course = $this->createCourse($lecturerA);

        $this->deleteJson("/api/courses/{$course->id}", [], $this->authHeader($lecturerB))
            ->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // Enrollments
    // -------------------------------------------------------------------------

    public function test_student_can_request_enrollment(): void
    {
        $student = $this->createUser('student');
        $lecturer = $this->createUser('lecturer');
        $course = $this->createCourse($lecturer);

        $this->postJson('/api/enrollments', [
            'course_id' => $course->id,
        ], $this->authHeader($student))
            ->assertStatus(201)
            ->assertJsonPath('enrollment.status', 'pending');
    }

    public function test_enrollment_prevents_duplicates(): void
    {
        $student = $this->createUser('student');
        $lecturer = $this->createUser('lecturer');
        $course = $this->createCourse($lecturer);

        $this->postJson('/api/enrollments', ['course_id' => $course->id], $this->authHeader($student));
        $this->postJson('/api/enrollments', ['course_id' => $course->id], $this->authHeader($student))
            ->assertStatus(409);
    }

    public function test_lecturer_cannot_enroll_in_a_course(): void
    {
        $lecturer = $this->createUser('lecturer');
        $anotherLecturer = $this->createUser('lecturer');
        $course = $this->createCourse($anotherLecturer);

        $this->postJson('/api/enrollments', [
            'course_id' => $course->id,
        ], $this->authHeader($lecturer))
            ->assertStatus(403);
    }

    public function test_lecturer_can_approve_enrollment_for_own_course(): void
    {
        $student = $this->createUser('student');
        $lecturer = $this->createUser('lecturer');
        $course = $this->createCourse($lecturer);

        $enrollment = Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'status' => Enrollment::STATUS_PENDING,
        ]);

        $this->patchJson("/api/enrollments/{$enrollment->id}/approve", [], $this->authHeader($lecturer))
            ->assertStatus(200)
            ->assertJsonPath('enrollment.status', 'approved');
    }

    public function test_lecturer_cannot_approve_enrollment_for_other_course(): void
    {
        $student = $this->createUser('student');
        $lecturerA = $this->createUser('lecturer');
        $lecturerB = $this->createUser('lecturer');
        $course = $this->createCourse($lecturerA);

        $enrollment = Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'status' => Enrollment::STATUS_PENDING,
        ]);

        $this->patchJson("/api/enrollments/{$enrollment->id}/approve", [], $this->authHeader($lecturerB))
            ->assertStatus(403);
    }

    public function test_already_approved_enrollment_cannot_be_approved_again(): void
    {
        $student = $this->createUser('student');
        $lecturer = $this->createUser('lecturer');
        $course = $this->createCourse($lecturer);

        $enrollment = Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'status' => Enrollment::STATUS_APPROVED,
        ]);

        $this->patchJson("/api/enrollments/{$enrollment->id}/approve", [], $this->authHeader($lecturer))
            ->assertStatus(422);
    }

    public function test_lecturer_can_reject_pending_enrollment(): void
    {
        $student = $this->createUser('student');
        $lecturer = $this->createUser('lecturer');
        $course = $this->createCourse($lecturer);

        $enrollment = Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'status' => Enrollment::STATUS_PENDING,
        ]);

        $this->patchJson("/api/enrollments/{$enrollment->id}/reject", [], $this->authHeader($lecturer))
            ->assertStatus(200)
            ->assertJsonPath('enrollment.status', 'rejected');
    }

    public function test_admin_can_approve_enrollment(): void
    {
        $admin = $this->createUser('admin');
        $student = $this->createUser('student');
        $lecturer = $this->createUser('lecturer');
        $course = $this->createCourse($lecturer);

        $enrollment = Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'status' => Enrollment::STATUS_PENDING,
        ]);

        $this->patchJson("/api/enrollments/{$enrollment->id}/approve", [], $this->authHeader($admin))
            ->assertStatus(200)
            ->assertJsonPath('enrollment.status', 'approved');
    }

    public function test_student_can_list_own_enrollments(): void
    {
        $student = $this->createUser('student');
        $lecturer = $this->createUser('lecturer');
        $course = $this->createCourse($lecturer);

        Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'status' => Enrollment::STATUS_PENDING,
        ]);

        $response = $this->getJson('/api/enrollments', $this->authHeader($student));

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('enrollments'));
    }

    public function test_lecturer_index_only_sees_enrollments_for_their_courses(): void
    {
        $lecturerA = $this->createUser('lecturer');
        $lecturerB = $this->createUser('lecturer');
        $student = $this->createUser('student');

        $courseA = $this->createCourse($lecturerA);
        $courseB = $this->createCourse($lecturerB);

        Enrollment::create(['student_id' => $student->id, 'course_id' => $courseA->id, 'status' => 'pending']);
        Enrollment::create(['student_id' => $student->id, 'course_id' => $courseB->id, 'status' => 'pending']);

        $response = $this->getJson('/api/enrollments', $this->authHeader($lecturerA));

        $response->assertStatus(200);
        // lecturerA should only see the enrollment for their own course
        $this->assertCount(1, $response->json('enrollments'));
    }

    // -------------------------------------------------------------------------
    // Announcements
    // -------------------------------------------------------------------------

    public function test_admin_can_create_announcement(): void
    {
        $admin = $this->createUser('admin');

        $response = $this->postJson('/api/announcements', [
            'title' => 'Welcome Everyone',
            'content' => 'The semester starts next week.',
        ], $this->authHeader($admin));

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'announcement'])
            ->assertJsonPath('announcement.title', 'Welcome Everyone');
    }

    public function test_lecturer_cannot_create_announcement(): void
    {
        $lecturer = $this->createUser('lecturer');

        $this->postJson('/api/announcements', [
            'title' => 'Lecturer Post',
            'content' => 'Should not work.',
        ], $this->authHeader($lecturer))->assertStatus(403);
    }

    public function test_student_cannot_create_announcement(): void
    {
        $student = $this->createUser('student');

        $this->postJson('/api/announcements', [
            'title' => 'Student Post',
            'content' => 'Should not work.',
        ], $this->authHeader($student))->assertStatus(403);
    }

    public function test_any_authenticated_user_can_list_announcements(): void
    {
        $admin = $this->createUser('admin');
        $this->createAnnouncement($admin);
        $student = $this->createUser('student');

        $this->getJson('/api/announcements', $this->authHeader($student))
            ->assertStatus(200)
            ->assertJsonStructure(['announcements']);
    }

    public function test_unauthenticated_user_cannot_list_announcements(): void
    {
        $this->getJson('/api/announcements')->assertStatus(401);
    }

    public function test_admin_can_update_announcement(): void
    {
        $admin = $this->createUser('admin');
        $announcement = $this->createAnnouncement($admin);

        $this->putJson("/api/announcements/{$announcement->id}", [
            'title' => 'Updated Title',
        ], $this->authHeader($admin))
            ->assertStatus(200)
            ->assertJsonPath('announcement.title', 'Updated Title');
    }

    public function test_non_admin_cannot_update_announcement(): void
    {
        $admin = $this->createUser('admin');
        $lecturer = $this->createUser('lecturer');
        $announcement = $this->createAnnouncement($admin);

        $this->putJson("/api/announcements/{$announcement->id}", [
            'title' => 'Hijacked',
        ], $this->authHeader($lecturer))->assertStatus(403);
    }

    public function test_admin_can_delete_announcement(): void
    {
        $admin = $this->createUser('admin');
        $announcement = $this->createAnnouncement($admin);

        $this->deleteJson("/api/announcements/{$announcement->id}", [], $this->authHeader($admin))
            ->assertStatus(200);

        $this->assertDatabaseMissing('announcements', ['id' => $announcement->id]);
    }

    public function test_non_admin_cannot_delete_announcement(): void
    {
        $admin = $this->createUser('admin');
        $student = $this->createUser('student');
        $announcement = $this->createAnnouncement($admin);

        $this->deleteJson("/api/announcements/{$announcement->id}", [], $this->authHeader($student))
            ->assertStatus(403);
    }

    public function test_announcement_admin_id_cannot_be_spoofed(): void
    {
        $admin = $this->createUser('admin');
        $anotherAdmin = $this->createUser('admin');

        $response = $this->postJson('/api/announcements', [
            'title' => 'Spoofed',
            'content' => 'Trying to set wrong admin_id.',
            'admin_id' => $anotherAdmin->id,   // should be ignored by the controller
        ], $this->authHeader($admin));

        $response->assertStatus(201);
        // The admin_id must match the authenticated admin, not the submitted value.
        $response->assertJsonPath('announcement.admin_id', $admin->id);
    }

    // -------------------------------------------------------------------------
    // Resources – access control
    // -------------------------------------------------------------------------

    public function test_approved_student_can_list_course_resources(): void
    {
        $lecturer = $this->createUser('lecturer');
        $student = $this->createUser('student');
        $course = $this->createCourse($lecturer);

        Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'status' => Enrollment::STATUS_APPROVED,
        ]);

        $this->getJson("/api/courses/{$course->id}/resources", $this->authHeader($student))
            ->assertStatus(200)
            ->assertJsonStructure(['resources']);
    }

    public function test_unapproved_student_cannot_list_course_resources(): void
    {
        $lecturer = $this->createUser('lecturer');
        $student = $this->createUser('student');
        $course = $this->createCourse($lecturer);

        Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'status' => Enrollment::STATUS_PENDING,
        ]);

        $this->getJson("/api/courses/{$course->id}/resources", $this->authHeader($student))
            ->assertStatus(403);
    }

    public function test_resource_response_does_not_include_file_path(): void
    {
        $lecturer = $this->createUser('lecturer');
        $student = $this->createUser('student');
        $course = $this->createCourse($lecturer);

        Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'status' => Enrollment::STATUS_APPROVED,
        ]);

        Resource::create([
            'course_id' => $course->id,
            'uploaded_by' => $lecturer->id,
            'title' => 'Lecture Notes',
            'file_path' => 'resources/secret.pdf',
        ]);

        $response = $this->getJson("/api/courses/{$course->id}/resources", $this->authHeader($student));

        $response->assertStatus(200);
        // file_path must not appear anywhere in the resource objects
        $resources = $response->json('resources');
        foreach ($resources as $resource) {
            $this->assertArrayNotHasKey('file_path', $resource);
        }
    }

    public function test_response_does_not_expose_user_password(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'PasswordTest',
            'email' => 'pwtest@example.com',
            'password' => 'password1',
            'password_confirmation' => 'password1',
        ]);

        $response->assertStatus(201);
        $this->assertArrayNotHasKey('password', $response->json('user'));
    }
}
