/*
 Navicat Premium Data Transfer

 Source Server         : netsolutions
 Source Server Type    : PostgreSQL
 Source Server Version : 150016 (150016)
 Source Host           : 45.224.128.87:5432
 Source Catalog        : atendeaicopia
 Source Schema         : public

 Target Server Type    : PostgreSQL
 Target Server Version : 150016 (150016)
 File Encoding         : 65001

 Date: 11/05/2026 17:47:56
*/


-- ----------------------------
-- Sequence structure for auditoria_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."auditoria_id_seq";
CREATE SEQUENCE "public"."auditoria_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for clientes_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."clientes_id_seq";
CREATE SEQUENCE "public"."clientes_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for compras_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."compras_id_seq";
CREATE SEQUENCE "public"."compras_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for contas_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."contas_id_seq";
CREATE SEQUENCE "public"."contas_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for empresa_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."empresa_id_seq";
CREATE SEQUENCE "public"."empresa_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for fechamentos_contas_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."fechamentos_contas_id_seq";
CREATE SEQUENCE "public"."fechamentos_contas_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for fechamentos_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."fechamentos_id_seq";
CREATE SEQUENCE "public"."fechamentos_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for financeiro_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."financeiro_id_seq";
CREATE SEQUENCE "public"."financeiro_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for itens_compra_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."itens_compra_id_seq";
CREATE SEQUENCE "public"."itens_compra_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for itens_venda_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."itens_venda_id_seq";
CREATE SEQUENCE "public"."itens_venda_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for mensagens_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."mensagens_id_seq";
CREATE SEQUENCE "public"."mensagens_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for pagamentos_venda_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."pagamentos_venda_id_seq";
CREATE SEQUENCE "public"."pagamentos_venda_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for pix_cobrancas_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."pix_cobrancas_id_seq";
CREATE SEQUENCE "public"."pix_cobrancas_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for produtos_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."produtos_id_seq";
CREATE SEQUENCE "public"."produtos_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for usuarios_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."usuarios_id_seq";
CREATE SEQUENCE "public"."usuarios_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for vendas_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."vendas_id_seq";
CREATE SEQUENCE "public"."vendas_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Table structure for auditoria
-- ----------------------------
DROP TABLE IF EXISTS "public"."auditoria";
CREATE TABLE "public"."auditoria" (
  "id" int4 NOT NULL DEFAULT nextval('auditoria_id_seq'::regclass),
  "usuario_id" int4 DEFAULT 0,
  "acao" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "data_hora" timestamp(6) DEFAULT CURRENT_TIMESTAMP,
  "ip_usuario" varchar(45) COLLATE "pg_catalog"."default",
  "detalhes" jsonb
)
;

-- ----------------------------
-- Table structure for clientes
-- ----------------------------
DROP TABLE IF EXISTS "public"."clientes";
CREATE TABLE "public"."clientes" (
  "id" int4 NOT NULL DEFAULT nextval('clientes_id_seq'::regclass),
  "nome" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "data_nascimento" date,
  "telefone" varchar(20) COLLATE "pg_catalog"."default"
)
;

-- ----------------------------
-- Table structure for colaboradores
-- ----------------------------
DROP TABLE IF EXISTS "public"."colaboradores";
CREATE TABLE "public"."colaboradores" (
  "id" int4 NOT NULL DEFAULT nextval('financeiro_id_seq'::regclass),
  "nome" text COLLATE "pg_catalog"."default",
  "salario" numeric(10,2),
  "data_contratacao" timestamp(6) DEFAULT CURRENT_TIMESTAMP,
  "cpf" int4,
  "idusuario" int2
)
;

-- ----------------------------
-- Table structure for compras
-- ----------------------------
DROP TABLE IF EXISTS "public"."compras";
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
)
;

-- ----------------------------
-- Table structure for contas
-- ----------------------------
DROP TABLE IF EXISTS "public"."contas";
CREATE TABLE "public"."contas" (
  "id" int4 NOT NULL DEFAULT nextval('contas_id_seq'::regclass),
  "nome" varchar(255) COLLATE "pg_catalog"."default",
  "tipo" varchar(255) COLLATE "pg_catalog"."default",
  "saldo" numeric(10,2),
  "data_atualizacao" timestamp(6)
)
;

