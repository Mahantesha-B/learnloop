<?php $pageTitle = 'Analytics'; require __DIR__ . '/../partials/header.php'; require __DIR__ . '/../partials/navbar.php'; ?>
<?php
/** A rate cell: small bar + the percentage + the raw fraction. */
$rateCell = function ($pct, int $part, int $whole): string {
    $w = max(0, min(100, (float)$pct));
    return '<div class="d-flex align-items-center gap-2">'
        . '<div class="progress" style="width:90px;flex:none"><div class="progress-bar" style="width:' . $w . '%"></div></div>'
        . '<strong class="tnum text-body">' . format_pct($pct) . '</strong>'
        . '<span class="text-secondary tnum small">' . $part . ' of ' . $whole . '</span></div>';
};
?>

<div class="ll-page-head">
    <div>
        <h1 class="ll-page-title">Analytics</h1>
        <p class="ll-page-sub">Pass rate uses each student's best attempt. A course counts as completed once every one of its lessons is marked complete.</p>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">Quiz pass rate by course</div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr><th>Course</th><th class="num">Enrolled</th><th class="num">Attempted</th><th>Pass rate</th></tr>
            </thead>
            <tbody>
                <?php foreach ($passRates as $r): ?>
                    <tr>
                        <td class="fw-semibold text-body"><?= e($r['title']) ?></td>
                        <td class="num"><?= (int)$r['enrolled_count'] ?></td>
                        <td class="num"><?= (int)$r['attempted_count'] ?></td>
                        <td>
                            <?php if ($r['pass_rate_pct'] === null): ?>
                                <span class="text-secondary">No attempts yet</span>
                            <?php else: ?>
                                <?= $rateCell($r['pass_rate_pct'], (int)$r['passed_count'], (int)$r['attempted_count']) ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="row g-4">
    <?php foreach (['Most completed courses' => $mostCompleted, 'Least completed courses' => $leastCompleted] as $heading => $rows): ?>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header"><?= $heading ?></div>
            <div class="table-responsive">
                <table class="table">
                    <thead><tr><th>Course</th><th>Completion</th></tr></thead>
                    <tbody>
                        <?php foreach (array_slice($rows, 0, 5) as $r): ?>
                            <tr>
                                <td class="fw-semibold text-body"><?= e($r['title']) ?></td>
                                <td><?= $rateCell($r['completion_rate_pct'], (int)$r['completed_count'], (int)$r['enrolled_count']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($rows)): ?><tr><td colspan="2"><div class="ll-empty">No lessons authored yet.</div></td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
