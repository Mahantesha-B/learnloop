<?php $pageTitle = 'My courses'; require __DIR__ . '/../partials/header.php'; require __DIR__ . '/../partials/navbar.php'; ?>

<div class="ll-page-head">
    <div>
        <h1 class="ll-page-title">My courses</h1>
        <p class="ll-page-sub">Courses are created and assigned to you by the Super Admin. Open a course to build its lessons and quiz, or check its gradebook.</p>
    </div>
</div>

<?php if (empty($courses)): ?>
    <div class="card"><div class="ll-empty"><strong>No courses assigned to you yet</strong>Ask the Super Admin to create a course and assign it to you.</div></div>
<?php else: ?>
<div class="row g-4">
    <?php foreach ($courses as $c): ?>
        <div class="col-md-6 col-lg-4">
            <article class="card ll-course-card">
                <?= course_cover((int)$c['id'], $c['title'], 'banner') ?>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <h2><a href="/index.php?route=instructor/lessons&course_id=<?= (int)$c['id'] ?>"><?= e($c['title']) ?></a></h2>
                        <?php if ($c['status'] !== 'active'): ?><span class="badge-status badge-inactive">Inactive</span><?php endif; ?>
                    </div>
                    <?php if (trim((string)$c['description']) !== ''): ?>
                        <p class="text-secondary mb-0 mt-1 ll-clamp-3"><?= e($c['description']) ?></p>
                    <?php endif; ?>
                    <div class="ll-course-foot">
                        <div class="ll-course-foot-row">
                            <span class="tnum"><?= (int)$c['lesson_count'] ?> <?= (int)$c['lesson_count'] === 1 ? 'lesson' : 'lessons' ?></span>
                            <span class="tnum"><?= (int)$c['enrollment_count'] ?> enrolled</span>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="/index.php?route=instructor/lessons&course_id=<?= (int)$c['id'] ?>" class="btn btn-primary flex-fill">Manage course</a>
                            <a href="/index.php?route=instructor/gradebook&course_id=<?= (int)$c['id'] ?>" class="btn btn-outline-secondary">Gradebook</a>
                        </div>
                    </div>
                </div>
            </article>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../partials/footer.php'; ?>
