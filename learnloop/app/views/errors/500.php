<?php $pageTitle = 'Something went wrong'; require __DIR__ . '/../partials/header.php'; ?>
<div class="ll-error-wrap">
    <div class="ll-error-card">
        <p class="code">500</p>
        <h1>Something went wrong</h1>
        <p class="text-secondary mb-4">The request couldn't be completed. Go back and try again; if it keeps happening, tell the Super Admin.</p>
        <?php $home = class_exists('Auth') && Auth::check() ? Auth::dashboardRoute(Auth::role()) : 'login'; ?>
        <a class="btn btn-primary" href="/index.php?route=<?= e($home) ?>"><?= $home === 'login' ? 'Go to login' : 'Back to dashboard' ?></a>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
