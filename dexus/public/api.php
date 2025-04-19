<?php
/**
 * Dexus - Sistema de Gestão
 * API para interação AJAX
 */

// Verificar se é uma chamada direta
if (!defined('BASE_PATH')) {
    // Definir caminho base
    define('BASE_PATH', dirname(__DIR__));
    
    // Carregar configurações
    require_once BASE_PATH . '/app/config.php';
    
    // Carregar funções utilitárias
    require_once BASE_PATH . '/app/functions.php';
    
    // Carregar conexão com o banco
    require_once BASE_PATH . '/app/database.php';
}

// Obter a ação solicitada
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Resposta padrão
$response = [
    'success' => false,
    'message' => 'Ação não especificada'
];

// Processar a ação
switch ($action) {
    // ============= DASHBOARD =============
    case 'dashboard_stats':
        // Estatísticas do Dashboard
        $response = getDashboardStats();
        break;
        
    case 'recent_os':
        // OS Recentes
        $response = getRecentOS();
        break;
        
    case 'recent_clientes':
        // Clientes Recentes
        $response = getRecentClientes();
        break;
    
    // ============= CLIENTES =============
    case 'listar_clientes':
        // Listar clientes
        $response = listarClientes();
        break;
        
    case 'obter_cliente':
        // Obter dados de um cliente específico
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        if ($id) {
            $response = obterCliente($id);
        } else {
            $response = ['success' => false, 'message' => 'ID do cliente não fornecido'];
        }
        break;
        
    case 'salvar_cliente':
        // Salvar (inserir/atualizar) cliente
        $data = getRequestData();
        $response = salvarCliente($data);
        break;
        
    case 'excluir_cliente':
        // Excluir cliente
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        if ($id) {
            $response = excluirCliente($id);
        } else {
            $response = ['success' => false, 'message' => 'ID do cliente não fornecido'];
        }
        break;
        
    case 'verificar_cliente_uso':
        // Verificar se um cliente está em uso
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        if ($id) {
            $response = verificarClienteUso($id);
        } else {
            $response = ['success' => false, 'message' => 'ID do cliente não fornecido'];
        }
        break;
    
    // Adicionar mais casos conforme necessário para as outras entidades
    
    default:
        $response = ['success' => false, 'message' => 'Ação desconhecida: ' . $action];
        break;
}

// Retornar resposta como JSON
header('Content-Type: application/json');
echo json_encode($response);

// ================================ FUNÇÕES DE DASHBOARD ================================

/**
 * Obtém estatísticas para o dashboard
 * @return array Estatísticas do dashboard
 */
