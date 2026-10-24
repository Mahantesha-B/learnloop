<?php

require_once __DIR__ . '/../../core/Database.php';

class Quiz
{
    public static function findByCourse(int $courseId): ?array
    {
        $stmt = Database::connect()->prepare('SELECT * FROM quizzes WHERE course_id = ?');
        $stmt->execute([$courseId]);
        return $stmt->fetch() ?: null;
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connect()->prepare('SELECT * FROM quizzes WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function findWithCourse(int $id): ?array
    {
        $stmt = Database::connect()->prepare(
            'SELECT q.*, c.title AS course_title, c.instructor_id
             FROM quizzes q JOIN courses c ON c.id = q.course_id
             WHERE q.id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    // Create the course's quiz if it doesn't exist yet, else update its settings --
    // one quiz per course, enforced by both the UNIQUE key and this upsert.
    public static function upsertForCourse(int $courseId, string $title, int $passThreshold, int $maxAttempts): int
    {
        $existing = self::findByCourse($courseId);
        $pdo = Database::connect();
        if ($existing) {
            $stmt = $pdo->prepare(
                'UPDATE quizzes SET title = ?, pass_threshold_pct = ?, max_attempts = ? WHERE id = ?'
            );
            $stmt->execute([$title, $passThreshold, $maxAttempts, $existing['id']]);
            return (int)$existing['id'];
        }
        $stmt = $pdo->prepare(
            'INSERT INTO quizzes (course_id, title, pass_threshold_pct, max_attempts) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$courseId, $title, $passThreshold, $maxAttempts]);
        return (int)$pdo->lastInsertId();
    }

    public static function countTotal(): int
    {
        return (int)Database::connect()->query('SELECT COUNT(*) FROM quizzes')->fetchColumn();
    }
}
