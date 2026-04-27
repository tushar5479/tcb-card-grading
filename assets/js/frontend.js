jQuery(function ($) {
    let currentPage = 1;
    let currentKeyword = '';
    let selectedTier = 'pristine-10';
    let step4Countries = {};

    function escapeHtml(text) {
        if (typeof text !== 'string') {
            text = text == null ? '' : String(text);
        }

        return text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function moneyFormat(num) {
        return '$' + parseFloat(num || 0).toFixed(2);
    }

    function tierLabel(tier) {
        const labels = {
            'standard': 'Standard',
            'pristine-10': 'Pristine 10',
            'elite-rare': 'Elite / Rare',
            'vintage': 'Vintage',
            'ultra-1of1': 'Ultra / 1 of 1'
        };
        return labels[tier] || '';
    }

    function updateTierPricingUI(tierData) {
        if (!tierData) return;

        if ($('#sgc-selected-tier-label').length) {
            $('#sgc-selected-tier-label').text(tierData.label || tierLabel(tierData.key));
        }
        if ($('#sgc-selected-tier-name').length) {
            $('#sgc-selected-tier-name').text(tierData.label || '');
        }
        if ($('#sgc-selected-tier-days').length) {
            $('#sgc-selected-tier-days').text(tierData.days || '');
        }
        if ($('#sgc-selected-tier-price').length) {
            $('#sgc-selected-tier-price').text(moneyFormat(tierData.price || 0));
        }

        if ($('#sgc-step3-tier-name').length) {
            $('#sgc-step3-tier-name').text(tierData.label || '');
        }
        if ($('#sgc-step3-tier-days').length) {
            $('#sgc-step3-tier-days').text(tierData.days || '');
        }
        if ($('#sgc-step3-tier-price').length) {
            $('#sgc-step3-tier-price').text(moneyFormat(tierData.price || 0));
        }
    }

    function markSelectedTier(tier) {
        selectedTier = tier;
        $('.sgc-tier-card').removeClass('is-selected');
        $('.sgc-tier-card[data-tier="' + tier + '"]').addClass('is-selected');
    }

    function loadSavedTier() {
        $.post(sgcStep2Data.ajaxUrl, {
            action: 'sgc_get_service_level',
            nonce: sgcStep2Data.nonce
        }).done(function (response) {
            if (response && response.success) {
                const tierData = response.data;
                selectedTier = tierData.key || 'pristine-10';
                markSelectedTier(selectedTier);
                updateTierPricingUI(tierData);
            }
        });
    }

    function saveTier(tier, callback = null) {
        $.post(sgcStep2Data.ajaxUrl, {
            action: 'sgc_save_service_level',
            nonce: sgcStep2Data.nonce,
            tier: tier
        }).done(function (response) {
            if (response && response.success) {
                updateTierPricingUI(response.data);
            }
            if (typeof callback === 'function') {
                callback(response);
            }
        });
    }

    function renderSearchResults(items, append = false) {
        let html = '';

        if (items.length) {
            items.forEach(function (item) {
                html += `
                    <div class="sgc-search-result-card" data-card-id="${item.id}">
                        <div class="sgc-search-result-title">${escapeHtml(item.title)}</div>
                        <div class="sgc-search-result-type">${escapeHtml(item.type || '')}</div>
                    </div>
                `;
            });
        }

        if (append) {
            $('#sgc-search-results').append(html);
        } else {
            $('#sgc-search-results').html(html);
        }
    }

    function renderSelectedCards(data) {
        const cards = data.selected_cards || [];
        let html = '';

        cards.forEach(function (card) {
            let serviceOptions = '';

            if (Array.isArray(card.services)) {
                card.services.forEach(function (service) {
                    const selected = service === card.service ? 'selected' : '';
                    serviceOptions += `<option value="${escapeHtml(service)}" ${selected}>${escapeHtml(service)}</option>`;
                });
            }

            const customMeta = `
                ${(card.player_name || card.year || card.card_number) ? `
                    <div class="sgc-selected-card-custom-meta">
                        ${card.player_name ? `<div><strong>Player:</strong> ${escapeHtml(card.player_name)}</div>` : ''}
                        ${card.year ? `<div><strong>Year:</strong> ${escapeHtml(card.year)}</div>` : ''}
                        ${card.card_number ? `<div><strong>Card #:</strong> ${escapeHtml(card.card_number)}</div>` : ''}
                    </div>
                ` : ''}
            `;

            const imageHtml = (card.image_front_url || card.image_back_url) ? `
                <div class="sgc-selected-card-images-wrap">
                    ${card.image_front_url ? `
                        <div class="sgc-selected-card-image-wrap">
                            <div class="sgc-selected-card-image-label">Front</div>
                            <img src="${escapeHtml(card.image_front_url)}" alt="Front Image" class="sgc-selected-card-image">
                        </div>
                    ` : ''}
                    ${card.image_back_url ? `
                        <div class="sgc-selected-card-image-wrap">
                            <div class="sgc-selected-card-image-label">Back</div>
                            <img src="${escapeHtml(card.image_back_url)}" alt="Back Image" class="sgc-selected-card-image">
                        </div>
                    ` : ''}
                </div>
            ` : '';

            html += `
                <div class="sgc-selected-card-box" data-row-id="${card.row_id}">
                    <button type="button" class="sgc-remove-selected-card" data-row-id="${card.row_id}" title="Remove">🗑</button>

                    <div class="sgc-selected-card-title">${escapeHtml(card.title)}</div>
                    <div class="sgc-selected-card-type">${escapeHtml(card.type || '')}</div>
                    ${customMeta}
                    ${imageHtml}

                    <input
                        type="number"
                        min="1"
                        step="1"
                        class="sgc-declared-value-input"
                        value="${card.declared_value}"
                        placeholder="Enter Declared Value"
                    >

                    <div class="sgc-dv-error" style="display:none;">Declared Value Required. Minimum $1.</div>

                    <div class="sgc-checkbox-row">
                        <label><input type="checkbox" class="sgc-encapsulate-checkbox" ${parseInt(card.encapsulate, 10) ? 'checked' : ''}> Encapsulate if altered</label>
                        <label><input type="checkbox" class="sgc-oversized-checkbox" ${parseInt(card.oversized, 10) ? 'checked' : ''}> Oversized Item</label>
                        <label><input type="checkbox" class="sgc-authentic-checkbox" ${parseInt(card.authentic, 10) ? 'checked' : ''}> Authentic</label>
                    </div>

                    <div class="sgc-service-title">SELECT CARD SERVICES</div>
                    <select class="sgc-card-service-dropdown">
                        ${serviceOptions}
                    </select>
                </div>
            `;
        });

        $('#sgc-selected-cards').html(html);
        $('#sgc-total-cards').text(data.summary.total_cards || 0);
        $('#sgc-total-dv').text(moneyFormat(data.summary.total_dv || 0));
        $('#sgc-grading-fee').text(parseFloat(data.summary.grading_fee || 0).toFixed(2));

        if (data.tier) {
            updateTierPricingUI(data.tier);
        }
    }

    function renderStep3Review(data) {
        if (!$('#sgc-step3-review-list').length) return;

        const cards = data.selected_cards || [];
        let html = '';

        cards.forEach(function (card, index) {
            const customMeta = `
                ${(card.player_name || card.year || card.card_number) ? `
                    <div class="sgc-step3-card-meta">
                        ${card.player_name ? `<div><strong>Player Name:</strong> ${escapeHtml(card.player_name)}</div>` : ''}
                        ${card.year ? `<div><strong>Year:</strong> ${escapeHtml(card.year)}</div>` : ''}
                        ${card.card_number ? `<div><strong>Card Number:</strong> ${escapeHtml(card.card_number)}</div>` : ''}
                    </div>
                ` : ''}
            `;

            const imageHtml = (card.image_front_url || card.image_back_url) ? `
                <div class="sgc-step3-card-images">
                    ${card.image_front_url ? `
                        <div class="sgc-step3-card-image-box">
                            <div class="sgc-step3-card-image-label">Front</div>
                            <img src="${escapeHtml(card.image_front_url)}" alt="Front Image" class="sgc-step3-card-image">
                        </div>
                    ` : ''}
                    ${card.image_back_url ? `
                        <div class="sgc-step3-card-image-box">
                            <div class="sgc-step3-card-image-label">Back</div>
                            <img src="${escapeHtml(card.image_back_url)}" alt="Back Image" class="sgc-step3-card-image">
                        </div>
                    ` : ''}
                </div>
            ` : '';

            html += `
                <div class="sgc-step3-card">
                    <div class="sgc-step3-card-head">
                        <div class="sgc-step3-card-number">Card ${index + 1}</div>
                        <div class="sgc-step3-card-title">${escapeHtml(card.title || 'Custom Card')}</div>
                    </div>

                    <div class="sgc-step3-card-body">
                        <div class="sgc-step3-card-info">
                            <div><strong>Type:</strong> ${escapeHtml(card.type || '')}</div>
                            <div><strong>Declared Value:</strong> ${moneyFormat(card.declared_value || 0)}</div>
                            <div><strong>Service:</strong> ${escapeHtml(card.service || '')}</div>
                            ${customMeta}
                        </div>
                        ${imageHtml}
                    </div>
                </div>
            `;
        });

        $('#sgc-step3-review-list').html(html);

        $('#sgc-step3-total-cards').text(data.summary.total_cards || 0);
        $('#sgc-step3-total-dv').text(moneyFormat(data.summary.total_dv || 0));
        $('#sgc-step3-grading-fee').text(moneyFormat(data.summary.grading_fee || 0));

        if (data.tier) {
            updateTierPricingUI(data.tier);
        }
    }

    function showNoResultAddLink(show) {
        if (show) {
            $('#sgc-add-card-inline').show();
        } else {
            $('#sgc-add-card-inline').hide();
        }
    }

    function syncAddLinkVisibility() {
        const keyword = ($('#sgc-card-search').val() || '').trim();
        showNoResultAddLink(keyword !== '');
    }

    function clearLeftSideResults(resetInput = false) {
        $('#sgc-search-results').html('');
        $('#sgc-load-more').hide();

        if (resetInput) {
            $('#sgc-card-search').val('');
            currentKeyword = '';
        }

        syncAddLinkVisibility();
    }

    function clearCustomCardModal() {
        $('#sgc_custom_player_name').val('');
        $('#sgc_custom_year').val('');
        $('#sgc_custom_card_number').val('');
        $('#sgc_custom_declared_value').val('1');

        $('#sgc_custom_image_front').val('');
        $('#sgc_custom_image_back').val('');

        $('#sgc_custom_image_preview_front').attr('src', '').hide();
        $('#sgc_custom_image_preview_back').attr('src', '').hide();

        $('#sgc_preview_placeholder_front').show();
        $('#sgc_preview_placeholder_back').show();

        $('#sgc_custom_declared_value_error').hide();
    }

    function openCustomCardModal() {
        clearCustomCardModal();
        $('#sgc-custom-card-modal').fadeIn(150);
    }

    function closeCustomCardModal() {
        $('#sgc-custom-card-modal').fadeOut(150);
    }

    function searchCards(reset = true) {
        $.post(sgcStep2Data.ajaxUrl, {
            action: 'sgc_search_cards',
            nonce: sgcStep2Data.nonce,
            keyword: currentKeyword,
            page: currentPage
        }).done(function (response) {
            if (!response || !response.success) {
                syncAddLinkVisibility();
                return;
            }

            const items = response.data.items || [];

            if (currentKeyword === '') {
                clearLeftSideResults(false);
                return;
            }

            if (reset) {
                $('#sgc-search-results').html('');
            }

            renderSearchResults(items, !reset);
            syncAddLinkVisibility();

            if (response.data.has_more) {
                $('#sgc-load-more').show();
            } else {
                $('#sgc-load-more').hide();
            }
        }).fail(function () {
            syncAddLinkVisibility();
        });
    }

    function loadSelectedCards() {
        $.post(sgcStep2Data.ajaxUrl, {
            action: 'sgc_get_selected_cards',
            nonce: sgcStep2Data.nonce
        }).done(function (response) {
            if (response && response.success) {
                if ($('#sgc-selected-cards').length) {
                    renderSelectedCards(response.data);
                }

                if ($('#sgc-step3-review-list').length) {
                    renderStep3Review(response.data);
                }
            }
        });
    }

    function updateSelectedCard($cardBox) {
        const rowId = $cardBox.data('row-id');
        const declaredValue = parseFloat($cardBox.find('.sgc-declared-value-input').val()) || 0;
        const encapsulate = $cardBox.find('.sgc-encapsulate-checkbox').is(':checked') ? 1 : 0;
        const oversized = $cardBox.find('.sgc-oversized-checkbox').is(':checked') ? 1 : 0;
        const authentic = $cardBox.find('.sgc-authentic-checkbox').is(':checked') ? 1 : 0;
        const service = $cardBox.find('.sgc-card-service-dropdown').val();

        if (declaredValue < 1) {
            $cardBox.find('.sgc-dv-error').show();
            return;
        }

        $cardBox.find('.sgc-dv-error').hide();

        $.post(sgcStep2Data.ajaxUrl, {
            action: 'sgc_update_selected_card',
            nonce: sgcStep2Data.nonce,
            row_id: rowId,
            declared_value: declaredValue,
            encapsulate: encapsulate,
            oversized: oversized,
            authentic: authentic,
            service: service
        }).done(function (response) {
            if (response && response.success) {
                renderSelectedCards(response.data);
            }
        });
    }

    function validateBeforeNext() {
        const $cards = $('.sgc-selected-card-box');

        if (!$cards.length) {
            alert('Please add at least one card.');
            return false;
        }

        let valid = true;

        $cards.each(function () {
            const $box = $(this);
            const dv = parseFloat($box.find('.sgc-declared-value-input').val()) || 0;

            if (dv < 1) {
                $box.find('.sgc-dv-error').show();
                valid = false;
            } else {
                $box.find('.sgc-dv-error').hide();
            }
        });

        if (!valid) {
            alert(sgcStep2Data.selectPlanMessage || 'Please complete required fields before continuing.');
            return false;
        }

        return true;
    }

    function renderStep4Addresses(data) {
        if (!$('#sgc-step4-address-list').length) return;

        const addresses = data.addresses || [];
        const selectedId = data.selected_address_id || '';
        let html = '';

        if (!addresses.length) {
            html = '<div class="sgc-step4-empty-address">No shipping address added yet.</div>';
        } else {
            addresses.forEach(function (address) {
                const isSelected = address.id === selectedId ? 'is-selected' : '';
                const line1 = escapeHtml(address.street || '');
                const line2Parts = [
                    address.apartment || '',
                    address.city || '',
                    address.state_label || address.state || '',
                    address.zip || '',
                    address.country_label || address.country || ''
                ].filter(Boolean);

                html += `
                    <div class="sgc-step4-address-card ${isSelected}" data-address-id="${address.id}">
                        <div class="sgc-step4-address-main" data-select-address="${address.id}">
                            <div class="sgc-step4-address-title">${line1}</div>
                            <div class="sgc-step4-address-line">${escapeHtml(line2Parts.join(', '))}</div>
                            <div class="sgc-step4-address-phone">${escapeHtml(address.phone || '')}</div>
                        </div>

                        <div class="sgc-step4-address-actions">
                            <button type="button" class="sgc-step4-address-edit-btn" data-address-id="${address.id}">Edit</button>
                            <button type="button" class="sgc-step4-address-delete-btn" data-address-id="${address.id}">Delete</button>
                        </div>
                    </div>
                `;
            });
        }

        $('#sgc-step4-address-list').html(html);

        if (data.summary) {
            $('#sgc-step4-tier-label').text(data.summary.tier_label || '-');
            $('#sgc-step4-total-cards').text(data.summary.total_cards || 0);
            $('#sgc-step4-total-dv').text(moneyFormat(data.summary.total_dv || 0));
            $('#sgc-step4-grading-fee').text(moneyFormat(data.summary.grading_fee || 0));
            $('#sgc-step4-shipping-fee').text(moneyFormat(data.summary.shipping_fee || 0));
            $('#sgc-step4-grand-total').text(moneyFormat(data.summary.grand_total || 0));
        }
    }

    function renderCountryOptions(countries, selectedValue) {
        let options = '<option value="">Select Country</option>';
        Object.keys(countries || {}).forEach(function (code) {
            const selected = selectedValue === code ? 'selected' : '';
            options += `<option value="${escapeHtml(code)}" ${selected}>${escapeHtml(countries[code])}</option>`;
        });
        $('#sgc_address_country').html(options);
    }

    function renderStateOptions(states, selectedValue) {
        let options = '<option value="">Select State / Province</option>';
        Object.keys(states || {}).forEach(function (code) {
            const selected = selectedValue === code ? 'selected' : '';
            options += `<option value="${escapeHtml(code)}" ${selected}>${escapeHtml(states[code])}</option>`;
        });
        $('#sgc_address_state').html(options);
    }

    function loadStep4Addresses() {
        if (!$('#sgc-step4-address-list').length) return;

        $.post(sgcStep2Data.ajaxUrl, {
            action: 'sgc_get_shipping_addresses',
            nonce: sgcStep2Data.nonce
        }).done(function (response) {
            if (response && response.success) {
                step4Countries = response.data.countries || {};
                renderStep4Addresses(response.data);
                renderCountryOptions(step4Countries, '');
                renderStateOptions({}, '');
            }
        });
    }

    function clearAddressModal() {
        $('#sgc_address_id').val('');
        $('#sgc_address_country').val('');
        $('#sgc_address_street').val('');
        $('#sgc_address_apartment').val('');
        $('#sgc_address_city').val('');
        renderStateOptions({}, '');
        $('#sgc_address_zip').val('');
        $('#sgc_address_phone').val('');
        $('#sgc_address_default').prop('checked', false);
        $('#sgc-address-error').hide().text('');
    }

    function openAddressModal() {
        clearAddressModal();
        $('#sgc-address-modal').fadeIn(150);
    }

    function closeAddressModal() {
        $('#sgc-address-modal').fadeOut(150);
    }

    function loadAddressForEdit(addressId) {
        $.post(sgcStep2Data.ajaxUrl, {
            action: 'sgc_get_shipping_address_detail',
            nonce: sgcStep2Data.nonce,
            address_id: addressId
        }).done(function (response) {
            if (response && response.success) {
                const address = response.data.address || {};
                const countries = response.data.countries || step4Countries;
                const states = response.data.states || {};

                $('#sgc_address_id').val(address.id || '');
                renderCountryOptions(countries, address.country || '');
                renderStateOptions(states, address.state || '');

                $('#sgc_address_street').val(address.street || '');
                $('#sgc_address_apartment').val(address.apartment || '');
                $('#sgc_address_city').val(address.city || '');
                $('#sgc_address_zip').val(address.zip || '');
                $('#sgc_address_phone').val(address.phone || '');
                $('#sgc_address_default').prop('checked', false);
                $('#sgc-address-error').hide().text('');
                $('#sgc-address-modal').fadeIn(150);
            } else if (response && response.data && response.data.message) {
                alert(response.data.message);
            }
        });
    }

    function renderStep5ShippingMethods(methods, selectedMethod) {
        if (!$('#sgc-step5-shipping-method-list').length) return;

        let html = '';

        (methods || []).forEach(function (method) {
            const selectedClass = selectedMethod && selectedMethod.key === method.key ? 'is-selected' : '';
            const logoHtml = method.logo
                ? `<img src="${escapeHtml(method.logo)}" alt="${escapeHtml(method.label)}" class="sgc-step5-method-logo">`
                : `<div class="sgc-step5-method-logo-placeholder">${escapeHtml(method.label.split(' ')[0])}</div>`;

            html += `
                <div class="sgc-step5-method-card ${selectedClass}" data-shipping-method="${method.key}">
                    <div class="sgc-step5-method-left">
                        ${logoHtml}
                    </div>
                    <div class="sgc-step5-method-right">
                        <div class="sgc-step5-method-title">${escapeHtml(method.label)}</div>
                        <div class="sgc-step5-method-price">${moneyFormat(method.price)}</div>
                    </div>
                </div>
            `;
        });

        $('#sgc-step5-shipping-method-list').html(html);

        if (selectedMethod && selectedMethod.label) {
            $('#sgc-step5-bottom-shipping-text').text(selectedMethod.label);
            $('#sgc-step5-next-btn').text('FINISH AND PAY');
        } else {
            $('#sgc-step5-bottom-shipping-text').text('No shipping selected');
            $('#sgc-step5-next-btn').text('NEXT');
        }
    }

    function renderStep5Review(data) {
        if (!$('#sgc-step5-address-preview').length) return;

        const address = data.selected_address || null;

        let addressHtml = '<div class="sgc-step5-empty-address">No selected address found.</div>';

        if (address) {
            const line2Parts = [
                address.apartment || '',
                address.city || '',
                address.state_label || address.state || '',
                address.zip || '',
                address.country_label || address.country || ''
            ].filter(Boolean);

            addressHtml = `
                <div class="sgc-step5-address-card">
                    <div class="sgc-step5-address-title">${escapeHtml(address.street || '')}</div>
                    <div class="sgc-step5-address-line">${escapeHtml(line2Parts.join(', '))}</div>
                    <div class="sgc-step5-address-phone">${escapeHtml(address.phone || '')}</div>
                </div>
            `;
        }

        $('#sgc-step5-address-preview').html(addressHtml);

        if (data.tier) {
            $('#sgc-step5-tier-label').text(data.tier.label || '-');
            $('#sgc-step5-tier-days').text(data.tier.days || '-');
            $('#sgc-step5-tier-price').text(moneyFormat(data.tier.price || 0));
        }

        if (data.summary) {
            $('#sgc-step5-total-cards').text(data.summary.total_cards || 0);
            $('#sgc-step5-total-dv').text(moneyFormat(data.summary.total_dv || 0));
            $('#sgc-step5-grading-fee').text(moneyFormat(data.summary.grading_fee || 0));
            $('#sgc-step5-shipping-fee').text(moneyFormat(data.summary.shipping_fee || 0));
            $('#sgc-step5-grand-total').text(moneyFormat(data.summary.grand_total || 0));
        }

        renderStep5ShippingMethods(data.shipping_methods || [], data.selected_shipping_method || null);
    }

    function loadStep5Review() {
        if (!$('#sgc-step5-address-preview').length) return;

        $.post(sgcStep2Data.ajaxUrl, {
            action: 'sgc_get_step5_review',
            nonce: sgcStep2Data.nonce
        }).done(function (response) {
            if (response && response.success) {
                renderStep5Review(response.data);
            }
        });
    }

    function saveShippingMethod(methodKey) {
        $.post(sgcStep2Data.ajaxUrl, {
            action: 'sgc_save_shipping_method',
            nonce: sgcStep2Data.nonce,
            shipping_method: methodKey
        }).done(function (response) {
            if (response && response.success) {
                renderStep5ShippingMethods(response.data.shipping_methods || [], response.data.selected_shipping_method || null);

                if (response.data.summary) {
                    $('#sgc-step5-shipping-fee').text(moneyFormat(response.data.summary.shipping_fee || 0));
                    $('#sgc-step5-grand-total').text(moneyFormat(response.data.summary.grand_total || 0));
                }
            } else if (response && response.data && response.data.message) {
                alert(response.data.message);
            }
        });
    }

    async function initStep7Payment() {
        if (!$('#sgc-express-checkout-element').length) return;
        if (typeof Stripe === 'undefined' || !sgcStep2Data.stripePublishableKey) return;

        const stripe = Stripe(sgcStep2Data.stripePublishableKey);

        const intentRes = await $.post(sgcStep2Data.ajaxUrl, {
            action: 'sgc_create_payment_intent',
            nonce: sgcStep2Data.nonce
        });

        if (!intentRes || !intentRes.success) {
            alert(intentRes && intentRes.data && intentRes.data.message ? intentRes.data.message : 'Unable to initialize payment.');
            return;
        }

        const formattedTotal = '$' + parseFloat(intentRes.data.amount || 0).toFixed(2);
        $('#sgc-step7-total').text(formattedTotal);
        $('#sgc-step7-total-mobile').text(formattedTotal);

        const elements = stripe.elements({
            clientSecret: intentRes.data.client_secret,
            appearance: {
                theme: 'stripe'
            }
        });

        let expressCheckout = null;
        try {
            expressCheckout = elements.create('expressCheckout');
            expressCheckout.mount('#sgc-express-checkout-element');
        } catch (e) {
            $('#sgc-express-checkout-element').hide();
        }

       const cardElement = elements.create('card', {
            hidePostalCode: true
        });
        cardElement.mount('#sgc-card-element');

        cardElement.on('change', function (event) {
            if (event.complete) {
                $('#sgc-pay-now-btn').show();
            } else {
                $('#sgc-pay-now-btn').hide();
            }

            $('#sgc-card-errors').text(event.error ? event.error.message : '');
        });

        // Updated Submit Handler to prevent double submission and handle unexpected state
        $('#sgc-card-payment-form').off('submit').on('submit', async function (e) {
            e.preventDefault();

            const $submitBtn = $('#sgc-pay-now-btn');
            
            // Prevent multiple clicks
            if ($submitBtn.prop('disabled')) return;

            $submitBtn.prop('disabled', true).text('PROCESSING...');
            $('#sgc-card-errors').text(''); // Clear previous errors

            const result = await stripe.confirmCardPayment(intentRes.data.client_secret, {
                payment_method: {
                    card: cardElement
                }
            });

            if (result.error) {
                // If payment already succeeded but button was clicked again
                if (result.error.code === 'payment_intent_unexpected_state') {
                    // Force the success AJAX call using the existing intent ID
                    processSuccessfulPayment(intentRes.data.payment_intent_id, 'card');
                    return;
                }

                // Handle regular errors (e.g., card declined)
                $('#sgc-card-errors').text(result.error.message || 'Payment failed.');
                $submitBtn.prop('disabled', false).text('PAY NOW');
                return;
            }

            if (result.paymentIntent && result.paymentIntent.status === 'succeeded') {
                processSuccessfulPayment(result.paymentIntent.id, 'card');
            }
        });

        // Helper function to process success AJAX and redirect
        async function processSuccessfulPayment(paymentIntentId, paymentMethodType) {
            const saveRes = await $.post(sgcStep2Data.ajaxUrl, {
                action: 'sgc_payment_success',
                nonce: sgcStep2Data.nonce,
                payment_intent_id: paymentIntentId,
                payment_method_type: paymentMethodType
            });

            if (saveRes && saveRes.success && saveRes.data.redirect_url) {
                window.location.href = saveRes.data.redirect_url;
            } else {
                 $('#sgc-card-errors').text(saveRes?.data?.message || 'Error saving order. Please contact support.');
                 $('#sgc-pay-now-btn').prop('disabled', false).text('TRY AGAIN');
            }
        }

        if (expressCheckout) {
            expressCheckout.on('confirm', async function () {
                const result = await stripe.confirmPayment({
                    elements,
                    clientSecret: intentRes.data.client_secret,
                    confirmParams: {},
                    redirect: 'if_required'
                });

                if (result.error) {
                    alert(result.error.message || 'Google Pay payment failed.');
                    return;
                }

                if (result.paymentIntent && result.paymentIntent.status === 'succeeded') {
                     processSuccessfulPayment(result.paymentIntent.id, 'google_pay');
                }
            });
        }
    }

    $(document).on('click', '.sgc-tier-card', function () {
        const tier = $(this).data('tier');
        markSelectedTier(tier);
        saveTier(tier);
    });

    $('#sgc-step1-next-btn').on('click', function () {
        if (!selectedTier) return;

        saveTier(selectedTier, function () {
            if (sgcStep2Data.step1NextUrl) {
                window.location.href = sgcStep2Data.step1NextUrl;
            }
        });
    });

    $('#sgc-card-search').on('keyup input', function () {
        currentKeyword = $(this).val().trim();
        currentPage = 1;

        if (currentKeyword === '') {
            clearLeftSideResults(false);
            return;
        }

        syncAddLinkVisibility();
        searchCards(true);
    });

    $('#sgc-load-more').on('click', function () {
        currentPage++;

        if (currentKeyword === '') {
            clearLeftSideResults(false);
            return;
        }

        searchCards(false);
    });

    $(document).on('click', '.sgc-search-result-card', function () {
        const cardId = $(this).data('card-id');

        $.post(sgcStep2Data.ajaxUrl, {
            action: 'sgc_add_selected_card',
            nonce: sgcStep2Data.nonce,
            card_id: cardId
        }).done(function (response) {
            if (response && response.success) {
                renderSelectedCards(response.data);
            }
        });
    });

    $(document).on('click', '#sgc-open-custom-card-modal', function (e) {
        e.preventDefault();
        e.stopPropagation();
        openCustomCardModal();
    });

    $('#sgc-close-custom-card-modal').on('click', function (e) {
        e.preventDefault();
        closeCustomCardModal();
    });

    $('#sgc-custom-card-modal').on('click', function (e) {
        if (e.target === this) {
            closeCustomCardModal();
        }
    });

    $('#sgc_custom_image_front').on('change', function () {
        const file = this.files && this.files[0];

        if (!file) {
            $('#sgc_custom_image_preview_front').attr('src', '').hide();
            $('#sgc_preview_placeholder_front').show();
            return;
        }

        const reader = new FileReader();
        reader.onload = function (e) {
            $('#sgc_custom_image_preview_front').attr('src', e.target.result).show();
            $('#sgc_preview_placeholder_front').hide();
        };
        reader.readAsDataURL(file);
    });

    $('#sgc_custom_image_back').on('change', function () {
        const file = this.files && this.files[0];

        if (!file) {
            $('#sgc_custom_image_preview_back').attr('src', '').hide();
            $('#sgc_preview_placeholder_back').show();
            return;
        }

        const reader = new FileReader();
        reader.onload = function (e) {
            $('#sgc_custom_image_preview_back').attr('src', e.target.result).show();
            $('#sgc_preview_placeholder_back').hide();
        };
        reader.readAsDataURL(file);
    });

    $('#sgc-submit-custom-card').on('click', function () {
        const playerName = $('#sgc_custom_player_name').val().trim();
        const year = $('#sgc_custom_year').val().trim();
        const cardNumber = $('#sgc_custom_card_number').val().trim();
        const declaredValue = parseFloat($('#sgc_custom_declared_value').val()) || 0;

        const fileInputFront = $('#sgc_custom_image_front')[0];
        const fileInputBack = $('#sgc_custom_image_back')[0];

        if (declaredValue < 1) {
            $('#sgc_custom_declared_value_error').show();
            return;
        }

        $('#sgc_custom_declared_value_error').hide();

        const formData = new FormData();
        formData.append('action', 'sgc_add_custom_card');
        formData.append('nonce', sgcStep2Data.nonce);
        formData.append('player_name', playerName);
        formData.append('year', year);
        formData.append('card_number', cardNumber);
        formData.append('declared_value', declaredValue);

        if (fileInputFront.files.length) {
            formData.append('custom_image_front', fileInputFront.files[0]);
        }

        if (fileInputBack.files.length) {
            formData.append('custom_image_back', fileInputBack.files[0]);
        }

        $.ajax({
            url: sgcStep2Data.ajaxUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response && response.success) {
                    renderSelectedCards(response.data);
                    closeCustomCardModal();
                    clearLeftSideResults(true);
                } else if (response && response.data && response.data.message) {
                    alert(response.data.message);
                }
            }
        });
    });

    $(document).on('click', '.sgc-remove-selected-card', function () {
        const rowId = $(this).data('row-id');

        $.post(sgcStep2Data.ajaxUrl, {
            action: 'sgc_remove_selected_card',
            nonce: sgcStep2Data.nonce,
            row_id: rowId
        }).done(function (response) {
            if (response && response.success) {
                renderSelectedCards(response.data);
            }
        });
    });

    $(document).on(
        'keyup change',
        '.sgc-declared-value-input, .sgc-encapsulate-checkbox, .sgc-oversized-checkbox, .sgc-authentic-checkbox, .sgc-card-service-dropdown',
        function () {
            const $box = $(this).closest('.sgc-selected-card-box');
            updateSelectedCard($box);
        }
    );

    $('#sgc-toggle-pricing-table').on('click', function (e) {
        e.preventDefault();

        const $pricingTable = $('#sgc-pricing-table');
        const $link = $(this);

        $pricingTable.toggle();

        if ($pricingTable.is(':visible')) {
            $link.text('Hide Pricing Table');
        } else {
            $link.text('Show Pricing Table');
        }
    });

    $('#sgc-next-btn').on('click', function () {
        if (!validateBeforeNext()) return;

        if (sgcStep2Data.nextStepUrl) {
            window.location.href = sgcStep2Data.nextStepUrl;
        }
    });

    $('#sgc-step3-next-btn').on('click', function () {
        if (sgcStep2Data.step3NextUrl) {
            window.location.href = sgcStep2Data.step3NextUrl;
        }
    });

    $('#sgc-open-address-modal').on('click', function () {
        openAddressModal();
    });

    $('#sgc-close-address-modal, #sgc-address-cancel-btn').on('click', function () {
        closeAddressModal();
    });

    $('#sgc-address-modal').on('click', function (e) {
        if (e.target === this) {
            closeAddressModal();
        }
    });

    $('#sgc_address_country').on('change', function () {
        const country = $(this).val();

        $('#sgc_address_state').html('<option value="">Loading...</option>');

        $.post(sgcStep2Data.ajaxUrl, {
            action: 'sgc_get_states_by_country',
            nonce: sgcStep2Data.nonce,
            country: country
        }).done(function (response) {
            if (response && response.success) {
                renderStateOptions(response.data.states || {}, '');
            } else {
                renderStateOptions({}, '');
            }
        });
    });

    $('#sgc-address-save-btn').on('click', function () {
        const addressId = $('#sgc_address_id').val().trim();

        const payload = {
            action: addressId ? 'sgc_update_shipping_address' : 'sgc_save_shipping_address',
            nonce: sgcStep2Data.nonce,
            address_id: addressId,
            country: $('#sgc_address_country').val(),
            street: $('#sgc_address_street').val().trim(),
            apartment: $('#sgc_address_apartment').val().trim(),
            city: $('#sgc_address_city').val().trim(),
            state: $('#sgc_address_state').val(),
            zip: $('#sgc_address_zip').val().trim(),
            phone: $('#sgc_address_phone').val().trim(),
            is_default: $('#sgc_address_default').is(':checked') ? 1 : 0
        };

        $.post(sgcStep2Data.ajaxUrl, payload).done(function (response) {
            if (response && response.success) {
                renderStep4Addresses(response.data);
                closeAddressModal();
            } else if (response && response.data && response.data.message) {
                $('#sgc-address-error').show().text(response.data.message);
            }
        });
    });

    $(document).on('click', '[data-select-address]', function () {
        const addressId = $(this).data('select-address');

        $.post(sgcStep2Data.ajaxUrl, {
            action: 'sgc_select_shipping_address',
            nonce: sgcStep2Data.nonce,
            address_id: addressId
        }).done(function (response) {
            if (response && response.success) {
                renderStep4Addresses(response.data);
            }
        });
    });

    $(document).on('click', '.sgc-step4-address-edit-btn', function (e) {
        e.stopPropagation();
        const addressId = $(this).data('address-id');
        loadAddressForEdit(addressId);
    });

    $(document).on('click', '.sgc-step4-address-delete-btn', function (e) {
        e.stopPropagation();
        const addressId = $(this).data('address-id');

        if (!window.confirm('Delete this address?')) {
            return;
        }

        $.post(sgcStep2Data.ajaxUrl, {
            action: 'sgc_delete_shipping_address',
            nonce: sgcStep2Data.nonce,
            address_id: addressId
        }).done(function (response) {
            if (response && response.success) {
                renderStep4Addresses(response.data);
            } else if (response && response.data && response.data.message) {
                alert(response.data.message);
            }
        });
    });

    $('#sgc-step4-next-btn').on('click', function () {
        const hasSelected = $('.sgc-step4-address-card.is-selected').length > 0;

        if (!hasSelected) {
            alert('Please select a return shipping address.');
            return;
        }

        if (sgcStep2Data.step4NextUrl) {
            window.location.href = sgcStep2Data.step4NextUrl;
        }
    });

    $(document).on('click', '.sgc-step5-method-card', function () {
        const methodKey = $(this).data('shipping-method');
        saveShippingMethod(methodKey);
    });

    $('#sgc-step5-next-btn').on('click', function () {
        const hasSelectedMethod = $('.sgc-step5-method-card.is-selected').length > 0;

        if (!hasSelectedMethod) {
            alert('Please select a shipping method.');
            return;
        }

        if (sgcStep2Data.step5NextUrl) {
            window.location.href = sgcStep2Data.step5NextUrl;
        }
    });

    // --- SMART DYNAMIC BACK BUTTON ROUTING ---
    $(document).on('click', '.sgc-back-btn, #sgc-step4-back-btn, #sgc-step5-back-btn, #sgc-step7-back-btn', function (e) {
        e.preventDefault();
        
        // ১. বর্তমান URL থেকে sgc_step নাম্বার বের করা
        const urlParams = new URLSearchParams(window.location.search);
        let currentStep = parseInt(urlParams.get('sgc_step')) || 1;
        
        // ২. আগের স্টেপ ক্যালকুলেট করা (বর্তমান স্টেপ - ১)
        let prevStep = currentStep - 1;
        
        // ৩. আপনার ফর্মের ফ্লো অনুযায়ী স্পেশাল রাউটিং
        // (যেমন: Step 7 Payment থেকে ব্যাক করলে Step 5 Review তে যাবে)
        if (currentStep === 7) {
            prevStep = 5;
        }
        
        // স্টেপ ১ এর নিচে যেন না যায় তা নিশ্চিত করা
        if (prevStep < 1) {
            prevStep = 1;
        }
        
        // ৪. নতুন URL সেট করে রিডাইরেক্ট করা
        urlParams.set('sgc_step', prevStep);
        window.location.search = urlParams.toString();
    });

    clearLeftSideResults(false);
    loadSelectedCards();
    loadSavedTier();
    loadStep4Addresses();
    loadStep5Review();
    initStep7Payment();
});