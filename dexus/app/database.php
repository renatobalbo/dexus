<?php
/**
 * Dexus - Sistema de Gestão
 * Conexão com o banco de dados
 */

/**
 * Obtém uma conexão com o banco de dados
 * @return PDO Conexão PDO com o banco de dados
 */
function getConnection() {
    static $connection = null;
    
    if ($connection === null) {
        try {
            // Criar conexão PDO
            $dsn = "sqlsrv:Server=" . DB_HOST . ";Database=" . DB_NAME;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ];
            
            $connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Registrar erro
            error_log('Erro de conexão: ' . $e->getMessage());
            
            // Retornar null em caso de erro
            return null;
        }
    }
    
    return $connection;
}

/**
 * Executa uma consulta SQL
 * @param string $sql Consulta SQL
 * @param array $params Parâmetros da consulta
 * @return array|false Resultado da consulta ou false em caso de erro
 */
function executeQuery($sql, $params = []) {
    $conn = getConnection();
    
    if (!$conn) {
        return false;
    }
    
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        
        return $stmt;
    } catch (PDOException $e) {
        // Registrar erro
        error_log('Erro ao executar consulta: ' . $e->getMessage());
        error_log('SQL: ' . $sql);
        error_log('Params: ' . json_encode($params));
        
        return false;
    }
}

/**
 * Obtém um único registro do banco de dados
 * @param string $sql Consulta SQL
 * @param array $params Parâmetros da consulta
 * @return array|false Registro encontrado ou false em caso de erro
 */
function fetchOne($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    
    if ($stmt === false) {
        return false;
    }
    
    return $stmt->fetch();
}

/**
 * Obtém múltiplos registros do banco de dados
 * @param string $sql Consulta SQL
 * @param array $params Parâmetros da consulta
 * @return array|false Registros encontrados ou false em caso de erro
 */
function fetchAll($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    
    if ($stmt === false) {
        return false;
    }
    
    return $stmt->fetchAll();
}

/**
 * Insere um registro no banco de dados
 * @param string $table Nome da tabela
 * @param array $data Dados a serem inseridos
 * @return int|false ID do registro inserido ou false em caso de erro
 */
function insert($table, $data) {
    $conn = getConnection();
    
    if (!$conn) {
        return false;
    }
    
    try {
        // Montar campos e valores
        $fields = array_keys($data);
        $placeholders = array_map(function($field) {
            return ':' . $field;
        }, $fields);
        
        // Montar SQL
        $sql = "INSERT INTO {$table} (" . implode(', ', $fields) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        // Executar
        $stmt = $conn->prepare($sql);
        $stmt->execute($data);
        
        // Retornar ID inserido
        return $conn->lastInsertId();
    } catch (PDOException $e) {
        // Registrar erro
        error_log('Erro ao inserir: ' . $e->getMessage());
        error_log('Tabela: ' . $table);
        error_log('Dados: ' . json_encode($data));
        
        return false;
    }
}

/**
 * Atualiza um registro no banco de dados
 * @param string $table Nome da tabela
 * @param array $data Dados a serem atualizados
 * @param string $condition Condição WHERE
 * @param array $params Parâmetros adicionais da condição
 * @return int|false Número de registros atualizados ou false em caso de erro
 */
function update($table, $data, $condition, $params = []) {
    $conn = getConnection();
    
    if (!$conn) {
        return false;
    }
    
    try {
        // Montar campos e valores para SET
        $setClauses = [];
        foreach ($data as $field => $value) {
            $setClauses[] = "{$field} = :set_{$field}";
            $params["set_{$field}"] = $value;
        }
        
        // Montar SQL
        $sql = "UPDATE {$table} SET " . implode(', ', $setClauses) . " WHERE {$condition}";
        
        // Executar
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        
        // Retornar número de registros afetados
        return $stmt->rowCount();
    } catch (PDOException $e) {
        // Registrar erro
        error_log('Erro ao atualizar: ' . $e->getMessage());
        error_log('SQL: ' . $sql);
        error_log('Params: ' . json_encode($params));
        
        return false;
    }
}

/**
 * Remove um registro do banco de dados
 * @param string $table Nome da tabela
 * @param string $condition Condição WHERE
 * @param array $params Parâmetros da condição
 * @return int|false Número de registros removidos ou false em caso de erro
 */
function delete($table, $condition, $params = []) {
    $conn = getConnection();
    
    if (!$conn) {
        return false;
    }
    
    try {
        // Montar SQL
        $sql = "DELETE FROM {$table} WHERE {$condition}";
        
        // Executar
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        
        // Retornar número de registros afetados
        return $stmt->rowCount();
    } catch (PDOException $e) {
        // Registrar erro
        error_log('Erro ao excluir: ' . $e->getMessage());
        error_log('SQL: ' . $sql);
        error_log('Params: ' . json_encode($params));
        
        return false;
    }
}

