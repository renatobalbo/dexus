/**
 * Sistema de Gestão Dexus - Validação JavaScript
 * Funções para validação de formulários
 */

/**
 * Valida um CPF
 * @param {string} cpf - CPF a ser validado
 * @return {boolean} - Indica se o CPF é válido
 */
function validarCPF(cpf) {
    // Remover caracteres não numéricos
    cpf = cpf.replace(/\D/g, '');
    
    // Verificar se tem 11 dígitos
    if (cpf.length !== 11) {
        return false;
    }
    
    // Verificar se todos os dígitos são iguais
    if (/^(\d)\1+$/.test(cpf)) {
        return false;
    }
    
    // Cálculo do primeiro dígito verificador
    let soma = 0;
    for (let i = 0; i < 9; i++) {
        soma += parseInt(cpf.charAt(i)) * (10 - i);
    }
    let resto = 11 - (soma % 11);
    let dv1 = resto > 9 ? 0 : resto;
    
    // Cálculo do segundo dígito verificador
    soma = 0;
    for (let i = 0; i < 10; i++) {
        soma += parseInt(cpf.charAt(i)) * (11 - i);
    }
    resto = 11 - (soma % 11);
    let dv2 = resto > 9 ? 0 : resto;
    
    // Verificar se os dígitos verificadores estão corretos
    return (parseInt(cpf.charAt(9)) === dv1 && parseInt(cpf.charAt(10)) === dv2);
}

/**
 * Valida um CNPJ
 * @param {string} cnpj - CNPJ a ser validado
 * @return {boolean} - Indica se o CNPJ é válido
 */
function validarCNPJ(cnpj) {
    // Remover caracteres não numéricos
    cnpj = cnpj.replace(/\D/g, '');
    
    // Verificar se tem 14 dígitos
    if (cnpj.length !== 14) {
        return false;
    }
    
    // Verificar se todos os dígitos são iguais
    if (/^(\d)\1+$/.test(cnpj)) {
        return false;
    }
    
    // Cálculo do primeiro dígito verificador
    let tamanho = cnpj.length - 2;
    let numeros = cnpj.substring(0, tamanho);
    const digitos = cnpj.substring(tamanho);
    let soma = 0;
    let pos = tamanho - 7;
    
    for (let i = tamanho; i >= 1; i--) {
        soma += numeros.charAt(tamanho - i) * pos--;
        if (pos < 2) {
            pos = 9;
        }
    }
    
    let resultado = soma % 11 < 2 ? 0 : 11 - (soma % 11);
    if (resultado !== parseInt(digitos.charAt(0))) {
        return false;
    }
    
    // Cálculo do segundo dígito verificador
    tamanho = tamanho + 1;
    numeros = cnpj.substring(0, tamanho);
    soma = 0;
    pos = tamanho - 7;
    
    for (let i = tamanho; i >= 1; i--) {
        soma += numeros.charAt(tamanho - i) * pos--;
        if (pos < 2) {
            pos = 9;
        }
    }
    
    resultado = soma % 11 < 2 ? 0 : 11 - (soma % 11);
    
    return (resultado === parseInt(digitos.charAt(1)));
}

/**
 * Valida um endereço de e-mail
 * @param {string} email - E-mail a ser validado
 * @return {boolean} - Indica se o e-mail é válido
 */
function validarEmail(email) {
    const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
}

/**
 * Valida uma data
 * @param {string} data - Data a ser validada (formato DD/MM/YYYY)
 * @return {boolean} - Indica se a data é válida
 */
function validarData(data) {
    // Verificar formato
    if (!/^\d{2}\/\d{2}\/\d{4}$/.test(data)) {
        return false;
    }
    
    // Extrair dia, mês e ano
    const partes = data.split('/');
    const dia = parseInt(partes[0], 10);
    const mes = parseInt(partes[1], 10);
    const ano = parseInt(partes[2], 10);
    
    // Verificar limites básicos
    if (mes < 1 || mes > 12) {
        return false;
    }
    
    // Obter o último dia do mês
    const ultimoDia = new Date(ano, mes, 0).getDate();
    
    // Verificar dia
    if (dia < 1 || dia > ultimoDia) {
        return false;
    }
    
    return true;
}

/**
 * Valida uma hora
 * @param {string} hora - Hora a ser validada (formato HH:MM)
 * @return {boolean} - Indica se a hora é válida
 */
function validarHora(hora) {
    // Verificar formato
    if (!/^\d{2}:\d{2}$/.test(hora)) {
        return false;
    }
    
    // Extrair horas e minutos
    const partes = hora.split(':');
    const horas = parseInt(partes[0], 10);
    const minutos = parseInt(partes[1], 10);
    
    // Verificar limites
    if (horas < 0 || horas > 23 || minutos < 0 || minutos > 59) {
        return false;
    }
    
    return true;
}

/**
 * Validação de formulário de cliente
 */
