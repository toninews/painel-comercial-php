# Painel Comercial PHP

Projeto de estudo evoluído para portfólio, com foco em engenharia de software aplicada em um cenário real de código legado.

## Contexto

Este sistema nasceu de um projeto antigo de curso (PHP OO clássico, server-side).  
O objetivo desta versão foi **refatorar progressivamente** a base para ganhar:

- melhor organização de código
- melhoria visual de interface
- correções de fluxo funcional
- maior previsibilidade para deploy e demonstração pública

## O que este projeto demonstra

- Capacidade de trabalhar em cima de legado sem “reescrever tudo do zero”
- Evolução incremental de arquitetura com entregas funcionando em produção
- Melhoria de UX/UI mantendo stack PHP tradicional
- Ajustes de segurança e proteção para ambiente demo
- Organização de documentação e trilha de evolução técnica

## Arquitetura atual (status honesto)

Este projeto **não é 100% Clean Architecture** e não se propõe a isso nesta fase.

Estado atual:

- Base original orientada a Active Record e componentes do microframework do curso
- Camada de `Services` criada/reforçada para concentrar regras de aplicação
- Controllers mais enxutos em fluxos principais
- Separação progressiva de responsabilidades sem ruptura total da base

Este projeto representa uma modernização incremental de um sistema legado em PHP, com refatoração progressiva orientada a manutenção, segurança e clareza arquitetural.

## Segurança e ambiente demo

Melhorias aplicadas no fluxo atual:

- autenticação por hash (`password_hash` / `password_verify`)
- limites de tentativa de login
- rate limit de escrita para conta demo (por IP e sessão)
- limite para criação em massa no modo demo
- scripts de reset/reseed para manter ambiente de demonstração estável

## Stack

- PHP 8.1+
- SQLite ou PostgreSQL
- Twig
- Dompdf
- Chart.js
- Apache (via Docker no deploy)

## Estrutura principal

- `App/Control` - controllers e páginas
- `App/Model` - entidades e regras de domínio legadas/atuais
- `App/Services` - regras de aplicação e integração entre camadas
- `App/Templates` - layout e recursos visuais
- `Lib/Nexa` - base/framework utilizado pelo projeto
- `scripts/` - utilitários de reset e seed

## Como rodar localmente

1. Instale dependências:

```bash
composer install
```

2. Crie `.env`:

```bash
cp .env.example .env
```

3. Configure credenciais de acesso:

- `APP_LOGIN`
- `APP_PASSWORD_HASH`

Gerar hash:

```bash
php -r "echo password_hash('sua-senha', PASSWORD_DEFAULT), PHP_EOL;"
```

4. Configure banco no `.env`:

SQLite:

```env
DB_TYPE=sqlite
DB_NAME=App/Database/painel_comercial.db
```

PostgreSQL:

```env
DB_TYPE=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_NAME=painel_comercial
DB_USER=postgres
DB_PASS=sua-senha
```

5. Suba servidor local:

```bash
php -S localhost:8000
```

6. Acesse:

- `http://localhost:8000/index-login.php`

## Conta demo (opcional)

Você pode publicar uma conta de demonstração via `.env`:

```env
APP_DEMO_LOGIN=demo
APP_DEMO_PASSWORD_HASH=HASH_DA_SENHA_DEMO
APP_DEMO_DISPLAY_LOGIN=demo
APP_DEMO_DISPLAY_PASSWORD=demo123
```

## Scripts úteis

Reset da base demo:

```bash
php scripts/reset_demo_db.php
```

Seed mínimo para estudo:

```bash
php scripts/reseed_minimal_demo.php
```

## Deploy (Render)

O projeto já possui arquivos prontos para deploy:

- `Dockerfile`
- `render.yaml`
- `docker/apache-vhost.conf`
- `docker/entrypoint.sh`

## Roadmap técnico

- continuar extração de regras de negócio para serviços
- reduzir pontos de SQL manual sensível
- ampliar cobertura de testes automatizados
- seguir evoluindo UX sem migrar para SPA neste momento

## Observação final

Este repositório mostra evolução real de código legado com decisões pragmáticas.  
Não é uma vitrine “perfeita” de arquitetura idealizada, e sim um projeto com refatoração contínua, trade-offs explícitos e melhoria consistente.
