<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gráfico de Produtos Mais Vendidos</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .chart-container {
            position: relative;
            height: 400px;
            margin: 20px 0;
        }
        h1 {
            text-align: center;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Produtos Mais Vendidos</h1>
        <div class="chart-container">
            <canvas id="donutChart"></canvas>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
    fetch('endpointProdutosMaisVendidos.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro na rede: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                renderChart(data);
            } else {
                showError(data.message || 'Erro desconhecido nos dados');
            }
        })
        .catch(error => {
            showError(error.message);
            console.error('Detalhes do erro:', error);
        });

    function renderChart(chartData) {
        const ctx = document.getElementById('donutChart').getContext('2d');
        
        new Chart(ctx, {
            // ... (seu código existente do gráfico)
        });
    }

    function showError(message) {
        const container = document.querySelector('.chart-container');
        container.innerHTML = `
            <div class="alert alert-danger">
                <strong>Erro:</strong> ${message}
                <p>Verifique: 
                   <br>- Se o servidor está online
                   <br>- Se o arquivo PHP está no local correto
                   <br>- O console do navegador para detalhes (F12)
                </p>
            </div>
        `;
        
        // Adicione este estilo no seu CSS
        const style = document.createElement('style');
        style.textContent = `
            .alert-danger {
                padding: 15px;
                background-color: #f8d7da;
                border: 1px solid #f5c6cb;
                color: #721c24;
                border-radius: 4px;
            }
        `;
        document.head.appendChild(style);
    }
});
    </script>
</body>
</html>