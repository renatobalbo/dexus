<?php
/**
 * Dexus - Sistema de Gestão
 * Ponto de entrada da aplicação
 */

// Iniciar sessão
session_start();

// Definir caminho base
define('BASE_PATH', dirname(__DIR__));

// Carregar configurações
require_once BASE_PATH . '/app/config.php';

// Carregar funções utilitárias
require_once BASE_PATH . '/app/functions.php';

// Carregar conexão com o banco
require_once BASE_PATH . '/app/database.php';

// Obter página solicitada
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Verificar se é uma solicitação de API
if (strpos($page, 'api/') === 0) {
    // Redirecionar para o manipulador de API
    include BASE_PATH . '/public/api.php';
    exit;
}

// Definir título da página
$pageTitle = ucfirst($page);
switch ($page) {
    case 'dashboard':
        $pageTitle = 'Dashboard';
        break;
    case 'clientes':
        $pageTitle = 'Clientes';
        break;
    case 'clientes/form':
        $pageTitle = 'Cadastro de Cliente';
        break;
    case 'clientes/visualizar':
        $pageTitle = 'Detalhes do Cliente';
        break;
    case 'servicos':
        $pageTitle = 'Serviços';
        break;
    case 'servicos/form':
        $pageTitle = 'Cadastro de Serviço';
        break;
    case 'modalidades':
        $pageTitle = 'Modalidades';
        break;
    case 'modalidades/form':
        $pageTitle = 'Cadastro de Modalidade';
        break;
    case 'consultores':
        $pageTitle = 'Consultores';
        break;
    case 'consultores/form':
        $pageTitle = 'Cadastro de Consultor';
        break;
    case 'consultores/visualizar':
        $pageTitle = 'Detalhes do Consultor';
        break;
    case 'os':
        $pageTitle = 'Ordens de Serviço';
        break;
    case 'os/form':
        $pageTitle = 'Cadastro de OS';
        break;
    case 'os/visualizar':
        $pageTitle = 'Detalhes da OS';
        break;
    case 'relacao':
        $pageTitle = 'Relação de OS';
        break;
}

// Definir página atual para o menu
$currentPage = $page;

// Carregar o cabeçalho
include BASE_PATH . '/resources/templates/header.php';

// Carregar a página solicitada
$filePath = BASE_PATH . "/resources/views/{$page}.php";
if (file_exists($filePath)) {
    include $filePath;
} else {
    // Verificar subpastas
    $parts = explode('/', $page);
    if (count($parts) > 1) {
        $mainPage = $parts[0];
        $action = $parts[1];
        
        $filePath = BASE_PATH . "/resources/views/{$mainPage}/{$action}.php";
        if (file_exists($filePath)) {
            include $filePath;
        } else {
            // Página não encontrada
            include BASE_PATH . '/resources/templates/error.php';
        }
    } else {
        // Página não encontrada
        include BASE_PATH . '/resources/templates/error.php';
    }
}

// Carregar o rodapé
include BASE_PATH . '/resources/templates/footer.php';
?>