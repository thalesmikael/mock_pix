#!/bin/bash

echo "=== Teste dos Endpoints PIX ===\n"

BASE_URL="http://localhost:8080"

echo "1. Health Check"
curl -s "$BASE_URL/health" | jq .
echo "\n"

echo "2. Criar PIX Pendente"
curl -s -X POST "$BASE_URL/pix" \
  -H 'Content-Type: application/json' \
  -d '{
    "external_id": "abc-123",
    "payer_email": "user@example.com",
    "amount": 50.00,
    "type": "NORMAL",
    "origin": "bankA"
  }' | jq .
echo "\n"

echo "3. Aprovar PIX (ID 1)"
curl -s -X POST "$BASE_URL/pix/1/approve" | jq .
echo "\n"

echo "4. Webhook de Expiração"
curl -s -X POST "$BASE_URL/webhooks/pix-expired" \
  -H 'Content-Type: application/json' \
  -d '{
    "external_id": "abc-123",
    "origin": "bankA"
  }' | jq .
echo "\n"

echo "5. Teste de Validação (Origem Inválida)"
curl -s -X POST "$BASE_URL/pix" \
  -H 'Content-Type: application/json' \
  -d '{
    "external_id": "def-456",
    "payer_email": "user2@example.com",
    "amount": 75.00,
    "type": "RECORRENTE",
    "origin": "bankC"
  }' | jq .
echo "\n"

echo "=== Testes Concluídos ==="
