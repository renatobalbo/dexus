<?php
// Obter ID da OS
$id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$id) {
    showError('Ordem de Serviço não informada.');
    redirect('?page=os');
    exit;
}

// Buscar dados da OS
$sql = "SELECT o.*, c.CLIRAZ, c.CLIRES, s.SERDES, con.CONNOM, m.MODDES 
        FROM ORDSER o
        LEFT JOIN CADCLI c ON o.OSCLICOD = c.CLICOD
        LEFT JOIN CADSER s ON o.OSSERCOD = s.SERCOD
        LEFT JOIN CADCON con ON o.OSCONCOD = con.CONCOD
        LEFT JOIN CADMOD m ON o.OSMODCOD = m.MODCOD
        WHERE o.OSNUM = :id";

$os = fetchOne($sql, [':id' => $id]);

if (!$os) {
    showError('Ordem de Serviço não encontrada.');
    redirect('?page=os');
    exit;
}

// Verificar se a OS já foi enviada
$osEnviada = isset($os['OSENV']) && $os['OSENV'] === 'S';

// Definir título da página
$pageTitle = 'Detalhes da Ordem de Serviço: ' . $os['OSNUM'];
?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary"><?php echo $pageTitle; ?></h6>
            <div class="btn-group">
                <a href="?page=os" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
                <?php if (!$osEnviada): ?>
                <a href="?page=os/form&id=<?php echo $id; ?>" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-edit"></i> Editar
                </a>
                <?php endif; ?>
                <a href="javascript:void(0);" onclick="gerarPDF(<?php echo $id; ?>)" class="btn btn-outline-danger btn-sm">
                    <i class="fas fa-file-pdf"></i> Imprimir
                </a>
                <?php if (!$osEnviada): ?>
                <a href="javascript:void(0);" onclick="enviarEmail(<?php echo $id; ?>)" class="btn btn-outline-success btn-sm">
                    <i class="fas fa-envelope"></i> Enviar
                </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="border-bottom pb-2">Informações Gerais</h5>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Número:</strong>
                            <p><?php echo $os['OSNUM']; ?></p>
                        </div>
                        <div class="col-md-6">
                            <strong>Data:</strong>
                            <p><?php echo formatDate($os['OSDATA']); ?></p>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Cliente:</strong>
                            <p><?php echo $os['CLIRAZ']; ?></p>
                        </div>
                        <div class="col-md-6">
                            <strong>Responsável:</strong>
                            <p><?php echo $os['CLIRES'] ?? '-'; ?></p>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Modalidade:</strong>
                            <p><?php echo $os['MODDES'] ?? '-'; ?></p>
                        </div>
                        <div class="col-md-6">
                            <strong>OS Enviada:</strong>
                            <p><?php echo $os['OSENV'] === 'S' ? 'Sim' : 'Não'; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <h5 class="border-bottom pb-2">Serviço e Consultor</h5>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Serviço:</strong>
                            <p><?php echo $os['SERDES']; ?></p>
                        </div>
                        <div class="col-md-6">
                            <strong>Consultor:</strong>
                            <p><?php echo $os['CONNOM']; ?></p>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <strong>Hora Início:</strong>
                            <p><?php echo $os['OSHINI'] ?? '-'; ?></p>
                        </div>
                        <div class="col-md-3">
                            <strong>Hora Fim:</strong>
                            <p><?php echo $os['OSHFIM'] ?? '-'; ?></p>
                        </div>
                        <div class="col-md-3">
                            <strong>Descontos:</strong>
                            <p><?php echo $os['OSHDES'] ?? '-'; ?></p>
                        </div>
                        <div class="col-md-3">
                            <strong>Traslado:</strong>
                            <p><?php echo $os['OSHTRA'] ?? '-'; ?></p>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Tempo Total:</strong>
                            <p><?php echo $os['OSHTOT'] ?? '-'; ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-12">
                    <h5 class="border-bottom pb-2">Detalhamento</h5>
                    <div class="p-3 bg-light rounded">
                        <?php 
                        if (isset($os['OSDET']) && !empty($os['OSDET'])) {
                            echo nl2br(h($os['OSDET']));
                        } else {
                            echo '<p class="text-muted">Nenhum detalhamento informado.</p>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Função para gerar PDF da OS
    function gerarPDF(id) {
        if (!id) return;
        
        // Exibir loader
        const loader = showLoader('Gerando PDF...');
        
        fetch(`api.php?action=gerar_os_pdf&id=${id}`)
            .then(response => response.json())
            .then(data => {
                hideLoader(loader);
                
                if (data.success && data.pdfUrl) {
                    // Abrir o PDF em uma nova aba
                    window.open(data.pdfUrl, '_blank');
                } else {
                    alert(data.message || 'Não foi possível gerar o PDF.');
                }
            })
            .catch(error => {
                hideLoader(loader);
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
                hideLoader(loader);
                
                if (data.success) {
                    alert(data.message);
                    // Recarregar a página para atualizar o status
                    window.location.reload();
                } else {
                    alert(data.message || 'Não foi possível enviar o e-mail.');
                }
            })
            .catch(error => {
                hideLoader(loader);
                console.error('Erro:', error);
                alert('Erro ao enviar o e-mail.');
            });
    }
</script>