function getDashboardStats() {
    // Inicializar resposta
    $stats = [
        'success' => true,
        'totalClientes' => 0,
        'osMes' => 0,
        'osPendentes' => 0,
        'osNaoFaturadas' => 0,
        'osMonthly' => [
            'labels' => [],
            'values' => []
        ],
        'modalidades' => [
            'labels' => [],
            'values' => []
        ]
    ];
    
    // Obter total de clientes
    $sql = "SELECT COUNT(*) as total FROM clientes";
    $result = fetchOne($sql);
    if ($result) {
        $stats['totalClientes'] = $result['total'];
    }
    
    // Obter OS do mês atual
    $mesAtual = date('m');
    $anoAtual = date('Y');
    $sql = "SELECT COUNT(*) as total FROM ordens_servico WHERE MONTH(OSDATA) = :mes AND YEAR(OSDATA) = :ano";
    $result = fetchOne($sql, [':mes' => $mesAtual, ':ano' => $anoAtual]);
    if ($result) {
        $stats['osMes'] = $result['total'];
    }
    
    // Obter OS pendentes (não enviadas)
    $sql = "SELECT COUNT(*) as total FROM ordens_servico WHERE OSENV = 'N'";
    $result = fetchOne($sql);
    if ($result) {
        $stats['osPendentes'] = $result['total'];
    }
    
    // Obter OS não faturadas
    $sql = "SELECT COUNT(*) as total FROM relacao_os WHERE RELOSFAT = 'N'";
    $result = fetchOne($sql);
    if ($result) {
        $stats['osNaoFaturadas'] = $result['total'];
    }
    
    // Obter OS por mês (últimos 6 meses)
    $labels = [];
    $values = [];
    
    for ($i = 5; $i >= 0; $i--) {
        $mes = date('m', strtotime("-$i months"));
        $ano = date('Y', strtotime("-$i months"));
        $nomeMes = date('F', strtotime("-$i months"));
        
        // Traduzir nome do mês para português
        $nomesMesesPtBr = [
            'January' => 'Janeiro',
            'February' => 'Fevereiro',
            'March' => 'Março',
            'April' => 'Abril',
            'May' => 'Maio',
            'June' => 'Junho',
            'July' => 'Julho',
            'August' => 'Agosto',
            'September' => 'Setembro',
            'October' => 'Outubro',
            'November' => 'Novembro',
            'December' => 'Dezembro'
        ];
        
        $nomeMesPtBr = $nomesMesesPtBr[$nomeMes];
        
        $labels[] = $nomeMesPtBr;
        
        $sql = "SELECT COUNT(*) as total FROM ordens_servico WHERE MONTH(OSDATA) = :mes AND YEAR(OSDATA) = :ano";
        $result = fetchOne($sql, [':mes' => $mes, ':ano' => $ano]);
        
        $values[] = $result ? $result['total'] : 0;
    }
    
    $stats['osMonthly']['labels'] = $labels;
    $stats['osMonthly']['values'] = $values;
    
    // Obter distribuição por modalidade
    $sql = "SELECT m.MODDES as modalidade, COUNT(o.OSNUM) as total 
            FROM ordens_servico o
            JOIN modalidades m ON o.OSMODCOD = m.MODCOD
            GROUP BY m.MODCOD
            ORDER BY total DESC
            LIMIT 5";
    
    $result = fetchAll($sql);
    
    if ($result) {
        $modalidadesLabels = [];
        $modalidadesValues = [];
        
        foreach ($result as $row) {
            $modalidadesLabels[] = $row['modalidade'];
            $modalidadesValues[] = $row['total'];
        }
        
        $stats['modalidades']['labels'] = $modalidadesLabels;
        $stats['modalidades']['values'] = $modalidadesValues;
    }
    
    return $stats;
}

/**
 * Obtém as OS mais recentes
 * @return array Lista de OS recentes
 */
function getRecentOS() {
    $sql = "SELECT o.OSNUM, o.OSDATA, c.CLIRAZ, s.SERDES
            FROM ordens_servico o
            JOIN clientes c ON o.OSCLICOD = c.CLICOD
            JOIN servicos s ON o.OSSERCOD = s.SERCOD
            ORDER BY o.OSDATA DESC, o.OSNUM DESC
            LIMIT 5";
    
    $result = fetchAll($sql);
    
    if ($result === false) {
        return ['success' => false, 'message' => 'Erro ao buscar OS recentes'];
    }
    
    return ['success' => true, 'os' => $result];
}

/**
 * Obtém os clientes mais recentes
 * @return array Lista de clientes recentes
 */
function getRecentClientes() {
    $sql = "SELECT CLICOD, CLIRAZ, CLIMUN
            FROM clientes
            ORDER BY CLICOD DESC
            LIMIT 5";
    
    $result = fetchAll($sql);
    
    if ($result === false) {
        return ['success' => false, 'message' => 'Erro ao buscar clientes recentes'];
    }
    
    return ['success' => true, 'clientes' => $result];
}

// ================================ FUNÇÕES DE CLIENTES ================================

/**
 * Lista clientes com paginação e filtros
 * @return array Lista de clientes
 */
