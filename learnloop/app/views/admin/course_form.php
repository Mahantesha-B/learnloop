<?php $pageTitle = 'New Course'; require __DIR__ . '/../partials/header.php'; require __DIR__ . '/../partials/navbar.php'; ?>

<?php $crumbs = [['Courses', 'admin/courses'], ['New course', null]]; require __DIR__ . '/../partials/breadcrumbs.php'; ?>
<h1 class="ll-page-title">New course</h1>
<p class="ll-page-sub">Create a course and assign it to an instructor.</p>

<div class="card ll-form-card mt-4">
    <div class="card-body p-4">
        <form method="POST" action="/index.php?route=admin/courses/store">
            <?= Csrf::field() ?>
            <div class="mb-3">
                <label class="form-label" for="title">Title</label>
                <input type="text" class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>" id="title" name="title" value="<?= old($old, 'title') ?>" required>
                <?php if (isset($errors['title'])): ?><div class="invalid-feedback"><?= e($errors['title']) ?></div><?php endif; ?>
            </div>
            <div class="mb-3">
                <label class="form-label" for="description">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3"><?= old($old, 'description') ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label" for="instructor_id">Instructor</label>
                <select class="form-select <?= isset($errors['instructor_id']) ? 'is-invalid' : '' ?>" id="instructor_id" name="instructor_id" required>
                    <option value="">Choose an instructor</option>
                    <?php foreach ($instructors as $i): ?>
                        <option value="<?= (int)$i['id'] ?>" <?= (($old['instructor_id'] ?? '') == $i['id']) ? 'selected' : '' ?>><?= e($i['name']) ?> (<?= e($i['email']) ?>)</option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['instructor_id'])): ?><div class="invalid-feedback"><?= e($errors['instructor_id']) ?></div><?php endif; ?>
                <?php if (empty($instructors)): ?><div class="form-text text-danger">No active instructors yet — create one under Manage Users first.</div><?php endif; ?>
            </div>
            <div class="ll-form-actions">
                <button type="submit" class="btn btn-primary">Create Course</button>
                <a href="/index.php?route=admin/courses" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
