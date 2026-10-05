<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;

/**
 * Date filter used on admin lists (Articles, Contact Messages, Subscribers, Ad Enquiries).
 *
 *   ?period=all                           (default – no date filter)
 *   ?period=today
 *   ?period=month
 *   ?period=custom&from=2026-09-01&to=2026-09-15
 *
 *   $range = DateRange::fromRequest($request);
 *   $range->apply($query, 'created_at');
 */
class DateRange
{
    public string $period = 'all';
    public ?Carbon $from = null;
    public ?Carbon $to = null;
    public string $label = 'All time';

    public static function fromRequest(Request $request): self
    {
        $range = new self();
        $now = now();
        $period = $request->query('period');

        if ($period === 'today') {
            $range->period = 'today';
            $range->from = $now->copy()->startOfDay();
            $range->to = $now->copy()->endOfDay();
            $range->label = 'Today';
        } elseif ($period === 'month') {
            $range->period = 'month';
            $range->from = $now->copy()->startOfMonth();
            $range->to = $now->copy()->endOfMonth();
            $range->label = $now->format('F Y');
        } elseif ($period === 'custom' && $request->filled('from')) {
            try {
                $from = Carbon::parse($request->query('from'))->startOfDay();
                $to = Carbon::parse($request->query('to') ?: $request->query('from'))->endOfDay();
                if ($to->lt($from)) {
                    [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
                }
                $range->period = 'custom';
                $range->from = $from;
                $range->to = $to;
                $range->label = $from->isSameDay($to)
                    ? $from->format('d M Y')
                    : $from->format('d M') . ' – ' . $to->format('d M Y');
            } catch (\Throwable $e) {
                // bad date typed → show everything
            }
        }

        return $range;
    }

    public function active(): bool
    {
        return $this->period !== 'all';
    }

    /**
     * Limit a query to the chosen dates.
     * $column may be a raw SQL expression such as "COALESCE(published_at, created_at)".
     */
    public function apply(EloquentBuilder|QueryBuilder $query, string $column = 'created_at'): EloquentBuilder|QueryBuilder
    {
        if (!$this->active()) {
            return $query;
        }

        if (preg_match('/^[a-z_.]+$/i', $column)) {
            return $query->whereBetween($column, [$this->from, $this->to]);
        }

        return $query->whereRaw("{$column} BETWEEN ? AND ?", [$this->from, $this->to]);
    }

    /** Value for the "from" / "to" date boxes. */
    public function fromValue(): string
    {
        return ($this->from ?? now()->startOfMonth())->format('Y-m-d');
    }

    public function toValue(): string
    {
        return ($this->to ?? now())->min(now())->format('Y-m-d');
    }
}
