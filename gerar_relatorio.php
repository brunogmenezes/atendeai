<?php

require_once 'config.php';
require_once 'funcoes.php';
require_once 'auth.php';
verificarSessao();
require_once 'vendor/autoload.php'; // Inclua a biblioteca TCPDF ou Dompdf

use Dompdf\Dompdf;

$tipoRelatorio = $_GET['tipo'] ?? '';

// Verificar autentica��o e permiss�es aqui

switch ($tipoRelatorio) {
    case 'financeiro-mensal':
        gerarRelatorioFinanceiroMensal();
        break;
    case 'vendas-diarias':
        gerarRelatorioVendasDiarias();
        break;
    // Adicione outros casos conforme necess�rio
    default:
        header('Location: relatorios.php');
        exit;
}

function gerarRelatorioFinanceiroMensal() {
    global $pdo;
    
    // Obter dados do banco de dados
    $ano = date('Y');
    $mesAtual = date('m');
    
    $query = "SELECT 
                MONTH(data) as mes, 
                SUM(CASE WHEN tipo = 'entrada' THEN valor ELSE 0 END) as receitas,
                SUM(CASE WHEN tipo = 'saida' THEN valor ELSE 0 END) as despesas,
                SUM(CASE WHEN tipo = 'entrada' THEN valor ELSE -valor END) as saldo
              FROM transacoes
              WHERE YEAR(data) = :ano
              GROUP BY MONTH(data)";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([':ano' => $ano]);
    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Gerar HTML do relat�rio
    $html = '
    <h1 style="text-align: center;">Relat�rio Financeiro Mensal - '.$ano.'</h1>
    <table border="1" cellpadding="5" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background-color: #f2f2f2;">
                <th>M�s</th>
                <th>Receitas (R$)</th>
                <th>Despesas (R$)</th>
                <th>Saldo (R$)</th>
            </tr>
        </thead>
        <tbody>';
    
    $meses = ['Janeiro', 'Fevereiro', 'Mar�o', 'Abril', 'Maio', 'Junho', 
              'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
    
    foreach ($meses as $index => $mes) {
        $mesNumero = $index + 1;
        $dadosMes = array_filter($dados, function($item) use ($mesNumero) {
            return $item['mes'] == $mesNumero;
        });
        
        $dadosMes = reset($dadosMes);
        
        $html .= '<tr>';
        $html .= '<td>'.$mes.'</td>';
        $html .= '<td style="text-align: right;">'.($dadosMes ? number_format($dadosMes['receitas'], 2, ',', '.') : '0,00').'</td>';
        $html .= '<td style="text-align: right;">'.($dadosMes ? number_format($dadosMes['despesas'], 2, ',', '.') : '0,00').'</td>';
        
        $saldo = $dadosMes ? $dadosMes['saldo'] : 0;
        $corSaldo = $saldo >= 0 ? '#28a745' : '#dc3545';
        
        $html .= '<td style="text-align: right; color: '.$corSaldo.';">'.number_format($saldo, 2, ',', '.').'</td>';
        $html .= '</tr>';
    }
    
    $html .= '</tbody></table>';
    
    // Gerar PDF
    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();
    
    // Sa�da do PDF
    $dompdf->stream("relatorio-financeiro-mensal-{$ano}.pdf", ["Attachment" => true]);
}

function gerarRelatorioVendasDiarias() {
    // Implementa��o similar para relat�rio de vendas di�rias
}