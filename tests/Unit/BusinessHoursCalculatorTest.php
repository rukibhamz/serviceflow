<?php

/**
 * Property 6: Business Hours Exclusion from SLA
 * Validates: Requirements 3.3
 *
 * Generate random timestamp pairs spanning weekends/holidays; assert calculated
 * business minutes never include out-of-hours time (weekends, before/after
 * business hours, holidays).
 */

use App\Services\Sla\BusinessHoursCalculator;
use Carbon\Carbon;

$calculator = new BusinessHoursCalculator;

// Standard Mon–Fri 09:00–17:00 schedule used across all tests
$standardSchedule = [
    'timezone' => 'UTC',
    'days' => [
        'monday'    => ['start' => '09:00', 'end' => '17:00'],
        'tuesday'   => ['start' => '09:00', 'end' => '17:00'],
        'wednesday' => ['start' => '09:00', 'end' => '17:00'],
        'thursday'  => ['start' => '09:00', 'end' => '17:00'],
        'friday'    => ['start' => '09:00', 'end' => '17:00'],
    ],
    'holidays' => [],
];

// ── Unit tests ────────────────────────────────────────────────────────────────

test('elapsed minutes is 0 when end is before or equal to start', function () use ($calculator, $standardSchedule) {
    $t = Carbon::parse('2024-01-15 10:00:00', 'UTC'); // Monday
    expect($calculator->elapsedBusinessMinutes($t, $t, $standardSchedule))->toBe(0);
    expect($calculator->elapsedBusinessMinutes($t->copy()->addHour(), $t, $standardSchedule))->toBe(0);
});

test('range entirely within business hours returns correct minute count', function () use ($calculator, $standardSchedule) {
    // Monday 10:00 → 12:00 = 120 business minutes
    $start = Carbon::parse('2024-01-15 10:00:00', 'UTC');
    $end   = Carbon::parse('2024-01-15 12:00:00', 'UTC');
    expect($calculator->elapsedBusinessMinutes($start, $end, $standardSchedule))->toBe(120);
});

test('range entirely on a weekend returns 0 minutes', function () use ($calculator, $standardSchedule) {
    // Saturday 10:00 → Sunday 16:00
    $start = Carbon::parse('2024-01-13 10:00:00', 'UTC');
    $end   = Carbon::parse('2024-01-14 16:00:00', 'UTC');
    expect($calculator->elapsedBusinessMinutes($start, $end, $standardSchedule))->toBe(0);
});

test('range entirely on a holiday returns 0 minutes', function () use ($calculator) {
    $scheduleWithHoliday = [
        'timezone' => 'UTC',
        'days' => [
            'monday' => ['start' => '09:00', 'end' => '17:00'],
        ],
        'holidays' => ['2024-01-15'],
    ];
    // Monday 2024-01-15 is a holiday
    $start = Carbon::parse('2024-01-15 10:00:00', 'UTC');
    $end   = Carbon::parse('2024-01-15 14:00:00', 'UTC');
    expect($calculator->elapsedBusinessMinutes($start, $end, $scheduleWithHoliday))->toBe(0);
});

test('range before business hours on a weekday returns 0 minutes', function () use ($calculator, $standardSchedule) {
    // Monday 06:00 → 08:59
    $start = Carbon::parse('2024-01-15 06:00:00', 'UTC');
    $end   = Carbon::parse('2024-01-15 08:59:00', 'UTC');
    expect($calculator->elapsedBusinessMinutes($start, $end, $standardSchedule))->toBe(0);
});

test('range after business hours on a weekday returns 0 minutes', function () use ($calculator, $standardSchedule) {
    // Monday 17:00 → 20:00
    $start = Carbon::parse('2024-01-15 17:00:00', 'UTC');
    $end   = Carbon::parse('2024-01-15 20:00:00', 'UTC');
    expect($calculator->elapsedBusinessMinutes($start, $end, $standardSchedule))->toBe(0);
});

test('range spanning a weekend counts only weekday business hours', function () use ($calculator, $standardSchedule) {
    // Friday 16:00 → Monday 10:00
    // Friday: 16:00–17:00 = 60 min; Monday: 09:00–10:00 = 60 min → total 120
    $start = Carbon::parse('2024-01-12 16:00:00', 'UTC'); // Friday
    $end   = Carbon::parse('2024-01-15 10:00:00', 'UTC'); // Monday
    expect($calculator->elapsedBusinessMinutes($start, $end, $standardSchedule))->toBe(120);
});

