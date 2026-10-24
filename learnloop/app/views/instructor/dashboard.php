<?php $pageTitle = 'Instructor dashboard'; require __DIR__ . '/../partials/header.php'; require __DIR__ . '/../partials/navbar.php'; ?>
<?php $firstName = explode(' ', trim((string)Auth::name()))[0]; ?>

<div class="ll-page-head">
    <div>
        <h1 class="ll-page-title">Welcome, <?= e($firstName) ?></h1>
        <p class="ll-page-sub">What's happening across the courses you teach.</p>
    </div>
    <a href="/index.php?route=instructor/courses" class="btn btn-outline-secondary">All my courses</a>
</div>

<div class="ll-stat-grid" style="--ll-stat-cols: 3;">
    <div class="ll-stat"><span class="ll-stat-value"><?= count($courses) ?></span><span class="ll-stat-label">Courses I teach</span></div>
    <div class="ll-stat"><span class="ll-stat-value"><?= (int)$enrollmentTotal ?></span><span class="ll-stat-label">Student enrollments</span></div>
    <div class="ll-stat"><span class="ll-stat-value"><?= (int)$questionTotal ?></span><span class="ll-stat-label">Quiz questions written</span></div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header">My courses</div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead><tr><th>Course</th><th class="num">Lessons</th><th class="num">Enrolled</th><th></th></tr></thead>
                    <tbody>
                        <?php foreach ($courses as $c): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <?= course_cover((int)$c['id'], $c['title'], 'sm') ?>
                                        <div class="min-w-0">
                                            <div class="fw-semibold text-body"><?= e($c['title']) ?></div>
                                            <?php if ($c['status'] !== 'active'): ?><div class="ll-cell-sub">Inactive</div><?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="num"><?= (int)$c['lesson_count'] ?></td>
                                <td class="num"><?= (int)$c['enrollment_count'] ?></td>
                                <td class="text-end"><a href="/index.php?route=instructor/lessons&course_id=<?= (int)$c['id'] ?>" class="btn btn-sm btn-outline-secondary">Manage</a></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($courses)): ?>
                            <tr><td colspan="4"><div class="ll-empty"><strong>No courses yet</strong>The Super Admin assigns courses to instructors.</div></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header">Recent quiz attempts</div>
            <div class="table-responsive">
                <table class="table">
                    <thead><tr><th>Student</th><th class="num">Score</th></tr></thead>
                    <tbody>
                        <?php foreach ($recentAttempts as $a): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold text-body"><?= e($a['student_name']) ?></div>
                                    <div class="ll-cell-sub"><?= e($a['course_title']) ?></div>
                                </td>
                                <td class="num">
                                    <span class="fw-semibold"><?= format_pct($a['score_pct']) ?></span>
                                    <span class="badge-status badge-<?= $a['passed'] ? 'pass' : 'fail' ?> ms-1"><?= $a['passed'] ? 'Pass' : 'Fail' ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($recentAttempts)): ?>
                            <tr><td colspan="2"><div class="ll-empty"><strong>No attempts yet</strong>Results appear here as students take your quizzes.</div></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
