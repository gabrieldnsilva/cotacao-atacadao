/**
 * Operator Logic for Cotação Online Atacadão
 */

$(document).ready(function () {
    let quotationItems = JSON.parse(sessionStorage.getItem('quotation')) || [];
    renderQuotation();

    // --- Search Logic ---
    let searchTimeout;
    $('#product-search').on('input', function () {
        clearTimeout(searchTimeout);
        const query = $(this).val().trim();

        if (query.length < 2) {
            $('#search-results').hide();
            return;
        }

        searchTimeout = setTimeout(() => {
            $.get(`../../api/catalog?search=${encodeURIComponent(query)}`, function (products) {
                renderSearchResults(products);
            });
        }, 300);
    });

    function renderSearchResults(products) {
        const $results = $('#search-results');
        $results.empty();

        if (products.length === 0) {
            $results.append('<div class="search-item text-muted">Nenhum produto encontrado.</div>');
        } else {
            products.forEach(p => {
                const $item = $(`
                    <div class="search-item">
                        <strong>${p.merc}-${p.digito}</strong> | ${p.descricao} | 
                        <span class="text-success">R$ ${parseFloat(p.preco_venda).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</span>
                    </div>
                `);
                $item.on('click', () => addItem(p));
                $results.append($item);
            });
        }
        $results.show();
    }

    // --- Quotation Management ---
    function addItem(product) {
        $('#search-results').hide();
        $('#product-search').val('');

        const existing = quotationItems.find(item => item.merc === product.merc && item.digito === product.digito);
        
        if (existing) {
            existing.quantity += 1;
        } else {
            quotationItems.push({
                merc: product.merc,
                digito: product.digito,
                descricao: product.descricao,
                embalagem: product.embalagem,
                price: parseFloat(product.preco_venda),
                quantity: 1
            });
        }

        saveAndRender();
    }

    window.updateQuantity = function (merc, digito, newQty) {
        const item = quotationItems.find(i => i.merc == merc && i.digito == digito);
        if (item) {
            item.quantity = parseInt(newQty) || 0;
            if (item.quantity < 0) item.quantity = 0;
            saveAndRender();
        }
    };

    window.removeItem = function (merc, digito) {
        quotationItems = quotationItems.filter(i => !(i.merc == merc && i.digito == digito));
        saveAndRender();
    };

    $('#clear-quotation').on('click', function() {
        if(confirm('Deseja realmente limpar toda a cotação?')) {
            quotationItems = [];
            saveAndRender();
        }
    });

    function saveAndRender() {
        sessionStorage.setItem('quotation', JSON.stringify(quotationItems));
        renderQuotation();
    }

    function renderQuotation() {
        const $body = $('#quotation-body');
        $body.empty();

        let totalGeral = 0;

        quotationItems.forEach(item => {
            const rowTotal = item.price * item.quantity;
            totalGeral += rowTotal;

            $body.append(`
                <tr>
                    <td>${item.merc}</td>
                    <td>${item.digito}</td>
                    <td>${item.descricao}</td>
                    <td><small>${item.embalagem}</small></td>
                    <td>
                        <input type="number" class="form-control form-control-sm" 
                               value="${item.quantity}" 
                               onchange="updateQuantity(${item.merc}, ${item.digito}, this.value)">
                    </td>
                    <td>R$ ${item.price.toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
                    <td><strong>R$ ${rowTotal.toLocaleString('pt-BR', {minimumFractionDigits: 2})}</strong></td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-danger" onclick="removeItem(${item.merc}, ${item.digito})">Remover</button>
                    </td>
                </tr>
            `);
        });

        $('#total-row').text(`R$ ${totalGeral.toLocaleString('pt-BR', {minimumFractionDigits: 2})}`);
    }

    // Hide search results when clicking outside
    $(document).on('click', function (e) {
        if (!$(e.target).closest('#product-search, #search-results').length) {
            $('#search-results').hide();
        }
    });

    // Generate PDF placeholder
    $('#generate-pdf').on('click', function() {
        alert('A funcionalidade de exportação de PDF será implementada na Story 1.6.');
    });
});
