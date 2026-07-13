<?php
include 'config.php';
include 'funcoes.php';
require_once 'auth.php';
verificarSessao();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id           = (int)$_POST['id'];
    $descricao    = trim($_POST['nome']);
    $tipo         = trim($_POST['tipo']);
    $valor_novo   = (float)trim($_POST['valor']);
    $conta_nova   = (int)trim($_POST['conta']);
    $data_venc    = trim($_POST['data_vencimento']);
    $categoria_id = !empty($_POST['categoria_id']) ? (int)$_POST['categoria_id'] : null;

    try {
        // Buscar o lançamento atual para ajuste de saldo
        $lancamento = buscarFinanceiroPorId($id);
        if (!$lancamento) {
            die("Lançamento não encontrado.");
        }

        $conta_antiga  = (int)$lancamento['conta'];
        $valor_antigo  = (float)$lancamento['valor'];
        $tipo_antigo   = (int)$lancamento['tipo'];

        // Reverter efeito do lançamento antigo na conta antiga
        $stmt = $pdo->prepare("SELECT saldo FROM contas WHERE id = :id");
        $stmt->execute([':id' => $conta_antiga]);
        $saldo_conta_antiga = (float)($stmt->fetchColumn() ?? 0);

        if ($tipo_antigo == 1) {
            $saldo_conta_antiga -= $valor_antigo; // desfaz entrada
        } elseif ($tipo_antigo == 2) {
            $saldo_conta_antiga += $valor_antigo; // desfaz saída
        }

        $pdo->prepare("UPDATE contas SET saldo = :saldo, data_atualizacao = NOW() WHERE id = :id")
            ->execute([':saldo' => $saldo_conta_antiga, ':id' => $conta_antiga]);

        // Aplicar novo lançamento na nova conta
        $stmt2 = $pdo->prepare("SELECT saldo FROM contas WHERE id = :id");
        $stmt2->execute([':id' => $conta_nova]);
        $saldo_conta_nova = (float)($stmt2->fetchColumn() ?? 0);

        if ($tipo == 1) {
            $saldo_conta_nova += $valor_novo;
        } elseif ($tipo == 2) {
            $saldo_conta_nova -= $valor_novo;
        }

        $pdo->prepare("UPDATE contas SET saldo = :saldo, data_atualizacao = NOW() WHERE id = :id")
            ->execute([':saldo' => $saldo_conta_nova, ':id' => $conta_nova]);

        // Atualizar o lançamento
        $upd = $pdo->prepare("UPDATE financeiro SET
            descricao     = :descricao,
            tipo          = :tipo,
            valor         = :valor,
            conta         = :conta,
            data_vencimento = :data_vencimento,
            categoria_id  = :categoria_id,
            data_edicao   = NOW()
            WHERE id = :id");
        $upd->execute([
            ':descricao'      => $descricao,
            ':tipo'           => $tipo,
            ':valor'          => $valor_novo,
            ':conta'          => $conta_nova,
            ':data_vencimento'=> $data_venc,
            ':categoria_id'   => $categoria_id,
            ':id'             => $id,
        ]);

        header('Location: index.php?page=ListarFinanceiro');
        exit;

    } catch (Exception $e) {
        die("Erro ao editar lançamento: " . $e->getMessage());
    }
}
?>