function listarClientes() {
    // Obter parâmetros de filtro e paginação
    $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    $itensPorPagina = isset($_GET['itensPorPagina']) ? (int)$_GET['itensPorPagina'] : 10;
    
    // Verificar limites
    if ($pagina < 1) $pagina = 1;
    if ($itensPorPagina < 5) $itensPorPagina = 5;
    if ($itensPorPagina > 100) $itensPorPagina = 100;
    
    // Calcular offset
    $offset = ($pagina - 1) * $itensPorPagina;
    
    // Montar filtros
    $filtros = [];
    $params = [];
    $options = [];
    
    // Filtro de código
    if (isset($_GET['codigo']) && $_GET['codigo'] !== '') {
        $filtros['CLICOD'] = $_GET['codigo'];
        $options['CLICOD'] = 'exact';
    }
    
    // Filtro de tipo
    if (isset($_GET['tipo']) && $_GET['tipo'] !== '') {
        $filtros['CLITIP'] = $_GET['tipo'];
        $options['CLITIP'] = 'exact';
    }
    
    // Filtro de razão social
    if (isset($_GET['razao']) && $_GET['razao'] !== '') {
        $filtros['CLIRAZ'] = $_GET['razao'];
        $options['CLIRAZ'] = 'like';
    }
    
    // Filtro de documento
    if (isset($_GET['documento']) && $_GET['documento'] !== '') {
        $filtros['CLIDOC'] = $_GET['documento'];
        $options['CLIDOC'] = 'like';
    }
    
    // Filtro de município
    if (isset($_GET['municipio']) && $_GET['municipio'] !== '') {
        $filtros['CLIMUN'] = $_GET['municipio'];
        $options['CLIMUN'] = 'like';
    }
    
    // Filtro de UF
    if (isset($_GET['uf']) && $_GET['uf'] !== '') {
        $filtros['CLIEST'] = $_GET['uf'];
        $options['CLIEST'] = 'exact';
    }
    
    // Filtro de modalidade
    if (isset($_GET['modalidade']) && $_GET['modalidade'] !== '') {
        $filtros['CLIMOD'] = $_GET['modalidade'];
        $options['CLIMOD'] = 'exact';
    }
    
    // Construir cláusula WHERE
    $whereClause = buildWhereClause($filtros, $params, $options);
    
    // Contar total de registros
    $sqlCount = "SELECT COUNT(*) as total FROM clientes WHERE $whereClause";
    $resultCount = fetchOne($sqlCount, $params);
    $total = $resultCount ? $resultCount['total'] : 0;
    
    // Calcular total de páginas
    $totalPaginas = ceil($total / $itensPorPagina);
    
    // Ajustar página atual se necessário
    if ($pagina > $totalPaginas && $totalPaginas > 0) {
        $pagina = $totalPaginas;
        $offset = ($pagina - 1) * $itensPorPagina;
    }
    
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
    
    if ($clientes === false) {
        return ['success' => false, 'message' => 'Erro ao buscar clientes'];
    }
    
    // Calcular informações de paginação
    $inicio = $total > 0 ? $offset + 1 : 0;
    $fim = min($offset + $itensPorPagina, $total);
    
    return [
        'success' => true,
        'clientes' => $clientes,
        'paginaAtual' => $pagina,
        'totalPaginas' => $totalPaginas,
        'itensPorPagina' => $itensPorPagina,
        'total' => $total,
        'inicio' => $inicio,
        'fim' => $fim
    ];
}

/**
 * Obtém dados de um cliente específico
 * @param int $id ID do cliente
 * @return array Dados do cliente
 */
function obterCliente($id) {
    $sql = "SELECT c.*, m.MODDES 
            FROM clientes c
            LEFT JOIN modalidades m ON c.CLIMOD = m.MODCOD
            WHERE c.CLICOD = :id";
    
    $cliente = fetchOne($sql, [':id' => $id]);
    
    if ($cliente === false) {
        return ['success' => false, 'message' => 'Erro ao buscar dados do cliente'];
    }
    
    if (!$cliente) {
        return ['success' => false, 'message' => 'Cliente não encontrado'];
    }
    
    return ['success' => true, 'cliente' => $cliente];
}

/**
 * Salva (insere/atualiza) um cliente
 * @param array $data Dados do cliente
 * @return array Resultado da operação
 */
function salvarCliente($data) {
    // Verificar se é inserção ou atualização
    $id = isset($data['CLICOD']) ? $data['CLICOD'] : null;
    
    // Validar dados
    if (!isset($data['CLITIP']) || empty($data['CLITIP'])) {
        return ['success' => false, 'message' => 'O tipo de pessoa é obrigatório'];
    }
    
    if (!isset($data['CLIRAZ']) || empty($data['CLIRAZ'])) {
        return ['success' => false, 'message' => 'A razão social é obrigatória'];
    }
    
    if (!isset($data['CLIDOC']) || empty($data['CLIDOC'])) {
        return ['success' => false, 'message' => 'O CPF/CNPJ é obrigatório'];
    }
    
    // Preparar dados para inserção/atualização
    $clienteData = [
        'CLITIP' => $data['CLITIP'],
        'CLIRAZ' => $data['CLIRAZ'],
        'CLIDOC' => $data['CLIDOC'],
        'CLIFAN' => isset($data['CLIFAN']) ? $data['CLIFAN'] : null,
        'CLIMUN' => isset($data['CLIMUN']) ? $data['CLIMUN'] : null,
        'CLIEST' => isset($data['CLIEST']) ? $data['CLIEST'] : null,
        'CLIMOD' => isset($data['CLIMOD']) ? $data['CLIMOD'] : null,
        'CLIRES' => isset($data['CLIRES']) ? $data['CLIRES'] : null,
        'CLIVAL' => isset($data['CLIVAL']) ? formatMoneyToDB($data['CLIVAL']) : null,
        'CLIEOS' => isset($data['CLIEOS']) ? $data['CLIEOS'] : null,
        'CLIENF' => isset($data['CLIENF']) ? $data['CLIENF'] : null
    ];
    
    if ($id) {
        // Atualização
        $result = update('clientes', $clienteData, 'CLICOD = :id', [':id' => $id]);
        
        if ($result === false) {
            return ['success' => false, 'message' => 'Erro ao atualizar cliente'];
        }
        
        return ['success' => true, 'message' => 'Cliente atualizado com sucesso', 'id' => $id];
    } else {
        // Inserção
        $result = insert('clientes', $clienteData);
        
        if ($result === false) {
            return ['success' => false, 'message' => 'Erro ao inserir cliente'];
        }
        
        return ['success' => true, 'message' => 'Cliente inserido com sucesso', 'id' => $result];
    }
}

