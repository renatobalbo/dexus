<?php
// Obter ID do consultor
$id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$id) {
    showError('Consultor não informado.');
    redirect('?page=consultores');
    exit;
}

// Função para formatar telefone
function formatarTelefone($telefone) {
    // Remover caracteres não numéricos
    $telefone = preg_replace('/\D/', '', $telefone);
    
    // Verificar tamanho
    $length = strlen($telefone);
    
    if ($length === 11) {
        // Celular: (XX) 9XXXX-XXXX
        return sprintf('(%s) %s%s-%s',
            substr($telefone, 0, 2),
            substr($telefone, 2, 1),
            substr($telefone, 3, 4),
            substr($telefone, 7, 4)
        );
    } else if ($length === 10) {
        // Fixo: (XX) XXXX-XXXX
        return sprintf('(%s) %s-%s',
            substr($telefone, 0, 2),
            substr($telefone, 2, 4),
            substr($telefone, 6, 4)
        );
    }
    
    return $telefone;
}

// Buscar dados do consultor
$sql = "SELECT * FROM CADCON WHERE CONCOD = :id";

$consultor = fetchOne($sql, [':id' => $id]);

if (!$consultor) {
    showError('Consultor não encontrado.');
    redirect('?page=consultores');
    exit;
}

// Definir título da página
$pageTitle = 'Detalhes do Consultor: ' . $consultor['CONNOM'];
?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary"><?php echo $pageTitle; ?></h6>
            <div class="btn-group">
                <a href="?page=consultores" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
                <a href="?page=consultores/form&id=<?php echo $id; ?>" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-edit"></i> Editar
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <strong>Código:</strong>
                            <p><?php echo $consultor['CONCOD']; ?></p>
                        </div>
                        <div class="col-md-9">
                            <strong>Nome:</strong>
                            <p><?php echo $consultor['CONNOM']; ?></p>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Telefone:</strong>
                            <p><?php echo isset($consultor['CONTEL']) ? formatarTelefone($consultor['CONTEL']) : '-'; ?></p>
                        </div>
                        <div class="col-md-6">
                            <strong>E-mail:</strong>
                            <p><?php echo isset($consultor['CONEMA']) ? $consultor['CONEMA'] : '-'; ?></p>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Atuação:</strong>
                            <p><?php echo isset($consultor['CONATU']) ? $consultor['CONATU'] : '-'; ?></p>
                        </div>
                        <div class="col-md-6">
                            <strong>Valor Hora:</strong>
                            <p><?php echo isset($consultor['CONVAL']) ? formatMoney($consultor['CONVAL']) : '-'; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Ordens de Serviço</h6>
                        </div>
                        <div class="card-body">
                            <?php
                            // Buscar as últimas 5 OS do consultor
                            $sql = "SELECT o.OSNUM, o.OSDATA, c.CLIRAZ
                                   FROM ORDSER o
                                   JOIN CADCLI c ON o.OSCLICOD = c.CLICOD
                                   WHERE o.OSCONCOD = :id
                                   ORDER BY o.OSDATA DESC, o.OSNUM DESC
                                   OFFSET 0 ROWS FETCH NEXT 5 ROWS ONLY";
                            
                            $ordens = fetchAll($sql, [':id' => $id]);
                            
                            if ($ordens && count($ordens) > 0) {
                                echo '<div class="list-group">';
                                foreach ($ordens as $os) {
                                    $data = formatDate($os['OSDATA']);
                                    echo '<a href="?page=os/visualizar&id=' . $os['OSNUM'] . '" class="list-group-item list-group-item-action">';
                                    echo '<div class="d-flex w-100 justify-content-between">';
                                    echo '<h6 class="mb-1">OS #' . $os['OSNUM'] . '</h6>';
                                    echo '<small>' . $data . '</small>';
                                    echo '</div>';
                                    echo '<p class="mb-1">' . $os['CLIRAZ'] . '</p>';
                                    echo '</a>';
                                }
                                echo '</div>';
                                
                                // Link para ver todas
                                $totalOS = count($ordens);
                                if ($totalOS >= 5) {
                                    echo '<div class="mt-2 text-center">';
                                    echo '<a href="?page=os&consultor=' . $id . '" class="btn btn-sm btn-outline-primary">Ver todas</a>';
                                    echo '</div>';
                                }
                            } else {
                                echo '<p class="text-center">Nenhuma OS encontrada para este consultor.</p>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>