-- ----------------------------
-- Table structure for despesasfixas
-- ----------------------------
DROP TABLE IF EXISTS "public"."despesasfixas";
CREATE TABLE "public"."despesasfixas" (
  "id" int4 NOT NULL DEFAULT nextval('financeiro_id_seq'::regclass),
  "descricao" text COLLATE "pg_catalog"."default",
  "valor" numeric(10,2),
  "data_lancamento" timestamp(6) DEFAULT CURRENT_TIMESTAMP
)
;

-- ----------------------------
-- Table structure for empresa
-- ----------------------------
DROP TABLE IF EXISTS "public"."empresa";
CREATE TABLE "public"."empresa" (
  "id" int4 NOT NULL DEFAULT nextval('empresa_id_seq'::regclass),
  "nome" varchar(100) COLLATE "pg_catalog"."default" NOT NULL,
  "endereco" text COLLATE "pg_catalog"."default" NOT NULL,
  "cnpj" varchar(18) COLLATE "pg_catalog"."default" NOT NULL,
  "telefone" varchar(15) COLLATE "pg_catalog"."default",
  "email" varchar(100) COLLATE "pg_catalog"."default",
  "logo" text COLLATE "pg_catalog"."default",
  "data_atualizacao" timestamp(6),
  "chave_pix" text COLLATE "pg_catalog"."default"
)
;

-- ----------------------------
-- Table structure for fechamentos
-- ----------------------------
DROP TABLE IF EXISTS "public"."fechamentos";
CREATE TABLE "public"."fechamentos" (
  "id" int4 NOT NULL DEFAULT nextval('fechamentos_id_seq'::regclass),
  "dia_fechamento" date NOT NULL,
  "saldo" numeric(15,2) NOT NULL,
  "usuario" varchar(100) COLLATE "pg_catalog"."default" NOT NULL,
  "created_at" timestamp(6) DEFAULT CURRENT_TIMESTAMP,
  "updated_at" timestamp(6) DEFAULT CURRENT_TIMESTAMP,
  "entrada" numeric(15,2) NOT NULL DEFAULT 0,
  "saida" numeric(15,2) NOT NULL DEFAULT 0
)
;

-- ----------------------------
-- Table structure for fechamentos_contas
-- ----------------------------
DROP TABLE IF EXISTS "public"."fechamentos_contas";
CREATE TABLE "public"."fechamentos_contas" (
  "id" int4 NOT NULL DEFAULT nextval('fechamentos_contas_id_seq'::regclass),
  "id_fechamento" int4 NOT NULL,
  "id_conta" int4 NOT NULL,
  "saldo" numeric(15,2) NOT NULL,
  "created_at" timestamp(6) DEFAULT CURRENT_TIMESTAMP,
  "usuario" varchar(100) COLLATE "pg_catalog"."default"
)
;

-- ----------------------------
-- Table structure for financeiro
-- ----------------------------
DROP TABLE IF EXISTS "public"."financeiro";
CREATE TABLE "public"."financeiro" (
  "id" int4 NOT NULL DEFAULT nextval('financeiro_id_seq'::regclass),
  "tipo" int4,
  "descricao" text COLLATE "pg_catalog"."default",
  "valor" numeric(10,2),
  "data_lancamento" timestamp(6) DEFAULT CURRENT_TIMESTAMP,
  "conta" int4,
  "data_edicao" timestamp(6),
  "data_vencimento" timestamp(6) DEFAULT CURRENT_TIMESTAMP,
  "pago" int2,
  "criado_manual" bool DEFAULT false
)
;

-- ----------------------------
-- Table structure for itens_compra
-- ----------------------------
DROP TABLE IF EXISTS "public"."itens_compra";
CREATE TABLE "public"."itens_compra" (
  "id" int4 NOT NULL DEFAULT nextval('itens_compra_id_seq'::regclass),
  "compra_id" int4,
  "produto_id" int4,
  "quantidade" int4 NOT NULL,
  "preco_custo" numeric(10,2) NOT NULL,
  "subtotal" numeric(10,2) NOT NULL,
  "preco_custo_diluido" numeric(10,2),
  "preco_custo_anterior" numeric(10,4)
)
;

