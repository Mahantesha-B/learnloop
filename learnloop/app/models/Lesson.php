<?php

require_once __DIR__ . '/../../core/Database.php';

class Lesson
{
    public static function byCourse(int $courseId): array
    {
        $stmt = Database::connect()->prepare('SELECT * FROM lessons WHERE course_id = ? ORDER BY sort_order');
        $stmt->execute([$courseId]);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connect()->prepare('SELECT * FROM lessons WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    // The lesson immediately before this one in its course's sequence, or null if this is lesson 1.
    public static function previous(array $lesson): ?array
    {
        $stmt = Database::connect()->prepare(
            'SELECT * FROM lessons WHERE course_id = ? AND sort_order = ?'
        );
        $stmt->execute([$lesson['course_id'], (int)$lesson['sort_order'] - 1]);
        return $stmt->fetch() ?: null;
    }

    public static function nextSortOrder(int $courseId): int
    {
        $stmt = Database::connect()->prepare('SELECT COALESCE(MAX(sort_order), 0) + 1 FROM lessons WHERE course_id = ?');
        $stmt->execute([$courseId]);
        return (int)$stmt->fetchColumn();
    }

    public static function create(int $courseId, string $title, string $content): int
    {
        $order = self::nextSortOrder($courseId);
        $stmt = Database::connect()->prepare(
            'INSERT INTO lessons (course_id, title, content, sort_order) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$courseId, $title, $content, $order]);
        return (int)Database::connect()->lastInsertId();
    }

    public static function update(int $id, string $title, string $content): void
    {
        $stmt = Database::connect()->prepare('UPDATE lessons SET title = ?, content = ? WHERE id = ?');
        $stmt->execute([$title, $content, $id]);
    }

    // Deletes the lesson and closes the gap in sort_order so the sequence stays contiguous
    // (1..N with no hole), which is what the "previous lesson" unlock check depends on.
    public static function delete(int $id): void
    {
        $pdo = Database::connect();
        $lesson = self::find($id);
        if (!$lesson) {
            return;
        }
        $pdo->beginTransaction();
        try {
            $pdo->prepare('DELETE FROM lessons WHERE id = ?')->execute([$id]);
            $pdo->prepare('UPDATE lessons SET sort_order = sort_order - 1 WHERE course_id = ? AND sort_order > ?')
                ->execute([$lesson['course_id'], $lesson['sort_order']]);
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    // Swaps this lesson's position with its neighbor in the given direction ('up'/'down').
    public static function move(int $id, string $direction): void
    {
        $lesson = self::find($id);
        if (!$lesson) {
            return;
        }
        $targetOrder = $direction === 'up' ? (int)$lesson['sort_order'] - 1 : (int)$lesson['sort_order'] + 1;

        $pdo = Database::connect();
        $stmt = $pdo->prepare('SELECT * FROM lessons WHERE course_id = ? AND sort_order = ?');
        $stmt->execute([$lesson['course_id'], $targetOrder]);
        $neighbor = $stmt->fetch();
        if (!$neighbor) {
            return; // already at the boundary
        }

        $pdo->beginTransaction();
        try {
            // Stage through a placeholder order (0, never used by a real lesson since
            // sort_order starts at 1) -- swapping the two target values directly would
            // collide with the uniq_course_order(course_id, sort_order) constraint,
            // since MySQL checks UNIQUE per-statement, not deferred to commit.
            $pdo->prepare('UPDATE lessons SET sort_order = 0 WHERE id = ?')->execute([$lesson['id']]);
            $pdo->prepare('UPDATE lessons SET sort_order = ? WHERE id = ?')->execute([$lesson['sort_order'], $neighbor['id']]);
            $pdo->prepare('UPDATE lessons SET sort_order = ? WHERE id = ?')->execute([$targetOrder, $lesson['id']]);
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function countByCourse(int $courseId): int
    {
        $stmt = Database::connect()->prepare('SELECT COUNT(*) FROM lessons WHERE course_id = ?');
        $stmt->execute([$courseId]);
        return (int)$stmt->fetchColumn();
    }
}
