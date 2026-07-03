-- Migration: adicionar permissões específicas do módulo de backup
-- Executar apenas uma vez no banco de dados

INSERT INTO permissoes (nome, descricao)
VALUES 
    ('visualizar_backup', 'Visualizar backups do sistema'),
    ('gerar_backup', 'Gerar novos backups do banco de dados'),
    ('baixar_backup', 'Baixar arquivos de backup'),
    ('restaurar_backup', 'Restaurar banco de dados a partir de um backup')
ON CONFLICT (nome) DO NOTHING;
