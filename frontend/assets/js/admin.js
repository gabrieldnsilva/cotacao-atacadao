/**
 * Admin Dashboard Logic for Cotação Online Atacadão
 */

$(document).ready(function () {
    checkAuthStatus();

    // --- Authentication ---
    function checkAuthStatus() {
        $.get('../../api/auth/status', function (res) {
            if (res.logged_in) {
                showDashboard();
            } else {
                showLogin();
            }
        });
    }

    function showLogin() {
        $('#admin-dashboard').hide();
        $('#logout-btn').hide();
        $('#login-section').fadeIn();
    }

    function showDashboard() {
        $('#login-section').hide();
        $('#admin-dashboard').fadeIn();
        $('#logout-btn').show();
        loadDashboardStats();
    }

    function loadDashboardStats() {
        $.get('../../api/catalog/stats', function (res) {
            $('#stat-total-items').text(res.total.toLocaleString('pt-BR'));
            
            if (res.last_update && res.last_update !== 'Nunca') {
                const date = new Date(res.last_update);
                $('#stat-last-update').text(date.toLocaleString('pt-BR'));
            } else {
                $('#stat-last-update').text('Nunca');
            }

            if (res.status === 'online') {
                $('#stat-db-status').html(`
                    Online
                    <ion-icon name="checkmark-circle" class="text-success"></ion-icon>
                `);
            }
        });
    }

    $('#login-form').on('submit', function (e) {
        e.preventDefault();
        const username = $('#username').val();
        const password = $('#password').val();

        $.ajax({
            url: '../../api/login',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ username, password }),
            success: function () {
                Swal.fire({
                    icon: 'success',
                    title: 'Bem-vindo!',
                    text: 'Login realizado com sucesso.',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    showDashboard();
                });
            },
            error: function (xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Falha no Login',
                    text: xhr.responseJSON.message || 'Credenciais inválidas.'
                });
            }
        });
    });

    $('#logout-btn').on('click', function () {
        $.post('../../api/logout', function () {
            showLogin();
        });
    });

    // --- CSV Upload ---
    $('#catalog_csv').on('change', function () {
        const fileName = this.files[0] ? this.files[0].name : 'Clique aqui para selecionar o arquivo .CSV';
        $('#file-name').text(fileName);
    });

    $('#upload-form').on('submit', function (e) {
        e.preventDefault();
        const fileInput = document.getElementById('catalog_csv');
        
        if (!fileInput.files[0]) {
            Swal.fire('Erro', 'Por favor, selecione um arquivo CSV.', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('catalog_csv', fileInput.files[0]);

        $('#loading').css('display', 'flex');

        $.ajax({
            url: '../../api/catalog/upload',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                $('#loading').hide();
                Swal.fire({
                    icon: 'success',
                    title: 'Catálogo Atualizado',
                    text: `${res.count} itens foram importados com sucesso.`,
                    confirmButtonColor: '#27ae60'
                });
                $('#upload-form')[0].reset();
                $('#file-name').text('Clique aqui para selecionar o arquivo .CSV');
                loadDashboardStats();
            },
            error: function (xhr) {
                $('#loading').hide();
                console.error("Server Response:", xhr.responseText);
                
                let msg = 'Erro ao processar arquivo.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    // If not JSON, show the first 100 chars of response
                    msg = 'Erro do Servidor: ' + xhr.responseText.substring(0, 100);
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Falha na Importação',
                    text: msg
                });
            }
        });
    });
});
