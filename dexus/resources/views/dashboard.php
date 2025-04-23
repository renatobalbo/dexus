<!-- Dashboard -->
<div class="container-fluid px-4">
    <!-- Cards de Estatísticas -->
    <div class="row mb-4">
        <!-- Total de Clientes -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total de Clientes</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="total-clientes">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- OS do Mês -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                OS do Mês</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="os-mes">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- OS Pendentes -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                OS Pendentes</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="os-pendentes">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- OS Não Faturadas -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                OS Não Faturadas</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="os-nao-faturadas">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos e Atividades Recentes -->
    <div class="row">
        <!-- Gráfico de OS nos últimos 6 meses -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Ordens de Serviço nos Últimos 6 Meses</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="osChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico de Modalidades -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Distribuição por Modalidade</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="modalidadesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- OS Recentes e Clientes Recentes -->
    <div class="row">
        <!-- OS Recentes -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">OS Recentes</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Número</th>
                                    <th>Data</th>
                                    <th>Cliente</th>
                                    <th>Serviço</th>
                                    <th>Ação</th>
                                </tr>
                            </thead>
                            <tbody id="os-recentes">
                                <tr>
                                    <td colspan="5" class="text-center">Carregando...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Clientes Recentes -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Clientes Recentes</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Razão Social</th>
                                    <th>Cidade</th>
                                    <th>Ação</th>
                                </tr>
                            </thead>
                            <tbody id="clientes-recentes">
                                <tr>
                                    <td colspan="4" class="text-center">Carregando...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar gráficos
    initCharts();
    
    // Carregar dados do dashboard
    loadDashboardData();
    
    // Carregar OS recentes
    loadRecentOS();
    
    // Carregar clientes recentes
    loadRecentClientes();
});

function initCharts() {
    // Gráfico de linhas para OS nos últimos 6 meses
    const osChartElement = document.getElementById('osChart');
    if (osChartElement) {
        const osChart = new Chart(osChartElement, {
            type: 'line',
            data: {
                labels: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho'],
                datasets: [{
                    label: 'Ordens de Serviço',
                    data: [0, 0, 0, 0, 0, 0], // Dados iniciais vazios
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.05)',
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
        
        // Guardar referência ao gráfico para atualização posterior
        window.osChart = osChart;
    }
    
    // Gráfico de pizza para modalidades
    const modalidadesChartElement = document.getElementById('modalidadesChart');
    if (modalidadesChartElement) {
        const modalidadesChart = new Chart(modalidadesChartElement, {
            type: 'pie',
            data: {
                labels: ['Consultoria', 'Desenvolvimento', 'Suporte', 'Treinamento'],
                datasets: [{
                    data: [0, 0, 0, 0], // Dados iniciais vazios
                    backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e'],
                    hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#dda20a'],
                    hoverBorderColor: "rgba(234, 236, 244, 1)"
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        
        // Guardar referência ao gráfico para atualização posterior
        window.modalidadesChart = modalidadesChart;
    }
}

function loadDashboardData() {
    // Função para carregar dados do dashboard via AJAX
    fetch('api.php?action=dashboard_stats')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Atualizar contadores
                document.getElementById('total-clientes').textContent = data.totalClientes || 0;
                document.getElementById('os-mes').textContent = data.osMes || 0;
                document.getElementById('os-pendentes').textContent = data.osPendentes || 0;
                document.getElementById('os-nao-faturadas').textContent = data.osNaoFaturadas || 0;
                
                // Atualizar gráficos
                updateCharts(data);
            } else {
                console.error('Erro ao carregar estatísticas:', data.message);
            }
        })
        .catch(error => {
            console.error('Erro na requisição:', error);
        });
}

function updateCharts(data) {
    // Atualizar gráfico de OS
    if (window.osChart && data.osMonthly) {
        window.osChart.data.labels = data.osMonthly.labels || [];
        window.osChart.data.datasets[0].data = data.osMonthly.values || [];
        window.osChart.update();
    }
    
    // Atualizar gráfico de modalidades
    if (window.modalidadesChart && data.modalidades) {
        window.modalidadesChart.data.labels = data.modalidades.labels || [];
        window.modalidadesChart.data.datasets[0].data = data.modalidades.values || [];
        window.modalidadesChart.update();
    }
}

function loadRecentOS() {
    // Função para carregar as OS recentes
    fetch('api.php?action=recent_os')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('os-recentes');
            
            if (data.success && data.os && data.os.length > 0) {
                container.innerHTML = '';
                
                data.os.forEach(os => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${os.OSNUM}</td>
                        <td>${formatDate(os.OSDATA)}</td>
                        <td>${os.CLIRAZ}</td>
                        <td>${os.SERDES}</td>
                        <td>
                            <a href="?page=os/visualizar&id=${os.OSNUM}" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    `;
                    container.appendChild(tr);
                });
            } else {
                container.innerHTML = '<tr><td colspan="5" class="text-center">Nenhuma OS encontrada</td></tr>';
            }
        })
        .catch(error => {
            console.error('Erro na requisição:', error);
            document.getElementById('os-recentes').innerHTML = 
                '<tr><td colspan="5" class="text-center text-danger">Erro ao carregar dados</td></tr>';
        });
}

function loadRecentClientes() {
    // Função para carregar os clientes recentes
    fetch('api.php?action=recent_clientes')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('clientes-recentes');
            
            if (data.success && data.clientes && data.clientes.length > 0) {
                container.innerHTML = '';
                
                data.clientes.forEach(cliente => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${cliente.CLICOD}</td>
                        <td>${cliente.CLIRAZ}</td>
                        <td>${cliente.CLIMUN}</td>
                        <td>
                            <a href="?page=clientes/visualizar&id=${cliente.CLICOD}" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    `;
                    container.appendChild(tr);
                });
            } else {
                container.innerHTML = '<tr><td colspan="4" class="text-center">Nenhum cliente encontrado</td></tr>';
            }
        })
        .catch(error => {
            console.error('Erro na requisição:', error);
            document.getElementById('clientes-recentes').innerHTML = 
                '<tr><td colspan="4" class="text-center text-danger">Erro ao carregar dados</td></tr>';
        });
}

function formatDate(dateString) {
    if (!dateString) return '';
    
    // Verificar se já está formatado
    if (dateString.includes('/')) return dateString;
    
    // Converter formato YYYY-MM-DD para DD/MM/YYYY
    const parts = dateString.split('-');
    if (parts.length !== 3) return dateString;
    
    return `${parts[2]}/${parts[1]}/${parts[0]}`;
}
</script>