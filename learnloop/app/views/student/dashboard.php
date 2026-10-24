<?php $pageTitle = 'My courses'; require __DIR__ . '/../partials/header.php'; require __DIR__ . '/../partials/navbar.php'; ?>
<?php $firstName = explode(' ', trim((string)Auth::name()))[0]; ?>

<div class="ll-page-head">
    <div>
        <h1 class="ll-page-title">Welcome back, <?= e($firstName) ?></h1>
        <p class="ll-page-sub">Pick up where you left off, or browse the catalog for something new.</p>
    </div>
    <a href="/index.php?route=student/catalog" class="btn btn-outline-secondary">Browse catalog</a>
</div>

<?php if (empty($enrollments)): ?>
    <div class="card"><div class="ll-empty">
        <strong>You're not enrolled in any course yet</strong>
        Find one in the catalog to get started.
        <div class="mt-3"><a href="/index.php?route=student/catalog" class="btn btn-primary">Browse the catalog</a></div>
    </div></div>
<?php else: ?>
<div class="row g-4">
    <?php foreach ($enrollments as $en): ?>
        <?php
            $done = (int)$en['completed_lessons'];
            $total = (int)$en['lesson_count'];
            $label = $done === 0 ? 'Start course' : ($total > 0 && $done >= $total ? 'Review course' : 'Continue');
        ?>
        <div class="col-md-6 col-lg-4">
            <article class="card ll-course-card">
                <?= course_cover((int)$en['course_id'], $en['title'], 'banner') ?>
                <div class="card-body">
                    <h2><a href="/index.php?route=student/courses/view&id=<?= (int)$en['course_id'] ?>"><?= e($en['title']) ?></a></h2>
                    <div class="ll-course-meta">
                        <?= e($en['instructor_name']) ?>
                        <?php if ($en['course_status'] === 'inactive'): ?> · <span class="badge-status badge-inactive">Deactivated</span><?php endif; ?>
                    </div>

                    <div class="ll-course-foot">
                        <div class="ll-progress-label"><span>Progress</span><span class="tnum"><?= $done ?> of <?= $total ?> lessons</span></div>
                        <div class="progress mb-3" role="progressbar" aria-label="Lesson progress" aria-valuenow="<?= (int)$en['progress_pct'] ?>" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar" style="width: <?= (int)$en['progress_pct'] ?>%"></div></div>
                        <div class="ll-course-foot-row">
                            <?php if (!$en['quiz']): ?>
                                <span>No quiz</span>
                            <?php elseif ($en['best_attempt']): ?>
                                <span>Quiz best <strong class="text-body tnum"><?= format_pct($en['best_attempt']['score_pct']) ?></strong></span>
                                <span class="badge-status badge-<?= $en['best_attempt']['passed'] ? 'pass' : 'fail' ?>"><?= $en['best_attempt']['passed'] ? 'Passed' : 'Not passed' ?></span>
                            <?php else: ?>
                                <span>Quiz not attempted yet</span>
                            <?php endif; ?>
                        </div>
                        <a href="/index.php?route=student/courses/view&id=<?= (int)$en['course_id'] ?>" class="btn btn-primary w-100"><?= $label ?></a>
                    </div>
                </div>
            </article>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../partials/footer.php'; ?>
