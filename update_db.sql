-- ----------------------------
-- Drop existing tables and sequences safely with CASCADE
-- ----------------------------
DROP TABLE IF EXISTS "public"."itens_compra" CASCADE;
DROP TABLE IF EXISTS "public"."pagamentos_compra" CASCADE;
DROP TABLE IF EXISTS "public"."compras" CASCADE;
DROP TABLE IF EXISTS "public"."pix_cobrancas" CASCADE;

DROP SEQUENCE IF EXISTS "public"."compras_id_seq" CASCADE;
DROP SEQUENCE IF EXISTS "public"."itens_compra_id_seq" CASCADE;
DROP SEQUENCE IF EXISTS "public"."pagamentos_compra_id_seq" CASCADE;
DROP SEQUENCE IF EXISTS "public"."pix_cobrancas_id_seq" CASCADE;

-- ----------------------------
-- Sequence structure for compras_id_seq
-- ----------------------------
CREATE SEQUENCE "public"."compras_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for itens_compra_id_seq
-- ----------------------------
CREATE SEQUENCE "public"."itens_compra_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for pagamentos_compra_id_seq
-- ----------------------------
CREATE SEQUENCE "public"."pagamentos_compra_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for pix_cobrancas_id_seq
-- ----------------------------
CREATE SEQUENCE "public"."pix_cobrancas_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Table structure for compras
-- ----------------------------
CREATE TABLE "public"."compras" (
  "id" int4 NOT NULL DEFAULT nextval('compras_id_seq'::regclass),
  "fornecedor" varchar(255) COLLATE "pg_catalog"."default",
  "data_compra" timestamp(6) DEFAULT CURRENT_TIMESTAMP,
  "total" numeric(10,2) NOT NULL,
  "usuario_id" int4,
  "created_at" timestamp(6) DEFAULT CURRENT_TIMESTAMP,
  "frete" numeric(10,2) DEFAULT 0,
  "outras_despesas" numeric(10,2) DEFAULT 0,
  "conta_id" int4
);

-- ----------------------------
-- Table structure for itens_compra
-- ----------------------------
CREATE TABLE "public"."itens_compra" (
  "id" int4 NOT NULL DEFAULT nextval('itens_compra_id_seq'::regclass),
  "compra_id" int4,
  "produto_id" int4,
  "quantidade" int4 NOT NULL,
  "preco_custo" numeric(10,2) NOT NULL,
  "subtotal" numeric(10,2) NOT NULL,
  "preco_custo_diluido" numeric(10,2),
  "preco_custo_anterior" numeric(10,4)
);

-- ----------------------------
-- Table structure for pagamentos_compra
-- ----------------------------
CREATE TABLE "public"."pagamentos_compra" (
  "id" int4 NOT NULL DEFAULT nextval('pagamentos_compra_id_seq'::regclass),
  "compra_id" int4 NOT NULL,
  "conta_id" int4 NOT NULL,
  "valor" numeric(10,2) NOT NULL,
  "data_pagamento" timestamp(6) DEFAULT CURRENT_TIMESTAMP
);

-- ----------------------------
-- Table structure for pix_cobrancas
-- ----------------------------
CREATE TABLE "public"."pix_cobrancas" (
  "id" int4 NOT NULL DEFAULT nextval('pix_cobrancas_id_seq'::regclass),
  "txid" varchar(35) COLLATE "pg_catalog"."default" NOT NULL,
  "e2eid" varchar(32) COLLATE "pg_catalog"."default",
  "venda_id" int4,
  "valor" numeric(10,2) NOT NULL,
  "chave_pix" varchar(77) COLLATE "pg_catalog"."default" NOT NULL,
  "devedor_cpf" varchar(14) COLLATE "pg_catalog"."default",
  "devedor_nome" varchar(200) COLLATE "pg_catalog"."default",
  "status" varchar(20) COLLATE "pg_catalog"."default" NOT NULL DEFAULT 'ATIVA'::character varying,
  "qrcode_payload" text COLLATE "pg_catalog"."default",
  "qrcode_imagem" text COLLATE "pg_catalog"."default",
  "location" varchar(255) COLLATE "pg_catalog"."default",
  "criado_em" timestamp(6) DEFAULT CURRENT_TIMESTAMP,
  "atualizado_em" timestamp(6) DEFAULT CURRENT_TIMESTAMP,
  "pago_em" timestamp(6),
  "expiracao" int4 DEFAULT 3600
);

