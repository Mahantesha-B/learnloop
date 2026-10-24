<?php

require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/QuizOption.php';

class QuizQuestion
{
    public static function find(int $id): ?array
    {
        $stmt = Database::connect()->prepare('SELECT * FROM quiz_questions WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function byQuiz(int $quizId): array
    {
        $stmt = Database::connect()->prepare('SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY sort_order');
        $stmt->execute([$quizId]);
        return $stmt->fetchAll();
    }

    // Full quiz content (all questions + their options), for taking/grading a quiz.
    public static function byQuizWithOptions(int $quizId): array
    {
        $questions = self::byQuiz($quizId);
        foreach ($questions as &$q) {
            $q['options'] = QuizOption::byQuestion((int)$q['id']);
        }
        return $questions;
    }

    public static function nextSortOrder(int $quizId): int
    {
        $stmt = Database::connect()->prepare('SELECT COALESCE(MAX(sort_order), 0) + 1 FROM quiz_questions WHERE quiz_id = ?');
        $stmt->execute([$quizId]);
        return (int)$stmt->fetchColumn();
    }

    public static function create(int $quizId, string $text, string $type): int
    {
        $order = self::nextSortOrder($quizId);
        $stmt = Database::connect()->prepare(
            'INSERT INTO quiz_questions (quiz_id, question_text, question_type, sort_order) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$quizId, $text, $type, $order]);
        return (int)Database::connect()->lastInsertId();
    }

    public static function update(int $id, string $text, string $type): void
    {
        $stmt = Database::connect()->prepare(
            'UPDATE quiz_questions SET question_text = ?, question_type = ? WHERE id = ?'
        );
        $stmt->execute([$text, $type, $id]);
    }

    public static function delete(int $id): void
    {
        $question = self::find($id);
        if (!$question) {
            return;
        }
        $pdo = Database::connect();
        $pdo->beginTransaction();
        try {
            $pdo->prepare('DELETE FROM quiz_questions WHERE id = ?')->execute([$id]);
            $pdo->prepare('UPDATE quiz_questions SET sort_order = sort_order - 1 WHERE quiz_id = ? AND sort_order > ?')
                ->execute([$question['quiz_id'], $question['sort_order']]);
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function countByQuiz(int $quizId): int
    {
        $stmt = Database::connect()->prepare('SELECT COUNT(*) FROM quiz_questions WHERE quiz_id = ?');
        $stmt->execute([$quizId]);
        return (int)$stmt->fetchColumn();
    }
}
