<?php $pageTitle = 'Edit Lesson'; require __DIR__ . '/../partials/header.php'; require __DIR__ . '/../partials/navbar.php'; ?>

<?php $courseTab = 'lessons'; $extraCrumbs = [['Edit lesson', null]]; require __DIR__ . '/../partials/course_tabs.php'; ?>
<h2 class="ll-section-title">Edit lesson</h2>

<div class="card ll-form-card">
    <div class="card-body p-4">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger py-2"><?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?></div>
        <?php endif; ?>
        <form method="POST" action="/index.php?route=instructor/lessons/update">
            <?= Csrf::field() ?>
            <input type="hidden" name="id" value="<?= (int)$lesson['id'] ?>">
            <div class="mb-3">
                <label class="form-label" for="title">Lesson Title</label>
                <input type="text" class="form-control" id="title" name="title" value="<?= e($_POST['title'] ?? $lesson['title']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label" for="content">Content</label>
                <textarea class="form-control" id="content" name="content" rows="8" required><?= e($_POST['content'] ?? $lesson['content']) ?></textarea>
            </div>
            <div class="ll-form-actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="/index.php?route=instructor/lessons&course_id=<?= (int)$course['id'] ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