COMMENT ON COLUMN "public"."pix_cobrancas"."txid" IS 'Identificador único da transação PIX (Transaction ID)';
COMMENT ON COLUMN "public"."pix_cobrancas"."e2eid" IS 'Identificador end-to-end da transação após pagamento';
COMMENT ON COLUMN "public"."pix_cobrancas"."status" IS 'Status da cobrança: ATIVA, CONCLUIDA, REMOVIDA_PELO_USUARIO_RECEBEDOR, REMOVIDA_PELO_PSP';
COMMENT ON COLUMN "public"."pix_cobrancas"."qrcode_payload" IS 'Payload do QR Code PIX (copia e cola)';
COMMENT ON COLUMN "public"."pix_cobrancas"."qrcode_imagem" IS 'Imagem do QR Code em base64';
COMMENT ON COLUMN "public"."pix_cobrancas"."expiracao" IS 'Tempo de expiração da cobrança em segundos (padrão: 3600 = 1 hora)';
COMMENT ON TABLE "public"."pix_cobrancas" IS 'Armazena informações de cobranças PIX geradas via API Banco do Brasil';

-- ----------------------------
-- Primary Key structure for tables
-- ----------------------------
ALTER TABLE "public"."compras" ADD CONSTRAINT "compras_pkey" PRIMARY KEY ("id");
ALTER TABLE "public"."itens_compra" ADD CONSTRAINT "itens_compra_pkey" PRIMARY KEY ("id");
ALTER TABLE "public"."pagamentos_compra" ADD CONSTRAINT "pagamentos_compra_pkey" PRIMARY KEY ("id");
ALTER TABLE "public"."pix_cobrancas" ADD CONSTRAINT "pix_cobrancas_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Alter existing tables for new columns
-- ----------------------------
ALTER TABLE "public"."produtos" ADD COLUMN IF NOT EXISTS "preco_custo_diluido" numeric(10,2) DEFAULT 0;
ALTER TABLE "public"."vendas" ADD COLUMN IF NOT EXISTS "pix_txid" varchar(35) COLLATE "pg_catalog"."default";

-- ----------------------------
-- Foreign Key and Unique constraints
-- ----------------------------
ALTER TABLE "public"."itens_compra" ADD CONSTRAINT "itens_compra_compra_id_fkey" FOREIGN KEY ("compra_id") REFERENCES "public"."compras" ("id") ON DELETE CASCADE ON UPDATE NO ACTION;
ALTER TABLE "public"."itens_compra" ADD CONSTRAINT "itens_compra_produto_id_fkey" FOREIGN KEY ("produto_id") REFERENCES "public"."produtos" ("id") ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE "public"."pagamentos_compra" ADD CONSTRAINT "fk_compra" FOREIGN KEY ("compra_id") REFERENCES "public"."compras" ("id") ON DELETE CASCADE ON UPDATE NO ACTION;
ALTER TABLE "public"."pagamentos_compra" ADD CONSTRAINT "fk_conta" FOREIGN KEY ("conta_id") REFERENCES "public"."contas" ("id") ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE "public"."pix_cobrancas" ADD CONSTRAINT "pix_cobrancas_txid_key" UNIQUE ("txid");
ALTER TABLE "public"."pix_cobrancas" ADD CONSTRAINT "fk_venda" FOREIGN KEY ("venda_id") REFERENCES "public"."vendas" ("id") ON DELETE SET NULL ON UPDATE NO ACTION;