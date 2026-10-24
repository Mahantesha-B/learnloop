<?php $pageTitle = 'Add Lesson'; require __DIR__ . '/../partials/header.php'; require __DIR__ . '/../partials/navbar.php'; ?>

<?php $courseTab = 'lessons'; $extraCrumbs = [['Add lesson', null]]; require __DIR__ . '/../partials/course_tabs.php'; ?>
<h2 class="ll-section-title">Add a lesson</h2>
<p class="ll-page-sub mb-3">This lesson will be added to the end of the sequence.</p>

<div class="card ll-form-card">
    <div class="card-body p-4">
        <form method="POST" action="/index.php?route=instructor/lessons/store">
            <?= Csrf::field() ?>
            <input type="hidden" name="course_id" value="<?= (int)$course['id'] ?>">
            <div class="mb-3">
                <label class="form-label" for="title">Lesson Title</label>
                <input type="text" class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>" id="title" name="title" value="<?= old($old, 'title') ?>" required>
                <?php if (isset($errors['title'])): ?><div class="invalid-feedback"><?= e($errors['title']) ?></div><?php endif; ?>
            </div>
            <div class="mb-3">
                <label class="form-label" for="content">Content</label>
                <textarea class="form-control <?= isset($errors['content']) ? 'is-invalid' : '' ?>" id="content" name="content" rows="8" required><?= old($old, 'content') ?></textarea>
                <div class="form-text">Plain text or simple markdown (**bold**, *italic*) — no video or file uploads in this project.</div>
                <?php if (isset($errors['content'])): ?><div class="invalid-feedback"><?= e($errors['content']) ?></div><?php endif; ?>
            </div>
            <div class="ll-form-actions">
                <button type="submit" class="btn btn-primary">Add Lesson</button>
                <a href="/index.php?route=instructor/lessons&course_id=<?= (int)$course['id'] ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
