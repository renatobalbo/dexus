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
                <div class="alert alert-info">
                    Campos marcados com <span class="text-danger">*</span> são obrigatórios.
                </div>
                <?php if ($edicao): ?>
                <input type="hidden" name="CLICOD" value="<?php echo $cliente['CLICOD']; ?>">
                <?php endif; ?>
                
                <div class="row">
                    <!-- Campo código -->
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="CLICOD">Código:</label>
                            <?php
                            if ($edicao) {
                                // Se for edição, mostrar o código existente
                                echo '<input type="text" class="form-control" id="CLICOD" name="CLICOD" value="' . $cliente['CLICOD'] . '" readonly>';
                            } else {
                                // Para o SQL Server com IDENTITY, não enviamos o código no INSERT
                                echo '<input type="text" class="form-control" id="CLICOD" value="Automático" readonly>';
                                //echo '<small class="form-text text-muted">Código gerado pelo sistema</small>';
                            }
                            ?>
                        </div>
                    </div>
                    
                    <!-- Campo tipo -->
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="CLITIP">Tipo: <span class="text-danger">*</span></label>
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
                            <label for="CLIDOC">CPF/CNPJ: <span class="text-danger">*</span></label>
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
                                <option value="">Carregando...</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Campo Razão Social -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="CLIRAZ">Razão Social: <span class="text-danger">*</span></label>
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

        // Chamar a função para travar campos chave
        travarCamposChave();

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
        
        // Verificar se CPF/CNPJ já existe ao sair do campo
        document.getElementById('CLIDOC').addEventListener('blur', function() {
            const documento = this.value;
            const tipo = document.getElementById('CLITIP').value;
            const btnSalvar = document.querySelector('button[type="submit"]');
            
            if (!documento || !tipo) {
                return; // Não verificar se não estiver preenchido corretamente
            }
            
            // Limpar caracteres não numéricos
            const docLimpo = documento.replace(/\D/g, '');
            
            // Verificar se o documento é válido conforme o tipo
            let docValido = false;
            if (tipo === 'F' && docLimpo.length === 11) {
                // Validação simplificada para CPF - apenas verifica o tamanho
                docValido = true;
            } else if (tipo === 'J' && docLimpo.length === 14) {
                // Validação simplificada para CNPJ - apenas verifica o tamanho
                docValido = true;
            }
            
            if (docValido) {
                // Verificar se já existe no banco
                fetch(`api.php?action=verificar_documento&doc=${docLimpo}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.exists) {
                            alert('ATENÇÃO: Já existe um cliente cadastrado com este CPF/CNPJ! Por favor, informe outro documento para continuar.');
                            // Desabilitar o botão de salvar
                            btnSalvar.disabled = true;
                            // Adicionar uma classe visual para indicar erro
                            this.classList.add('is-invalid');
                            // Adicionar mensagem de erro abaixo do campo
                            let errorDiv = this.parentNode.querySelector('.invalid-feedback');
                            if (!errorDiv) {
                                errorDiv = document.createElement('div');
                                errorDiv.className = 'invalid-feedback';
                                this.parentNode.appendChild(errorDiv);
                            }
                            errorDiv.textContent = 'Documento já cadastrado no sistema';
                            errorDiv.style.display = 'block';
                        } else {
                            // Se o documento não existe, habilitar o botão de salvar
                            btnSalvar.disabled = false;
                            // Remover classe de erro
                            this.classList.remove('is-invalid');
                            // Remover mensagem de erro
                            const errorDiv = this.parentNode.querySelector('.invalid-feedback');
                            if (errorDiv) {
                                errorDiv.remove();
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Erro ao verificar documento:', error);
                        // Em caso de erro na verificação, permitir o envio
                        btnSalvar.disabled = false;
                    });
            }
        });

        // Adicione também este código para limpar o estado de erro quando o usuário modifica o documento
        document.getElementById('CLIDOC').addEventListener('input', function() {
            const btnSalvar = document.querySelector('button[type="submit"]');
            // Remover classe de erro
            this.classList.remove('is-invalid');
            // Remover mensagem de erro
            const errorDiv = this.parentNode.querySelector('.invalid-feedback');
            if (errorDiv) {
                errorDiv.remove();
            }
            // Habilitar o botão de salvar
            btnSalvar.disabled = false;
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

        // Botão para consultar documento
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
                
                // Mostrar que o botão está processando
                this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
                this.disabled = true;
                
                if (tipo === 'J') {
                    // Consulta de CNPJ na Brasil API
                    fetch(`https://brasilapi.com.br/api/cnpj/v1/${docLimpo}`)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('CNPJ não encontrado ou inválido');
                            }
                            return response.json();
                        })
                        .then(data => {
                            // Função para sanitizar texto e remover caracteres especiais
                            const sanitizarTexto = (texto) => {
                                if (!texto) return '';
                                // Remove caracteres especiais mantendo letras, números, espaços e pontuações básicas
                                return texto.normalize('NFD')
                                        .replace(/[\u0300-\u036f]/g, '') // Remove acentos
                                        .replace(/[^\w\s.,\-]/g, '') // Mantém apenas letras, números, espaços, ponto, vírgula e hífen
                                        .trim();
                            };
                            
                            // Preencher campos com os dados sanitizados
                            document.getElementById('CLIRAZ').value = sanitizarTexto(data.razao_social).substring(0, 80);
                            document.getElementById('CLIFAN').value = sanitizarTexto(data.nome_fantasia).substring(0, 40);
                            document.getElementById('CLIMUN').value = sanitizarTexto(data.municipio);
                            document.getElementById('CLIEST').value = sanitizarTexto(data.uf);
                            
                            // Restaurar botão
                            this.innerHTML = '<i class="fas fa-search"></i>';
                            this.disabled = false;
                            
                            alert('CNPJ consultado com sucesso!');
                        })
                        .catch(error => {
                            console.error('Erro na consulta:', error);
                            
                            // Restaurar botão
                            this.innerHTML = '<i class="fas fa-search"></i>';
                            this.disabled = false;
                            
                            alert('Erro ao consultar CNPJ: ' + error.message);
                        });
                } else {
                    // Para CPF, como não temos API disponível, usar dados fictícios
                    setTimeout(() => {
                        // Preencher com dados fictícios para CPF
                        document.getElementById('CLIRAZ').value = 'PESSOA FISICA TESTE';
                        document.getElementById('CLIFAN').value = '';
                        document.getElementById('CLIMUN').value = 'SAO PAULO';
                        document.getElementById('CLIEST').value = 'SP';
                        
                        // Restaurar botão
                        this.innerHTML = '<i class="fas fa-search"></i>';
                        this.disabled = false;
                        
                        alert('CPF consultado com sucesso! (Dados fictícios)');
                    }, 500);
                }
            });
        }
    });

    // Função para carregar modalidades via API
    document.addEventListener('DOMContentLoaded', function() {
        // Obter o select de modalidades
        const selectModalidades = document.getElementById('CLIMOD');
        
        // Buscar modalidades via API
        fetch('api.php?action=listar_modalidades')
            .then(response => response.json())
            .then(data => {
                // Limpar opções existentes
                selectModalidades.innerHTML = '<option value="">Selecione...</option>';
                
                // Verificar se há modalidades
                if (data.success && data.modalidades && data.modalidades.length > 0) {
                    // Adicionar opções ao select
                    data.modalidades.forEach(modalidade => {
                        const option = document.createElement('option');
                        option.value = modalidade.MODCOD;
                        option.textContent = modalidade.MODDES;
                        
                        // Se estiver em modo de edição, selecionar a opção atual
                        <?php if ($edicao && isset($cliente['CLIMOD'])): ?>
                        if (modalidade.MODCOD == '<?php echo $cliente['CLIMOD']; ?>') {
                            option.selected = true;
                        }
                        <?php endif; ?>
                        
                        selectModalidades.appendChild(option);
                    });
                } else {
                    // Exibir mensagem de que não há modalidades
                    const option = document.createElement('option');
                    option.value = "";
                    option.textContent = "Nenhuma modalidade cadastrada";
                    option.disabled = true;
                    selectModalidades.appendChild(option);
                }
            })
            .catch(error => {
                console.error('Erro ao carregar modalidades:', error);
                selectModalidades.innerHTML = '<option value="">Erro ao carregar modalidades</option>';
            });
    });

    document.addEventListener('DOMContentLoaded', function() {
        // Adicione esta função sanitizadora antes do evento de submit do formulário:
        function sanitizarCamposFormulario() {
            // Função para sanitizar texto
            const sanitizarTexto = (texto) => {
                if (!texto) return '';
                return texto.normalize('NFD')
                        .replace(/[\u0300-\u036f]/g, '') // Remove acentos
                        .replace(/[^\w\s.,\-]/g, '') // Mantém apenas letras, números, espaços, ponto, vírgula e hífen
                        .trim();
            };
            
            // Lista de campos de texto para sanitizar
            const camposTexto = ['CLIRAZ', 'CLIFAN', 'CLIMUN', 'CLIRES'];
            
            // Sanitizar cada campo
            camposTexto.forEach(campoId => {
                const campo = document.getElementById(campoId);
                if (campo && campo.value) {
                    campo.value = sanitizarTexto(campo.value);
                }
            });
        }

        // Interceptar o envio do formulário
        document.getElementById('form-cliente').addEventListener('submit', function(e) {
            // Previne o envio padrão do formulário
            e.preventDefault();
            
            // Lista de campos obrigatórios (ID dos campos)
            const camposObrigatorios = ['CLITIP', 'CLIRAZ', 'CLIDOC'];
            let formValido = true;
            let campoComErro = null;
            
            // Verificar cada campo obrigatório
            camposObrigatorios.forEach(campo => {
                const elemento = document.getElementById(campo);
                // Remover qualquer indicação de erro anterior
                elemento.classList.remove('is-invalid');
                const errorDiv = elemento.parentNode.querySelector('.invalid-feedback');
                if (errorDiv) {
                    errorDiv.remove();
                }
                
                // Verificar se está vazio
                if (!elemento.value.trim()) {
                    formValido = false;
                    if (!campoComErro) campoComErro = elemento;
                    
                    // Marcar como inválido
                    elemento.classList.add('is-invalid');
                    
                    // Adicionar mensagem de erro
                    const novoErrorDiv = document.createElement('div');
                    novoErrorDiv.className = 'invalid-feedback';
                    novoErrorDiv.textContent = 'Campo obrigatório';
                    novoErrorDiv.style.display = 'block';
                    elemento.parentNode.appendChild(novoErrorDiv);
                }
            });
            
            // Verificar se o documento está marcado como inválido (já existe)
            if (document.getElementById('CLIDOC').classList.contains('is-invalid')) {
                formValido = false;
                if (!campoComErro) campoComErro = document.getElementById('CLIDOC');
            }
            
            // Se o formulário não for válido, interromper o envio
            if (!formValido) {
                // Focar no primeiro campo com erro
                if (campoComErro) {
                    campoComErro.focus();
                }
                // Mostrar alerta
                alert('Por favor, preencha todos os campos obrigatórios corretamente antes de salvar.');
                return false;
            }
            
            // Se chegou até aqui, o formulário é válido
            // Sanitizar campos de texto para evitar problemas com caracteres especiais
            sanitizarCamposFormulario();
            
            // Exibir loader
            const loaderModal = showLoader('Salvando cliente...');
            
            // Obter dados do formulário
            const formData = new FormData(this);
            
            // Enviar via AJAX
            fetch('api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Ocultar loader
                hideLoader();
                
                // Exibir mensagem e redirecionar em caso de sucesso
                if (data.success) {
                    alert(data.message);
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    }
                } else {
                    // Apenas mostrar mensagem de erro se realmente falhou
                    alert(data.message || 'Erro ao salvar cliente.');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                
                // Verificar se podemos detectar se o cliente foi salvo mesmo com erro
                fetch(`api.php?action=verificar_documento&doc=${document.getElementById('CLIDOC').value.replace(/\D/g, '')}`)
                    .then(response => response.json())
                    .then(data => {
                        hideLoader();
                        
                        if (data.exists) {
                            // Cliente provavelmente foi salvo, redirecionar para a lista
                            alert('Cliente cadastrado com sucesso!');
                            window.location.href = '?page=clientes';
                        } else {
                            // Realmente houve falha
                            alert('Erro ao processar requisição. Por favor, tente novamente.');
                        }
                    })
                    .catch(() => {
                        // Erro na verificação, mostrar mensagem genérica
                        hideLoader();
                        alert('Erro ao processar requisição. Por favor, tente novamente.');
                    });
            });
        });
    });

    // Função para travar campos chave no modo de edição
    function travarCamposChave() {
        // Verificar se está em modo de edição (quando temos um código de cliente)
        const codigoCliente = document.getElementById('CLICOD');
        if (codigoCliente && codigoCliente.value && codigoCliente.value !== 'Automático') {
            // Estamos em modo de edição, então vamos travar campos chave
            
            // Campo Tipo (CLITIP)
            const campoTipo = document.getElementById('CLITIP');
            if (campoTipo) {
                campoTipo.disabled = true;
                // Adicionar uma dica visual que o campo está desabilitado
                campoTipo.parentNode.classList.add('campo-chave');
                // Adicionar um elemento de informação
                const infoTipo = document.createElement('small');
                infoTipo.className = 'form-text text-muted';
                campoTipo.parentNode.appendChild(infoTipo);
            }
            
            // Campo CPF/CNPJ (CLIDOC)
            const campoDoc = document.getElementById('CLIDOC');
            if (campoDoc) {
                campoDoc.readOnly = true;
                // Desabilitar o botão de consulta, se existir
                const btnConsulta = document.getElementById('btn-consultar-doc');
                if (btnConsulta) {
                    btnConsulta.disabled = true;
                    btnConsulta.style.display = 'none'; // Ocultar o botão
                }
                // Adicionar uma dica visual
                campoDoc.parentNode.classList.add('campo-chave');
                // Adicionar um elemento de informação
                const infoDoc = document.createElement('small');
                infoDoc.className = 'form-text text-muted';
                campoDoc.parentNode.appendChild(infoDoc);
            }
        }
    }
</script>