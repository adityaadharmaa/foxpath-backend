<?php

namespace App\Exports\Criterias;

use App\Models\Criteria;
use Maatwebsite\Excel\Concerns\{
    FromQuery,
    WithHeadings,
    WithMapping
};

class CriteriaExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(
        protected ?bool $isActive = null
    ) {}

    public function query()
    {
        $query = Criteria::query();

        if (!is_null($this->isActive)) {
            $query->where('is_active', (bool) $this->isActive);
        }

        return $query->orderBy('created_at');
    }

    public function headings(): array
    {
        return [
            'Code',
            'Name',
            'Weight',
            'Type',
            'Status',
            'Created At',
        ];
    }

    public function map($criteria): array
    {
        return [
            $criteria->code,
            $criteria->name,
            $criteria->weight,
            ucfirst($criteria->type),
            $criteria->is_active ? 'Active' : 'Inactive',
            optional($criteria->created_at)->toDateTimeString(),
        ];
    }
}