<?php
// Obter ID da modalidade
$id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$id) {
    showError('Modalidade não informada.');
    redirect('?page=modalidades');
    exit;
}

// Buscar dados da modalidade
$sql = "SELECT * FROM CADMOD WHERE MODCOD = :id";

$modalidade = fetchOne($sql, [':id' => $id]);

if (!$modalidade) {
    showError('Modalidade não encontrada.');
    redirect('?page=modalidades');
    exit;
}

// Definir título da página
$pageTitle = 'Detalhes da Modalidade: ' . $modalidade['MODDES'];
?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary"><?php echo $pageTitle; ?></h6>
            <div class="btn-group">
                <a href="?page=modalidades" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
                <a href="?page=modalidades/form&id=<?php echo $id; ?>" class="btn btn-outline-primary btn-sm">
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
                            <p><?php echo $modalidade['MODCOD']; ?></p>
                        </div>
                        <div class="col-md-9">
                            <strong>Descrição:</strong>
                            <p><?php echo $modalidade['MODDES']; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Clientes Vinculados</h6>
                        </div>
                        <div class="card-body">
                            <?php
                            // Buscar clientes vinculados a esta modalidade
                            $sql = "SELECT CLICOD, CLIRAZ, CLIMUN
                                   FROM CADCLI
                                   WHERE CLIMOD = :id
                                   ORDER BY CLIRAZ
                                   OFFSET 0 ROWS FETCH NEXT 5 ROWS ONLY";
                            
                            $clientes = fetchAll($sql, [':id' => $id]);
                            
                            if ($clientes && count($clientes) > 0) {
                                echo '<div class="list-group">';
                                foreach ($clientes as $cliente) {
                                    echo '<a href="?page=clientes/visualizar&id=' . $cliente['CLICOD'] . '" class="list-group-item list-group-item-action">';
                                    echo '<div class="d-flex w-100 justify-content-between">';
                                    echo '<h6 class="mb-1">' . $cliente['CLIRAZ'] . '</h6>';
                                    echo '</div>';
                                    echo '<p class="mb-1">' . ($cliente['CLIMUN'] ?? '-') . '</p>';
                                    echo '</a>';
                                }
                                echo '</div>';
                                
                                // Contar o total de clientes com esta modalidade
                                $sqlCount = "SELECT COUNT(*) as total FROM CADCLI WHERE CLIMOD = :id";
                                $resultCount = fetchOne($sqlCount, [':id' => $id]);
                                $totalClientes = $resultCount ? $resultCount['total'] : 0;
                                
                                // Se houver mais clientes do que os exibidos, mostrar link para ver todos
                                if ($totalClientes > 5) {
                                    echo '<div class="mt-2 text-center">';
                                    echo '<a href="?page=clientes&modalidade=' . $id . '" class="btn btn-sm btn-outline-primary">Ver todos os clientes (' . $totalClientes . ')</a>';
                                    echo '</div>';
                                }
                            } else {
                                echo '<p class="text-center">Nenhum cliente vinculado a esta modalidade.</p>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>