<?php

namespace App\Exports\Programs;

use App\Models\Program;
use Maatwebsite\Excel\Concerns\{
    FromQuery,
    WithHeadings,
    WithMapping,
};

class ProgramsExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(
        protected bool $includeDeleted = false,
        protected bool $deletedOnly = false,
        protected ?bool $isActive = null
    ) {}

    public function query()
    {
        $query = Program::query();

        if ($this->deletedOnly) {
            $query->onlyTrashed();
        } elseif ($this->includeDeleted) {
            $query->withTrashed();
        }

        if (!is_null($this->isActive)) {
            $query->where('is_active', (int) $this->isActive);
        }

        return $query->orderByDesc('created_at');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Description',
            'Capacity',
            'Is Active',
            'Deleted',
            'Created At',
        ];
    }

    public function map($program): array
    {
        return [
            $program->id,
            $program->name,
            $program->description,
            $program->capacity,
            $program->is_active ? 'Active' : 'Inactive',
            $program->deleted_at ? 'Yes' : 'No',
            optional($program->created_at)->toDateTimeString(),
        ];
    }
}