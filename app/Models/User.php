<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'cpf',
        'matricula',
        'data_nascimento',
        'ativo',
    ];

    /**
     * Cache de permissões resolvidas dentro de uma mesma requisição.
     *
     * @var array<string, bool>|null
     */
    private ?array $resolvedPermissions = null;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'ativo' => 'boolean',
        ];
    }

    /**
     * Cargos (roles) atribuídos ao usuário.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    /**
     * Linhas que o usuário conduz como motorista.
     */
    public function linhas(): HasMany
    {
        return $this->hasMany(Linha::class, 'motorista_id');
    }

    /**
     * Verifica se o usuário possui algum dos cargos informados (por título).
     */
    public function hasRole(string ...$titles): bool
    {
        return $this->roles->whereIn('title', $titles)->isNotEmpty();
    }

    /**
     * @return list<string>
     */
    public function roleTitles(): array
    {
        return $this->roles
            ->pluck('title')
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    public function permissionTitles(): array
    {
        if ($this->hasRole('admin')) {
            return Permission::query()
                ->orderBy('title')
                ->pluck('title')
                ->all();
        }

        return $this->roles
            ->loadMissing('permissions')
            ->pluck('permissions')
            ->flatten()
            ->pluck('title')
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /**
     * Verifica se o usuário possui a permissão informada (por título),
     * considerando as permissões de todos os seus cargos.
     */
    public function hasPermission(string $title): bool
    {
        if ($this->resolvedPermissions === null) {
            $this->resolvedPermissions = $this->roles
                ->loadMissing('permissions')
                ->pluck('permissions')
                ->flatten()
                ->pluck('title')
                ->flip()
                ->map(fn () => true)
                ->all();
        }

        return isset($this->resolvedPermissions[$title]);
    }
}
