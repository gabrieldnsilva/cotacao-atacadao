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

    // Generate PDF logic
    $('#generate-pdf').on('click', function() {
        if (quotationItems.length === 0) {
            alert('Adicione pelo menos um item para gerar o faturamento.');
            return;
        }

        const { jsPDF } = window.jspdf;
        const $pdfBody = $('#pdf-body');
        const $pdfContainer = $('#pdf-export-container');
        
        $pdfBody.empty();
        let totalGeral = 0;

        const now = new Date();
        $('#pdf-date').text(now.toLocaleDateString('pt-BR'));
        $('#pdf-time').text(now.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit', second: '2-digit' }));

        quotationItems.forEach(item => {
            const rowTotal = item.price * item.quantity;
            totalGeral += rowTotal;

            $pdfBody.append(`
                <tr>
                    <td>${item.merc}</td>
                    <td>${item.digito}</td>
                    <td>${item.descricao}</td>
                    <td>${item.embalagem}</td>
                    <td>${item.quantity}</td>
                    <td>${item.price.toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
                    <td>${rowTotal.toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
                    <td class="barcode">*${item.merc}${item.digito}*</td>
                </tr>
            `);
        });

        $('#pdf-total').text(`R$ ${totalGeral.toLocaleString('pt-BR', {minimumFractionDigits: 2})}`);

        // Small delay to ensure DOM is updated and font is ready
        setTimeout(() => {
            html2canvas(document.querySelector("#pdf-export-container"), {
                scale: 2, // Higher resolution
                useCORS: true,
                logging: false
            }).then(canvas => {
                const imgData = canvas.toDataURL('image/png');
                const pdf = new jsPDF('p', 'mm', 'a4');
                const imgProps = pdf.getImageProperties(imgData);
                const pdfWidth = pdf.internal.pageSize.getWidth();
                const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;
                
                pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
                pdf.save(`faturamento_${now.getTime()}.pdf`);
            });
        }, 500);
    });
});
