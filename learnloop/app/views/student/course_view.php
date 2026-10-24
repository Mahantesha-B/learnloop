<?php $pageTitle = $course['title']; require __DIR__ . '/../partials/header.php'; require __DIR__ . '/../partials/navbar.php'; ?>
<?php
$crumbs = [['My courses', 'student/dashboard'], [$course['title'], null]];
require __DIR__ . '/../partials/breadcrumbs.php';

$doneCount = count(array_filter($lessons, fn($l) => $l['is_completed']));
$totalLessons = count($lessons);
// The "current" step is the first unlocked lesson not yet completed.
$currentId = null;
foreach ($lessons as $l) {
    if ($l['is_unlocked'] && !$l['is_completed']) { $currentId = (int)$l['id']; break; }
}
$check = '<svg viewBox="0 0 16 16" aria-hidden="true"><path d="M3.5 8.5l3 3 6-7" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
$lock = '<svg viewBox="0 0 16 16" aria-hidden="true"><rect x="3.5" y="7" width="9" height="6.5" rx="1.5" fill="currentColor"/><path d="M5.5 7V5.5a2.5 2.5 0 0 1 5 0V7" fill="none" stroke="currentColor" stroke-width="1.6"/></svg>';
?>

<div class="ll-course-head">
    <?= course_cover((int)$course['id'], $course['title']) ?>
    <div class="min-w-0">
        <h1 class="ll-page-title"><?= e($course['title']) ?></h1>
        <div class="text-secondary"><?= e($course['instructor_name']) ?> · <span class="tnum"><?= $doneCount ?> of <?= $totalLessons ?> lessons complete</span></div>
    </div>
</div>
<?php if ($course['status'] === 'inactive'): ?>
    <div class="ll-note"><strong>This course has been deactivated.</strong> It's no longer in the catalog, but you can still finish your lessons and quiz.</div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-8">
        <h2 class="ll-section-title">Learning path</h2>
        <?php if (empty($lessons)): ?>
            <div class="card"><div class="ll-empty"><strong>No lessons published yet</strong>Check back soon.</div></div>
        <?php else: ?>
        <ol class="ll-path">
            <?php foreach ($lessons as $lesson): ?>
                <?php
                    $state = $lesson['is_completed'] ? 'is-completed' : ((int)$lesson['id'] === $currentId ? 'is-current' : (!$lesson['is_unlocked'] ? 'is-locked' : ''));
                ?>
                <li class="ll-path-step <?= $state ?>">
                    <span class="ll-path-dot"><?= $lesson['is_completed'] ? $check : (!$lesson['is_unlocked'] ? $lock : (int)$lesson['sort_order']) ?></span>
                    <div class="ll-path-body">
                        <div class="min-w-0">
                            <div class="ll-path-meta">Lesson <?= (int)$lesson['sort_order'] ?><?= $lesson['is_completed'] ? ' · completed' : ((int)$lesson['id'] === $currentId ? ' · up next' : '') ?></div>
                            <div class="ll-path-title"><?= e($lesson['title']) ?></div>
                        </div>
                        <?php if ($lesson['is_unlocked']): ?>
                            <a href="/index.php?route=student/lessons/view&id=<?= (int)$lesson['id'] ?>" class="btn btn-sm <?= (int)$lesson['id'] === $currentId ? 'btn-primary' : 'btn-outline-secondary' ?>"><?= $lesson['is_completed'] ? 'Review' : 'Start lesson' ?></a>
                        <?php else: ?>
                            <span class="ll-path-meta text-nowrap">Unlocks after lesson <?= (int)$lesson['sort_order'] - 1 ?></span>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ol>
        <?php endif; ?>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">Course quiz</div>
            <div class="card-body">
                <?php if (!$quiz): ?>
                    <p class="text-secondary mb-0">No quiz has been published for this course yet.</p>
                <?php else: ?>
                    <p class="fw-semibold text-body mb-1"><?= e($quiz['title']) ?></p>
                    <p class="text-secondary small mb-3">Pass mark <?= (int)$quiz['pass_threshold_pct'] ?>% · <?= (int)$quiz['max_attempts'] ?> attempt<?= (int)$quiz['max_attempts'] === 1 ? '' : 's' ?> · your best score counts</p>

                    <?php if ($bestAttempt): ?>
                        <div class="d-flex justify-content-between align-items-center mb-3 p-2 px-3 rounded" style="background: var(--ll-ground);">
                            <span>Best score <strong class="tnum"><?= format_pct($bestAttempt['score_pct']) ?></strong></span>
                            <span class="badge-status badge-<?= $bestAttempt['passed'] ? 'pass' : 'fail' ?>"><?= $bestAttempt['passed'] ? 'Passed' : 'Not passed' ?></span>
                        </div>
                    <?php endif; ?>

                    <?php $remaining = (int)$quiz['max_attempts'] - count($attempts); ?>
                    <?php if ($remaining > 0): ?>
                        <a href="/index.php?route=student/quiz/take&course_id=<?= (int)$course['id'] ?>" class="btn btn-primary w-100">
                            <?= count($attempts) === 0 ? 'Take the quiz' : 'Retake the quiz' ?>
                        </a>
                        <p class="text-secondary small text-center mt-2 mb-0"><?= $remaining ?> attempt<?= $remaining === 1 ? '' : 's' ?> left</p>
                    <?php else: ?>
                        <p class="text-secondary small mb-0">You've used all <?= (int)$quiz['max_attempts'] ?> attempts.</p>
                    <?php endif; ?>

                    <?php if (!empty($attempts)): ?>
                        <h3 class="h6 mt-4 mb-2">Your attempts</h3>
                        <?php foreach ($attempts as $a): ?>
                            <a href="/index.php?route=student/quiz/result&id=<?= (int)$a['id'] ?>" class="d-flex justify-content-between align-items-center py-2 border-top text-body">
                                <span>Attempt <?= (int)$a['attempt_number'] ?></span>
                                <span class="tnum"><?= format_pct($a['score_pct']) ?> <span class="text-primary ms-2 small fw-semibold">Review</span></span>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
