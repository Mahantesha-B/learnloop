<?php
/**
 * Breadcrumb trail for pages below a top-level section.
 * @var array $crumbs  list of [label, route|null]; the last entry is the current page and is not linked.
 */
$lastCrumb = count($crumbs) - 1;
?>
<nav aria-label="Breadcrumb">
    <ol class="ll-breadcrumbs">
        <?php foreach ($crumbs as $i => [$label, $route]): ?>
            <li>
                <?php if ($i < $lastCrumb && $route !== null): ?>
                    <a href="/index.php?route=<?= e($route) ?>"><?= e($label) ?></a>
                <?php else: ?>
                    <span <?= $i === $lastCrumb ? 'aria-current="page"' : '' ?>><?= e($label) ?></span>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ol>
</nav>
