<?php $pageTitle = 'Courses'; require __DIR__ . '/../partials/header.php'; require __DIR__ . '/../partials/navbar.php'; ?>

<div class="ll-page-head">
    <div>
        <h1 class="ll-page-title">Courses</h1>
        <p class="ll-page-sub">Create courses and assign an instructor. Deactivating hides a course from the catalog without deleting any student history.</p>
    </div>
    <a href="/index.php?route=admin/courses/create" class="btn btn-primary">New course</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr><th>Course</th><th>Instructor</th><th>Status</th><th class="num">Lessons</th><th class="num">Enrolled</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($courses as $c): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <?= course_cover((int)$c['id'], $c['title'], 'sm') ?>
                                <span class="fw-semibold text-body"><?= e($c['title']) ?></span>
                            </div>
                        </td>
                        <td class="text-nowrap"><?= e($c['instructor_name']) ?></td>
                        <td><span class="badge-status badge-<?= e($c['status']) ?>"><?= e(ucfirst($c['status'])) ?></span></td>
                        <td class="num"><?= (int)$c['lesson_count'] ?></td>
                        <td class="num"><?= (int)$c['enrollment_count'] ?></td>
                        <td class="text-end text-nowrap">
                            <a href="/index.php?route=admin/courses/edit&id=<?= (int)$c['id'] ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                            <form method="POST" action="/index.php?route=admin/courses/status" class="d-inline">
                                <?= Csrf::field() ?>
                                <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                                <?php if ($c['status'] === 'active'): ?>
                                    <input type="hidden" name="status" value="inactive">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Deactivate this course? It will disappear from the catalog but stay visible to enrolled students.');">Deactivate</button>
                                <?php else: ?>
                                    <input type="hidden" name="status" value="active">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">Reactivate</button>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($courses)): ?>
                    <tr><td colspan="6"><div class="ll-empty"><strong>No courses yet</strong>Create the first one.</div></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
