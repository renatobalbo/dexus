<!-- Listagem de Clientes -->
<div class="container-fluid">
    <!-- Cabeçalho da Página -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Clientes</h1>
        <a href="?page=clientes/form" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Novo Cliente
        </a>
    </div>

    <!-- Cartão de Pesquisa -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filtros</h6>
        </div>
        <div class="card-body">
            <form id="form-filtro" method="get">
                <input type="hidden" name="page" value="clientes">
                
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filtro-codigo">Código:</label>
                            <input type="text" class="form-control" id="filtro-codigo" name="codigo" 
                                value="<?php echo isset($_GET['codigo']) ? $_GET['codigo'] : ''; ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filtro-tipo">Tipo:</label>
                            <select class="form-control" id="filtro-tipo" name="tipo">
                                <option value="">Todos</option>
                                <option value="F" <?php echo isset($_GET['tipo']) && $_GET['tipo'] === 'F' ? 'selected' : ''; ?>>
                                    F - Pessoa Física
                                </option>
                                <option value="J" <?php echo isset($_GET['tipo']) && $_GET['tipo'] === 'J' ? 'selected' : ''; ?>>
                                    J - Pessoa Jurídica
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="filtro-razao">Razão Social:</label>
                            <input type="text" class="form-control" id="filtro-razao" name="razao" 
                                value="<?php echo isset($_GET['razao']) ? $_GET['razao'] : ''; ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filtro-documento">CPF/CNPJ:</label>
                            <input type="text" class="form-control" id="filtro-documento" name="documento" 
                                value="<?php echo isset($_GET['documento']) ? $_GET['documento'] : ''; ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filtro-municipio">Município:</label>
                            <input type="text" class="form-control" id="filtro-municipio" name="municipio" 
                                value="<?php echo isset($_GET['municipio']) ? $_GET['municipio'] : ''; ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filtro-uf">UF:</label>
                            <select class="form-control" id="filtro-uf" name="uf">
                                <option value="">Todos</option>
                                <?php
                                $ufs = ['AC', 'AL', 'AM', 'AP', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MG', 'MS', 'MT', 
                                       'PA', 'PB', 'PE', 'PI', 'PR', 'RJ', 'RN', 'RO', 'RR', 'RS', 'SC', 'SE', 'SP', 'TO'];
                                foreach ($ufs as $uf) {
                                    $selected = isset($_GET['uf']) && $_GET['uf'] === $uf ? 'selected' : '';
                                    echo "<option value=\"{$uf}\" {$selected}>{$uf}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filtro-modalidade">Modalidade:</label>
                            <select class="form-control" id="filtro-modalidade" name="modalidade">
                                <option value="">Todas</option>
                                <?php
                                // Buscar modalidades diretamente no banco - com debug
                                $sql = "SELECT MODCOD, MODDES FROM CADMOD ORDER BY MODDES";
                                try {
                                    $conn = getConnection(); // Certifique-se de que esta função está disponível
                                    if ($conn) {
                                        $stmt = $conn->prepare($sql);
                                        $stmt->execute();
                                        $modalidades = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                        
                                        echo "<!-- DEBUG: Encontradas " . count($modalidades) . " modalidades -->";
                                        
                                        if ($modalidades && count($modalidades) > 0) {
                                            foreach ($modalidades as $modalidade) {
                                                $selected = isset($_GET['modalidade']) && $_GET['modalidade'] == $modalidade['MODCOD'] ? 'selected' : '';
                                                echo "<option value=\"{$modalidade['MODCOD']}\" {$selected}>{$modalidade['MODDES']}</option>";
                                            }
                                        } else {
                                            echo "<option value=\"\" disabled>Nenhuma modalidade encontrada</option>";
                                        }
                                    } else {
                                        echo "<option value=\"\" disabled>Erro de conexão com o banco</option>";
                                    }
                                } catch (Exception $e) {
                                    echo "<option value=\"\" disabled>Erro: " . htmlspecialchars($e->getMessage()) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                        <a href="?page=clientes" class="btn btn-secondary">
                            <i class="fas fa-eraser"></i> Limpar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Cartão de Resultados -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Clientes Cadastrados</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="tabela-clientes" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Tipo</th>
                            <th>CPF/CNPJ</th>
                            <th>Razão Social</th>
                            <th>Nome Fantasia</th>
                            <th>Município</th>
                            <th>UF</th>
                            <th>Modalidade</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
// Obter parâmetros de paginação
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$itensPorPagina = 10;

// Inicializar consulta base
$sqlBase = "FROM CADCLI c LEFT JOIN CADMOD m ON c.CLIMOD = m.MODCOD";
$whereConditions = [];
$whereParams = [];

// Construir condições de filtro
if (isset($_GET['codigo']) && $_GET['codigo'] !== '') {
    $whereConditions[] = "c.CLICOD = '" . $_GET['codigo'] . "'"; // Valor inserido diretamente para fins de teste
}

if (isset($_GET['tipo']) && $_GET['tipo'] !== '') {
    $whereConditions[] = "c.CLITIP = '" . $_GET['tipo'] . "'"; // Valor inserido diretamente para fins de teste
}

if (isset($_GET['razao']) && $_GET['razao'] !== '') {
    $whereConditions[] = "c.CLIRAZ LIKE '%" . $_GET['razao'] . "%'"; // Valor inserido diretamente para fins de teste
}

if (isset($_GET['documento']) && $_GET['documento'] !== '') {
    $whereConditions[] = "c.CLIDOC LIKE '%" . $_GET['documento'] . "%'"; // Valor inserido diretamente para fins de teste
}

if (isset($_GET['municipio']) && $_GET['municipio'] !== '') {
    $whereConditions[] = "c.CLIMUN LIKE '%" . $_GET['municipio'] . "%'"; // Valor inserido diretamente para fins de teste
}

if (isset($_GET['uf']) && $_GET['uf'] !== '') {
    $whereConditions[] = "c.CLIEST = '" . $_GET['uf'] . "'"; // Valor inserido diretamente para fins de teste
}

if (isset($_GET['modalidade']) && $_GET['modalidade'] !== '') {
    $whereConditions[] = "c.CLIMOD = '" . $_GET['modalidade'] . "'"; // Valor inserido diretamente para fins de teste
}

// Adicionar cláusula WHERE se houver condições
$whereClause = '';
if (count($whereConditions) > 0) {
    $whereClause = " WHERE " . implode(' AND ', $whereConditions);
}

// Consulta para contar o total de registros
$sqlCount = "SELECT COUNT(*) as total " . $sqlBase . $whereClause;

// Depuração
echo "<!-- DEBUG COUNT SQL: " . $sqlCount . " -->";

$resultCount = fetchOne($sqlCount);
$total = $resultCount ? $resultCount['total'] : 0;

// Calcular total de páginas
$totalPaginas = $total > 0 ? ceil($total / $itensPorPagina) : 1;

// Ajustar página atual se necessário
if ($pagina > $totalPaginas) {
    $pagina = $totalPaginas;
}
if ($pagina < 1) {
    $pagina = 1;
}

// Calcular offset para paginação
$offset = ($pagina - 1) * $itensPorPagina;

// Consulta com paginação
$sql = "SELECT c.*, m.MODDES " . $sqlBase . $whereClause . 
       " ORDER BY c.CLICOD DESC OFFSET " . $offset . " ROWS FETCH NEXT " . $itensPorPagina . " ROWS ONLY";

// Depuração
echo "<!-- DEBUG FINAL SQL: " . $sql . " -->";

// Executar a consulta
$clientes = fetchAll($sql);

// Verificar se há resultados
if ($clientes && count($clientes) > 0) {
    foreach ($clientes as $cliente) {
        echo "<tr>";
        echo "<td>{$cliente['CLICOD']}</td>";
        echo "<td>" . ($cliente['CLITIP'] === 'F' ? 'Física' : 'Jurídica') . "</td>";
        echo "<td>" . formatDocument($cliente['CLIDOC'], $cliente['CLITIP']) . "</td>";
        echo "<td>{$cliente['CLIRAZ']}</td>";
        echo "<td>" . ($cliente['CLIFAN'] ?? '') . "</td>";
        echo "<td>" . ($cliente['CLIMUN'] ?? '') . "</td>";
        echo "<td>" . ($cliente['CLIEST'] ?? '') . "</td>";
        echo "<td>" . ($cliente['MODDES'] ?? '') . "</td>";
        echo "<td>
            <div class='btn-group'>
                <a href='?page=clientes/visualizar&id={$cliente['CLICOD']}' class='btn btn-sm btn-info'>
                    <i class='fas fa-eye'></i>
                </a>
                <a href='?page=clientes/form&id={$cliente['CLICOD']}' class='btn btn-sm btn-primary'>
                    <i class='fas fa-edit'></i>
                </a>
                <button type='button' class='btn btn-sm btn-danger' 
                    onclick='confirmarExclusao({$cliente['CLICOD']}, \"{$cliente['CLIRAZ']}\")'>
                    <i class='fas fa-trash'></i>
                </button>
            </div>
        </td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='9' class='text-center'>Nenhum cliente encontrado</td></tr>";
}

// Calcular valores para a informação de paginação
$inicio = $total > 0 ? $offset + 1 : 0;
$fim = min($offset + $itensPorPagina, $total);
?>
                    </tbody>
                </table>
            </div>
            
            <!-- Paginação -->
            <div class="row mt-3">
                <div class="col-md-6">
                    <p>
                        <?php
                        if ($total > 0) {
                            $inicio = $offset + 1;
                            $fim = min($offset + $itensPorPagina, $total);
                            echo "Exibindo $inicio a $fim de $total registros";
                        } else {
                            echo "0 registros encontrados";
                        }
                        ?>
                    </p>
                </div>
                <div class="col-md-6">
                    <?php if ($totalPaginas > 1): ?>
                        <nav aria-label="Paginação">
                            <ul class="pagination justify-content-end">
                                <!-- Botão Anterior -->
                                <li class="page-item <?php echo $pagina <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="<?php echo $pagina <= 1 ? '#' : '?page=clientes&pagina=' . ($pagina - 1) . '&' . http_build_query(array_filter($_GET, function($key) { return $key !== 'page' && $key !== 'pagina'; }, ARRAY_FILTER_USE_KEY)); ?>" tabindex="-1">
                                        Anterior
                                    </a>
                                </li>
                                
                                <!-- Páginas numéricas -->
                                <?php
                                $inicio = max(1, $pagina - 2);
                                $fim = min($totalPaginas, $inicio + 4);
                                
                                if ($fim - $inicio < 4 && $inicio > 1) {
                                    $inicio = max(1, $fim - 4);
                                }
                                
                                for ($i = $inicio; $i <= $fim; $i++) {
                                    $active = $i == $pagina ? 'active' : '';
                                    $queryParams = array_filter($_GET, function($key) { 
                                        return $key !== 'page' && $key !== 'pagina'; 
                                    }, ARRAY_FILTER_USE_KEY);
                                    
                                    echo "<li class='page-item $active'>";
                                    echo "<a class='page-link' href='?page=clientes&pagina=$i&" . http_build_query($queryParams) . "'>$i</a>";
                                    echo "</li>";
                                }
                                ?>
                                
                                <!-- Botão Próximo -->
                                <li class="page-item <?php echo $pagina >= $totalPaginas ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="<?php echo $pagina >= $totalPaginas ? '#' : '?page=clientes&pagina=' . ($pagina + 1) . '&' . http_build_query(array_filter($_GET, function($key) { return $key !== 'page' && $key !== 'pagina'; }, ARRAY_FILTER_USE_KEY)); ?>">
                                        Próximo
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Exclusão -->
<div class="modal fade" id="modal-confirma-exclusao" tabindex="-1" aria-labelledby="modal-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-label">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <p>Deseja realmente excluir o cliente <strong id="nome-cliente-exclusao"></strong>?</p>
                <p class="text-danger">Esta ação não poderá ser desfeita.</p>
            </div>
            <div class="modal-footer">
                <form action="api.php" method="post" id="form-excluir">
                    <input type="hidden" name="action" value="excluir_cliente">
                    <input type="hidden" name="id" id="id-cliente-exclusao">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Excluir</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Função para confirmar exclusão
    function confirmarExclusao(id, nome) {
        document.getElementById('nome-cliente-exclusao').textContent = nome;
        document.getElementById('id-cliente-exclusao').value = id;
        
        const modal = new bootstrap.Modal(document.getElementById('modal-confirma-exclusao'));
        modal.show();
    }
    
    // Configurar formulário de exclusão
    document.getElementById('form-excluir').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const id = document.getElementById('id-cliente-exclusao').value;
        const formData = new FormData(this);
        
        // Enviar requisição
        fetch('api.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Fechar modal
                bootstrap.Modal.getInstance(document.getElementById('modal-confirma-exclusao')).hide();
                
                // Exibir mensagem de sucesso
                alert(data.message);
                
                // Recarregar página
                window.location.reload();
            } else {
                alert(data.message || 'Erro ao excluir cliente.');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao excluir cliente.');
        });
    });
</script>