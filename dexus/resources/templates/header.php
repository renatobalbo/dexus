<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . SYSTEM_NAME : SYSTEM_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo baseUrl('/assets/css/style.css'); ?>">
    <?php if (isset($printMode) && $printMode): ?>
    <link rel="stylesheet" href="<?php echo baseUrl('/assets/css/print.css'); ?>">
    <?php endif; ?>
</head>
<body>
    <!-- Mensagens de alerta -->
    <div class="alert-container position-fixed top-0 end-0 p-3">
        <?php 
        // Exibir mensagens de sucesso
        $successMessage = getSuccessMessage();
        if ($successMessage): 
        ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $successMessage; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
        <?php endif; ?>

        <?php 
        // Exibir mensagens de erro
        $errorMessage = getErrorMessage();
        if ($errorMessage): 
        ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $errorMessage; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
        <?php endif; ?>

        <?php 
        // Exibir mensagens de aviso
        $warningMessage = getWarningMessage();
        if ($warningMessage): 
        ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <?php echo $warningMessage; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Conteúdo -->
    <div class="container-fluid">
        <div class="row">
            <?php if (!isset($hideSidebar) || !$hideSidebar): ?>
            <!-- Sidebar -->
            <?php include_once BASE_PATH . '/resources/templates/menu.php'; ?>
            
            <!-- Conteúdo principal -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <?php else: ?>
            <!-- Conteúdo principal (tela cheia) -->
            <main class="col-12 px-md-4">
            <?php endif; ?>
                
                <!-- Cabeçalho da página -->
                <?php if (!isset($hidePageHeader)): ?>
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><?php echo $pageTitle; ?></h1>
                    <?php if (isset($pageActions)): ?>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <?php echo $pageActions; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>