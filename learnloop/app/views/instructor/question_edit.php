<?php $pageTitle = 'Edit Question'; require __DIR__ . '/../partials/header.php'; require __DIR__ . '/../partials/navbar.php'; ?>

<?php $courseTab = 'quiz'; $extraCrumbs = [['Edit question', null]]; require __DIR__ . '/../partials/course_tabs.php'; ?>
<h2 class="ll-section-title">Edit question</h2>
<p class="ll-page-sub mb-3">Tick "Correct" next to every right option. Saving replaces this question's full option list.</p>

<div class="card ll-form-card">
    <div class="card-body p-4">
        <form method="POST" action="/index.php?route=instructor/quiz/questions/update">
            <?= Csrf::field() ?>
            <input type="hidden" name="id" value="<?= (int)$question['id'] ?>">
            <div class="mb-3">
                <label class="form-label" for="question_text">Question</label>
                <textarea class="form-control" id="question_text" name="question_text" rows="2" required><?= e($question['question_text']) ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label" for="question_type">Answer Type</label>
                <select class="form-select" id="question_type" name="question_type">
                    <option value="single" <?= $question['question_type'] === 'single' ? 'selected' : '' ?>>Single correct answer</option>
                    <option value="multi" <?= $question['question_type'] === 'multi' ? 'selected' : '' ?>>Multiple correct answers</option>
                </select>
            </div>
            <label class="form-label">Options</label>
            <?php for ($i = 0; $i < 4; $i++): $opt = $options[$i] ?? null; ?>
                <div class="input-group mb-2">
                    <label class="input-group-text gap-2 small fw-semibold">
                        <input type="checkbox" class="form-check-input m-0" name="is_correct[]" value="<?= $i ?>" title="Mark correct" <?= ($opt && $opt['is_correct']) ? 'checked' : '' ?>> Correct
                    </label>
                    <input type="text" class="form-control" name="option_text[<?= $i ?>]" value="<?= e($opt['option_text'] ?? '') ?>" placeholder="Option <?= $i + 1 ?><?= $i < 2 ? ' (required)' : ' (optional)' ?>" <?= $i < 2 ? 'required' : '' ?>>
                </div>
            <?php endfor; ?>
            <div class="form-text mb-3">Leave an option blank to use fewer than 4 choices (at least 2 required).</div>
            <div class="ll-form-actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="/index.php?route=instructor/quiz&course_id=<?= (int)$course['id'] ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
