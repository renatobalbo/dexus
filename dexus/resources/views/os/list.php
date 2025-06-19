<!-- Listagem de Ordens de Serviço -->
<div class="container-fluid">
    <!-- Cabeçalho da Página -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Ordens de Serviço</h1>
        <a href="?page=os/form" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Nova OS
        </a>
    </div>

    <!-- Cartão de Pesquisa -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filtros</h6>
        </div>
        <div class="card-body">
            <form id="form-filtro" method="get">
                <input type="hidden" name="page" value="os">
                
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="filtro-numero">Número:</label>
                            <input type="text" class="form-control" id="filtro-numero" name="numero" 
                                value="<?php echo isset($_GET['numero']) ? $_GET['numero'] : ''; ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filtro-data-inicio">Data Início:</label>
                            <input type="text" class="form-control date-mask" id="filtro-data-inicio" name="data_inicio" 
                                value="<?php echo isset($_GET['data_inicio']) ? $_GET['data_inicio'] : ''; ?>" placeholder="DD/MM/AAAA">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filtro-data-fim">Data Fim:</label>
                            <input type="text" class="form-control date-mask" id="filtro-data-fim" name="data_fim" 
                                value="<?php echo isset($_GET['data_fim']) ? $_GET['data_fim'] : ''; ?>" placeholder="DD/MM/AAAA">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="filtro-cliente">Cliente:</label>
                            <select class="form-control" id="filtro-cliente" name="cliente">
                                <option value="">Todos</option>
                                <?php
                                // Buscar clientes
                                $sqlClientes = "SELECT CLICOD, CLIRAZ FROM CADCLI ORDER BY CLIRAZ";
                                $clientes = fetchAll($sqlClientes);
                                
                                if ($clientes) {
                                    foreach ($clientes as $cliente) {
                                        $selected = isset($_GET['cliente']) && $_GET['cliente'] == $cliente['CLICOD'] ? 'selected' : '';
                                        echo "<option value=\"{$cliente['CLICOD']}\" $selected>{$cliente['CLIRAZ']}</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="filtro-servico">Serviço:</label>
                            <select class="form-control" id="filtro-servico" name="servico">
                                <option value="">Todos</option>
                                <?php
                                // Buscar serviços
                                $sqlServicos = "SELECT SERCOD, SERDES FROM CADSER ORDER BY SERDES";
                                $servicos = fetchAll($sqlServicos);
                                
                                if ($servicos) {
                                    foreach ($servicos as $servico) {
                                        $selected = isset($_GET['servico']) && $_GET['servico'] == $servico['SERCOD'] ? 'selected' : '';
                                        echo "<option value=\"{$servico['SERCOD']}\" $selected>{$servico['SERDES']}</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="filtro-consultor">Consultor:</label>
                            <select class="form-control" id="filtro-consultor" name="consultor">
                                <option value="">Todos</option>
                                <?php
                                // Buscar consultores
                                $sqlConsultores = "SELECT CONCOD, CONNOM FROM CADCON ORDER BY CONNOM";
                                $consultores = fetchAll($sqlConsultores);
                                
                                if ($consultores) {
                                    foreach ($consultores as $consultor) {
                                        $selected = isset($_GET['consultor']) && $_GET['consultor'] == $consultor['CONCOD'] ? 'selected' : '';
                                        echo "<option value=\"{$consultor['CONCOD']}\" $selected>{$consultor['CONNOM']}</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="filtro-enviada">OS Enviada:</label>
                            <select class="form-control" id="filtro-enviada" name="enviada">
                                <option value="">Todos</option>
                                <option value="S" <?php echo isset($_GET['enviada']) && $_GET['enviada'] === 'S' ? 'selected' : ''; ?>>Sim</option>
                                <option value="N" <?php echo isset($_GET['enviada']) && $_GET['enviada'] === 'N' ? 'selected' : ''; ?>>Não</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                        <a href="?page=os" class="btn btn-secondary">
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
            <h6 class="m-0 font-weight-bold text-primary">Ordens de Serviço Cadastradas</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="tabela-os" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Número</th>
                            <th>Data</th>
                            <th>Cliente</th>
                            <th>Serviço</th>
                            <th>Consultor</th>
                            <th>Enviada</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
// Obter parâmetros de paginação
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$itensPorPagina = 10;

// Inicializar consulta base
$sqlBase = "FROM ORDSER o
           LEFT JOIN CADCLI c ON o.OSCLICOD = c.CLICOD
           LEFT JOIN CADSER s ON o.OSSERCOD = s.SERCOD
           LEFT JOIN CADCON con ON o.OSCONCOD = con.CONCOD";

$whereConditions = [];
$whereParams = [];
$index = 0;

// Filtro de número
if (isset($_GET['numero']) && $_GET['numero'] !== '') {
    $paramName = ':osnum_' . $index++;
    $whereConditions[] = "o.OSNUM = $paramName";
    $whereParams[$paramName] = $_GET['numero'];
}

// Filtro de data início
if (isset($_GET['data_inicio']) && $_GET['data_inicio'] !== '') {
    $paramName = ':data_inicio_' . $index++;
    $whereConditions[] = "o.OSDATA >= $paramName";
    $whereParams[$paramName] = formatDateToDB($_GET['data_inicio']);
}

// Filtro de data fim
if (isset($_GET['data_fim']) && $_GET['data_fim'] !== '') {
    $paramName = ':data_fim_' . $index++;
    $whereConditions[] = "o.OSDATA <= $paramName";
    $whereParams[$paramName] = formatDateToDB($_GET['data_fim']);
}

// Filtro de cliente
if (isset($_GET['cliente']) && $_GET['cliente'] !== '') {
    $paramName = ':cliente_' . $index++;
    $whereConditions[] = "o.OSCLICOD = $paramName";
    $whereParams[$paramName] = $_GET['cliente'];
}

// Filtro de serviço
if (isset($_GET['servico']) && $_GET['servico'] !== '') {
    $paramName = ':servico_' . $index++;
    $whereConditions[] = "o.OSSERCOD = $paramName";
    $whereParams[$paramName] = $_GET['servico'];
}

// Filtro de consultor
if (isset($_GET['consultor']) && $_GET['consultor'] !== '') {
    $paramName = ':consultor_' . $index++;
    $whereConditions[] = "o.OSCONCOD = $paramName";
    $whereParams[$paramName] = $_GET['consultor'];
}

// Filtro de OS enviada
if (isset($_GET['enviada']) && $_GET['enviada'] !== '') {
    $paramName = ':enviada_' . $index++;
    $whereConditions[] = "o.OSENV = $paramName";
    $whereParams[$paramName] = $_GET['enviada'];
}

// Adicionar cláusula WHERE se houver condições
$whereClause = '';
if (count($whereConditions) > 0) {
    $whereClause = " WHERE " . implode(' AND ', $whereConditions);
}

// Consulta para contar o total de registros
$sqlCount = "SELECT COUNT(*) as total " . $sqlBase . $whereClause;

$resultCount = fetchOne($sqlCount, $whereParams);
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
$sql = "SELECT o.OSNUM, o.OSDATA, o.OSENV, c.CLIRAZ, s.SERDES, con.CONNOM " . $sqlBase . $whereClause . 
       " ORDER BY o.OSNUM DESC OFFSET " . $offset . " ROWS FETCH NEXT " . $itensPorPagina . " ROWS ONLY";

// Executar a consulta
$ordens = fetchAll($sql, $whereParams);

// Verificar se há resultados
if ($ordens && count($ordens) > 0) {
    foreach ($ordens as $os) {
        echo "<tr>";
        echo "<td>{$os['OSNUM']}</td>";
        echo "<td>" . formatDate($os['OSDATA']) . "</td>";
        echo "<td>{$os['CLIRAZ']}</td>";
        echo "<td>{$os['SERDES']}</td>";
        echo "<td>{$os['CONNOM']}</td>";
        echo "<td>" . ($os['OSENV'] === 'S' ? '<span class="badge bg-success text-white">Sim</span>' : '<span class="badge bg-danger text-white">Não</span>') . "</td>";
        echo "<td>
            <div class='btn-group'>
                <a href='?page=os/visualizar&id={$os['OSNUM']}' class='btn btn-sm btn-info'>
                    <i class='fas fa-eye'></i>
                </a>";
        // Botões de editar e excluir apenas se não estiver enviada
        if ($os['OSENV'] !== 'S') {
            echo "<a href='?page=os/form&id={$os['OSNUM']}' class='btn btn-sm btn-primary'>
                    <i class='fas fa-edit'></i>
                </a>
                <button type='button' class='btn btn-sm btn-danger' 
                    onclick='confirmarExclusao({$os['OSNUM']}, \"{$os['OSNUM']}\")'>
                    <i class='fas fa-trash'></i>
                </button>";
        }
        echo "<a href='javascript:void(0);' onclick='gerarPDF({$os['OSNUM']})' class='btn btn-sm btn-danger'>
                <i class='fas fa-file-pdf'></i>
            </a>";
        // Botão de enviar e-mail apenas se não estiver enviada
        if ($os['OSENV'] !== 'S') {
            echo "<a href='javascript:void(0);' onclick='enviarEmail({$os['OSNUM']})' class='btn btn-sm btn-success'>
                    <i class='fas fa-envelope'></i>
                </a>";
        }
        echo "</div>
        </td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='7' class='text-center'>Nenhuma ordem de serviço encontrada</td></tr>";
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
                                    <a class="page-link" href="<?php echo $pagina <= 1 ? '#' : '?page=os&pagina=' . ($pagina - 1) . '&' . http_build_query(array_filter($_GET, function($key) { return $key !== 'page' && $key !== 'pagina'; }, ARRAY_FILTER_USE_KEY)); ?>" tabindex="-1">
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
                                    echo "<a class='page-link' href='?page=os&pagina=$i&" . http_build_query($queryParams) . "'>$i</a>";
                                    echo "</li>";
                                }
                                ?>
                                
                                <!-- Botão Próximo -->
                                <li class="page-item <?php echo $pagina >= $totalPaginas ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="<?php echo $pagina >= $totalPaginas ? '#' : '?page=os&pagina=' . ($pagina + 1) . '&' . http_build_query(array_filter($_GET, function($key) { return $key !== 'page' && $key !== 'pagina'; }, ARRAY_FILTER_USE_KEY)); ?>">
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
                <p>Deseja realmente excluir a Ordem de Serviço <strong id="numero-os-exclusao"></strong>?</p>
                <p class="text-danger">Esta ação não poderá ser desfeita.</p>
            </div>
            <div class="modal-footer">
                <form action="api.php" method="post" id="form-excluir">
                    <input type="hidden" name="action" value="excluir_os">
                    <input type="hidden" name="id" id="id-os-exclusao">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Excluir</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Função para confirmar exclusão
    function confirmarExclusao(id, numero) {
        document.getElementById('numero-os-exclusao').textContent = numero;
        document.getElementById('id-os-exclusao').value = id;
        
        const modal = new bootstrap.Modal(document.getElementById('modal-confirma-exclusao'));
        modal.show();
    }
    
    // Configurar formulário de exclusão
    document.getElementById('form-excluir').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const id = document.getElementById('id-os-exclusao').value;
        const formData = new FormData(this);
        
        // Verificar se a OS pode ser excluída
        fetch(`api.php?action=verificar_os_modificavel&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.canModify) {
                    // Enviar requisição de exclusão
                    return fetch('api.php', {
                        method: 'POST',
                        body: formData
                    });
                } else {
                    // Exibir mensagem de erro se não puder excluir
                    bootstrap.Modal.getInstance(document.getElementById('modal-confirma-exclusao')).hide();
                    alert(data.message || 'Esta ordem de serviço não pode ser excluída porque já foi enviada.');
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
                    alert(data.message || 'Erro ao excluir ordem de serviço.');
                }
            })
            .catch(error => {
                if (error.message !== 'Não pode excluir') {
                    console.error('Erro:', error);
                    alert('Erro ao processar requisição.');
                }
            });
    });
    
    // Função para gerar PDF da OS
    function gerarPDF(id) {
        if (!id) return;
        
        // Exibir loader
        const loader = showLoader('Gerando PDF...');
        
        fetch(`api.php?action=gerar_os_pdf&id=${id}`)
            .then(response => response.json())
            .then(data => {
                hideLoader(loader);
                
                if (data.success && data.pdfUrl) {
                    // Abrir o PDF em uma nova aba
                    window.open(data.pdfUrl, '_blank');
                } else {
                    alert(data.message || 'Não foi possível gerar o PDF.');
                }
            })
            .catch(error => {
                hideLoader(loader);
                console.error('Erro:', error);
                alert('Erro ao gerar o PDF.');
            });
    }
    
    // Função para enviar OS por e-mail
    function enviarEmail(id) {
        if (!id) return;
        
        if (!confirm('Confirma o envio desta OS por e-mail?')) {
            return;
        }
        
        // Exibir loader
        const loader = showLoader('Enviando e-mail...');
        
        fetch(`api.php?action=enviar_os_email&id=${id}`)
            .then(response => response.json())
            .then(data => {
                hideLoader(loader);
                
                if (data.success) {
                    alert(data.message);
                    // Recarregar a página para atualizar o status
                    window.location.reload();
                } else {
                    alert(data.message || 'Não foi possível enviar o e-mail.');
                }
            })
            .catch(error => {
                hideLoader(loader);
                console.error('Erro:', error);
                alert('Erro ao enviar o e-mail.');
            });
    }
</script>