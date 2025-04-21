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
if (empty($action) && isset($_POST['action'])) {
    $action = $_POST['action'];
}

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
        $id = isset($_POST['CLICOD']) ? $_POST['CLICOD'] : null;
        
        // Validar dados
        if (!isset($_POST['CLITIP']) || empty($_POST['CLITIP'])) {
            $response = ['success' => false, 'message' => 'O tipo de pessoa é obrigatório'];
            break;
        }
        
        if (!isset($_POST['CLIRAZ']) || empty($_POST['CLIRAZ'])) {
            $response = ['success' => false, 'message' => 'A razão social é obrigatória'];
            break;
        }
        
        if (!isset($_POST['CLIDOC']) || empty($_POST['CLIDOC'])) {
            $response = ['success' => false, 'message' => 'O CPF/CNPJ é obrigatório'];
            break;
        }
        
        // Preparar dados para inserção/atualização
        $clienteData = [
            'CLITIP' => $_POST['CLITIP'],
            'CLIRAZ' => $_POST['CLIRAZ'],
            'CLIDOC' => $_POST['CLIDOC'],
            'CLIFAN' => isset($_POST['CLIFAN']) ? $_POST['CLIFAN'] : null,
            'CLIMUN' => isset($_POST['CLIMUN']) ? $_POST['CLIMUN'] : null,
            'CLIEST' => isset($_POST['CLIEST']) ? $_POST['CLIEST'] : null,
            'CLIMOD' => isset($_POST['CLIMOD']) ? $_POST['CLIMOD'] : null,
            'CLIRES' => isset($_POST['CLIRES']) ? $_POST['CLIRES'] : null,
            'CLIVAL' => isset($_POST['CLIVAL']) ? formatMoneyToDB($_POST['CLIVAL']) : null,
            'CLIEOS' => isset($_POST['CLIEOS']) ? $_POST['CLIEOS'] : null,
            'CLIENF' => isset($_POST['CLIENF']) ? $_POST['CLIENF'] : null
        ];
        
        if ($id) {
            // Atualização
            $result = update('CADCLI', $clienteData, 'CLICOD = :id', [':id' => $id]);
            
            if ($result === false) {
                $response = ['success' => false, 'message' => 'Erro ao atualizar cliente'];
            } else {
                $response = [
                    'success' => true, 
                    'message' => 'Cliente atualizado com sucesso',
                    'redirect' => '?page=clientes'
                ];
            }
        } else {
            // Inserção - Obter o próximo código disponível
            $sqlNextCode = "SELECT MAX(CLICOD) as max_cod FROM CADCLI";
            $resultNextCode = fetchOne($sqlNextCode);
            $nextCode = 1; // Código padrão inicial
            
            if ($resultNextCode && isset($resultNextCode['max_cod'])) {
                $nextCode = (int)$resultNextCode['max_cod'] + 1;
            }
            
            // Adicionar o código gerado aos dados
            $clienteData['CLICOD'] = $nextCode;
            
            // Inserção
            $result = insert('CADCLI', $clienteData);
            
            if ($result === false) {
                $response = ['success' => false, 'message' => 'Erro ao inserir cliente'];
            } else {
                $response = [
                    'success' => true, 
                    'message' => 'Cliente inserido com sucesso',
                    'redirect' => '?page=clientes'
                ];
            }
        }
        break;
        
    case 'excluir_cliente':
        // Excluir cliente
        $id = isset($_POST['id']) ? $_POST['id'] : (isset($_GET['id']) ? $_GET['id'] : null);
        
        if ($id) {
            // Verificar se o cliente está vinculado a alguma OS
            $sql = "SELECT COUNT(*) as total FROM ORDSER WHERE OSCLICOD = :id";
            $result = fetchOne($sql, [':id' => $id]);
            
            if ($result && $result['total'] > 0) {
                $response = [
                    'success' => false,
                    'message' => 'Este cliente não pode ser excluído porque está vinculado a ' . $result['total'] . ' ordem(ns) de serviço.'
                ];
            } else {
                // Excluir cliente
                $result = delete('CADCLI', 'CLICOD = :id', [':id' => $id]);
                
                if ($result === false) {
                    $response = ['success' => false, 'message' => 'Erro ao excluir cliente'];
                } else {
                    $response = ['success' => true, 'message' => 'Cliente excluído com sucesso'];
                }
            }
        } else {
            $response = ['success' => false, 'message' => 'ID do cliente não fornecido'];
        }
        break;
        
    case 'verificar_cliente_uso':
        // Verificar se um cliente está em uso
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        if ($id) {
            // Verificar se o cliente está vinculado a alguma OS
            $sql = "SELECT COUNT(*) as total FROM ORDSER WHERE OSCLICOD = :id";
            $result = fetchOne($sql, [':id' => $id]);
            
            if ($result === false) {
                $response = ['success' => false, 'message' => 'Erro ao verificar uso do cliente'];
            } else if ($result['total'] > 0) {
                $response = [
                    'success' => true,
                    'canDelete' => false,
                    'message' => 'Este cliente não pode ser excluído porque está vinculado a ' . $result['total'] . ' ordem(ns) de serviço.'
                ];
            } else {
                $response = ['success' => true, 'canDelete' => true];
            }
        } else {
            $response = ['success' => false, 'message' => 'ID do cliente não fornecido'];
        }
        break;
        
    case 'consultar_documento':
        // Consultar dados de um documento (CPF/CNPJ)
        $documento = isset($_POST['documento']) ? $_POST['documento'] : null;
        $tipo = isset($_POST['tipo']) ? $_POST['tipo'] : null;
        
        if ($documento && $tipo) {
            // Simulação de resposta para teste
            $response = [
                'success' => true,
                'razaoSocial' => $tipo === 'F' ? 'PESSOA FÍSICA TESTE' : 'EMPRESA TESTE LTDA',
                'nomeFantasia' => $tipo === 'F' ? '' : 'EMPRESA TESTE',
                'municipio' => 'SÃO PAULO',
                'uf' => 'SP'
            ];
        } else {
            $response = ['success' => false, 'message' => 'Parâmetros inválidos'];
        }
        break;
    

    case 'listar_modalidades':
        // Listar modalidades
        $sql = "SELECT MODCOD, MODDES FROM CADMOD ORDER BY MODCOD";
        $modalidades = fetchAll($sql);
        
        if ($modalidades === false) {
            $response = ['success' => false, 'message' => 'Erro ao buscar modalidades'];
        } else {
            $response = ['success' => true, 'modalidades' => $modalidades ?: []];
        }
        break;
    // Adicionar mais casos para outras entidades conforme necessário
    
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
    $sql = "SELECT COUNT(*) as total FROM CADCLI";
    $result = fetchOne($sql);
    if ($result) {
        $stats['totalClientes'] = $result['total'];
    }
    
    // Obter OS do mês atual
    $mesAtual = date('m');
    $anoAtual = date('Y');
    $sql = "SELECT COUNT(*) as total FROM ORDSER WHERE MONTH(OSDATA) = :mes AND YEAR(OSDATA) = :ano";
    $result = fetchOne($sql, [':mes' => $mesAtual, ':ano' => $anoAtual]);
    if ($result) {
        $stats['osMes'] = $result['total'];
    }
    
    // Obter OS pendentes (não enviadas)
    $sql = "SELECT COUNT(*) as total FROM ORDSER WHERE OSENV = 'N'";
    $result = fetchOne($sql);
    if ($result) {
        $stats['osPendentes'] = $result['total'];
    }
    
    // Obter OS não faturadas
    $sql = "SELECT COUNT(*) as total FROM RELOS WHERE RELOSFAT = 'N'";
    $result = fetchOne($sql);
    if ($result) {
        $stats['osNaoFaturadas'] = $result['total'];
    }
    
    // Simular dados para os gráficos
    $stats['osMonthly']['labels'] = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho'];
    $stats['osMonthly']['values'] = [10, 15, 8, 12, 20, 18];
    
    $stats['modalidades']['labels'] = ['Consultoria', 'Desenvolvimento', 'Suporte', 'Treinamento'];
    $stats['modalidades']['values'] = [40, 25, 20, 15];
    
    return $stats;
}

/**
 * Obtém as OS mais recentes
 * @return array Lista de OS recentes
 */
function getRecentOS() {
    $sql = "SELECT o.OSNUM, o.OSDATA, c.CLIRAZ, s.SERDES
            FROM ORDSER o
            JOIN CADCLI c ON o.OSCLICOD = c.CLICOD
            JOIN CADSER s ON o.OSSERCOD = s.SERCOD
            ORDER BY o.OSDATA DESC, o.OSNUM DESC
            OFFSET 0 ROWS FETCH NEXT 5 ROWS ONLY";
    
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
            FROM CADCLI
            ORDER BY CLICOD DESC
            OFFSET 0 ROWS FETCH NEXT 5 ROWS ONLY";
    
    $result = fetchAll($sql);
    
    if ($result === false) {
        return ['success' => false, 'message' => 'Erro ao buscar clientes recentes'];
    }
    
    return ['success' => true, 'clientes' => $result];
}

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