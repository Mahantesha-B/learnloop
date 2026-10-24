# LearnLoop — Course/Quiz Platform

A course/quiz platform: Instructors build courses out of sequential lessons
and auto-graded quizzes, Students enroll and work through them in order, and
a Super Admin oversees the course catalog, user accounts, and platform-wide
activity.

## 1. Overview & Objective

Most course-builder toy projects let a student jump straight to the final
quiz without ever opening a lesson. LearnLoop's whole point is that it
doesn't: a lesson unlocks only once the one before it is marked complete,
enforced on the server (not a disabled "Next" button), so progress is
actually sequential. Quizzes are multiple-choice only so every submission
can be graded instantly and deterministically against an answer key — no
manual grading queue — and a student gets a capped number of attempts, with
the *best* of those attempts (not the most recent) counting as their final
grade everywhere it's shown. A course with real enrollment/grade history
attached is never hard-deleted, only deactivated — it disappears from the
catalog but stays fully visible (and gradeable) to students already on it.

## 2. Tech Stack

- **Backend**: Plain PHP 8.1+ (custom MVC, no framework) — PDO/MySQL,
  native sessions
- **Frontend**: Bootstrap 5, "Edu-fresh" theme (emerald/white) kept flat and
  professional: one deep emerald (`#0B7A5A`) as the only brand colour, a
  green-black ink for headings, red/amber only for fail/pending states, no
  gradients. Lexend for headings, Source Sans 3 for body. Course pages show a
  "learning path" stepper that mirrors the sequential lesson unlock; courses and
  people get placeholder images (one open-book image / one silhouette, tinted
  per id). Breadcrumbs on every sub-page and a Lessons/Quiz/Gradebook/Details
  tab bar on instructor course pages. Responsive down to phone width — no build
  step, no JS framework
