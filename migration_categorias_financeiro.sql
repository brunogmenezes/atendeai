-- ====================================================
-- Migration: Categorias de Lançamento Financeiro
-- ====================================================

-- Criar tabela de categorias
CREATE TABLE IF NOT EXISTS "public"."categorias_financeiro" (
  "id" SERIAL PRIMARY KEY,
  "nome" text NOT NULL,
  "tipo" int4 NOT NULL,
  "ativo" bool DEFAULT true,
  "criado_em" timestamp DEFAULT CURRENT_TIMESTAMP
);

-- Categorias padrão de Entrada (tipo = 1)
INSERT INTO categorias_financeiro (nome, tipo)
SELECT nome, tipo FROM (VALUES
  ('Venda de Produto', 1),
  ('Venda de Serviço', 1),
  ('Recebimento de Cliente', 1),
  ('Transferência Recebida', 1),
  ('Outros Recebimentos', 1)
) AS v(nome, tipo)
WHERE NOT EXISTS (SELECT 1 FROM categorias_financeiro WHERE tipo = 1 LIMIT 1);

-- Categorias padrão de Saída (tipo = 2)
INSERT INTO categorias_financeiro (nome, tipo)
SELECT nome, tipo FROM (VALUES
  ('Fornecedor', 2),
  ('Salários', 2),
  ('Aluguel', 2),
  ('Despesas Fixas', 2),
  ('Impostos', 2),
  ('Compra de Estoque', 2),
  ('Outras Despesas', 2)
) AS v(nome, tipo)
WHERE NOT EXISTS (SELECT 1 FROM categorias_financeiro WHERE tipo = 2 LIMIT 1);

-- Adicionar coluna categoria_id na tabela financeiro (se não existir)
ALTER TABLE financeiro ADD COLUMN IF NOT EXISTS categoria_id int4 REFERENCES categorias_financeiro(id);