function validarFormCliente() {
    let valido = true;
    const formulario = document.getElementById('form-cliente');
    
    // Limpar mensagens de erro anteriores
    limparMensagensErro();
    
    // Validar tipo
    const tipo = document.getElementById('CLITIP');
    if (!tipo.value) {
        exibirErro(tipo, 'O tipo de pessoa é obrigatório.');
        valido = false;
    }
    
    // Validar documento (CPF/CNPJ)
    const documento = document.getElementById('CLIDOC');
    if (!documento.value) {
        exibirErro(documento, 'O CPF/CNPJ é obrigatório.');
        valido = false;
    } else {
        // Validar formato conforme o tipo
        if (tipo.value === 'F') {
            if (!validarCPF(documento.value)) {
                exibirErro(documento, 'CPF inválido.');
                valido = false;
            }
        } else if (tipo.value === 'J') {
            if (!validarCNPJ(documento.value)) {
                exibirErro(documento, 'CNPJ inválido.');
                valido = false;
            }
        }
    }
    
    // Validar razão social
    const razaoSocial = document.getElementById('CLIRAZ');
    if (!razaoSocial.value) {
        exibirErro(razaoSocial, 'A razão social é obrigatória.');
        valido = false;
    }
    
    // Validar e-mail OS (se preenchido)
    const emailOS = document.getElementById('CLIEOS');
    if (emailOS.value && !validarEmail(emailOS.value)) {
        exibirErro(emailOS, 'E-mail inválido.');
        valido = false;
    }
    
    // Validar e-mail NF (se preenchido)
    const emailNF = document.getElementById('CLIENF');
    if (emailNF.value && !validarEmail(emailNF.value)) {
        exibirErro(emailNF, 'E-mail inválido.');
        valido = false;
    }
    
    return valido;
}

/**
 * Validação de formulário de serviço
 */
function validarFormServico() {
    let valido = true;
    const formulario = document.getElementById('form-servico');
    
    // Limpar mensagens de erro anteriores
    limparMensagensErro();
    
    // Validar descrição
    const descricao = document.getElementById('SERDES');
    if (!descricao.value) {
        exibirErro(descricao, 'A descrição do serviço é obrigatória.');
        valido = false;
    }
    
    return valido;
}

/**
 * Validação de formulário de modalidade
 */
function validarFormModalidade() {
    let valido = true;
    const formulario = document.getElementById('form-modalidade');
    
    // Limpar mensagens de erro anteriores
    limparMensagensErro();
    
    // Validar descrição
    const descricao = document.getElementById('MODDES');
    if (!descricao.value) {
        exibirErro(descricao, 'A descrição da modalidade é obrigatória.');
        valido = false;
    }
    
    return valido;
}

/**
 * Validação de formulário de consultor
 */
function validarFormConsultor() {
    let valido = true;
    const formulario = document.getElementById('form-consultor');
    
    // Limpar mensagens de erro anteriores
    limparMensagensErro();
    
    // Validar nome
    const nome = document.getElementById('CONNOM');
    if (!nome.value) {
        exibirErro(nome, 'O nome do consultor é obrigatório.');
        valido = false;
    }
    
    // Validar e-mail (se preenchido)
    const email = document.getElementById('CONEMA');
    if (email.value && !validarEmail(email.value)) {
        exibirErro(email, 'E-mail inválido.');
        valido = false;
    }
    
    return valido;
}

/**
 * Validação de formulário de ordem de serviço
 */
function validarFormOS() {
    let valido = true;
    const formulario = document.getElementById('form-os');
    
    // Limpar mensagens de erro anteriores
    limparMensagensErro();
    
    // Validar cliente
    const cliente = document.getElementById('OSCLICOD');
    if (!cliente.value) {
        exibirErro(cliente, 'O cliente é obrigatório.');
        valido = false;
    }
    
    // Validar data
    const data = document.getElementById('OSDATA');
    if (!data.value) {
        exibirErro(data, 'A data é obrigatória.');
        valido = false;
    } else if (!validarData(data.value)) {
        exibirErro(data, 'Data inválida.');
        valido = false;
    }
    
    // Validar serviço
    const servico = document.getElementById('OSSERCOD');
    if (!servico.value) {
        exibirErro(servico, 'O serviço é obrigatório.');
        valido = false;
    }
    
    // Validar consultor
    const consultor = document.getElementById('OSCONCOD');
    if (!consultor.value) {
        exibirErro(consultor, 'O consultor é obrigatório.');
        valido = false;
    }
    
    // Validar hora início (se preenchida)
    const horaInicio = document.getElementById('OSHINI');
    if (horaInicio.value && !validarHora(horaInicio.value)) {
        exibirErro(horaInicio, 'Hora inválida.');
        valido = false;
    }
    
    // Validar hora fim (se preenchida)
    const horaFim = document.getElementById('OSHFIM');
    if (horaFim.value && !validarHora(horaFim.value)) {
        exibirErro(horaFim, 'Hora inválida.');
        valido = false;
    }
    
    return valido;
}

/**
 * Exibe mensagem de erro para um campo
 * @param {HTMLElement} campo - Campo com erro
 * @param {string} mensagem - Mensagem de erro
 */
