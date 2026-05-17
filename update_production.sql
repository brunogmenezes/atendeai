DROP SEQUENCE IF EXISTS "public"."compras_id_seq";

CREATE SEQUENCE "public"."compras_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

DROP SEQUENCE IF EXISTS "public"."itens_compra_id_seq";

CREATE SEQUENCE "public"."itens_compra_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

DROP SEQUENCE IF EXISTS "public"."pagamentos_compra_id_seq";

CREATE SEQUENCE "public"."pagamentos_compra_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

DROP SEQUENCE IF EXISTS "public"."pix_cobrancas_id_seq";

CREATE SEQUENCE "public"."pix_cobrancas_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

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
-- Table structure for pagamentos_compra
-- ----------------------------
DROP TABLE IF EXISTS "public"."pagamentos_compra";
CREATE TABLE "public"."pagamentos_compra" (
  "id" int4 NOT NULL DEFAULT nextval('pagamentos_compra_id_seq'::regclass),
  "compra_id" int4 NOT NULL,
  "conta_id" int4 NOT NULL,
  "valor" numeric(10,2) NOT NULL,
  "data_pagamento" timestamp(6) DEFAULT CURRENT_TIMESTAMP
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
  "qtd_itens_combo" int4,
  "preco_custo_diluido" numeric(10,2) DEFAULT 0
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
SELECT setval('"public"."auditoria_id_seq"', 2284, true);

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
-- Table structure for pagamentos_compra
-- ----------------------------
DROP TABLE IF EXISTS "public"."pagamentos_compra";
CREATE TABLE "public"."pagamentos_compra" (
  "id" int4 NOT NULL DEFAULT nextval('pagamentos_compra_id_seq'::regclass),
  "compra_id" int4 NOT NULL,
  "conta_id" int4 NOT NULL,
  "valor" numeric(10,2) NOT NULL,
  "data_pagamento" timestamp(6) DEFAULT CURRENT_TIMESTAMP
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
  "qtd_itens_combo" int4,
  "preco_custo_diluido" numeric(10,2) DEFAULT 0
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
SELECT setval('"public"."auditoria_id_seq"', 2284, true);

DROP TABLE IF EXISTS "public"."pagamentos_compra";

CREATE TABLE "public"."pagamentos_compra" (
  "id" int4 NOT NULL DEFAULT nextval('pagamentos_compra_id_seq'::regclass),
  "compra_id" int4 NOT NULL,
  "conta_id" int4 NOT NULL,
  "valor" numeric(10,2) NOT NULL,
  "data_pagamento" timestamp(6) DEFAULT CURRENT_TIMESTAMP
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
  "qtd_itens_combo" int4,
  "preco_custo_diluido" numeric(10,2) DEFAULT 0
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
SELECT setval('"public"."auditoria_id_seq"', 2284, true);

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
  "qtd_itens_combo" int4,
  "preco_custo_diluido" numeric(10,2) DEFAULT 0
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
SELECT setval('"public"."auditoria_id_seq"', 2284, true);

ALTER TABLE "public"."compras" ADD CONSTRAINT "compras_pkey" PRIMARY KEY ("id");

ALTER TABLE "public"."itens_compra" ADD CONSTRAINT "itens_compra_pkey" PRIMARY KEY ("id");

ALTER TABLE "public"."itens_compra" ADD CONSTRAINT "itens_compra_compra_id_fkey" FOREIGN KEY ("compra_id") REFERENCES "public"."compras" ("id") ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE "public"."itens_compra" ADD CONSTRAINT "itens_compra_produto_id_fkey" FOREIGN KEY ("produto_id") REFERENCES "public"."produtos" ("id") ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE "public"."pagamentos_compra" ADD CONSTRAINT "pagamentos_compra_pkey" PRIMARY KEY ("id");

ALTER TABLE "public"."pagamentos_compra" ADD CONSTRAINT "fk_compra" FOREIGN KEY ("compra_id") REFERENCES "public"."compras" ("id") ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE "public"."pagamentos_compra" ADD CONSTRAINT "fk_conta" FOREIGN KEY ("conta_id") REFERENCES "public"."contas" ("id") ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE "public"."pix_cobrancas" ADD CONSTRAINT "pix_cobrancas_txid_key" UNIQUE ("txid");

ALTER TABLE "public"."pix_cobrancas" ADD CONSTRAINT "pix_cobrancas_pkey" PRIMARY KEY ("id");

ALTER TABLE "public"."pix_cobrancas" ADD CONSTRAINT "fk_venda" FOREIGN KEY ("venda_id") REFERENCES "public"."vendas" ("id") ON DELETE SET NULL ON UPDATE NO ACTION;

ALTER TABLE "public"."produtos" ADD COLUMN IF NOT EXISTS "preco_custo_diluido" numeric(10,2) DEFAULT 0;

ALTER TABLE "public"."vendas" ADD COLUMN IF NOT EXISTS "pix_txid" varchar(35) COLLATE "pg_catalog"."default";