<?php $pageTitle = 'Quiz result'; require __DIR__ . '/../partials/header.php'; require __DIR__ . '/../partials/navbar.php'; ?>
<?php
$crumbs = [['My courses', 'student/dashboard'], [$quiz['course_title'], 'student/courses/view&id=' . (int)$quiz['course_id']], ['Attempt ' . (int)$attempt['attempt_number'] . ' result', null]];
require __DIR__ . '/../partials/breadcrumbs.php';

// Per-question verdict: right only if the selected set exactly matches the correct set.
$rightCount = 0;
foreach ($questions as $q) {
    $correctIds = array_map('intval', array_column(array_filter($q['options'], fn($o) => (int)$o['is_correct'] === 1), 'id'));
    $mine = $selections[(int)$q['id']] ?? [];
    sort($correctIds); sort($mine);
    if ($correctIds === $mine) { $rightCount++; }
}
$pct = (float)$attempt['score_pct'];
?>

<section class="ll-score <?= $attempt['passed'] ? 'is-pass' : 'is-fail' ?>">
    <div class="ll-score-ring" style="--pct: <?= max(0, min(100, $pct)) ?>"><span class="tnum"><?= format_pct($pct) ?></span></div>
    <div>
        <h1 class="h2 mb-1"><?= $attempt['passed'] ? 'Passed' : 'Not passed this time' ?></h1>
        <p class="text-secondary mb-0"><?= e($quiz['title']) ?> · attempt <?= (int)$attempt['attempt_number'] ?> · <span class="tnum"><?= $rightCount ?> of <?= count($questions) ?></span> correct · pass mark <?= (int)$quiz['pass_threshold_pct'] ?>%</p>
    </div>
</section>

<h2 class="ll-section-title">Answer review</h2>
<?php foreach ($questions as $i => $q): ?>
    <?php
        $mySelections = $selections[(int)$q['id']] ?? [];
        $correctIds = array_map('intval', array_column(array_filter($q['options'], fn($o) => (int)$o['is_correct'] === 1), 'id'));
        $a = $correctIds; $b = $mySelections; sort($a); sort($b);
        $gotIt = $a === $b;
    ?>
    <div class="ll-question-card">
        <div class="d-flex justify-content-between gap-3">
            <div class="ll-q-label">Question <?= $i + 1 ?></div>
            <span class="ll-q-verdict <?= $gotIt ? 'ok' : 'no' ?>"><?= $gotIt ? 'Correct' : ($mySelections ? 'Incorrect' : 'Not answered') ?></span>
        </div>
        <div class="ll-q-text"><?= e($q['question_text']) ?></div>
        <?php foreach ($q['options'] as $opt): ?>
            <?php
                $wasSelected = in_array((int)$opt['id'], $mySelections, true);
                $isCorrect = (int)$opt['is_correct'] === 1;
                $cls = $isCorrect ? 'is-correct' : ($wasSelected ? 'is-wrong' : '');
                $tag = $isCorrect ? ($wasSelected ? 'Your answer · correct' : 'Correct answer') : ($wasSelected ? 'Your answer' : '');
            ?>
            <div class="ll-review-opt <?= $cls ?>">
                <span><?= e($opt['option_text']) ?></span>
                <?php if ($tag): ?><span class="ll-review-tag"><?= $tag ?></span><?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endforeach; ?>

<a href="/index.php?route=student/courses/view&id=<?= (int)$quiz['course_id'] ?>" class="btn btn-outline-primary">Back to the course</a>

<?php require __DIR__ . '/../partials/footer.php'; ?>
