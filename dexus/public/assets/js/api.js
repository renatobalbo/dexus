/**
 * Sistema de Gestão Dexus - API JavaScript
 * Funções para comunicação com o backend
 */

/**
 * Realiza uma requisição AJAX para a API
 * @param {string} action - Ação a ser executada
 * @param {object} data - Dados a serem enviados (opcional)
 * @param {string} method - Método HTTP (GET, POST, PUT, DELETE)
 * @return {Promise} - Promessa com o resultado da requisição
 */
function apiRequest(action, data = null, method = 'GET') {
    // Construir URL base
    let url = 'api.php?action=' + action;
    
    // Configurar opções da requisição
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json'
        }
    };
    
    // Adicionar dados à requisição
    if (data !== null) {
        if (method === 'GET') {
            // Adicionar parâmetros à URL
            const params = new URLSearchParams();
            for (const key in data) {
                if (data[key] !== null && data[key] !== undefined) {
                    params.append(key, data[key]);
                }
            }
            url += '&' + params.toString();
        } else {
            // Adicionar corpo da requisição
            options.body = JSON.stringify(data);
        }
    }
    
    // Exibir indicador de carregamento
    const loader = showLoader();
    
    // Realizar requisição
    return fetch(url, options)
        .then(response => {
            // Verificar status da resposta
            if (!response.ok) {
                throw new Error('Erro na requisição: ' + response.status);
            }
            
            // Parsear resposta como JSON
            return response.json();
        })
        .finally(() => {
            // Ocultar indicador de carregamento
            hideLoader(loader);
        });
}

/**
 * Obtém estatísticas para o dashboard
 * @return {Promise} - Promessa com as estatísticas do dashboard
 */
function getDashboardStats() {
    return apiRequest('dashboard_stats');
}

/**
 * Obtém as OS recentes
 * @return {Promise} - Promessa com as OS recentes
 */
function getRecentOS() {
    return apiRequest('recent_os');
}

/**
 * Obtém os clientes recentes
 * @return {Promise} - Promessa com os clientes recentes
 */
function getRecentClientes() {
    return apiRequest('recent_clientes');
}

// ===================== FUNÇÕES DE CLIENTES =====================

/**
 * Lista clientes com paginação e filtros
 * @param {object} filtros - Filtros a serem aplicados
 * @return {Promise} - Promessa com a lista de clientes
 */
function listarClientes(filtros = {}) {
    return apiRequest('listar_clientes', filtros);
}

/**
 * Obtém dados de um cliente específico
 * @param {number} id - ID do cliente
 * @return {Promise} - Promessa com os dados do cliente
 */
function obterCliente(id) {
    return apiRequest('obter_cliente', { id });
}

/**
 * Salva (insere/atualiza) um cliente
 * @param {object} data - Dados do cliente
 * @return {Promise} - Promessa com o resultado da operação
 */
function salvarCliente(data) {
    return apiRequest('salvar_cliente', data, 'POST');
}

/**
 * Exclui um cliente
 * @param {number} id - ID do cliente
 * @return {Promise} - Promessa com o resultado da operação
 */
function excluirCliente(id) {
    return apiRequest('excluir_cliente', { id }, 'DELETE');
}

/**
 * Verifica se um cliente está em uso
 * @param {number} id - ID do cliente
 * @return {Promise} - Promessa com o resultado da verificação
 */
function verificarClienteUso(id) {
    return apiRequest('verificar_cliente_uso', { id });
}

// ===================== FUNÇÕES DE SERVIÇOS =====================

/**
 * Lista serviços com paginação e filtros
 * @param {object} filtros - Filtros a serem aplicados
 * @return {Promise} - Promessa com a lista de serviços
 */
function listarServicos(filtros = {}) {
    return apiRequest('listar_servicos', filtros);
}

/**
 * Obtém dados de um serviço específico
 * @param {number} id - ID do serviço
 * @return {Promise} - Promessa com os dados do serviço
 */
function obterServico(id) {
    return apiRequest('obter_servico', { id });
}

/**
 * Salva (insere/atualiza) um serviço
 * @param {object} data - Dados do serviço
 * @return {Promise} - Promessa com o resultado da operação
 */
function salvarServico(data) {
    return apiRequest('salvar_servico', data, 'POST');
}

/**
 * Exclui um serviço
 * @param {number} id - ID do serviço
 * @return {Promise} - Promessa com o resultado da operação
 */
function excluirServico(id) {
    return apiRequest('excluir_servico', { id }, 'DELETE');
}

/**
 * Verifica se um serviço está em uso
 * @param {number} id - ID do serviço
 * @return {Promise} - Promessa com o resultado da verificação
 */
function verificarServicoUso(id) {
    return apiRequest('verificar_servico_uso', { id });
}

// ===================== FUNÇÕES DE MODALIDADES =====================

/**
 * Lista modalidades com paginação e filtros
 * @param {object} filtros - Filtros a serem aplicados
 * @return {Promise} - Promessa com a lista de modalidades
 */
function listarModalidades(filtros = {}) {
    return apiRequest('listar_modalidades', filtros);
}

/**
 * Obtém dados de uma modalidade específica
 * @param {number} id - ID da modalidade
 * @return {Promise} - Promessa com os dados da modalidade
 */
function obterModalidade(id) {
    return apiRequest('obter_modalidade', { id });
}

/**
 * Salva (insere/atualiza) uma modalidade
 * @param {object} data - Dados da modalidade
 * @return {Promise} - Promessa com o resultado da operação
 */
function salvarModalidade(data) {
    return apiRequest('salvar_modalidade', data, 'POST');
}

/**
 * Exclui uma modalidade
 * @param {number} id - ID da modalidade
 * @return {Promise} - Promessa com o resultado da operação
 */
