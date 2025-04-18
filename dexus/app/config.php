<?php
/**
 * Dexus - Sistema de Gestão
 * Configurações do sistema
 */

// Configurações do banco de dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'dexus');
define('DB_USER', 'dexus_user');
define('DB_PASS', 'dexus_pass');

// URL base do sistema
define('BASE_URL', 'http://localhost/dexus');

// Configurações de E-mail
define('MAIL_HOST', 'smtp.example.com');
define('MAIL_PORT', 587);
define('MAIL_USER', 'noreply@example.com');
define('MAIL_PASS', 'email_password');
define('MAIL_FROM', 'Sistema Dexus <noreply@example.com>');

// Configurações de exibição de erros
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configurações gerais do sistema
define('SYSTEM_NAME', 'Dexus - Sistema de Gestão');
define('COMPANY_NAME', 'Dexus Consultoria');
define('COMPANY_DOCUMENT', '00.000.000/0000-00');
define('COMPANY_ADDRESS', 'Av. Exemplo, 1000 - Bairro - Cidade/UF - CEP: 00000-000');
define('COMPANY_PHONE', '(00) 0000-0000');
define('COMPANY_EMAIL', 'contato@dexus.com.br');
define('COMPANY_WEBSITE', 'www.dexus.com.br');

// Versão do sistema
define('SYSTEM_VERSION', '1.0.0');
?>