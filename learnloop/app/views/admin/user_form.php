<?php $pageTitle = 'New Account'; require __DIR__ . '/../partials/header.php'; require __DIR__ . '/../partials/navbar.php'; ?>

<?php $crumbs = [['Users', 'admin/users'], ['New account', null]]; require __DIR__ . '/../partials/breadcrumbs.php'; ?>
<h1 class="ll-page-title">New account</h1>
<p class="ll-page-sub">Create an Instructor or Student login. There is no self-registration in LearnLoop — every account is provisioned here.</p>

<div class="card ll-form-card mt-4">
    <div class="card-body p-4">
        <form method="POST" action="/index.php?route=admin/users/store">
            <?= Csrf::field() ?>
            <div class="mb-3">
                <label class="form-label" for="name">Full Name</label>
                <input type="text" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" id="name" name="name" value="<?= old($old, 'name') ?>" required>
                <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= e($errors['name']) ?></div><?php endif; ?>
            </div>
            <div class="mb-3">
                <label class="form-label" for="email">Email</label>
                <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" id="email" name="email" value="<?= old($old, 'email') ?>" required>
                <?php if (isset($errors['email'])): ?><div class="invalid-feedback"><?= e($errors['email']) ?></div><?php endif; ?>
            </div>
            <div class="mb-3">
                <label class="form-label" for="role">Role</label>
                <select class="form-select <?= isset($errors['role']) ? 'is-invalid' : '' ?>" id="role" name="role" required>
                    <option value="">Choose a role</option>
                    <option value="instructor" <?= (($old['role'] ?? '') === 'instructor') ? 'selected' : '' ?>>Instructor</option>
                    <option value="student" <?= (($old['role'] ?? '') === 'student') ? 'selected' : '' ?>>Student</option>
                </select>
                <?php if (isset($errors['role'])): ?><div class="invalid-feedback"><?= e($errors['role']) ?></div><?php endif; ?>
            </div>
            <div class="mb-3">
                <label class="form-label" for="password">Temporary Password</label>
                <input type="text" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" id="password" name="password" minlength="6" required>
                <?php if (isset($errors['password'])): ?><div class="invalid-feedback"><?= e($errors['password']) ?></div><?php endif; ?>
                <div class="form-text">At least 6 characters. Share this with the user directly.</div>
            </div>
            <div class="ll-form-actions">
                <button type="submit" class="btn btn-primary">Create Account</button>
                <a href="/index.php?route=admin/users" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
