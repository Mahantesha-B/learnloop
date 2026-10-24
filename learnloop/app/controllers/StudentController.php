<?php

require_once __DIR__ . '/../models/Course.php';
require_once __DIR__ . '/../models/Lesson.php';
require_once __DIR__ . '/../models/Enrollment.php';
require_once __DIR__ . '/../models/LessonProgress.php';
require_once __DIR__ . '/../models/Quiz.php';
require_once __DIR__ . '/../models/QuizQuestion.php';
require_once __DIR__ . '/../models/QuizAttempt.php';

class StudentController
{
    private function guard(): void
    {
        Auth::requireRole('student');
    }

    private function enrolledCourseOrFail(int $courseId): array
    {
        $course = Course::find($courseId);
        if (!$course || !Enrollment::isEnrolled(Auth::id(), $courseId)) {
            http_response_code(404);
            require __DIR__ . '/../views/errors/404.php';
            exit;
        }
        return $course;
    }

    public function dashboard(): void
    {
        $this->guard();
        $enrollments = Enrollment::byStudent(Auth::id());

        foreach ($enrollments as &$en) {
            $completed = LessonProgress::countCompleted(Auth::id(), (int)$en['course_id']);
            $en['completed_lessons'] = $completed;
            $en['progress_pct'] = percent($completed, (int)$en['lesson_count']);

            $quiz = Quiz::findByCourse((int)$en['course_id']);
            $en['quiz'] = $quiz;
            $en['best_attempt'] = $quiz ? QuizAttempt::bestForStudent((int)$quiz['id'], Auth::id()) : null;
        }
        unset($en);

        require __DIR__ . '/../views/student/dashboard.php';
    }

    public function catalog(): void
    {
        $this->guard();
        $courses = Course::allActive();
        $enrolledIds = array_map(fn($e) => (int)$e['course_id'], Enrollment::byStudent(Auth::id()));

        require __DIR__ . '/../views/student/catalog.php';
    }

    public function enroll(): void
    {
        $this->guard();
        Csrf::requireValid();

        $courseId = (int)($_POST['course_id'] ?? 0);
        $course = Course::find($courseId);

        if (!$course || $course['status'] !== 'active') {
            flash_set('error', 'This course is not available for enrollment.');
            redirect('student/catalog');
        }
        if (Enrollment::isEnrolled(Auth::id(), $courseId)) {
            flash_set('error', 'You are already enrolled in this course.');
            redirect('student/catalog');
        }

        Enrollment::create(Auth::id(), $courseId);
        flash_set('success', 'Enrolled in "' . $course['title'] . '". Lesson 1 is unlocked -- good luck!');
        redirect('student/courses/view?id=' . $courseId);
    }

    public function courseView(): void
    {
        $this->guard();
        $course = $this->enrolledCourseOrFail((int)($_GET['id'] ?? 0));

        $lessons = Lesson::byCourse((int)$course['id']);
        $completedIds = LessonProgress::completedLessonIds(Auth::id(), (int)$course['id']);
        $completedOrders = [];
        foreach ($lessons as $l) {
            if (in_array((int)$l['id'], $completedIds, true)) {
                $completedOrders[] = (int)$l['sort_order'];
            }
        }

        foreach ($lessons as &$lesson) {
            $lesson['is_completed'] = in_array((int)$lesson['id'], $completedIds, true);
            // Lesson 1 (sort_order 1) is always unlocked; any later lesson unlocks only
            // once the immediately preceding one has been completed -- mirrors the
            // server-side check in lessonView()/lessonComplete(), just for display here.
            $lesson['is_unlocked'] = (int)$lesson['sort_order'] === 1
                || in_array((int)$lesson['sort_order'] - 1, $completedOrders, true);
        }
        unset($lesson);

        $quiz = Quiz::findByCourse((int)$course['id']);
        $attempts = $quiz ? QuizAttempt::byStudentQuiz((int)$quiz['id'], Auth::id()) : [];
        $bestAttempt = $quiz ? QuizAttempt::bestForStudent((int)$quiz['id'], Auth::id()) : null;

        require __DIR__ . '/../views/student/course_view.php';
    }

    public function lessonView(): void
    {
        $this->guard();
        $lesson = Lesson::find((int)($_GET['id'] ?? 0));
        if (!$lesson) {
            http_response_code(404);
            require __DIR__ . '/../views/errors/404.php';
            exit;
        }
        $course = $this->enrolledCourseOrFail((int)$lesson['course_id']);

        // Sequential unlock, enforced here -- not just a disabled button in the UI.
        // Lesson 1 is always reachable; anything later requires the previous lesson
        // (by sort_order) to already be marked complete for this student.
        $previous = Lesson::previous($lesson);
        if ($previous !== null && !LessonProgress::isCompleted(Auth::id(), (int)$previous['id'])) {
            flash_set('error', 'Complete "' . $previous['title'] . '" before moving on to this lesson.');
            redirect('student/courses/view?id=' . $course['id']);
        }

        $isCompleted = LessonProgress::isCompleted(Auth::id(), (int)$lesson['id']);
        $lessons = Lesson::byCourse((int)$course['id']);

        require __DIR__ . '/../views/student/lesson_view.php';
    }