/**
 * Exclui um cliente
 * @param int $id ID do cliente
 * @return array Resultado da operação
 */
function excluirCliente($id) {
    // Verificar se o cliente está em uso
    $resultado = verificarClienteUso($id);
    
    if (!$resultado['success']) {
        return $resultado;
    }
    
    if (!$resultado['canDelete']) {
        return ['success' => false, 'message' => $resultado['message']];
    }
    
    // Excluir cliente
    $result = delete('clientes', 'CLICOD = :id', [':id' => $id]);
    
    if ($result === false) {
        return ['success' => false, 'message' => 'Erro ao excluir cliente'];
    }
    
    if ($result === 0) {
        return ['success' => false, 'message' => 'Cliente não encontrado'];
    }
    
    return ['success' => true, 'message' => 'Cliente excluído com sucesso'];
}

/**
 * Verifica se um cliente está em uso
 * @param int $id ID do cliente
 * @return array Resultado da verificação
 */
function verificarClienteUso($id) {
    // Verificar se o cliente está vinculado a alguma OS
    $sql = "SELECT COUNT(*) as total FROM ordens_servico WHERE OSCLICOD = :id";
    $result = fetchOne($sql, [':id' => $id]);
    
    if ($result === false) {
        return ['success' => false, 'message' => 'Erro ao verificar uso do cliente'];
    }
    
    if ($result['total'] > 0) {
        return [
            'success' => true,
            'canDelete' => false,
            'message' => 'Este cliente não pode ser excluído porque está vinculado a ' . $result['total'] . ' ordem(ns) de serviço.'
        ];
    }
    
    return ['success' => true, 'canDelete' => true];
}

// Adicionar ao arquivo api.php, no switch de ações

// ============= CLIENTES =============
case 'listar_clientes':
    // Listar clientes
    $response = listarClientes();
    break;
    
case 'obter_cliente':
    // Obter dados de um cliente específico
    $id = isset($_GET['id']) ? $_GET['id'] : null;
    if ($id) {
        $response = obterCliente($id);
    } else {
        $response = ['success' => false, 'message' => 'ID do cliente não fornecido'];
    }
    break;
    
case 'salvar_cliente':
    // Salvar (inserir/atualizar) cliente
    $data = getRequestData();
    $response = salvarCliente($data);
    break;
    
case 'excluir_cliente':
    // Excluir cliente
    $id = isset($_GET['id']) ? $_GET['id'] : null;
    if ($id) {
        $response = excluirCliente($id);
    } else {
        $response = ['success' => false, 'message' => 'ID do cliente não fornecido'];
    }
    break;
    
case 'verificar_cliente_uso':
    // Verificar se um cliente está em uso
    $id = isset($_GET['id']) ? $_GET['id'] : null;
    if ($id) {
        $response = verificarClienteUso($id);
    } else {
        $response = ['success' => false, 'message' => 'ID do cliente não fornecido'];
    }
    break;
    
case 'consultar_documento':
    // Consultar dados de um documento (CPF/CNPJ)
    $data = getRequestData();
    if (isset($data['documento']) && isset($data['tipo'])) {
        $response = consultarDocumento($data['documento'], $data['tipo']);
    } else {
        $response = ['success' => false, 'message' => 'Parâmetros inválidos'];
    }
    break;

// Adicionar após o switch case

// ================================ FUNÇÕES DE CLIENTES ================================

