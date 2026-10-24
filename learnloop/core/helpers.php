<?php

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect(string $route): void
{
    // $route may carry its own query string, e.g. redirect('instructor/lessons?course_id=5').
    // Split it so those extra params land as real top-level query params alongside 'route'
    // rather than becoming a single unmatched value like "route=lessons?course_id=5".
    [$path, $query] = array_pad(explode('?', $route, 2), 2, null);
    $url = '/index.php?route=' . $path;
    if ($query !== null && $query !== '') {
        $url .= '&' . $query;
    }
    header('Location: ' . $url);
    exit;
}

function flash_set(string $type, string $message): void
{
    $_SESSION['flash'][$type] = $message;
}

function flash_get(string $type): ?string
{
    if (empty($_SESSION['flash'][$type])) {
        return null;
    }
    $message = $_SESSION['flash'][$type];
    unset($_SESSION['flash'][$type]);
    return $message;
}

function old(array $data, string $key, string $default = ''): string
{
    return e($data[$key] ?? $default);
}

function format_date(?string $date): string
{
    if (empty($date)) {
        return '-';
    }
    $ts = strtotime($date);
    return $ts === false ? '-' : date('d M Y', $ts);
}

function format_datetime(?string $datetime): string
{
    if (empty($datetime)) {
        return '-';
    }
    $ts = strtotime($datetime);
    return $ts === false ? '-' : date('d M Y, h:i A', $ts);
}

/** Renders lesson content: escaped first, then a minimal set of markdown-ish substitutions
 *  (line breaks, **bold**, *italic*) -- content is plain/markdown text per FEATURES.md, not HTML. */
function render_lesson_content(?string $content): string
{
    $escaped = e($content);
    $escaped = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $escaped);
    $escaped = preg_replace('/(?<!\*)\*(?!\*)(.+?)(?<!\*)\*(?!\*)/s', '<em>$1</em>', $escaped);
    return nl2br($escaped);
}

function percent(int $part, int $whole): int
{
    if ($whole <= 0) {
        return 0;
    }
    return (int)round(($part / $whole) * 100);
}

/** A stored percentage for display: 75.00 -> "75%", 66.67 -> "66.7%". */
function format_pct($value): string
{
    if ($value === null || $value === '') {
        return '—';
    }
    $rounded = round((float)$value, 1);
    return rtrim(rtrim(number_format($rounded, 1, '.', ''), '0'), '.') . '%';
}

/** Stable tint (0-7) so each course or person keeps the same colour on every screen. */
function avatar_tint(?int $id, string $name = ''): int
{
    return $id !== null && $id > 0 ? $id % 8 : abs(crc32($name)) % 8;
}

/** Placeholder cover for a course: the same open-book image for every course, tinted per course. */
function course_cover(int $courseId, string $title, string $size = ''): string
{
    $svg = '<svg viewBox="0 0 48 48" aria-hidden="true">'
        . '<path d="M24 13.5c-3.6-2.6-8.4-3.7-14-3.3a1.6 1.6 0 0 0-1.5 1.6v22.4c0 .9.8 1.7 1.7 1.6 5.2-.3 9.8.7 13.8 3.2z" opacity=".55"/>'
        . '<path d="M24 13.5c3.6-2.6 8.4-3.7 14-3.3 .8.1 1.5.8 1.5 1.6v22.4c0 .9-.8 1.7-1.7 1.6-5.2-.3-9.8.7-13.8 3.2z"/>'
        . '</svg>';
    return '<span class="course-cover ' . e($size) . ' tint-' . avatar_tint($courseId, $title) . '" role="img" aria-label="' . e($title) . '">' . $svg . '</span>';
}

/** Placeholder image for a person: the same silhouette for everyone, tinted per user. */
function person_avatar(?int $userId, string $name, string $size = ''): string
{
    $svg = '<svg viewBox="0 0 40 40" aria-hidden="true">'
        . '<circle cx="20" cy="15.5" r="7.2"/><path d="M5.5 41c0-9 6.5-14.2 14.5-14.2S34.5 32 34.5 41z"/>'
        . '</svg>';
    return '<span class="person-avatar ' . e($size) . ' tint-' . avatar_tint($userId, $name) . '" role="img" aria-label="' . e($name) . '">' . $svg . '</span>';
}

/** The LearnLoop mark: a ring with a gap and an arrowhead -- a learning loop. */
function brand_mark(string $class = 'brand-mark'): string
{
    return '<svg class="' . e($class) . '" viewBox="0 0 32 32" aria-hidden="true">'
        . '<path d="M26.2 11.5A11 11 0 1 0 27 16" fill="none" stroke="currentColor" stroke-width="3.4" stroke-linecap="round"/>'
        . '<path d="M22.6 10.3l5.2 1.9 1.4-5.4" fill="none" stroke="currentColor" stroke-width="3.4" stroke-linecap="round" stroke-linejoin="round"/>'
        . '<circle cx="16" cy="16" r="3.6" fill="#E8A33D"/>'
        . '</svg>';
}