- **Database**: MySQL/MariaDB (InnoDB, foreign keys enforced)
- **Server**: Apache via XAMPP (or PHP's built-in server for local testing)

## 3. Database — Table List

| Table | Purpose |
|---|---|
| `users` | All three personas (`role`: super_admin / instructor / student), bcrypt password hash, active/inactive status |
| `courses` | Title, description, owning instructor, active/inactive status |
| `lessons` | One course's ordered content (`sort_order`), text/markdown only |
| `enrollments` | A student's enrollment in a course |
| `lesson_progress` | One row per lesson a student has marked complete — its presence/absence for lesson N-1 is what gates lesson N |
| `quizzes` | One per course (unique key), with `pass_threshold_pct` and `max_attempts` |
| `quiz_questions` | Single- or multi-select MCQ, ordered within its quiz |
| `quiz_options` | Answer options per question, each flagged `is_correct` |
| `quiz_attempts` | One row per graded attempt: `score_pct`, `passed`, computed once at submission and never re-derived |
| `quiz_attempt_selections` | Which option(s) a student picked on a given attempt/question, kept for result review |

See `database/schema.sql` for full column definitions, foreign keys, and
seed data.

### Entity relationships (simplified)

```
users(instructor) ──< courses ──< lessons
                         │            │
                         │            └──< lesson_progress >── users(student)
                         │
                         ├──< enrollments >── users(student)
                         │
                         └──< quizzes ──< quiz_questions ──< quiz_options
                                  │                              │
                                  └──< quiz_attempts ──< quiz_attempt_selections
                                        (student, score, pass/fail)
```

## 4. Feature List

See `FEATURES.md` for the full persona-by-feature breakdown. In short:

- **Super Admin**: create courses and assign/reassign their instructor,
  deactivate/reactivate courses, manage instructor & student accounts
  (activate/deactivate), platform analytics (course/enrollment/quiz counts,
  per-course pass rate, most/least completed courses)
- **Instructor**: create/edit own courses, build an ordered lesson sequence
  (add/edit/delete/reorder), build one quiz per course (questions, options,
  pass threshold, attempt cap), Gradebook per course (every enrolled
  student's progress + best quiz score)
- **Student**: browse the active course catalog and enroll, work through
  lessons in order (each unlocks only once the previous one is marked
  complete), take the quiz up to the attempt cap with instant auto-graded
  results, view own progress and grades across every enrolled course

## 5. Default Login Credentials

All accounts use the passwords below (bcrypt-hashed in the database, never
stored in plaintext).

| Role | Email | Password |
|---|---|---|
| Super Admin | `admin@learnloop.test` | `Admin@123` |
| Instructor | `instructor1@learnloop.test` | `Instructor@123` |
| Instructor | `instructor2@learnloop.test` | `Instructor@123` |
| Instructor | `instructor3@learnloop.test` | `Instructor@123` |
| Instructor | `instructor4@learnloop.test` | `Instructor@123` |
| Student | `student1@learnloop.test` | `Student@123` |
| Student | `student2@learnloop.test` | `Student@123` |
| Student (deliberately inactive — for testing login rejection) | `student20@learnloop.test` | `Student@123` |

(`student1` through `student19` all follow the same
`studentN@learnloop.test` / `Student@123` pattern — see
`database/schema.sql` for the full roster, seeded courses, and quiz
attempt history.)

## 6. Setup Instructions

1. Copy the `learnloop/` folder under XAMPP's `htdocs`, or serve it
   directly with PHP's built-in server (see below).
2. Start MySQL/MariaDB. On this suite's reference machine, MariaDB (via
   XAMPP) listens on the default **port 3306** — see the
   top-level `CLAUDE.md` for why. Adjust `config/database.php` if your
   MySQL is on a different host/port.
3. Import the schema (creates the database, tables, and demo seed data):
   ```
   mysql -u root -P 3306 < database/schema.sql
   ```
   or import `database/schema.sql` via phpMyAdmin.
4. Serve the app. From the project folder:
   ```
   php -S localhost:8011 -t public public/index.php
   ```
   (`-t public` matters — without it, static assets under `public/assets`
   404 while the HTML still renders, since the built-in server's document
   root defaults to the directory PHP was launched from otherwise.)
5. Visit `http://localhost:8011/index.php?route=login` and sign in with
   any of the demo credentials above.

This project has no `composer.json` — no email/PDF/Excel dependency was
needed, so there's no `composer install` step.

## 7. Folder Structure

```
learnloop/
  public/
    index.php             Front controller — all routes registered here
    assets/css/style.css  Bootstrap 5 override, "Edu-fresh" emerald/white theme
  app/
    controllers/          AuthController, AdminController, InstructorController, StudentController
    models/                User, Course, Lesson, Enrollment, LessonProgress,
                            Quiz, QuizQuestion, QuizOption, QuizAttempt — one class per table (or closely related group), all SQL lives here
    views/
      partials/            header.php, footer.php, navbar.php
      admin/, instructor/, student/, auth/, errors/
  core/                    Router, Database (PDO singleton), Auth, Csrf, Validator, helpers.php
  config/database.php      DB credentials (local XAMPP defaults)
  database/schema.sql      Full schema + seed/demo data
```

Follows the suite's standard plain-PHP MVC layout (`GROUNDRULES.md` §1) —
no framework deviation for this project.

## 8. Anticipated Viva Questions

**Why enforce lesson unlocking on the server instead of just disabling the
"Next" button in the UI?**
A disabled button is a UI courtesy, not a security boundary — anyone can
type `?route=student/lessons/view&id=7` directly. `StudentController::
lessonView()` looks up the immediately preceding lesson
(`Lesson::previous()`) and checks `LessonProgress::isCompleted()` for it
server-side before rendering anything; if it isn't complete, the request is
redirected back with a message instead. The same check is repeated in
`lessonComplete()` so a student can't mark lesson 5 done without having
completed lesson 4, even via a crafted POST.

**Why cap quiz attempts, and how is the cap actually enforced?**
Unlimited retries make a "graded" quiz meaningless — a student could brute
force it. Each quiz carries an Instructor-set `max_attempts` (default 2).
`StudentController::quizSubmit()` counts the student's existing
`quiz_attempts` rows for that quiz via `QuizAttempt::countForStudent()`
**immediately before** inserting a new one — the count comes from the
database, never from anything the client submitted, so re-posting the quiz
form (or crafting a request directly, bypassing the UI that would otherwise
just not render the form) can't buy an extra attempt. This was verified
directly: a 3rd POST to `student/quiz/submit` after using both allowed
attempts is rejected, and the `quiz_attempts` table still shows exactly 2
rows for that student/quiz.

**Why is grading deterministic instead of needing manual review?**
Every question is single/multi-select MCQ with a pre-defined correct
option set. `QuizAttempt::grade()` compares the submitted option-id set
against `quiz_options.is_correct` exactly (a question only counts as
correct if the sets match precisely — no partial credit for multi-select),
computes `score_pct` once at submission time, and stores it on the
`quiz_attempts` row. The Gradebook and result views read that stored value
— they never re-derive it from raw answers, so display is always
consistent with what was actually graded at submission time.

**Why does the Gradebook show the *best* attempt instead of the most
recent one?**
A single bad early attempt (nerves, a misread question) shouldn't
permanently cap a student's grade once they've since passed. Both
`QuizAttempt::bestForStudent()` (student's own grade view) and
`QuizAttempt::gradebookForCourse()` (Instructor's roster) order by
`score_pct DESC, attempt_number ASC` and take the top row — this was
verified end-to-end: a student who failed attempt 1 (0%) and passed
attempt 2 (100%) shows 100% in both the Gradebook and their own Grades
page, not the most recent value.

**Why deactivate a course instead of deleting it?**
`courses.status` is `active`/`inactive`; there is no course-delete
endpoint at all. A course with enrollments, lesson progress, and quiz
attempts attached can't be hard-removed without orphaning that history
(and violating the foreign keys those tables carry back to `courses`).
`Course::setStatus()` only ever flips the status column.
`Course::allActive()` — what the Student catalog and new-enrollment check
both query — filters to `status = 'active'`, so a deactivated course
vanishes from the catalog and can no longer be freshly enrolled in
(`StudentController::enroll()` explicitly re-checks `status === 'active'`
server-side). But `Course::find()` has no such filter, so an
already-enrolled student's course view, lesson pages, and quiz results all
keep working exactly as before, and the course still appears in the
Instructor's Gradebook. Verified directly: deactivating a course removed
it from a non-enrolled student's catalog listing while an already-enrolled
student's dashboard, course view, and the Instructor's Gradebook for that
course all still showed it.

**How are passwords and CSRF handled?**
`password_hash()` / `password_verify()` (bcrypt), never plaintext. Every
state-changing form carries a per-session token (`Csrf::field()`)
verified via `Csrf::requireValid()` before anything touches the database
— a forged POST with a missing or bogus token gets HTTP 419, not silently
processed. Login regenerates the session ID
(`session_regenerate_id(true)`) to prevent session fixation.

**What stops a Student from reaching an Instructor/Admin page directly, or
an Instructor from touching another Instructor's course?**
Every controller action calls `Auth::requireRole(...)` first — a session
with the wrong role gets HTTP 403, not just a hidden nav link. On top of
that, `InstructorController::ownedCourseOrFail()` re-checks
`courses.instructor_id` against the logged-in Instructor's own id before
any lesson/quiz action on that course proceeds, so Instructor A can't edit
Instructor B's course by guessing its `course_id` in the URL — that path
returns 404, same as a genuinely nonexistent course. All of this was
exercised directly (Student → Instructor/Admin routes, Instructor →
Admin/other-Instructor's-Student routes, unauthenticated → any protected
route) and confirmed to reject at the server, not just hide the option in
the UI.

**Why does `Lesson::move()` (reordering lessons) stage through a
placeholder `sort_order` of 0 instead of swapping the two values directly?**
`lessons` has a `UNIQUE (course_id, sort_order)` constraint. Swapping two
adjacent lessons' orders by writing the target value directly into each
row, one `UPDATE` at a time, means the first `UPDATE` collides with the
unique constraint the instant it tries to claim an order value the
*other* row hasn't vacated yet — MySQL checks `UNIQUE` per statement, not
deferred to commit. Moving the lesson being repositioned through an unused
placeholder value first, then relocating its neighbor into the vacated
slot, then finally moving it into the neighbor's old slot, avoids ever
having two rows collide on the same `(course_id, sort_order)` at any
intermediate point in the transaction.
