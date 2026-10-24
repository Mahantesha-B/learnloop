<?php
$role = Auth::role();
$navItems = [];
if ($role === 'super_admin') {
    $navItems = [
        'admin/dashboard'  => 'Dashboard',
        'admin/courses'    => 'Courses',
        'admin/users'      => 'Users',
        'admin/analytics'  => 'Analytics',
    ];
} elseif ($role === 'instructor') {
    $navItems = [
        'instructor/dashboard' => 'Dashboard',
        'instructor/courses'   => 'My courses',
    ];
} elseif ($role === 'student') {
    $navItems = [
        'student/dashboard' => 'My courses',
        'student/catalog'   => 'Catalog',
        'student/grades'    => 'Grades',
    ];
}
$roleLabel = ['super_admin' => 'Super Admin', 'instructor' => 'Instructor', 'student' => 'Student'][$role] ?? $role;

// Keep the owning section highlighted on its sub-pages (a lesson or quiz lights up the student's
// "My courses"; every instructor course tool lights up "My courses").
$currentRoute = $_GET['route'] ?? '';
$activeRoute = $currentRoute;
foreach (array_keys($navItems) as $route) {
    if (str_starts_with($currentRoute, $route . '/')) {
        $activeRoute = $route;
    }
}
if (preg_match('#^student/(courses|lessons|quiz)/#', $currentRoute)) {
    $activeRoute = 'student/dashboard';
}
if (preg_match('#^instructor/(lessons|quiz|gradebook)#', $currentRoute)) {
    $activeRoute = 'instructor/courses';
}
?>
<nav class="navbar navbar-expand-lg ll-navbar">
    <div class="container">
        <a class="navbar-brand" href="/index.php?route=<?= e(Auth::dashboardRoute($role)) ?>">
            <?= brand_mark() ?> LearnLoop
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#llNav" aria-controls="llNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="llNav">
            <ul class="navbar-nav me-auto">
                <?php foreach ($navItems as $route => $label): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $activeRoute === $route ? 'active' : '' ?>" <?= $activeRoute === $route ? 'aria-current="page"' : '' ?>
                           href="/index.php?route=<?= e($route) ?>"><?= e($label) ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="ll-user">
                <?= person_avatar(Auth::id(), (string)Auth::name()) ?>
                <span class="ll-user-text">
                    <span class="ll-user-name"><?= e(Auth::name()) ?></span>
                    <span class="ll-user-role"><?= e($roleLabel) ?></span>
                </span>
                <form method="POST" action="/index.php?route=logout" class="mb-0 ms-2">
                    <?= Csrf::field() ?>
                    <button type="submit" class="btn btn-outline-secondary btn-sm">Log out</button>
                </form>
            </div>
        </div>
    </div>
</nav>
<?php $llMainOpen = true; ?>
<main class="container ll-main">
    <?php $flashSuccess = flash_get('success'); $flashError = flash_get('error'); ?>
    <?php if ($flashSuccess): ?>
        <div class="alert alert-success" role="status"><?= e($flashSuccess) ?></div>
    <?php endif; ?>
    <?php if ($flashError): ?>
        <div class="alert alert-danger" role="alert"><?= e($flashError) ?></div>
    <?php endif; ?>
