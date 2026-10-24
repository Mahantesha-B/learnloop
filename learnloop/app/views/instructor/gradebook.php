<?php $pageTitle = 'Gradebook · ' . $course['title']; require __DIR__ . '/../partials/header.php'; require __DIR__ . '/../partials/navbar.php'; ?>
<?php $courseTab = 'gradebook'; require __DIR__ . '/../partials/course_tabs.php'; ?>

<p class="ll-page-sub mb-3">Best score is the highest of each student's attempts, not their latest. Progress is out of <?= $lessonCount ?> lesson<?= $lessonCount === 1 ? '' : 's' ?>.</p>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr><th>Student</th><th>Enrolled</th><th>Progress</th><th>Attempts</th><th class="num">Best score</th><th>Result</th></tr>
            </thead>
            <tbody>
                <?php foreach ($roster as $r): ?>
                    <?php
                        $completed = LessonProgress::countCompleted((int)$r['student_id'], (int)$course['id']);
                        $progressPct = percent($completed, $lessonCount);
                        $maxAttempts = $quiz ? (int)$quiz['max_attempts'] : 0;
                    ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <?= person_avatar((int)$r['student_id'], $r['student_name']) ?>
                                <div class="min-w-0">
                                    <div class="fw-semibold text-body text-nowrap"><?= e($r['student_name']) ?></div>
                                    <div class="ll-cell-sub"><?= e($r['student_email']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="text-nowrap text-secondary"><?= format_date($r['enrolled_at']) ?></td>
                        <td style="min-width:160px;">
                            <div class="ll-progress-label mb-1"><span class="tnum"><?= $completed ?> of <?= $lessonCount ?></span><span class="tnum"><?= $progressPct ?>%</span></div>
                            <div class="progress"><div class="progress-bar" style="width: <?= $progressPct ?>%"></div></div>
                        </td>
                        <td class="tnum"><?= (int)$r['attempts_used'] ?><?= $quiz ? ' of ' . $maxAttempts : '' ?></td>
                        <td class="num fw-semibold"><?= $r['best_score_pct'] !== null ? format_pct($r['best_score_pct']) : '—' ?></td>
                        <td>
                            <?php if ($r['best_score_pct'] === null): ?>
                                <span class="badge-status badge-pending">Not attempted</span>
                            <?php elseif ($r['best_passed']): ?>
                                <span class="badge-status badge-pass">Passed</span>
                            <?php else: ?>
                                <span class="badge-status badge-fail">Not passed</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($roster)): ?>
                    <tr><td colspan="6"><div class="ll-empty"><strong>No students enrolled yet</strong>Students appear here once they enroll from the catalog.</div></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
