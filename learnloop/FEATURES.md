# FEATURES.md — LearnLoop

## 1. One-line pitch

A course/quiz platform: Instructors build courses out of sequential
lessons and auto-graded quizzes, Students enroll and work through them in
order, and a Super Admin oversees the course catalog, user accounts, and
platform-wide activity.

## 2. Core modules

1. **Auth & Role Management** — single login; all three roles
   (Super Admin, Instructor, Student) are admin-provisioned/seeded, no
   self-registration (unlike project #11 CampusConnect, whose Student
   persona is this suite's one deliberate self-registration exception).
2. **Course & Lesson Management** — Instructors create courses and break
   each into an ordered sequence of lessons (text/markdown content);
   Super Admin can create/deactivate any course and (re)assign its
   Instructor.
3. **Enrollment & Progress Tracking** — Students browse the catalog and
   enroll; a lesson unlocks only once the previous lesson in that course
   is marked complete, so progress is sequential, not a free-for-all.
4. **Quiz Engine & Auto-Grading** — Instructors attach one quiz per course
   (multiple-choice questions, single or multiple correct options);
   Students get a capped number of attempts, each auto-graded instantly
   with a percentage score against the Instructor's pass threshold; the
   best of the allowed attempts counts as the final grade.
5. **Gradebook** — Instructors see every enrolled student's progress and
   quiz results per course; Students see their own progress/grades across
   all enrolled courses.
6. **Platform Analytics** — Super Admin dashboard: course/enrollment/quiz
   counts, average pass rate per course, most/least completed courses.

## 3. Design notes (the non-obvious mechanics)

**Sequential lesson unlocking, enforced server-side.** `lesson_progress`
rows mark a lesson complete per student; the lesson view controller
rejects a direct request for lesson N if lesson N-1 isn't yet marked
complete for that student (redirects back with a message), not just a
disabled "Next" button in the UI.

**Capped, best-of-attempts grading.** Each quiz has an `max_attempts`
value set by the Instructor (default 2). The attempt controller counts
the student's existing `quiz_attempts` rows for that quiz server-side
before allowing a new one — the cap can't be bypassed by resubmitting the
form. The Gradebook and Student's own grade view both report the
highest-scoring attempt, not the most recent one.

**Auto-grading is deterministic, not judged.** Every question is
single/multi-select MCQ with pre-defined correct option(s) — scoring is a
straight match against `quiz_options.is_correct`, computed at submission
time and stored on the attempt row (score, pass/fail) so the Gradebook
never has to re-derive it from raw answers.

**Course deactivation, not deletion.** A course with existing enrollments
can't be hard-deleted (would orphan progress/grade history) — the Admin
can only deactivate it, which hides it from the catalog but keeps it
visible (read-only) to already-enrolled students and in the Gradebook.

## 4. Feature breakdown by persona

| Feature | Super Admin | Instructor | Student |
|---|---|---|---|
| Login / logout | ✅ | ✅ | ✅ |
| Create/deactivate courses, assign instructor | ✅ | — | — |
| Create/edit own course's lessons (ordered) | — | ✅ | — |
| Build quiz (questions, options, pass threshold, attempt cap) | — | ✅ (own course) | — |
| Browse course catalog | ✅ | ✅ | ✅ |
| Enroll in a course | — | — | ✅ |
| View lessons, mark complete (sequential unlock) | — | ✅ (preview) | ✅ |
| Take quiz (up to attempt cap) | — | — | ✅ |
| View own course's gradebook/roster | — | ✅ | — |
| View own progress & grades across courses | — | — | ✅ |
| Manage user accounts (activate/deactivate) | ✅ | — | — |
| Platform analytics dashboard | ✅ | — | — |

## 5. Screen list per persona

**Super Admin** (5 screens)
1. Login
2. Dashboard (platform stats: courses, enrollments, avg pass rate)
3. Manage Courses (create/deactivate, assign instructor)
4. Manage Users (instructors & students, activate/deactivate)
5. Platform Analytics (per-course completion/pass-rate report)

**Instructor** (6 screens)
1. Login
2. Dashboard (my courses, enrollment counts, recent quiz activity)
3. My Courses (create/edit)
4. Course Builder (add/reorder/edit lessons)
5. Quiz Builder (questions, options, pass threshold, attempt cap)
6. Gradebook (per course: roster, progress, best quiz score)

**Student** (6 screens)
1. Login
2. Dashboard (enrolled courses, progress bars, latest grades)
3. Course Catalog (browse/enroll)
4. Course View (lesson list, sequential unlock, mark complete)
5. Take Quiz (attempt, instant auto-graded result)
6. My Grades & Progress (across all enrolled courses)

## 6. Out of scope

- Video hosting/streaming — lesson content is text/markdown only.
- Live classes, webinars, or any real-time session (that's project #17's
  peer-to-peer scheduling domain, not this one).
- Per-course discussion forums or comments — no social layer here (that's
  project #11 CampusConnect's job).
- Certificates/badges on course completion.
- Paid courses or any payment flow.
- Free-response/essay questions requiring manual grading — MCQ only, so
  every quiz stays instantly auto-gradable.
