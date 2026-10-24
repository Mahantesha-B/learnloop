<?php $pageTitle = 'Edit Course Details'; require __DIR__ . '/../partials/header.php'; require __DIR__ . '/../partials/navbar.php'; ?>

<?php $courseTab = 'details'; $extraCrumbs = []; require __DIR__ . '/../partials/course_tabs.php'; ?>
<p class="ll-page-sub mb-3">You can update the title and description. Reassigning the course to another instructor is a Super Admin action.</p>

<div class="card ll-form-card">
    <div class="card-body p-4">
        <form method="POST" action="/index.php?route=instructor/courses/update">
            <?= Csrf::field() ?>
            <input type="hidden" name="id" value="<?= (int)$course['id'] ?>">
            <div class="mb-3">
                <label class="form-label" for="title">Title</label>
                <input type="text" class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>" id="title" name="title" value="<?= old($old ?: $course, 'title') ?>" required>
                <?php if (isset($errors['title'])): ?><div class="invalid-feedback"><?= e($errors['title']) ?></div><?php endif; ?>
            </div>
            <div class="mb-3">
                <label class="form-label" for="description">Description</label>
                <textarea class="form-control" id="description" name="description" rows="4"><?= old($old ?: $course, 'description') ?></textarea>
            </div>
            <div class="ll-form-actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="/index.php?route=instructor/courses" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
