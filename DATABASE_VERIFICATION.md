# 🗄️ Verificação da Estrutura do Banco de Dados

## ✅ Checklist - Tabelas Necessárias

Execute os seguintes comandos SQL no seu banco de dados para verificar se todas as estruturas estão em lugar:

### 1. Verificar tabela `vendas`
```sql
DESCRIBE vendas;
-- Ou
SHOW COLUMNS FROM vendas;
```

**Campos obrigatórios:**
- `id` - INT, PRIMARY KEY
- `total` - DECIMAL ou FLOAT
- `data_venda` - DATE ou DATETIME
- `estornado` - CHAR(1) ou VARCHAR(1) com valores 't' (true) ou 'f' (false)

---

### 2. Verificar tabela `itens_venda`
```sql
DESCRIBE itens_venda;
```

**Campos obrigatórios:**
- `id` - INT, PRIMARY KEY
- `venda_id` - INT, FOREIGN KEY referenciando vendas.id
- `quantidade` - INT

---

### 3. Verificar tabela `clientes`
```sql
DESCRIBE clientes;
```

**Campo obrigatório:**
- `id` - INT, PRIMARY KEY

---

### 4. Verificar tabela `produtos`
```sql
DESCRIBE produtos;
```

**Campos obrigatórios:**
- `id` - INT, PRIMARY KEY
- `quantidade` - INT (estoque atual)
- `quantidade_critico` - INT (limite crítico)
- `preco_venda` - DECIMAL ou FLOAT
- `preco_custo` - DECIMAL ou FLOAT (opcional, para cálculo de lucro)

---

### 5. Verificar tabela `despesasfixas`
```sql
DESCRIBE despesasfixas;
```

**Campos obrigatórios:**
- `id` - INT, PRIMARY KEY
- `valor` - DECIMAL ou FLOAT

---

### 6. Verificar tabela `contas`
```sql
DESCRIBE contas;
```

**Campos obrigatórios:**
- `id` - INT, PRIMARY KEY
- `saldo` - DECIMAL ou FLOAT

---

### 7. Verificar tabela `colaboradores`
```sql
DESCRIBE colaboradores;
```

**Campos obrigatórios:**
- `id` - INT, PRIMARY KEY
- `salario` - DECIMAL ou FLOAT

---

## 🔍 Verificação de Endpoints

Os seguintes endpoints PHP devem existir e retornar dados em JSON:

### 1. `endpointProdutosMaisVendidos.php`

**Retorno esperado:**
```json
{
  "labels": ["Produto A", "Produto B", "Produto C", "Produto D", "Produto E"],
  "data": [100, 80, 60, 40, 20],
  "backgroundColor": ["#FF6384", "#36A2EB", "#FFCE56", "#4BC0C0", "#9966FF"]
}
```

---

### 2. `endpointTiposdePagamentos.php`

**Retorno esperado:**
```json
{
  "labels": ["Dinheiro", "Cartão Débito", "Cartão Crédito", "Pix"],
  "data": [150, 200, 300, 250],
  "backgroundColor": ["#FF6384", "#36A2EB", "#FFCE56", "#4BC0C0"]
}
```

---

### 3. `endpointDiasVendas.php`

**Retorno esperado:**
```json
{
  "labels": ["Segunda", "Terça", "Quarta", "Quinta", "Sexta", "Sábado", "Domingo"],
  "data": [120, 130, 145, 155, 170, 200, 90],
  "backgroundColor": ["#FFA727", "#FFA727", "#FFA727", "#FFA727", "#FFA727", "#FFA727", "#FFA727"]
}
```

---

### 4. `endpointVendasPorHora.php`

**Retorno esperado:**
```json
{
  "data": [
    {"x": 0, "y": 8, "v": 5},
    {"x": 0, "y": 9, "v": 15},
    {"x": 1, "y": 10, "v": 25},
    // ... mais dados
  ],
  "diasSemana": ["Domingo", "Segunda", "Terça", "Quarta", "Quinta", "Sexta", "Sábado"],
  "horas": ["00h", "01h", "02h", "03h", "04h", "05h", "06h", "07h", "08h", "09h", "10h", "11h", 
            "12h", "13h", "14h", "15h", "16h", "17h", "18h", "19h", "20h", "21h", "22h", "23h"],
  "maxValue": 50
}
```

---

### 5. `endpoint.php`

**Retorno esperado:**
```json
{
  "saidas": [5000, 4500, 6000, 5500, 7000, 6500, 8000, 7500, 9000, 8500, 10000, 9500],
  "entradas": [15000, 16000, 18000, 17000, 20000, 21000, 22000, 23000, 25000, 26000, 28000, 30000],
  "saldoAcumulado": [10000, 21500, 33500, 45000, 58000, 72500, 86500, 102000, 118000, 135500, 153500, 174000]
}
```

---

## 🛠️ Exemplo de Queries SQL

Se precisar criar ou recriar as tabelas, aqui estão os comandos:

### Criar tabela `vendas`
```sql
CREATE TABLE vendas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  total DECIMAL(10, 2) NOT NULL,
  data_venda DATE NOT NULL,
  estornado CHAR(1) DEFAULT 'f',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Criar tabela `itens_venda`
```sql
CREATE TABLE itens_venda (
  id INT AUTO_INCREMENT PRIMARY KEY,
  venda_id INT NOT NULL,
  quantidade INT NOT NULL,
  FOREIGN KEY (venda_id) REFERENCES vendas(id)
);
```

### Criar tabela `produtos`
```sql
CREATE TABLE produtos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(255) NOT NULL,
  quantidade INT NOT NULL DEFAULT 0,
  quantidade_critico INT DEFAULT 10,
  preco_venda DECIMAL(10, 2) NOT NULL,
  preco_custo DECIMAL(10, 2) NOT NULL
);
```

---

## ⚠️ Possíveis Erros e Soluções

### Erro: "Tabela 'vendas' não existe"
**Solução:** Crie a tabela usando o comando SQL acima

### Erro: "Column 'data_venda' doesn't exist"
**Solução:** Adicione a coluna faltante com:
```sql
ALTER TABLE vendas ADD COLUMN data_venda DATE NOT NULL;
```

### Erro: "Valor NULL em uma coluna NOT NULL"
**Solução:** Verifique se os dados estão sendo inseridos corretamente

### Gráficos em branco/vazios
**Solução:** Verifique os endpoints PHP, pode ser que:
- Os endpoints não estão retornando dados
- Os endpoints não estão retornando JSON válido
- Não há dados no banco correspondentes ao período consultado

---

## 📊 Para Testar os Endpoints

1. Abra seu navegador
2. Acesse: `http://seu-dominio/atendeai/endpointProdutosMaisVendidos.php`
3. Você deve ver um JSON formatado
4. Repita para os outros endpoints

Se ver um erro ou JSON vazio, há um problema no endpoint PHP.

---

## 💡 Dicas

- Sempre faça backup antes de alterar a estrutura do banco
- Use `DESCRIBE tabela` para verificar campos
- Use `SELECT COUNT(*) FROM tabela` para verificar se há dados
- Verifique os logs PHP (`error_log`) se algo não funcionar

