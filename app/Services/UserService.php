<?php

namespace App\Services;

use App\Models\User;
use App\Filters\UserFilter;
use App\Http\Requests\UserFilterRequest;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class UserService
{
    public function getPaginatedUsers(Request $request, string $orderBy = 'created_at', string $orderDirection = 'desc', int $perPage = 15): LengthAwarePaginator
    {
        // preciso criar uma facade para não precisar ficar fazendo new UserFilter
        // ou criar um trait Filterable e usar o scope filter
        return User::filter(new UserFilter($request))
            ->orderBy($orderBy, $orderDirection)
            ->paginate($perPage);
    }

    public function findUserById(string $id): User
    {
        return User::findOrFail($id);
    }
    
    public function findUserByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function createUser(array $data): User
    {
        $this->hashPasswordIfNeeded($data);
        return User::create($data);
    }

    public function updateUser(User $user, array $data): User
    {
        $this->hashPasswordIfNeeded($data);
        $user->update($data);
        
        return $user->fresh();
    }

    public function deleteUser(User $user): bool
    {
        return $user->delete();
    }

    public function restoreUser(string $id): bool
    {
        $user = User::withTrashed()->findOrFail($id);
        return $user->restore();
    }

    public function setUserActiveStatus(User $user, bool $active): User
    {
        $user->is_active = $active;
        $user->save();
        
        return $user->fresh();
    }

    protected function hashPasswordIfNeeded(array &$data): void
    {
        if (isset($data['password']) && $data['password']) {
            $data['password'] = bcrypt($data['password']);
        }
    }
}
