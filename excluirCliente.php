<?php
    include("config.php");
    include("funcoes.php");
    
    require_once 'auth.php';
verificarSessao();

    // Verificando se o ID foi passado via POST
    if (isset($_POST['id']))
    {
        $idCliente = $_POST['id'];
    
        // Excluindo o produto
        $query = "DELETE FROM clientes WHERE id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->execute([':id' => $idCliente]);
    
        // Registra a auditoria
                registrarAuditoria(
                    $_SESSION['user_id'],
                    'Excluiu um cliente',
                    $_SERVER['REMOTE_ADDR'],
                    ['idCliente' => $_POST['id'], 'username' => $_SESSION['username']]
                );
    
        // Redirecionando ap�s a exclus�o
        header('Location: index.php?page=ListarClientes');
        exit;
    }
?>
