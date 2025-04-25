<?php
/**
 * Dexus - Sistema de Gestão
 * Funções utilitárias
 */

/**
 * Redireciona para outra página
 * @param string $url URL de destino
 */
function redirect($url) {
    header("Location: {$url}");
    exit;
}

/**
 * Obtém a URL base do sistema
 * @param string $path Caminho a ser anexado à URL base
 * @return string URL completa
 */
function baseUrl($path = '') {
    if (!empty($path) && $path[0] != '/') {
        $path = '/' . $path;
    }
    return BASE_URL . $path;
}

/**
 * Verifica se o método de requisição é POST
 * @return bool
 */
function isPost() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * Verifica se o método de requisição é GET
 * @return bool
 */
function isGet() {
    return $_SERVER['REQUEST_METHOD'] === 'GET';
}

/**
 * Exibe mensagem de erro
 * @param string $message Mensagem a ser exibida
 * @return void
 */
function showError($message) {
    $_SESSION['error_message'] = $message;
}

/**
 * Exibe mensagem de sucesso
 * @param string $message Mensagem a ser exibida
 * @return void
 */
function showSuccess($message) {
    $_SESSION['success_message'] = $message;
}

/**
 * Exibe mensagem de aviso
 * @param string $message Mensagem a ser exibida
 * @return void
 */
function showWarning($message) {
    $_SESSION['warning_message'] = $message;
}

/**
 * Obtém mensagem de erro
 * @return string|null Mensagem de erro, se houver
 */
function getErrorMessage() {
    $message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : null;
    unset($_SESSION['error_message']);
    return $message;
}

/**
 * Obtém mensagem de sucesso
 * @return string|null Mensagem de sucesso, se houver
 */
function getSuccessMessage() {
    $message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : null;
    unset($_SESSION['success_message']);
    return $message;
}

/**
 * Obtém mensagem de aviso
 * @return string|null Mensagem de aviso, se houver
 */
function getWarningMessage() {
    $message = isset($_SESSION['warning_message']) ? $_SESSION['warning_message'] : null;
    unset($_SESSION['warning_message']);
    return $message;
}

/**
 * Formata data para exibição (YYYY-MM-DD para DD/MM/YYYY)
 * @param string $date Data no formato YYYY-MM-DD
 * @return string Data formatada para exibição
 */
function formatDate($date) {
    if (empty($date)) {
        return '';
    }
    
    if (strpos($date, '/') !== false) {
        return $date; // Já está no formato DD/MM/YYYY
    }
    
    return date('d/m/Y', strtotime($date));
}

/**
 * Formata data para o banco de dados (DD/MM/YYYY para YYYY-MM-DD)
 * @param string $date Data no formato DD/MM/YYYY
 * @return string Data formatada para o banco
 */
function formatDateToDB($date) {
    if (empty($date)) {
        return '';
    }
    
    if (strpos($date, '-') !== false) {
        return $date; // Já está no formato YYYY-MM-DD
    }
    
    $parts = explode('/', $date);
    if (count($parts) !== 3) {
        return $date;
    }
    
    return "{$parts[2]}-{$parts[1]}-{$parts[0]}";
}

/**
 * Formata CPF/CNPJ para exibição
 * @param string $document CPF ou CNPJ
 * @param string $type Tipo do documento (F para CPF, J para CNPJ)
 * @return string Documento formatado
 */
function formatDocument($document, $type = null) {
    // Remover caracteres não numéricos
    $document = preg_replace('/\D/', '', $document);
    
    if (empty($document)) {
        return '';
    }
    
    // Se não foi especificado o tipo, tentar adivinhar pelo tamanho
    if ($type === null) {
        $type = (strlen($document) > 11) ? 'J' : 'F';
    }
    
    if ($type === 'F') {
        // CPF: 000.000.000-00
        if (strlen($document) !== 11) {
            return $document;
        }
        
        return substr($document, 0, 3) . '.' .
               substr($document, 3, 3) . '.' .
               substr($document, 6, 3) . '-' .
               substr($document, 9, 2);
    } else {
        // CNPJ: 00.000.000/0000-00
        if (strlen($document) !== 14) {
            return $document;
        }
        
        return substr($document, 0, 2) . '.' .
               substr($document, 2, 3) . '.' .
               substr($document, 5, 3) . '/' .
               substr($document, 8, 4) . '-' .
               substr($document, 12, 2);
    }
}

/**
 * Formata valor monetário para exibição
 * @param float $value Valor a ser formatado
 * @return string Valor formatado
 */
function formatMoney($value) {
    return 'R$ ' . number_format($value, 2, ',', '.');
}

/**
 * Formata valor monetário para o banco de dados
 * @param string $value Valor a ser formatado
 * @return float Valor formatado
 */
function formatMoneyToDB($value) {
    // Remover formatação
    $value = str_replace(['R$', '.', ' '], '', $value);
    $value = str_replace(',', '.', $value);
    
    return (float) $value;
}

/**
 * Limpa um texto para exibição segura em HTML
 * @param string $text Texto a ser limpo
 * @return string Texto limpo
 */