/**
 * Lista clientes com paginação e filtros
 * @return array Lista de clientes
 */
function listarClientes() {
    // Obter parâmetros de filtro e paginação
    $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    $itensPorPagina = isset($_GET['itensPorPagina']) ? (int)$_GET['itensPorPagina'] : 10;
    
    // Verificar limites
    if ($pagina < 1) $pagina = 1;
    if ($itensPorPagina < 5) $itensPorPagina = 5;
    if ($itensPorPagina > 100) $itensPorPagina = 100;
    
    // Calcular offset
    $offset = ($pagina - 1) * $itensPorPagina;
    
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
    $sqlCount = "SELECT COUNT(*) as total FROM CADCLI c WHERE $whereClause";
    $resultCount = fetchOne($sqlCount, $params);
    $total = $resultCount ? $resultCount['total'] : 0;
    
    // Calcular total de páginas
    $totalPaginas = ceil($total / $itensPorPagina);
    
    // Ajustar página atual se necessário
    if ($pagina > $totalPaginas && $totalPaginas > 0) {
        $pagina = $totalPaginas;
        $offset = ($pagina - 1) * $itensPorPagina;
    }
    
    // Buscar clientes
    $sql = "SELECT c.*, m.MODDES 
            FROM CADCLI c
            LEFT JOIN CADMOD m ON c.CLIMOD = m.MODCOD
            WHERE $whereClause
            ORDER BY c.CLICOD DESC
            OFFSET :offset ROWS FETCH NEXT :limit ROWS ONLY";
    
    $params[':offset'] = $offset;
    $params[':limit'] = $itensPorPagina;
    
    $clientes = fetchAll($sql, $params);
    
    if ($clientes === false) {
        return ['success' => false, 'message' => 'Erro ao buscar clientes'];
    }
    
    // Calcular informações de paginação
    $inicio = $total > 0 ? $offset + 1 : 0;
    $fim = min($offset + $itensPorPagina, $total);
    
    return [
        'success' => true,
        'clientes' => $clientes,
        'paginaAtual' => $pagina,
        'totalPaginas' => $totalPaginas,
        'itensPorPagina' => $itensPorPagina,
        'total' => $total,
        'inicio' => $inicio,
        'fim' => $fim
    ];
}

/**
 * Obtém dados de um cliente específico
 * @param int $id ID do cliente
 * @return array Dados do cliente
 */
function obterCliente($id) {
    $sql = "SELECT c.*, m.MODDES 
            FROM CADCLI c
            LEFT JOIN CADMOD m ON c.CLIMOD = m.MODCOD
            WHERE c.CLICOD = :id";
    
    $cliente = fetchOne($sql, [':id' => $id]);
    
    if ($cliente === false) {
        return ['success' => false, 'message' => 'Erro ao buscar dados do cliente'];
    }
    
    if (!$cliente) {
        return ['success' => false, 'message' => 'Cliente não encontrado'];
    }
    
    return ['success' => true, 'cliente' => $cliente];
}

/**
 * Salva (insere/atualiza) um cliente
 * @param array $data Dados do cliente
 * @return array Resultado da operação
 */
