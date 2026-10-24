<?php

require_once __DIR__ . '/../models/Course.php';
require_once __DIR__ . '/../models/Lesson.php';
require_once __DIR__ . '/../models/Quiz.php';
require_once __DIR__ . '/../models/QuizQuestion.php';
require_once __DIR__ . '/../models/QuizOption.php';
require_once __DIR__ . '/../models/QuizAttempt.php';
require_once __DIR__ . '/../models/Enrollment.php';

class InstructorController
{
    private function guard(): void
    {
        Auth::requireRole('instructor');
    }

    // Loads a course and 404s (via the role-guard 404 view) unless it belongs to the
    // logged-in instructor -- every course/lesson/quiz action below calls this first.
    private function ownedCourseOrFail(int $courseId): array
    {
        $course = Course::find($courseId);
        if (!$course || (int)$course['instructor_id'] !== Auth::id()) {
            http_response_code(404);
            require __DIR__ . '/../views/errors/404.php';
            exit;
        }
        return $course;
    }

    public function dashboard(): void
    {
        $this->guard();
        $courses = Course::byInstructor(Auth::id());

        $enrollmentTotal = 0;
        $questionTotal = 0;
        foreach ($courses as $course) {
            $enrollmentTotal += (int)$course['enrollment_count'];
            $quiz = Quiz::findByCourse((int)$course['id']);
            if ($quiz) {
                $questionTotal += QuizQuestion::countByQuiz((int)$quiz['id']);
            }
        }

        $recentAttempts = QuizAttempt::recentForInstructor(Auth::id(), 8);

        require __DIR__ . '/../views/instructor/dashboard.php';
    }

    public function courses(): void
    {
        $this->guard();
        $courses = Course::byInstructor(Auth::id());
        require __DIR__ . '/../views/instructor/courses.php';
    }

    public function courseEditForm(): void
    {
        $this->guard();
        $course = $this->ownedCourseOrFail((int)($_GET['id'] ?? 0));
        $errors = [];
        $old = [];
        require __DIR__ . '/../views/instructor/course_form.php';
    }

    public function courseUpdate(): void
    {
        $this->guard();
        Csrf::requireValid();

        $id = (int)($_POST['id'] ?? 0);
        $course = $this->ownedCourseOrFail($id);

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');

        $v = new Validator();
        $v->required($_POST, 'title', 'Title')->maxLength($_POST, 'title', 150, 'Title');
        $errors = $v->errors();

        if (!empty($errors)) {
            $old = $_POST;
            require __DIR__ . '/../views/instructor/course_form.php';
            return;
        }

        Course::update($id, $title, $description ?: null);
        flash_set('success', 'Course details updated.');
        redirect('instructor/courses');
    }

    // ---- Lessons (Course Builder) ----

    public function lessons(): void
    {
        $this->guard();
        $course = $this->ownedCourseOrFail((int)($_GET['course_id'] ?? 0));
        $lessons = Lesson::byCourse((int)$course['id']);
        require __DIR__ . '/../views/instructor/lessons.php';
    }

    public function lessonCreateForm(): void
    {
        $this->guard();
        $course = $this->ownedCourseOrFail((int)($_GET['course_id'] ?? 0));
        $errors = [];
        $old = [];
        require __DIR__ . '/../views/instructor/lesson_form.php';
    }

    public function lessonStore(): void
    {
        $this->guard();
        Csrf::requireValid();

        $courseId = (int)($_POST['course_id'] ?? 0);
        $course = $this->ownedCourseOrFail($courseId);

        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');

        $v = new Validator();
        $v->required($_POST, 'title', 'Title')->maxLength($_POST, 'title', 150, 'Title')
          ->required($_POST, 'content', 'Content');
        $errors = $v->errors();

        if (!empty($errors)) {
            $old = $_POST;
            require __DIR__ . '/../views/instructor/lesson_form.php';
            return;
        }

        Lesson::create($courseId, $title, $content);
        flash_set('success', 'Lesson added.');
        redirect('instructor/lessons?course_id=' . $courseId);
    }

    public function lessonEditForm(): void
    {
        $this->guard();
        $lesson = Lesson::find((int)($_GET['id'] ?? 0));
        if (!$lesson) {
            flash_set('error', 'Lesson not found.');
            redirect('instructor/courses');
        }
        $course = $this->ownedCourseOrFail((int)$lesson['course_id']);
        $errors = [];
        require __DIR__ . '/../views/instructor/lesson_edit.php';
    }

    public function lessonUpdate(): void
    {
        $this->guard();
        Csrf::requireValid();

        $id = (int)($_POST['id'] ?? 0);
        $lesson = Lesson::find($id);
        if (!$lesson) {
            flash_set('error', 'Lesson not found.');
            redirect('instructor/courses');
        }
        $course = $this->ownedCourseOrFail((int)$lesson['course_id']);

        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');

        $v = new Validator();
        $v->required($_POST, 'title', 'Title')->maxLength($_POST, 'title', 150, 'Title')
          ->required($_POST, 'content', 'Content');
        $errors = $v->errors();

        if (!empty($errors)) {
            require __DIR__ . '/../views/instructor/lesson_edit.php';
            return;
        }

        Lesson::update($id, $title, $content);
        flash_set('success', 'Lesson updated.');
        redirect('instructor/lessons?course_id=' . $lesson['course_id']);
    }

