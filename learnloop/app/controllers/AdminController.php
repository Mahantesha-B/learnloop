<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Course.php';
require_once __DIR__ . '/../models/Enrollment.php';
require_once __DIR__ . '/../models/Quiz.php';
require_once __DIR__ . '/../models/QuizAttempt.php';

class AdminController
{
    private function guard(): void
    {
        Auth::requireRole('super_admin');
    }

    public function dashboard(): void
    {
        $this->guard();

        $stats = [
            'courses'      => Course::countTotal(),
            'active'       => Course::countActive(),
            'instructors'  => User::countByRole('instructor'),
            'students'     => User::countByRole('student'),
            'enrollments'  => Enrollment::countTotal(),
            'quizzes'      => Quiz::countTotal(),
            'attempts'     => QuizAttempt::countTotal(),
        ];
        $recentCourses = array_slice(Course::all(), 0, 6);

        require __DIR__ . '/../views/admin/dashboard.php';
    }

    public function courses(): void
    {
        $this->guard();
        $courses = Course::all();
        require __DIR__ . '/../views/admin/courses.php';
    }

    public function courseCreateForm(): void
    {
        $this->guard();
        $instructors = User::activeInstructors();
        $errors = [];
        $old = [];
        require __DIR__ . '/../views/admin/course_form.php';
    }

    public function courseStore(): void
    {
        $this->guard();
        Csrf::requireValid();

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $instructorId = (int)($_POST['instructor_id'] ?? 0);

        $v = new Validator();
        $v->required($_POST, 'title', 'Title')->maxLength($_POST, 'title', 150, 'Title')
          ->required($_POST, 'instructor_id', 'Instructor');
        $errors = $v->errors();

        if (empty($errors)) {
            $instructor = User::find($instructorId);
            if (!$instructor || $instructor['role'] !== 'instructor') {
                $errors['instructor_id'] = 'Please choose a valid instructor.';
            }
        }

        if (!empty($errors)) {
            $old = $_POST;
            $instructors = User::activeInstructors();
            require __DIR__ . '/../views/admin/course_form.php';
            return;
        }

        Course::create($title, $description ?: null, $instructorId);
        flash_set('success', "Course \"$title\" created.");
        redirect('admin/courses');
    }

    public function courseEditForm(): void
    {
        $this->guard();
        $course = Course::find((int)($_GET['id'] ?? 0));
        if (!$course) {
            flash_set('error', 'Course not found.');
            redirect('admin/courses');
        }
        $instructors = User::activeInstructors();
        $errors = [];
        $old = [];
        require __DIR__ . '/../views/admin/course_edit.php';
    }

    public function courseUpdate(): void
    {
        $this->guard();
        Csrf::requireValid();

        $id = (int)($_POST['id'] ?? 0);
        $course = Course::find($id);
        if (!$course) {
            flash_set('error', 'Course not found.');
            redirect('admin/courses');
        }

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $instructorId = (int)($_POST['instructor_id'] ?? 0);

        $v = new Validator();
        $v->required($_POST, 'title', 'Title')->maxLength($_POST, 'title', 150, 'Title')
          ->required($_POST, 'instructor_id', 'Instructor');
        $errors = $v->errors();

        if (empty($errors)) {
            $instructor = User::find($instructorId);
            if (!$instructor || $instructor['role'] !== 'instructor') {
                $errors['instructor_id'] = 'Please choose a valid instructor.';
            }
        }

        if (!empty($errors)) {
            $old = $_POST;
            $instructors = User::activeInstructors();
            require __DIR__ . '/../views/admin/course_edit.php';
            return;
        }

        Course::reassign($id, $title, $description ?: null, $instructorId);
        flash_set('success', 'Course updated.');
        redirect('admin/courses');
    }

    public function courseStatus(): void
    {
        $this->guard();
        Csrf::requireValid();

        $id = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] === 'active' ? 'active' : 'inactive';
        Course::setStatus($id, $status);

        flash_set('success', $status === 'active' ? 'Course reactivated.' : 'Course deactivated -- hidden from the catalog, still visible to enrolled students.');
        redirect('admin/courses');
    }

    public function users(): void
    {
        $this->guard();
        $users = User::allManaged();
        require __DIR__ . '/../views/admin/users.php';
    }

    public function userCreateForm(): void
    {
        $this->guard();
        $errors = [];
        $old = [];
        require __DIR__ . '/../views/admin/user_form.php';
    }

    public function userStore(): void
    {
        $this->guard();
        Csrf::requireValid();

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? '';
        $password = (string)($_POST['password'] ?? '');

        $v = new Validator();
        $v->required($_POST, 'name', 'Name')->maxLength($_POST, 'name', 100, 'Name')
          ->required($_POST, 'email', 'Email')->email($_POST, 'email', 'Email')
          ->required($_POST, 'role', 'Role')->inArray($_POST, 'role', ['instructor', 'student'], 'Role')
          ->required($_POST, 'password', 'Password')->minLength($_POST, 'password', 6, 'Password');

        $errors = $v->errors();
        if (empty($errors) && User::emailExists($email)) {
            $errors['email'] = 'This email is already registered.';
        }

        if (!empty($errors)) {
            $old = $_POST;
            require __DIR__ . '/../views/admin/user_form.php';
            return;
        }

        User::create($name, $email, $password, $role);
        flash_set('success', "Account created for $name ($role).");
        redirect('admin/users');
    }

    public function userStatus(): void
    {
        $this->guard();
        Csrf::requireValid();

        $id = (int)($_POST['id'] ?? 0);
        if ($id === Auth::id()) {
            flash_set('error', 'You cannot deactivate your own account.');
            redirect('admin/users');
        }

        $status = $_POST['status'] === 'active' ? 'active' : 'inactive';
        User::setStatus($id, $status);

        flash_set('success', 'Account status updated.');
        redirect('admin/users');
    }

    public function analytics(): void
    {
        $this->guard();
        $passRates = QuizAttempt::passRateByCourse();
        $completion = Course::completionStats();

        $mostCompleted = $completion;
        usort($mostCompleted, fn($a, $b) => $b['completion_rate_pct'] <=> $a['completion_rate_pct']);
        $leastCompleted = $completion;
        usort($leastCompleted, fn($a, $b) => $a['completion_rate_pct'] <=> $b['completion_rate_pct']);

        require __DIR__ . '/../views/admin/analytics.php';
    }
}
