# Testes

Estratégia de testes do projeto, organizada por técnica.

## Como rodar

```bash
php artisan test            # toda a suíte
php artisan test --filter=InscricaoStatusServiceTest
```

> **Banco de testes:** a suíte usa o MySQL (`transporte_test`), configurado em
> `phpunit.xml`. O banco é recriado a cada execução via `RefreshDatabase`.
> Para usar SQLite em memória (mais rápido), instale `php8.4-sqlite3` e troque
> `DB_CONNECTION`/`DB_DATABASE` no `phpunit.xml`.

## Teste Funcional — caixa-preta

Particionamento em **classes de equivalência** + **análise do valor-limite**.

| Arquivo | Alvo |
|---|---|
| `Feature/Auth/CpfLoginValidationTest.php` | Validação do `login` (e-mail OU CPF) em `POST /api/auth/token`. Classes válidas/inválidas + limites do comprimento do CPF (10 / 11 / 12 dígitos). |
| `Feature/Inscricao/InscricaoStoreValidationTest.php` | Regras do `StoreInscricaoRequest` em `POST /api/inscricoes`: `cpf` (size:11), `name` (min:3/max:255), `number` (min:1), `status` (prohibited). |
| `Feature/Inscricao/AnaliseInscricaoTest.php` | Decisão da lista de espera em `PUT /api/inscricoes/analise/{id}`: aprovação gera o estudante ativo, rejeição exige motivo, inscrição sem dados institucionais não é aprovada. |
| `Feature/Reecadastro/ReecadastroPublicoTest.php` | Fluxo público por CPF: período fechado, CPF fora do sistema, estudante inativo, abertura da solicitação, token de sessão, envio/reenvio de documentos, prazo adicional e finalização. |
| `Feature/Reecadastro/AnaliseReecadastroTest.php` | Homologação do recadastro: aprovar / rejeitar / devolver documentos, efeito no estudante, abertura exclusiva de período e download do arquivo. |

## Teste Estrutural — caixa-branca

Casos projetados para exercitar **todos os nós/arcos** do grafo de fluxo de
controle.

| Arquivo | Alvo |
|---|---|
| `Feature/Inscricao/InscricaoStatusServiceTest.php` | `InscricaoStatusService::isComplete()` — cada ramo (entrada nula, não encontrada, campo de inscrição nulo, termo não aceito, sem instituição, campo de instituição nulo, tudo completo → true). |

## Teste de Mutação

Configurado em `infection.json5` (pacote `infection/infection`).

```bash
# Requer um driver de cobertura (pcov ou xdebug):
#   sudo apt install php8.4-pcov
vendor/bin/infection --threads=max --show-mutations
vendor/bin/infection --filter=InscricaoStatusService.php   # run focado
```

O Infection gera mutantes (ex.: `===`→`!==`, `&&`→`||`, `true`→`false`) e mede
o **Mutation Score Indicator (MSI)** — quantos mutantes os testes "mataram".

## Achados durante a escrita dos testes

- **`isComplete()` exige 7 documentos**, mas a migration `inscricao_documentos`
  tentou declarar `inscricao_id` como `unique()` (1 doc por inscrição). O
  `->unique()` encadeado após a foreign key **não cria índice algum** no schema
  real, então o caminho "completo" é alcançável. Se a unicidade for corrigida,
  o ramo `true` se tornaria um *requisito não executável*.