    public function lessonDelete(): void
    {
        $this->guard();
        Csrf::requireValid();

        $id = (int)($_POST['id'] ?? 0);
        $lesson = Lesson::find($id);
        if (!$lesson) {
            redirect('instructor/courses');
        }
        $course = $this->ownedCourseOrFail((int)$lesson['course_id']);

        Lesson::delete($id);
        flash_set('success', 'Lesson removed.');
        redirect('instructor/lessons?course_id=' . $course['id']);
    }

    public function lessonMove(): void
    {
        $this->guard();
        Csrf::requireValid();

        $id = (int)($_POST['id'] ?? 0);
        $direction = $_POST['direction'] === 'up' ? 'up' : 'down';
        $lesson = Lesson::find($id);
        if (!$lesson) {
            redirect('instructor/courses');
        }
        $course = $this->ownedCourseOrFail((int)$lesson['course_id']);

        Lesson::move($id, $direction);
        redirect('instructor/lessons?course_id=' . $course['id']);
    }

    // ---- Quiz Builder ----

    public function quiz(): void
    {
        $this->guard();
        $course = $this->ownedCourseOrFail((int)($_GET['course_id'] ?? 0));
        $quiz = Quiz::findByCourse((int)$course['id']);
        $questions = $quiz ? QuizQuestion::byQuizWithOptions((int)$quiz['id']) : [];
        $locked = $quiz && QuizAttempt::existsForQuiz((int)$quiz['id']);
        require __DIR__ . '/../views/instructor/quiz_builder.php';
    }

    public function quizStore(): void
    {
        $this->guard();
        Csrf::requireValid();

        $courseId = (int)($_POST['course_id'] ?? 0);
        $course = $this->ownedCourseOrFail($courseId);

        $existing = Quiz::findByCourse($courseId);
        if ($existing && QuizAttempt::existsForQuiz((int)$existing['id'])) {
            flash_set('error', 'A student has already attempted this quiz — its settings can no longer be changed, to keep already-recorded results accurate.');
            redirect('instructor/quiz?course_id=' . $courseId);
        }

        $title = trim($_POST['title'] ?? '');
        $passThreshold = (int)($_POST['pass_threshold_pct'] ?? 60);
        $maxAttempts = (int)($_POST['max_attempts'] ?? 2);

        $v = new Validator();
        $v->required($_POST, 'title', 'Quiz title')->maxLength($_POST, 'title', 150, 'Quiz title')
          ->numeric($_POST, 'pass_threshold_pct', 'Pass threshold')->min($_POST, 'pass_threshold_pct', 1, 'Pass threshold')->max($_POST, 'pass_threshold_pct', 100, 'Pass threshold')
          ->numeric($_POST, 'max_attempts', 'Max attempts')->min($_POST, 'max_attempts', 1, 'Max attempts')->max($_POST, 'max_attempts', 10, 'Max attempts');

        if ($v->fails()) {
            flash_set('error', 'Please fix the errors: ' . implode(' ', $v->errors()));
            redirect('instructor/quiz?course_id=' . $courseId);
        }

        Quiz::upsertForCourse($courseId, $title, $passThreshold, $maxAttempts);
        flash_set('success', 'Quiz settings saved.');
        redirect('instructor/quiz?course_id=' . $courseId);
    }

    private function collectOptionsFromPost(): array
    {
        $texts = $_POST['option_text'] ?? [];
        $correctRaw = $_POST['is_correct'] ?? []; // checkbox array of indices marked correct
        $options = [];
        foreach ($texts as $i => $text) {
            $text = trim((string)$text);
            if ($text === '') {
                continue;
            }
            $options[] = [
                'text' => $text,
                'is_correct' => in_array((string)$i, array_map('strval', $correctRaw), true),
            ];
        }
        return $options;
    }

    public function questionCreateForm(): void
    {
        $this->guard();
        $quiz = Quiz::find((int)($_GET['quiz_id'] ?? 0));
        if (!$quiz) {
            flash_set('error', 'Create the quiz settings first.');
            redirect('instructor/courses');
        }
        $course = $this->ownedCourseOrFail((int)$quiz['course_id']);
        if (QuizAttempt::existsForQuiz((int)$quiz['id'])) {
            flash_set('error', 'A student has already attempted this quiz — the question bank is locked, to keep already-recorded results accurate.');
            redirect('instructor/quiz?course_id=' . $course['id']);
        }
        $errors = [];
        require __DIR__ . '/../views/instructor/question_form.php';
    }

