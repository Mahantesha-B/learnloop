<?php

require_once __DIR__ . '/../../core/Database.php';

class QuizOption
{
    public static function byQuestion(int $questionId): array
    {
        $stmt = Database::connect()->prepare('SELECT * FROM quiz_options WHERE question_id = ? ORDER BY sort_order');
        $stmt->execute([$questionId]);
        return $stmt->fetchAll();
    }

    // Replaces every option for a question in one go -- the question edit/create form
    // posts the full option list each time rather than diffing individual rows.
    public static function replaceForQuestion(int $questionId, array $options): void
    {
        $pdo = Database::connect();
        $pdo->beginTransaction();
        try {
            $pdo->prepare('DELETE FROM quiz_options WHERE question_id = ?')->execute([$questionId]);
            $stmt = $pdo->prepare(
                'INSERT INTO quiz_options (question_id, option_text, is_correct, sort_order) VALUES (?, ?, ?, ?)'
            );
            foreach ($options as $i => $opt) {
                $stmt->execute([$questionId, $opt['text'], $opt['is_correct'] ? 1 : 0, $i + 1]);
            }
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function correctOptionIds(int $questionId): array
    {
        $stmt = Database::connect()->prepare('SELECT id FROM quiz_options WHERE question_id = ? AND is_correct = 1');
        $stmt->execute([$questionId]);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }
}
