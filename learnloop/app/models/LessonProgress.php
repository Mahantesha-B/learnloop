<?php

require_once __DIR__ . '/../../core/Database.php';

class LessonProgress
{
    public static function isCompleted(int $studentId, int $lessonId): bool
    {
        $stmt = Database::connect()->prepare(
            'SELECT COUNT(*) FROM lesson_progress WHERE student_id = ? AND lesson_id = ?'
        );
        $stmt->execute([$studentId, $lessonId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public static function markComplete(int $studentId, int $lessonId): void
    {
        if (self::isCompleted($studentId, $lessonId)) {
            return;
        }
        $stmt = Database::connect()->prepare(
            'INSERT INTO lesson_progress (student_id, lesson_id, completed_at) VALUES (?, ?, NOW())'
        );
        $stmt->execute([$studentId, $lessonId]);
    }

    // Set of lesson ids this student has completed within one course, keyed by lesson id.
    public static function completedLessonIds(int $studentId, int $courseId): array
    {
        $stmt = Database::connect()->prepare(
            'SELECT lp.lesson_id FROM lesson_progress lp
             JOIN lessons l ON l.id = lp.lesson_id
             WHERE lp.student_id = ? AND l.course_id = ?'
        );
        $stmt->execute([$studentId, $courseId]);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    public static function countCompleted(int $studentId, int $courseId): int
    {
        return count(self::completedLessonIds($studentId, $courseId));
    }
}
