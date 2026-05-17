<?php
include("config.php");
include("funcoes.php");
require_once 'auth.php';
verificarSessao();

if (!temPermissao('gerenciar_sistema')) {
    die("Acesso negado.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = !empty($_POST['id']) ? intval($_POST['id']) : null;
    $nome = !empty($_POST['nome']) ? trim($_POST['nome']) : '';
    $action = $_POST['action'] ?? '';

    // Ação de Exclusão
    if ($action === 'excluir' && $id) {
        try {
            $pdo->beginTransaction();

            // 1. Excluir todas as amarrações de permissões do perfil
            $stmtDelPerms = $pdo->prepare("DELETE FROM perfil_permissoes WHERE perfil_id = :perfil_id");
            $stmtDelPerms->execute([':perfil_id' => $id]);

            // 2. Definir perfil_id como NULL na tabela de usuarios para evitar violação de integridade
            $stmtNullUsers = $pdo->prepare("UPDATE usuarios SET perfil_id = NULL WHERE perfil_id = :perfil_id");
            $stmtNullUsers->execute([':perfil_id' => $id]);

            // 3. Excluir o perfil em si
            $stmtDelPerfil = $pdo->prepare("DELETE FROM perfis WHERE id = :id");
            $stmtDelPerfil->execute([':id' => $id]);

            $pdo->commit();

            // Invalida cache de sessões locais caso o próprio usuário seja afetado
            if (isset($_SESSION['user_id'])) {
                $stmtCheckSelf = $pdo->prepare("SELECT perfil_id FROM usuarios WHERE id = :id");
                $stmtCheckSelf->execute([':id' => $_SESSION['user_id']]);
                $selfUser = $stmtCheckSelf->fetch(PDO::FETCH_ASSOC);
                if (!$selfUser || $selfUser['perfil_id'] === null) {
                    unset($_SESSION['permissoes']);
                    unset($_SESSION['is_admin']);
                }
            }

            $_SESSION['success_message'] = "Perfil excluído com sucesso!";
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $_SESSION['error_message'] = "Erro ao excluir perfil: " . $e->getMessage();
        }

        header("Location: index.php?page=ListarPerfis");
        exit;
    }

    // Ação de Cadastro ou Edição
    if (!empty($nome)) {
        $permissoesSelecionadas = $_POST['permissoes'] ?? [];

        try {
            $pdo->beginTransaction();

            if ($id) {
                // Modo Edição:
                // 1. Atualiza o nome do perfil
                $stmtUpProfile = $pdo->prepare("UPDATE perfis SET nome = :nome WHERE id = :id");
                $stmtUpProfile->execute([
                    ':nome' => $nome,
                    ':id' => $id
                ]);

                // 2. Limpa as antigas amarrações de permissões
                $stmtDelPerms = $pdo->prepare("DELETE FROM perfil_permissoes WHERE perfil_id = :perfil_id");
                $stmtDelPerms->execute([':perfil_id' => $id]);

                $perfilId = $id;
            } else {
                // Modo Cadastro:
                // 1. Insere o novo perfil
                $stmtInsProfile = $pdo->prepare("INSERT INTO perfis (nome) VALUES (:nome)");
                $stmtInsProfile->execute([':nome' => $nome]);
                $perfilId = $pdo->lastInsertId();
            }

            // 3. Insere as novas permissões amarradas
            if ($perfilId && !empty($permissoesSelecionadas)) {
                $stmtInsMapping = $pdo->prepare("
                    INSERT INTO perfil_permissoes (perfil_id, permissao_id) 
                    VALUES (:perfil_id, :permissao_id)
                ");
                foreach ($permissoesSelecionadas as $permId) {
                    $stmtInsMapping->execute([
                        ':perfil_id' => $perfilId,
                        ':permissao_id' => intval($permId)
                    ]);
                }
            }

            $pdo->commit();

            // Invalida cache de permissões do próprio usuário logado caso ele pertença a este perfil
            if (isset($_SESSION['user_id'])) {
                $stmtCheckSelf = $pdo->prepare("SELECT perfil_id FROM usuarios WHERE id = :id");
                $stmtCheckSelf->execute([':id' => $_SESSION['user_id']]);
                $selfUser = $stmtCheckSelf->fetch(PDO::FETCH_ASSOC);
                if ($selfUser && $selfUser['perfil_id'] == $perfilId) {
                    unset($_SESSION['permissoes']);
                    unset($_SESSION['is_admin']);
                }
            }

            $_SESSION['success_message'] = "Perfil salvo com sucesso!";
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $_SESSION['error_message'] = "Erro ao salvar perfil: " . $e->getMessage();
        }

        header("Location: index.php?page=ListarPerfis");
        exit;
    }
}

// Fallback caso acesse diretamente
header("Location: index.php?page=ListarPerfis");
exit;
?>
