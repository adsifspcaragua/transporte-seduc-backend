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
        // `frequencias.todas` alcanca a chamada de qualquer linha; sem ela, so
        // as linhas que o usuario conduz como motorista.
        'frequencias.view', 'frequencias.write', 'frequencias.delete', 'frequencias.todas',
        'justificativas.view', 'justificativas.analise',
        // Presenca por QR + dispositivo autorizado (ROADMAP.md).
        'veiculos.view', 'veiculos.write', 'veiculos.delete',
        'viagens.view', 'viagens.write',
        'geocercas.view', 'geocercas.write',
        // `dispositivos.analise` aprova/rejeita vinculacao e revoga credencial.
        'dispositivos.view', 'dispositivos.analise',
        // `presencas.analise` decide revisoes e registra presenca manual.
        'presencas.view', 'presencas.analise',
        // Exclusiva do estudante: registrar a propria presenca.
        'presencas.registrar',
        'auditoria.view',
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
            'frequencias.view', 'frequencias.write', 'frequencias.delete', 'frequencias.todas',
            'justificativas.view', 'justificativas.analise',
            'veiculos.view', 'veiculos.write', 'veiculos.delete',
            'viagens.view', 'viagens.write',
            'geocercas.view', 'geocercas.write',
            'dispositivos.view', 'dispositivos.analise',
            'presencas.view', 'presencas.analise',
            'auditoria.view',
        ],
        // Acompanha tudo, mas nao aprova dispositivo nem abre excecao de
        // presenca: quem libera a credencial ainda e decisao da gestao (D07).
        'operador' => [
            'estudantes.view', 'estudantes.write',
            'inscricoes.view',
            'linhas.view',
            'documentos.view',
            'periodos.view',
            'solicitacoes.view',
            'frequencias.view', 'frequencias.todas',
            'justificativas.view',
            'veiculos.view', 'viagens.view', 'geocercas.view',
            'dispositivos.view', 'presencas.view',
        ],
        // Faz a chamada da propria linha e nada mais: nao ve cadastro de
        // estudante, inscricao nem documento. A presenca por QR nao depende
        // dele, entao nenhuma permissao nova entra aqui.
        'motorista' => [
            'frequencias.view', 'frequencias.write',
        ],
        // A conta do estudante (estudantes.user_id): so registra e consulta a
        // propria presenca.
        'estudante' => [
            'presencas.registrar',
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
