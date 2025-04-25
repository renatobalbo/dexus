<?php
// Obter ID do serviço
$id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$id) {
    showError('Serviço não informado.');
    redirect('?page=servicos');
    exit;
}

// Buscar dados do serviço
$sql = "SELECT * FROM CADSER WHERE SERCOD = :id";

$servico = fetchOne($sql, [':id' => $id]);

if (!$servico) {
    showError('Serviço não encontrado.');
    redirect('?page=servicos');
    exit;
}

// Definir título da página
$pageTitle = 'Detalhes do Serviço: ' . $servico['SERDES'];
?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary"><?php echo $pageTitle; ?></h6>
            <div class="btn-group">
                <a href="?page=servicos" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
                <a href="?page=servicos/form&id=<?php echo $id; ?>" class="btn btn-outline-primary btn-sm">
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
                            <p><?php echo $servico['SERCOD']; ?></p>
                        </div>
                        <div class="col-md-9">
                            <strong>Descrição:</strong>
                            <p><?php echo $servico['SERDES']; ?></p>
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
                            // Buscar as últimas 5 OS que usam este serviço
                            $sql = "SELECT o.OSNUM, o.OSDATA, c.CLIRAZ
                                   FROM ORDSER o
                                   JOIN CADCLI c ON o.OSCLICOD = c.CLICOD
                                   WHERE o.OSSERCOD = :id
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
                                    echo '<a href="?page=os&servico=' . $id . '" class="btn btn-sm btn-outline-primary">Ver todas</a>';
                                    echo '</div>';
                                }
                            } else {
                                echo '<p class="text-center">Nenhuma OS encontrada para este serviço.</p>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>