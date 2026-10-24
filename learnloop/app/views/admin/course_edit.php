<?php $pageTitle = 'Edit Course'; require __DIR__ . '/../partials/header.php'; require __DIR__ . '/../partials/navbar.php'; ?>

<?php $crumbs = [['Courses', 'admin/courses'], [$course['title'], null]]; require __DIR__ . '/../partials/breadcrumbs.php'; ?>
<h1 class="ll-page-title">Edit course</h1>
<p class="ll-page-sub">Update details or reassign this course to a different instructor.</p>

<div class="card ll-form-card mt-4">
    <div class="card-body p-4">
        <form method="POST" action="/index.php?route=admin/courses/update">
            <?= Csrf::field() ?>
            <input type="hidden" name="id" value="<?= (int)$course['id'] ?>">
            <div class="mb-3">
                <label class="form-label" for="title">Title</label>
                <input type="text" class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>" id="title" name="title" value="<?= old($old ?: $course, 'title') ?>" required>
                <?php if (isset($errors['title'])): ?><div class="invalid-feedback"><?= e($errors['title']) ?></div><?php endif; ?>
            </div>
            <div class="mb-3">
                <label class="form-label" for="description">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3"><?= old($old ?: $course, 'description') ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label" for="instructor_id">Instructor</label>
                <select class="form-select <?= isset($errors['instructor_id']) ? 'is-invalid' : '' ?>" id="instructor_id" name="instructor_id" required>
                    <?php foreach ($instructors as $i): ?>
                        <option value="<?= (int)$i['id'] ?>" <?= ((int)($old['instructor_id'] ?? $course['instructor_id']) === (int)$i['id']) ? 'selected' : '' ?>><?= e($i['name']) ?> (<?= e($i['email']) ?>)</option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['instructor_id'])): ?><div class="invalid-feedback"><?= e($errors['instructor_id']) ?></div><?php endif; ?>
            </div>
            <div class="ll-form-actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="/index.php?route=admin/courses" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
