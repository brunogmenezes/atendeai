<?php
	include("config.php");
	include("funcoes.php");
    require_once 'auth.php';
verificarSessao();
?>
<style>
    .form-group
    {
        margin-bottom: 1rem;
    }
    .form-select, .form-control
    {
        height: calc(2.25rem + 8px);
    }
    .card-header
    {
        padding-bottom: 1.5rem;
    }
</style>
<div class="col-md-12">
	<div class="card">
		<div class="card-header">
            <div class="d-flex flex-column flex-sm-row align-items-center">
                <h4 class="card-title mb-2 mb-sm-0">Listar Transferências entre Contas</h4>
                <button class="btn btn-primary btn-round ms-sm-auto w-100 w-sm-auto" data-bs-toggle="modal" data-bs-target="#addRowModal">
                    <i class="fa fa-plus"></i>
                    Cadastrar Transferência
                </button>
            </div>
        </div>
		<div class="card-body">
			<div class="table-responsive">
				<table id="add-row" class="display table table-striped table-hover table-mobile-cards">
					<thead>
						<tr>
							<th class="d-none d-md-table-cell" style="width: 5%">ID</th>
							<th>Transferência</th>
                            <th style="width: 20%">Valor</th>
                            <th class="d-none d-sm-table-cell" style="width: 20%">Data</th>
                            <th class="text-center" style="width: 5%">Ação</th>
						</tr>
					</thead>
					<tbody>
						<?php
                            $pagina = $_GET['pagina'] ?? 1;
                            $limite = 10;
                            $offset = ($pagina - 1) * $limite;
                            
                            // Buscar os dados com os filtros
                            $querys = buscarTransferencias($limite, $offset);
                            $totalQuerys = contarTransferencias();
                            $totalPaginas = ceil($totalQuerys / $limite);
			
                			if ($querys)
                			{
                    			foreach ($querys as $query)
                    			{
                    	?>
									<tr>
										<td class="d-none d-md-table-cell" data-label="ID">#<?=$query['id'];?></td>
										<td data-label="Transferência"><?=$query['nome_conta_origem'];?> <i class="fas fa-arrow-right text-warning mx-2"></i> <?=$query['nome_conta_destino'];?></td>
										<td data-label="Valor">R$ <?=number_format($query['valor'], 2, ',', '.');?></td>
                                        <td class="d-none d-sm-table-cell" data-label="Data"><?= date('d/m/Y', strtotime($query['data_lancamento'])) ?></td>
                                        <td data-label="Ação">
                                            <div class="d-flex justify-content-center">
                                                <button type="button" class="btn btn-link btn-danger open-delete-modal p-0" data-id="<?=$query['id'];?>" data-id-conta-origem="<?=$query['id_conta_origem'];?>" data-id-conta-destino="<?=$query['id_conta_destino'];?>" data-valor="<?=$query['valor'];?>" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal">
                                                    <i class="fa fa-times"></i> <span class="d-md-none">Excluir</span>
                                                </button>
                                            </div>
                                        </td>
									</tr>
						<?php
								}
							}
						?>
					</tbody>
				</table>
			</div>
            <div class="col-md-12 mt-3">
                <div class="demo d-flex justify-content-center">
                    <ul class="pagination pg-primary">
                        <?php if ($totalPaginas > 1): ?>
                        <li class="page-item <?= ($pagina == 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= htmlspecialchars($_GET['page'] ?? '') ?>&pagina=1">&laquo;</a>
                        </li>
                
                        <li class="page-item <?= ($pagina == 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= htmlspecialchars($_GET['page'] ?? '') ?>&pagina=<?= ($pagina - 1) ?>">&lsaquo;</a>
                        </li>
                
                        <?php 
                        $paginas_visiveis = 5;
                        $inicio = max(1, $pagina - floor($paginas_visiveis/2));
                        $fim = min($totalPaginas, $inicio + $paginas_visiveis - 1);
                        $inicio = max(1, $fim - $paginas_visiveis + 1);
                
                        for ($i = $inicio; $i <= $fim; $i++): 
                        ?>
                            <li class="page-item <?= ($pagina == $i) ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= htmlspecialchars($_GET['page'] ?? '') ?>&pagina=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                
                        <li class="page-item <?= ($pagina == $totalPaginas) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= htmlspecialchars($_GET['page'] ?? '') ?>&pagina=<?= min($totalPaginas, $pagina + 1) ?>">&rsaquo;</a>
                        </li>
                
                        <li class="page-item <?= ($pagina == $totalPaginas) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= htmlspecialchars($_GET['page'] ?? '') ?>&pagina=<?= $totalPaginas ?>">&raquo;</a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
		</div>
	</div>
