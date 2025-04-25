<?php
// Verificar se é edição
$id = isset($_GET['id']) ? $_GET['id'] : null;
$edicao = $id !== null;
$visualizacao = isset($_GET['visualizar']) && $_GET['visualizar'] === '1';

// Buscar dados do consultor se for edição
$consultor = [];
if ($edicao) {
    $sql = "SELECT * FROM CADCON WHERE CONCOD = :id";
    
    $consultor = fetchOne($sql, [':id' => $id]);
    
    if (!$consultor) {
        showError('Consultor não encontrado.');
        redirect('?page=consultores');
        exit;
    }
}

// Definir título da página
$pageTitle = $edicao ? 'Editar Consultor' : 'Novo Consultor';
if ($visualizacao) {
    $pageTitle = 'Visualizar Consultor';
}
?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary"><?php echo $pageTitle; ?></h6>
            <div class="btn-group">
                <a href="?page=consultores" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>
        </div>
        <div class="card-body">
            <form id="form-consultor" method="post" action="api.php">
                <input type="hidden" name="action" value="salvar_consultor">
                <div class="alert alert-info">
                    Campos marcados com <span class="text-danger">*</span> são obrigatórios.
                </div>
                <?php if ($edicao): ?>
                <input type="hidden" name="CONCOD" value="<?php echo $consultor['CONCOD']; ?>">
                <?php endif; ?>
                
                <div class="row">
                    <!-- Campo código -->
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="CONCOD">Código:</label>
                            <?php
                            if ($edicao) {
                                // Se for edição, mostrar o código existente
                                echo '<input type="text" class="form-control" id="CONCOD" name="CONCOD" value="' . $consultor['CONCOD'] . '" readonly>';
                            } else {
                                // Para o SQL Server com IDENTITY, não enviamos o código no INSERT
                                echo '<input type="text" class="form-control" id="CONCOD" value="Automático" readonly>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Campo Nome -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="CONNOM">Nome: <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="CONNOM" name="CONNOM" maxlength="40"
                                value="<?php echo $edicao ? $consultor['CONNOM'] : ''; ?>" <?php echo $visualizacao ? 'readonly' : ''; ?>>
                        </div>
                    </div>
                    
                    <!-- Campo Telefone -->
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="CONTEL">Telefone:</label>
                            <input type="text" class="form-control phone-mask" id="CONTEL" name="CONTEL" maxlength="15"
                                value="<?php echo $edicao ? ($consultor['CONTEL'] ?? '') : ''; ?>" <?php echo $visualizacao ? 'readonly' : ''; ?>>
                        </div>
                    </div>
                    
                    <!-- Campo Valor Hora -->
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="CONVAL">Valor Hora:</label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="text" class="form-control currency-mask" id="CONVAL" name="CONVAL"
                                    value="<?php echo $edicao && isset($consultor['CONVAL']) ? number_format($consultor['CONVAL'], 2, ',', '.') : ''; ?>"
                                    <?php echo $visualizacao ? 'readonly' : ''; ?>>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Campo E-mail -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="CONEMA">E-mail:</label>
                            <input type="email" class="form-control" id="CONEMA" name="CONEMA" maxlength="100"
                                value="<?php echo $edicao ? ($consultor['CONEMA'] ?? '') : ''; ?>" <?php echo $visualizacao ? 'readonly' : ''; ?>>
                        </div>
                    </div>
                    
                    <!-- Campo Atuação -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="CONATU">Atuação:</label>
                            <input type="text" class="form-control" id="CONATU" name="CONATU" maxlength="40"
                                value="<?php echo $edicao ? ($consultor['CONATU'] ?? '') : ''; ?>" <?php echo $visualizacao ? 'readonly' : ''; ?>>
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
                        <a href="?page=consultores/form&id=<?php echo $id; ?>" class="btn btn-primary">
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
        // Aplicar máscara para telefone
        document.querySelectorAll('.phone-mask').forEach(input => {
            input.addEventListener('input', function() {
                let value = this.value.replace(/\D/g, '');
                
                if (value.length > 10) {
                    // Celular: (99) 99999-9999
                    value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
                } else {
                    // Telefone fixo: (99) 9999-9999
                    value = value.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
                }
                
                this.value = value;
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

        // Função para sanitizar campos do formulário
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
            const camposTexto = ['CONNOM', 'CONATU'];
            
            // Sanitizar cada campo
            camposTexto.forEach(campoId => {
                const campo = document.getElementById(campoId);
                if (campo && campo.value) {
                    campo.value = sanitizarTexto(campo.value);
                }
            });
        }

        // Interceptar o envio do formulário
        document.getElementById('form-consultor').addEventListener('submit', function(e) {
            // Previne o envio padrão do formulário
            e.preventDefault();
            
            // Lista de campos obrigatórios (ID dos campos)
            const camposObrigatorios = ['CONNOM'];
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
            
            // Validar o e-mail se estiver preenchido
            const campoEmail = document.getElementById('CONEMA');
            if (campoEmail && campoEmail.value) {
                const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!regexEmail.test(campoEmail.value)) {
                    formValido = false;
                    if (!campoComErro) campoComErro = campoEmail;
                    
                    // Marcar como inválido
                    campoEmail.classList.add('is-invalid');
                    
                    // Adicionar mensagem de erro
                    const novoErrorDiv = document.createElement('div');
                    novoErrorDiv.className = 'invalid-feedback';
                    novoErrorDiv.textContent = 'E-mail inválido';
                    novoErrorDiv.style.display = 'block';
                    campoEmail.parentNode.appendChild(novoErrorDiv);
                }
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
            const loaderModal = showLoader('Salvando consultor...');
            
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
                    alert(data.message || 'Erro ao salvar consultor.');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                hideLoader();
                alert('Erro ao processar requisição. Por favor, tente novamente.');
            });
        });
    });
// Verificar se nome já existe ao sair do campo
document.getElementById('CONNOM').addEventListener('blur', function() {
    const nome = this.value;
    const id = document.getElementById('CONCOD').value !== 'Automático' ? document.getElementById('CONCOD').value : '';
    const btnSalvar = document.querySelector('button[type="submit"]');
    
    if (!nome) {
        return; // Não verificar se não estiver preenchido
    }
    
    // Verificar se já existe no banco
    fetch(`api.php?action=verificar_consultor_nome&nome=${encodeURIComponent(nome)}${id ? '&id=' + id : ''}`)
        .then(response => response.json())
        .then(data => {
            if (data.exists) {
                alert('ATENÇÃO: Já existe um consultor cadastrado com este nome! Por favor, informe outro nome para continuar.');
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
                errorDiv.textContent = 'Nome já cadastrado no sistema';
                errorDiv.style.display = 'block';
            } else {
                // Se o nome não existe, habilitar o botão de salvar
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
            console.error('Erro ao verificar nome:', error);
            // Em caso de erro na verificação, permitir o envio
            btnSalvar.disabled = false;
        });
    });

    // Adicione também este código para limpar o estado de erro quando o usuário modifica o nome
    document.getElementById('CONNOM').addEventListener('input', function() {
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
</script>