-- ----------------------------
-- Table structure for itens_venda
-- ----------------------------
DROP TABLE IF EXISTS "public"."itens_venda";
CREATE TABLE "public"."itens_venda" (
  "id" int4 NOT NULL DEFAULT nextval('itens_venda_id_seq'::regclass),
  "venda_id" int4,
  "produto_id" int4,
  "quantidade" int4,
  "preco_unitario" numeric(10,2)
)
;

-- ----------------------------
-- Table structure for mensagens
-- ----------------------------
DROP TABLE IF EXISTS "public"."mensagens";
CREATE TABLE "public"."mensagens" (
  "id" int4 NOT NULL DEFAULT nextval('mensagens_id_seq'::regclass),
  "remetente" text COLLATE "pg_catalog"."default",
  "destinatario" text COLLATE "pg_catalog"."default",
  "conteudo" text COLLATE "pg_catalog"."default",
  "data_recebimento" timestamp(6) DEFAULT CURRENT_TIMESTAMP
)
;

-- ----------------------------
-- Table structure for pagamentos_venda
-- ----------------------------
DROP TABLE IF EXISTS "public"."pagamentos_venda";
CREATE TABLE "public"."pagamentos_venda" (
  "id" int4 NOT NULL DEFAULT nextval('pagamentos_venda_id_seq'::regclass),
  "venda_id" int4 NOT NULL,
  "forma_pagamento_id" int4 NOT NULL,
  "valor" numeric(10,2) NOT NULL,
  "data_pagamento" timestamp(6) DEFAULT CURRENT_TIMESTAMP
)
;

-- ----------------------------
-- Table structure for pix_cobrancas
-- ----------------------------
DROP TABLE IF EXISTS "public"."pix_cobrancas";
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
)
;
COMMENT ON COLUMN "public"."pix_cobrancas"."txid" IS 'Identificador único da transação PIX (Transaction ID)';
COMMENT ON COLUMN "public"."pix_cobrancas"."e2eid" IS 'Identificador end-to-end da transação após pagamento';
COMMENT ON COLUMN "public"."pix_cobrancas"."status" IS 'Status da cobrança: ATIVA, CONCLUIDA, REMOVIDA_PELO_USUARIO_RECEBEDOR, REMOVIDA_PELO_PSP';
COMMENT ON COLUMN "public"."pix_cobrancas"."qrcode_payload" IS 'Payload do QR Code PIX (copia e cola)';
COMMENT ON COLUMN "public"."pix_cobrancas"."qrcode_imagem" IS 'Imagem do QR Code em base64';
COMMENT ON COLUMN "public"."pix_cobrancas"."expiracao" IS 'Tempo de expiração da cobrança em segundos (padrão: 3600 = 1 hora)';
COMMENT ON TABLE "public"."pix_cobrancas" IS 'Armazena informações de cobranças PIX geradas via API Banco do Brasil';

-- ----------------------------
-- Table structure for produtos
-- ----------------------------
DROP TABLE IF EXISTS "public"."produtos";
CREATE TABLE "public"."produtos" (
  "id" int4 NOT NULL DEFAULT nextval('produtos_id_seq'::regclass),
  "nome" varchar(100) COLLATE "pg_catalog"."default" NOT NULL,
  "descricao" text COLLATE "pg_catalog"."default",
  "preco_custo" numeric(10,2),
  "quantidade" int4,
  "criado_em" timestamp(6) DEFAULT CURRENT_TIMESTAMP,
  "atualizado_em" timestamp(6) DEFAULT CURRENT_TIMESTAMP,
  "imagem" varchar(255) COLLATE "pg_catalog"."default",
  "preco_venda" numeric(10,2),
  "quantidade_critico" int4,
  "combo" bool DEFAULT false,
  "qtd_itens_combo" int4
)
;

-- ----------------------------
-- Table structure for tipopagamento
-- ----------------------------
DROP TABLE IF EXISTS "public"."tipopagamento";
CREATE TABLE "public"."tipopagamento" (
  "id" int4 NOT NULL DEFAULT nextval('contas_id_seq'::regclass),
  "nome" varchar(255) COLLATE "pg_catalog"."default",
  "conta" int4,
  "data_atualizacao" timestamp(6)
)
;