function salvarCliente($data) {
    // Verificar se é inserção ou atualização
    $id = isset($data['CLICOD']) ? $data['CLICOD'] : null;
    
    // Validar dados
    if (!isset($data['CLITIP']) || empty($data['CLITIP'])) {
        return ['success' => false, 'message' => 'O tipo de pessoa é obrigatório'];
    }
    
    if (!isset($data['CLIRAZ']) || empty($data['CLIRAZ'])) {
        return ['success' => false, 'message' => 'A razão social é obrigatória'];
    }
    
    if (!isset($data['CLIDOC']) || empty($data['CLIDOC'])) {
        return ['success' => false, 'message' => 'O CPF/CNPJ é obrigatório'];
    }
    
    // Validar formato do CPF/CNPJ conforme o tipo
    $documento = $data['CLIDOC'];
    $tipo = $data['CLITIP'];
    
    if ($tipo === 'F') {
        if (!validateCPF($documento)) {
            return ['success' => false, 'message' => 'CPF inválido'];
        }
    } else if ($tipo === 'J') {
        if (!validateCNPJ($documento)) {
            return ['success' => false, 'message' => 'CNPJ inválido'];
        }
    }
    
    // Preparar dados para inserção/atualização
    $clienteData = [
        'CLITIP' => $data['CLITIP'],
        'CLIRAZ' => $data['CLIRAZ'],
        'CLIDOC' => $data['CLIDOC'],
        'CLIFAN' => isset($data['CLIFAN']) ? $data['CLIFAN'] : null,
        'CLIMUN' => isset($data['CLIMUN']) ? $data['CLIMUN'] : null,
        'CLIEST' => isset($data['CLIEST']) ? $data['CLIEST'] : null,
        'CLIMOD' => isset($data['CLIMOD']) ? $data['CLIMOD'] : null,
        'CLIRES' => isset($data['CLIRES']) ? $data['CLIRES'] : null,
        'CLIVAL' => isset($data['CLIVAL']) ? formatMoneyToDB($data['CLIVAL']) : null,
        'CLIEOS' => isset($data['CLIEOS']) ? $data['CLIEOS'] : null,
        'CLIENF' => isset($data['CLIENF']) ? $data['CLIENF'] : null
    ];
    
    if ($id) {
        // Atualização
        $result = update('CADCLI', $clienteData, 'CLICOD = :id', [':id' => $id]);
        
        if ($result === false) {
            return ['success' => false, 'message' => 'Erro ao atualizar cliente'];
        }
        
        return ['success' => true, 'message' => 'Cliente atualizado com sucesso', 'id' => $id];
    } else {
        // Inserção
        $result = insert('CADCLI', $clienteData);
        
        if ($result === false) {
            return ['success' => false, 'message' => 'Erro ao inserir cliente'];
        }
        
        return ['success' => true, 'message' => 'Cliente inserido com sucesso', 'id' => $result];
    }
}

/**
 * Exclui um cliente
 * @param int $id ID do cliente
 * @return array Resultado da operação
 */
function excluirCliente($id) {
    // Verificar se o cliente está em uso
    $resultado = verificarClienteUso($id);
    
    if (!$resultado['success']) {
        return $resultado;
    }
    
    if (!$resultado['canDelete']) {
        return ['success' => false, 'message' => $resultado['message']];
    }
    
    // Excluir cliente
    $result = delete('CADCLI', 'CLICOD = :id', [':id' => $id]);
    
    if ($result === false) {
        return ['success' => false, 'message' => 'Erro ao excluir cliente'];
    }
    
    if ($result === 0) {
        return ['success' => false, 'message' => 'Cliente não encontrado'];
    }
    
    return ['success' => true, 'message' => 'Cliente excluído com sucesso'];
}

/**
 * Verifica se um cliente está em uso
 * @param int $id ID do cliente
 * @return array Resultado da verificação
 */
function verificarClienteUso($id) {
    // Verificar se o cliente está vinculado a alguma OS
    $sql = "SELECT COUNT(*) as total FROM ORDSER WHERE OSCLICOD = :id";
    $result = fetchOne($sql, [':id' => $id]);
    
    if ($result === false) {
        return ['success' => false, 'message' => 'Erro ao verificar uso do cliente'];
    }
    
    if ($result['total'] > 0) {
        return [
            'success' => true,
            'canDelete' => false,
            'message' => 'Este cliente não pode ser excluído porque está vinculado a ' . $result['total'] . ' ordem(ns) de serviço.'
        ];
    }
    
    return ['success' => true, 'canDelete' => true];
}

/**
 * Consulta dados de um documento (CPF/CNPJ)
 * @param string $documento Número do documento
 * @param string $tipo Tipo do documento (F para CPF, J para CNPJ)
 * @return array Dados consultados
 */
function consultarDocumento($documento, $tipo) {
    // Esta é uma simulação de consulta. Em um ambiente real, você faria uma chamada à API externa
    // como ReceitaWS, SERPRO, ou similar para obter os dados reais.
    
    // Validar documento
    if ($tipo === 'F' && !validateCPF($documento)) {
        return ['success' => false, 'message' => 'CPF inválido'];
    } else if ($tipo === 'J' && !validateCNPJ($documento)) {
        return ['success' => false, 'message' => 'CNPJ inválido'];
    }
    
    // Simulação de resposta para teste
    $respostaSimulada = [
        'success' => true,
        'razaoSocial' => $tipo === 'F' ? 'PESSOA FÍSICA TESTE' : 'EMPRESA TESTE LTDA',
        'nomeFantasia' => $tipo === 'F' ? '' : 'EMPRESA TESTE',
        'municipio' => 'SÃO PAULO',
        'uf' => 'SP'
    ];
    
    return $respostaSimulada;
}