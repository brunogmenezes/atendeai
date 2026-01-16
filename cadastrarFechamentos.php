<?php
include 'config.php';
include("funcoes.php");
require_once 'auth.php';
verificarSessao();

// Verificando se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    // Coletando os dados do formulário
    $dia_fechamento = date('Y-m-d');
    $saldo = trim($_POST['saldo_total']);
    $usuario = $_SESSION['username'];
    $valor_entrada = trim($_POST['valor_entrada']);
    $valor_saida = trim($_POST['valor_saida']);
    
    // Arrays com os dados das contas
    $ids_contas = $_POST['ids_contas'] ?? [];
    $saldos_contas = $_POST['saldos_contas'] ?? [];

    try {
        // VERIFICAR SE JÁ EXISTE FECHAMENTO DO DIA
        if (existeFechamentoDoDia($dia_fechamento)) {
            $_SESSION['erro'] = "Já existe um fechamento cadastrado para a data de " . date('d/m/Y');
            header('Location: index.php?page=ListarFechamentos');
            exit;
        }

        // Iniciar transação para garantir consistência dos dados
        $pdo->beginTransaction();

        // 1. Inserindo o fechamento principal
        $query = "INSERT INTO fechamentos (dia_fechamento, saldo, usuario, created_at, updated_at, entrada, saida) 
                  VALUES (:dia_fechamento, :saldo, :usuario, NOW(), NOW(), :entrada, :saida)";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':dia_fechamento' => $dia_fechamento,
            ':saldo' => $saldo,
            ':usuario' => $usuario,
            ':entrada' => $valor_entrada,
            ':saida' => $valor_saida
        ]);

        // Recuperar o ID do fechamento inserido
        $fechamento_id = $pdo->lastInsertId();

        // 2. Inserir os dados de cada conta na tabela fechamentos_contas
        if (!empty($ids_contas) && count($ids_contas) === count($saldos_contas)) {
            $query_conta = "INSERT INTO fechamentos_contas (id_fechamento, id_conta, saldo, usuario, created_at) 
                           VALUES (:id_fechamento, :id_conta, :saldo, :usuario, NOW())";
            $stmt_conta = $pdo->prepare($query_conta);
            
            for ($i = 0; $i < count($ids_contas); $i++) {
                $stmt_conta->execute([
                    ':id_fechamento' => $fechamento_id,
                    ':id_conta' => $ids_contas[$i],
                    ':saldo' => $saldos_contas[$i],
                    ':usuario' => $usuario
                ]);
            }
        }

        // Confirmar transação
        $pdo->commit();

        $_SESSION['sucesso'] = "Fechamento cadastrado com sucesso!";

        // Redirecionando para a página de gerenciamento de fechamentos
        header('Location: index.php?page=ListarFechamentos');
        exit;
        
    } catch (Exception $e) {
        // Em caso de erro, desfazer transação
        $pdo->rollBack();
        $_SESSION['erro'] = "Erro ao cadastrar fechamento: " . $e->getMessage();
        header('Location: index.php?page=ListarFechamentos');
        exit;
    }
}
?>