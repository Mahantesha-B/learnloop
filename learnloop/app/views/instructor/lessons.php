<?php $pageTitle = 'Lessons · ' . $course['title']; require __DIR__ . '/../partials/header.php'; require __DIR__ . '/../partials/navbar.php'; ?>
<?php $courseTab = 'lessons'; require __DIR__ . '/../partials/course_tabs.php'; ?>

<div class="ll-page-head">
    <p class="ll-page-sub">Students unlock lessons in this exact order — lesson 2 opens only once lesson 1 is marked complete, and so on.</p>
    <a href="/index.php?route=instructor/lessons/create&course_id=<?= (int)$course['id'] ?>" class="btn btn-primary">Add lesson</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead><tr><th style="width:64px;">Order</th><th>Lesson</th><th>Move</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
                <?php foreach ($lessons as $i => $l): ?>
                    <tr>
                        <td><span class="ll-lesson-num"><?= (int)$l['sort_order'] ?></span></td>
                        <td class="fw-semibold text-body"><?= e($l['title']) ?></td>
                        <td class="text-nowrap">
                            <form method="POST" action="/index.php?route=instructor/lessons/move" class="d-inline">
                                <?= Csrf::field() ?>
                                <input type="hidden" name="id" value="<?= (int)$l['id'] ?>">
                                <input type="hidden" name="direction" value="up">
                                <button type="submit" class="btn btn-sm btn-outline-secondary" aria-label="Move up" <?= $i === 0 ? 'disabled' : '' ?>>↑ Up</button>
                            </form>
                            <form method="POST" action="/index.php?route=instructor/lessons/move" class="d-inline">
                                <?= Csrf::field() ?>
                                <input type="hidden" name="id" value="<?= (int)$l['id'] ?>">
                                <input type="hidden" name="direction" value="down">
                                <button type="submit" class="btn btn-sm btn-outline-secondary" aria-label="Move down" <?= $i === count($lessons) - 1 ? 'disabled' : '' ?>>↓ Down</button>
                            </form>
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="/index.php?route=instructor/lessons/edit&id=<?= (int)$l['id'] ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                            <form method="POST" action="/index.php?route=instructor/lessons/delete" class="d-inline" onsubmit="return confirm('Delete this lesson? Any student progress on it is removed too.');">
                                <?= Csrf::field() ?>
                                <input type="hidden" name="id" value="<?= (int)$l['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($lessons)): ?>
                    <tr><td colspan="4"><div class="ll-empty"><strong>No lessons yet</strong>Add the first lesson to open this course to students.</div></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