</div>
<!-- Modal de Confirmação -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza de que deseja excluir o cadastro desse lançamento?</p>
            </div>
            <div class="modal-footer">
                <form id="deleteForm" action="formExcluir.php" method="POST">
                    <input type="hidden" name="id" id="idTransferencia">
                    <input type="hidden" name="funcao" value="ExcluirTransferencia">
                    <input type="hidden" name="tabela" value="transferencias">
                    <input type="hidden" name="page" value="<?= htmlspecialchars($_GET['page'] ?? '') ?>">
                    <input type="hidden" name="id_conta_origem" id="id_conta_origem">
                    <input type="hidden" name="id_conta_destino" id="id_conta_destino">
                    <input type="hidden" name="valor" id="valor">
                    <button type="submit" class="btn btn-danger">Excluir</button>
                </form>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal de Cadastro -->
<div class="modal fade" id="addRowModal" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header border-0">
				<h5 class="modal-title">
					<span class="fw-mediumbold"> Nova</span>
					<span class="fw-light"> Transferência </span>
				</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
                <form id="formTransferencia" action="cadastrarTransferencias.php" method="POST" enctype="multipart/form-data">
					<div class="row">
                        <div class="col-md-6 pe-0">
                            <div class="form-group form-group-default">
                                <label>Conta de Origem</label>
                                <select class="form-select" id="cadastro_id_conta_origem" name="cadastro_id_conta_origem" required="">
                                    <option value="">Selecione</option>
                                    <?php
                                        $resultado = buscarTodasContasFinanceiro();
                                        if (!empty($resultado))
                                        {
                                            foreach ($resultado as $conta)
                                            {
                                                echo "<option value='" . htmlspecialchars($conta['id']) . "'>" . htmlspecialchars($conta['nome']) . "</option>";
                                            }
                                        }
                                        else
                                        {
                                            echo "<option value=''>Nenhuma conta encontrada</option>";
                                        }
                                    ?>
                                </select>
                            </div>
                        </div>

						<div class="col-md-6">
							<div class="form-group form-group-default">
								<label>Valor R$</label>
								<input id="cadastro_valor" type="number" step="0.01" class="form-control" name="cadastro_valor" required="" />
							</div>
						</div>
                        <div class="col-md-6 pe-0">
                            <div class="form-group form-group-default">
                                <label>Conta de Destino</label>
                                <select class="form-select" id="cadastro_id_conta_destino" name="cadastro_id_conta_destino" required="">
                                    <option value="">Selecione</option>
                                    <?php
                                        $resultado = buscarTodasContasFinanceiro();
                                        if (!empty($resultado))
                                        {
                                            foreach ($resultado as $conta)
                                            {
                                                echo "<option value='" . htmlspecialchars($conta['id']) . "'>" . htmlspecialchars($conta['nome']) . "</option>";
                                            }
                                        }
                                        else
                                        {
                                            echo "<option value=''>Nenhuma conta encontrada</option>";
                                        }
                                    ?>
                                </select>
                            </div>
                        </div>
					</div>
					
                    <div class="modal-footer border-0">
                        <button type="submit" class="btn btn-primary">Salvar</button>
						<button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function ()
    {
        const form = document.getElementById('formTransferencia');
        const origem = document.getElementById('cadastro_id_conta_origem');
        const destino = document.getElementById('cadastro_id_conta_destino');
        const valor = document.getElementById('cadastro_valor');

        form.addEventListener('submit', function (e)
        {
            let erros = [];

            // Verifica se conta de origem e destino são iguais
            if (origem.value === destino.value)
            {
                erros.push("A conta de origem e destino não podem ser iguais.");
            }

            // Verifica se o valor é maior que zero
            if (parseFloat(valor.value) <= 0 || isNaN(parseFloat(valor.value)))
            {
                erros.push("O valor da transferência deve ser maior que zero.");
            }

            if (erros.length > 0)
            {
                e.preventDefault(); // Impede o envio
                alert(erros.join("\n"));
            }
        });
    
        //DELETAR    
        const deleteButtons = document.querySelectorAll('.open-delete-modal');
        const idTransferenciaInput = document.getElementById('idTransferencia');
        const idContaOrigemInput = document.getElementById('id_conta_origem');
        const idContaDestinoInput = document.getElementById('id_conta_destino');
        const valorLancamentoInput = document.getElementById('valor');

        deleteButtons.forEach(button => {
            button.addEventListener('click', function () {
                const idTransferencia = this.getAttribute('data-id');
                const idContaOrigem = this.getAttribute('data-id-conta-origem');
                const idContaDestino = this.getAttribute('data-id-conta-destino');
                const valorTransferencia = this.getAttribute('data-valor');

                idTransferenciaInput.value = idTransferencia;
                idContaOrigemInput.value = idContaOrigem;
                idContaDestinoInput.value = idContaDestino;
                valorLancamentoInput.value = valorTransferencia;
            });
        });
    });
</script>

