<?php

require_once __DIR__ . '/../../core/Database.php';

class Enrollment
{
    public static function isEnrolled(int $studentId, int $courseId): bool
    {
        $stmt = Database::connect()->prepare(
            'SELECT COUNT(*) FROM enrollments WHERE student_id = ? AND course_id = ?'
        );
        $stmt->execute([$studentId, $courseId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public static function create(int $studentId, int $courseId): void
    {
        $stmt = Database::connect()->prepare(
            'INSERT INTO enrollments (student_id, course_id, enrolled_at) VALUES (?, ?, NOW())'
        );
        $stmt->execute([$studentId, $courseId]);
    }

    public static function byStudent(int $studentId): array
    {
        $stmt = Database::connect()->prepare(
            "SELECT e.*, c.title, c.description, c.status AS course_status, c.instructor_id,
                    u.name AS instructor_name,
                    (SELECT COUNT(*) FROM lessons l WHERE l.course_id = c.id) AS lesson_count
             FROM enrollments e
             JOIN courses c ON c.id = e.course_id
             JOIN users u ON u.id = c.instructor_id
             WHERE e.student_id = ?
             ORDER BY e.enrolled_at DESC"
        );
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }

    public static function byCourse(int $courseId): array
    {
        $stmt = Database::connect()->prepare(
            "SELECT e.*, u.name AS student_name, u.email AS student_email
             FROM enrollments e
             JOIN users u ON u.id = e.student_id
             WHERE e.course_id = ?
             ORDER BY u.name"
        );
        $stmt->execute([$courseId]);
        return $stmt->fetchAll();
    }

    public static function countByCourse(int $courseId): int
    {
        $stmt = Database::connect()->prepare('SELECT COUNT(*) FROM enrollments WHERE course_id = ?');
        $stmt->execute([$courseId]);
        return (int)$stmt->fetchColumn();
    }

    public static function countTotal(): int
    {
        return (int)Database::connect()->query('SELECT COUNT(*) FROM enrollments')->fetchColumn();
    }
}
