<!-- Listagem de Consultores -->
<div class="container-fluid">
    <!-- Cabeçalho da Página -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Consultores</h1>
        <a href="?page=consultores/form" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Novo Consultor
        </a>
    </div>

    <!-- Cartão de Pesquisa -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filtros</h6>
        </div>
        <div class="card-body">
            <form id="form-filtro" method="get">
                <input type="hidden" name="page" value="consultores">
                
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="filtro-codigo">Código:</label>
                            <input type="text" class="form-control" id="filtro-codigo" name="codigo" 
                                value="<?php echo isset($_GET['codigo']) ? $_GET['codigo'] : ''; ?>">
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="filtro-nome">Nome:</label>
                            <input type="text" class="form-control" id="filtro-nome" name="nome" 
                                value="<?php echo isset($_GET['nome']) ? $_GET['nome'] : ''; ?>">
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="filtro-atuacao">Atuação:</label>
                            <input type="text" class="form-control" id="filtro-atuacao" name="atuacao" 
                                value="<?php echo isset($_GET['atuacao']) ? $_GET['atuacao'] : ''; ?>">
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                        <a href="?page=consultores" class="btn btn-secondary">
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
            <h6 class="m-0 font-weight-bold text-primary">Consultores Cadastrados</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="tabela-consultores" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Nome</th>
                            <th>Telefone</th>
                            <th>E-mail</th>
                            <th>Atuação</th>
                            <th>Valor Hora</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
// Função para formatar telefone (já que não está disponível)
function formatarTelefone($telefone) {
    // Remover caracteres não numéricos
    $telefone = preg_replace('/\D/', '', $telefone);
    
    // Verificar tamanho
    $length = strlen($telefone);
    
    if ($length === 11) {
        // Celular: (XX) 9XXXX-XXXX
        return sprintf('(%s) %s%s-%s',
            substr($telefone, 0, 2),
            substr($telefone, 2, 1),
            substr($telefone, 3, 4),
            substr($telefone, 7, 4)
        );
    } else if ($length === 10) {
        // Fixo: (XX) XXXX-XXXX
        return sprintf('(%s) %s-%s',
            substr($telefone, 0, 2),
            substr($telefone, 2, 4),
            substr($telefone, 6, 4)
        );
    }
    
    return $telefone;
}

// Obter parâmetros de paginação
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$itensPorPagina = 10;

// Inicializar consulta base
$sqlBase = "FROM CADCON";
$whereConditions = [];
$whereParams = [];

// Construir condições de filtro - aqui é a parte crítica!
if (isset($_GET['codigo']) && $_GET['codigo'] !== '') {
    $whereConditions[] = "CONCOD = '" . $_GET['codigo'] . "'"; // Seguindo o padrão do cadastro de clientes
}

if (isset($_GET['nome']) && $_GET['nome'] !== '') {
    $whereConditions[] = "CONNOM LIKE '%" . $_GET['nome'] . "%'"; // Seguindo o padrão do cadastro de clientes
}

if (isset($_GET['atuacao']) && $_GET['atuacao'] !== '') {
    $whereConditions[] = "CONATU LIKE '%" . $_GET['atuacao'] . "%'"; // Seguindo o padrão do cadastro de clientes
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

// Consulta com paginação - seguindo exatamente o formato do cadastro de clientes
$sql = "SELECT * " . $sqlBase . $whereClause . 
       " ORDER BY CONCOD DESC OFFSET " . $offset . " ROWS FETCH NEXT " . $itensPorPagina . " ROWS ONLY";

// Depuração
echo "<!-- DEBUG FINAL SQL: " . $sql . " -->";

// Executar a consulta
$consultores = fetchAll($sql);

// Verificar se há resultados
if ($consultores && count($consultores) > 0) {
    foreach ($consultores as $consultor) {
        echo "<tr>";
        echo "<td>{$consultor['CONCOD']}</td>";
        echo "<td>{$consultor['CONNOM']}</td>";
        echo "<td>" . (isset($consultor['CONTEL']) ? formatarTelefone($consultor['CONTEL']) : '') . "</td>";
        echo "<td>" . (isset($consultor['CONEMA']) ? $consultor['CONEMA'] : '') . "</td>";
        echo "<td>" . (isset($consultor['CONATU']) ? $consultor['CONATU'] : '') . "</td>";
        echo "<td>" . (isset($consultor['CONVAL']) ? formatMoney($consultor['CONVAL']) : '') . "</td>";
        echo "<td>
            <div class='btn-group'>
                <a href='?page=consultores/visualizar&id={$consultor['CONCOD']}' class='btn btn-sm btn-info'>
                    <i class='fas fa-eye'></i>
                </a>
                <a href='?page=consultores/form&id={$consultor['CONCOD']}' class='btn btn-sm btn-primary'>
                    <i class='fas fa-edit'></i>
                </a>
                <button type='button' class='btn btn-sm btn-danger' 
                    onclick='confirmarExclusao({$consultor['CONCOD']}, \"{$consultor['CONNOM']}\")'>
                    <i class='fas fa-trash'></i>
                </button>
            </div>
        </td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='7' class='text-center'>Nenhum consultor encontrado</td></tr>";
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
                                    <a class="page-link" href="<?php echo $pagina <= 1 ? '#' : '?page=consultores&pagina=' . ($pagina - 1) . '&' . http_build_query(array_filter($_GET, function($key) { return $key !== 'page' && $key !== 'pagina'; }, ARRAY_FILTER_USE_KEY)); ?>" tabindex="-1">
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
                                    echo "<a class='page-link' href='?page=consultores&pagina=$i&" . http_build_query($queryParams) . "'>$i</a>";
                                    echo "</li>";
                                }
                                ?>
                                
                                <!-- Botão Próximo -->
                                <li class="page-item <?php echo $pagina >= $totalPaginas ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="<?php echo $pagina >= $totalPaginas ? '#' : '?page=consultores&pagina=' . ($pagina + 1) . '&' . http_build_query(array_filter($_GET, function($key) { return $key !== 'page' && $key !== 'pagina'; }, ARRAY_FILTER_USE_KEY)); ?>">
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
                <p>Deseja realmente excluir o consultor <strong id="nome-consultor-exclusao"></strong>?</p>
                <p class="text-danger">Esta ação não poderá ser desfeita.</p>
            </div>
            <div class="modal-footer">
                <form action="api.php" method="post" id="form-excluir">
                    <input type="hidden" name="action" value="excluir_consultor">
                    <input type="hidden" name="id" id="id-consultor-exclusao">
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
        document.getElementById('nome-consultor-exclusao').textContent = nome;
        document.getElementById('id-consultor-exclusao').value = id;
        
        const modal = new bootstrap.Modal(document.getElementById('modal-confirma-exclusao'));
        modal.show();
    }
    
    // Configurar formulário de exclusão
    document.getElementById('form-excluir').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const id = document.getElementById('id-consultor-exclusao').value;
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
                alert(data.message || 'Erro ao excluir consultor.');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao excluir consultor.');
        });
    });
</script>
