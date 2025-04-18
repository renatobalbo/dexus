<!-- Rodapé -->
                <footer class="pt-5 my-5 text-muted border-top">
                    <div class="row">
                        <div class="col-md-6">
                            &copy; <?php echo COMPANY_NAME; ?> - <?php echo date('Y'); ?>
                        </div>
                        <div class="col-md-6 text-end">
                            <?php echo SYSTEM_NAME; ?> v<?php echo SYSTEM_VERSION; ?>
                        </div>
                    </div>
                </footer>
            </main>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
    <script src="<?php echo baseUrl('/assets/js/main.js'); ?>"></script>
    <script src="<?php echo baseUrl('/assets/js/api.js'); ?>"></script>
    <script src="<?php echo baseUrl('/assets/js/validation.js'); ?>"></script>
    
    <!-- Modal de Loader -->
    <div class="modal fade" id="loader-modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="loader-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                    <h5 id="loader-message">Processando, aguarde...</h5>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    // Função para exibir loader
    function showLoader(message = 'Processando, aguarde...') {
        document.getElementById('loader-message').textContent = message;
        const loaderModal = new bootstrap.Modal(document.getElementById('loader-modal'));
        loaderModal.show();
        return loaderModal;
    }
    
    // Função para ocultar loader
    function hideLoader() {
        const loaderModal = bootstrap.Modal.getInstance(document.getElementById('loader-modal'));
        if (loaderModal) {
            loaderModal.hide();
        }
    }
    </script>
</body>
</html>