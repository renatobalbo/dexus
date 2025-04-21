<?php
// Obter ID do cliente
$id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$id) {
    showError('Cliente não informado.');
    redirect('?page=clientes');
    exit;
}

// Buscar dados do cliente
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

// Definir título da página
$pageTitle = 'Detalhes do Cliente: ' . $cliente['CLIRAZ'];
?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary"><?php echo $pageTitle; ?></h6>
            <div class="btn-group">
                <a href="?page=clientes" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
                <a href="?page=clientes/form&id=<?php echo $id; ?>" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-edit"></i> Editar
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Código:</strong>
                            <p><?php echo $cliente['CLICOD']; ?></p>
                        </div>
                        <div class="col-md-4">
                            <strong>Tipo:</strong>
                            <p><?php echo $cliente['CLITIP'] === 'F' ? 'F - Pessoa Física' : 'J - Pessoa Jurídica'; ?></p>
                        </div>
                        <div class="col-md-4">
                            <strong>CPF/CNPJ:</strong>
                            <p><?php echo formatDocument($cliente['CLIDOC'], $cliente['CLITIP']); ?></p>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Razão Social:</strong>
                            <p><?php echo $cliente['CLIRAZ']; ?></p>
                        </div>
                        <div class="col-md-6">
                            <strong>Nome Fantasia:</strong>
                            <p><?php echo $cliente['CLIFAN'] ?? '-'; ?></p>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <strong>Município:</strong>
                            <p><?php echo $cliente['CLIMUN'] ?? '-'; ?></p>
                        </div>
                        <div class="col-md-4">
                            <strong>UF:</strong>
                            <p><?php echo $cliente['CLIEST'] ?? '-'; ?></p>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Modalidade:</strong>
                            <p><?php echo $cliente['MODDES'] ?? '-'; ?></p>
                        </div>
                        <div class="col-md-6">
                            <strong>Responsável:</strong>
                            <p><?php echo $cliente['CLIRES'] ?? '-'; ?></p>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Valor Hora:</strong>
                            <p><?php echo isset($cliente['CLIVAL']) ? formatMoney($cliente['CLIVAL']) : '-'; ?></p>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>E-mail OS:</strong>
                            <p><?php echo $cliente['CLIEOS'] ?? '-'; ?></p>
                        </div>
                        <div class="col-md-6">
                            <strong>E-mail NF:</strong>
                            <p><?php echo $cliente['CLIENF'] ?? '-'; ?></p>
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
                            // Buscar as últimas 5 OS do cliente
                            $sql = "SELECT o.OSNUM, o.OSDATA, s.SERDES
                                   FROM ORDSER o
                                   JOIN CADSER s ON o.OSSERCOD = s.SERCOD
                                   WHERE o.OSCLICOD = :id
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
                                    echo '<p class="mb-1">' . $os['SERDES'] . '</p>';
                                    echo '</a>';
                                }
                                echo '</div>';
                                
                                // Link para ver todas
                                $totalOS = count($ordens);
                                if ($totalOS >= 5) {
                                    echo '<div class="mt-2 text-center">';
                                    echo '<a href="?page=os&cliente=' . $id . '" class="btn btn-sm btn-outline-primary">Ver todas</a>';
                                    echo '</div>';
                                }
                            } else {
                                echo '<p class="text-center">Nenhuma OS encontrada para este cliente.</p>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>