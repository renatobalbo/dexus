<?php
// Verificar se é edição
$id = isset($_GET['id']) ? $_GET['id'] : null;
$edicao = $id !== null;
$visualizacao = isset($_GET['visualizar']) && $_GET['visualizar'] === '1';

// Buscar dados do cliente se for edição
$cliente = [];
if ($edicao) {
    $sql = "SELECT c.*, m.MODDES 
            FROM CADCLI c
            LEFT JOIN CADMOD m ON c.CLIMOD = m.MODCOD
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
                                $sql = "SELECT MODCOD, MODDES FROM CADMOD ORDER BY MODDES";
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
    // Configurar campo CPF/CNPJ conforme o tipo de pessoa
    document.addEventListener('DOMContentLoaded', function() {
        const tipoPessoa = document.getElementById('CLITIP');
        const cpfCnpj = document.getElementById('CLIDOC');
        const labelCpfCnpj = document.querySelector('label[for="CLIDOC"]');
        
        if (tipoPessoa && cpfCnpj && labelCpfCnpj) {
            // Configurar inicialmente com base no valor selecionado
            if (tipoPessoa.value === 'F') {
                labelCpfCnpj.innerText = 'CPF:';
                cpfCnpj.setAttribute('placeholder', '000.000.000-00');
            } else if (tipoPessoa.value === 'J') {
                labelCpfCnpj.innerText = 'CNPJ:';
                cpfCnpj.setAttribute('placeholder', '00.000.000/0000-00');
            }
            
            // Adicionar evento de mudança
            tipoPessoa.addEventListener('change', function() {
                if (this.value === 'F') {
                    // Pessoa Física - CPF
                    labelCpfCnpj.innerText = 'CPF:';
                    cpfCnpj.setAttribute('placeholder', '000.000.000-00');
                } else if (this.value === 'J') {
                    // Pessoa Jurídica - CNPJ
                    labelCpfCnpj.innerText = 'CNPJ:';
                    cpfCnpj.setAttribute('placeholder', '00.000.000/0000-00');
                } else {
                    // Tipo não definido
                    labelCpfCnpj.innerText = 'CPF/CNPJ:';
                    cpfCnpj.setAttribute('placeholder', '');
                }
                
                // Limpar campo
                cpfCnpj.value = '';
            });
        }
        
        // Configurar consulta de CPF/CNPJ
        const btnConsultarDoc = document.getElementById('btn-consultar-doc');
        if (btnConsultarDoc) {
            btnConsultarDoc.addEventListener('click', function() {
                const documento = document.getElementById('CLIDOC').value;
                const tipo = document.getElementById('CLITIP').value;
                
                if (!documento || !tipo) {
                    alert('Informe o tipo de pessoa e o documento para consultar.');
                    return;
                }
                
                // Limpar caracteres não numéricos para a consulta
                const docLimpo = documento.replace(/\D/g, '');
                
                // Exibir loader
                this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
                this.disabled = true;
                
                // Simular consulta (em um ambiente real, seria uma chamada à API)
                setTimeout(() => {
                    // Simular dados retornados
                    if (tipo === 'F') {
                        document.getElementById('CLIRAZ').value = 'PESSOA FÍSICA TESTE';
                        document.getElementById('CLIFAN').value = '';
                        document.getElementById('CLIMUN').value = 'SÃO PAULO';
                        document.getElementById('CLIEST').value = 'SP';
                    } else {
                        document.getElementById('CLIRAZ').value = 'EMPRESA TESTE LTDA';
                        document.getElementById('CLIFAN').value = 'EMPRESA TESTE';
                        document.getElementById('CLIMUN').value = 'SÃO PAULO';
                        document.getElementById('CLIEST').value = 'SP';
                    }
                    
                    // Restaurar botão
                    this.innerHTML = '<i class="fas fa-search"></i>';
                    this.disabled = false;
                    
                    alert('Dados consultados com sucesso!');
                }, 1000);
            });
        }
        
        // Aplicar máscara para CPF/CNPJ
        document.querySelectorAll('.cpf-cnpj-mask').forEach(input => {
            input.addEventListener('input', function() {
                const tipo = document.getElementById('CLITIP').value;
                let v = this.value.replace(/\D/g, '');
                
                if (tipo === 'F') {
                    // CPF: 000.000.000-00
                    v = v.replace(/(\d{3})(\d)/, '$1.$2');
                    v = v.replace(/(\d{3})(\d)/, '$1.$2');
                    v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                } else if (tipo === 'J') {
                    // CNPJ: 00.000.000/0000-00
                    v = v.replace(/(\d{2})(\d)/, '$1.$2');
                    v = v.replace(/(\d{3})(\d)/, '$1.$2');
                    v = v.replace(/(\d{3})(\d)/, '$1/$2');
                    v = v.replace(/(\d{4})(\d{1,2})$/, '$1-$2');
                }
                
                this.value = v;
            });
        });
        
        // Aplicar máscara para valor
        document.querySelectorAll('.currency-mask').forEach(input => {
            input.addEventListener('input', function() {
                let v = this.value.replace(/\D/g, '');
                v = (parseInt(v) / 100).toFixed(2) + '';
                v = v.replace(".", ",");
                v = v.replace(/(\d)(\d{3})(\,)/g, "$1.$2$3");
                v = v.replace(/(\d)(\d{3})(\.\d{3})/g, "$1.$2$3");
                this.value = v;
            });
        });
    });
</script>