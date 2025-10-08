# Multban - Sistema ERP

## Visao geral
O Multban e uma plataforma ERP desenvolvida em Laravel voltada para a gestao integrada de empresas que operam com multiplos produtos, servicos e pontos de venda. A aplicacao concentra os principais pilares administrativos (cadastros, vendas, faturamento, cobrancas, relatorios e workflows) em um unico painel, com controle de permissoes por perfil e suporte a filas de processamento para tarefas assincronas.

## Principais modulos
- **Clientes:** cadastro, prontuario, cartoes e pesquisa avancada de clientes (`app/Http/Controllers/Multban/Cliente`).
- **Usuarios e Perfis:** gestao de contas, papeis e permissoes com Spatie Permission (`app/Http/Controllers/Multban/Usuario`, `Perfil`, `PerfilDeAcesso`).
- **Agenda e Workflow:** agendamento de atendimentos e definicao de fluxos operacionais (`app/Http/Controllers/Multban/Agendamento`, `WorkFlow`).
- **Produtos, Vendas e Programa de Pontos:** catalogo, transacoes e fidelizacao (`app/Http/Controllers/Multban/Produto`, `Venda`, `ProgramaPTS`).
- **Faturamento e Cobranca:** emissao e manutencao de titulos, painel financeiro e integracao com DataTables (`app/Http/Controllers/Multban/FaturamentoServico`, `PainelCobranca`, `ManutencaoTitulo`).
- **Recarga, Gift Cards e Carga de Dados:** operacoes financeiras complementares e importadores (`app/Http/Controllers/Multban/RecargaCartoes`, `GiftCard`, `CargaDados`).
- **Relatorios e Configuracoes:** dashboards gerenciais e parametrizacoes gerais (`app/Http/Controllers/Multban/Relatorios`, `Configuracoes`).

## Stack e dependencias
- **Backend:** Laravel 12, Livewire, PHP 8.2+, Spatie Permission, Yajra DataTables.
- **Frontend:** Vite 6, Tailwind CSS 4, Bootstrap 5, Sass e Axios.
- **Banco de dados:** compativel com SQLite (padrao local) ou MySQL/MariaDB; armazenamento de sessoes, cache e filas via drivers de banco.
- **Outros:** filas com `queue:work`, upload de arquivos pelo disco `storage` (necessario `php artisan storage:link`).

## Pre-requisitos
- PHP 8.2 ou superior com extensoes padrao do Laravel (`mbstring`, `pdo`, `openssl`, `json`, `ctype`, `fileinfo`, `bcmath`).
- Composer 2.x.
- Node.js 18+ e npm.
- Banco de dados disponivel (SQLite, MySQL ou MariaDB). Para SQLite e preciso garantir a extensao `pdo_sqlite`.
- Redis opcional se desejar migrar o cache/filas para Redis (ajustar `.env`).

## Como iniciar o projeto
1. **Clonar o repositorio**
   ```bash
   git clone <url-do-repo>
   cd Multban-Sistema-ERP
   ```
2. **Configurar variaveis de ambiente**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   - Ajuste `APP_URL`, zona de horario e as credenciais de banco.
   - Para usar SQLite localmente:
     ```bash
     touch database/database.sqlite
     DB_CONNECTION=sqlite
     ```
3. **Instalar dependencias**
   ```bash
   composer install
   npm install
   ```
4. **Preparar assets e armazenamento**
   ```bash
   php artisan storage:link
   ```
5. **Migrar e popular a base**
   ```bash
   php artisan migrate --seed
   ```
   - A semente cria um usuario administrador (`admin` / `12345678`) e sincroniza permissoes iniciais.
6. **Rodar servidores de desenvolvimento**
   - Backend: `php artisan serve`
   - Frontend (Vite): `npm run dev`
   - Ou utilize o script integrado: `composer run dev` (orquestra PHP, fila e Vite via `concurrently`).
7. **Filas e tarefas assincronas**
   ```bash
   php artisan queue:work
   ```
   Recomendado manter um worker ativo quando funcionalidades que disparam jobs forem utilizadas.

## Scripts uteis
- `npm run build`: gera os assets otimizados para producao.
- `php artisan migrate:fresh --seed`: recria o banco com dados iniciais.
- `php artisan test`: executa a suite de testes (inclui testes Livewire e de autenticacao).

### Rotinas especificas de dados
- `php artisan migrate:fresh --seed`: reinicializa o banco principal.
- `php artisan dbsysclient "migrate:fresh --path=database/migrations/dbsysclient --database=dbsysclient" --dbsysclient 1`: recria o schema do banco secundario `dbsysclient`.
- `php artisan dbsysclient "db:seed --class=DatabaseDbSysClientSeeder --database=dbsysclient" --dbsysclient 1`: popula o banco `dbsysclient` com dados mestres.

## Estrutura de diretorios (alto nivel)
- `app/Http/Controllers/Multban`: controladores dos modulos de negocio.
- `app/Http/Routes`: agrupadores que registram rotas com middlewares de permissao.
- `resources/views`: templates Blade organizados por modulos (tema Multban).
- `database/migrations` e `database/seeders`: schema e dados iniciais (perfis, usuario admin, configuracao de empresas).
- `tests/Feature`: cobertura para fluxos de autenticacao, perfis e configuracoes via Livewire.

## Proximos passos
- Ajustar o `.env` com credenciais reais de banco/redis e e-mail antes de subir para ambientes externos.
- Revisar as seeds sensiveis (como credenciais padrao) quando publicar em producao.
- Configurar supervisores (ex.: Supervisor, systemd) para `queue:work` e `schedule:run` em producao.

---
Projeto construido sobre o **Laravel Livewire Starter Kit**, customizado para atender o ecossistema Multban.