function excluirModalidade(id) {
    return apiRequest('excluir_modalidade', { id }, 'DELETE');
}

/**
 * Verifica se uma modalidade está em uso
 * @param {number} id - ID da modalidade
 * @return {Promise} - Promessa com o resultado da verificação
 */
function verificarModalidadeUso(id) {
    return apiRequest('verificar_modalidade_uso', { id });
}

// ===================== FUNÇÕES DE CONSULTORES =====================

/**
 * Lista consultores com paginação e filtros
 * @param {object} filtros - Filtros a serem aplicados
 * @return {Promise} - Promessa com a lista de consultores
 */
function listarConsultores(filtros = {}) {
    return apiRequest('listar_consultores', filtros);
}

/**
 * Obtém dados de um consultor específico
 * @param {number} id - ID do consultor
 * @return {Promise} - Promessa com os dados do consultor
 */
function obterConsultor(id) {
    return apiRequest('obter_consultor', { id });
}

/**
 * Salva (insere/atualiza) um consultor
 * @param {object} data - Dados do consultor
 * @return {Promise} - Promessa com o resultado da operação
 */
function salvarConsultor(data) {
    return apiRequest('salvar_consultor', data, 'POST');
}

/**
 * Exclui um consultor
 * @param {number} id - ID do consultor
 * @return {Promise} - Promessa com o resultado da operação
 */
function excluirConsultor(id) {
    return apiRequest('excluir_consultor', { id }, 'DELETE');
}

/**
 * Verifica se um consultor está em uso
 * @param {number} id - ID do consultor
 * @return {Promise} - Promessa com o resultado da verificação
 */
function verificarConsultorUso(id) {
    return apiRequest('verificar_consultor_uso', { id });
}

// ===================== FUNÇÕES DE ORDENS DE SERVIÇO =====================

/**
 * Lista ordens de serviço com paginação e filtros
 * @param {object} filtros - Filtros a serem aplicados
 * @return {Promise} - Promessa com a lista de ordens de serviço
 */
function listarOS(filtros = {}) {
    return apiRequest('listar_os', filtros);
}

/**
 * Obtém dados de uma ordem de serviço específica
 * @param {number} id - ID da ordem de serviço
 * @return {Promise} - Promessa com os dados da ordem de serviço
 */
function obterOS(id) {
    return apiRequest('obter_os', { id });
}

/**
 * Salva (insere/atualiza) uma ordem de serviço
 * @param {object} data - Dados da ordem de serviço
 * @return {Promise} - Promessa com o resultado da operação
 */
function salvarOS(data) {
    return apiRequest('salvar_os', data, 'POST');
}

/**
 * Exclui uma ordem de serviço
 * @param {number} id - ID da ordem de serviço
 * @return {Promise} - Promessa com o resultado da operação
 */
function excluirOS(id) {
    return apiRequest('excluir_os', { id }, 'DELETE');
}

/**
 * Verifica se uma ordem de serviço pode ser modificada
 * @param {number} id - ID da ordem de serviço
 * @return {Promise} - Promessa com o resultado da verificação
 */
function verificarOSModificavel(id) {
    return apiRequest('verificar_os_modificavel', { id });
}

/**
 * Gera o PDF de uma ordem de serviço
 * @param {number} id - ID da ordem de serviço
 * @return {Promise} - Promessa com a URL do PDF
 */
function gerarOSPDF(id) {
    return apiRequest('gerar_os_pdf', { id });
}

/**
 * Envia uma ordem de serviço por e-mail
 * @param {number} id - ID da ordem de serviço
 * @return {Promise} - Promessa com o resultado do envio
 */
function enviarOSEmail(id) {
    return apiRequest('enviar_os_email', { id });
}

// ===================== FUNÇÕES DE RELAÇÃO DE OS =====================

/**
 * Lista relação de ordens de serviço com paginação e filtros
 * @param {object} filtros - Filtros a serem aplicados
 * @return {Promise} - Promessa com a relação de ordens de serviço
 */
function listarRelacaoOS(filtros = {}) {
    return apiRequest('listar_relacao_os', filtros);
}

/**
 * Atualiza o status de faturamento de uma OS
 * @param {number} id - ID da OS
 * @param {object} data - Dados de faturamento
 * @return {Promise} - Promessa com o resultado da operação
 */
function atualizarOSFaturamento(id, data) {
    return apiRequest('atualizar_os_faturamento', { id, ...data }, 'PUT');
}

/**
 * Atualiza o status de cobrança de uma OS
 * @param {number} id - ID da OS
 * @param {object} data - Dados de cobrança
 * @return {Promise} - Promessa com o resultado da operação
 */
function atualizarOSCobranca(id, data) {
    return apiRequest('atualizar_os_cobranca', { id, ...data }, 'PUT');
}

/**
 * Obtém estatísticas da relação de OS
 * @param {object} filtros - Filtros a serem aplicados
 * @return {Promise} - Promessa com as estatísticas
 */
function obterEstatisticasRelacao(filtros = {}) {
    return apiRequest('obter_estatisticas_relacao', filtros);
}

/**
 * Gera um relatório em PDF da relação de OS
 * @param {object} filtros - Filtros a serem aplicados
 * @return {Promise} - Promessa com a URL do PDF
 */
function gerarRelacaoPDF(filtros = {}) {
    return apiRequest('gerar_relacao_pdf', filtros);
}

/**
 * Exporta a relação de OS para Excel
 * @param {object} filtros - Filtros a serem aplicados
 * @return {Promise} - Promessa com a URL do arquivo Excel
 */
function exportarRelacaoExcel(filtros = {}) {
    return apiRequest('exportar_relacao_excel', filtros);
}