<?php

namespace App\Modules\Holidays\Repositories;

use App\Models\Holiday;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class HolidayRepository
{
    /**
     * @return array<int, int>
     */
    public function availableYears(): array
    {
        $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();
        $yearExpr = match ($driver) {
            'sqlite' => "strftime('%Y', holiday_date) as year",
            'pgsql' => "EXTRACT(YEAR FROM holiday_date)::integer as year",
            default => "YEAR(holiday_date) as year",
        };

        return Holiday::query()
            ->selectRaw($yearExpr)
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->map(fn ($year) => (int) $year)
            ->filter(fn (int $year): bool => $year > 0)
            ->values()
            ->all();
    }

    public function paginateCurrentYear(int $year, int $perPage = 20): LengthAwarePaginator
    {
        $perPage = max(10, min(100, $perPage));

        return Holiday::query()
            ->whereYear('holiday_date', $year)
            ->orderBy('holiday_date')
            ->orderBy('title')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return Collection<int, Holiday>
     */
    public function listCurrentYear(int $year): Collection
    {
        return Holiday::query()
            ->whereYear('holiday_date', $year)
            ->orderBy('holiday_date')
            ->orderBy('title')
            ->get();
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function create(array $attributes): Holiday
    {
        return Holiday::query()->create($attributes);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(Holiday $holiday, array $attributes): void
    {
        $holiday->update($attributes);
    }

    public function delete(Holiday $holiday): void
    {
        $holiday->delete();
    }
}