/**
 * Inicia uma transação
 * @return PDO|false Conexão com a transação ou false em caso de erro
 */
function beginTransaction() {
    $conn = getConnection();
    
    if (!$conn) {
        return false;
    }
    
    try {
        $conn->beginTransaction();
        return $conn;
    } catch (PDOException $e) {
        error_log('Erro ao iniciar transação: ' . $e->getMessage());
        return false;
    }
}

/**
 * Confirma uma transação
 * @param PDO $conn Conexão com a transação
 * @return bool Indica se a transação foi confirmada com sucesso
 */
function commitTransaction($conn) {
    if (!$conn) {
        return false;
    }
    
    try {
        $conn->commit();
        return true;
    } catch (PDOException $e) {
        error_log('Erro ao confirmar transação: ' . $e->getMessage());
        return false;
    }
}

/**
 * Desfaz uma transação
 * @param PDO $conn Conexão com a transação
 * @return bool Indica se a transação foi desfeita com sucesso
 */
function rollbackTransaction($conn) {
    if (!$conn) {
        return false;
    }
    
    try {
        $conn->rollBack();
        return true;
    } catch (PDOException $e) {
        error_log('Erro ao desfazer transação: ' . $e->getMessage());
        return false;
    }
}

/**
 * Constrói uma cláusula WHERE com base em um array de filtros
 * @param array $filters Array associativo de filtros (campo => valor)
 * @param array &$params Array para armazenar os parâmetros da consulta
 * @param array $options Opções adicionais (exact, prefix, between, etc)
 * @return string Cláusula WHERE formatada
 */
function buildWhereClause($filters, &$params, $options = array()) {
    if (empty($filters)) {
        return '1=1';
    }
    
    $whereConditions = array();
    $index = 0;
    
    foreach ($filters as $field => $value) {
        if (is_null($value) || $value === '') {
            continue;
        }
        
        // Verificar opção de comparação para este campo
        $comparison = isset($options[$field]) ? $options[$field] : 'like';
        
        // Gerar nome de parâmetro único
        $paramName = ':' . str_replace('.', '_', $field) . '_' . $index++;
        
        switch ($comparison) {
            case 'exact':
                $whereConditions[] = "$field = $paramName";
                $params[$paramName] = $value;
                break;
                
            case 'like':
                $whereConditions[] = "$field LIKE $paramName";
                $params[$paramName] = '%' . $value . '%';
                break;
                
            case 'prefix':
                $whereConditions[] = "$field LIKE $paramName";
                $params[$paramName] = $value . '%';
                break;
                
            case 'suffix':
                $whereConditions[] = "$field LIKE $paramName";
                $params[$paramName] = '%' . $value;
                break;
                
            case 'greater':
                $whereConditions[] = "$field > $paramName";
                $params[$paramName] = $value;
                break;
                
            case 'greater_equal':
                $whereConditions[] = "$field >= $paramName";
                $params[$paramName] = $value;
                break;
                
            case 'less':
                $whereConditions[] = "$field < $paramName";
                $params[$paramName] = $value;
                break;
                
            case 'less_equal':
                $whereConditions[] = "$field <= $paramName";
                $params[$paramName] = $value;
                break;
                
            case 'between':
                if (is_array($value) && count($value) == 2) {
                    $paramName1 = $paramName . '_1';
                    $paramName2 = $paramName . '_2';
                    $whereConditions[] = "($field BETWEEN $paramName1 AND $paramName2)";
                    $params[$paramName1] = $value[0];
                    $params[$paramName2] = $value[1];
                }
                break;
                
            case 'in':
                if (is_array($value) && !empty($value)) {
                    $inParams = array();
                    foreach ($value as $i => $val) {
                        $inParamName = $paramName . '_' . $i;
                        $inParams[] = $inParamName;
                        $params[$inParamName] = $val;
                    }
                    $whereConditions[] = "$field IN (" . implode(', ', $inParams) . ")";
                }
                break;
                
            case 'not_in':
                if (is_array($value) && !empty($value)) {
                    $inParams = array();
                    foreach ($value as $i => $val) {
                        $inParamName = $paramName . '_' . $i;
                        $inParams[] = $inParamName;
                        $params[$inParamName] = $val;
                    }
                    $whereConditions[] = "$field NOT IN (" . implode(', ', $inParams) . ")";
                }
                break;
                
            case 'null':
                if ($value) {
                    $whereConditions[] = "$field IS NULL";
                } else {
                    $whereConditions[] = "$field IS NOT NULL";
                }
                break;
                
            default:
                $whereConditions[] = "$field = $paramName";
                $params[$paramName] = $value;
                break;
        }
    }
    
    if (empty($whereConditions)) {
        return '1=1';
    }
    
    return implode(' AND ', $whereConditions);
}
?>