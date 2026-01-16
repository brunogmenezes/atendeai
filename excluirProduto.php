<?php
include("config.php");
include("funcoes.php");

require_once 'auth.php';
verificarSessao();

// Verificando se o ID foi passado via POST
if (isset($_POST['id'])) {
    $idProduto = $_POST['id'];

    // Excluindo o produto
    $query = "DELETE FROM produtos WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':id' => $idProduto]);

    // Registra a auditoria
            registrarAuditoria(
                $_SESSION['user_id'],
                'Excluiu um produto',
                $_SERVER['REMOTE_ADDR'],
                ['idProduto' => $_POST['id'], 'username' => $_SESSION['username']]
            );

    // Redirecionando ap�s a exclus�o
    header('Location: index.php?page=ListarProdutos');
    exit;
}
?>
