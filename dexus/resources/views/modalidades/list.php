<!-- Listagem de Modalidades -->
<div class="container-fluid">
    <!-- Cabeçalho da Página -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Modalidades</h1>
        <a href="?page=modalidades/form" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Nova Modalidade
        </a>
    </div>

    <!-- Cartão de Pesquisa -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filtros</h6>
        </div>
        <div class="card-body">
            <form id="form-filtro" method="get">
                <input type="hidden" name="page" value="modalidades">
                
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="filtro-codigo">Código:</label>
                            <input type="text" class="form-control" id="filtro-codigo" name="codigo" 
                                value="<?php echo isset($_GET['codigo']) ? $_GET['codigo'] : ''; ?>">
                        </div>
                    </div>
                    <div class="col-md-10">
                        <div class="form-group">
                            <label for="filtro-descricao">Descrição:</label>
                            <input type="text" class="form-control" id="filtro-descricao" name="descricao" 
                                value="<?php echo isset($_GET['descricao']) ? $_GET['descricao'] : ''; ?>">
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                        <a href="?page=modalidades" class="btn btn-secondary">
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
            <h6 class="m-0 font-weight-bold text-primary">Modalidades Cadastradas</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="tabela-modalidades" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Descrição</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
// Obter parâmetros de paginação
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$itensPorPagina = 10;

// Inicializar consulta base
$sqlBase = "FROM CADMOD";
$whereConditions = [];
$whereParams = [];

// Construir condições de filtro
if (isset($_GET['codigo']) && $_GET['codigo'] !== '') {
    $whereConditions[] = "MODCOD = '" . $_GET['codigo'] . "'";
}

if (isset($_GET['descricao']) && $_GET['descricao'] !== '') {
    $whereConditions[] = "MODDES LIKE '%" . $_GET['descricao'] . "%'";
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
$sql = "SELECT * " . $sqlBase . $whereClause . 
       " ORDER BY MODCOD DESC OFFSET " . $offset . " ROWS FETCH NEXT " . $itensPorPagina . " ROWS ONLY";

// Depuração
echo "<!-- DEBUG FINAL SQL: " . $sql . " -->";

// Executar a consulta
$modalidades = fetchAll($sql);

// Verificar se há resultados
if ($modalidades && count($modalidades) > 0) {
    foreach ($modalidades as $modalidade) {
        echo "<tr>";
        echo "<td>{$modalidade['MODCOD']}</td>";
        echo "<td>{$modalidade['MODDES']}</td>";
        echo "<td>
            <div class='btn-group'>
                <a href='?page=modalidades/visualizar&id={$modalidade['MODCOD']}' class='btn btn-sm btn-info'>
                    <i class='fas fa-eye'></i>
                </a>
                <a href='?page=modalidades/form&id={$modalidade['MODCOD']}' class='btn btn-sm btn-primary'>
                    <i class='fas fa-edit'></i>
                </a>
                <button type='button' class='btn btn-sm btn-danger' 
                    onclick='confirmarExclusao({$modalidade['MODCOD']}, \"{$modalidade['MODDES']}\")'>
                    <i class='fas fa-trash'></i>
                </button>
            </div>
        </td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='3' class='text-center'>Nenhuma modalidade encontrada</td></tr>";
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
                                    <a class="page-link" href="<?php echo $pagina <= 1 ? '#' : '?page=modalidades&pagina=' . ($pagina - 1) . '&' . http_build_query(array_filter($_GET, function($key) { return $key !== 'page' && $key !== 'pagina'; }, ARRAY_FILTER_USE_KEY)); ?>" tabindex="-1">
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
                                    echo "<a class='page-link' href='?page=modalidades&pagina=$i&" . http_build_query($queryParams) . "'>$i</a>";
                                    echo "</li>";
                                }
                                ?>
                                
                                <!-- Botão Próximo -->
                                <li class="page-item <?php echo $pagina >= $totalPaginas ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="<?php echo $pagina >= $totalPaginas ? '#' : '?page=modalidades&pagina=' . ($pagina + 1) . '&' . http_build_query(array_filter($_GET, function($key) { return $key !== 'page' && $key !== 'pagina'; }, ARRAY_FILTER_USE_KEY)); ?>">
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
                <p>Deseja realmente excluir a modalidade <strong id="nome-modalidade-exclusao"></strong>?</p>
                <p class="text-danger">Esta ação não poderá ser desfeita.</p>
            </div>
            <div class="modal-footer">
                <form action="api.php" method="post" id="form-excluir">
                    <input type="hidden" name="action" value="excluir_modalidade">
                    <input type="hidden" name="id" id="id-modalidade-exclusao">
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
        document.getElementById('nome-modalidade-exclusao').textContent = nome;
        document.getElementById('id-modalidade-exclusao').value = id;
        
        const modal = new bootstrap.Modal(document.getElementById('modal-confirma-exclusao'));
        modal.show();
    }
    
    // Configurar formulário de exclusão
    document.getElementById('form-excluir').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const id = document.getElementById('id-modalidade-exclusao').value;
        const formData = new FormData(this);
        
        // Verificar se a modalidade pode ser excluída
        fetch(`api.php?action=verificar_modalidade_uso&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.canDelete) {
                    // Enviar requisição de exclusão
                    return fetch('api.php', {
                        method: 'POST',
                        body: formData
                    });
                } else {
                    // Exibir mensagem de erro se não puder excluir
                    bootstrap.Modal.getInstance(document.getElementById('modal-confirma-exclusao')).hide();
                    alert(data.message || 'Esta modalidade não pode ser excluída porque está em uso.');
                    throw new Error('Não pode excluir');
                }
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
                    alert(data.message || 'Erro ao excluir modalidade.');
                }
            })
            .catch(error => {
                if (error.message !== 'Não pode excluir') {
                    console.error('Erro:', error);
                    alert('Erro ao processar requisição.');
                }
            });
    });
</script>