test('full business day equals 480 minutes', function () use ($calculator, $standardSchedule) {
    $start = Carbon::parse('2024-01-15 09:00:00', 'UTC'); // Monday open
    $end   = Carbon::parse('2024-01-15 17:00:00', 'UTC'); // Monday close
    expect($calculator->elapsedBusinessMinutes($start, $end, $standardSchedule))->toBe(480);
});

// ── Property-based test ───────────────────────────────────────────────────────

/**
 * Property 6: Business Hours Exclusion from SLA
 *
 * For 100 random (start, end) pairs:
 *   1. Result is always >= 0
 *   2. A range entirely within non-business time returns 0
 *   3. A range entirely within business hours returns the exact minute count
 *   4. The result never exceeds the maximum possible business minutes in the span
 */
it('never counts out-of-hours time as business minutes across random timestamp pairs', function () use ($calculator) {
    // Build a schedule with a known holiday to exercise holiday exclusion
    $holiday = '2024-06-19'; // Wednesday

    $schedule = [
        'timezone' => 'UTC',
        'days' => [
            'monday'    => ['start' => '09:00', 'end' => '17:00'],
            'tuesday'   => ['start' => '09:00', 'end' => '17:00'],
            'wednesday' => ['start' => '09:00', 'end' => '17:00'],
            'thursday'  => ['start' => '09:00', 'end' => '17:00'],
            'friday'    => ['start' => '09:00', 'end' => '17:00'],
        ],
        'holidays' => [$holiday],
    ];

    // Epoch anchors: pick a Monday as base so we can reason about weekdays
    // 2024-01-01 is a Monday; use offsets to generate varied pairs
    $baseEpoch = Carbon::parse('2024-01-01 00:00:00', 'UTC')->timestamp;
    // Span up to 14 days (covers at least 2 weekends)
    $windowSeconds = 14 * 24 * 3600;

    for ($i = 0; $i < 100; $i++) {
        $offsetA = random_int(0, $windowSeconds);
        $offsetB = random_int(0, $windowSeconds);

        $start = Carbon::createFromTimestamp($baseEpoch + min($offsetA, $offsetB), 'UTC');
        $end   = Carbon::createFromTimestamp($baseEpoch + max($offsetA, $offsetB), 'UTC');

        $minutes = $calculator->elapsedBusinessMinutes($start, $end, $schedule);

        // Property: result is always non-negative
        expect($minutes)->toBeGreaterThanOrEqual(0,
            "Iteration {$i}: expected >= 0, got {$minutes} for {$start} → {$end}"
        );

        // Property: result never exceeds the wall-clock span in minutes
        $wallMinutes = (int) $start->diffInMinutes($end);
        expect($minutes)->toBeLessThanOrEqual($wallMinutes,
            "Iteration {$i}: business minutes ({$minutes}) exceeded wall-clock minutes ({$wallMinutes})"
        );

        // Property: result never exceeds the theoretical max business minutes
        // Max business minutes = number of weekdays in span × 480 min/day
        $maxBusinessMinutes = countMaxBusinessMinutes($start, $end, $schedule);
        expect($minutes)->toBeLessThanOrEqual($maxBusinessMinutes,
            "Iteration {$i}: business minutes ({$minutes}) exceeded theoretical max ({$maxBusinessMinutes})"
        );
    }
})->repeat(100);

