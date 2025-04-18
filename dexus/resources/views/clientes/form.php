<?php
// Verificar se é edição
$id = isset($_GET['id']) ? $_GET['id'] : null;
$edicao = $id !== null;
$visualizacao = isset($_GET['visualizar']) && $_GET['visualizar'] === '1';

// Buscar dados do cliente se for edição
$cliente = [];
if ($edicao) {
    $sql = "SELECT c.*, m.MODDES 
            FROM clientes c
            LEFT JOIN modalidades m ON c.CLIMOD = m.MODCOD
            WHERE c.CLICOD = :id";
    
    $cliente = fetchOne($sql, [':id' => $id]);
    
    if (!$cliente) {
        showError('Cliente não encontrado.');
        redirect('?page=clientes');
        exit;
    }
}

// Definir título da página
$pageTitle = $edicao ? 'Editar Cliente' : 'Novo Cliente';
if ($visualizacao) {
    $pageTitle = 'Visualizar Cliente';
}
?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary"><?php echo $pageTitle; ?></h6>
            <div class="btn-group">
                <a href="?page=clientes" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>
        </div>
        <div class="card-body">
            <form id="form-cliente" method="post" action="api.php">
                <input type="hidden" name="action" value="salvar_cliente">
                <?php if ($edicao): ?>
                <input type="hidden" name="CLICOD" value="<?php echo $cliente['CLICOD']; ?>">
                <?php endif; ?>
                
                <div class="row">
                    <!-- Campo código -->
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="CLICOD">Código:</label>
                            <input type="text" class="form-control" id="CLICOD" name="CLICOD" 
                                value="<?php echo $edicao ? $cliente['CLICOD'] : ''; ?>" readonly>
                        </div>
                    </div>
                    
                    <!-- Campo tipo -->
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="CLITIP">Tipo:</label>
                            <select class="form-control" id="CLITIP" name="CLITIP" <?php echo $visualizacao ? 'disabled' : ''; ?>>
                                <option value="">Selecione...</option>
                                <option value="F" <?php echo $edicao && $cliente['CLITIP'] === 'F' ? 'selected' : ''; ?>>
                                    F - Pessoa Física
                                </option>
                                <option value="J" <?php echo $edicao && $cliente['CLITIP'] === 'J' ? 'selected' : ''; ?>>
                                    J - Pessoa Jurídica
                                </option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Campo CPF/CNPJ -->
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="CLIDOC">CPF/CNPJ:</label>
                            <div class="input-group">
                                <input type="text" class="form-control cpf-cnpj-mask" id="CLIDOC" name="CLIDOC" 
                                    value="<?php echo $edicao ? $cliente['CLIDOC'] : ''; ?>" <?php echo $visualizacao ? 'readonly' : ''; ?>>
                                <?php if (!$visualizacao): ?>
                                <button class="btn btn-outline-secondary" type="button" id="btn-consultar-doc">
                                    <i class="fas fa-search"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Campo Modalidade -->
                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="CLIMOD">Modalidade:</label>
                            <select class="form-control" id="CLIMOD" name="CLIMOD" <?php echo $visualizacao ? 'disabled' : ''; ?>>
                                <option value="">Selecione...</option>
                                <?php
                                // Buscar modalidades no banco
                                $sql = "SELECT MODCOD, MODDES FROM modalidades ORDER BY MODDES";
                                $modalidades = fetchAll($sql);
                                
                                if ($modalidades) {
                                    foreach ($modalidades as $modalidade) {
                                        $selected = $edicao && $cliente['CLIMOD'] == $modalidade['MODCOD'] ? 'selected' : '';
                                        echo "<option value=\"{$modalidade['MODCOD']}\" {$selected}>{$modalidade['MODDES']}</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Campo Razão Social -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="CLIRAZ">Razão Social:</label>
                            <input type="text" class="form-control" id="CLIRAZ" name="CLIRAZ" maxlength="40"
                                value="<?php echo $edicao ? $cliente['CLIRAZ'] : ''; ?>" <?php echo $visualizacao ? 'readonly' : ''; ?>>
                        </div>
                    </div>
                    
                    <!-- Campo Nome Fantasia -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="CLIFAN">Nome Fantasia:</label>
                            <input type="text" class="form-control" id="CLIFAN" name="CLIFAN" maxlength="20"
                                value="<?php echo $edicao ? ($cliente['CLIFAN'] ?? '') : ''; ?>" <?php echo $visualizacao ? 'readonly' : ''; ?>>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Campo Município -->
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="CLIMUN">Município:</label>
                            <input type="text" class="form-control" id="CLIMUN" name="CLIMUN" maxlength="40"
                                value="<?php echo $edicao ? ($cliente['CLIMUN'] ?? '') : ''; ?>" <?php echo $visualizacao ? 'readonly' : ''; ?>>
                        </div>
                    </div>
                    
                    <!-- Campo UF -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="CLIEST">UF:</label>
                            <select class="form-control" id="CLIEST" name="CLIEST" <?php echo $visualizacao ? 'disabled' : ''; ?>>
                                <option value="">Selecione...</option>
                                <?php
                                $ufs = ['AC', 'AL', 'AM', 'AP', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MG', 'MS', 'MT', 
                                       'PA', 'PB', 'PE', 'PI', 'PR', 'RJ', 'RN', 'RO', 'RR', 'RS', 'SC', 'SE', 'SP', 'TO'];
                                foreach ($ufs as $uf) {
                                    $selected = $edicao && isset($cliente['CLIEST']) && $cliente['CLIEST'] === $uf ? 'selected' : '';
                                    echo "<option value=\"{$uf}\" {$selected}>{$uf}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Campo Responsável -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="CLIRES">Responsável:</label>
                            <input type="text" class="form-control" id="CLIRES" name="CLIRES" maxlength="20"
                                value="<?php echo $edicao ? ($cliente['CLIRES'] ?? '') : ''; ?>" <?php echo $visualizacao ? 'readonly' : ''; ?>>
                        </div>
                    </div>
                    
                    <!-- Campo Valor Hora -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="CLIVAL">Valor Hora:</label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="text" class="form-control currency-mask" id="CLIVAL" name="CLIVAL"
                                    value="<?php echo $edicao && isset($cliente['CLIVAL']) ? number_format($cliente['CLIVAL'], 2, ',', '.') : ''; ?>"
                                    <?php echo $visualizacao ? 'readonly' : ''; ?>>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Campo E-mail OS -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="CLIEOS">E-mail OS:</label>
                            <input type="email" class="form-control" id="CLIEOS" name="CLIEOS" maxlength="100"
                                value="<?php echo $edicao ? ($cliente['CLIEOS'] ?? '') : ''; ?>" <?php echo $visualizacao ? 'readonly' : ''; ?>>
                        </div>
                    </div>
                    
                    <!-- Campo E-mail NF -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="CLIENF">E-mail NF:</label>
                            <input type="email" class="form-control" id="CLIENF" name="CLIENF" maxlength="100"
                                value="<?php echo $edicao ? ($cliente['CLIENF'] ?? '') : ''; ?>" <?php echo $visualizacao ? 'readonly' : ''; ?>>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-12">
                        <?php if (!$visualizacao): ?>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Salvar
                        </button>
                        <button type="reset" class="btn btn-secondary">
                            <i class="fas fa-undo"></i> Limpar
                        </button>
                        <?php else: ?>
                        <a href="?page=clientes/form&id=<?php echo $id; ?>" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configurar validação do formulário
    const form = document.getElementById('form-cliente');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validar formulário
            if (validarFormCliente()) {
                // Limpar máscaras antes de enviar
                const tipoPessoa = document.getElementById('CLITIP').value;
                const cpfCnpj = document.getElementById('CLIDOC');
                if (cpfCnpj.value) {
                    cpfCnpj.value = cpfCnpj.value.replace(/\D/g, '');
                }
                
                const valorHora = document.getElementById('CLIVAL');
                if (valorHora.value) {
                    valorHora.value = valorHora.value.replace(/[^\d,]/g, '').replace(',', '.');
                }
                
                // Serializar dados do formulário
                const data = {
                    action: 'salvar_cliente'
                };
                
                // Adicionar campos ao objeto de dados
                const formData = new FormData(form);
                formData.forEach(function(value, key) {
                    data[key] = value;
                });
                
                // Enviar requisição
                salvarCliente(data)
                    .then(response => {
                        if (response.success) {
                            showAlert(response.message, 'success');
                            
                            // Redirecionar para a listagem após 1,5 segundos
                            setTimeout(() => {
                                window.location.href = '?page=clientes';
                            }, 1500);
                        } else {
                            showAlert(response.message, 'danger');
                        }
                    })
                    .catch(error => {
                        showAlert('Erro ao salvar cliente: ' + error.message, 'danger');
                    });
            }
        });
    }
    
    // Configurar consulta de CPF/CNPJ
    const btnConsultarDoc = document.getElementById('btn-consultar-doc');
    if (btnConsultarDoc) {
        btnConsultarDoc.addEventListener('click', function() {
            const documento = document.getElementById('CLIDOC').value;
            const tipo = document.getElementById('CLITIP').value;
            
            if (!documento || !tipo) {
                showAlert('Informe o tipo de pessoa e o documento para consultar.', 'warning');
                return;
            }
            
            // Limpar caracteres não numéricos para a consulta
            const docLimpo = documento.replace(/\D/g, '');
            
            // Exibir loader
            showLoader('Consultando documento, aguarde...');
            
            // Simular consulta (em um ambiente real, seria uma chamada à API)
            // Aqui apenas iremos preencher alguns campos aleatórios
            setTimeout(() => {
                if (tipo === 'F') {
                    // Pessoa física
                    document.getElementById('CLIRAZ').value = 'PESSOA FÍSICA EXEMPLO';
                    document.getElementById('CLIFAN').value = '';
                    document.getElementById('CLIMUN').value = 'SÃO PAULO';
                    document.getElementById('CLIEST').value = 'SP';
                } else {
                    // Pessoa jurídica
                    document.getElementById('CLIRAZ').value = 'EMPRESA EXEMPLO LTDA';
                    document.getElementById('CLIFAN').value = 'EXEMPLO';
                    document.getElementById('CLIMUN').value = 'SÃO PAULO';
                    document.getElementById('CLIEST').value = 'SP';
                }
                
                // Ocultar loader
                hideLoader();
                
                showAlert('Documento consultado com sucesso!', 'success');
            }, 1500);
        });
    }
    
    // Configurar campo CPF/CNPJ conforme o tipo de pessoa
    configurarCampoCPFCNPJ();
    
    // Configurar máscaras
    setupMasks();
});
</script>