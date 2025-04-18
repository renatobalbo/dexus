/**
 * Sistema de Gestão Dexus - JavaScript Principal
 * Funções gerais da aplicação
 */

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tooltips do Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
    
    // Configurar alertas para fechamento automático
    configurarAlertasAutomaticos();
});

/**
 * Configura alertas para fechamento automático
 */
function configurarAlertasAutomaticos() {
    document.querySelectorAll('.alert:not(.alert-permanent)').forEach(alert => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getInstance(alert);
            if (bsAlert) {
                bsAlert.close();
            } else {
                alert.classList.remove('show');
                setTimeout(() => {
                    alert.remove();
                }, 150);
            }
        }, 5000); // Fechar após 5 segundos
    });
}

/**
 * Exibe um alerta na tela
 * @param {string} message - Mensagem a ser exibida
 * @param {string} type - Tipo de alerta (success, danger, warning, info)
 * @param {boolean} autoClose - Se o alerta deve ser fechado automaticamente
 */
function showAlert(message, type = 'info', autoClose = true) {
    // Criar o elemento de alerta
    const alertElement = document.createElement('div');
    alertElement.className = `alert alert-${type} alert-dismissible fade show ${autoClose ? '' : 'alert-permanent'}`;
    alertElement.role = 'alert';
    
    alertElement.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    `;
    
    // Adicionar ao container de alertas
    const alertContainer = document.querySelector('.alert-container');
    alertContainer.appendChild(alertElement);
    
    // Fechar automaticamente após 5 segundos, se solicitado
    if (autoClose) {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getInstance(alertElement);
            if (bsAlert) {
                bsAlert.close();
            } else {
                alertElement.classList.remove('show');
                setTimeout(() => {
                    alertElement.remove();
                }, 150);
            }
        }, 5000);
    }
    
    return alertElement;
}

/**
 * Exibe um diálogo de confirmação
 * @param {string} message - Mensagem a ser exibida
 * @param {Function} onConfirm - Função a ser executada se confirmado
 * @param {Function} onCancel - Função a ser executada se cancelado
 * @param {string} confirmText - Texto do botão de confirmação
 * @param {string} cancelText - Texto do botão de cancelamento
 */
function showConfirm(message, onConfirm, onCancel = null, confirmText = 'Confirmar', cancelText = 'Cancelar') {
    // Verificar se já existe um modal de confirmação
    let modal = document.getElementById('confirm-modal');
    
    // Se não existir, criar um novo
    if (!modal) {
        modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.id = 'confirm-modal';
        modal.tabIndex = '-1';
        modal.setAttribute('aria-labelledby', 'confirm-modal-label');
        modal.setAttribute('aria-hidden', 'true');
        
        modal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="confirm-modal-label">Confirmação</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body" id="confirm-modal-message">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="confirm-modal-cancel"></button>
                        <button type="button" class="btn btn-primary" id="confirm-modal-confirm"></button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
    }
    
    // Configurar mensagem e botões
    document.getElementById('confirm-modal-message').innerHTML = message;
    document.getElementById('confirm-modal-confirm').textContent = confirmText;
    document.getElementById('confirm-modal-cancel').textContent = cancelText;
    
    // Remover event listeners antigos
    const confirmButton = document.getElementById('confirm-modal-confirm');
    const cancelButton = document.getElementById('confirm-modal-cancel');
    
    const newConfirmButton = confirmButton.cloneNode(true);
    const newCancelButton = cancelButton.cloneNode(true);
    
    confirmButton.parentNode.replaceChild(newConfirmButton, confirmButton);
    cancelButton.parentNode.replaceChild(newCancelButton, cancelButton);
    
    // Adicionar event listeners
    newConfirmButton.addEventListener('click', function() {
        if (typeof onConfirm === 'function') {
            onConfirm();
        }
        bootstrap.Modal.getInstance(modal).hide();
    });
    
    newCancelButton.addEventListener('click', function() {
        if (typeof onCancel === 'function') {
            onCancel();
        }
    });
    
    // Exibir o modal
    const modalInstance = new bootstrap.Modal(modal);
    modalInstance.show();
}

/**
 * Função para formatar data (YYYY-MM-DD para DD/MM/YYYY)
 * @param {string} dateString - Data no formato YYYY-MM-DD
 * @return {string} - Data formatada DD/MM/YYYY
 */
function formatDate(dateString) {
    if (!dateString) return '';
    
    // Verificar se já está formatado
    if (dateString.includes('/')) return dateString;
    
    // Converter formato YYYY-MM-DD para DD/MM/YYYY
    const parts = dateString.split('-');
    if (parts.length !== 3) return dateString;
    
    return `${parts[2]}/${parts[1]}/${parts[0]}`;
}

/**
 * Formatar data para banco de dados (DD/MM/YYYY para YYYY-MM-DD)
 * @param {string} dateString - Data no formato DD/MM/YYYY
 * @return {string} - Data formatada YYYY-MM-DD
 */
function formatDateDB(dateString) {
    if (!dateString) return '';
    
    // Verificar se já está formatado
    if (dateString.includes('-')) return dateString;
    
    // Converter formato DD/MM/YYYY para YYYY-MM-DD
    const parts = dateString.split('/');
    if (parts.length !== 3) return dateString;
    
    return `${parts[2]}-${parts[1]}-${parts[0]}`;
}

/**
 * Formatar valor monetário
 * @param {number} value - Valor a ser formatado
 * @return {string} - Valor formatado (R$ 0,00)
 */
function formatMoney(value) {
    if (value === null || value === undefined) return '';
    
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(value);
}

/**
 * Formatar CPF/CNPJ
 * @param {string} doc - Documento a ser formatado
 * @param {string} type - Tipo de documento (F ou J)
 * @return {string} - Documento formatado
 */
function formatDocument(doc, type) {
    if (!doc) return '';
    
    // Remover caracteres não numéricos
    doc = doc.replace(/\D/g, '');
    
    if (type === 'F' || doc.length <= 11) {
        // CPF: 000.000.000-00
        return doc.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
    } else {
        // CNPJ: 00.000.000/0000-00
        return doc.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5');
    }
}

/**
 * Configurar máscaras para os campos
 */
function setupMasks() {
    // Máscara de CPF
    document.querySelectorAll('.cpf-mask').forEach(input => {
        input.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            this.value = value;
        });
    });
    
    // Máscara de CNPJ
    document.querySelectorAll('.cnpj-mask').forEach(input => {
        input.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            value = value.replace(/(\d{2})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d)/, '$1/$2');
            value = value.replace(/(\d{4})(\d{1,2})$/, '$1-$2');
            this.value = value;
        });
    });
    
    // Máscara de CPF/CNPJ
    document.querySelectorAll('.cpf-cnpj-mask').forEach(input => {
        input.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            
            if (value.length <= 11) {
                // CPF
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            } else {
                // CNPJ
                value = value.replace(/(\d{2})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d)/, '$1/$2');
                value = value.replace(/(\d{4})(\d{1,2})$/, '$1-$2');
            }
            
            this.value = value;
        });
    });
    
    // Máscara de telefone
    document.querySelectorAll('.phone-mask').forEach(input => {
        input.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            
            if (value.length > 10) {
                // Celular
                value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            } else {
                // Telefone fixo
                value = value.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
            }
            
            this.value = value;
        });
    });
    
    // Máscara de data
    document.querySelectorAll('.date-mask').forEach(input => {
        input.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            
            value = value.replace(/(\d{2})(\d)/, '$1/$2');
            value = value.replace(/(\d{2})(\d)/, '$1/$2');
            value = value.replace(/(\d{4})(\d)/, '$1');
            
            this.value = value;
        });
    });
    
    // Máscara de hora
    document.querySelectorAll('.time-mask').forEach(input => {
        input.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            
            value = value.replace(/(\d{2})(\d)/, '$1:$2');
            value = value.replace(/(\d{2})(?:\d+)?/, '$1');
            
            this.value = value;
        });
    });
    
    // Máscara de moeda
    document.querySelectorAll('.currency-mask').forEach(input => {
        input.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            
            if (value === '') {
                this.value = '';
                return;
            }
            
            value = parseFloat(value) / 100;
            
            this.value = value.toLocaleString('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            });
        });
    });
}

/**
 * Limpar máscaras de um formulário antes de enviar
 * @param {HTMLFormElement} form - Formulário a ser tratado
 */
function clearMasks(form) {
    // CPF/CNPJ
    form.querySelectorAll('.cpf-mask, .cnpj-mask, .cpf-cnpj-mask').forEach(input => {
        input.value = input.value.replace(/\D/g, '');
    });
    
    // Telefone
    form.querySelectorAll('.phone-mask').forEach(input => {
        input.value = input.value.replace(/\D/g, '');
    });
    
    // Data
    form.querySelectorAll('.date-mask').forEach(input => {
        const value = input.value;
        if (value) {
            input.value = formatDateDB(value);
        }
    });
    
    // Moeda
    form.querySelectorAll('.currency-mask').forEach(input => {
        const value = input.value.replace(/[^\d,]/g, '').replace(',', '.');
        input.value = value;
    });
}