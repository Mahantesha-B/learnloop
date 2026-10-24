<?php

require_once __DIR__ . '/../../core/Database.php';

class QuizAttempt
{
    public static function countForStudent(int $quizId, int $studentId): int
    {
        $stmt = Database::connect()->prepare(
            'SELECT COUNT(*) FROM quiz_attempts WHERE quiz_id = ? AND student_id = ?'
        );
        $stmt->execute([$quizId, $studentId]);
        return (int)$stmt->fetchColumn();
    }

    // Whether any student has ever submitted an attempt on this quiz -- used to lock the
    // quiz's settings and question bank against edits that would desync already-recorded
    // results (QuizOption::replaceForQuestion() hard-deletes and re-inserts option rows,
    // and quiz_attempt_selections.option_id cascades on delete).
    public static function existsForQuiz(int $quizId): bool
    {
        $stmt = Database::connect()->prepare('SELECT COUNT(*) FROM quiz_attempts WHERE quiz_id = ?');
        $stmt->execute([$quizId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public static function byStudentQuiz(int $quizId, int $studentId): array
    {
        $stmt = Database::connect()->prepare(
            'SELECT * FROM quiz_attempts WHERE quiz_id = ? AND student_id = ? ORDER BY attempt_number'
        );
        $stmt->execute([$quizId, $studentId]);
        return $stmt->fetchAll();
    }

    // The student's highest-scoring attempt on this quiz -- the Gradebook and the
    // student's own grade view both report this, never the most recent attempt.
    public static function bestForStudent(int $quizId, int $studentId): ?array
    {
        $stmt = Database::connect()->prepare(
            'SELECT * FROM quiz_attempts WHERE quiz_id = ? AND student_id = ?
             ORDER BY score_pct DESC, attempt_number ASC LIMIT 1'
        );
        $stmt->execute([$quizId, $studentId]);
        return $stmt->fetch() ?: null;
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connect()->prepare('SELECT * FROM quiz_attempts WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    // Grades a submitted quiz against its answer key. $questions must include each
    // question's options (id + is_correct) -- see QuizQuestion::byQuizWithOptions().
    // $postedAnswers is $_POST['answers'] as-is: [question_id => option_id] for single-select,
    // [question_id => [option_id, ...]] for multi-select. A question counts as correct only
    // when the selected option set exactly matches the correct option set.
    public static function grade(array $questions, array $postedAnswers): array
    {
        $total = count($questions);
        $correctCount = 0;
        $selections = [];

        foreach ($questions as $q) {
            $qid = (int)$q['id'];
            $submitted = $postedAnswers[$qid] ?? [];
            if (!is_array($submitted)) {
                $submitted = [$submitted];
            }
            $submitted = array_values(array_unique(array_map('intval', $submitted)));
            sort($submitted);

            $correctIds = array_values(array_map(
                fn($o) => (int)$o['id'],
                array_filter($q['options'], fn($o) => (int)$o['is_correct'] === 1)
            ));
            sort($correctIds);

            if (count($submitted) > 0 && $submitted === $correctIds) {
                $correctCount++;
            }
            $selections[] = ['question_id' => $qid, 'option_ids' => $submitted];
        }

        $scorePct = $total > 0 ? round(($correctCount / $total) * 100, 2) : 0.0;

        return [
            'correct_count' => $correctCount,
            'total' => $total,
            'score_pct' => $scorePct,
            'selections' => $selections,
        ];
    }

    // Persists one graded attempt + its option selections in a single transaction.
    public static function record(
        int $quizId,
        int $studentId,
        int $attemptNumber,
        float $scorePct,
        bool $passed,
        string $startedAt,
        array $selections
    ): int {
        $pdo = Database::connect();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO quiz_attempts (quiz_id, student_id, attempt_number, score_pct, passed, started_at, submitted_at)
                 VALUES (?, ?, ?, ?, ?, ?, NOW())'
            );
            $stmt->execute([$quizId, $studentId, $attemptNumber, $scorePct, $passed ? 1 : 0, $startedAt]);
            $attemptId = (int)$pdo->lastInsertId();

            $selStmt = $pdo->prepare(
                'INSERT INTO quiz_attempt_selections (attempt_id, question_id, option_id) VALUES (?, ?, ?)'
            );
            foreach ($selections as $sel) {
                foreach ($sel['option_ids'] as $optionId) {
                    $selStmt->execute([$attemptId, $sel['question_id'], $optionId]);
                }
            }
            $pdo->commit();
            return $attemptId;
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    // [question_id => [selected option ids]] for one already-submitted attempt (result review).
    public static function selectionsFor(int $attemptId): array
    {
        $stmt = Database::connect()->prepare(
            'SELECT question_id, option_id FROM quiz_attempt_selections WHERE attempt_id = ?'
        );
        $stmt->execute([$attemptId]);
        $map = [];
        foreach ($stmt->fetchAll() as $row) {
            $map[(int)$row['question_id']][] = (int)$row['option_id'];
        }
        return $map;
    }

    // For the instructor Gradebook: one row per enrolled student with their best score,
    // pass/fail, and attempts used -- students who haven't attempted yet still appear
    // (LEFT JOIN against a best-attempt subquery), so the roster is never partial.
    public static function gradebookForCourse(int $courseId): array
    {
        $stmt = Database::connect()->prepare(
            "SELECT u.id AS student_id, u.name AS student_name, u.email AS student_email,
                    e.enrolled_at,
                    (SELECT COUNT(*) FROM quiz_attempts qa
                       JOIN quizzes q ON q.id = qa.quiz_id
                      WHERE q.course_id = c.id AND qa.student_id = u.id) AS attempts_used,
                    best.score_pct AS best_score_pct,
                    best.passed AS best_passed
             FROM enrollments e
             JOIN courses c ON c.id = e.course_id
             JOIN users u ON u.id = e.student_id
             LEFT JOIN quiz_attempts best ON best.id = (
                 SELECT qa2.id FROM quiz_attempts qa2
                  JOIN quizzes q2 ON q2.id = qa2.quiz_id
                 WHERE q2.course_id = c.id AND qa2.student_id = u.id
                 ORDER BY qa2.score_pct DESC, qa2.attempt_number ASC
                 LIMIT 1
             )
             WHERE c.id = ?
             ORDER BY u.name"
        );
        $stmt->execute([$courseId]);
        return $stmt->fetchAll();
    }

    // Most recent attempts across every course this instructor teaches, for their dashboard's
    // "recent quiz activity" widget.
    public static function recentForInstructor(int $instructorId, int $limit = 8): array
    {
        $limit = max(1, $limit);
        $stmt = Database::connect()->prepare(
            "SELECT qa.*, u.name AS student_name, c.title AS course_title
             FROM quiz_attempts qa
             JOIN quizzes q ON q.id = qa.quiz_id
             JOIN courses c ON c.id = q.course_id
             JOIN users u ON u.id = qa.student_id
             WHERE c.instructor_id = ?
             ORDER BY qa.submitted_at DESC
             LIMIT $limit"
        );
        $stmt->execute([$instructorId]);
        return $stmt->fetchAll();
    }

    public static function countTotal(): int
    {
        return (int)Database::connect()->query('SELECT COUNT(*) FROM quiz_attempts')->fetchColumn();
    }

    // Platform-wide pass rate per course, for the Super Admin analytics screen.
    // "Passed" is determined by each student's *best* attempt having its stored
    // quiz_attempts.passed flag set -- the same snapshot computed and persisted at
    // grading time that gradebookForCourse() and the student's own Grades page use,
    // not a live re-join against the quiz's *current* pass_threshold_pct (which could
    // since have been changed and would then disagree with those other screens for
    // the exact same already-graded attempts).
    public static function passRateByCourse(): array
    {
        $stmt = Database::connect()->query(
            "SELECT c.id, c.title,
                    (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id) AS enrolled_count,
                    (SELECT COUNT(DISTINCT qa.student_id) FROM quiz_attempts qa
                       JOIN quizzes q ON q.id = qa.quiz_id WHERE q.course_id = c.id) AS attempted_count,
                    (SELECT COUNT(DISTINCT qa2.student_id) FROM quiz_attempts qa2
                       JOIN quizzes q2 ON q2.id = qa2.quiz_id
                      WHERE q2.course_id = c.id AND qa2.passed = 1) AS passed_count
             FROM courses c
             ORDER BY c.title"
        );
        $rows = $stmt->fetchAll();
        foreach ($rows as &$row) {
            $attempted = (int)$row['attempted_count'];
            $row['pass_rate_pct'] = $attempted > 0 ? round(((int)$row['passed_count'] / $attempted) * 100, 1) : null;
        }
        return $rows;
    }
}
