<?php $pageTitle = $quiz['title']; require __DIR__ . '/../partials/header.php'; require __DIR__ . '/../partials/navbar.php'; ?>
<?php
$crumbs = [['My courses', 'student/dashboard'], [$course['title'], 'student/courses/view&id=' . (int)$course['id']], ['Quiz', null]];
require __DIR__ . '/../partials/breadcrumbs.php';
?>

<div class="ll-page-head">
    <div>
        <h1 class="ll-page-title"><?= e($quiz['title']) ?></h1>
        <p class="ll-page-sub">Pass mark <?= (int)$quiz['pass_threshold_pct'] ?>%<?= $attemptsRemaining > 0 ? ' · attempt ' . ($attemptsUsed + 1) . ' of ' . (int)$quiz['max_attempts'] : '' ?> · your best attempt is the one that counts.</p>
    </div>
</div>

<?php if ($attemptsRemaining <= 0): ?>
    <div class="card"><div class="ll-empty">
        <strong>You've used all <?= (int)$quiz['max_attempts'] ?> attempts</strong>
        Your best score is the one that counts.
        <div class="mt-3"><a href="/index.php?route=student/courses/view&id=<?= (int)$course['id'] ?>" class="btn btn-outline-primary">Back to the course</a></div>
    </div></div>
<?php elseif (empty($questions)): ?>
    <div class="card"><div class="ll-empty"><strong>No questions yet</strong>The instructor hasn't added questions to this quiz.</div></div>
<?php else: ?>
    <form method="POST" action="/index.php?route=student/quiz/submit" id="quiz-form">
        <?= Csrf::field() ?>
        <input type="hidden" name="course_id" value="<?= (int)$course['id'] ?>">

        <?php foreach ($questions as $i => $q): ?>
            <fieldset class="ll-question-card">
                <legend class="visually-hidden">Question <?= $i + 1 ?></legend>
                <div class="ll-q-label">Question <?= $i + 1 ?> of <?= count($questions) ?> · <?= $q['question_type'] === 'multi' ? 'select all that apply' : 'select one answer' ?></div>
                <div class="ll-q-text"><?= e($q['question_text']) ?></div>
                <?php foreach ($q['options'] as $opt): ?>
                    <label class="ll-option" for="opt<?= (int)$opt['id'] ?>">
                        <?php if ($q['question_type'] === 'multi'): ?>
                            <input class="form-check-input" type="checkbox" name="answers[<?= (int)$q['id'] ?>][]" value="<?= (int)$opt['id'] ?>" id="opt<?= (int)$opt['id'] ?>">
                        <?php else: ?>
                            <input class="form-check-input" type="radio" name="answers[<?= (int)$q['id'] ?>]" value="<?= (int)$opt['id'] ?>" id="opt<?= (int)$opt['id'] ?>">
                        <?php endif; ?>
                        <span><?= e($opt['option_text']) ?></span>
                    </label>
                <?php endforeach; ?>
            </fieldset>
        <?php endforeach; ?>

        <div class="ll-submit-bar">
            <span class="ll-answered"><strong class="text-body tnum" id="answered-count">0</strong> of <?= count($questions) ?> answered</span>
            <button type="submit" class="btn btn-primary">Submit answers</button>
        </div>
    </form>
<?php endif; ?>

<?php require __DIR__ . '/../partials/footer.php'; ?>
