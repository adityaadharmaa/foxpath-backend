<?php

namespace App\Exports\Users;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsersExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(
        protected ?string $type = null
    ) {}

    public function query()
    {
        $query = User::select(
            'users.id',
            'users.username',
            'users.email',
            'roles.name as role_name',
            'profiles.applicant_type',
            'users.is_active',
            'users.created_at'
        )
            ->join('roles', 'users.roles_id', '=', 'roles.id')
            ->join('profiles', 'users.id', '=', 'profiles.users_id');

        if ($this->type) {
            $query->where('profiles.applicant_type', $this->type);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Username',
            'Email',
            'Role',
            'Applicant Type',
            'Status',
            'Created At'
        ];
    }

    public function map($user): array
    {
        return [
            $user->id,
            $user->username,
            $user->email,
            $user->role_name,
            $user->applicant_type ?? '-',
            $user->is_active ? 'Active' : 'Inactive',
            optional($user->created_at)->toDateTimeString(),
        ];
    }
}
