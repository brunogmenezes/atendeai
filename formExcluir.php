<?php
    include("config.php");
    include("funcoes.php");
    require_once 'auth.php';
verificarSessao();
    if ($_POST['funcao']=='ExcluirColaborador')
    {
        // Verificando se o ID foi passado via POST
        if (isset($_POST['id']))
        {
            $idForm = $_POST['id'];
            $tabela = $_POST['tabela'];
            $tabela2 = $_POST['tabela2'];
            $paginaPos = $_POST['page'];
        
            // Excluindo o produto
            $query = "DELETE FROM ".$tabela." WHERE id = :id";
            $stmt = $pdo->prepare($query);
            $stmt->execute([':id' => $idForm]);
        
            // Registra a auditoria
                    registrarAuditoria(
                        $_SESSION['user_id'],
                        'Excluiu um cliente',
                        $_SERVER['REMOTE_ADDR'],
                        ['idForm' => $_POST['id'], 'username' => $_SESSION['username']]
                    );
        
            // Redirecionando ap�s a exclus�o
            header('Location: index.php?page='.$paginaPos.'');
            exit;
        }
    }
    else if ($_POST['funcao']=='ExcluirFinanceiro')
    {
        // Verificando se o ID foi passado via POST
        if (isset($_POST['id']))
        {
            $idForm = $_POST['id'];
            $conta  = $_POST['conta'];
            $tipo   = $_POST['tipo'];
            $valor  = $_POST['valor'];
            $tabela = $_POST['tabela'];
            $paginaPos = $_POST['page'];
        
            // Excluindo o produto
            $query = "DELETE FROM ".$tabela." WHERE id = :id";
            $stmt = $pdo->prepare($query);
            $stmt->execute([':id' => $idForm]);

            $resultado = buscarContasFinanceiro($conta);
            if (!empty($resultado))
            {
                $contaAtual = $resultado[0];
                $saldo_atual = $contaAtual['saldo'];

                if ($tipo == 1)
                {
                    $saldo_apos_lancamento = $saldo_atual - $valor;
                }
                else if($tipo == 2)
                {
                    $saldo_apos_lancamento = $saldo_atual + $valor;
                }
                // Query de atualiza��o
                $queryConta = "UPDATE contas SET saldo = :saldo, data_atualizacao = NOW() WHERE id = :id";
                $stmt = $pdo->prepare($queryConta);
        
                // Executa a consulta com par�metros
                $stmt->execute([
                    ':saldo' => $saldo_apos_lancamento,
                    ':id' => $conta
                ]);
            }
        
            // Registra a auditoria
                    registrarAuditoria(
                        $_SESSION['user_id'],
                        'Excluiu um cliente',
                        $_SERVER['REMOTE_ADDR'],
                        ['idForm' => $_POST['id'], 'username' => $_SESSION['username']]
                    );
        
            // Redirecionando ap�s a exclus�o
            header('Location: index.php?page='.$paginaPos.'');
            exit;
        }
    }
    else if ($_POST['funcao']=='ExcluirTransferencia')
    {
        if (isset($_POST['id']))
        {
            $id_transferencia = trim($_POST['id']);
            $id_conta_origem = trim($_POST['id_conta_origem']);
            $id_conta_destino = trim($_POST['id_conta_destino']);
            $valor = trim($_POST['valor']);
            $tabela = trim($_POST['tabela']);
            $paginaPos = trim($_POST['page']);
    
            try
            {
                $pdo->beginTransaction();
    
                // Excluindo o produto
                $query = "DELETE FROM ".$tabela." WHERE id = :id";
                $stmt = $pdo->prepare($query);
                $stmt->execute([':id' => $id_transferencia]);
        
                // Atualizar conta origem
                $contaOrigem = buscarContasFinanceiro($id_conta_origem);
                if (!empty($contaOrigem))
                {
                    foreach ($contaOrigem as $resultContaOrigem)
                    {
                        $saldoOrigem_apos_lancamento = $resultContaOrigem['saldo'] + $valor;
        
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
                        $saldoDestino_apos_lancamento = $resultContaDestino['saldo'] - $valor;
    
                        $queryConta = "UPDATE contas SET saldo = :saldo, data_atualizacao = NOW() WHERE id = :id";
                        $stmt = $pdo->prepare($queryConta);
                        $stmt->execute([
                            ':saldo' => $saldoDestino_apos_lancamento,
                            ':id' => $id_conta_destino
                        ]);
                    }
                }
    
                $pdo->commit();
                header('Location: index.php?page='.$paginaPos.'');
                exit;
            }
            catch (Exception $e)
            {
                $pdo->rollBack();
                die("Erro ao cadastrar lan�amento: " . $e->getMessage());
            }
        }
    }
    else if ($_POST['funcao']=='EstornarVenda')
    {
        if (isset($_POST['id']))
        {
            try
            {
                $pdo->beginTransaction();

                $idVenda = $_POST['id'];
                $usuario = $_SESSION['username'];
                $userId = $_SESSION['user_id'];

                // 1. Obter dados da venda
                $sqlGetVenda = "SELECT
                    v.id,
                    v.total AS valor_total,
                    v.estornado,
                    tp.conta,
                    tp.nome AS nome_pagamento,
                    pv.forma_pagamento_id,
                    pv.valor
                FROM
                    vendas v
                LEFT JOIN
                    pagamentos_venda pv ON pv.venda_id = v.id
                LEFT JOIN
                    tipopagamento tp ON tp.id = pv.forma_pagamento_id
                WHERE
                    v.id = :id_venda;
                ";
                $stmt = $pdo->prepare($sqlGetVenda);
                $stmt->bindParam(':id_venda', $idVenda, PDO::PARAM_INT);
                $stmt->execute();
                $venda = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$venda)
                {
                    throw new Exception("Venda não encontrada!");
                }

                // 2. Verificar se já está estornada
                if ($venda['estornado'])
                {
                    throw new Exception("Esta venda já foi estornada anteriormente!");
                }
                
                // 3. Atualizar a venda como estornada
                $sqlUpdateVenda = "UPDATE vendas 
                SET estornado = true, 
                data_estorno = NOW(), 
                usuario_estorno = :usuario
                WHERE id = :id_venda";
                
                $stmt = $pdo->prepare($sqlUpdateVenda);
                $stmt->execute([
                    ':id_venda' => $idVenda,
                    ':usuario' => $usuario
                ]);
                
                // 4. Atualizar o saldo das contas (reverter os valores individuais dos pagamentos)
$sqlPagamentos = "SELECT pv.valor, tp.conta, tp.nome 
FROM pagamentos_venda pv
JOIN tipopagamento tp ON tp.id = pv.forma_pagamento_id
WHERE pv.venda_id = :venda_id";
$stmt = $pdo->prepare($sqlPagamentos);
$stmt->bindParam(':venda_id', $idVenda, PDO::PARAM_INT);
$stmt->execute();
$pagamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($pagamentos as $pagamento) {
// Estorna o valor para a conta correspondente
$sqlUpdateConta = "UPDATE contas 
     SET saldo = saldo - :valor, 
         data_atualizacao = NOW() 
     WHERE id = :conta";
$stmtUpdate = $pdo->prepare($sqlUpdateConta);
$stmtUpdate->execute([
':valor' => $pagamento['valor'],
':conta' => $pagamento['conta']
]);

// Insere no financeiro o estorno de cada parte
$sqlInsertFinanceiro = "INSERT INTO financeiro 
          (tipo, descricao, valor, data_lancamento, conta)
          VALUES 
          (3, :descricao, :valor, NOW(), :conta)";
$stmtFinanceiro = $pdo->prepare($sqlInsertFinanceiro);
$stmtFinanceiro->execute([
':descricao' => 'Estorno - Venda #' . $idVenda . ' (' . $pagamento['nome'] . ')',
':valor' => $pagamento['valor'],
':conta' => $pagamento['conta']
]);
}


                // 6. Restaurar estoque dos produtos
                $sqlItensVenda = "SELECT produto_id, quantidade FROM itens_venda WHERE venda_id = :venda_id";
                $stmt = $pdo->prepare($sqlItensVenda);
                $stmt->bindParam(':venda_id', $idVenda, PDO::PARAM_INT);
                $stmt->execute();
                $itens = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($itens as $item)
                {
                    $sqlUpdateEstoque = "UPDATE produtos 
                    SET quantidade = quantidade + :quantidade 
                    WHERE id = :produto_id";
                    
                    $stmt = $pdo->prepare($sqlUpdateEstoque);
                    $stmt->execute([
                        ':quantidade' => $item['quantidade'],
                        ':produto_id' => $item['produto_id']
                    ]);
                }

                // 7. Commit final
                $pdo->commit();

                //// 8. Registrar auditoria
                //registrarAuditoria(
                //    $userId,
                //    'Estornou uma venda',
                //    $_SERVER['REMOTE_ADDR'],
                //    [
                //        'venda_id' => $idVenda,
                //        'valor' => $venda['total'],
                //        'pagamento' => $venda['nome_pagamento']
                //    ]
                //);

                $_SESSION['sucesso'] = "Venda estornada com sucesso!";
                $dia = date('d');
                $mes = date('m');
                $ano = date('Y');
                header("Location: index.php?page=ListarVendas&dia=$dia&mes=$mes&ano=$ano&status=sucesso");
                exit;
            }
            catch (Exception $e)
            {
                $pdo->rollBack();
                $_SESSION['erro'] = "Erro ao estornar venda: " . $e->getMessage();
                $dia = date('d');
                $mes = date('m');
                $ano = date('Y');
                header("Location: index.php?page=ListarVendas&dia=$dia&mes=$mes&ano=$ano&status=erro");
                exit;
            }
        }
    }
    
?>
