<?php
    include("config.php");
    include("funcoes.php");
   require_once 'auth.php';
verificarSessao();
    if ($_POST['funcao']=='CadastrarColaborador')
    {
        // Verificando se o formul�rio foi enviado
        if ($_SERVER['REQUEST_METHOD'] === 'POST')
        {
            // Coletando os dados do formul�rio
            $paginaPos = $_POST['page'];

            $nome = trim($_POST['nome']);
            $data_contratacao = trim($_POST['data_contratacao']);
            $salario = trim($_POST['salario']);
            $usuario = trim($_POST['usuario']);
            $password = trim($_POST['senha']);
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            try
            {
                $perfil_id = !empty($_POST['perfil_id']) ? intval($_POST['perfil_id']) : null;
                $is_admin = isset($_POST['is_admin']) && $_POST['is_admin'] === '1';

                $queryUsuario = "INSERT INTO usuarios (username, password, \"isAdmin\", perfil_id)
                                    VALUES (:username, :password, :is_admin, :perfil_id)";
                $stmtUsuario = $pdo->prepare($queryUsuario);
                $stmtUsuario->execute([
                            ':username' => $usuario,
                            ':password' => $password_hash,
                            ':is_admin' => $is_admin ? 1 : 0,
                            ':perfil_id' => $perfil_id
                        ]);
                $usuarioID = $pdo->lastInsertId();




                // Validando a data de nascimento
                $data_contratacao_formatada = date('Y-m-d', strtotime($data_contratacao));

                // Inserindo o novo cliente
                $query = "INSERT INTO colaboradores (nome, data_contratacao, salario, idusuario) 
                          VALUES (:nome, :data_contratacao, :salario, :idusuario)";
                $stmt = $pdo->prepare($query);
                $stmt->execute([
                    ':nome' => $nome,
                    ':data_contratacao' => $data_contratacao_formatada,
                    ':salario' => $salario,
                    ':idusuario' => $usuarioID
                ]);
        
                // Recuperar o ID do cliente inserido
                $clienteId = $pdo->lastInsertId();
        
                // Registra a auditoria
                registrarAuditoria(
                    $_SESSION['user_id'],
                    'Cadastrou um cliente',
                    $_SERVER['REMOTE_ADDR'],
                    ['idCliente' => $clienteId]
                );
        
                // Redirecionando para a p�gina de gerenciamento de clientes
                header('Location: index.php?page='.$paginaPos.'');
                exit;
            }
            catch (Exception $e)
            {
                die("Erro ao cadastrar colaborador: " . $e->getMessage());
            }
        }
    }
    
?>
