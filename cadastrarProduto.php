<?php
   
    include 'config.php';
    include("funcoes.php");

    require_once 'auth.php';
verificarSessao();
    
    // Verificando se o formul�rio foi enviado
    if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        // Coletando os dados do formul�rio
        $nome = $_POST['nome'];
        $descricao = $_POST['descricao'];
        $preco_custo = $_POST['preco_custo'];
        $preco_venda = $_POST['preco_venda'];
        $quantidade = $_POST['quantidade'];
        $quantidade_critico = $_POST['quantidade_critico'];
        //if (isset($_POST['combo'])) 
        //{
        //    $combo = true;
        //    $qtd_itens_combo = $_POST['qtd_itens_combo'];
        //}
        //else
        //{
        //    $combo = false;
        //    $qtd_itens_combo = null;
        //}
    
        // Verificar se foi feito upload de um arquivo
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == UPLOAD_ERR_OK)
        {
            // Gerar um nome aleat�rio para o arquivo
            $extensao = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION); // Obt�m a extens�o do arquivo
            $nomeArquivo = uniqid('produto_', true) . '.' . $extensao; // Nome �nico com extens�o
    
            // Diret�rio de destino para armazenar a imagem
            $diretorioDestino = 'uploads/';
    
            // Caminho completo do arquivo
            $caminhoDestino = $diretorioDestino . $nomeArquivo;
    
            // Verificar se a pasta existe, caso contr�rio, criar
            if (!file_exists($diretorioDestino))
            {
                mkdir($diretorioDestino, 0777, true);
            }
    
            // Tentar mover o arquivo para o diret�rio de destino
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
            $nomeArquivo = null; // Caso n�o tenha sido enviada imagem
        }
    
        try
        {
            // Inserindo o novo produto com o nome da imagem
            $query = "INSERT INTO produtos (nome, descricao, preco_custo, quantidade, imagem, preco_venda, quantidade_critico) 
                      VALUES (:nome, :descricao, :preco_custo, :quantidade, :imagem, :preco_venda, :quantidade_critico)";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                ':nome' => $nome,
                ':descricao' => $descricao,
                ':preco_custo' => $preco_custo,
                ':quantidade' => $quantidade,
                ':imagem' => $nomeArquivo, // Salva o nome da imagem no banco de dados
                ':preco_venda' => $preco_venda,
                'quantidade_critico' => $quantidade_critico
            ]);
    
            // Recuperar o ID do produto inserido
            $produtoId = $pdo->lastInsertId();
    
            // Registra a auditoria
            registrarAuditoria(
                $_SESSION['user_id'],
                'Cadastrou um produto',
                $_SERVER['REMOTE_ADDR'],
                ['idProduto' => $produtoId]
            );
    
            // Redirecionando para a p�gina de gerenciamento de produtos
            header('Location: index.php?page=ListarProdutos');
            exit;
        }
        catch (PDOException $e)
        {
            die("Erro ao cadastrar produto: " . $e->getMessage());
        }
    }
?>
