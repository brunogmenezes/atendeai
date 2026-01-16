<?php
   
    include 'config.php';
    include("funcoes.php");

    require_once 'auth.php';
verificarSessao();
    
    // Verificando se o formulário foi enviado
    if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        // Coletando os dados do formulário
        $descricao = trim($_POST['nome']);
        $tipo = trim($_POST['tipo']);
        $valor = trim($_POST['valor']);
        $conta = trim($_POST['conta']);
        $data_vencimento = trim($_POST['data_vencimento']);
    
        try {
    
            // Inserindo o novo lançamento
            $query = "INSERT INTO financeiro (descricao, tipo, valor, conta, data_lancamento, data_vencimento, criado_manual) 
                      VALUES (:descricao, :tipo, :valor, :conta, NOW(), :data_vencimento, true)";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                ':descricao' => $descricao,
                ':tipo' => $tipo,
                ':valor' => $valor,
                ':conta' => $conta,
                ':data_vencimento' => $data_vencimento
            ]);

            $resultado = buscarContasFinanceiro($conta);
            if (!empty($resultado))
            {
                foreach ($resultado as $contaAtual)
                {
                    $saldo_atual = $contaAtual['saldo'];
                    echo $saldo_atual;
                    echo "<br/>";
                    echo $valor;
                    if ($tipo == 1)
                    {
                        $saldo_apos_lancamento = $saldo_atual + $valor;
                    }
                    else if($tipo == 2)
                    {
                        $saldo_apos_lancamento = $saldo_atual - $valor;
                    }
                    echo "<br/>";
                    echo $saldo_apos_lancamento;

                    // Query de atualização
                    $queryConta = "UPDATE contas SET saldo = :saldo, data_atualizacao = NOW() WHERE id = :id";
                    $stmt = $pdo->prepare($queryConta);
            
                    // Executa a consulta com parâmetros
                    $stmt->execute([
                        ':saldo' => $saldo_apos_lancamento,
                        ':id' => $conta
                    ]);
                }
            }
    
            // Recuperar o ID do lançamento inserido
            //$clienteId = $pdo->lastInsertId();
    
            // Registra a auditoria
            //registrarAuditoria(
            //    $_SESSION['user_id'],
            //    'Cadastrou um lançamento',
            //    $_SERVER['REMOTE_ADDR'],
            //    ['idCliente' => $clienteId]
            //);
    
            // Redirecionando para a página de gerenciamento de clientes
            header('Location: index.php?page=ListarFinanceiro');
            exit;
        } catch (Exception $e) {
            die("Erro ao cadastrar lançamento: " . $e->getMessage());
        }
    }
?>
