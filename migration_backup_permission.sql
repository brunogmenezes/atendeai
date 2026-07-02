-- Migration: adicionar permissão gerenciar_backup
-- Executar apenas uma vez no banco de dados

INSERT INTO permissoes (nome, descricao)
VALUES (
    'gerenciar_backup',
    'Visualizar, gerar e baixar backups do banco de dados'
)
ON CONFLICT (nome) DO NOTHING;
