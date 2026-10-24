<?php $pageTitle = 'Quiz · ' . $course['title']; require __DIR__ . '/../partials/header.php'; require __DIR__ . '/../partials/navbar.php'; ?>
<?php $courseTab = 'quiz'; require __DIR__ . '/../partials/course_tabs.php'; ?>

<?php if ($locked): ?>
    <div class="ll-note"><strong>This quiz is locked.</strong> A student has already attempted it, so its settings and questions can't change — that keeps every recorded score accurate.</div>
<?php else: ?>
    <p class="ll-page-sub mb-3">One quiz per course, multiple-choice only. Students get up to the maximum number of attempts, and their best score counts.</p>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header"><?= $quiz ? 'Quiz settings' : 'Create the quiz' ?></div>
    <div class="card-body">
        <form method="POST" action="/index.php?route=instructor/quiz/store" class="row g-3 align-items-end">
            <?= Csrf::field() ?>
            <input type="hidden" name="course_id" value="<?= (int)$course['id'] ?>">
            <div class="col-md-6">
                <label class="form-label" for="title">Quiz title</label>
                <input type="text" class="form-control" id="title" name="title" value="<?= e($quiz['title'] ?? $course['title'] . ' Quiz') ?>" required <?= $locked ? 'disabled' : '' ?>>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label" for="pass_threshold_pct">Pass mark (%)</label>
                <input type="number" class="form-control" id="pass_threshold_pct" name="pass_threshold_pct" min="1" max="100" value="<?= (int)($quiz['pass_threshold_pct'] ?? 60) ?>" required <?= $locked ? 'disabled' : '' ?>>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label" for="max_attempts">Max attempts</label>
                <input type="number" class="form-control" id="max_attempts" name="max_attempts" min="1" max="10" value="<?= (int)($quiz['max_attempts'] ?? 2) ?>" required <?= $locked ? 'disabled' : '' ?>>
            </div>
            <?php if (!$locked): ?>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><?= $quiz ? 'Save settings' : 'Create quiz' ?></button>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<?php if ($quiz): ?>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="ll-section-title mb-0">Questions <span class="text-secondary fw-normal">(<?= count($questions) ?>)</span></h2>
        <?php if (!$locked): ?>
            <a href="/index.php?route=instructor/quiz/questions/create&quiz_id=<?= (int)$quiz['id'] ?>" class="btn btn-primary">Add question</a>
        <?php endif; ?>
    </div>

    <?php foreach ($questions as $i => $q): ?>
        <div class="ll-question-card">
            <div class="d-flex justify-content-between align-items-start gap-3">
                <div class="min-w-0">
                    <div class="ll-q-label">Question <?= $i + 1 ?> · <?= $q['question_type'] === 'multi' ? 'multiple correct answers' : 'one correct answer' ?></div>
                    <div class="ll-q-text mb-2"><?= e($q['question_text']) ?></div>
                    <?php foreach ($q['options'] as $opt): ?>
                        <div class="ll-key-opt <?= $opt['is_correct'] ? 'is-correct' : '' ?>"><?= e($opt['option_text']) ?><?= $opt['is_correct'] ? ' <span class="small fw-normal">— correct</span>' : '' ?></div>
                    <?php endforeach; ?>
                </div>
                <?php if (!$locked): ?>
                <div class="d-flex gap-1 flex-shrink-0">
                    <a href="/index.php?route=instructor/quiz/questions/edit&id=<?= (int)$q['id'] ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                    <form method="POST" action="/index.php?route=instructor/quiz/questions/delete" onsubmit="return confirm('Delete this question?');">
                        <?= Csrf::field() ?>
                        <input type="hidden" name="id" value="<?= (int)$q['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (empty($questions)): ?>
        <div class="card"><div class="ll-empty"><strong>No questions yet</strong>Students can't take this quiz until it has at least one question.</div></div>
    <?php endif; ?>
<?php else: ?>
    <div class="card"><div class="ll-empty"><strong>No quiz yet</strong>Save the settings above to create it, then add questions.</div></div>
<?php endif; ?>

<?php require __DIR__ . '/../partials/footer.php'; ?>
