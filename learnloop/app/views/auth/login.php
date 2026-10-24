<?php $pageTitle = 'Log in'; require __DIR__ . '/../partials/header.php'; ?>
<div class="ll-auth-wrap">
    <div class="ll-auth">
        <div class="ll-auth-brand"><?= brand_mark() ?> LearnLoop</div>

        <div class="ll-auth-card">
            <h1>Log in</h1>
            <p class="text-secondary mb-4">Pick up your courses where you left off.</p>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger py-2" role="alert">
                    <?php foreach ($errors as $err): ?>
                        <div><?= e($err) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="/index.php?route=login" id="login-form">
                <?= Csrf::field() ?>
                <div class="mb-3">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= old($old ?? [], 'email') ?>" autocomplete="username" required autofocus>
                </div>
                <div class="mb-4">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password" autocomplete="current-password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2">Log in</button>
            </form>
            <p class="text-secondary small mt-3 mb-0">Accounts are created by the Super Admin — there's no self sign-up.</p>
        </div>

        <details class="ll-demo">
            <summary>Demo accounts</summary>
            <?php foreach ([
                ['Super Admin', 'admin@learnloop.test', 'Admin@123'],
                ['Instructor', 'instructor1@learnloop.test', 'Instructor@123'],
                ['Student', 'student1@learnloop.test', 'Student@123'],
                ['Student · inactive (login refused)', 'student20@learnloop.test', 'Student@123'],
            ] as [$label, $demoEmail, $demoPassword]): ?>
                <button type="button" class="ll-demo-fill" data-email="<?= e($demoEmail) ?>" data-password="<?= e($demoPassword) ?>">
                    <span><?= e($label) ?></span>
                    <span><?= e($demoEmail) ?></span>
                </button>
            <?php endforeach; ?>
            <p>Click an account to fill the form. The full roster (4 instructors, 20 students) is in README.md.</p>
        </details>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
