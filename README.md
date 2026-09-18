# SIGTU — Sistema de Gestão de Transporte Universitário (Backend)

API REST em **Laravel** que gerencia o transporte universitário atendido pela
**Secretaria de Educação (SEDUC) de Caraguatatuba**. Este é o **backend** do SIGTU,
desenvolvido no componente curricular de **Engenharia de Software** do
**IFSP – Câmpus Caraguatatuba**, em substituição ao sistema legado **UNIBUS**.

A API cobre o domínio de inscrições e recadastro de estudantes, instituições de ensino,
cursos, linhas de transporte e a área administrativa, com autenticação via tokens e
controle de acesso baseado em papéis (RBAC).

---

## Stack tecnológica

| Camada | Tecnologia |
|---|---|
| Linguagem | **PHP 8.3** |
| Framework | **Laravel 13** |
| Autenticação | **Laravel Sanctum** (tokens) + **Laravel Fortify** |
| Monitoramento | **Laravel Pulse** |
| Documentação da API | **Scribe** (`knuckleswtf/scribe`) |
| Testes | **PHPUnit** + **Infection** (mutation testing) |
| Padronização de código | **Laravel Pint** |
| Banco de dados | **MySQL** |
| Build de assets | **Vite** + Tailwind CSS |

---

## Pré-requisitos

- **PHP 8.3+** (com as extensões padrão do Laravel)
- **Composer**
- **MySQL**
- **Node.js** (para o Vite / build de assets)

---

## Instalação

### 1. Clonar o repositório

```bash
git clone https://github.com/adsifspcaragua/transporte-seduc-backend.git
cd transporte-seduc-backend
```

### 2. Instalar as dependências PHP

```bash
composer install
```

### 3. Criar o arquivo de ambiente

```bash
cp .env.example .env
```

### 4. Gerar a chave da aplicação

```bash
php artisan key:generate
```

### 5. Configurar o banco de dados no `.env`

O padrão já é **MySQL** com a base **`transporte`**. Ajuste as credenciais conforme
o seu ambiente:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=transporte
DB_USERNAME=root
DB_PASSWORD=
```

> Crie a base de dados `transporte` no MySQL antes de rodar as migrations.

### 6. Rodar as migrations

```bash
php artisan migrate
```

### 7. Popular o banco com dados iniciais (seeders)

```bash
php artisan db:seed
```

### 8. Criar o link simbólico de storage

```bash
php artisan storage:link
```

### 9. Subir o servidor de desenvolvimento

```bash
php artisan serve
```

A API ficará disponível em `http://127.0.0.1:8000`.

### Atalhos do Composer

O `composer.json` já traz dois scripts utilitários:

- **`composer setup`** — instala dependências, cria o `.env` (se não existir),
  gera a chave, roda as migrations (`--force`), instala os pacotes do front
  (`npm install`) e faz o build (`npm run build`).

  ```bash
  composer setup
  ```

- **`composer dev`** — sobe, em paralelo, o servidor (`php artisan serve`), a fila
  (`queue:listen`), os logs (`php artisan pail`) e o Vite (`npm run dev`).

  ```bash
  composer dev
  ```

> O `composer setup` **não** roda `db:seed` nem `storage:link` — execute esses
> passos separadamente quando necessário.

### E-mails de teste (Mailpit)

Os avisos de frequência são enviados por e-mail. Em desenvolvimento eles vão
para o **[Mailpit](https://mailpit.axllent.org/)**, um receptor local e gratuito
que captura tudo sem entregar a ninguém:

1. Baixe o binário em <https://github.com/axllent/mailpit/releases> (no Windows,
   `mailpit-windows-amd64.zip`) e rode:

   ```bash
   mailpit --listen 127.0.0.1:8025 --smtp 127.0.0.1:1025
   ```

2. O `.env.example` já aponta para ele (`MAIL_MAILER=smtp`, porta `1025`).
3. Abra a caixa de entrada em <http://127.0.0.1:8025>.

Os e-mails saem pela **fila** (`QUEUE_CONNECTION=database`), então é preciso um
worker rodando. O `composer dev` já sobe um; rodando só o `php artisan serve`,
use também:

```bash
php artisan queue:work
```

> Os testes de upload geram imagens falsas e precisam da extensão **GD** do PHP
> habilitada (`extension=gd` no `php.ini`).

---

## Usuários de teste

Após `php artisan db:seed`, estes usuários ficam disponíveis (senha **`12345678`** para todos):

| E-mail | Perfil | Senha |
|---|---|---|
| `admin@example.com` | **admin** | `12345678` |
| `user@example.com` | **operador** | `12345678` |
| `motorista@example.com` | **motorista** (Linha Centro e Linha Noturna) | `12345678` |

---

## Testes

A suíte usa **MySQL** (`transporte_test`, configurado em `phpunit.xml`), recriado a cada
execução via `RefreshDatabase`.

```bash
php artisan test
# ou
composer test
```

O projeto também usa **Infection** para **teste de mutação** (configurado em
`infection.json5`), que semeia defeitos no código e mede o *Mutation Score Indicator* (MSI):

```bash
# Requer um driver de cobertura (pcov ou xdebug)
vendor/bin/infection --threads=max --show-mutations
```

> Detalhes da estratégia de testes (funcional, estrutural e de mutação) estão em
> [`tests/README.md`](tests/README.md).

---

## Documentação da API

A documentação é gerada pelo **Scribe**. Para (re)gerar:

```bash
php artisan scribe:generate
```

Depois, acesse a documentação interativa em:

```
http://127.0.0.1:8000/docs
```

> Também ficam disponíveis a coleção Postman (`/docs.postman`) e a especificação
> OpenAPI (`/docs.openapi`). As rotas protegidas exigem o cabeçalho
> `Authorization: Bearer TOKEN`, obtido em `POST /api/auth/token`.

---

## Equipe

| Área | Integrante | GitHub |
|---|---|---|
| Frontend | Hyan Ferreira | [@HyanFerreira](https://github.com/HyanFerreira) |
| Frontend | Murillo Diogo | [@mrllmoreira](https://github.com/mrllmoreira) |
| Backend | Mauricio Roseo | [@MauricioRoseo](https://github.com/MauricioRoseo) |
| Backend | Luan Ibanhez | [@luaniban](https://github.com/luaniban) |
| Backend | Eduarda Gonçalves | [@edualnd](https://github.com/edualnd) |
| UX/UI | Emerson Soares | [@emersonsoasilva](https://github.com/emersonsoasilva) |
| POO | Robert Cortez Rudi | [@Robert-Cortez-Rudi](https://github.com/Robert-Cortez-Rudi) |

---

## Licença

Este projeto está licenciado sob a **GNU General Public License v3.0 (GPLv3)**.
Consulte o arquivo [LICENSE](LICENSE) para o texto completo.