-- ----------------------------
-- Table structure for transferencias
-- ----------------------------
DROP TABLE IF EXISTS "public"."transferencias";
CREATE TABLE "public"."transferencias" (
  "id" int4 NOT NULL DEFAULT nextval('financeiro_id_seq'::regclass),
  "id_conta_origem" int4,
  "id_conta_destino" int2,
  "valor" numeric(10,2),
  "data_lancamento" timestamp(6) DEFAULT CURRENT_TIMESTAMP,
  "criadopor" int2
)
;

-- ----------------------------
-- Table structure for usuarios
-- ----------------------------
DROP TABLE IF EXISTS "public"."usuarios";
CREATE TABLE "public"."usuarios" (
  "id" int4 NOT NULL DEFAULT nextval('usuarios_id_seq'::regclass),
  "username" varchar(50) COLLATE "pg_catalog"."default" NOT NULL,
  "password" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "nome" varchar(100) COLLATE "pg_catalog"."default",
  "ip_address" varchar(45) COLLATE "pg_catalog"."default",
  "last_login" timestamp(6) DEFAULT now(),
  "email" varchar(255) COLLATE "pg_catalog"."default",
  "criadoem" timestamp(6) DEFAULT CURRENT_TIMESTAMP,
  "isAdmin" bool DEFAULT false
)
;

