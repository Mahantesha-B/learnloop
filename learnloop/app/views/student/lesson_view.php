<?php $pageTitle = $lesson['title']; require __DIR__ . '/../partials/header.php'; require __DIR__ . '/../partials/navbar.php'; ?>
<?php
$crumbs = [['My courses', 'student/dashboard'], [$course['title'], 'student/courses/view&id=' . (int)$course['id']], ['Lesson ' . (int)$lesson['sort_order'], null]];
require __DIR__ . '/../partials/breadcrumbs.php';

$prevLesson = null;
$nextLesson = null;
foreach ($lessons as $l) {
    if ((int)$l['sort_order'] === (int)$lesson['sort_order'] - 1) { $prevLesson = $l; }
    if ((int)$l['sort_order'] === (int)$lesson['sort_order'] + 1) { $nextLesson = $l; }
}
$totalLessons = count($lessons);
$position = (int)$lesson['sort_order'];
?>

<div class="ll-lesson-meta">
    <span class="tnum">Lesson <?= $position ?> of <?= $totalLessons ?></span>
    <div class="progress" role="progressbar" aria-label="Position in course" aria-valuenow="<?= percent($position, $totalLessons) ?>" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar" style="width: <?= percent($position, $totalLessons) ?>%"></div></div>
</div>
<h1 class="ll-page-title mb-3"><?= e($lesson['title']) ?></h1>

<article class="ll-lesson-content">
    <div class="ll-prose"><?= render_lesson_content($lesson['content']) ?></div>
</article>

<?php if ($isCompleted): ?>
    <div class="ll-lesson-done">
        <svg viewBox="0 0 20 20" aria-hidden="true"><circle cx="10" cy="10" r="9" fill="currentColor"/><path d="M5.8 10.3l2.8 2.8 5.6-6" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        You've completed this lesson
    </div>
<?php else: ?>
    <form method="POST" action="/index.php?route=student/lessons/complete" class="d-flex align-items-center gap-3 flex-wrap">
        <?= Csrf::field() ?>
        <input type="hidden" name="lesson_id" value="<?= (int)$lesson['id'] ?>">
        <button type="submit" class="btn btn-primary">Mark as complete</button>
        <?php if ($nextLesson): ?><span class="text-secondary small">Completing this lesson unlocks lesson <?= $position + 1 ?>.</span><?php endif; ?>
    </form>
<?php endif; ?>

<div class="ll-lesson-nav">
    <div>
        <?php if ($prevLesson): ?>
            <a href="/index.php?route=student/lessons/view&id=<?= (int)$prevLesson['id'] ?>" class="btn btn-outline-secondary btn-sm">← <?= e($prevLesson['title']) ?></a>
        <?php endif; ?>
    </div>
    <div>
        <?php if ($nextLesson && $isCompleted): ?>
            <a href="/index.php?route=student/lessons/view&id=<?= (int)$nextLesson['id'] ?>" class="btn btn-primary btn-sm">Next: <?= e($nextLesson['title']) ?> →</a>
        <?php elseif ($nextLesson): ?>
            <span class="text-secondary small">Next: <?= e($nextLesson['title']) ?> (locked)</span>
        <?php else: ?>
            <a href="/index.php?route=student/courses/view&id=<?= (int)$course['id'] ?>" class="btn btn-outline-primary btn-sm">Back to the course</a>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