function exibirErro(campo, mensagem) {
    campo.classList.add('is-invalid');
    
    // Criar div de feedback
    const feedback = document.createElement('div');
    feedback.className = 'invalid-feedback';
    feedback.innerText = mensagem;
    
    // Adicionar após o campo
    campo.parentNode.appendChild(feedback);
}

/**
 * Limpa todas as mensagens de erro do formulário
 */
function limparMensagensErro() {
    // Remover classe de erro dos campos
    document.querySelectorAll('.is-invalid').forEach(campo => {
        campo.classList.remove('is-invalid');
    });
    
    // Remover mensagens de erro
    document.querySelectorAll('.invalid-feedback').forEach(feedback => {
        feedback.remove();
    });
}

/**
 * Configura campo CPF/CNPJ conforme o tipo de pessoa
 */
function configurarCampoCPFCNPJ() {
    const tipoPessoa = document.getElementById('CLITIP');
    const cpfCnpj = document.getElementById('CLIDOC');
    const labelCpfCnpj = document.querySelector('label[for="CLIDOC"]');
    
    if (tipoPessoa && cpfCnpj && labelCpfCnpj) {
        tipoPessoa.addEventListener('change', function() {
            if (this.value === 'F') {
                // Pessoa Física - CPF
                labelCpfCnpj.innerText = 'CPF:';
                cpfCnpj.setAttribute('placeholder', '000.000.000-00');
                cpfCnpj.classList.remove('cnpj-mask');
                cpfCnpj.classList.remove('cpf-cnpj-mask');
                cpfCnpj.classList.add('cpf-mask');
            } else if (this.value === 'J') {
                // Pessoa Jurídica - CNPJ
                labelCpfCnpj.innerText = 'CNPJ:';
                cpfCnpj.setAttribute('placeholder', '00.000.000/0000-00');
                cpfCnpj.classList.remove('cpf-mask');
                cpfCnpj.classList.remove('cpf-cnpj-mask');
                cpfCnpj.classList.add('cnpj-mask');
            } else {
                // Tipo não definido
                labelCpfCnpj.innerText = 'CPF/CNPJ:';
                cpfCnpj.setAttribute('placeholder', '');
                cpfCnpj.classList.remove('cpf-mask');
                cpfCnpj.classList.remove('cnpj-mask');
                cpfCnpj.classList.add('cpf-cnpj-mask');
            }
            
            // Limpar campo
            cpfCnpj.value = '';
        });
    }
}

/**
 * Configura cálculo automático do tempo total
 */
function configurarCalculoTempoTotal() {
    // Campos de tempo
    const horaInicio = document.getElementById('OSHINI');
    const horaFim = document.getElementById('OSHFIM');
    const descontos = document.getElementById('OSHDES');
    const traslado = document.getElementById('OSHTRA');
    const tempoTotal = document.getElementById('OSHTOT');
    
    if (horaInicio && horaFim && descontos && traslado && tempoTotal) {
        // Função para calcular o tempo total
        const calcularTempoTotal = function() {
            // Verificar se os campos necessários estão preenchidos
            if (!horaInicio.value || !horaFim.value) {
                tempoTotal.value = '';
                return;
            }
            
            // Converter tempos para minutos
            const inicioMinutos = horaParaMinutos(horaInicio.value);
            const fimMinutos = horaParaMinutos(horaFim.value);
            const descontosMinutos = descontos.value ? horaParaMinutos(descontos.value) : 0;
            const trasladoMinutos = traslado.value ? horaParaMinutos(traslado.value) : 0;
            
            // Calcular tempo total
            let totalMinutos = fimMinutos - inicioMinutos - descontosMinutos + trasladoMinutos;
            
            // Se o resultado for negativo (trabalho que passa da meia-noite)
            if (totalMinutos < 0) {
                totalMinutos += 24 * 60; // Adicionar 24 horas
            }
            
            // Converter de volta para formato HH:MM
            tempoTotal.value = minutosParaHora(totalMinutos);
        };
        
        // Adicionar event listeners para recalcular o tempo total
        horaInicio.addEventListener('input', calcularTempoTotal);
        horaFim.addEventListener('input', calcularTempoTotal);
        descontos.addEventListener('input', calcularTempoTotal);
        traslado.addEventListener('input', calcularTempoTotal);
    }
}

/**
 * Converte hora no formato HH:MM para minutos
 * @param {string} hora - Hora no formato HH:MM
 * @return {number} - Tempo em minutos
 */
function horaParaMinutos(hora) {
    if (!hora) return 0;
    
    const partes = hora.split(':');
    return parseInt(partes[0], 10) * 60 + parseInt(partes[1], 10);
}

/**
 * Converte minutos para o formato HH:MM
 * @param {number} minutos - Tempo em minutos
 * @return {string} - Tempo no formato HH:MM
 */
function minutosParaHora(minutos) {
    const horas = Math.floor(minutos / 60);
    const mins = minutos % 60;
    
    return `${horas.toString().padStart(2, '0')}:${mins.toString().padStart(2, '0')}`;
}