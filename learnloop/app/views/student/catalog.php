<?php $pageTitle = 'Catalog'; require __DIR__ . '/../partials/header.php'; require __DIR__ . '/../partials/navbar.php'; ?>

<div class="ll-page-head">
    <div>
        <h1 class="ll-page-title">Course catalog</h1>
        <p class="ll-page-sub">Every active course on LearnLoop. Enroll to unlock the first lesson.</p>
    </div>
    <?php if (!empty($courses)): ?><span class="text-secondary tnum"><?= count($courses) ?> courses</span><?php endif; ?>
</div>

<?php if (empty($courses)): ?>
    <div class="card"><div class="ll-empty"><strong>No courses available right now</strong>New courses appear here once they're published.</div></div>
<?php else: ?>
<div class="row g-4">
    <?php foreach ($courses as $c): ?>
        <?php $isEnrolled = in_array((int)$c['id'], $enrolledIds, true); ?>
        <div class="col-md-6 col-lg-4">
            <article class="card ll-course-card">
                <?= course_cover((int)$c['id'], $c['title'], 'banner') ?>
                <div class="card-body">
                    <h2><?= e($c['title']) ?></h2>
                    <div class="ll-course-meta mb-2"><?= e($c['instructor_name']) ?></div>
                    <?php if (trim((string)$c['description']) !== ''): ?>
                        <p class="text-secondary mb-0 ll-clamp-3"><?= e($c['description']) ?></p>
                    <?php endif; ?>
                    <div class="ll-course-foot">
                        <div class="ll-course-foot-row">
                            <span class="tnum"><?= (int)$c['lesson_count'] ?> <?= (int)$c['lesson_count'] === 1 ? 'lesson' : 'lessons' ?></span>
                            <span class="tnum"><?= (int)$c['enrollment_count'] ?> enrolled</span>
                        </div>
                        <?php if ($isEnrolled): ?>
                            <a href="/index.php?route=student/courses/view&id=<?= (int)$c['id'] ?>" class="btn btn-outline-primary w-100">Enrolled · open course</a>
                        <?php elseif ((int)$c['lesson_count'] === 0): ?>
                            <button type="button" class="btn btn-outline-secondary w-100" disabled>Lessons coming soon</button>
                        <?php else: ?>
                            <form method="POST" action="/index.php?route=student/enroll">
                                <?= Csrf::field() ?>
                                <input type="hidden" name="course_id" value="<?= (int)$c['id'] ?>">
                                <button type="submit" class="btn btn-primary w-100">Enroll</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../partials/footer.php'; ?>
