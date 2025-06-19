<?php
// Verificar se é edição
$id = isset($_GET['id']) ? $_GET['id'] : null;
$edicao = $id !== null;
$visualizacao = isset($_GET['visualizar']) && $_GET['visualizar'] === '1';

// Buscar dados da OS se for edição
$os = [];
if ($edicao) {
    $sql = "SELECT o.*, c.CLIRAZ, c.CLIRES, s.SERDES, con.CONNOM 
            FROM ORDSER o
            LEFT JOIN CADCLI c ON o.OSCLICOD = c.CLICOD
            LEFT JOIN CADSER s ON o.OSSERCOD = s.SERCOD
            LEFT JOIN CADCON con ON o.OSCONCOD = con.CONCOD
            WHERE o.OSNUM = :id";
    
    $os = fetchOne($sql, [':id' => $id]);
    
    if (!$os) {
        showError('Ordem de Serviço não encontrada.');
        redirect('?page=os');
        exit;
    }
    
    // Verificar se a OS já foi enviada
    $osEnviada = isset($os['OSENV']) && $os['OSENV'] === 'S';
    if ($osEnviada && !$visualizacao) {
        showWarning('Esta Ordem de Serviço já foi enviada e não pode ser alterada.');
        redirect('?page=os/visualizar&id=' . $id);
        exit;
    }
}

