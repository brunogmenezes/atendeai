<?php
include("config.php");

require_once 'auth.php';
verificarSessao();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int) $_POST['id'];
    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao']);
    $preco_custo = trim($_POST['preco_custo']);
    $preco_venda = trim($_POST['preco_venda']);
    $quantidade = trim($_POST['quantidade']);
    $quantidade_critico = trim($_POST['quantidade_critico']);

    // Verificar se foi feito upload de um arquivo
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == UPLOAD_ERR_OK)
        {
            // Gerar um nome aleatório para o arquivo
            $extensao = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION); // Obtém a extensão do arquivo
            $nomeArquivo = uniqid('produto_', true) . '.' . $extensao; // Nome único com extensão
    
            // Diretório de destino para armazenar a imagem
            $diretorioDestino = 'uploads/';
    
            // Caminho completo do arquivo
            $caminhoDestino = $diretorioDestino . $nomeArquivo;
    
            // Verificar se a pasta existe, caso contrário, criar
            if (!file_exists($diretorioDestino))
            {
                mkdir($diretorioDestino, 0777, true);
            }
    
            // Tentar mover o arquivo para o diretório de destino
            if (move_uploaded_file($_FILES['imagem']['tmp_name'], $caminhoDestino))
            {
                echo "Imagem enviada com sucesso!";
            }
            else
            {
                die("Erro ao mover a imagem.");
            }
        }
        else
        {
            $nomeArquivo = null; // Caso não tenha sido enviada imagem
        }

    try {
        // Query de atualização
        $query = "UPDATE produtos SET nome = :nome, descricao = :descricao, preco_custo = :preco_custo, preco_venda = :preco_venda, quantidade = :quantidade, quantidade_critico = :quantidade_critico, imagem = :imagem WHERE id = :id";
        $stmt = $pdo->prepare($query);

        // Executa a consulta com parâmetros
        $stmt->execute([
            ':nome' => $nome,
            ':descricao' => $descricao,
            ':preco_custo' => $preco_custo,
            ':preco_venda' => $preco_venda,
            ':quantidade' => $quantidade,
            ':quantidade_critico' => $quantidade_critico,
            ':imagem' => $nomeArquivo,
            ':id' => $id
        ]);

        header("Location: index.php?page=ListarProdutos");
    } catch (PDOException $e) {
        // Erro na execução
        header("Location: index.php?page=ListarProdutos&error=" . urlencode("Erro ao atualizar o produto: " . $e->getMessage()));
    }
    exit();
}
?>
