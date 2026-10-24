<?php
/**
 * Instructor course header: breadcrumb, course title, and the tab bar for the course's tools.
 * @var array  $course
 * @var string $courseTab  'lessons' | 'quiz' | 'gradebook' | 'details'
 * @var array  $extraCrumbs  optional deeper crumbs (e.g. "Add lesson") after the tab's own crumb
 */
$tabs = [
    'lessons'   => ['Lessons', 'instructor/lessons&course_id=' . (int)$course['id']],
    'quiz'      => ['Quiz', 'instructor/quiz&course_id=' . (int)$course['id']],
    'gradebook' => ['Gradebook', 'instructor/gradebook&course_id=' . (int)$course['id']],
    'details'   => ['Details', 'instructor/courses/edit&id=' . (int)$course['id']],
];
$extraCrumbs = $extraCrumbs ?? [];
$crumbs = [['My courses', 'instructor/courses'], [$course['title'], $tabs['lessons'][1]]];
$crumbs[] = [$tabs[$courseTab][0], $extraCrumbs ? $tabs[$courseTab][1] : null];
foreach ($extraCrumbs as $c) {
    $crumbs[] = $c;
}
require __DIR__ . '/breadcrumbs.php';
?>
<div class="ll-course-head">
    <?= course_cover((int)$course['id'], $course['title']) ?>
    <div class="min-w-0">
        <h1 class="ll-page-title"><?= e($course['title']) ?></h1>
        <?php if (($course['status'] ?? 'active') !== 'active'): ?>
            <span class="badge-status badge-inactive">Deactivated — hidden from the catalog</span>
        <?php endif; ?>
    </div>
</div>
<nav class="ll-tabs" aria-label="Course tools">
    <?php foreach ($tabs as $key => [$label, $route]): ?>
        <a href="/index.php?route=<?= e($route) ?>" class="<?= $key === $courseTab ? 'active' : '' ?>" <?= $key === $courseTab ? 'aria-current="page"' : '' ?>><?= e($label) ?></a>
    <?php endforeach; ?>
</nav>
