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
        // Verificar se já existe cliente com este documento
        if (!$exists) {
            // Limpar caracteres não numéricos do documento
            $docLimpo = preg_replace('/\D/', '', $clienteData['CLIDOC']);
            
            // Verificar se já existe no banco
            $sql = "SELECT COUNT(*) as total FROM CADCLI WHERE CLIDOC = :doc";
            $resultDoc = fetchOne($sql, [':doc' => $docLimpo]);
            
            if ($resultDoc && $resultDoc['total'] > 0) {
                $response = [
                    'success' => false, 
                    'message' => 'Já existe um cliente cadastrado com este CPF/CNPJ!'
                ];
                break; // Sai do switch sem executar o resto do código
            }
            
            // Garante que o documento está limpo para salvar
            $clienteData['CLIDOC'] = $docLimpo;
        }

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
            //'CLICOD' => $id, // Usar o código já informado no formulário
            'CLITIP' => $_POST['CLITIP'],
            'CLIRAZ' => $_POST['CLIRAZ'],
            'CLIDOC' => preg_replace('/\D/', '', $_POST['CLIDOC']), // Remove caracteres não numéricos
            'CLIFAN' => isset($_POST['CLIFAN']) ? $_POST['CLIFAN'] : null,
            'CLIMUN' => isset($_POST['CLIMUN']) ? $_POST['CLIMUN'] : null,
            'CLIEST' => isset($_POST['CLIEST']) ? $_POST['CLIEST'] : null,
            'CLIMOD' => isset($_POST['CLIMOD']) && $_POST['CLIMOD'] !== '' ? (int)$_POST['CLIMOD'] : null, // Converter para inteiro
            'CLIRES' => isset($_POST['CLIRES']) ? $_POST['CLIRES'] : null,
            'CLIVAL' => isset($_POST['CLIVAL']) ? formatMoneyToDB($_POST['CLIVAL']) : null,
            'CLIEOS' => isset($_POST['CLIEOS']) ? $_POST['CLIEOS'] : null,
            'CLIENF' => isset($_POST['CLIENF']) ? $_POST['CLIENF'] : null
        ];
        
        // Verificar se o registro já existe (para distinguir INSERT de UPDATE)
        $sql = "SELECT COUNT(*) as total FROM CADCLI WHERE CLICOD = :id";
        $existsResult = fetchOne($sql, [':id' => $id]);
        $exists = $existsResult && $existsResult['total'] > 0;
        
        if ($exists) {
            // Atualização
            $result = update('CADCLI', $clienteData, 'CLICOD = :id', [':id' => $id]);
            
            if ($result === false) {
                $response = [
                    'success' => false, 
                    'message' => 'Não foi possível atualizar o cliente. Por favor, verifique os dados e tente novamente.'
                ];
            } else {
                $response = [
                    'success' => true, 
                    'message' => 'Cliente "' . $clienteData['CLIRAZ'] . '" atualizado com sucesso!',
                    'redirect' => '?page=clientes'
                ];
            }
        } else {
            // Inserção - remova o CLICOD
            unset($clienteData['CLICOD']);
            
            $result = insert('CADCLI', $clienteData);
            
            if ($result === false) {
                $response = [
                    'success' => false, 
                    'message' => 'Não foi possível cadastrar o cliente. Por favor, verifique os dados e tente novamente.'
                ];
            } else {
                $response = [
                    'success' => true, 
                    'message' => 'Cliente "' . $clienteData['CLIRAZ'] . '" cadastrado com sucesso!',
                    'redirect' => '?page=clientes'
                ];
            }
        }
        break;

    case 'verificar_documento':
        // Verificar se um documento já existe no banco
        $doc = isset($_GET['doc']) ? preg_replace('/\D/', '', $_GET['doc']) : null;
        
        if ($doc) {
            $sql = "SELECT COUNT(*) as total FROM CADCLI WHERE CLIDOC = :doc";
            $result = fetchOne($sql, [':doc' => $doc]);
            
            $exists = $result && $result['total'] > 0;
            $response = [
                'success' => true,
                'exists' => $exists
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'Documento não informado'
            ];
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
                    $response = ['success' => true, 'message' => 'Cliente excluído com sucesso!'];
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

// ============= CONSULTORES =============
    case 'listar_consultores':
        // Listar consultores
        $response = listarConsultores();
        break;
        
    case 'obter_consultor':
        // Obter dados de um consultor específico
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        if ($id) {
            $response = obterConsultor($id);
        } else {
            $response = ['success' => false, 'message' => 'ID do consultor não fornecido'];
        }
        break;
        
    case 'salvar_consultor':
        // Salvar (inserir/atualizar) consultor
        $id = isset($_POST['CONCOD']) ? $_POST['CONCOD'] : null;
        
        // Validar dados
        if (!isset($_POST['CONNOM']) || empty($_POST['CONNOM'])) {
            $response = ['success' => false, 'message' => 'O nome do consultor é obrigatório'];
            break;
        }
        
        // Verificar se já existe consultor com este nome
        $sql = "SELECT COUNT(*) as total FROM CADCON WHERE CONNOM = :nome";
        $params = [':nome' => $_POST['CONNOM']];
        
        // Se for edição, excluir o registro atual da verificação
        if ($id) {
            $sql .= " AND CONCOD != :id";
            $params[':id'] = $id;
        }
        
        $result = fetchOne($sql, $params);
        
        if ($result && $result['total'] > 0) {
            $response = [
                'success' => false, 
                'message' => 'Já existe um consultor cadastrado com este nome!'
            ];
            break;
        }
        
        // Validar e-mail (se preenchido)
        if (isset($_POST['CONEMA']) && !empty($_POST['CONEMA']) && !filter_var($_POST['CONEMA'], FILTER_VALIDATE_EMAIL)) {
            $response = ['success' => false, 'message' => 'E-mail inválido'];
            break;
        }
        
        // Preparar dados para inserção/atualização
        $consultorData = [
            'CONNOM' => $_POST['CONNOM'],
            'CONTEL' => isset($_POST['CONTEL']) ? preg_replace('/\D/', '', $_POST['CONTEL']) : null, // Remove caracteres não numéricos
            'CONEMA' => isset($_POST['CONEMA']) ? $_POST['CONEMA'] : null,
            'CONATU' => isset($_POST['CONATU']) ? $_POST['CONATU'] : null,
            'CONVAL' => isset($_POST['CONVAL']) ? formatMoneyToDB($_POST['CONVAL']) : null
        ];
        
        // Verificar se o registro já existe (para distinguir INSERT de UPDATE)
        $sql = "SELECT COUNT(*) as total FROM CADCON WHERE CONCOD = :id";
        $existsResult = fetchOne($sql, [':id' => $id]);
        $exists = $existsResult && $existsResult['total'] > 0;
        
        if ($exists) {
            // Atualização
            $result = update('CADCON', $consultorData, 'CONCOD = :id', [':id' => $id]);
            
            if ($result === false) {
                $response = [
                    'success' => false, 
                    'message' => 'Não foi possível atualizar o consultor. Por favor, verifique os dados e tente novamente.'
                ];
            } else {
                $response = [
                    'success' => true, 
                    'message' => 'Consultor "' . $consultorData['CONNOM'] . '" atualizado com sucesso!',
                    'redirect' => '?page=consultores'
                ];
            }
        } else {
            // Inserção - remova o CONCOD
            unset($consultorData['CONCOD']);
            
            $result = insert('CADCON', $consultorData);
            
            if ($result === false) {
                $response = [
                    'success' => false, 
                    'message' => 'Não foi possível cadastrar o consultor. Por favor, verifique os dados e tente novamente.'
                ];
            } else {
                $response = [
                    'success' => true, 
                    'message' => 'Consultor "' . $consultorData['CONNOM'] . '" cadastrado com sucesso!',
                    'redirect' => '?page=consultores'
                ];
            }
        }
        break;
        
    case 'excluir_consultor':
        // Excluir consultor
        $id = isset($_POST['id']) ? $_POST['id'] : (isset($_GET['id']) ? $_GET['id'] : null);
        
        if ($id) {
            // Verificar se o consultor está vinculado a alguma OS
            $sql = "SELECT COUNT(*) as total FROM ORDSER WHERE OSCONCOD = :id";
            $result = fetchOne($sql, [':id' => $id]);
            
            if ($result && $result['total'] > 0) {
                $response = [
                    'success' => false,
                    'message' => 'Este consultor não pode ser excluído porque está vinculado a ' . $result['total'] . ' ordem(ns) de serviço.'
                ];
            } else {
                // Excluir consultor
                $result = delete('CADCON', 'CONCOD = :id', [':id' => $id]);
                
                if ($result === false) {
                    $response = ['success' => false, 'message' => 'Erro ao excluir consultor'];
                } else {
                    $response = ['success' => true, 'message' => 'Consultor excluído com sucesso!'];
                }
            }
        } else {
            $response = ['success' => false, 'message' => 'ID do consultor não fornecido'];
        }
        break;
        
    case 'verificar_consultor_uso':
        // Verificar se um consultor está em uso
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        if ($id) {
            // Verificar se o consultor está vinculado a alguma OS
            $sql = "SELECT COUNT(*) as total FROM ORDSER WHERE OSCONCOD = :id";
            $result = fetchOne($sql, [':id' => $id]);
            
            if ($result === false) {
                $response = ['success' => false, 'message' => 'Erro ao verificar uso do consultor'];
            } else if ($result['total'] > 0) {
                $response = [
                    'success' => true,
                    'canDelete' => false,
                    'message' => 'Este consultor não pode ser excluído porque está vinculado a ' . $result['total'] . ' ordem(ns) de serviço.'
                ];
            } else {
                $response = ['success' => true, 'canDelete' => true];
            }
        } else {
            $response = ['success' => false, 'message' => 'ID do consultor não fornecido'];
        }
        break;

    case 'verificar_consultor_nome':
        // Verificar se um nome de consultor já existe
        $nome = isset($_GET['nome']) ? $_GET['nome'] : null;
        $id = isset($_GET['id']) ? $_GET['id'] : null; // ID atual para excluir da verificação (em caso de edição)
        
        if ($nome) {
            $sql = "SELECT COUNT(*) as total FROM CADCON WHERE CONNOM = :nome";
            $params = [':nome' => $nome];
            
            // Se for edição, excluir o registro atual da verificação
            if ($id) {
                $sql .= " AND CONCOD != :id";
                $params[':id'] = $id;
            }
            
            $result = fetchOne($sql, $params);
            
            if ($result === false) {
                $response = ['success' => false, 'message' => 'Erro ao verificar nome do consultor'];
            } else {
                $exists = $result['total'] > 0;
                $response = [
                    'success' => true,
                    'exists' => $exists,
                    'message' => $exists ? 'Este nome de consultor já está em uso.' : 'Nome disponível.'
                ];
            }
        } else {
            $response = ['success' => false, 'message' => 'Nome do consultor não fornecido'];
        }
        break;

// ============= SERVIÇOS =============
    case 'listar_servicos':
            // Listar serviços
            $response = listarServicos();
            break;
            
    case 'obter_servico':
        // Obter dados de um serviço específico
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        if ($id) {
            $response = obterServico($id);
        } else {
            $response = ['success' => false, 'message' => 'ID do serviço não fornecido'];
        }
        break;
        
    case 'salvar_servico':
        // Salvar (inserir/atualizar) serviço
        $id = isset($_POST['SERCOD']) ? $_POST['SERCOD'] : null;
        
        // Validar dados
        if (!isset($_POST['SERDES']) || empty($_POST['SERDES'])) {
            $response = ['success' => false, 'message' => 'A descrição do serviço é obrigatória'];
            break;
        }
        
        // Preparar dados para inserção/atualização
        $servicoData = [
            'SERDES' => $_POST['SERDES']
        ];
        
        // Verificar se o registro já existe (para distinguir INSERT de UPDATE)
        $sql = "SELECT COUNT(*) as total FROM CADSER WHERE SERCOD = :id";
        $existsResult = fetchOne($sql, [':id' => $id]);
        $exists = $existsResult && $existsResult['total'] > 0;
        
        // Verificar se a descrição já existe
        $sqlDesc = "SELECT COUNT(*) as total FROM CADSER WHERE SERDES = :desc" . ($exists ? " AND SERCOD <> :id" : "");
        $paramsDesc = [':desc' => $servicoData['SERDES']];
        if ($exists) {
            $paramsDesc[':id'] = $id;
        }
        
        $descResult = fetchOne($sqlDesc, $paramsDesc);
        
        if ($descResult && $descResult['total'] > 0) {
            $response = [
                'success' => false, 
                'message' => 'Já existe um serviço cadastrado com esta descrição!'
            ];
            break;
        }
        
        if ($exists) {
            // Atualização
            $result = update('CADSER', $servicoData, 'SERCOD = :id', [':id' => $id]);
            
            if ($result === false) {
                $response = [
                    'success' => false, 
                    'message' => 'Não foi possível atualizar o serviço. Por favor, verifique os dados e tente novamente.'
                ];
            } else {
                $response = [
                    'success' => true, 
                    'message' => 'Serviço "' . $servicoData['SERDES'] . '" atualizado com sucesso!',
                    'redirect' => '?page=servicos'
                ];
            }
        } else {
            // Inserção - remova o SERCOD
            unset($servicoData['SERCOD']);
            
            $result = insert('CADSER', $servicoData);
            
            if ($result === false) {
                $response = [
                    'success' => false, 
                    'message' => 'Não foi possível cadastrar o serviço. Por favor, verifique os dados e tente novamente.'
                ];
            } else {
                $response = [
                    'success' => true, 
                    'message' => 'Serviço "' . $servicoData['SERDES'] . '" cadastrado com sucesso!',
                    'redirect' => '?page=servicos'
                ];
            }
        }
        break;
        
    case 'excluir_servico':
        // Excluir serviço
        $id = isset($_POST['id']) ? $_POST['id'] : (isset($_GET['id']) ? $_GET['id'] : null);
        
        if ($id) {
            // Verificar se o serviço está vinculado a alguma OS
            $sql = "SELECT COUNT(*) as total FROM ORDSER WHERE OSSERCOD = :id";
            $result = fetchOne($sql, [':id' => $id]);
            
            if ($result && $result['total'] > 0) {
                $response = [
                    'success' => false,
                    'message' => 'Este serviço não pode ser excluído porque está vinculado a ' . $result['total'] . ' ordem(ns) de serviço.'
                ];
            } else {
                // Excluir serviço
                $result = delete('CADSER', 'SERCOD = :id', [':id' => $id]);
                
                if ($result === false) {
                    $response = ['success' => false, 'message' => 'Erro ao excluir serviço'];
                } else {
                    $response = ['success' => true, 'message' => 'Serviço excluído com sucesso!'];
                }
            }
        } else {
            $response = ['success' => false, 'message' => 'ID do serviço não fornecido'];
        }
        break;
        
    case 'verificar_servico_uso':
        // Verificar se um serviço está em uso
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        if ($id) {
            // Verificar se o serviço está vinculado a alguma OS
            $sql = "SELECT COUNT(*) as total FROM ORDSER WHERE OSSERCOD = :id";
            $result = fetchOne($sql, [':id' => $id]);
            
            if ($result === false) {
                $response = ['success' => false, 'message' => 'Erro ao verificar uso do serviço'];
            } else if ($result['total'] > 0) {
                $response = [
                    'success' => true,
                    'canDelete' => false,
                    'message' => 'Este serviço não pode ser excluído porque está vinculado a ' . $result['total'] . ' ordem(ns) de serviço.'
                ];
            } else {
                $response = ['success' => true, 'canDelete' => true];
            }
        } else {
            $response = ['success' => false, 'message' => 'ID do serviço não fornecido'];
        }
        break;
        
    case 'verificar_servico_descricao':
        // Verificar se uma descrição de serviço já existe
        $descricao = isset($_GET['descricao']) ? $_GET['descricao'] : null;
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        
        if ($descricao) {
            // Verificar se a descrição já existe
            $sql = "SELECT COUNT(*) as total FROM CADSER WHERE SERDES = :desc";
            $params = [':desc' => $descricao];
            
            // Se for edição, excluir o próprio registro da verificação
            if ($id) {
                $sql .= " AND SERCOD <> :id";
                $params[':id'] = $id;
            }
            
            $result = fetchOne($sql, $params);
            
            if ($result === false) {
                $response = ['success' => false, 'message' => 'Erro ao verificar descrição'];
            } else {
                $exists = $result['total'] > 0;
                $response = [
                    'success' => true,
                    'exists' => $exists
                ];
            }
        } else {
            $response = ['success' => false, 'message' => 'Descrição não informada'];
        }
        break;

// ============= MODALIDADES =============
    case 'listar_modalidades':
        // Listar modalidades
        $response = listarModalidades();
        break;
        
    case 'obter_modalidade':
        // Obter dados de uma modalidade específica
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        if ($id) {
            $response = obterModalidade($id);
        } else {
            $response = ['success' => false, 'message' => 'ID da modalidade não fornecido'];
        }
        break;
        
    case 'salvar_modalidade':
        // Salvar (inserir/atualizar) modalidade
        $id = isset($_POST['MODCOD']) ? $_POST['MODCOD'] : null;
        
        // Validar dados
        if (!isset($_POST['MODDES']) || empty($_POST['MODDES'])) {
            $response = ['success' => false, 'message' => 'A descrição da modalidade é obrigatória'];
            break;
        }
        
        // Preparar dados para inserção/atualização
        $modalidadeData = [
            'MODDES' => $_POST['MODDES']
        ];
        
        // Verificar se o registro já existe (para distinguir INSERT de UPDATE)
        $sql = "SELECT COUNT(*) as total FROM CADMOD WHERE MODCOD = :id";
        $existsResult = fetchOne($sql, [':id' => $id]);
        $exists = $existsResult && $existsResult['total'] > 0;
        
        // Verificar se já existe outra modalidade com esta descrição
        $sqlDesc = "SELECT COUNT(*) as total FROM CADMOD WHERE MODDES = :desc" . ($exists ? " AND MODCOD <> :id" : "");
        $paramsDesc = [':desc' => $modalidadeData['MODDES']];
        if ($exists) {
            $paramsDesc[':id'] = $id;
        }
        
        $descResult = fetchOne($sqlDesc, $paramsDesc);
        
        if ($descResult && $descResult['total'] > 0) {
            $response = [
                'success' => false, 
                'message' => 'Já existe uma modalidade cadastrada com esta descrição!'
            ];
            break;
        }
        
        if ($exists) {
            // Atualização
            $result = update('CADMOD', $modalidadeData, 'MODCOD = :id', [':id' => $id]);
            
            if ($result === false) {
                $response = [
                    'success' => false, 
                    'message' => 'Não foi possível atualizar a modalidade. Por favor, verifique os dados e tente novamente.'
                ];
            } else {
                $response = [
                    'success' => true, 
                    'message' => 'Modalidade "' . $modalidadeData['MODDES'] . '" atualizada com sucesso!',
                    'redirect' => '?page=modalidades'
                ];
            }
        } else {
            // Inserção - remova o MODCOD
            unset($modalidadeData['MODCOD']);
            
            $result = insert('CADMOD', $modalidadeData);
            
            if ($result === false) {
                $response = [
                    'success' => false, 
                    'message' => 'Não foi possível cadastrar a modalidade. Por favor, verifique os dados e tente novamente.'
                ];
            } else {
                $response = [
                    'success' => true, 
                    'message' => 'Modalidade "' . $modalidadeData['MODDES'] . '" cadastrada com sucesso!',
                    'redirect' => '?page=modalidades'
                ];
            }
        }
        break;
        
    case 'excluir_modalidade':
        // Excluir modalidade
        $id = isset($_POST['id']) ? $_POST['id'] : (isset($_GET['id']) ? $_GET['id'] : null);
        
        if ($id) {
            // Verificar se a modalidade está vinculada a algum cliente
            $sql = "SELECT COUNT(*) as total FROM CADCLI WHERE CLIMOD = :id";
            $result = fetchOne($sql, [':id' => $id]);
            
            if ($result && $result['total'] > 0) {
                $response = [
                    'success' => false,
                    'message' => 'Esta modalidade não pode ser excluída porque está vinculada a ' . $result['total'] . ' cliente(s).'
                ];
            } else {
                // Excluir modalidade
                $result = delete('CADMOD', 'MODCOD = :id', [':id' => $id]);
                
                if ($result === false) {
                    $response = ['success' => false, 'message' => 'Erro ao excluir modalidade'];
                } else {
                    $response = ['success' => true, 'message' => 'Modalidade excluída com sucesso!'];
                }
            }
        } else {
            $response = ['success' => false, 'message' => 'ID da modalidade não fornecido'];
        }
        break;
        
    case 'verificar_modalidade_uso':
        // Verificar se uma modalidade está em uso
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        if ($id) {
            // Verificar se a modalidade está vinculada a algum cliente
            $sql = "SELECT COUNT(*) as total FROM CADCLI WHERE CLIMOD = :id";
            $result = fetchOne($sql, [':id' => $id]);
            
            if ($result === false) {
                $response = ['success' => false, 'message' => 'Erro ao verificar uso da modalidade'];
            } else if ($result['total'] > 0) {
                $response = [
                    'success' => true,
                    'canDelete' => false,
                    'message' => 'Esta modalidade não pode ser excluída porque está vinculada a ' . $result['total'] . ' cliente(s).'
                ];
            } else {
                $response = ['success' => true, 'canDelete' => true];
            }
        } else {
            $response = ['success' => false, 'message' => 'ID da modalidade não fornecido'];
        }
        break;
        
    case 'verificar_modalidade_descricao':
        // Verificar se já existe uma modalidade com esta descrição
        $descricao = isset($_GET['descricao']) ? $_GET['descricao'] : null;
        $codigo = isset($_GET['codigo']) ? $_GET['codigo'] : null;
        
        if ($descricao) {
            $sql = "SELECT COUNT(*) as total FROM CADMOD WHERE MODDES = :descricao";
            $params = [':descricao' => $descricao];
            
            // Se for edição, excluir o próprio registro da verificação
            if ($codigo) {
                $sql .= " AND MODCOD <> :codigo";
                $params[':codigo'] = $codigo;
            }
            
            $result = fetchOne($sql, $params);
            
            $exists = $result && $result['total'] > 0;
            $response = [
                'success' => true,
                'exists' => $exists
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'Descrição não informada'
            ];
        }
        break;

// ============= ORDENS DE SERVIÇO =============
    case 'listar_os':
        // Listar ordens de serviço
        $response = listarOS();
        break;
        
    case 'obter_os':
        // Obter dados de uma OS específica
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        if ($id) {
            $response = obterOS($id);
        } else {
            $response = ['success' => false, 'message' => 'ID da OS não fornecido'];
        }
        break;
        
    case 'salvar_os':
        // Salvar (inserir/atualizar) OS
        $id = isset($_POST['OSNUM']) ? $_POST['OSNUM'] : null;
        
        // Validar dados
        if (!isset($_POST['OSDATA']) || empty($_POST['OSDATA'])) {
            $response = ['success' => false, 'message' => 'A data é obrigatória'];
            break;
        }
        
        if (!isset($_POST['OSCLICOD']) || empty($_POST['OSCLICOD'])) {
            $response = ['success' => false, 'message' => 'O cliente é obrigatório'];
            break;
        }
        
        if (!isset($_POST['OSSERCOD']) || empty($_POST['OSSERCOD'])) {
            $response = ['success' => false, 'message' => 'O serviço é obrigatório'];
            break;
        }
        
        if (!isset($_POST['OSCONCOD']) || empty($_POST['OSCONCOD'])) {
            $response = ['success' => false, 'message' => 'O consultor é obrigatório'];
            break;
        }
        
        // Se for edição, verificar se a OS já foi enviada
        if ($id) {
            $sql = "SELECT OSENV FROM ORDSER WHERE OSNUM = :id";
            $result = fetchOne($sql, [':id' => $id]);
            
            if ($result && $result['OSENV'] === 'S') {
                $response = ['success' => false, 'message' => 'Esta OS já foi enviada e não pode ser alterada'];
                break;
            }
        }
        
        // Preparar dados para inserção/atualização
        $osData = [
            'OSDATA' => formatDateToDB($_POST['OSDATA']),
            'OSCLICOD' => $_POST['OSCLICOD'],
            'OSMODCOD' => isset($_POST['OSMODCOD']) && $_POST['OSMODCOD'] ? $_POST['OSMODCOD'] : null,
            'OSCLIRES' => isset($_POST['OSCLIRES']) ? $_POST['OSCLIRES'] : null,
            'OSSERCOD' => $_POST['OSSERCOD'],
            'OSCONCOD' => $_POST['OSCONCOD'],
            'OSHINI' => isset($_POST['OSHINI']) ? $_POST['OSHINI'] : null,
            'OSHFIM' => isset($_POST['OSHFIM']) ? $_POST['OSHFIM'] : null,
            'OSHDES' => isset($_POST['OSHDES']) ? $_POST['OSHDES'] : null,
            'OSHTRA' => isset($_POST['OSHTRA']) ? $_POST['OSHTRA'] : null,
            'OSHTOT' => isset($_POST['OSHTOT']) ? $_POST['OSHTOT'] : null,
            'OSDET' => isset($_POST['OSDET']) ? $_POST['OSDET'] : null,
            'OSENV' => isset($_POST['OSENV']) ? $_POST['OSENV'] : 'N'
        ];
        
        // Verificar se o registro já existe (para distinguir INSERT de UPDATE)
        $exists = false;
        if ($id) {
            $sql = "SELECT COUNT(*) as total FROM ORDSER WHERE OSNUM = :id";
            $existsResult = fetchOne($sql, [':id' => $id]);
            $exists = $existsResult && $existsResult['total'] > 0;
        }
        
        if ($exists) {
            // Atualização
            $result = update('ORDSER', $osData, 'OSNUM = :id', [':id' => $id]);
            
            if ($result === false) {
                $response = [
                    'success' => false, 
                    'message' => 'Não foi possível atualizar a OS. Por favor, verifique os dados e tente novamente.'
                ];
            } else {
                $response = [
                    'success' => true, 
                    'message' => 'Ordem de Serviço atualizada com sucesso!',
                    'redirect' => '?page=os/visualizar&id=' . $id
                ];
            }
        } else {
            // Inserção - remova o OSNUM
            unset($osData['OSNUM']);
            
            $result = insert('ORDSER', $osData);
            
            if ($result === false) {
                $response = [
                    'success' => false, 
                    'message' => 'Não foi possível cadastrar a OS. Por favor, verifique os dados e tente novamente.'
                ];
            } else {
                // Obter o ID da OS inserida
                $sql = "SELECT MAX(OSNUM) as id FROM ORDSER";
                $idResult = fetchOne($sql);
                $newId = $idResult ? $idResult['id'] : null;
                
                $response = [
                    'success' => true, 
                    'message' => 'Ordem de Serviço cadastrada com sucesso!',
                    'redirect' => '?page=os/visualizar&id=' . $newId
                ];
            }
        }
        break;
        
    case 'excluir_os':
        // Excluir OS
        $id = isset($_POST['id']) ? $_POST['id'] : (isset($_GET['id']) ? $_GET['id'] : null);
        
        if ($id) {
            // Verificar se a OS já foi enviada
            $sql = "SELECT OSENV FROM ORDSER WHERE OSNUM = :id";
            $result = fetchOne($sql, [':id' => $id]);
            
            if ($result && $result['OSENV'] === 'S') {
                $response = [
                    'success' => false,
                    'message' => 'Esta OS não pode ser excluída porque já foi enviada.'
                ];
            } else {
                // Excluir OS
                $result = delete('ORDSER', 'OSNUM = :id', [':id' => $id]);
                
                if ($result === false) {
                    $response = ['success' => false, 'message' => 'Erro ao excluir OS'];
                } else {
                    $response = ['success' => true, 'message' => 'Ordem de Serviço excluída com sucesso!'];
                }
            }
        } else {
            $response = ['success' => false, 'message' => 'ID da OS não fornecido'];
        }
        break;
        
    case 'verificar_os_modificavel':
        // Verificar se uma OS pode ser modificada (ainda não foi enviada)
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        if ($id) {
            // Verificar se a OS já foi enviada
            $sql = "SELECT OSENV FROM ORDSER WHERE OSNUM = :id";
            $result = fetchOne($sql, [':id' => $id]);
            
            if ($result === false) {
                $response = ['success' => false, 'message' => 'Erro ao verificar status da OS'];
            } else if ($result && $result['OSENV'] === 'S') {
                $response = [
                    'success' => true,
                    'canModify' => false,
                    'message' => 'Esta OS não pode ser modificada porque já foi enviada.'
                ];
            } else {
                $response = ['success' => true, 'canModify' => true];
            }
        } else {
            $response = ['success' => false, 'message' => 'ID da OS não fornecido'];
        }
        break;
        
    case 'gerar_os_pdf':
        // Gerar PDF da OS
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        if ($id) {
            // Em um ambiente real, aqui iria gerar um PDF
            // Para este exemplo, simularemos o sucesso da operação
            $pdfUrl = "arquivos/os_" . $id . ".pdf";
            
            $response = [
                'success' => true, 
                'pdfUrl' => $pdfUrl,
                'message' => 'PDF gerado com sucesso!'
            ];
        } else {
            $response = ['success' => false, 'message' => 'ID da OS não fornecido'];
        }
        break;
        
    case 'enviar_os_email':
        // Enviar OS por e-mail
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        if ($id) {
            // Verificar se a OS já foi enviada
            $sql = "SELECT o.OSENV, c.CLIEOS 
                FROM ORDSER o
                JOIN CADCLI c ON o.OSCLICOD = c.CLICOD
                WHERE o.OSNUM = :id";
            $result = fetchOne($sql, [':id' => $id]);
            
            if (!$result) {
                $response = ['success' => false, 'message' => 'OS não encontrada'];
            } else if ($result['OSENV'] === 'S') {
                $response = ['success' => false, 'message' => 'Esta OS já foi enviada anteriormente'];
            } else if (empty($result['CLIEOS'])) {
                $response = ['success' => false, 'message' => 'O cliente não possui e-mail cadastrado para envio de OS'];
            } else {
                // Em um ambiente real, aqui iria enviar o e-mail
                // Para este exemplo, simularemos o sucesso da operação e atualizaremos o status
                
                // Atualizar status da OS para enviada
                $result = update('ORDSER', ['OSENV' => 'S'], 'OSNUM = :id', [':id' => $id]);
                
                if ($result === false) {
                    $response = ['success' => false, 'message' => 'Erro ao atualizar status da OS'];
                } else {
                    $response = ['success' => true, 'message' => 'Ordem de Serviço enviada com sucesso por e-mail!'];
                }
            }
        } else {
            $response = ['success' => false, 'message' => 'ID da OS não fornecido'];
        }
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
    
    // Montar filtros
    $whereConditions = [];
    $params = [];
    $index = 0;
    
    // Filtro de código
    if (isset($_GET['codigo']) && $_GET['codigo'] !== '') {
        $paramName = ':clicod_' . $index++;
        $whereConditions[] = "c.CLICOD = $paramName";
        $params[$paramName] = $_GET['codigo'];
    }
    
    // Filtro de tipo
    if (isset($_GET['tipo']) && $_GET['tipo'] !== '') {
        $paramName = ':clitip_' . $index++;
        $whereConditions[] = "c.CLITIP = $paramName";
        $params[$paramName] = $_GET['tipo'];
    }
    
    // Filtro de razão social
    if (isset($_GET['razao']) && $_GET['razao'] !== '') {
        $paramName = ':cliraz_' . $index++;
        $whereConditions[] = "c.CLIRAZ LIKE $paramName";
        $params[$paramName] = '%' . $_GET['razao'] . '%';
    }
    
    // Filtro de documento
    if (isset($_GET['documento']) && $_GET['documento'] !== '') {
        $paramName = ':clidoc_' . $index++;
        $whereConditions[] = "c.CLIDOC LIKE $paramName";
        $params[$paramName] = '%' . $_GET['documento'] . '%';
    }
    
    // Filtro de município
    if (isset($_GET['municipio']) && $_GET['municipio'] !== '') {
        $paramName = ':climun_' . $index++;
        $whereConditions[] = "c.CLIMUN LIKE $paramName";
        $params[$paramName] = '%' . $_GET['municipio'] . '%';
    }
    
    // Filtro de UF
    if (isset($_GET['uf']) && $_GET['uf'] !== '') {
        $paramName = ':cliest_' . $index++;
        $whereConditions[] = "c.CLIEST = $paramName";
        $params[$paramName] = $_GET['uf'];
    }
    
    // Filtro de modalidade
    if (isset($_GET['modalidade']) && $_GET['modalidade'] !== '') {
        $paramName = ':climod_' . $index++;
        $whereConditions[] = "c.CLIMOD = $paramName";
        $params[$paramName] = $_GET['modalidade'];
    }
    
    // Construir cláusula WHERE
    $whereClause = count($whereConditions) > 0 ? implode(' AND ', $whereConditions) : '1=1';
    
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
    } else {
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

// ================================ FUNÇÕES DE CONSULTORES ================================
/**
 * Lista consultores com paginação e filtros
 * @return array Lista de consultores
 */
function listarConsultores() {
    // Obter parâmetros de filtro e paginação
    $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    $itensPorPagina = isset($_GET['itensPorPagina']) ? (int)$_GET['itensPorPagina'] : 10;
    
    // Verificar limites
    if ($pagina < 1) $pagina = 1;
    if ($itensPorPagina < 5) $itensPorPagina = 5;
    if ($itensPorPagina > 100) $itensPorPagina = 100;
    
    // Montar filtros
    $whereConditions = [];
    $params = [];
    $index = 0;
    
    // Filtro de código
    if (isset($_GET['codigo']) && $_GET['codigo'] !== '') {
        $paramName = ':concod_' . $index++;
        $whereConditions[] = "CONCOD = $paramName";
        $params[$paramName] = $_GET['codigo'];
    }
    
    // Filtro de nome
    if (isset($_GET['nome']) && $_GET['nome'] !== '') {
        $paramName = ':connom_' . $index++;
        $whereConditions[] = "CONNOM LIKE $paramName";
        $params[$paramName] = '%' . $_GET['nome'] . '%';
    }
    
    // Filtro de atuação
    if (isset($_GET['atuacao']) && $_GET['atuacao'] !== '') {
        $paramName = ':conatu_' . $index++;
        $whereConditions[] = "CONATU LIKE $paramName";
        $params[$paramName] = '%' . $_GET['atuacao'] . '%';
    }
    
    // Construir cláusula WHERE
    $whereClause = count($whereConditions) > 0 ? implode(' AND ', $whereConditions) : '1=1';
    
    // Contar total de registros
    $sqlCount = "SELECT COUNT(*) as total FROM CADCON WHERE $whereClause";
    $resultCount = fetchOne($sqlCount, $params);
    $total = $resultCount ? $resultCount['total'] : 0;
    
    // Calcular total de páginas
    $totalPaginas = ceil($total / $itensPorPagina);
    
    // Ajustar página atual se necessário
    if ($pagina > $totalPaginas && $totalPaginas > 0) {
        $pagina = $totalPaginas;
        $offset = ($pagina - 1) * $itensPorPagina;
    } else {
        $offset = ($pagina - 1) * $itensPorPagina;
    }
    
    // Buscar consultores
    $sql = "SELECT * 
            FROM CADCON
            WHERE $whereClause
            ORDER BY CONCOD DESC
            OFFSET :offset ROWS FETCH NEXT :limit ROWS ONLY";
    
    $params[':offset'] = $offset;
    $params[':limit'] = $itensPorPagina;
    
    $consultores = fetchAll($sql, $params);
    
    if ($consultores === false) {
        return ['success' => false, 'message' => 'Erro ao buscar consultores'];
    }
    
    // Calcular informações de paginação
    $inicio = $total > 0 ? $offset + 1 : 0;
    $fim = min($offset + $itensPorPagina, $total);
    
    return [
        'success' => true,
        'consultores' => $consultores,
        'paginaAtual' => $pagina,
        'totalPaginas' => $totalPaginas,
        'itensPorPagina' => $itensPorPagina,
        'total' => $total,
        'inicio' => $inicio,
        'fim' => $fim
    ];
}

/**
 * Obtém dados de um consultor específico
 * @param int $id ID do consultor
 * @return array Dados do consultor
 */
function obterConsultor($id) {
    $sql = "SELECT * FROM CADCON WHERE CONCOD = :id";
    
    $consultor = fetchOne($sql, [':id' => $id]);
    
    if ($consultor === false) {
        return ['success' => false, 'message' => 'Erro ao buscar dados do consultor'];
    }
    
    if (!$consultor) {
        return ['success' => false, 'message' => 'Consultor não encontrado'];
    }
    
    return ['success' => true, 'consultor' => $consultor];
}

// ================================ FUNÇÕES DE SERVIÇOS ================================
/**
 * Lista serviços com paginação e filtros
 * @return array Lista de serviços
 */
function listarServicos() {
    // Obter parâmetros de filtro e paginação
    $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    $itensPorPagina = isset($_GET['itensPorPagina']) ? (int)$_GET['itensPorPagina'] : 10;
    
    // Verificar limites
    if ($pagina < 1) $pagina = 1;
    if ($itensPorPagina < 5) $itensPorPagina = 5;
    if ($itensPorPagina > 100) $itensPorPagina = 100;
    
    // Montar filtros
    $whereConditions = [];
    $params = [];
    $index = 0;
    
    // Filtro de código
    if (isset($_GET['codigo']) && $_GET['codigo'] !== '') {
        $paramName = ':sercod_' . $index++;
        $whereConditions[] = "SERCOD = $paramName";
        $params[$paramName] = $_GET['codigo'];
    }
    
    // Filtro de descrição
    if (isset($_GET['descricao']) && $_GET['descricao'] !== '') {
        $paramName = ':serdes_' . $index++;
        $whereConditions[] = "SERDES LIKE $paramName";
        $params[$paramName] = '%' . $_GET['descricao'] . '%';
    }
    
    // Construir cláusula WHERE
    $whereClause = count($whereConditions) > 0 ? implode(' AND ', $whereConditions) : '1=1';
    
    // Contar total de registros
    $sqlCount = "SELECT COUNT(*) as total FROM CADSER WHERE $whereClause";
    $resultCount = fetchOne($sqlCount, $params);
    $total = $resultCount ? $resultCount['total'] : 0;
    
    // Calcular total de páginas
    $totalPaginas = ceil($total / $itensPorPagina);
    
    // Ajustar página atual se necessário
    if ($pagina > $totalPaginas && $totalPaginas > 0) {
        $pagina = $totalPaginas;
        $offset = ($pagina - 1) * $itensPorPagina;
    } else {
        $offset = ($pagina - 1) * $itensPorPagina;
    }
    
    // Buscar serviços
    $sql = "SELECT * 
            FROM CADSER
            WHERE $whereClause
            ORDER BY SERCOD DESC
            OFFSET :offset ROWS FETCH NEXT :limit ROWS ONLY";
    
    $params[':offset'] = $offset;
    $params[':limit'] = $itensPorPagina;
    
    $servicos = fetchAll($sql, $params);
    
    if ($servicos === false) {
        return ['success' => false, 'message' => 'Erro ao buscar serviços'];
    }
    
    // Calcular informações de paginação
    $inicio = $total > 0 ? $offset + 1 : 0;
    $fim = min($offset + $itensPorPagina, $total);
    
    return [
        'success' => true,
        'servicos' => $servicos,
        'paginaAtual' => $pagina,
        'totalPaginas' => $totalPaginas,
        'itensPorPagina' => $itensPorPagina,
        'total' => $total,
        'inicio' => $inicio,
        'fim' => $fim
    ];
}

/**
 * Obtém dados de um serviço específico
 * @param int $id ID do serviço
 * @return array Dados do serviço
 */
function obterServico($id) {
    $sql = "SELECT * FROM CADSER WHERE SERCOD = :id";
    
    $servico = fetchOne($sql, [':id' => $id]);
    
    if ($servico === false) {
        return ['success' => false, 'message' => 'Erro ao buscar dados do serviço'];
    }
    
    if (!$servico) {
        return ['success' => false, 'message' => 'Serviço não encontrado'];
    }
    
    return ['success' => true, 'servico' => $servico];
}

// ================================ FUNÇÕES DE MODALIDADES ================================
/**
 * Lista modalidades com paginação e filtros
 * @return array Lista de modalidades
 */
function listarModalidades() {
    // Obter parâmetros de filtro e paginação
    $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    $itensPorPagina = isset($_GET['itensPorPagina']) ? (int)$_GET['itensPorPagina'] : 10;
    
    // Verificar limites
    if ($pagina < 1) $pagina = 1;
    if ($itensPorPagina < 5) $itensPorPagina = 5;
    if ($itensPorPagina > 100) $itensPorPagina = 100;
    
    // Montar filtros
    $whereConditions = [];
    $params = [];
    $index = 0;
    
    // Filtro de código
    if (isset($_GET['codigo']) && $_GET['codigo'] !== '') {
        $paramName = ':modcod_' . $index++;
        $whereConditions[] = "MODCOD = $paramName";
        $params[$paramName] = $_GET['codigo'];
    }
    
    // Filtro de descrição
    if (isset($_GET['descricao']) && $_GET['descricao'] !== '') {
        $paramName = ':moddes_' . $index++;
        $whereConditions[] = "MODDES LIKE $paramName";
        $params[$paramName] = '%' . $_GET['descricao'] . '%';
    }
    
    // Construir cláusula WHERE
    $whereClause = count($whereConditions) > 0 ? implode(' AND ', $whereConditions) : '1=1';
    
    // Contar total de registros
    $sqlCount = "SELECT COUNT(*) as total FROM CADMOD WHERE $whereClause";
    $resultCount = fetchOne($sqlCount, $params);
    $total = $resultCount ? $resultCount['total'] : 0;
    
    // Calcular total de páginas
    $totalPaginas = ceil($total / $itensPorPagina);
    
    // Ajustar página atual se necessário
    if ($pagina > $totalPaginas && $totalPaginas > 0) {
        $pagina = $totalPaginas;
        $offset = ($pagina - 1) * $itensPorPagina;
    } else {
        $offset = ($pagina - 1) * $itensPorPagina;
    }
    
    // Buscar modalidades
    $sql = "SELECT * 
            FROM CADMOD
            WHERE $whereClause
            ORDER BY MODCOD DESC
            OFFSET :offset ROWS FETCH NEXT :limit ROWS ONLY";
    
    $params[':offset'] = $offset;
    $params[':limit'] = $itensPorPagina;
    
    $modalidades = fetchAll($sql, $params);
    
    if ($modalidades === false) {
        return ['success' => false, 'message' => 'Erro ao buscar modalidades'];
    }
    
    // Calcular informações de paginação
    $inicio = $total > 0 ? $offset + 1 : 0;
    $fim = min($offset + $itensPorPagina, $total);
    
    return [
        'success' => true,
        'modalidades' => $modalidades,
        'paginaAtual' => $pagina,
        'totalPaginas' => $totalPaginas,
        'itensPorPagina' => $itensPorPagina,
        'total' => $total,
        'inicio' => $inicio,
        'fim' => $fim
    ];
}

/**
 * Obtém dados de uma modalidade específica
 * @param int $id ID da modalidade
 * @return array Dados da modalidade
 */
function obterModalidade($id) {
    $sql = "SELECT * FROM CADMOD WHERE MODCOD = :id";
    
    $modalidade = fetchOne($sql, [':id' => $id]);
    
    if ($modalidade === false) {
        return ['success' => false, 'message' => 'Erro ao buscar dados da modalidade'];
    }
    
    if (!$modalidade) {
        return ['success' => false, 'message' => 'Modalidade não encontrada'];
    }
    
    return ['success' => true, 'modalidade' => $modalidade];
}

// ================================ FUNÇÕES DE ORDENS DE SERVIÇO ================================
/**
 * Lista ordens de serviço com paginação e filtros
 * @return array Lista de ordens de serviço
 */
function listarOS() {
    // Obter parâmetros de filtro e paginação
    $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    $itensPorPagina = isset($_GET['itensPorPagina']) ? (int)$_GET['itensPorPagina'] : 10;
    
    // Verificar limites
    if ($pagina < 1) $pagina = 1;
    if ($itensPorPagina < 5) $itensPorPagina = 5;
    if ($itensPorPagina > 100) $itensPorPagina = 100;
    
    // Montar filtros
    $whereConditions = [];
    $params = [];
    $index = 0;
    
    // Filtro de número
    if (isset($_GET['numero']) && $_GET['numero'] !== '') {
        $paramName = ':osnum_' . $index++;
        $whereConditions[] = "o.OSNUM = $paramName";
        $params[$paramName] = $_GET['numero'];
    }
    
    // Filtro de data início
    if (isset($_GET['data_inicio']) && $_GET['data_inicio'] !== '') {
        $paramName = ':data_inicio_' . $index++;
        $whereConditions[] = "o.OSDATA >= $paramName";
        $params[$paramName] = formatDateToDB($_GET['data_inicio']);
    }
    
    // Filtro de data fim
    if (isset($_GET['data_fim']) && $_GET['data_fim'] !== '') {
        $paramName = ':data_fim_' . $index++;
        $whereConditions[] = "o.OSDATA <= $paramName";
        $params[$paramName] = formatDateToDB($_GET['data_fim']);
    }
    
    // Filtro de cliente
    if (isset($_GET['cliente']) && $_GET['cliente'] !== '') {
        $paramName = ':cliente_' . $index++;
        $whereConditions[] = "o.OSCLICOD = $paramName";
        $params[$paramName] = $_GET['cliente'];
    }
    
    // Filtro de serviço
    if (isset($_GET['servico']) && $_GET['servico'] !== '') {
        $paramName = ':servico_' . $index++;
        $whereConditions[] = "o.OSSERCOD = $paramName";
        $params[$paramName] = $_GET['servico'];
    }
    
    // Filtro de consultor
    if (isset($_GET['consultor']) && $_GET['consultor'] !== '') {
        $paramName = ':consultor_' . $index++;
        $whereConditions[] = "o.OSCONCOD = $paramName";
        $params[$paramName] = $_GET['consultor'];
    }
    
    // Filtro de OS enviada
    if (isset($_GET['enviada']) && $_GET['enviada'] !== '') {
        $paramName = ':enviada_' . $index++;
        $whereConditions[] = "o.OSENV = $paramName";
        $params[$paramName] = $_GET['enviada'];
    }
    
    // Construir cláusula WHERE
    $whereClause = count($whereConditions) > 0 ? implode(' AND ', $whereConditions) : '1=1';
    
    // Base da consulta
    $sqlBase = "FROM ORDSER o
               LEFT JOIN CADCLI c ON o.OSCLICOD = c.CLICOD
               LEFT JOIN CADSER s ON o.OSSERCOD = s.SERCOD
               LEFT JOIN CADCON con ON o.OSCONCOD = con.CONCOD
               WHERE $whereClause";
    
    // Contar total de registros
    $sqlCount = "SELECT COUNT(*) as total $sqlBase";
    $resultCount = fetchOne($sqlCount, $params);
    $total = $resultCount ? $resultCount['total'] : 0;
    
    // Calcular total de páginas
    $totalPaginas = ceil($total / $itensPorPagina);
    
    // Ajustar página atual se necessário
    if ($pagina > $totalPaginas && $totalPaginas > 0) {
        $pagina = $totalPaginas;
        $offset = ($pagina - 1) * $itensPorPagina;
    } else {
        $offset = ($pagina - 1) * $itensPorPagina;
    }
    
    // Buscar ordens de serviço
    $sql = "SELECT o.OSNUM, o.OSDATA, o.OSENV, c.CLIRAZ, s.SERDES, con.CONNOM 
            $sqlBase
            ORDER BY o.OSNUM DESC
            OFFSET :offset ROWS FETCH NEXT :limit ROWS ONLY";
    
    $params[':offset'] = $offset;
    $params[':limit'] = $itensPorPagina;
    
    $ordens = fetchAll($sql, $params);
    
    if ($ordens === false) {
        return ['success' => false, 'message' => 'Erro ao buscar ordens de serviço'];
    }
    
    // Calcular informações de paginação
    $inicio = $total > 0 ? $offset + 1 : 0;
    $fim = min($offset + $itensPorPagina, $total);
    
    return [
        'success' => true,
        'ordens' => $ordens,
        'paginaAtual' => $pagina,
        'totalPaginas' => $totalPaginas,
        'itensPorPagina' => $itensPorPagina,
        'total' => $total,
        'inicio' => $inicio,
        'fim' => $fim
    ];
}

/**
 * Obtém dados de uma OS específica
 * @param int $id ID da OS
 * @return array Dados da OS
 */
function obterOS($id) {
    $sql = "SELECT o.*, c.CLIRAZ, c.CLIRES, s.SERDES, con.CONNOM, m.MODDES 
            FROM ORDSER o
            LEFT JOIN CADCLI c ON o.OSCLICOD = c.CLICOD
            LEFT JOIN CADSER s ON o.OSSERCOD = s.SERCOD
            LEFT JOIN CADCON con ON o.OSCONCOD = con.CONCOD
            LEFT JOIN CADMOD m ON o.OSMODCOD = m.MODCOD
            WHERE o.OSNUM = :id";
    
    $os = fetchOne($sql, [':id' => $id]);
    
    if ($os === false) {
        return ['success' => false, 'message' => 'Erro ao buscar dados da OS'];
    }
    
    if (!$os) {
        return ['success' => false, 'message' => 'Ordem de Serviço não encontrada'];
    }
    
    return ['success' => true, 'os' => $os];
}