    public function lessonComplete(): void
    {
        $this->guard();
        Csrf::requireValid();

        $lessonId = (int)($_POST['lesson_id'] ?? 0);
        $lesson = Lesson::find($lessonId);
        if (!$lesson) {
            redirect('student/dashboard');
        }
        $course = $this->enrolledCourseOrFail((int)$lesson['course_id']);

        $previous = Lesson::previous($lesson);
        if ($previous !== null && !LessonProgress::isCompleted(Auth::id(), (int)$previous['id'])) {
            flash_set('error', 'Complete the previous lesson first.');
            redirect('student/courses/view?id=' . $course['id']);
        }

        LessonProgress::markComplete(Auth::id(), $lessonId);
        flash_set('success', '"' . $lesson['title'] . '" marked complete.');
        redirect('student/lessons/view?id=' . $lessonId);
    }

    public function quizTake(): void
    {
        $this->guard();
        $course = $this->enrolledCourseOrFail((int)($_GET['course_id'] ?? 0));
        $quiz = Quiz::findByCourse((int)$course['id']);
        if (!$quiz) {
            flash_set('error', 'This course does not have a quiz yet.');
            redirect('student/courses/view?id=' . $course['id']);
        }

        $attemptsUsed = QuizAttempt::countForStudent((int)$quiz['id'], Auth::id());
        $attempts = QuizAttempt::byStudentQuiz((int)$quiz['id'], Auth::id());
        $attemptsRemaining = (int)$quiz['max_attempts'] - $attemptsUsed;

        $questions = $attemptsRemaining > 0 ? QuizQuestion::byQuizWithOptions((int)$quiz['id']) : [];

        require __DIR__ . '/../views/student/quiz_take.php';
    }

    public function quizSubmit(): void
    {
        $this->guard();
        Csrf::requireValid();

        $courseId = (int)($_POST['course_id'] ?? 0);
        $course = $this->enrolledCourseOrFail($courseId);
        $quiz = Quiz::findByCourse((int)$course['id']);
        if (!$quiz) {
            redirect('student/courses/view?id=' . $courseId);
        }

        // Re-check the attempt cap server-side, right before writing -- the count comes
        // from quiz_attempts itself, not anything the client submitted, so re-posting
        // this form (or crafting a request directly) can never buy an extra attempt.
        $attemptsUsed = QuizAttempt::countForStudent((int)$quiz['id'], Auth::id());
        if ($attemptsUsed >= (int)$quiz['max_attempts']) {
            flash_set('error', 'You have used all ' . $quiz['max_attempts'] . ' allowed attempts for this quiz.');
            redirect('student/courses/view?id=' . $courseId);
        }

        $questions = QuizQuestion::byQuizWithOptions((int)$quiz['id']);
        $postedAnswers = $_POST['answers'] ?? [];
        // Normalize keys to int question ids regardless of PHP's string-keyed array quirks.
        $normalized = [];
        foreach ($postedAnswers as $qid => $val) {
            $normalized[(int)$qid] = $val;
        }

        $result = QuizAttempt::grade($questions, $normalized);
        $passed = $result['score_pct'] >= (float)$quiz['pass_threshold_pct'];

        $attemptId = QuizAttempt::record(
            (int)$quiz['id'],
            Auth::id(),
            $attemptsUsed + 1,
            $result['score_pct'],
            $passed,
            date('Y-m-d H:i:s'),
            $result['selections']
        );

        redirect('student/quiz/result?id=' . $attemptId);
    }

    public function quizResult(): void
    {
        $this->guard();
        $attempt = QuizAttempt::find((int)($_GET['id'] ?? 0));
        if (!$attempt || (int)$attempt['student_id'] !== Auth::id()) {
            http_response_code(404);
            require __DIR__ . '/../views/errors/404.php';
            exit;
        }
        $quiz = Quiz::findWithCourse((int)$attempt['quiz_id']);
        $questions = QuizQuestion::byQuizWithOptions((int)$quiz['id']);
        $selections = QuizAttempt::selectionsFor((int)$attempt['id']);

        require __DIR__ . '/../views/student/quiz_result.php';
    }

    public function grades(): void
    {
        $this->guard();
        $enrollments = Enrollment::byStudent(Auth::id());

        foreach ($enrollments as &$en) {
            $completed = LessonProgress::countCompleted(Auth::id(), (int)$en['course_id']);
            $en['completed_lessons'] = $completed;
            $en['progress_pct'] = percent($completed, (int)$en['lesson_count']);

            $quiz = Quiz::findByCourse((int)$en['course_id']);
            $en['quiz'] = $quiz;
            $en['best_attempt'] = $quiz ? QuizAttempt::bestForStudent((int)$quiz['id'], Auth::id()) : null;
            $en['attempts_used'] = $quiz ? QuizAttempt::countForStudent((int)$quiz['id'], Auth::id()) : 0;
        }
        unset($en);

        require __DIR__ . '/../views/student/grades.php';
    }
}
