<?php
// Definir caminho base
define('BASE_PATH', __DIR__);

// Carregar configurações
require_once BASE_PATH . '/app/config.php';

// Exibir configurações do banco de dados (sem a senha)
echo "<h2>Configurações do Banco de Dados</h2>";
echo "<p>Host: " . DB_HOST . "</p>";
echo "<p>Nome do Banco: " . DB_NAME . "</p>";
echo "<p>Usuário: " . DB_USER . "</p>";

// Tentar conexão
echo "<h2>Tentando conectar ao banco de dados SQL Server...</h2>";

try {
    // Criar conexão PDO para SQL Server
    $dsn = "sqlsrv:Server=" . DB_HOST . ";Database=" . DB_NAME;
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ];
    
    $conexao = new PDO($dsn, DB_USER, DB_PASS, $options);
    
    echo "<p style='color: green; font-weight: bold;'>Conexão estabelecida com sucesso!</p>";
    
    // Testar consulta para tabela CADMOD
    echo "<h2>Testando consulta na tabela CADMOD</h2>";
    
    $sql = "SELECT MODCOD, MODDES FROM CADMOD ORDER BY MODCOD";
    $stmt = $conexao->prepare($sql);
    $stmt->execute();
    $modalidades = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($modalidades) > 0) {
        echo "<p>Encontradas " . count($modalidades) . " modalidades:</p>";
        echo "<ul>";
        foreach ($modalidades as $modalidade) {
            echo "<li>Código: " . $modalidade['MODCOD'] . " - Descrição: " . $modalidade['MODDES'] . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>Nenhuma modalidade encontrada na tabela CADMOD.</p>";
        
        // Verificar se a tabela existe (SQL Server)
        echo "<h3>Verificando se a tabela CADMOD existe</h3>";
        try {
            $stmt = $conexao->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'CADMOD'");
            $tabelaExiste = $stmt->rowCount() > 0;
            
            if ($tabelaExiste) {
                echo "<p>A tabela CADMOD existe, mas não contém registros.</p>";
            } else {
                echo "<p style='color: red;'>A tabela CADMOD não existe no banco de dados.</p>";
                
                // Listar tabelas existentes (SQL Server)
                echo "<h3>Tabelas existentes no banco de dados:</h3>";
                $stmt = $conexao->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE'");
                $tabelas = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                echo "<ul>";
                foreach ($tabelas as $tabela) {
                    echo "<li>" . $tabela . "</li>";
                }
                echo "</ul>";
            }
        } catch (PDOException $e) {
            echo "<p style='color: red;'>Erro ao verificar tabelas: " . $e->getMessage() . "</p>";
        }
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red; font-weight: bold;'>Erro de conexão: " . $e->getMessage() . "</p>";
    
    // Verificar tipos específicos de erro
    if (strpos($e->getMessage(), "Login failed") !== false) {
        echo "<p>Possível problema: Usuário ou senha incorretos.</p>";
    } elseif (strpos($e->getMessage(), "Cannot open database") !== false) {
        echo "<p>Possível problema: O banco de dados '" . DB_NAME . "' não existe.</p>";
    } elseif (strpos($e->getMessage(), "Could not find driver") !== false) {
        echo "<p>Possível problema: Driver SQL Server para PHP não está instalado.</p>";
        echo "<p>Verifique se a extensão 'sqlsrv' está instalada e habilitada no PHP.</p>";
    }
}
?>