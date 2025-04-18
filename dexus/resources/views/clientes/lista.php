<!-- Listagem de Clientes -->
<div class="container-fluid">
    <!-- Cabeçalho e botão de Novo -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Clientes</h1>
        <a href="?page=clientes/form" class="btn btn-sm btn-primary shadow-sm">
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
                                // Buscar modalidades no banco
                                $sql = "SELECT MODCOD, MODDES FROM modalidades ORDER BY MODDES";
                                $modalidades = fetchAll($sql);
                                
                                if ($modalidades) {
                                    foreach ($modalidades as $modalidade) {
                                        $selected = isset($_GET['modalidade']) && $_GET['modalidade'] == $modalidade['MODCOD'] ? 'selected' : '';
                                        echo "<option value=\"{$modalidade['MODCOD']}\" {$selected}>{$modalidade['MODDES']}</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
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
                        
                        // Montar filtros
                        $filtros = [];
                        $params = [];
                        $options = [];
                        
                        // Filtro de código
                        if (isset($_GET['codigo']) && $_GET['codigo'] !== '') {
                            $filtros['c.CLICOD'] = $_GET['codigo'];
                            $options['c.CLICOD'] = 'exact';
                        }
                        
                        // Filtro de tipo
                        if (isset($_GET['tipo']) && $_GET['tipo'] !== '') {
                            $filtros['c.CLITIP'] = $_GET['tipo'];
                            $options['c.CLITIP'] = 'exact';
                        }
                        
                        // Filtro de razão social
                        if (isset($_GET['razao']) && $_GET['razao'] !== '') {
                            $filtros['c.CLIRAZ'] = $_GET['razao'];
                            $options['c.CLIRAZ'] = 'like';
                        }
                        
                        // Filtro de documento
                        if (isset($_GET['documento']) && $_GET['documento'] !== '') {
                            $filtros['c.CLIDOC'] = $_GET['documento'];
                            $options['c.CLIDOC'] = 'like';
                        }
                        
                        // Filtro de município
                        if (isset($_GET['municipio']) && $_GET['municipio'] !== '') {
                            $filtros['c.CLIMUN'] = $_GET['municipio'];
                            $options['c.CLIMUN'] = 'like';
                        }
                        
                        // Filtro de UF
                        if (isset($_GET['uf']) && $_GET['uf'] !== '') {
                            $filtros['c.CLIEST'] = $_GET['uf'];
                            $options['c.CLIEST'] = 'exact';
                        }
                        
                        // Filtro de modalidade
                        if (isset($_GET['modalidade']) && $_GET['modalidade'] !== '') {
                            $filtros['c.CLIMOD'] = $_GET['modalidade'];
                            $options['c.CLIMOD'] = 'exact';
                        }
                        
                        // Construir cláusula WHERE
                        $whereClause = buildWhereClause($filtros, $params, $options);
                        
                        // Contar total de registros
                        $sqlCount = "SELECT COUNT(*) as total FROM clientes c WHERE $whereClause";
                        $resultCount = fetchOne($sqlCount, $params);
                        $total = $resultCount ? $resultCount['total'] : 0;
                        
                        // Calcular total de páginas
                        $totalPaginas = ceil($total / $itensPorPagina);
                        
                        // Ajustar página atual se necessário
                        if ($pagina > $totalPaginas && $totalPaginas > 0) {
                            $pagina = $totalPaginas;
                        }
                        
                        // Calcular offset
                        $offset = ($pagina - 1) * $itensPorPagina;
                        
                        // Buscar clientes
                        $sql = "SELECT c.*, m.MODDES 
                                FROM clientes c
                                LEFT JOIN modalidades m ON c.CLIMOD = m.MODCOD
                                WHERE $whereClause
                                ORDER BY c.CLICOD DESC
                                LIMIT :offset, :limit";
                        
                        $params[':offset'] = $offset;
                        $params[':limit'] = $itensPorPagina;
                        
                        $clientes = fetchAll($sql, $params);
                        
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
        
        // Verificar se o cliente pode ser excluído
        verificarClienteUso(id)
            .then(response => {
                if (response.success && response.canDelete) {
                    // Excluir cliente
                    return excluirCliente(id);
                } else {
                    // Exibir mensagem de erro
                    throw new Error(response.message || 'Este cliente não pode ser excluído porque está vinculado a uma ou mais ordens de serviço.');
                }
            })
            .then(response => {
                if (response.success) {
                    // Fechar modal
                    bootstrap.Modal.getInstance(document.getElementById('modal-confirma-exclusao')).hide();
                    
                    // Exibir mensagem de sucesso
                    showAlert(response.message, 'success');
                    
                    // Recarregar página
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    throw new Error(response.message || 'Erro ao excluir cliente.');
                }
            })
            .catch(error => {
                // Fechar modal
                bootstrap.Modal.getInstance(document.getElementById('modal-confirma-exclusao')).hide();
                
                // Exibir mensagem de erro
                showAlert(error.message, 'danger');
            });
    });
</script>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filtro-tipo">Tipo:</label>
                            <select class="form-control" id="filtro-tipo" name="tipo">
                                <option value="">Todos</option>
                                <option value="F" <?php