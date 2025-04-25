<?php
// Verificar se é edição
$id = isset($_GET['id']) ? $_GET['id'] : null;
$edicao = $id !== null;
$visualizacao = isset($_GET['visualizar']) && $_GET['visualizar'] === '1';

// Buscar dados da modalidade se for edição
$modalidade = [];
if ($edicao) {
    $sql = "SELECT * FROM CADMOD WHERE MODCOD = :id";
    
    $modalidade = fetchOne($sql, [':id' => $id]);
    
    if (!$modalidade) {
        showError('Modalidade não encontrada.');
        redirect('?page=modalidades');
        exit;
    }
}

// Definir título da página
$pageTitle = $edicao ? 'Editar Modalidade' : 'Nova Modalidade';
if ($visualizacao) {
    $pageTitle = 'Visualizar Modalidade';
}
?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary"><?php echo $pageTitle; ?></h6>
            <div class="btn-group">
                <a href="?page=modalidades" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>
        </div>
        <div class="card-body">
            <form id="form-modalidade" method="post" action="api.php">
                <input type="hidden" name="action" value="salvar_modalidade">
                <div class="alert alert-info">
                    Campos marcados com <span class="text-danger">*</span> são obrigatórios.
                </div>
                <?php if ($edicao): ?>
                <input type="hidden" name="MODCOD" value="<?php echo $modalidade['MODCOD']; ?>">
                <?php endif; ?>
                
                <div class="row">
                    <!-- Campo código -->
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="MODCOD">Código:</label>
                            <?php
                            if ($edicao) {
                                // Se for edição, mostrar o código existente
                                echo '<input type="text" class="form-control" id="MODCOD" name="MODCOD" value="' . $modalidade['MODCOD'] . '" readonly>';
                            } else {
                                // Para o SQL Server com IDENTITY, não enviamos o código no INSERT
                                echo '<input type="text" class="form-control" id="MODCOD" value="Automático" readonly>';
                            }
                            ?>
                        </div>
                    </div>
                    
                    <!-- Campo Descrição -->
                    <div class="col-md-10">
                        <div class="form-group">
                            <label for="MODDES">Descrição: <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="MODDES" name="MODDES" maxlength="40"
                                value="<?php echo $edicao ? $modalidade['MODDES'] : ''; ?>" <?php echo $visualizacao ? 'readonly' : ''; ?>>
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
                        <a href="?page=modalidades/form&id=<?php echo $id; ?>" class="btn btn-primary">
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
        // Interceptar o envio do formulário
        document.getElementById('form-modalidade').addEventListener('submit', function(e) {
            // Previne o envio padrão do formulário
            e.preventDefault();
            
            // Lista de campos obrigatórios (ID dos campos)
            const camposObrigatorios = ['MODDES'];
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
            // Exibir loader
            const loaderModal = showLoader('Salvando...');
            
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
                    // Verificar se foi erro de descrição já existente
                    if (data.message && data.message.includes('descrição')) {
                        // Marcar campo de descrição como inválido
                        const campo = document.getElementById('MODDES');
                        campo.classList.add('is-invalid');
                        
                        // Adicionar mensagem de erro abaixo do campo
                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'invalid-feedback';
                        errorDiv.textContent = 'Descrição já existente';
                        errorDiv.style.display = 'block';
                        campo.parentNode.appendChild(errorDiv);
                        
                        // Focar no campo
                        campo.focus();
                    }
                    
                    // Mostrar mensagem de erro
                    alert(data.message || 'Erro ao salvar modalidade.');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                hideLoader();
                alert('Erro ao processar requisição. Por favor, tente novamente.');
            });
        });
    });
</script>