function h($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * Gera um slug a partir de um texto
 * @param string $text Texto de entrada
 * @return string Slug gerado
 */
function slugify($text) {
    // Remover acentos
    $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
    
    // Converter para minúsculas
    $text = strtolower($text);
    
    // Remover caracteres especiais
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    
    // Substituir espaços por hífens
    $text = preg_replace('/[\s-]+/', '-', $text);
    
    // Remover hífens do início e do fim
    $text = trim($text, '-');
    
    return $text;
}

/**
 * Gera JSON para resposta da API
 * @param array $data Dados a serem retornados
 * @param int $statusCode Código de status HTTP
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Obtém os dados enviados via POST
 * @return array Dados do POST decodificados
 */
function getRequestData() {
    // Se for um POST normal
    if (!empty($_POST)) {
        return $_POST;
    }
    
    // Se for um POST com JSON
    $jsonData = file_get_contents('php://input');
    if (!empty($jsonData)) {
        $data = json_decode($jsonData, true);
        if ($data !== null) {
            return $data;
        }
    }
    
    return [];
}

/**
 * Obtém parâmetros GET
 * @param string $key Chave do parâmetro
 * @param mixed $default Valor padrão se o parâmetro não existir
 * @return mixed Valor do parâmetro ou valor padrão
 */
function getParam($key, $default = null) {
    return isset($_GET[$key]) ? $_GET[$key] : $default;
}

/**
 * Obtém parâmetros POST
 * @param string $key Chave do parâmetro
 * @param mixed $default Valor padrão se o parâmetro não existir
 * @return mixed Valor do parâmetro ou valor padrão
 */
function postParam($key, $default = null) {
    return isset($_POST[$key]) ? $_POST[$key] : $default;
}

/**
 * Gera URL para a página solicitada
 * @param string $page Nome da página
 * @param array $params Parâmetros adicionais
 * @return string URL gerada
 */
function url($page, $params = []) {
    $url = BASE_URL . '?page=' . $page;
    
    foreach ($params as $key => $value) {
        $url .= "&{$key}=" . urlencode($value);
    }
    
    return $url;
}

/**
 * Gera um ID único
 * @return string ID gerado
 */
function generateId() {
    return uniqid();
}

/**
 * Verifica se há um usuário autenticado
 * @return bool Verdadeiro se há um usuário autenticado
 */
function isAuthenticated() {
    return isset($_SESSION['user_id']);
}

/**
 * Obtém o ID do usuário autenticado
 * @return int|null ID do usuário ou null se não autenticado
 */
function getUserId() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

/**
 * Obtém o nome do usuário autenticado
 * @return string|null Nome do usuário ou null se não autenticado
 */
function getUserName() {
    return isset($_SESSION['user_name']) ? $_SESSION['user_name'] : null;
}

/**
 * Obtém o perfil do usuário autenticado
 * @return string|null Perfil do usuário ou null se não autenticado
 */
function getUserProfile() {
    return isset($_SESSION['user_profile']) ? $_SESSION['user_profile'] : null;
}

/**
 * Verifica se o usuário tem permissão para a ação
 * @param string $action Ação a ser verificada
 * @return bool Verdadeiro se o usuário tem permissão
 */
function userHasPermission($action) {
    // Se o usuário é admin, tem permissão para tudo
    if (getUserProfile() === 'admin') {
        return true;
    }
    
    // Verificar permissões específicas por perfil
    $profile = getUserProfile();
    
    // Matriz de permissões por perfil
    $permissions = [
        'manager' => ['view', 'edit', 'create'],
        'operator' => ['view', 'create'],
        'viewer' => ['view']
    ];
    
    // Verificar se o perfil tem a permissão
    if (isset($permissions[$profile]) && in_array($action, $permissions[$profile])) {
        return true;
    }
    
    return false;
}

/**
 * Verifica se o usuário atual tem acesso a um módulo
 * @param string $module Nome do módulo
 * @return bool Verdadeiro se o usuário tem acesso
 */
function userHasAccess($module) {
    // Se o usuário é admin, tem acesso a tudo
    if (getUserProfile() === 'admin') {
        return true;
    }
    
    // Verificar acessos específicos por perfil
    $profile = getUserProfile();
    
    // Matriz de acessos por perfil
    $access = [
        'manager' => ['clientes', 'servicos', 'modalidades', 'consultores', 'os', 'relacao'],
        'operator' => ['clientes', 'os', 'relacao'],
        'viewer' => ['clientes', 'os', 'relacao']
    ];
    
    // Verificar se o perfil tem acesso ao módulo
    if (isset($access[$profile]) && in_array($module, $access[$profile])) {
        return true;
    }
    
    return false;
}

/**
 * Converte data para o formato brasileiro
 * @param string $date Data no formato americano (YYYY-MM-DD)
 * @return string Data no formato brasileiro (DD/MM/YYYY)
 */
function dateToBr($date) {
    if (empty($date)) {
        return '';
    }
    
    // Verificar se já está no formato brasileiro
    if (strpos($date, '/') !== false) {
        return $date;
    }
    
    // Converter
    $timestamp = strtotime($date);
    if ($timestamp === false) {
        return $date;
    }
    
    return date('d/m/Y', $timestamp);
}

/**
 * Converte data para o formato americano
 * @param string $date Data no formato brasileiro (DD/MM/YYYY)
 * @return string Data no formato americano (YYYY-MM-DD)
 */
function dateToUs($date) {
    if (empty($date)) {
        return '';
    }
    
    // Verificar se já está no formato americano
    if (strpos($date, '-') !== false) {
        return $date;
    }
    
    // Verificar formato
    if (!preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $date, $matches)) {
        return $date;
    }
    
    // Extrair partes
    $day = $matches[1];
    $month = $matches[2];
    $year = $matches[3];
    
    return "$year-$month-$day";
}