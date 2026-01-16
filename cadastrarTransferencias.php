<?php
    
    include 'config.php';
    include("funcoes.php");

    require_once 'auth.php';
verificarSessao();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        $id_conta_origem = trim($_POST['cadastro_id_conta_origem']);
        $id_conta_destino = trim($_POST['cadastro_id_conta_destino']);
        $valor = trim($_POST['cadastro_valor']);

        try
        {
            $pdo->beginTransaction();

            // Inserir a transferência
            $query = "INSERT INTO transferencias (id_conta_origem, id_conta_destino, valor, data_lancamento) 
                      VALUES (:id_conta_origem, :id_conta_destino, :valor, NOW())";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                ':id_conta_origem' => $id_conta_origem,
                ':id_conta_destino' => $id_conta_destino,
                ':valor' => $valor
            ]);
    
            // Atualizar conta origem
            $contaOrigem = buscarContasFinanceiro($id_conta_origem);
            if (!empty($contaOrigem))
            {
                foreach ($contaOrigem as $resultContaOrigem)
                {
                    $saldoOrigem_apos_lancamento = $resultContaOrigem['saldo'] - $valor;
    
                    $queryConta = "UPDATE contas SET saldo = :saldo, data_atualizacao = NOW() WHERE id = :id";
                    $stmt = $pdo->prepare($queryConta);
                    $stmt->execute([
                        ':saldo' => $saldoOrigem_apos_lancamento,
                        ':id' => $id_conta_origem
                    ]);
                }
            }

            // Atualizar conta destino
            $contaDestino = buscarContasFinanceiro($id_conta_destino);
            if (!empty($contaDestino))
            {
                foreach ($contaDestino as $resultContaDestino)
                {
                    $saldoDestino_apos_lancamento = $resultContaDestino['saldo'] + $valor;

                    $queryConta = "UPDATE contas SET saldo = :saldo, data_atualizacao = NOW() WHERE id = :id";
                    $stmt = $pdo->prepare($queryConta);
                    $stmt->execute([
                        ':saldo' => $saldoDestino_apos_lancamento,
                        ':id' => $id_conta_destino
                    ]);
                }
            }

            $pdo->commit();
            header('Location: index.php?page=ListarTransferencias');
            exit;
        }
        catch (Exception $e)
        {
            $pdo->rollBack();
            die("Erro ao cadastrar lançamento: " . $e->getMessage());
        }
    }
?>
