/**
 * Operator Logic for Cotação Online Atacadão - Advanced UX Edition
 */

$(document).ready(function () {
    let quotationItems = JSON.parse(sessionStorage.getItem('quotation')) || [];
    renderQuotation();

    // --- Search Logic with Debounce ---
    let searchTimeout;
    $('#product-search').on('input', function () {
        clearTimeout(searchTimeout);
        const query = $(this).val().trim();

        if (query.length < 2) {
            $('#search-results').hide();
            $(this).attr('aria-expanded', 'false');
            return;
        }

        $('#status-text').text('Buscando produtos...');

        searchTimeout = setTimeout(() => {
            $.get(`../../api/catalog?search=${encodeURIComponent(query)}`, function (products) {
                renderSearchResults(products);
                $('#product-search').attr('aria-expanded', products.length > 0 ? 'true' : 'false');
                $('#status-text').text(products.length > 0 ? 'Produtos encontrados.' : 'Nenhum produto encontrado.');
            });
        }, 400); // 400ms debounce
    });

    function renderSearchResults(products) {
        const $results = $('#search-results');
        $results.empty();

        if (products.length === 0) {
            $results.append('<div class="search-item text-muted p-3" role="option">Nenhum produto encontrado.</div>');
        } else {
            products.forEach(p => {
                const $item = $(`
                    <div class="search-item d-flex justify-content-between align-items-center" role="option">
                        <div>
                            <div class="fw-bold">${p.merc}-${p.digito}</div>
                            <div class="small text-uppercase">${p.descricao}</div>
                        </div>
                        <div class="text-end">
                            <div class="text-success fw-bold">R$ ${parseFloat(p.preco_venda).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</div>
                            <div class="text-muted extra-small" style="font-size: 0.7rem;">${p.embalagem}</div>
                        </div>
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
        $('#product-search').val('').attr('aria-expanded', 'false');
        
        const qtyToAdd = parseInt($('#quick-qty').val()) || 1;

        const existing = quotationItems.find(item => item.merc === product.merc && item.digito === product.digito);
        
        if (existing) {
            existing.quantity += qtyToAdd;
        } else {
            quotationItems.push({
                merc: product.merc,
                digito: product.digito,
                descricao: product.descricao,
                embalagem: product.embalagem,
                price: parseFloat(product.preco_venda),
                quantity: qtyToAdd
            });
        }

        $('#status-text').text(`Adicionado: ${product.descricao}`);
        saveAndRender();
        
        // Reset quick qty
        $('#quick-qty').val(1);
    }

    window.updateQuantity = function (merc, digito, newQty) {
        const item = quotationItems.find(i => i.merc == merc && i.digito == digito);
        if (item) {
            item.quantity = parseInt(newQty) || 0;
            if (item.quantity < 0) item.quantity = 0;
            saveAndRender();
        }
    };

    window.updatePrice = function (merc, digito, newPrice) {
        const item = quotationItems.find(i => i.merc == merc && i.digito == digito);
        if (item) {
            const price = parseFloat(newPrice.toString().replace(',', '.')) || 0;
            item.price = price;
            saveAndRender();
        }
    };

    window.removeItem = function (merc, digito) {
        const item = quotationItems.find(i => i.merc == merc && i.digito == digito);
        if (item) {
            quotationItems = quotationItems.filter(i => !(i.merc == merc && i.digito == digito));
            $('#status-text').text(`Removido: ${item.descricao}`);
            saveAndRender();
        }
    };

    $('#clear-quotation').on('click', function() {
        if (quotationItems.length > 0 && confirm('Deseja realmente limpar toda a cotação?')) {
            quotationItems = [];
            $('#status-text').text('Cotação limpa.');
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
        let totalUnidades = 0;

        quotationItems.forEach(item => {
            const rowTotal = item.price * item.quantity;
            totalGeral += rowTotal;
            totalUnidades += item.quantity;

            $body.append(`
                <tr class="${item.quantity > 0 ? 'row-highlight' : ''}">
                    <td class="fw-bold">${item.merc}</td>
                    <td>${item.digito}</td>
                    <td class="text-uppercase small fw-medium">${item.descricao}</td>
                    <td class="text-center small text-muted">${item.embalagem}</td>
                    <td>
                        <input type="number" class="form-control form-control-sm text-center fw-bold" 
                               value="${item.quantity}" 
                               aria-label="Quantidade para ${item.descricao}"
                               onchange="updateQuantity(${item.merc}, ${item.digito}, this.value)">
                    </td>
                    <td>
                        <div class="d-flex align-items-center justify-content-end">
                            <span class="me-1 text-muted extra-small" aria-hidden="true">R$</span>
                            <input type="text" class="price-input" 
                                   value="${item.price.toLocaleString('pt-BR', {minimumFractionDigits: 2})}" 
                                   aria-label="Preço unitário para ${item.descricao}"
                                   onchange="updatePrice(${item.merc}, ${item.digito}, this.value)">
                        </div>
                    </td>
                    <td class="text-right fw-black">R$ ${rowTotal.toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
                    <td class="text-center font-barcode">*${item.merc}${item.digito}*</td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-link text-danger p-0" 
                                aria-label="Remover ${item.descricao}"
                                onclick="removeItem(${item.merc}, ${item.digito})">
                            <ion-icon name="trash-outline" style="font-size: 1.2rem;"></ion-icon>
                        </button>
                    </td>
                </tr>
            `);
        });

        // Update stats
        $('#total-row').text(`R$ ${totalGeral.toLocaleString('pt-BR', {minimumFractionDigits: 2})}`);
        $('#items-count').text(quotationItems.length);
        $('#units-count').text(totalUnidades);

        if (quotationItems.length === 0) {
            $body.append('<tr><td colspan="9" class="text-center py-5 text-muted italic">Nenhum item adicionado à cotação.</td></tr>');
            $('#status-text').text('Aguardando inserção de itens.');
        }
    }

    // Hide search results when clicking outside
    $(document).on('click', function (e) {
        if (!$(e.target).closest('#product-search, #search-results').length) {
            $('#search-results').hide();
            $('#product-search').attr('aria-expanded', 'false');
        }
    });

    // Generate PDF logic
    $('#generate-pdf').on('click', function() {
        if (quotationItems.length === 0) {
            Swal.fire('Cotação Vazia', 'Adicione pelo menos um item para gerar o faturamento.', 'warning');
            return;
        }

        $('#status-text').text('Gerando PDF...');
        const { jsPDF } = window.jspdf;
        const $pdfBody = $('#pdf-body');
        
        $pdfBody.empty();
        let totalGeral = 0;

        const now = new Date();
        $('#pdf-date').text(now.toLocaleDateString('pt-BR'));
        $('#pdf-time').text(now.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' }));

        quotationItems.forEach(item => {
            const rowTotal = item.price * item.quantity;
            totalGeral += rowTotal;

            $pdfBody.append(`
                <tr style="font-size: 9px;">
                    <td>${item.merc}</td>
                    <td>${item.digito}</td>
                    <td class="text-uppercase">${item.descricao}</td>
                    <td>${item.embalagem}</td>
                    <td class="text-center font-weight-bold">${item.quantity}</td>
                    <td class="text-center">R$ ${item.price.toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
                    <td class="text-right font-weight-bold">R$ ${rowTotal.toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
                    <td class="text-center font-barcode" style="font-size: 20px;">*${item.merc}${item.digito}*</td>
                </tr>
            `);
        });

        $('#pdf-total').text(`R$ ${totalGeral.toLocaleString('pt-BR', {minimumFractionDigits: 2})}`);

        setTimeout(() => {
            html2canvas(document.querySelector("#pdf-export-container"), {
                scale: 3, // Very high quality for barcodes
                useCORS: true,
                logging: false
            }).then(canvas => {
                const imgData = canvas.toDataURL('image/png');
                const pdf = new jsPDF('p', 'mm', 'a4');
                const imgProps = pdf.getImageProperties(imgData);
                const pdfWidth = pdf.internal.pageSize.getWidth();
                const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;
                
                pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
                pdf.save(`faturamento_atacadao_${now.getTime()}.pdf`);
                $('#status-text').text('PDF gerado com sucesso!');
            });
        }, 600);
    });
});
