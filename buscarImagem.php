<?php
require_once 'auth.php';
verificarSessao();
header('Content-Type: application/json');

include("config.php");
echo $_GET['id'];

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Consulta para buscar a imagem
    $sql = "SELECT imagem FROM produtos WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);

    if ($stmt->rowCount() > 0) {
        $produto = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verifica se a imagem existe
        if (!empty($produto['imagem']) && file_exists($produto['imagem'])) {
            echo json_encode([
                'sucesso' => true,
                'imagem' => $produto['imagem']
            ]);
        } else {
            echo json_encode([
                'sucesso' => false,
                'mensagem' => 'Imagem n�o encontrada.'
            ]);
        }
    } else {
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Produto n�o encontrado.'
        ]);
    }
} else {
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'ID do produto n�o fornecido.'
    ]);
}
?>