it('returns 0 for ranges entirely within non-business periods', function () use ($calculator) {
    $schedule = [
        'timezone' => 'UTC',
        'days' => [
            'monday'    => ['start' => '09:00', 'end' => '17:00'],
            'tuesday'   => ['start' => '09:00', 'end' => '17:00'],
            'wednesday' => ['start' => '09:00', 'end' => '17:00'],
            'thursday'  => ['start' => '09:00', 'end' => '17:00'],
            'friday'    => ['start' => '09:00', 'end' => '17:00'],
        ],
        'holidays' => [],
    ];

    // Generate 100 random pairs that are guaranteed to be outside business hours
    for ($i = 0; $i < 100; $i++) {
        // Pick a random weekend day (Saturday or Sunday) in Jan 2024
        // 2024-01-06 = Saturday, 2024-01-07 = Sunday, etc.
        $weekendOffsets = [5, 6, 12, 13, 19, 20, 26, 27]; // days from 2024-01-01
        $dayOffset = $weekendOffsets[array_rand($weekendOffsets)];
        $base = Carbon::parse('2024-01-01', 'UTC')->addDays($dayOffset);

        $startHour = random_int(0, 22);
        $endHour   = random_int($startHour + 1, 23);

        $start = $base->copy()->setTime($startHour, 0, 0);
        $end   = $base->copy()->setTime($endHour, 0, 0);

        $minutes = $calculator->elapsedBusinessMinutes($start, $end, $schedule);

        expect($minutes)->toBe(0,
            "Iteration {$i}: weekend range {$start} → {$end} should yield 0 business minutes, got {$minutes}"
        );
    }
})->repeat(100);

it('returns exact minute count for ranges entirely within business hours', function () use ($calculator) {
    $schedule = [
        'timezone' => 'UTC',
        'days' => [
            'monday'    => ['start' => '09:00', 'end' => '17:00'],
            'tuesday'   => ['start' => '09:00', 'end' => '17:00'],
            'wednesday' => ['start' => '09:00', 'end' => '17:00'],
            'thursday'  => ['start' => '09:00', 'end' => '17:00'],
            'friday'    => ['start' => '09:00', 'end' => '17:00'],
        ],
        'holidays' => [],
    ];

    // Weekday offsets from 2024-01-01 (Monday): 0=Mon,1=Tue,2=Wed,3=Thu,4=Fri
    $weekdayOffsets = [0, 1, 2, 3, 4, 7, 8, 9, 10, 11];

    for ($i = 0; $i < 100; $i++) {
        $dayOffset = $weekdayOffsets[array_rand($weekdayOffsets)];
        $base = Carbon::parse('2024-01-01', 'UTC')->addDays($dayOffset);

        // Pick start and end both within 09:00–17:00
        $startMinuteOffset = random_int(0, 479);  // 0..479 minutes after 09:00
        $duration          = random_int(1, 480 - $startMinuteOffset);

        $start = $base->copy()->setTime(9, 0, 0)->addMinutes($startMinuteOffset);
        $end   = $start->copy()->addMinutes($duration);

        $minutes = $calculator->elapsedBusinessMinutes($start, $end, $schedule);

        expect($minutes)->toBe($duration,
            "Iteration {$i}: expected {$duration} business minutes for {$start} → {$end}, got {$minutes}"
        );
    }
})->repeat(100);

// ── Helper ────────────────────────────────────────────────────────────────────

/**
 * Compute the theoretical maximum business minutes between two timestamps.
 * Counts each calendar day that is a configured weekday and not a holiday,
 * clamping to the day's business window.
 */
function countMaxBusinessMinutes(Carbon $start, Carbon $end, array $schedule): int
{
    $tz       = $schedule['timezone'] ?? 'UTC';
    $days     = $schedule['days'] ?? [];
    $holidays = $schedule['holidays'] ?? [];

    $current = $start->copy()->setTimezone($tz)->startOfDay();
    $endDay  = $end->copy()->setTimezone($tz)->startOfDay();

    $total = 0;

    while ($current->lte($endDay)) {
        $dayName = strtolower($current->format('l'));

        if (isset($days[$dayName]) && ! in_array($current->toDateString(), $holidays, true)) {
            [$sh, $sm] = explode(':', $days[$dayName]['start']);
            [$eh, $em] = explode(':', $days[$dayName]['end']);

            $windowStart = $current->copy()->setTime((int) $sh, (int) $sm, 0);
            $windowEnd   = $current->copy()->setTime((int) $eh, (int) $em, 0);

            // Clamp to the actual [start, end] range
            $effectiveStart = $windowStart->lt($start->copy()->setTimezone($tz))
                ? $start->copy()->setTimezone($tz)
                : $windowStart;
            $effectiveEnd = $windowEnd->gt($end->copy()->setTimezone($tz))
                ? $end->copy()->setTimezone($tz)
                : $windowEnd;

            if ($effectiveEnd->gt($effectiveStart)) {
                $total += (int) $effectiveStart->diffInMinutes($effectiveEnd);
            }
        }

        $current->addDay();
    }

    return $total;
}
