// LearnLoop -- small progressive-enhancement touches, no framework.
document.addEventListener('DOMContentLoaded', function () {
    // Login page: clicking a demo account fills the form.
    document.querySelectorAll('.ll-demo-fill').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var form = document.getElementById('login-form');
            if (!form) return;
            form.querySelector('[name=email]').value = btn.dataset.email;
            form.querySelector('[name=password]').value = btn.dataset.password;
            form.querySelector('button[type=submit]').focus();
        });
    });

    // Quiz page: live "answered x of N" count in the submit bar, and a check before
    // submitting with unanswered questions (an unanswered question scores zero).
    var quiz = document.getElementById('quiz-form');
    if (quiz) {
        var cards = quiz.querySelectorAll('.ll-question-card');
        var counter = document.getElementById('answered-count');
        var answered = function () {
            var n = 0;
            cards.forEach(function (c) { if (c.querySelector('input:checked')) n++; });
            return n;
        };
        var update = function () { if (counter) counter.textContent = answered(); };
        quiz.addEventListener('change', update);
        update();
        quiz.addEventListener('submit', function (e) {
            var left = cards.length - answered();
            if (left > 0 && !window.confirm(left + (left === 1 ? ' question is' : ' questions are') + ' unanswered and will score zero. Submit anyway?')) {
                e.preventDefault();
            }
        });
    }
});
