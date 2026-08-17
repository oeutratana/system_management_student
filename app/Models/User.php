<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'role_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
    ];

    public function classes()
    {
        return $this->hasMany(SchoolClass::class, 'teacher_id');
    }

    public function teacherProfile()
    {
        return $this->hasOne(Teacher::class, 'user_id');
    }

    public function studentProfile()
    {
        return $this->hasOne(Student::class, 'user_id');
    }

    /**
     * A user has exactly one role, linked through users.role_id.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * The permissions granted to the user through their role.
     *
     * The pivot table is permission_role and the user's key on the pivot is
     * its role_id, so the parent key must be users.role_id rather than users.id.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'permission_role',
            'role_id',
            'permission_id',
            'role_id'
        );
    }

    /**
     * Keep the legacy `role` name column in sync with the roles table.
     *
     * Whenever `role` is mass-assigned (register / user management) we resolve
     * and store the matching role_id so permission checks keep working.
     */
    public function setRoleAttribute(?string $value): void
    {
        $this->attributes['role'] = $value;

        $this->attributes['role_id'] = $value
            ? Role::where('name', $value)->value('id')
            : null;
    }

    /**
     * The name of the user's role, or null when the user has no role.
     */
    public function roleName(): ?string
    {
        return $this->role;
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->roleName(), $roles, true);
    }

    /**
     * Whether the user's role is granted the given permission.
     *
     * Returns false when the user has no role assigned.
     */
    public function hasPermission(string $permission): bool
    {
        return $this->role_id !== null
            && $this->permissions()->where('permissions.name', $permission)->exists();
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isTeacher(): bool
    {
        return $this->hasRole('teacher');
    }

    public function isStudent(): bool
    {
        return $this->hasRole('student');
    }
}
