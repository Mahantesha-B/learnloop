<?php $pageTitle = 'Admin dashboard'; require __DIR__ . '/../partials/header.php'; require __DIR__ . '/../partials/navbar.php'; ?>

<div class="ll-page-head">
    <div>
        <h1 class="ll-page-title">Platform overview</h1>
        <p class="ll-page-sub">Courses, people and quiz activity across LearnLoop.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="/index.php?route=admin/users/create" class="btn btn-outline-secondary">New account</a>
        <a href="/index.php?route=admin/courses/create" class="btn btn-primary">New course</a>
    </div>
</div>

<div class="ll-stat-grid" style="--ll-stat-cols: 7;">
    <div class="ll-stat"><span class="ll-stat-value"><?= (int)$stats['courses'] ?></span><span class="ll-stat-label">Courses</span></div>
    <div class="ll-stat"><span class="ll-stat-value"><?= (int)$stats['active'] ?></span><span class="ll-stat-label">Active courses</span></div>
    <div class="ll-stat"><span class="ll-stat-value"><?= (int)$stats['instructors'] ?></span><span class="ll-stat-label">Instructors</span></div>
    <div class="ll-stat"><span class="ll-stat-value"><?= (int)$stats['students'] ?></span><span class="ll-stat-label">Students</span></div>
    <div class="ll-stat"><span class="ll-stat-value"><?= (int)$stats['enrollments'] ?></span><span class="ll-stat-label">Enrollments</span></div>
    <div class="ll-stat"><span class="ll-stat-value"><?= (int)$stats['quizzes'] ?></span><span class="ll-stat-label">Quizzes</span></div>
    <div class="ll-stat"><span class="ll-stat-value"><?= (int)$stats['attempts'] ?></span><span class="ll-stat-label">Quiz attempts</span></div>
</div>

<div class="card">
    <div class="card-header">
        Recently created courses
        <a href="/index.php?route=admin/courses">All courses</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr><th>Course</th><th>Instructor</th><th>Status</th><th class="num">Lessons</th><th class="num">Enrolled</th></tr>
            </thead>
            <tbody>
                <?php foreach ($recentCourses as $c): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <?= course_cover((int)$c['id'], $c['title'], 'sm') ?>
                                <a href="/index.php?route=admin/courses/edit&id=<?= (int)$c['id'] ?>" class="fw-semibold text-body"><?= e($c['title']) ?></a>
                            </div>
                        </td>
                        <td><?= e($c['instructor_name']) ?></td>
                        <td><span class="badge-status badge-<?= e($c['status']) ?>"><?= e(ucfirst($c['status'])) ?></span></td>
                        <td class="num"><?= (int)$c['lesson_count'] ?></td>
                        <td class="num"><?= (int)$c['enrollment_count'] ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($recentCourses)): ?>
                    <tr><td colspan="5"><div class="ll-empty"><strong>No courses yet</strong>Create the first course and assign it to an instructor.</div></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
