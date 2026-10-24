<?php

require_once __DIR__ . '/../../core/Database.php';

class Course
{
    private static function baseSelect(): string
    {
        return "SELECT c.*, u.name AS instructor_name,
                        (SELECT COUNT(*) FROM lessons l WHERE l.course_id = c.id) AS lesson_count,
                        (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id) AS enrollment_count
                 FROM courses c
                 JOIN users u ON u.id = c.instructor_id";
    }

    public static function all(): array
    {
        $stmt = Database::connect()->query(self::baseSelect() . ' ORDER BY c.created_at DESC');
        return $stmt->fetchAll();
    }

    public static function allActive(): array
    {
        $stmt = Database::connect()->query(self::baseSelect() . " WHERE c.status = 'active' ORDER BY c.title");
        return $stmt->fetchAll();
    }

    public static function byInstructor(int $instructorId): array
    {
        $stmt = Database::connect()->prepare(self::baseSelect() . ' WHERE c.instructor_id = ? ORDER BY c.created_at DESC');
        $stmt->execute([$instructorId]);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connect()->prepare(self::baseSelect() . ' WHERE c.id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function belongsToInstructor(int $courseId, int $instructorId): bool
    {
        $stmt = Database::connect()->prepare('SELECT COUNT(*) FROM courses WHERE id = ? AND instructor_id = ?');
        $stmt->execute([$courseId, $instructorId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public static function create(string $title, ?string $description, int $instructorId): int
    {
        $stmt = Database::connect()->prepare(
            "INSERT INTO courses (title, description, instructor_id, status) VALUES (?, ?, ?, 'active')"
        );
        $stmt->execute([$title, $description, $instructorId]);
        return (int)Database::connect()->lastInsertId();
    }

    public static function update(int $id, string $title, ?string $description): void
    {
        $stmt = Database::connect()->prepare('UPDATE courses SET title = ?, description = ? WHERE id = ?');
        $stmt->execute([$title, $description, $id]);
    }

    public static function reassign(int $id, string $title, ?string $description, int $instructorId): void
    {
        $stmt = Database::connect()->prepare(
            'UPDATE courses SET title = ?, description = ?, instructor_id = ? WHERE id = ?'
        );
        $stmt->execute([$title, $description, $instructorId, $id]);
    }

    // Deactivation, never deletion -- a course with progress/grade history attached
    // can't be hard-removed without orphaning it (see FEATURES.md design notes).
    public static function setStatus(int $id, string $status): void
    {
        $status = $status === 'active' ? 'active' : 'inactive';
        $stmt = Database::connect()->prepare('UPDATE courses SET status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);
    }

    public static function countTotal(): int
    {
        return (int)Database::connect()->query('SELECT COUNT(*) FROM courses')->fetchColumn();
    }

    // Per-course completion stats for the Super Admin analytics screen: a student counts
    // as "completed" once they've marked every lesson in that course done. Courses with
    // no lessons yet are excluded (nothing to complete, so a rate would be meaningless).
    // completed_count uses a double NOT EXISTS ("relational division") rather than a
    // GROUP BY/HAVING derived table: MySQL/MariaDB reject a correlated reference (c.id)
    // from inside a non-LATERAL derived table, so that form fails with a false "Unknown
    // column" error even though c.id exists on the outer query.
    public static function completionStats(): array
    {
        $stmt = Database::connect()->query(
            "SELECT c.id, c.title,
                    (SELECT COUNT(*) FROM lessons l WHERE l.course_id = c.id) AS lesson_count,
                    (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id) AS enrolled_count,
                    (SELECT COUNT(*) FROM enrollments e2
                      WHERE e2.course_id = c.id
                        AND NOT EXISTS (
                            SELECT 1 FROM lessons l3
                             WHERE l3.course_id = c.id
                               AND NOT EXISTS (
                                   SELECT 1 FROM lesson_progress lp
                                    WHERE lp.lesson_id = l3.id AND lp.student_id = e2.student_id
                               )
                        )
                    ) AS completed_count
             FROM courses c
             HAVING lesson_count > 0
             ORDER BY c.title"
        );
        $rows = $stmt->fetchAll();
        foreach ($rows as &$row) {
            $enrolled = (int)$row['enrolled_count'];
            $row['completion_rate_pct'] = $enrolled > 0 ? round(((int)$row['completed_count'] / $enrolled) * 100, 1) : 0.0;
        }
        return $rows;
    }

    public static function countActive(): int
    {
        return (int)Database::connect()->query("SELECT COUNT(*) FROM courses WHERE status = 'active'")->fetchColumn();
    }
}