-- ----------------------------
-- Table structure for vendas
-- ----------------------------
DROP TABLE IF EXISTS "public"."vendas";
CREATE TABLE "public"."vendas" (
  "id" int4 NOT NULL DEFAULT nextval('vendas_id_seq'::regclass),
  "total" numeric(10,2) NOT NULL,
  "data_venda" timestamp(6) DEFAULT CURRENT_TIMESTAMP,
  "tipo_pagamento" int4,
  "vendedor" int4,
  "estornado" bool DEFAULT false,
  "data_estorno" timestamp(6),
  "usuario_estorno" varchar COLLATE "pg_catalog"."default",
  "desconto" numeric(10,2),
  "pix_txid" varchar(35) COLLATE "pg_catalog"."default"
)
;

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."auditoria_id_seq"
OWNED BY "public"."auditoria"."id";
SELECT setval('"public"."auditoria_id_seq"', 2275, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."clientes_id_seq"
OWNED BY "public"."clientes"."id";
SELECT setval('"public"."clientes_id_seq"', 6, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."compras_id_seq"
OWNED BY "public"."compras"."id";
SELECT setval('"public"."compras_id_seq"', 5, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."contas_id_seq"
OWNED BY "public"."contas"."id";
SELECT setval('"public"."contas_id_seq"', 25, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."empresa_id_seq"
OWNED BY "public"."empresa"."id";
SELECT setval('"public"."empresa_id_seq"', 1, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."fechamentos_contas_id_seq"
OWNED BY "public"."fechamentos_contas"."id";
SELECT setval('"public"."fechamentos_contas_id_seq"', 212, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."fechamentos_id_seq"
OWNED BY "public"."fechamentos"."id";
SELECT setval('"public"."fechamentos_id_seq"', 53, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."financeiro_id_seq"
OWNED BY "public"."financeiro"."id";
SELECT setval('"public"."financeiro_id_seq"', 7291, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."itens_compra_id_seq"
OWNED BY "public"."itens_compra"."id";
SELECT setval('"public"."itens_compra_id_seq"', 8, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."itens_venda_id_seq"
OWNED BY "public"."itens_venda"."id";
SELECT setval('"public"."itens_venda_id_seq"', 11478, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."mensagens_id_seq"
OWNED BY "public"."mensagens"."id";
SELECT setval('"public"."mensagens_id_seq"', 624, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."pagamentos_venda_id_seq"
OWNED BY "public"."pagamentos_venda"."id";
SELECT setval('"public"."pagamentos_venda_id_seq"', 6187, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."pix_cobrancas_id_seq"
OWNED BY "public"."pix_cobrancas"."id";
SELECT setval('"public"."pix_cobrancas_id_seq"', 1, false);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."produtos_id_seq"
OWNED BY "public"."produtos"."id";
SELECT setval('"public"."produtos_id_seq"', 80, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."usuarios_id_seq"
OWNED BY "public"."usuarios"."id";
SELECT setval('"public"."usuarios_id_seq"', 17, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."vendas_id_seq"
OWNED BY "public"."vendas"."id";
SELECT setval('"public"."vendas_id_seq"', 6061, true);

-- ----------------------------
-- Primary Key structure for table auditoria
-- ----------------------------
ALTER TABLE "public"."auditoria" ADD CONSTRAINT "auditoria_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Primary Key structure for table clientes
-- ----------------------------
ALTER TABLE "public"."clientes" ADD CONSTRAINT "clientes_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Primary Key structure for table colaboradores
-- ----------------------------
ALTER TABLE "public"."colaboradores" ADD CONSTRAINT "despesasfixas_copy1_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Primary Key structure for table compras
-- ----------------------------
ALTER TABLE "public"."compras" ADD CONSTRAINT "compras_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Primary Key structure for table contas
-- ----------------------------
ALTER TABLE "public"."contas" ADD CONSTRAINT "contas_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Primary Key structure for table despesasfixas
-- ----------------------------
ALTER TABLE "public"."despesasfixas" ADD CONSTRAINT "financeiro_copy1_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Primary Key structure for table empresa
-- ----------------------------
ALTER TABLE "public"."empresa" ADD CONSTRAINT "empresa_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Indexes structure for table fechamentos
-- ----------------------------
CREATE INDEX "idx_fechamentos_dia" ON "public"."fechamentos" USING btree (
  "dia_fechamento" "pg_catalog"."date_ops" ASC NULLS LAST
);

-- ----------------------------
-- Primary Key structure for table fechamentos
-- ----------------------------
ALTER TABLE "public"."fechamentos" ADD CONSTRAINT "fechamentos_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Indexes structure for table fechamentos_contas
-- ----------------------------
CREATE INDEX "idx_fechamentos_contas_conta" ON "public"."fechamentos_contas" USING btree (
  "id_conta" "pg_catalog"."int4_ops" ASC NULLS LAST
);
CREATE INDEX "idx_fechamentos_contas_fechamento" ON "public"."fechamentos_contas" USING btree (
  "id_fechamento" "pg_catalog"."int4_ops" ASC NULLS LAST
);

-- ----------------------------
-- Primary Key structure for table fechamentos_contas
-- ----------------------------
ALTER TABLE "public"."fechamentos_contas" ADD CONSTRAINT "fechamentos_contas_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Primary Key structure for table financeiro
-- ----------------------------
ALTER TABLE "public"."financeiro" ADD CONSTRAINT "financeiro_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Primary Key structure for table itens_compra
-- ----------------------------
ALTER TABLE "public"."itens_compra" ADD CONSTRAINT "itens_compra_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Primary Key structure for table itens_venda
-- ----------------------------
ALTER TABLE "public"."itens_venda" ADD CONSTRAINT "itens_venda_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Primary Key structure for table mensagens
-- ----------------------------
ALTER TABLE "public"."mensagens" ADD CONSTRAINT "mensagens_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Primary Key structure for table pagamentos_venda
-- ----------------------------
ALTER TABLE "public"."pagamentos_venda" ADD CONSTRAINT "pagamentos_venda_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Indexes structure for table pix_cobrancas
-- ----------------------------
CREATE INDEX "idx_pix_status" ON "public"."pix_cobrancas" USING btree (
  "status" COLLATE "pg_catalog"."default" "pg_catalog"."text_ops" ASC NULLS LAST
);
CREATE INDEX "idx_pix_txid" ON "public"."pix_cobrancas" USING btree (
  "txid" COLLATE "pg_catalog"."default" "pg_catalog"."text_ops" ASC NULLS LAST
);
CREATE INDEX "idx_pix_venda_id" ON "public"."pix_cobrancas" USING btree (
  "venda_id" "pg_catalog"."int4_ops" ASC NULLS LAST
);

-- ----------------------------
-- Uniques structure for table pix_cobrancas
-- ----------------------------
ALTER TABLE "public"."pix_cobrancas" ADD CONSTRAINT "pix_cobrancas_txid_key" UNIQUE ("txid");

-- ----------------------------
-- Primary Key structure for table pix_cobrancas
-- ----------------------------
ALTER TABLE "public"."pix_cobrancas" ADD CONSTRAINT "pix_cobrancas_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Primary Key structure for table produtos
-- ----------------------------
ALTER TABLE "public"."produtos" ADD CONSTRAINT "produtos_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Primary Key structure for table tipopagamento
-- ----------------------------
ALTER TABLE "public"."tipopagamento" ADD CONSTRAINT "contas_copy1_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Uniques structure for table usuarios
-- ----------------------------
ALTER TABLE "public"."usuarios" ADD CONSTRAINT "usuarios_username_key" UNIQUE ("username");

-- ----------------------------
-- Primary Key structure for table usuarios
-- ----------------------------
ALTER TABLE "public"."usuarios" ADD CONSTRAINT "usuarios_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Indexes structure for table vendas
-- ----------------------------
CREATE INDEX "idx_vendas_pix_txid" ON "public"."vendas" USING btree (
  "pix_txid" COLLATE "pg_catalog"."default" "pg_catalog"."text_ops" ASC NULLS LAST
);

-- ----------------------------
-- Primary Key structure for table vendas
-- ----------------------------
ALTER TABLE "public"."vendas" ADD CONSTRAINT "vendas_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Foreign Keys structure for table auditoria
-- ----------------------------
ALTER TABLE "public"."auditoria" ADD CONSTRAINT "auditoria_usuario_id_fkey" FOREIGN KEY ("usuario_id") REFERENCES "public"."usuarios" ("id") ON DELETE NO ACTION ON UPDATE NO ACTION;

-- ----------------------------
-- Foreign Keys structure for table fechamentos_contas
-- ----------------------------
ALTER TABLE "public"."fechamentos_contas" ADD CONSTRAINT "fk_fechamentos_contas_conta" FOREIGN KEY ("id_conta") REFERENCES "public"."contas" ("id") ON DELETE CASCADE ON UPDATE NO ACTION;
ALTER TABLE "public"."fechamentos_contas" ADD CONSTRAINT "fk_fechamentos_contas_fechamento" FOREIGN KEY ("id_fechamento") REFERENCES "public"."fechamentos" ("id") ON DELETE CASCADE ON UPDATE NO ACTION;

-- ----------------------------
-- Foreign Keys structure for table itens_compra
-- ----------------------------
ALTER TABLE "public"."itens_compra" ADD CONSTRAINT "itens_compra_compra_id_fkey" FOREIGN KEY ("compra_id") REFERENCES "public"."compras" ("id") ON DELETE CASCADE ON UPDATE NO ACTION;
ALTER TABLE "public"."itens_compra" ADD CONSTRAINT "itens_compra_produto_id_fkey" FOREIGN KEY ("produto_id") REFERENCES "public"."produtos" ("id") ON DELETE NO ACTION ON UPDATE NO ACTION;

-- ----------------------------
-- Foreign Keys structure for table itens_venda
-- ----------------------------
ALTER TABLE "public"."itens_venda" ADD CONSTRAINT "itens_venda_venda_id_fkey" FOREIGN KEY ("venda_id") REFERENCES "public"."vendas" ("id") ON DELETE CASCADE ON UPDATE NO ACTION;

-- ----------------------------
-- Foreign Keys structure for table pagamentos_venda
-- ----------------------------
ALTER TABLE "public"."pagamentos_venda" ADD CONSTRAINT "pagamentos_venda_forma_pagamento_id_fkey" FOREIGN KEY ("forma_pagamento_id") REFERENCES "public"."tipopagamento" ("id") ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE "public"."pagamentos_venda" ADD CONSTRAINT "pagamentos_venda_venda_id_fkey" FOREIGN KEY ("venda_id") REFERENCES "public"."vendas" ("id") ON DELETE NO ACTION ON UPDATE NO ACTION;

-- ----------------------------
-- Foreign Keys structure for table pix_cobrancas
-- ----------------------------
ALTER TABLE "public"."pix_cobrancas" ADD CONSTRAINT "fk_venda" FOREIGN KEY ("venda_id") REFERENCES "public"."vendas" ("id") ON DELETE SET NULL ON UPDATE NO ACTION;

-- ----------------------------
-- Foreign Keys structure for table vendas
-- ----------------------------
ALTER TABLE "public"."vendas" ADD CONSTRAINT "fk_vendas_pix_txid" FOREIGN KEY ("pix_txid") REFERENCES "public"."pix_cobrancas" ("txid") ON DELETE SET NULL ON UPDATE NO ACTION;
