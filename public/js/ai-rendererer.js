/**
 * AI Chat Renderer — shared rendering helpers for the AI Assistant UI.
 * Loaded by backend/ai/index.blade.php
 */
window.renderAIMessage = function (role, content, responseType, metadata, svgs) {
    const wrapper = document.createElement('div');
    wrapper.className = 'ai-message ai-message-' + (role === 'user' ? 'user' : 'assistant');

    const bubble = document.createElement('div');
    bubble.className = 'ai-bubble';

    // For user messages, just render the text
    if (role === 'user') {
        bubble.textContent = content;
        wrapper.appendChild(bubble);
        return wrapper;
    }

    // For assistant messages — render text + optional structured content
    bubble.textContent = content || '';
    wrapper.appendChild(bubble);

    // Structured content (cards, tables, warnings, errors, links)
    if (metadata && responseType !== 'text' && responseType !== 'error') {
        const structured = document.createElement('div');
        structured.className = 'ai-structured-content';

        // Cards
        if (metadata.cards && metadata.cards.length > 0) {
            const cardsGrid = document.createElement('div');
            cardsGrid.className = 'ai-cards-grid';

            const cardsTitle = document.createElement('h5');
            cardsTitle.textContent = 'Summary';
            cardsGrid.appendChild(cardsTitle);

            metadata.cards.forEach(card => {
                const cardEl = document.createElement('div');
                cardEl.className = 'ai-card';

                const cardTitle = document.createElement('div');
                cardTitle.className = 'ai-card-title';
                cardTitle.textContent = card.title || '';

                const cardValue = document.createElement('div');
                cardValue.className = 'ai-card-value';
                cardValue.textContent = card.value || '';

                cardEl.appendChild(cardTitle);
                cardEl.appendChild(cardValue);
                cardsGrid.appendChild(cardEl);
            });

            structured.appendChild(cardsGrid);
        }

        // Table
        if (metadata.table && metadata.table.columns && metadata.table.rows) {
            const tableWrap = document.createElement('div');
            tableWrap.className = 'ai-table-wrapper';

            const table = document.createElement('table');
            table.className = 'ai-table';

            const thead = document.createElement('thead');
            const headerRow = document.createElement('tr');
            metadata.table.columns.forEach(col => {
                const th = document.createElement('th');
                th.textContent = col;
                headerRow.appendChild(th);
            });
            thead.appendChild(headerRow);
            table.appendChild(thead);

            const tbody = document.createElement('tbody');
            metadata.table.rows.forEach(row => {
                const tr = document.createElement('tr');
                if (Array.isArray(row)) {
                    row.forEach(cell => {
                        const td = document.createElement('td');
                        td.textContent = cell !== null && cell !== undefined ? String(cell) : '';
                        tr.appendChild(td);
                    });
                } else if (typeof row === 'object') {
                    metadata.table.columns.forEach(col => {
                        const td = document.createElement('td');
                        td.textContent = row[col] !== null && row[col] !== undefined ? String(row[col]) : '';
                        tr.appendChild(td);
                    });
                }
                tbody.appendChild(tr);
            });
            table.appendChild(tbody);
            tableWrap.appendChild(table);
            structured.appendChild(tableWrap);
        }

        // Warnings
        if (metadata.warnings && metadata.warnings.length > 0) {
            metadata.warnings.forEach(w => {
                const warn = document.createElement('div');
                warn.className = 'ai-warning';
                if (svgs && svgs.alert) {
                    const icon = document.createElement('span');
                    icon.innerHTML = svgs.alert;
                    warn.appendChild(icon);
                }
                const text = document.createElement('div');
                text.textContent = typeof w === 'string' ? w : (w.message || JSON.stringify(w));
                warn.appendChild(text);
                structured.appendChild(warn);
            });
        }

        // Links
        if (metadata.links && metadata.links.length > 0) {
            metadata.links.forEach(link => {
                const linkWrap = document.createElement('div');
                linkWrap.className = 'ai-link';
                const a = document.createElement('a');
                a.href = link.url || '#';
                a.textContent = link.label || link.url || 'View';
                if (svgs && svgs.link) {
                    a.innerHTML = (link.label || link.url || 'View') + ' ' + svgs.link;
                }
                linkWrap.appendChild(a);
                structured.appendChild(linkWrap);
            });
        }

        if (structured.children.length > 0) {
            wrapper.appendChild(structured);
        }
    }

    return wrapper;
};

window.renderAIError = function (message, svgs) {
    const wrapper = document.createElement('div');
    wrapper.className = 'ai-message ai-message-assistant';

    const bubble = document.createElement('div');
    bubble.className = 'ai-bubble';
    bubble.style.background = '#f8d7da';
    bubble.style.color = '#721c24';
    bubble.style.borderLeft = '4px solid #f5c6cb';

    if (svgs && svgs.error) {
        const icon = document.createElement('span');
        icon.style.marginRight = '8px';
        icon.innerHTML = svgs.error;
        bubble.appendChild(icon);
    }

    const text = document.createElement('span');
    text.textContent = message;
    bubble.appendChild(text);

    wrapper.appendChild(bubble);
    return wrapper;
};