<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'permissions',
    ];

    public const PERMISSIONS = [
        'visualizar_usuarios' => 'Visualizar usuarios',
        'criar_usuarios' => 'Criar usuarios',
        'editar_usuarios' => 'Editar usuarios',
        'excluir_usuarios' => 'Excluir usuarios',
        'editar_cliente' => 'Editar cliente',
        'excluir_cliente' => 'Excluir cliente',
        'editar_contrato' => 'Editar contrato',
        'excluir_contrato' => 'Excluir contrato',
        'visualizar_bancos' => 'Visualizar bancos',
        'criar_bancos' => 'Criar bancos',
        'editar_bancos' => 'Editar bancos',
        'excluir_bancos' => 'Excluir bancos',
        'visualizar_convenios' => 'Visualizar convenios',
        'criar_convenio' => 'Criar convenio',
        'editar_convenio' => 'Editar convenio',
        'excluir_convenio' => 'Excluir convenio',
    ];

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function hasPermission(string $permission): bool
    {
        return $this->isAdmin() || in_array($permission, $this->permissions ?? [], true);
    }

    public function hasAnyPermission(array $permissions): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return collect($permissions)->contains(fn (string $permission) => $this->hasPermission($permission));
    }

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
            'permissions' => 'array',
        ];
    }
}
