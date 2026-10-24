<?php $pageTitle = 'Grades'; require __DIR__ . '/../partials/header.php'; require __DIR__ . '/../partials/navbar.php'; ?>

<div class="ll-page-head">
    <div>
        <h1 class="ll-page-title">Grades &amp; progress</h1>
        <p class="ll-page-sub">Every course you're enrolled in. The quiz score shown is your best attempt.</p>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr><th>Course</th><th>Progress</th><th>Attempts</th><th class="num">Best score</th><th>Result</th></tr>
            </thead>
            <tbody>
                <?php foreach ($enrollments as $en): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <?= course_cover((int)$en['course_id'], $en['title'], 'sm') ?>
                                <div class="min-w-0">
                                    <a href="/index.php?route=student/courses/view&id=<?= (int)$en['course_id'] ?>" class="fw-semibold"><?= e($en['title']) ?></a>
                                    <?php if ($en['course_status'] === 'inactive'): ?><div class="ll-cell-sub">Course deactivated</div><?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td style="min-width:170px;">
                            <div class="ll-progress-label mb-1"><span class="tnum"><?= $en['completed_lessons'] ?> of <?= (int)$en['lesson_count'] ?></span><span class="tnum"><?= (int)$en['progress_pct'] ?>%</span></div>
                            <div class="progress"><div class="progress-bar" style="width: <?= (int)$en['progress_pct'] ?>%"></div></div>
                        </td>
                        <td class="tnum"><?= $en['quiz'] ? $en['attempts_used'] . ' of ' . (int)$en['quiz']['max_attempts'] : '—' ?></td>
                        <td class="num fw-semibold"><?= $en['best_attempt'] ? format_pct($en['best_attempt']['score_pct']) : '—' ?></td>
                        <td>
                            <?php if (!$en['quiz']): ?>
                                <span class="text-secondary">No quiz</span>
                            <?php elseif (!$en['best_attempt']): ?>
                                <span class="badge-status badge-pending">Not attempted</span>
                            <?php elseif ($en['best_attempt']['passed']): ?>
                                <span class="badge-status badge-pass">Passed</span>
                            <?php else: ?>
                                <span class="badge-status badge-fail">Not passed</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($enrollments)): ?>
                    <tr><td colspan="5"><div class="ll-empty"><strong>No courses yet</strong>Enroll in a course from the catalog to see your grades here.</div></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
