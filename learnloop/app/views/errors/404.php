<?php $pageTitle = 'Page not found'; require __DIR__ . '/../partials/header.php'; ?>
<div class="ll-error-wrap">
    <div class="ll-error-card">
        <p class="code">404</p>
        <h1>Page not found</h1>
        <p class="text-secondary mb-4">That page doesn't exist, or your account doesn't have access to it.</p>
        <?php $home = class_exists('Auth') && Auth::check() ? Auth::dashboardRoute(Auth::role()) : 'login'; ?>
        <a class="btn btn-primary" href="/index.php?route=<?= e($home) ?>"><?= $home === 'login' ? 'Go to login' : 'Back to dashboard' ?></a>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
