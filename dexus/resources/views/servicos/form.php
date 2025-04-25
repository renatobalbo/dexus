<?php
// Verificar se é edição
$id = isset($_GET['id']) ? $_GET['id'] : null;
$edicao = $id !== null;
$visualizacao = isset($_GET['visualizar']) && $_GET['visualizar'] === '1';

// Buscar dados do serviço se for edição
$servico = [];
if ($edicao) {
    $sql = "SELECT * FROM CADSER WHERE SERCOD = :id";
    
    $servico = fetchOne($sql, [':id' => $id]);
    
    if (!$servico) {
        showError('Serviço não encontrado.');
        redirect('?page=servicos');
        exit;
    }
}

// Definir título da página
$pageTitle = $edicao ? 'Editar Serviço' : 'Novo Serviço';
if ($visualizacao) {
    $pageTitle = 'Visualizar Serviço';
}
?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary"><?php echo $pageTitle; ?></h6>
            <div class="btn-group">
                <a href="?page=servicos" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>
        </div>
        <div class="card-body">
            <form id="form-servico" method="post" action="api.php">
                <input type="hidden" name="action" value="salvar_servico">
                <div class="alert alert-info">
                    Campos marcados com <span class="text-danger">*</span> são obrigatórios.
                </div>
                <?php if ($edicao): ?>
                <input type="hidden" name="SERCOD" value="<?php echo $servico['SERCOD']; ?>">
                <?php endif; ?>
                
                <div class="row">
                    <!-- Campo código -->
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="SERCOD">Código:</label>
                            <?php
                            if ($edicao) {
                                // Se for edição, mostrar o código existente
                                echo '<input type="text" class="form-control" id="SERCOD" name="SERCOD" value="' . $servico['SERCOD'] . '" readonly>';
                            } else {
                                // Para o SQL Server com IDENTITY, não enviamos o código no INSERT
                                echo '<input type="text" class="form-control" id="SERCOD" value="Automático" readonly>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Campo Descrição -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="SERDES">Descrição: <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="SERDES" name="SERDES" maxlength="40"
                                value="<?php echo $edicao ? $servico['SERDES'] : ''; ?>" <?php echo $visualizacao ? 'readonly' : ''; ?>>
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
                        <a href="?page=servicos/form&id=<?php echo $id; ?>" class="btn btn-primary">
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
            const camposTexto = ['SERDES'];
            
            // Sanitizar cada campo
            camposTexto.forEach(campoId => {
                const campo = document.getElementById(campoId);
                if (campo && campo.value) {
                    campo.value = sanitizarTexto(campo.value);
                }
            });
        }

        // Interceptar o envio do formulário
        document.getElementById('form-servico').addEventListener('submit', function(e) {
            // Previne o envio padrão do formulário
            e.preventDefault();
            
            // Lista de campos obrigatórios (ID dos campos)
            const camposObrigatorios = ['SERDES'];
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
            
            // Verificar se a descrição está marcada como inválida (já existe)
            if (document.getElementById('SERDES').classList.contains('is-invalid')) {
                formValido = false;
                if (!campoComErro) campoComErro = document.getElementById('SERDES');
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
            const loaderModal = showLoader('Salvando serviço...');
            
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
                    alert(data.message || 'Erro ao salvar serviço.');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                hideLoader();
                alert('Erro ao processar requisição. Por favor, tente novamente.');
            });
        });

        // Adicionar verificação de descrição existente
        document.getElementById('SERDES').addEventListener('blur', function() {
            const descricao = this.value.trim();
            const btnSalvar = document.querySelector('button[type="submit"]');
            
            if (!descricao) {
                return; // Não verificar se não estiver preenchido
            }
            
            // Verificar se já existe no banco
            fetch(`api.php?action=verificar_servico_descricao&descricao=${encodeURIComponent(descricao)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.exists) {
                        alert('ATENÇÃO: Já existe um serviço cadastrado com esta descrição! Por favor, informe outra descrição para continuar.');
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
                        errorDiv.textContent = 'Descrição já cadastrada no sistema';
                        errorDiv.style.display = 'block';
                    } else {
                        // Se a descrição não existe, habilitar o botão de salvar
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
                    console.error('Erro ao verificar descrição:', error);
                    // Em caso de erro na verificação, permitir o envio
                    btnSalvar.disabled = false;
                });
        });

        // Adicione este código para limpar o estado de erro quando o usuário modifica a descrição
        document.getElementById('SERDES').addEventListener('input', function() {
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
    });
</script>