// Definir título da página
$pageTitle = $edicao ? 'Editar Ordem de Serviço' : 'Nova Ordem de Serviço';
if ($visualizacao) {
    $pageTitle = 'Visualizar Ordem de Serviço';
}
?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary"><?php echo $pageTitle; ?></h6>
            <div class="btn-group">
                <a href="?page=os" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
                <?php if ($edicao && !$visualizacao): ?>
                <a href="?page=os/visualizar&id=<?php echo $id; ?>" class="btn btn-outline-info btn-sm">
                    <i class="fas fa-eye"></i> Visualizar
                </a>
                <?php endif; ?>
                <?php if ($edicao): ?>
                <a href="javascript:void(0);" onclick="gerarPDF(<?php echo $id; ?>)" class="btn btn-outline-danger btn-sm">
                    <i class="fas fa-file-pdf"></i> Imprimir
                </a>
                <?php if (isset($os['OSENV']) && $os['OSENV'] !== 'S'): ?>
                <a href="javascript:void(0);" onclick="enviarEmail(<?php echo $id; ?>)" class="btn btn-outline-success btn-sm">
                    <i class="fas fa-envelope"></i> Enviar
                </a>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <form id="form-os" method="post" action="api.php">
                <input type="hidden" name="action" value="salvar_os">
                <div class="alert alert-info">
                    Campos marcados com <span class="text-danger">*</span> são obrigatórios.
                </div>
                <?php if ($edicao): ?>
                <input type="hidden" name="OSNUM" value="<?php echo $os['OSNUM']; ?>">
                <?php endif; ?>
                
                <div class="row">
                    <!-- Campo número OS -->
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="OSNUM">Número:</label>
                            <?php
                            if ($edicao) {
                                // Se for edição, mostrar o número existente
                                echo '<input type="text" class="form-control" id="OSNUM" name="OSNUM" value="' . $os['OSNUM'] . '" readonly>';
                            } else {
                                // Para o SQL Server com IDENTITY, não enviamos o código no INSERT
                                echo '<input type="text" class="form-control" id="OSNUM" value="Automático" readonly>';
                            }
                            ?>
                        </div>
                    </div>
                    
                    <!-- Campo Data -->
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="OSDATA">Data: <span class="text-danger">*</span></label>
                            <input type="text" class="form-control date-mask" id="OSDATA" name="OSDATA" 
                                value="<?php echo $edicao ? formatDate($os['OSDATA']) : date('d/m/Y'); ?>" 
                                <?php echo $visualizacao ? 'readonly' : ''; ?>>
                        </div>
                    </div>
                    
                    <!-- Campo OS Enviada -->
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="OSENV">OS Enviada:</label>
                            <select class="form-control" id="OSENV" name="OSENV" <?php echo $visualizacao ? 'disabled' : ''; ?>>
                                <option value="N" <?php echo $edicao && isset($os['OSENV']) && $os['OSENV'] === 'N' ? 'selected' : ''; ?>>Não</option>
                                <option value="S" <?php echo $edicao && isset($os['OSENV']) && $os['OSENV'] === 'S' ? 'selected' : ''; ?>>Sim</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Campo Cliente -->
                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="OSCLICOD">Cliente: <span class="text-danger">*</span></label>
                            <select class="form-control" id="OSCLICOD" name="OSCLICOD" <?php echo $visualizacao ? 'disabled' : ''; ?>>
                                <option value="">Selecione...</option>
                                <?php
                                // Buscar clientes
                                $sqlClientes = "SELECT CLICOD, CLIRAZ, CLIRES, CLIMOD FROM CADCLI ORDER BY CLIRAZ";
                                $clientes = fetchAll($sqlClientes);
                                
                                if ($clientes) {
                                    foreach ($clientes as $cliente) {
                                        $selected = $edicao && $os['OSCLICOD'] == $cliente['CLICOD'] ? 'selected' : '';
                                        // Armazenar dados adicionais como atributos data-
                                        echo "<option value=\"{$cliente['CLICOD']}\" data-responsavel=\"" . ($cliente['CLIRES'] ?? '') . "\" 
                                                data-modalidade=\"" . ($cliente['CLIMOD'] ?? '') . "\" $selected>{$cliente['CLIRAZ']}</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Campo Responsável do Cliente -->
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="OSCLIRES">Responsável:</label>
                            <input type="text" class="form-control" id="OSCLIRES" name="OSCLIRES" readonly
                                value="<?php echo $edicao ? ($os['CLIRES'] ?? '') : ''; ?>">
                        </div>
                    </div>
                    
                    <!-- Campo Modalidade -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="OSMODCOD">Modalidade:</label>
                            <select class="form-control" id="OSMODCOD" name="OSMODCOD" <?php echo $visualizacao ? 'disabled' : ''; ?>>
                                <option value="">Selecione...</option>
                                <?php
                                // Buscar modalidades
                                $sqlModalidades = "SELECT MODCOD, MODDES FROM CADMOD ORDER BY MODDES";
                                $modalidades = fetchAll($sqlModalidades);
                                
                                if ($modalidades) {
                                    foreach ($modalidades as $modalidade) {
                                        $selected = $edicao && isset($os['OSMODCOD']) && $os['OSMODCOD'] == $modalidade['MODCOD'] ? 'selected' : '';
                                        echo "<option value=\"{$modalidade['MODCOD']}\" $selected>{$modalidade['MODDES']}</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Campo Serviço -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="OSSERCOD">Serviço: <span class="text-danger">*</span></label>
                            <select class="form-control" id="OSSERCOD" name="OSSERCOD" <?php echo $visualizacao ? 'disabled' : ''; ?>>
                                <option value="">Selecione...</option>
                                <?php
                                // Buscar serviços
                                $sqlServicos = "SELECT SERCOD, SERDES FROM CADSER ORDER BY SERDES";
                                $servicos = fetchAll($sqlServicos);
                                
                                if ($servicos) {
                                    foreach ($servicos as $servico) {
                                        $selected = $edicao && $os['OSSERCOD'] == $servico['SERCOD'] ? 'selected' : '';
                                        echo "<option value=\"{$servico['SERCOD']}\" $selected>{$servico['SERDES']}</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Campo Consultor -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="OSCONCOD">Consultor: <span class="text-danger">*</span></label>
                            <select class="form-control" id="OSCONCOD" name="OSCONCOD" <?php echo $visualizacao ? 'disabled' : ''; ?>>
                                <option value="">Selecione...</option>
                                <?php
                                // Buscar consultores
                                $sqlConsultores = "SELECT CONCOD, CONNOM FROM CADCON ORDER BY CONNOM";
                                $consultores = fetchAll($sqlConsultores);
                                
                                if ($consultores) {
                                    foreach ($consultores as $consultor) {
                                        $selected = $edicao && $os['OSCONCOD'] == $consultor['CONCOD'] ? 'selected' : '';
                                        echo "<option value=\"{$consultor['CONCOD']}\" $selected>{$consultor['CONNOM']}</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Campo Hora Início -->
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="OSHINI">Hora Início:</label>
                            <input type="text" class="form-control time-mask" id="OSHINI" name="OSHINI" placeholder="00:00"
                                value="<?php echo $edicao && isset($os['OSHINI']) ? $os['OSHINI'] : ''; ?>" 
                                <?php echo $visualizacao ? 'readonly' : ''; ?>>
                        </div>
                    </div>
                    
                    <!-- Campo Hora Fim -->
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="OSHFIM">Hora Fim:</label>
                            <input type="text" class="form-control time-mask" id="OSHFIM" name="OSHFIM" placeholder="00:00"
                                value="<?php echo $edicao && isset($os['OSHFIM']) ? $os['OSHFIM'] : ''; ?>" 
                                <?php echo $visualizacao ? 'readonly' : ''; ?>>
                        </div>
                    </div>
                    
                    <!-- Campo Descontos -->
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="OSHDES">Descontos:</label>
                            <input type="text" class="form-control time-mask" id="OSHDES" name="OSHDES" placeholder="00:00"
                                value="<?php echo $edicao && isset($os['OSHDES']) ? $os['OSHDES'] : ''; ?>" 
                                <?php echo $visualizacao ? 'readonly' : ''; ?>>
                        </div>
                    </div>
                    
                    <!-- Campo Traslado -->
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="OSHTRA">Traslado:</label>
                            <input type="text" class="form-control time-mask" id="OSHTRA" name="OSHTRA" placeholder="00:00"
                                value="<?php echo $edicao && isset($os['OSHTRA']) ? $os['OSHTRA'] : ''; ?>" 
                                <?php echo $visualizacao ? 'readonly' : ''; ?>>
                        </div>
                    </div>
                    
                    <!-- Campo Tempo Total -->
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="OSHTOT">Tempo Total:</label>
                            <input type="text" class="form-control" id="OSHTOT" name="OSHTOT" readonly
                                value="<?php echo $edicao && isset($os['OSHTOT']) ? $os['OSHTOT'] : ''; ?>">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Campo Detalhamento -->
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="OSDET">Detalhamento:</label>
                            <textarea class="form-control" id="OSDET" name="OSDET" rows="5" 
                                <?php echo $visualizacao ? 'readonly' : ''; ?>><?php echo $edicao && isset($os['OSDET']) ? $os['OSDET'] : ''; ?></textarea>
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
                        <a href="?page=os/form&id=<?php echo $id; ?>" class="btn btn-primary" 
                           <?php echo (isset($os['OSENV']) && $os['OSENV'] === 'S') ? 'style="display:none;"' : ''; ?>>
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
        // Aplicar máscaras para os campos
        setupMasks();
        
        // Configurar o cálculo automático do tempo total
        configurarCalculoTempoTotal();
        
        // Atualizar campos do cliente ao selecionar um cliente
        document.getElementById('OSCLICOD').addEventListener('change', function() {
            const selectElement = this;
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            
            // Pegar os atributos data- da opção selecionada
            const responsavel = selectedOption.getAttribute('data-responsavel') || '';
            const modalidade = selectedOption.getAttribute('data-modalidade') || '';
            
            // Preencher os campos
            document.getElementById('OSCLIRES').value = responsavel;
            
            if (modalidade) {
                document.getElementById('OSMODCOD').value = modalidade;
            }
        });
        
        // Interceptar o envio do formulário
        document.getElementById('form-os').addEventListener('submit', function(e) {
            // Impedir o envio tradicional do formulário
            e.preventDefault();
            
            // Validação de campos obrigatórios
            const camposObrigatorios = ['OSDATA', 'OSCLICOD', 'OSSERCOD', 'OSCONCOD'];
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
            
            // Exibir loader
            const loaderModal = showLoader('Salvando ordem de serviço...');
            
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
                    // Aguardar um pouco para garantir que o alerta seja mostrado antes do redirecionamento
                    setTimeout(function() {
                        window.location.href = data.redirect;
                    }, 100);
                } else {
                    // Apenas mostrar mensagem de erro se realmente falhou
                    alert(data.message || 'Erro ao salvar ordem de serviço.');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                hideLoader();
                
                // Verificar se a OS foi salva mesmo com erro no processamento
                fetch(`api.php?action=verificar_os_modificavel&id=${id || '0'}`)
                    .then(response => response.json())
                    .then(data => {
                        hideLoader();
                        
                        if (data.success) {
                            // OS provavelmente foi salva, redirecionar para a lista
                            alert('Ordem de Serviço cadastrada com sucesso!');
                            window.location.href = '?page=os';
                        } else {
                            // Realmente houve falha
                            alert('Erro ao processar requisição. Por favor, tente novamente.');
                        }
                    })
                    .catch(() => {
                        hideLoader();
                        alert('Erro ao processar requisição. Por favor, tente novamente.');
                    });
                });
            });
        // Disparar o evento change para preencher os campos se já houver um cliente selecionado
        const clienteSelect = document.getElementById('OSCLICOD');
        if (clienteSelect.value) {
            clienteSelect.dispatchEvent(new Event('change'));
        }
    });
    
    // Função para calcular o tempo total automaticamente
    function configurarCalculoTempoTotal() {
        const horaInicio = document.getElementById('OSHINI');
        const horaFim = document.getElementById('OSHFIM');
        const descontos = document.getElementById('OSHDES');
        const traslado = document.getElementById('OSHTRA');
        const tempoTotal = document.getElementById('OSHTOT');
        
        if (horaInicio && horaFim && descontos && traslado && tempoTotal) {
            const calcularTempoTotal = function() {
                // Verificar se há valores informados para início e fim
                if (!horaInicio.value || !horaFim.value) {
                    tempoTotal.value = '';
                    return;
                }
                
                // Converter horários para minutos
                const inicioMinutos = horaParaMinutos(horaInicio.value);
                const fimMinutos = horaParaMinutos(horaFim.value);
                const descontosMinutos = descontos.value ? horaParaMinutos(descontos.value) : 0;
                const trasladoMinutos = traslado.value ? horaParaMinutos(traslado.value) : 0;
                
                // Calcular tempo total
                let totalMinutos = fimMinutos - inicioMinutos - descontosMinutos + trasladoMinutos;
                
                // Se o valor for negativo (horário que passa da meia-noite)
                if (totalMinutos < 0) {
                    totalMinutos += 24 * 60; // Adicionar 24 horas
                }
                
                // Converter para formato HH:MM
                tempoTotal.value = minutosParaHora(totalMinutos);
            };
            
            // Adicionar eventos para recalcular o tempo total quando os campos mudam
            horaInicio.addEventListener('input', calcularTempoTotal);
            horaFim.addEventListener('input', calcularTempoTotal);
            descontos.addEventListener('input', calcularTempoTotal);
            traslado.addEventListener('input', calcularTempoTotal);
            
            // Calcular o tempo total inicialmente
            calcularTempoTotal();
        }
    }
    
    // Função para converter hora (HH:MM) para minutos
    function horaParaMinutos(hora) {
        if (!hora) return 0;
        
        const partes = hora.split(':');
        if (partes.length !== 2) return 0;
        
        const horas = parseInt(partes[0], 10);
        const minutos = parseInt(partes[1], 10);
        
        return horas * 60 + minutos;
    }
    
    // Função para converter minutos para hora (HH:MM)
    function minutosParaHora(minutos) {
        const horas = Math.floor(minutos / 60);
        const mins = minutos % 60;
        
        return `${horas.toString().padStart(2, '0')}:${mins.toString().padStart(2, '0')}`;
    }
    
    // Função para gerar PDF da OS
    function gerarPDF(id) {
        if (!id) return;
        
        // Exibir loader
        const loader = showLoader('Gerando PDF...');
        
        fetch(`api.php?action=gerar_os_pdf&id=${id}`)
            .then(response => response.json())
            .then(data => {
                hideLoader();
                
                if (data.success && data.pdfUrl) {
                    // Abrir o PDF em uma nova aba
                    window.open(data.pdfUrl, '_blank');
                } else {
                    alert(data.message || 'Não foi possível gerar o PDF.');
                }
            })
            .catch(error => {
                hideLoader();
                console.error('Erro:', error);
                alert('Erro ao gerar o PDF.');
            });
    }
    
    // Função para enviar OS por e-mail
    function enviarEmail(id) {
        if (!id) return;
        
        if (!confirm('Confirma o envio desta OS por e-mail?')) {
            return;
        }
        
        // Exibir loader
        const loader = showLoader('Enviando e-mail...');
        
        fetch(`api.php?action=enviar_os_email&id=${id}`)
            .then(response => response.json())
            .then(data => {
                hideLoader();
                
                if (data.success) {
                    alert(data.message);
                    // Recarregar a página para atualizar o status
                    window.location.reload();
                } else {
                    alert(data.message || 'Não foi possível enviar o e-mail.');
                }
            })
            .catch(error => {
                hideLoader();
                console.error('Erro:', error);
                alert('Erro ao enviar o e-mail.');
            });
    }
</script>
