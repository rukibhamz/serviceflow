<?php

namespace App\Services\Sla;

use Carbon\Carbon;

class BusinessHoursCalculator
{
    /**
     * Advance $start by $minutes of business time and return the resulting timestamp.
     */
    public function addBusinessMinutes(Carbon $start, int $minutes, array $schedule): Carbon
    {
        $current = $start->copy();
        $remaining = $minutes;

        while ($remaining > 0) {
            if ($this->isBusinessTime($current, $schedule)) {
                // Find how many consecutive business minutes remain from $current
                $chunk = $this->minutesUntilEndOfBusinessWindow($current, $schedule);
                if ($chunk >= $remaining) {
                    $current->addMinutes($remaining);
                    $remaining = 0;
                } else {
                    $remaining -= $chunk;
                    $current->addMinutes($chunk);
                }
            } else {
                // Jump to the next business window start
                $current = $this->nextBusinessWindowStart($current, $schedule);
            }
        }

        return $current;
    }

    /**
     * Count business minutes between two timestamps.
     */
    public function elapsedBusinessMinutes(Carbon $start, Carbon $end, array $schedule): int
    {
        if ($end->lte($start)) {
            return 0;
        }

        $current = $start->copy();
        $elapsed = 0;

        while ($current->lt($end)) {
            if ($this->isBusinessTime($current, $schedule)) {
                $windowEnd = $this->endOfBusinessWindow($current, $schedule);
                $chunkEnd = $windowEnd->lt($end) ? $windowEnd : $end->copy();
                $elapsed += (int) $current->diffInMinutes($chunkEnd);
                $current = $chunkEnd->copy();
            } else {
                $next = $this->nextBusinessWindowStart($current, $schedule);
                if ($next->gte($end)) {
                    break;
                }
                $current = $next;
            }
        }

        return $elapsed;
    }

    /**
     * Returns true if $moment falls within a working window.
     */
    public function isBusinessTime(Carbon $moment, array $schedule): bool
    {
        $tz = $schedule['timezone'] ?? 'UTC';
        $local = $moment->copy()->setTimezone($tz);

        // Check holidays
        $holidays = $schedule['holidays'] ?? [];
        if (in_array($local->toDateString(), $holidays, true)) {
            return false;
        }

        $dayName = strtolower($local->format('l')); // e.g. 'monday'
        $days = $schedule['days'] ?? [];

        if (! isset($days[$dayName])) {
            return false;
        }

        $dayConfig = $days[$dayName];
        $start = $this->parseTime($local, $dayConfig['start']);
        $end   = $this->parseTime($local, $dayConfig['end']);

        return $local->gte($start) && $local->lt($end);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function parseTime(Carbon $date, string $time): Carbon
    {
        [$h, $m] = explode(':', $time);

        return $date->copy()->setTime((int) $h, (int) $m, 0);
    }

    /**
     * Returns the end of the current business window (exclusive).
     */
    private function endOfBusinessWindow(Carbon $moment, array $schedule): Carbon
    {
        $tz = $schedule['timezone'] ?? 'UTC';
        $local = $moment->copy()->setTimezone($tz);
        $dayName = strtolower($local->format('l'));
        $days = $schedule['days'] ?? [];

        $end = $this->parseTime($local, $days[$dayName]['end']);

        return $end->setTimezone($moment->timezone);
    }

    /**
     * How many minutes from $moment until the end of its business window.
     */
    private function minutesUntilEndOfBusinessWindow(Carbon $moment, array $schedule): int
    {
        $end = $this->endOfBusinessWindow($moment, $schedule);

        return max(0, (int) $moment->diffInMinutes($end));
    }

    /**
     * Jump forward to the start of the next business window.
     */
    private function nextBusinessWindowStart(Carbon $moment, array $schedule): Carbon
    {
        $tz = $schedule['timezone'] ?? 'UTC';
        $current = $moment->copy()->setTimezone($tz);
        $days = $schedule['days'] ?? [];
        $holidays = $schedule['holidays'] ?? [];

        // Try up to 14 days ahead to find the next window
        for ($i = 0; $i < 14; $i++) {
            $dayName = strtolower($current->format('l'));

            if (isset($days[$dayName]) && ! in_array($current->toDateString(), $holidays, true)) {
                $windowStart = $this->parseTime($current, $days[$dayName]['start']);
                $windowEnd   = $this->parseTime($current, $days[$dayName]['end']);

                if ($current->lt($windowStart)) {
                    return $windowStart->setTimezone($moment->timezone);
                }

                if ($current->lt($windowEnd)) {
                    // Already inside — caller should not reach here, but handle gracefully
                    return $current->setTimezone($moment->timezone);
                }
            }

            // Move to start of next day
            $current = $current->copy()->addDay()->startOfDay();
        }

        // Fallback: return moment + 1 day (should never happen with a valid schedule)
        return $moment->copy()->addDay();
    }
}
