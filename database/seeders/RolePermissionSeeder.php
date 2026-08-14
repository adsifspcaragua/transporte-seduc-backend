<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Todas as permissões do sistema (título => descrição implícita pelo nome).
     *
     * @var list<string>
     */
    private array $permissions = [
        'users.view', 'users.write', 'users.delete',
        'roles.view', 'roles.write', 'roles.delete',
        'estudantes.view', 'estudantes.write', 'estudantes.delete',
        'inscricoes.view', 'inscricoes.delete', 'inscricoes.analise',
        'instituicoes.write', 'instituicoes.delete',
        'linhas.view', 'linhas.write', 'linhas.delete',
        'documentos.view', 'documentos.delete',
        'periodos.view', 'periodos.write', 'periodos.delete',
        'solicitacoes.view', 'solicitacoes.analise', 'solicitacoes.delete',
    ];

    /**
     * Matriz de permissões por perfil. "admin" recebe todas (e ainda tem bypass no middleware).
     *
     * @var array<string, list<string>>
     */
    private array $matrix = [
        'gestor' => [
            'estudantes.view', 'estudantes.write', 'estudantes.delete',
            'inscricoes.view', 'inscricoes.delete', 'inscricoes.analise',
            'instituicoes.write', 'instituicoes.delete',
            'linhas.view', 'linhas.write', 'linhas.delete',
            'documentos.view', 'documentos.delete',
            'periodos.view', 'periodos.write', 'periodos.delete',
            'solicitacoes.view', 'solicitacoes.analise', 'solicitacoes.delete',
        ],
        'operador' => [
            'estudantes.view', 'estudantes.write',
            'inscricoes.view',
            'linhas.view',
            'documentos.view',
            'periodos.view',
            'solicitacoes.view',
        ],
    ];

    public function run(): void
    {
        $permissions = collect($this->permissions)->mapWithKeys(
            fn (string $title) => [$title => Permission::firstOrCreate(['title' => $title])]
        );

        // admin: todas as permissões
        $admin = Role::firstOrCreate(['title' => 'admin']);
        $admin->permissions()->sync($permissions->pluck('id'));

        // demais perfis conforme a matriz
        foreach ($this->matrix as $roleTitle => $granted) {
            $role = Role::firstOrCreate(['title' => $roleTitle]);
            $ids = $permissions->only($granted)->pluck('id');
            $role->permissions()->sync($ids);
        }

        // Permissões que deixaram de existir saem junto com os vínculos (cascata).
        Permission::whereNotIn('title', $this->permissions)->delete();
    }
}
