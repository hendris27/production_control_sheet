<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

/**
 * @mixin \Spatie\Permission\Traits\HasRoles
 */
class User extends Authenticatable
{
    protected $fillable = [
        'name', 'nik', 'password', 'role',
    ];
    /**
     * Include role names in array / JSON representations.
     * This adds a `role_names` attribute containing an array of role names.
     */
    protected $appends = ['role_names'];
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles {
        // alias trait methods so we can call them from overrides
        assignRole as protected traitAssignRole;
        syncRoles as protected traitSyncRoles;
        removeRole as protected traitRemoveRole;
    }
    // ...existing code...

    /**
     * Override auth identifier to use nik instead of email.
     */
    public function getAuthIdentifierName()
    {
        return 'nik';
    }
// ...existing code...
    /**
     * Override username for authentication to use nik instead of email.
     */
    public function username()
    {
        return 'nik';
    }

    /**
     * Return role names for JSON/array output.
     *
     * @return array
     */
    public function getRoleNamesAttribute()
    {
        return $this->getRoleNames()->toArray();
    }

    /**
     * Keep `role` column in users table in sync with assigned roles.
     */
    public function updateRoleColumn(): void
    {
        $names = $this->getRoleNames()->toArray();
        $value = is_array($names) ? implode(', ', $names) : $names;
        $this->role = $value ?: null;
        $this->saveQuietly();
    }

    public function assignRole(...$roles)
    {
        $result = $this->traitAssignRole(...$roles);
        $this->updateRoleColumn();
        return $result;
    }

    public function syncRoles(...$roles)
    {
        $result = $this->traitSyncRoles(...$roles);
        $this->updateRoleColumn();
        return $result;
    }

    public function removeRole($role)
    {
        $result = $this->traitRemoveRole($role);
        $this->updateRoleColumn();
        return $result;
    }
}
