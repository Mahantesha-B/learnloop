<?php

declare(strict_types=1);

// Let PHP's built-in dev server (`php -S`) serve real static files directly
// instead of routing every asset request through the front controller.
if (PHP_SAPI === 'cli-server') {
    $requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $assetPath = __DIR__ . $requestPath;
    if (!str_ends_with($requestPath, '.php') && is_file($assetPath)) {
        return false;
    }
}

date_default_timezone_set('Asia/Kolkata');
ini_set('display_errors', '1');
error_reporting(E_ALL);

session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
]);

require_once __DIR__ . '/../core/helpers.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Csrf.php';
require_once __DIR__ . '/../core/Validator.php';
require_once __DIR__ . '/../core/Router.php';

require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/models/Course.php';
require_once __DIR__ . '/../app/models/Lesson.php';
require_once __DIR__ . '/../app/models/Enrollment.php';
require_once __DIR__ . '/../app/models/LessonProgress.php';
require_once __DIR__ . '/../app/models/Quiz.php';
require_once __DIR__ . '/../app/models/QuizQuestion.php';
require_once __DIR__ . '/../app/models/QuizOption.php';
require_once __DIR__ . '/../app/models/QuizAttempt.php';

require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/AdminController.php';
require_once __DIR__ . '/../app/controllers/InstructorController.php';
require_once __DIR__ . '/../app/controllers/StudentController.php';

$router = new Router();

// ---- Auth ----
$router->get('login', fn() => (new AuthController())->showLogin());
$router->post('login', fn() => (new AuthController())->login());
$router->post('logout', fn() => (new AuthController())->logout());

// ---- Super Admin ----
$router->get('admin/dashboard', fn() => (new AdminController())->dashboard());
$router->get('admin/courses', fn() => (new AdminController())->courses());
$router->get('admin/courses/create', fn() => (new AdminController())->courseCreateForm());
$router->post('admin/courses/store', fn() => (new AdminController())->courseStore());
$router->get('admin/courses/edit', fn() => (new AdminController())->courseEditForm());
$router->post('admin/courses/update', fn() => (new AdminController())->courseUpdate());
$router->post('admin/courses/status', fn() => (new AdminController())->courseStatus());
$router->get('admin/users', fn() => (new AdminController())->users());
$router->get('admin/users/create', fn() => (new AdminController())->userCreateForm());
$router->post('admin/users/store', fn() => (new AdminController())->userStore());
$router->post('admin/users/status', fn() => (new AdminController())->userStatus());
$router->get('admin/analytics', fn() => (new AdminController())->analytics());

// ---- Instructor ----
$router->get('instructor/dashboard', fn() => (new InstructorController())->dashboard());
$router->get('instructor/courses', fn() => (new InstructorController())->courses());
$router->get('instructor/courses/edit', fn() => (new InstructorController())->courseEditForm());
$router->post('instructor/courses/update', fn() => (new InstructorController())->courseUpdate());
$router->get('instructor/lessons', fn() => (new InstructorController())->lessons());
$router->get('instructor/lessons/create', fn() => (new InstructorController())->lessonCreateForm());
$router->post('instructor/lessons/store', fn() => (new InstructorController())->lessonStore());
$router->get('instructor/lessons/edit', fn() => (new InstructorController())->lessonEditForm());
$router->post('instructor/lessons/update', fn() => (new InstructorController())->lessonUpdate());
$router->post('instructor/lessons/delete', fn() => (new InstructorController())->lessonDelete());
$router->post('instructor/lessons/move', fn() => (new InstructorController())->lessonMove());
$router->get('instructor/quiz', fn() => (new InstructorController())->quiz());
$router->post('instructor/quiz/store', fn() => (new InstructorController())->quizStore());
$router->get('instructor/quiz/questions/create', fn() => (new InstructorController())->questionCreateForm());
$router->post('instructor/quiz/questions/store', fn() => (new InstructorController())->questionStore());
$router->get('instructor/quiz/questions/edit', fn() => (new InstructorController())->questionEditForm());
$router->post('instructor/quiz/questions/update', fn() => (new InstructorController())->questionUpdate());
$router->post('instructor/quiz/questions/delete', fn() => (new InstructorController())->questionDelete());
$router->get('instructor/gradebook', fn() => (new InstructorController())->gradebook());

// ---- Student ----
$router->get('student/dashboard', fn() => (new StudentController())->dashboard());
$router->get('student/catalog', fn() => (new StudentController())->catalog());
$router->post('student/enroll', fn() => (new StudentController())->enroll());
$router->get('student/courses/view', fn() => (new StudentController())->courseView());
$router->get('student/lessons/view', fn() => (new StudentController())->lessonView());
$router->post('student/lessons/complete', fn() => (new StudentController())->lessonComplete());
$router->get('student/quiz/take', fn() => (new StudentController())->quizTake());
$router->post('student/quiz/submit', fn() => (new StudentController())->quizSubmit());
$router->get('student/quiz/result', fn() => (new StudentController())->quizResult());
$router->get('student/grades', fn() => (new StudentController())->grades());

$route = $_GET['route'] ?? '';

if ($route === '') {
    if (Auth::check()) {
        redirect(Auth::dashboardRoute(Auth::role()));
    }
    redirect('login');
}

$router->dispatch($_SERVER['REQUEST_METHOD'], $route);