    public function questionStore(): void
    {
        $this->guard();
        Csrf::requireValid();

        $quizId = (int)($_POST['quiz_id'] ?? 0);
        $quiz = Quiz::find($quizId);
        if (!$quiz) {
            redirect('instructor/courses');
        }
        $course = $this->ownedCourseOrFail((int)$quiz['course_id']);
        if (QuizAttempt::existsForQuiz($quizId)) {
            flash_set('error', 'A student has already attempted this quiz — the question bank is locked, to keep already-recorded results accurate.');
            redirect('instructor/quiz?course_id=' . $course['id']);
        }

        $text = trim($_POST['question_text'] ?? '');
        $type = $_POST['question_type'] === 'multi' ? 'multi' : 'single';
        $options = $this->collectOptionsFromPost();
        $correctCount = count(array_filter($options, fn($o) => $o['is_correct']));

        $errors = [];
        if ($text === '') {
            $errors['question_text'] = 'Question text is required.';
        }
        if (count($options) < 2) {
            $errors['options'] = 'Provide at least 2 answer options.';
        }
        if ($correctCount === 0) {
            $errors['options'] = 'Mark at least one option as correct.';
        }
        if ($type === 'single' && $correctCount > 1) {
            $errors['options'] = 'A single-answer question can have only one correct option.';
        }

        if (!empty($errors)) {
            flash_set('error', implode(' ', $errors));
            redirect('instructor/quiz/questions/create?quiz_id=' . $quizId);
        }

        $questionId = QuizQuestion::create($quizId, $text, $type);
        QuizOption::replaceForQuestion($questionId, $options);

        flash_set('success', 'Question added.');
        redirect('instructor/quiz?course_id=' . $course['id']);
    }

    public function questionEditForm(): void
    {
        $this->guard();
        $question = QuizQuestion::find((int)($_GET['id'] ?? 0));
        if (!$question) {
            flash_set('error', 'Question not found.');
            redirect('instructor/courses');
        }
        $quiz = Quiz::find((int)$question['quiz_id']);
        $course = $this->ownedCourseOrFail((int)$quiz['course_id']);
        if (QuizAttempt::existsForQuiz((int)$quiz['id'])) {
            flash_set('error', 'A student has already attempted this quiz — the question bank is locked, to keep already-recorded results accurate.');
            redirect('instructor/quiz?course_id=' . $course['id']);
        }
        $options = QuizOption::byQuestion((int)$question['id']);
        require __DIR__ . '/../views/instructor/question_edit.php';
    }

    public function questionUpdate(): void
    {
        $this->guard();
        Csrf::requireValid();

        $id = (int)($_POST['id'] ?? 0);
        $question = QuizQuestion::find($id);
        if (!$question) {
            redirect('instructor/courses');
        }
        $quiz = Quiz::find((int)$question['quiz_id']);
        $course = $this->ownedCourseOrFail((int)$quiz['course_id']);
        if (QuizAttempt::existsForQuiz((int)$quiz['id'])) {
            flash_set('error', 'A student has already attempted this quiz — the question bank is locked, to keep already-recorded results accurate.');
            redirect('instructor/quiz?course_id=' . $course['id']);
        }

        $text = trim($_POST['question_text'] ?? '');
        $type = $_POST['question_type'] === 'multi' ? 'multi' : 'single';
        $options = $this->collectOptionsFromPost();
        $correctCount = count(array_filter($options, fn($o) => $o['is_correct']));

        $errors = [];
        if ($text === '') {
            $errors[] = 'Question text is required.';
        }
        if (count($options) < 2) {
            $errors[] = 'Provide at least 2 answer options.';
        }
        if ($correctCount === 0) {
            $errors[] = 'Mark at least one option as correct.';
        }
        if ($type === 'single' && $correctCount > 1) {
            $errors[] = 'A single-answer question can have only one correct option.';
        }

        if (!empty($errors)) {
            flash_set('error', implode(' ', $errors));
            redirect('instructor/quiz/questions/edit?id=' . $id);
        }

        QuizQuestion::update($id, $text, $type);
        QuizOption::replaceForQuestion($id, $options);

        flash_set('success', 'Question updated.');
        redirect('instructor/quiz?course_id=' . $course['id']);
    }

    public function questionDelete(): void
    {
        $this->guard();
        Csrf::requireValid();

        $id = (int)($_POST['id'] ?? 0);
        $question = QuizQuestion::find($id);
        if (!$question) {
            redirect('instructor/courses');
        }
        $quiz = Quiz::find((int)$question['quiz_id']);
        $course = $this->ownedCourseOrFail((int)$quiz['course_id']);
        if (QuizAttempt::existsForQuiz((int)$quiz['id'])) {
            flash_set('error', 'A student has already attempted this quiz — the question bank is locked, to keep already-recorded results accurate.');
            redirect('instructor/quiz?course_id=' . $course['id']);
        }

        QuizQuestion::delete($id);
        flash_set('success', 'Question removed.');
        redirect('instructor/quiz?course_id=' . $course['id']);
    }

    // ---- Gradebook ----

    public function gradebook(): void
    {
        $this->guard();
        $course = $this->ownedCourseOrFail((int)($_GET['course_id'] ?? 0));
        $quiz = Quiz::findByCourse((int)$course['id']);
        $lessonCount = (int)$course['lesson_count'];
        $roster = QuizAttempt::gradebookForCourse((int)$course['id']);

        require __DIR__ . '/../views/instructor/gradebook.php';
    }
}
