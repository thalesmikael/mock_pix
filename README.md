# Mock PIX
Sistema mock para PIX seguindo padrão MVC com **Model (DAO), Controllers e Services**.

## 🏗️ Arquitetura

- **PHP 8.3** com tipagem estrita
- **Padrão MVC** com separação clara de responsabilidades
- **MySQL 8** para persistência
- **RabbitMQ** para eventos assíncronos
- **Código limpo e testável** sem overengineering

## Fluxograma

- <img width="853" height="1090" alt="image" src="https://github.com/user-attachments/assets/7405d17d-998a-40d9-a2b0-8c040daa84ba" />


## 🚀 Funcionalidades

### Validação de Origem
- Whitelist de origens confiáveis configurável via `.env`

### Tipos de PIX
- **NORMAL**: PIX único
- **RECORRENTE**: PIX recorrente

### Status do PIX
- **PENDENTE**: Aguardando aprovação
- **APROVADO**: PIX aprovado
- **EXPIRADO**: PIX expirado

### Eventos e Webhooks
- **Webhook de expiração**: Banco notifica expiração
- **Evento de aprovação**: Publica para envio de email
- **Evento de expiração**: Publica para recuperação de vendas

### SLA e Monitoramento
- **SLA < 2 min**: Entre aprovação e envio de email
- **Logs de SLA**: Monitoramento de performance

### Relatório Diário
- **Cron às 09:00**: Gera CSV com totais do dia anterior
- **Envio automático**: Email para CEO com anexo CSV

## 📁 Estrutura do Projeto

```
/app
  /Config          # Configurações e container DI
  /Domain/Enums    # Enums de domínio
  /Models          # Entidades e DAOs
  /Services        # Regras de negócio e implementações
  /Http/Controllers # Controllers HTTP
/public            # Ponto de entrada da aplicação
/scripts           # Scripts de migração, worker e relatório
/storage           # Emails e relatórios gerados
```

## 🛠️ Stack Técnica

- **PHP 8.3**
- **PDO MySQL** para persistência
- **Composer** para autoload
- **Router simples** próprio
- **MySQL 8** em container
- **RabbitMQ**
- **Email mock**: Salva `.eml` em `storage/mails/`
- **Cron**: Executa relatório diário às 09:00

## 🚀 Como Executar

### 1. Setup Inicial
```bash
# Instalar dependências
make setup

# Copiar arquivo de ambiente
cp env.example .env
# Editar .env com suas configurações
```

### 2. Subir Containers
```bash
make up
```

### 3. Executar Migração
```bash
make migrate
```

### 4. Iniciar Worker
```bash
make worker
```

### 5. Testar Endpoints

#### Criar PIX Pendente
```bash
curl -X POST http://localhost:8080/pix \
  -H 'Content-Type: application/json' \
  -d '{
    "external_id": "abc-123",
    "payer_email": "user@example.com",
    "amount": 50.00,
    "type": "NORMAL",
    "origin": "bankA"
  }'
```

#### Aprovar PIX
```bash
curl -X POST http://localhost:8080/pix/1/approve
```

#### Webhook de Expiração
```bash
curl -X POST http://localhost:8080/webhooks/pix-expired \
  -H 'Content-Type: application/json' \
  -d '{
    "external_id": "abc-123",
    "origin": "bankA"
  }'
```

#### Health Check
```bash
curl http://localhost:8080/health
```

## 📊 Monitoramento

### Logs de SLA
O worker monitora o SLA entre aprovação e envio de email:
```
SLA: 1.2 minutos
ALERTA: SLA excedido! (2.5 minutos > 2 minutos)
```

### Relatório Diário
Executado automaticamente às 09:00 (America/Sao_Paulo):
- Gera CSV em `storage/reports/report_YYYY-MM-DD.csv`
- Envia email para CEO com anexo
- Salva `.eml` em `storage/mails/`

## 🔧 Comandos Make

```bash
make setup      # composer install
make up         # docker compose up -d
make down       # docker compose down -v
make migrate    # Executa migração do banco
make worker     # Inicia worker RabbitMQ
make report     # Gera relatório manual
make logs       # Logs dos containers
make shell      # Shell no container app
```

## 📋 Variáveis de Ambiente

```bash
APP_TZ=America/Sao_Paulo
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=pixdb
DB_USERNAME=root
DB_PASSWORD=root
ORIGIN_WHITELIST=bankA,bankB
RABBITMQ_HOST=rabbitmq
RABBITMQ_USER=guest
RABBITMQ_PASS=guest
CEO_EMAIL=ceo@company.test
EMAIL_SLA_MINUTES=2
```

## 🧪 Testes

```bash
composer test
```

## 📝 Endpoints da API

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| `POST` | `/pix` | Criar PIX pendente |
| `POST` | `/pix/{id}/approve` | Aprovar PIX |
| `POST` | `/webhooks/pix-expired` | Webhook de expiração |
| `GET` | `/health` | Health check |

## 🔄 Fluxo de Eventos

1. **Criação**: PIX criado com status `PENDENTE`
2. **Aprovação**: PIX aprovado → evento `pix.approved` → email de confirmação
3. **Expiração**: Webhook → PIX expirado → evento `pix.expired` → notificação de recuperação
4. **Relatório**: Cron diário → CSV + email para CEO

## 🎯 Critérios de Aceite

- ✅ Criar PIX `PENDENTE` e aprovar → `.eml` gerado
- ✅ SLA < 2 min entre aprovação e envio
- ✅ Webhook de expiração → evento publicado
- ✅ Relatório diário às 09:00 → CSV + email
- ✅ Sobe com `make up`, migra com `make migrate`
- ✅ Worker com `make worker`

## 🚨 Tratamento de Erros

- **Validação**: Campos obrigatórios, email válido, valor > 0
- **Origem**: Whitelist configurável
- **Estado**: Validações de transição de status
- **Idempotência**: Webhook sempre retorna 200
- **Logs**: Erros registrados para monitoramento

## 🔒 Segurança

- Validação de origem por whitelist
- Sanitização de inputs
- Prepared statements PDO
- Headers CORS configurados
- Logs de auditoria

---

**Desenvolvido com PHP 8.3, seguindo boas práticas de código limpo e arquitetura MVC.**
