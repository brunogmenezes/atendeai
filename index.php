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
<html lang="pt-BR">
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <title>Atende Ai - Sistema de Vendas e controle de estoque</title>
        <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport"/>
        <link rel="icon" href="assets/img/kaiadmin/favicon.ico" type="image/x-icon"/>

        <!-- Fonts and icons -->
        <script src="assets/js/plugin/webfont/webfont.min.js"></script>

        <!-- Chart JS & Plugins -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chartjs-chart-matrix@2.0.1/dist/chartjs-chart-matrix.min.js"></script>
        <script>
            // Shim para compatibilidade com scripts que esperam Chart.instances (comportamento do v2)
            if (window.Chart && !Chart.instances) {
                Chart.instances = {};
            }
        </script>

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
        <link rel="stylesheet" href="assets/css/demo.css" />
        <link rel="stylesheet" href="css/dashboard-enhanced.css" />
    </head>
    <body>
        <div class="wrapper">
            <!-- Sidebar -->
            <div class="sidebar" data-background-color="dark">
                <div class="sidebar-logo">
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
                </div>
                <div class="sidebar-wrapper scrollbar scrollbar-inner">
                    <div class="sidebar-content">
                        <div class="user">
                            <div class="avatar-sm float-start me-2">
                                <div class="avatar-title rounded-circle border border-white bg-primary text-white fw-bold">
                                    <?= strtoupper(substr($user['username'], 0, 1)) ?>
                                </div>
                            </div>
                            <div class="info">
                                <a data-bs-toggle="collapse" href="#collapseExample" aria-expanded="true">
                                    <span>
                                        <?= htmlspecialchars($user['username']) ?>
                                        <span class="user-level"><?= $user['isAdmin'] ? 'Administrador' : 'Operador' ?></span>
                                        <span class="caret"></span>
                                    </span>
                                </a>
                                <div class="clearfix"></div>
                                <div class="collapse" id="collapseExample">
                                    <ul class="nav">
                                        <li>
                                            <a href="#" data-bs-toggle="modal" data-bs-target="#modalMinhaConta">
                                                <span class="link-collapse"><i class="fas fa-user-cog me-2"></i>Minha Conta</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="logout.php">
                                                <span class="link-collapse text-danger"><i class="fas fa-sign-out-alt me-2"></i>Sair do Sistema</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <ul class="nav nav-secondary">
                            <?php if(temPermissao('ver_dashboard')): ?>
                                <li class="nav-section">
                                    <span class="sidebar-mini-icon"><i class="fa fa-ellipsis-h"></i></span>
                                    <h4 class="text-section">Dashboard</h4>
                                </li>
                                <li class="nav-item <?php echo ($page == 'Dashboard') ? 'active' : ''; ?>">
                                    <a href="index.php?page=Dashboard" >
                                        <i class="fas fa-chart-line"></i>
                                        <p>Visão Geral</p>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php if(temPermissao('acessar_pdv') || temPermissao('listar_vendas')): ?>
                                <li class="nav-section">
                                    <span class="sidebar-mini-icon"><i class="fa fa-ellipsis-h"></i></span>
                                    <h4 class="text-section">Operacional</h4>
                                </li>
                                <?php if(temPermissao('acessar_pdv')): ?>
                                    <li class="nav-item <?php echo ($page == 'InicioPVD') ? 'active' : ''; ?>">
                                        <a href="index.php?page=InicioPVD">
                                            <i class="fas fa-dollar-sign"></i>
                                            <p>PDV</p>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <?php if(temPermissao('listar_vendas')): ?>
                                    <li class="nav-item <?php echo ($page == 'ListarVendas') ? 'active' : ''; ?>">
                                        <a data-bs-toggle="collapse" href="#sidebarVendas">
                                            <i class="fas fa-briefcase"></i>
                                            <p>Vendas</p>
                                            <span class="caret"></span>
                                        </a>
                                        <div class="collapse <?php echo ($page == 'ListarVendas') ? 'show' : ''; ?>" id="sidebarVendas">
                                             <ul class="nav nav-collapse">
                                                 <li>
                                                     <a href="index.php?page=ListarVendas&dia=<?=date('d');?>&mes=<?=date('m');?>&ano=<?=date('Y');?>">
                                                         <span class="sub-item">Relatório de Vendas</span>
                                                     </a>
                                                 </li>
                                             </ul>
                                         </div>
                                     </li>
                                 <?php endif; ?>
                             <?php endif; ?>

                             <?php if(temPermissao('listar_produtos') || temPermissao('listar_compras')): ?>
                                 <li class="nav-section">
                                     <span class="sidebar-mini-icon"><i class="fa fa-ellipsis-h"></i></span>
                                     <h4 class="text-section">Gestão de Estoque</h4>
                                 </li>
                                 <li class="nav-item <?php echo ($page == 'ListarProdutos' || $page == 'ListarCompras' || $page == 'AdicionarCompra') ? 'active' : ''; ?>">
                                     <a data-bs-toggle="collapse" href="#sidebarEstoque">
                                         <i class="fas fa-boxes"></i>
                                         <p>Mercadorias</p>
                                         <span class="caret"></span>
                                     </a>
                                     <div class="collapse <?php echo ($page == 'ListarProdutos' || $page == 'ListarCompras' || $page == 'AdicionarCompra') ? 'show' : ''; ?>" id="sidebarEstoque">
                                         <ul class="nav nav-collapse">
                                             <?php if(temPermissao('listar_produtos')): ?>
                                                 <li class="<?php echo ($page == 'ListarProdutos') ? 'active' : ''; ?>">
                                                     <a href="index.php?page=ListarProdutos">
                                                         <span class="sub-item">Catálogo de Produtos</span>
                                                     </a>
                                                 </li>
                                             <?php endif; ?>
                                             <?php if(temPermissao('listar_compras')): ?>
                                                 <li class="<?php echo ($page == 'ListarCompras' || $page == 'AdicionarCompra') ? 'active' : ''; ?>">
                                                     <a href="index.php?page=ListarCompras">
                                                         <span class="sub-item">Entradas / Compras</span>
                                                     </a>
                                                 </li>
                                             <?php endif; ?>
                                         </ul>
                                     </div>
                                 </li>
                             <?php endif; ?>

                             <?php if(temPermissao('listar_clientes') || temPermissao('listar_financeiro')): ?>
                                 <li class="nav-section">
                                     <span class="sidebar-mini-icon"><i class="fa fa-ellipsis-h"></i></span>
                                     <h4 class="text-section">Pessoas e Finanças</h4>
                                 </li>
                                 <?php if(temPermissao('listar_clientes')): ?>
                                     <li class="nav-item <?php echo ($page == 'ListarClientes') ? 'active' : ''; ?>">
                                         <a href="index.php?page=ListarClientes">
                                             <i class="fas fa-user-friends"></i>
                                             <p>Clientes</p>
                                         </a>
                                     </li>
                                 <?php endif; ?>
                                 <?php if(temPermissao('listar_financeiro')): ?>
                                     <li class="nav-item <?php echo ($page == 'ListarFinanceiro' || $page == 'ListarFechamentos' || $page == 'ListarTransferencias' || $page == 'ListarDespesasFixas' || $page == 'ListarTipoPagamento' || $page == 'ListarContas') ? 'active' : ''; ?>">
                                         <a data-bs-toggle="collapse" href="#financeiro">
                                             <i class="fas fa-wallet"></i>
                                             <p>Financeiro</p>
                                             <span class="caret"></span>
                                         </a>
                                         <div class="collapse <?php echo ($page == 'ListarFinanceiro' || $page == 'ListarFechamentos' || $page == 'ListarTransferencias' || $page == 'ListarDespesasFixas' || $page == 'ListarTipoPagamento' || $page == 'ListarContas') ? 'show' : ''; ?>" id="financeiro">
                                             <ul class="nav nav-collapse">
                                                 <li class="<?php echo ($page == 'ListarFinanceiro') ? 'active' : ''; ?>">
                                                     <a href="index.php?page=ListarFinanceiro">
                                                         <span class="sub-item">Fluxo de Caixa</span>
                                                     </a>
                                                 </li>
                                                 <li class="<?php echo ($page == 'ListarContas') ? 'active' : ''; ?>">
                                                     <a href="index.php?page=ListarContas">
                                                         <span class="sub-item">Minhas Contas</span>
                                                     </a>
                                                 </li>
                                                 <li class="<?php echo ($page == 'ListarTransferencias') ? 'active' : ''; ?>">
                                                     <a href="index.php?page=ListarTransferencias">
                                                         <span class="sub-item">Transferência entre Contas</span>
                                                     </a>
                                                 </li>
                                                 <li class="<?php echo ($page == 'ListarFechamentos') ? 'active' : ''; ?>">
                                                     <a href="index.php?page=ListarFechamentos">
                                                         <span class="sub-item">Fechamentos</span>
                                                     </a>
                                                 </li>
                                                 <li class="<?php echo ($page == 'ListarTipoPagamento') ? 'active' : ''; ?>">
                                                     <a href="index.php?page=ListarTipoPagamento">
                                                         <span class="sub-item">Config. Pagamentos</span>
                                                     </a>
                                                 </li>
                                             </ul>
                                         </div>
                                     </li>
                                 <?php endif; ?>
                             <?php endif; ?>

                             <?php if(temPermissao('listar_relatorios')): ?>
                                <li class="nav-item <?php echo ($page == 'ListarRelatorios') ? 'active' : ''; ?>">
                                    <a href="index.php?page=ListarRelatorios">
                                        <i class="fas fa-book"></i>
                                        <p>Relatórios</p>
                                    </a>
                                </li>
                             <?php endif; ?>

                             <?php if(temPermissao('gerenciar_sistema')): ?>
                                <li class="nav-item <?php echo ($page == 'ListarEmpresa' || $page == 'ListarSalarios' || $page == 'ListarPerfis' || $page == 'ListarSistema') ? 'active' : ''; ?>">
                                    <a data-bs-toggle="collapse" href="#sistema">
                                        <i class="fas fa-cog"></i>
                                        <p>Sistema</p>
                                        <span class="caret"></span>
                                    </a>
                                    <div class="collapse <?php echo ($page == 'ListarEmpresa' || $page == 'ListarSalarios' || $page == 'ListarPerfis') ? 'show' : ''; ?>" id="sistema">
                                        <ul class="nav nav-collapse">
                                            <li class="<?php echo ($page == 'ListarEmpresa') ? 'active' : ''; ?>">
                                                <a href="index.php?page=ListarEmpresa">
                                                    <span class="sub-item">Dados da Empresa</span>
                                                </a>
                                            </li>
                                            <li class="<?php echo ($page == 'ListarSalarios') ? 'active' : ''; ?>">
                                                <a href="index.php?page=ListarSalarios">
                                                    <span class="sub-item">Colaboradores</span>
                                                </a>
                                            </li>
                                            <li class="<?php echo ($page == 'ListarPerfis') ? 'active' : ''; ?>">
                                                <a href="index.php?page=ListarPerfis">
                                                    <span class="sub-item">Perfis e Permissões</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- End Sidebar -->

            <div class="main-panel">
                <div class="main-header">
                    <div class="main-header-logo">
                        <div class="logo-header" data-background-color="dark">
                            <a href="index.php" class="logo">
                                <img src="assets/img/testelogo.svg" alt="navbar brand" class="navbar-brand" height="20"/>
                            </a>
                            <div class="nav-toggle">
                                <button class="btn btn-toggle toggle-sidebar"><i class="gg-menu-right"></i></button>
                                <button class="btn btn-toggle sidenav-toggler"><i class="gg-menu-left"></i></button>
                            </div>
                            <button class="topbar-toggler more"><i class="gg-more-vertical-alt"></i></button>
                        </div>
                    </div>
                    <!-- TOPBAR REMOVED AS REQUESTED -->
                </div>
                <div class="container">
                    <div class="page-inner">
                        <?php
                            $page_permissions = [
                                'Dashboard' => 'ver_dashboard',
                                'InicioPVD' => 'acessar_pdv',
                                'ListarVendas' => 'listar_vendas',
                                'ListarProdutos' => 'listar_produtos',
                                'ListarClientes' => 'listar_clientes',
                                'ListarFinanceiro' => 'listar_financeiro',
                                'ListarTransferencias' => 'listar_financeiro',
                                'ListarFechamentos' => 'listar_financeiro',
                                'ListarDespesasFixas' => 'listar_financeiro',
                                'ListarTipoPagamento' => 'listar_financeiro',
                                'ListarContas' => 'listar_financeiro',
                                'ListarRelatorios' => 'listar_relatorios',
                                'ListarEmpresa' => 'gerenciar_sistema',
                                'ListarSalarios' => 'gerenciar_sistema',
                                'ListarPerfis' => 'gerenciar_sistema',
                                'ListarCompras' => 'listar_compras',
                                'AdicionarCompra' => 'lancar_compras'
                            ];

                            $active_page = !isset($_GET['page']) ? 'InicioPVD' : $page;
                            
                            if (isset($page_permissions[$active_page]) && !temPermissao($page_permissions[$active_page])) {
                                echo '<div class="alert alert-danger mt-4"><i class="fas fa-exclamation-triangle me-2"></i><b>Acesso Negado:</b> Você não tem permissão para acessar esta página.</div>';
                            } else {
                                if(!isset($_GET['page'])) { include('pagePVD.php'); }
                                else if ($page=='Dashboard') { include('pageDashboard.php'); }
                                else if ($page=='InicioPVD') { include('pagePVD.php'); }
                                else if ($page=='ListarVendas') { include('pageListarVendas.php'); }
                                else if ($page=='ListarProdutos') { include('pageListarProdutos.php'); }
                                else if ($page=='ListarClientes') { include('pageListarClientes.php'); }
                                else if ($page=='ListarFinanceiro') { include('pageListarFinanceiro.php'); }
                                else if ($page=='ListarTransferencias') { include('pageListarTransferencias.php'); }
                                else if ($page=='ListarFechamentos') { include('pageListarFechamentos.php'); }
                                else if ($page=='ListarDespesasFixas') { include('pageListarDespesasFixas.php'); }
                                else if ($page=='ListarSalarios') { include('pageListarSalarios.php'); }
                                else if ($page=='ListarTipoPagamento') { include('pageListarTipoPagamento.php'); }
                                else if ($page=='ListarContas') { include('pageListarContas.php'); }
                                else if ($page=='ListarRelatorios') { include('pageListarRelatorios.php'); }
                                else if ($page=='ListarEmpresa') { include('pageListarEmpresa.php'); }
                                else if ($page=='ListarCompras') { include('pageListarCompras.php'); }
                                else if ($page=='AdicionarCompra') { include('pageAdicionarCompra.php'); }
                                else if ($page=='ListarPerfis') { include('pageListarPerfis.php'); }
                            }
                        ?>
                    </div>
                </div>

                <footer class="footer">
                    <div class="container-fluid d-flex justify-content-between">
                        <div class="copyright">
                            <?=date('Y');?>, feito por <a href="https://netsolutions.com.br" target="_blank">NetSolutions</a>
                        </div>
                    </div>
                </footer>
            </div>
        </div>

        <!-- MODAL MINHA CONTA -->
        <div class="modal fade" id="modalMinhaConta" tabindex="-1" aria-labelledby="modalMinhaContaLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalMinhaContaLabel">Minha Conta - Alterar Senha</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="alterarSenha.php" method="POST">
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="senha_atual">Senha Atual</label>
                                <input type="password" class="form-control" name="senha_atual" required>
                            </div>
                            <div class="form-group mt-2">
                                <label for="nova_senha">Nova Senha</label>
                                <input type="password" class="form-control" name="nova_senha" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!--   Core JS Files   -->
        <script src="assets/js/core/jquery-3.7.1.min.js"></script>
        <script src="assets/js/core/popper.min.js"></script>
        <script src="assets/js/core/bootstrap.min.js"></script>

        <!-- jQuery Scrollbar -->
        <script src="assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>

        <!-- jQuery Sparkline -->
        <script src="assets/js/plugin/jquery.sparkline/jquery.sparkline.min.js"></script>
        <script src="assets/js/plugin/chart-circle/circles.min.js"></script>
        <script src="assets/js/plugin/datatables/datatables.min.js"></script>
        <script src="assets/js/plugin/bootstrap-notify/bootstrap-notify.min.js"></script>
        <script src="assets/js/plugin/jsvectormap/jsvectormap.min.js"></script>
        <script src="assets/js/plugin/jsvectormap/world-merc.js"></script>
        <script src="assets/js/plugin/sweetalert/sweetalert.min.js"></script>
        <script src="assets/js/kaiadmin.min.js"></script>
    </body>
</html>
