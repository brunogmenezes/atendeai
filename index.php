<?php
    include("config.php");
    require_once 'auth.php';
    verificarSessao();


    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE username = :username");
    $stmt->execute([':username' => $_SESSION['username']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $page = $_GET['page'] ?? 'InicioPVD';
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <title>Atende Ai - Sistema de Vendas e controle de estoque</title>
        <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport"/>
        <link rel="icon" href="assets/img/kaiadmin/favicon.ico" type="image/x-icon"/>

        <!-- Fonts and icons -->
        <script src="assets/js/plugin/webfont/webfont.min.js"></script>
        <script>
            WebFont.load(
            {
                google: { families: ["Public Sans:300,400,500,600,700"] },
                custom: 
                {
                    families: [
                        "Font Awesome 5 Solid",
                        "Font Awesome 5 Regular",
                        "Font Awesome 5 Brands",
                        "simple-line-icons",
                    ],
                    urls: ["assets/css/fonts.min.css"],
                },
                active: function ()
                {
                    sessionStorage.fonts = true;
                },
            });
        </script>

        <!-- CSS Files -->
        <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
        <link rel="stylesheet" href="assets/css/plugins.min.css" />
        <link rel="stylesheet" href="assets/css/kaiadmin.min.css" />

        <!-- CSS Just for demo purpose, don't include it in your project -->
        <link rel="stylesheet" href="assets/css/demo.css" />
    </head>
    <body>
        <div class="wrapper">
            <!-- Sidebar -->
            <div class="sidebar" data-background-color="dark">
                <div class="sidebar-logo">
                    <!-- Logo Header -->
                    <div class="logo-header" data-background-color="dark">
                        <a href="index.php" class="logo">
                            <img src="assets/img/testelogo.svg" alt="navbar brand" class="navbar-brand" height="20"/>
                        </a>
                        <div class="nav-toggle">
                            <button class="btn btn-toggle toggle-sidebar">
                                <i class="gg-menu-right"></i>
                            </button>
                            <button class="btn btn-toggle sidenav-toggler">
                                <i class="gg-menu-left"></i>
                            </button>
                        </div>
                        <button class="topbar-toggler more">
                            <i class="gg-more-vertical-alt"></i>
                        </button>
                    </div>
                    <!-- End Logo Header -->
                </div>
                <div class="sidebar-wrapper scrollbar scrollbar-inner">
                    <div class="sidebar-content">
                        <ul class="nav nav-secondary">
                            <?php
                            if($user['isAdmin']==true)
                            {
                            ?>
                                <li class="nav-item <?php echo ($page == 'Dashboard') ? 'active' : ''; ?>">
                                    <a href="index.php?page=Dashboard" >
                                        <i class="fas fa-home"></i>
                                        <p>Dashboard</p>
                                    </a>
                                </li>
                            <?php
                            }
                            ?>
                            <li class="nav-section">
                                <span class="sidebar-mini-icon">
                                    <i class="fa fa-ellipsis-h"></i>
                                </span>
                                <h4 class="text-section">Menus</h4>
                            </li>
                            <li class="nav-item <?php echo ($page == 'InicioPVD') ? 'active' : ''; ?>">
                                <a href="index.php?page=InicioPVD">
                                    <i class="fas fa-dollar-sign"></i>
                                    <p>PDV</p>
                                </a>
                            </li>
                            <li class="nav-item <?php echo ($page == 'ListarVendas') ? 'active' : ''; ?>">
                                <a data-bs-toggle="collapse" href="#sidebarVendas">
                                    <i class="fas fa-briefcase"></i>
                                    <p>Vendas</p>
                                    <span class="caret"></span>
                                </a>
                                <div class="collapse" id="sidebarVendas">
                                    <ul class="nav nav-collapse">
                                        <li>
                                            <a href="index.php?page=ListarVendas&dia=<?=date('d');?>&mes=<?=date('m');?>&ano=<?=date('Y');?>">
                                                <span class="sub-item">Listar Vendas</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <?php
                            if($user['isAdmin']==true)
                            {
                            ?>
                                <li class="nav-item <?php echo ($page == 'ListarProdutos') ? 'active' : ''; ?>">
                                    <a data-bs-toggle="collapse" href="#sidebarEstoque">
                                        <i class="fas fa-box"></i>
                                        <p>Estoque</p>
                                        <span class="caret"></span>
                                    </a>
                                    <div class="collapse" id="sidebarEstoque">
                                        <ul class="nav nav-collapse">
                                            <li>
                                                <a href="index.php?page=ListarProdutos">
                                                    <span class="sub-item">Listar Produtos</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                            <?php
                            }
                            ?>
                            <li class="nav-item <?php echo ($page == 'ListarClientes') ? 'active' : ''; ?>">
                                <a href="index.php?page=ListarClientes">
                                    <i class="fas fa-users"></i>
                                    <p>Clientes</p>
                                </a>
                            </li>
                            <?php
                            if($user['isAdmin']==true)
                            {
                            ?>
                            <li class="nav-item <?php echo ($page == 'ListarFinanceiro') ? 'active' : ''; ?>">
                                <a data-bs-toggle="collapse" href="#financeiro">
                                    <i class="fas fa-balance-scale"></i>
                                    <p>Financeiro</p>
                                    <span class="caret"></span>
                                </a>
                                <div class="collapse" id="financeiro">
                                    <ul class="nav nav-collapse">
                                        <li>
                                            <a href="index.php?page=ListarFinanceiro">
                                                <span class="sub-item">Lancamentos (Entrada/Saida)</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="index.php?page=ListarFechamentos">
                                                <span class="sub-item">Fechamento de Caixa</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="index.php?page=ListarTransferencias">
                                                <span class="sub-item">Transferências entre Contas</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="index.php?page=ListarDespesasFixas">
                                                <span class="sub-item">Despesas Fixas</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="index.php?page=ListarSalarios">
                                                <span class="sub-item">Colaboradores</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="index.php?page=ListarTipoPagamento">
                                                <span class="sub-item">Tipos de Pagamento</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="index.php?page=ListarContas">
                                                <span class="sub-item">Contas</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <?php
                            }
                            ?>
                            <?php
                            if($user['isAdmin']==true)
                            {
                            ?>
                            <li class="nav-item <?php echo ($page == 'ListarRelatorios') ? 'active' : ''; ?>">
                                <a href="index.php?page=ListarRelatorios">
                                    <i class="fas fa-book"></i>
                                    <p>Relatórios</p>
                                </a>
                            </li>
                            <?php
                            }
                            ?>
                            <?php
                            if($user['isAdmin']==true)
                            {
                            ?>
                                <li class="nav-item <?php echo ($page == 'ListarSistema') ? 'active' : ''; ?>">
                                    <a data-bs-toggle="collapse" href="#sistema">
                                        <i class="fas fa-cog"></i>
                                        <p>Sistema</p>
                                        <span class="caret"></span>
                                    </a>
                                    <div class="collapse" id="sistema">
                                        <ul class="nav nav-collapse">
                                            <li>
                                                <a href="index.php?page=ListarEmpresa">
                                                    <span class="sub-item">Dados da Empresa</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                            <?php
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- End Sidebar -->

            <div class="main-panel">
                <div class="main-header">
                    <div class="main-header-logo">
                        <!-- Logo Header -->
                        <div class="logo-header" data-background-color="dark">
                            <a href="index.php" class="logo">
                                <img src="assets/img/kaiadmin/logo_light.svg" alt="navbar brand" class="navbar-brand" height="20"/>
                            </a>
                            <div class="nav-toggle">
                                <button class="btn btn-toggle toggle-sidebar">
                                    <i class="gg-menu-right"></i>
                                </button>
                                <button class="btn btn-toggle sidenav-toggler">
                                    <i class="gg-menu-left"></i>
                                </button>
                            </div>
                            <button class="topbar-toggler more">
                                <i class="gg-more-vertical-alt"></i>
                            </button>
                        </div>
                        <!-- End Logo Header -->
                    </div>
                    <!-- Navbar Header -->
                    <nav class="navbar navbar-header navbar-header-transparent navbar-expand-lg border-bottom">
                        <div class="container-fluid">
                            <ul class="navbar-nav topbar-nav ms-md-auto align-items-center">
                                <li class="nav-item topbar-user dropdown hidden-caret">
                                    <a class="dropdown-toggle profile-pic" data-bs-toggle="dropdown" href="#" aria-expanded="false">
                                        <div class="avatar-sm">
                                            <img src="assets/img/Bijulogo.jpg" alt="..." class="avatar-img rounded-circle"/>
                                        </div>
                                        <span class="profile-username">
                                            <span class="op-7">Olá,</span>
                                            <span class="fw-bold"><?=$_SESSION['username'];?></span>
                                        </span>
                                    </a>
                                    <ul class="dropdown-menu dropdown-user animated fadeIn">
                                        <div class="dropdown-user-scroll scrollbar-outer">
                                            <li>
                                                <div class="user-box">
                                                    <div class="avatar-lg">
                                                        <img src="assets/img/Bijulogo.jpg" alt="image profile" class="avatar-img rounded"/>
                                                    </div>
                                                    <div class="u-text">
                                                        <h4><?=$_SESSION['username'];?></h4>
                                                        <a href="#" class="btn btn-xs btn-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#modalMinhaConta">
                                                            Minha Conta
                                                        </a>
                                                    </div>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item" href="logout.php">Logout</a>
                                            </li>
                                        </div>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </nav>
                </div>
                <div class="container">
                    <div class="page-inner">
                        <?php
                            if(!isset($_GET['page']))
                            {
                                include('pagePVD.php');
                            }
                            else if ($page=='Dashboard')
                            {
                              include('pageDashboard.php');
                            }
                            else if ($page=='InicioPVD')
                            {
                              include('pagePVD.php');
                            }
                            else if ($page=='ListarVendas')
                            {
                              include('pageListarVendas.php');
                            }
                            else if ($page=='Whatsapp')
                            {
                              include('pageWhatsapp.php');
                            }
                            else if ($page=='Calendario')
                            {
                              include('pageCalendario.php');
                            }
                            else if ($page=='ListarProdutos')
                            {
                              include('pageListarProdutos.php');
                            }
                            else if ($page=='ListarClientes')
                            {
                              include('pageListarClientes.php');
                            }
                            else if ($page=='ListarFinanceiro')
                            {
                              include('pageListarFinanceiro.php');
                            }
                            else if ($page=='ListarTransferencias')
                            {
                              include('pageListarTransferencias.php');
                            }
                            else if ($page=='ListarFechamentos')
                            {
                              include('pageListarFechamentos.php');
                            }
                            else if ($page=='ListarLivroCaixa')
                            {
                              include('pageListarLivroCaixa.php');
                            }
                            else if ($page=='ListarDespesasFixas')
                            {
                              include('pageListarDespesasFixas.php');
                            }
                            else if ($page=='ListarSalarios')
                            {
                              include('pageListarSalarios.php');
                            }
                            else if ($page=='ListarTipoPagamento')
                            {
                              include('pageListarTipoPagamento.php');
                            }
                            else if ($page=='ListarContas')
                            {
                              include('pageListarContas.php');
                            }
                            else if ($page=='ListarRelatorios')
                            {
                              include('pageListarRelatorios.php');
                            }
                            else if ($page=='ListarEmpresa')
                            {
                              include('pageListarEmpresa.php');
                            }
                        ?>
                    </div>
                </div>

                <footer class="footer">
                    <div class="container-fluid d-flex justify-content-between">
                        <nav class="pull-left">
                        </nav>
                        <div class="copyright">
                            <?=date('Y');?>, feito por <a href="https://netsolutions.com.br" target="_blank">NetSolutions</a>
                        </div>
                    </div>
                </footer>
            </div>
        </div>
        <!-- MODAL TROCA SENHA USUARIO -->
       
        <div class="modal fade" id="modalMinhaConta" tabindex="-1" aria-labelledby="modalMinhaContaLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form class="modal-content" method="post" action="formEditar.php">
                    <input type="hidden" name="funcao" value="EditarColaborador">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalMinhaContaLabel">Minha Conta</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="usuario" class="form-label">Usuário</label>
                            <input type="text" class="form-control" id="usuario" name="usuario" value="<?php echo $user['username']; ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Nova Senha</label>
                            <input type="password" class="form-control" id="password" name="password">
                            <small class="text-muted">Deixe em branco para manter a senha atual.</small>
                        </div>
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome</label>
                            <input type="text" class="form-control" id="nome" name="nome" value="<?= htmlspecialchars($user['nome'] ?? '');?>">
                        </div>
                        <div class="mb-3">
                            <label for="cpf" class="form-label">CPF</label>
                            <input type="text" class="form-control" id="cpf" name="cpf" value="<?= htmlspecialchars($user['cpf'] ?? '');?>">
                        </div>
                        <div class="mb-3">
                            <label for="foto" class="form-label">Foto perfil</label>
                            <input type="file" class="form-control" id="imagem" name="imagem" accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
        <!--   Core JS Files   -->
        <script src="assets/js/core/jquery-3.7.1.min.js"></script>
        <script src="assets/js/core/popper.min.js"></script>
        <script src="assets/js/core/bootstrap.min.js"></script>
    
        <!-- jQuery Scrollbar -->
        <script src="assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>
    
        <!-- Chart JS -->
        <script src="assets/js/plugin/chart.js/chart.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chartjs-chart-matrix@latest"></script>
    
        <!-- Datatables -->
        <script src="assets/js/plugin/datatables/datatables.min.js"></script>
    
        <!-- Bootstrap Notify -->
        <script src="assets/js/plugin/bootstrap-notify/bootstrap-notify.min.js"></script>
    
        <!-- Sweet Alert -->
        <script src="assets/js/plugin/sweetalert/sweetalert.min.js"></script>
    
        <!-- Kaiadmin JS -->
        <script src="assets/js/kaiadmin.min.js"></script>
    
    </body>
</html>
