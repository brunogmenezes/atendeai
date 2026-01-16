<?php
include("config.php");
require_once 'auth.php';
verificarSessao();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $imagem = $_FILES['imagem'];

    // Verificar se a imagem foi enviada corretamente
    if ($imagem['error'] === 0) {
        $extensao = pathinfo($imagem['name'], PATHINFO_EXTENSION);
        $nomeArquivo = "produto_$id." . $extensao;
        $diretorio = "uploads/";

        // Mover o arquivo para o diret�rio
        if (!is_dir($diretorio)) {
            mkdir($diretorio, 0755, true);
        }

        $caminho = $diretorio . $nomeArquivo;

        if (move_uploaded_file($imagem['tmp_name'], $caminho)) {
            // Atualizar o caminho da imagem no banco de dados
            $sql = "UPDATE produtos SET imagem = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$nomeArquivo, $id])) {
                echo "Imagem atualizada com sucesso.";
                // Redirecionando para a p�gina de gerenciamento de produtos
            header('Location: index.php?page=Produtos');
            exit;
            } else {
                echo "Erro ao atualizar imagem no banco.";
                // Redirecionando para a p�gina de gerenciamento de produtos
            header('Location: index.php?page=Produtos');
            exit;
            }
        } else {
            echo "Erro ao salvar o arquivo.";
            // Redirecionando para a p�gina de gerenciamento de produtos
            header('Location: index.php?page=Produtos');
            exit;
        }
    } else {
        echo "Erro no envio da imagem.";
        // Redirecionando para a p�gina de gerenciamento de produtos
            header('Location: index.php?page=Produtos');
            exit;
    }
} else {
    echo "M�todo inv�lido.";
    // Redirecionando para a p�gina de gerenciamento de produtos
            header('Location: index.php?page=Produtos');
            exit;
}
?>
