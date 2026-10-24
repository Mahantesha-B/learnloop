<?php $pageTitle = 'Users'; require __DIR__ . '/../partials/header.php'; require __DIR__ . '/../partials/navbar.php'; ?>

<div class="ll-page-head">
    <div>
        <h1 class="ll-page-title">Users</h1>
        <p class="ll-page-sub">Every instructor and student account is created here — there's no self sign-up on LearnLoop.</p>
    </div>
    <a href="/index.php?route=admin/users/create" class="btn btn-primary">New account</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2 text-nowrap">
                                <?= person_avatar((int)$u['id'], $u['name']) ?>
                                <span class="fw-semibold text-body"><?= e($u['name']) ?></span>
                            </div>
                        </td>
                        <td class="text-secondary"><?= e($u['email']) ?></td>
                        <td><span class="badge-status badge-role"><?= e(ucfirst($u['role'])) ?></span></td>
                        <td><span class="badge-status badge-<?= e($u['status']) ?>"><?= e(ucfirst($u['status'])) ?></span></td>
                        <td class="text-end">
                            <form method="POST" action="/index.php?route=admin/users/status" class="d-inline">
                                <?= Csrf::field() ?>
                                <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                                <?php if ($u['status'] === 'active'): ?>
                                    <input type="hidden" name="status" value="inactive">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Deactivate this account? They will no longer be able to log in.');">Deactivate</button>
                                <?php else: ?>
                                    <input type="hidden" name="status" value="active">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">Reactivate</button>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($users)): ?>
                    <tr><td colspan="5"><div class="ll-empty"><strong>No accounts yet</strong>Create the first instructor or student account.</div></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
