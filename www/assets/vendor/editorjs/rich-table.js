/**
 * RichTable - Custom Editor.js Block Tool
 * Features:
 *  - Rich content per cell (bold, italic, line breaks with Enter)
 *  - Column width resize (drag handle)
 *  - Cell text alignment (left/center/right/top/middle/bottom)
 *  - Insert links as buttons or plain links, with target selection
 *  - With/without header row
 *  - Add / remove row & column
 */
class RichTable {
    static get toolbox() {
        return {
            title: 'Rich Table',
            icon: '<svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="1" y="1" width="16" height="16" rx="2" stroke="currentColor" stroke-width="1.5" fill="none"/><line x1="1" y1="6" x2="17" y2="6" stroke="currentColor" stroke-width="1.5"/><line x1="1" y1="11" x2="17" y2="11" stroke="currentColor" stroke-width="1.5"/><line x1="7" y1="1" x2="7" y2="17" stroke="currentColor" stroke-width="1.5"/><line x1="12" y1="1" x2="12" y2="17" stroke="currentColor" stroke-width="1.5"/></svg>'
        };
    }

    static get isReadOnlySupported() { return true; }
    static get enableLineBreaks() { return true; }


    constructor({ data, api, readOnly }) {
        this.api = api;
        this.readOnly = readOnly;
        this._data = data && data.rows ? data : {
            withHeadings: true,
            colWidths: [],
            rows: [
                [
                    { content: 'หัวข้อ 1', align: 'center', valign: 'middle' },
                    { content: 'หัวข้อ 2', align: 'center', valign: 'middle' },
                    { content: 'หัวข้อ 3', align: 'center', valign: 'middle' }
                ],
                [
                    { content: '', align: 'left', valign: 'top' },
                    { content: '', align: 'left', valign: 'top' },
                    { content: '', align: 'left', valign: 'top' }
                ]
            ]
        };
        this.container = null;
        this.linkModal = null;
        this.activeCell = null;
        this._editingLink = null;
        this._savedRange = null;   // Save cursor position before blur
        this._savedRangeCell = null;
    }

    render() {
        this.container = document.createElement('div');
        this.container.classList.add('rtable-wrapper');
        this.container.style.cssText = 'position:relative; margin:1rem 0;';

        this._buildUI();
        return this.container;
    }

    _buildUI() {
        this.container.innerHTML = '';

        const toolbar = this._buildToolbar();
        const tableWrap = this._buildTable();

        this.container.appendChild(toolbar);
        this.container.appendChild(tableWrap);
        this._buildLinkModal();
    }

    _buildToolbar() {
        const bar = document.createElement('div');
        bar.style.cssText = `
            position:sticky; top:10px; z-index:40;
            display:flex; align-items:center; gap:6px; flex-wrap:wrap;
            background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px 10px 0 0;
            padding:6px 10px; box-shadow:0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05);
        `;

        const btnStyle = (color='#475569') => `
            display:inline-flex; align-items:center; justify-content:center;
            gap:4px; height:30px; padding:0 10px; border-radius:6px;
            border:1px solid #e2e8f0; background:#fff; cursor:pointer;
            font-size:12px; font-weight:600; color:${color};
            transition:all 0.15s; white-space:nowrap;
        `;

        // Heading toggle
        const headingBtn = document.createElement('button');
        headingBtn.type = 'button';
        headingBtn.innerHTML = `<svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M4 6h16M4 12h16M4 18h7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg> หัวตาราง`;
        headingBtn.style.cssText = btnStyle(this._data.withHeadings ? '#4f46e5' : '#475569');
        headingBtn.style.background = this._data.withHeadings ? '#eef2ff' : '#fff';
        headingBtn.title = 'Toggle header row';
        headingBtn.addEventListener('click', () => {
            this._data.withHeadings = !this._data.withHeadings;
            this._buildUI();
        });

        // Separator
        const sep = () => {
            const s = document.createElement('div');
            s.style.cssText = 'width:1px;height:20px;background:#e2e8f0;margin:0 2px;';
            return s;
        };

        // Add Row
        const addRowBtn = document.createElement('button');
        addRowBtn.type = 'button';
        addRowBtn.innerHTML = `<svg width="12" height="12" viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2"/><path d="M12 8v8M8 12h8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg> แถว`;
        addRowBtn.style.cssText = btnStyle('#059669');
        addRowBtn.title = 'Add row';
        addRowBtn.addEventListener('click', () => this._addRow());

        // Add Col
        const addColBtn = document.createElement('button');
        addColBtn.type = 'button';
        addColBtn.innerHTML = `<svg width="12" height="12" viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2"/><path d="M12 8v8M8 12h8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg> คอลัมน์`;
        addColBtn.style.cssText = btnStyle('#0ea5e9');
        addColBtn.title = 'Add column';
        addColBtn.addEventListener('click', () => this._addCol());

        // Delete Row
        const delRowBtn = document.createElement('button');
        delRowBtn.type = 'button';
        delRowBtn.innerHTML = `<svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M6 6l12 12M6 18L18 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg> ลบแถว`;
        delRowBtn.style.cssText = btnStyle('#dc2626');
        delRowBtn.title = 'Delete last row';
        delRowBtn.addEventListener('click', () => this._deleteRow());

        // Delete Col
        const delColBtn = document.createElement('button');
        delColBtn.type = 'button';
        delColBtn.innerHTML = `<svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M6 6l12 12M6 18L18 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg> ลบคอลัมน์`;
        delColBtn.style.cssText = btnStyle('#dc2626');
        delColBtn.title = 'Delete last column';
        delColBtn.addEventListener('click', () => this._deleteCol());

        // Cell toolbar (alignment)
        bar.appendChild(headingBtn);
        bar.appendChild(sep());
        bar.appendChild(addRowBtn);
        bar.appendChild(addColBtn);
        bar.appendChild(sep());
        bar.appendChild(delRowBtn);
        bar.appendChild(delColBtn);
        bar.appendChild(sep());

        // Align buttons
        const alignBtns = [
            { icon: `<svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M3 6h18M3 12h12M3 18h15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>`, val: 'left', title: 'จัดซ้าย' },
            { icon: `<svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M3 6h18M6 12h12M4 18h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>`, val: 'center', title: 'จัดกึ่งกลาง' },
            { icon: `<svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M3 6h18M9 12h12M6 18h15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>`, val: 'right', title: 'จัดขวา' },
        ];

        alignBtns.forEach(({ icon, val, title }) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.innerHTML = icon;
            btn.style.cssText = `width:30px;height:30px;border-radius:6px;border:1px solid #e2e8f0;background:#fff;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;color:#475569;transition:all 0.15s;`;
            btn.title = title;
            btn.dataset.alignVal = val;
            btn.addEventListener('click', () => this._setSelectedCellsAlign(val));
            bar.appendChild(btn);
        });

        bar.appendChild(sep());

        // V-Align buttons
        const valignBtns = [
            { icon: `<svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M12 4v4M8 8h8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><rect x="6" y="10" width="12" height="10" rx="2" stroke="currentColor" stroke-width="1.5" fill="none"/></svg>`, val: 'top', title: 'จัดบน' },
            { icon: `<svg width="12" height="12" viewBox="0 0 24 24" fill="none"><rect x="6" y="4" width="12" height="16" rx="2" stroke="currentColor" stroke-width="1.5" fill="none"/><path d="M12 12V16M8 14h8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>`, val: 'middle', title: 'จัดกลาง' },
            { icon: `<svg width="12" height="12" viewBox="0 0 24 24" fill="none"><rect x="6" y="4" width="12" height="10" rx="2" stroke="currentColor" stroke-width="1.5" fill="none"/><path d="M12 16v4M8 16h8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>`, val: 'bottom', title: 'จัดล่าง' },
        ];

        valignBtns.forEach(({ icon, val, title }) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.innerHTML = icon;
            btn.style.cssText = `width:30px;height:30px;border-radius:6px;border:1px solid #e2e8f0;background:#fff;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;color:#475569;transition:all 0.15s;`;
            btn.title = title;
            btn.dataset.valignVal = val;
            btn.addEventListener('click', () => this._setSelectedCellsVAlign(val));
            bar.appendChild(btn);
        });

        bar.appendChild(sep());

        // Link button
        const linkBtn = document.createElement('button');
        linkBtn.type = 'button';
        linkBtn.innerHTML = `<svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg> ลิงก์/ปุ่ม`;
        linkBtn.style.cssText = btnStyle('#7c3aed');
        linkBtn.title = 'แทรกลิงก์หรือปุ่ม';
        linkBtn.addEventListener('click', () => this._openLinkModal());
        bar.appendChild(linkBtn);

        return bar;
    }

    _buildTable() {
        const wrap = document.createElement('div');
        wrap.style.cssText = 'overflow-x:auto;';

        const table = document.createElement('table');
        table.style.cssText = `
            width:100%; border-collapse:collapse;
            border:1px solid #e2e8f0; border-radius:0 0 8px 8px;
            table-layout:fixed;
        `;
        table.id = 'rtable-' + Math.random().toString(36).slice(2, 7);
        this._tableEl = table;

        const colGroup = document.createElement('colgroup');
        const numCols = this._data.rows[0]?.length || 1;
        for (let c = 0; c < numCols; c++) {
            const col = document.createElement('col');
            const w = this._data.colWidths?.[c];
            col.style.width = w ? w + 'px' : Math.floor(100 / numCols) + '%';
            colGroup.appendChild(col);
        }
        table.appendChild(colGroup);

        this._data.rows.forEach((row, ri) => {
            const tr = this._buildRow(row, ri);
            table.appendChild(tr);
        });

        // Resize handles between columns (header row)
        this._addResizeHandles(table, colGroup);

        wrap.appendChild(table);
        return wrap;
    }

    _buildRow(rowData, ri) {
        const tr = document.createElement('tr');
        tr.dataset.row = ri;

        rowData.forEach((cellData, ci) => {
            const isHead = this._data.withHeadings && ri === 0;
            const tag = isHead ? 'th' : 'td';
            const cell = document.createElement(tag);
            cell.dataset.row = ri;
            cell.dataset.col = ci;

            // Styles
            const vAlignMap = { top: 'flex-start', middle: 'center', bottom: 'flex-end' };
            cell.style.cssText = `
                border:1px solid #e2e8f0;
                padding:0; min-width:80px; position:relative;
                background:${isHead ? '#f1f5f9' : '#fff'};
                vertical-align:${cellData.valign || 'top'};
            `;

            // Inner content editable
            const inner = document.createElement('div');
            inner.contentEditable = this.readOnly ? 'false' : 'true';
            inner.style.cssText = `
                min-height:40px; padding:8px 10px;
                outline:none;
                text-align:${cellData.align || 'left'};
                font-weight:${isHead ? '600' : 'normal'};
                word-break:break-word;
                line-height:1.6;
            `;
            inner.innerHTML = cellData.content || '';

            // Events
            if (!this.readOnly) {
                inner.addEventListener('focus', () => {
                    cell.style.outline = '2px solid #6366f1';
                    cell.style.outlineOffset = '-2px';
                    cell.style.borderRadius = '4px';
                    this.activeCell = { ri, ci, inner, cell };
                    this._syncToolbarToCell(cellData);
                });
                inner.addEventListener('blur', () => {
                    cell.style.outline = '';
                    cell.style.borderRadius = '';
                    this._data.rows[ri][ci].content = inner.innerHTML;
                    // 🔑 Save cursor position before focus leaves the cell
                    const sel = window.getSelection();
                    if (sel && sel.rangeCount > 0) {
                        try {
                            this._savedRange = sel.getRangeAt(0).cloneRange();
                            this._savedRangeCell = { ri, ci, inner, cell };
                        } catch (e) { /* ignore */ }
                    }
                });
                inner.addEventListener('keydown', (e) => {
                    if (e.key === 'Tab') {
                        e.preventDefault();
                        const nextCell = this._getNextCell(ri, ci);
                        if (nextCell) nextCell.focus();
                    }
                });
                inner.addEventListener('paste', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    const text = e.clipboardData.getData('text/plain');
                    if (!text) return;

                    const sel = window.getSelection();
                    if (sel && sel.rangeCount > 0) {
                        const range = sel.getRangeAt(0);
                        range.deleteContents();
                        const textNode = document.createTextNode(text);
                        range.insertNode(textNode);
                        // Move cursor to end of inserted text
                        range.setStartAfter(textNode);
                        range.collapse(true);
                        sel.removeAllRanges();
                        sel.addRange(range);
                    } else {
                        // Fallback: append to end
                        inner.innerHTML += text;
                    }
                    // Save content after paste
                    this._data.rows[ri][ci].content = inner.innerHTML;
                });

                // Double-click on <a> tag = open edit modal for that link
                inner.addEventListener('dblclick', (e) => {
                    const anchor = e.target.closest('a');
                    if (anchor) {
                        e.preventDefault();
                        this.activeCell = { ri, ci, inner, cell };
                        this._editingLink = anchor;
                        this._openLinkModal(anchor);
                    }
                });

                // Ctrl+click on <a> also opens edit
                inner.addEventListener('click', (e) => {
                    if (e.ctrlKey || e.metaKey) {
                        const anchor = e.target.closest('a');
                        if (anchor) {
                            e.preventDefault();
                            this.activeCell = { ri, ci, inner, cell };
                            this._editingLink = anchor;
                            this._openLinkModal(anchor);
                        }
                    }
                });
            }

            cell.appendChild(inner);

            // Row control (delete row) - only on last col
            if (!this.readOnly && ci === rowData.length - 1) {
                const rowCtrl = document.createElement('div');
                rowCtrl.style.cssText = `
                    position:absolute; right:-26px; top:50%; transform:translateY(-50%);
                    width:22px; height:22px; border-radius:50%;
                    background:#fee2e2; border:1px solid #fca5a5;
                    display:flex; align-items:center; justify-content:center;
                    cursor:pointer; opacity:0; transition:opacity 0.15s;
                    font-size:10px; color:#dc2626; z-index:10;
                `;
                rowCtrl.innerHTML = `<svg width="10" height="10" viewBox="0 0 24 24" fill="none"><path d="M6 18L18 6M6 6l12 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>`;
                rowCtrl.title = 'ลบแถวนี้';
                rowCtrl.addEventListener('click', (e) => {
                    e.stopPropagation();
                    this._deleteSpecificRow(ri);
                });
                tr.addEventListener('mouseenter', () => rowCtrl.style.opacity = '1');
                tr.addEventListener('mouseleave', () => rowCtrl.style.opacity = '0');
                // inline-block usage so it doesn't affect table layout
                cell.style.position = 'relative';
                // We place rowCtrl outside cell as overlay
                rowCtrl.style.right = '-28px';
            }

            tr.appendChild(cell);
        });

        return tr;
    }

    _addResizeHandles(table, colGroup) {
        if (this.readOnly) return;

        const cols = colGroup.querySelectorAll('col');
        const thead = table.querySelector('tr');
        if (!thead) return;

        const cells = thead.querySelectorAll('th, td');
        cells.forEach((cell, i) => {
            if (i === cells.length - 1) return; // no handle after last col

            const handle = document.createElement('div');
            handle.style.cssText = `
                position:absolute; right:-3px; top:0; bottom:0;
                width:6px; cursor:col-resize; z-index:5;
                display:flex; align-items:center; justify-content:center;
            `;
            handle.innerHTML = `<div style="width:2px;height:60%;background:#cbd5e1;border-radius:2px;transition:background 0.15s;"></div>`;
            handle.addEventListener('mouseenter', () => { handle.children[0].style.background = '#6366f1'; });
            handle.addEventListener('mouseleave', () => { handle.children[0].style.background = '#cbd5e1'; });

            cell.style.position = 'relative';
            cell.appendChild(handle);

            let startX, startW, nextW;
            handle.addEventListener('mousedown', (e) => {
                e.preventDefault();
                startX = e.pageX;
                startW = cell.getBoundingClientRect().width;
                const nextCell = thead.querySelectorAll('th, td')[i + 1];
                nextW = nextCell ? nextCell.getBoundingClientRect().width : 0;

                const onMove = (me) => {
                    const diff = me.pageX - startX;
                    const newW = Math.max(60, startW + diff);
                    cols[i].style.width = newW + 'px';
                    if (!this._data.colWidths) this._data.colWidths = [];
                    this._data.colWidths[i] = newW;
                };
                const onUp = () => {
                    document.removeEventListener('mousemove', onMove);
                    document.removeEventListener('mouseup', onUp);
                };
                document.addEventListener('mousemove', onMove);
                document.addEventListener('mouseup', onUp);
            });
        });
    }

    _syncToolbarToCell(cellData) {
        const bar = this.container.querySelector('div');
        if (!bar) return;
        bar.querySelectorAll('[data-align-val]').forEach(btn => {
            const isActive = btn.dataset.alignVal === (cellData.align || 'left');
            btn.style.background = isActive ? '#eef2ff' : '#fff';
            btn.style.color = isActive ? '#4f46e5' : '#475569';
        });
        bar.querySelectorAll('[data-valign-val]').forEach(btn => {
            const isActive = btn.dataset.valignVal === (cellData.valign || 'top');
            btn.style.background = isActive ? '#eef2ff' : '#fff';
            btn.style.color = isActive ? '#4f46e5' : '#475569';
        });
    }

    _getNextCell(ri, ci) {
        const numCols = this._data.rows[0]?.length || 1;
        const numRows = this._data.rows.length;
        let nextCi = ci + 1, nextRi = ri;
        if (nextCi >= numCols) { nextCi = 0; nextRi++; }
        if (nextRi >= numRows) { this._addRow(); nextRi = numRows; nextCi = 0; }
        const cells = this._tableEl.querySelectorAll(`[data-row="${nextRi}"][data-col="${nextCi}"] [contenteditable]`);
        return cells[0] || null;
    }

    _setSelectedCellsAlign(align) {
        if (this.activeCell) {
            const { ri, ci, inner } = this.activeCell;
            this._data.rows[ri][ci].align = align;
            inner.style.textAlign = align;
            this._syncToolbarToCell(this._data.rows[ri][ci]);
        }
    }

    _setSelectedCellsVAlign(valign) {
        if (this.activeCell) {
            const { ri, ci, inner, cell } = this.activeCell;
            this._data.rows[ri][ci].valign = valign;
            cell.style.verticalAlign = valign;
            this._syncToolbarToCell(this._data.rows[ri][ci]);
        }
    }

    _addRow() {
        const numCols = this._data.rows[0]?.length || 1;
        const newRow = Array.from({ length: numCols }, () => ({ content: '', align: 'left', valign: 'top' }));
        this._data.rows.push(newRow);
        this._buildUI();
    }

    _addCol() {
        this._data.rows.forEach((row) => row.push({ content: '', align: 'left', valign: 'top' }));
        this._buildUI();
    }

    _deleteRow() {
        if (this._data.rows.length <= 1) return;
        this._data.rows.pop();
        this._buildUI();
    }

    _deleteSpecificRow(ri) {
        if (this._data.rows.length <= 1) return;
        this._data.rows.splice(ri, 1);
        this._buildUI();
    }

    _deleteCol() {
        if (this._data.rows[0]?.length <= 1) return;
        this._data.rows.forEach(row => row.pop());
        if (this._data.colWidths) this._data.colWidths.pop();
        this._buildUI();
    }

    // ======= Link/Button Modal =======
    _buildLinkModal() {
        const existing = document.getElementById('rtable-link-modal');
        if (existing) { this.linkModal = existing; return; }

        const modal = document.createElement('div');
        modal.id = 'rtable-link-modal';
        modal.style.cssText = `
            display:none; position:fixed; inset:0; z-index:9999;
            background:rgba(0,0,0,0.4); backdrop-filter:blur(4px);
            align-items:center; justify-content:center;
        `;

        const box = document.createElement('div');
        box.style.cssText = `
            background:#fff; border-radius:16px; padding:28px 32px;
            width:460px; max-width:95vw; box-shadow:0 20px 60px rgba(0,0,0,0.15);
        `;

        box.innerHTML = `
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                <h3 style="font-size:16px;font-weight:700;color:#1e293b;margin:0;">แทรกลิงก์หรือปุ่ม</h3>
                <button id="rtable-link-close" type="button" style="width:32px;height:32px;border-radius:50%;border:none;background:#f1f5f9;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#64748b;font-size:16px;">✕</button>
            </div>
            <div style="display:flex;flex-direction:column;gap:14px;">
                <div>
                    <label style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;display:block;margin-bottom:4px;">ข้อความ / Label</label>
                    <input id="rtable-link-text" type="text" placeholder="ข้อความบนปุ่มหรือลิงก์..."
                        style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:14px;outline:none;box-sizing:border-box;"/>
                </div>
                <div>
                    <label style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;display:block;margin-bottom:4px;">URL</label>
                    <input id="rtable-link-url" type="url" placeholder="https://..."
                        style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:14px;outline:none;box-sizing:border-box;"/>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <label style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;display:block;margin-bottom:4px;">เปิดใน</label>
                        <select id="rtable-link-target" style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:14px;outline:none;background:#fff;">
                            <option value="_blank">แท็บใหม่</option>
                            <option value="_self">แท็บเดิม</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;display:block;margin-bottom:4px;">รูปแบบ</label>
                        <select id="rtable-link-style" style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:14px;outline:none;background:#fff;">
                            <option value="link">ลิงก์ธรรมดา</option>
                            <option value="btn-primary">ปุ่ม Primary (น้ำเงิน)</option>
                            <option value="btn-success">ปุ่ม Success (เขียว)</option>
                            <option value="btn-danger">ปุ่ม Danger (แดง)</option>
                            <option value="btn-outline">ปุ่ม Outline</option>
                            <option value="btn-gray">ปุ่ม Gray</option>
                        </select>
                    </div>
                </div>
                <div style="display:flex;gap:10px;margin-top:6px;">
                    <button id="rtable-link-insert" type="button" style="flex:1;padding:12px;background:linear-gradient(135deg,#6366f1,#4f46e5);color:#fff;border:none;border-radius:10px;font-weight:700;font-size:14px;cursor:pointer;">แทรกลิงก์/ปุ่ม</button>
                    <button id="rtable-link-cancel" type="button" style="padding:12px 20px;background:#f1f5f9;color:#64748b;border:none;border-radius:10px;font-weight:600;font-size:14px;cursor:pointer;">ยกเลิก</button>
                </div>
            </div>
        `;

        modal.appendChild(box);
        document.body.appendChild(modal);
        this.linkModal = modal;

        modal.addEventListener('click', (e) => { if (e.target === modal) this._closeLinkModal(); });
        document.getElementById('rtable-link-close').addEventListener('click', () => this._closeLinkModal());
        document.getElementById('rtable-link-cancel').addEventListener('click', () => this._closeLinkModal());
        document.getElementById('rtable-link-insert').addEventListener('click', () => this._insertLink());
    }

    _openLinkModal(existingAnchor = null) {
        const modal = document.getElementById('rtable-link-modal') || this.linkModal;
        if (!modal) { this._buildLinkModal(); }

        if (existingAnchor) {
            // Pre-fill from existing anchor
            document.getElementById('rtable-link-text').value = existingAnchor.textContent || '';
            document.getElementById('rtable-link-url').value = existingAnchor.getAttribute('href') || '';
            document.getElementById('rtable-link-target').value = existingAnchor.getAttribute('target') || '_blank';

            // Detect style from inline style
            const s = existingAnchor.getAttribute('style') || '';
            let styleVal = 'link';
            if (s.includes('background:#4f46e5') || s.includes('background: #4f46e5')) styleVal = 'btn-primary';
            else if (s.includes('background:#059669') || s.includes('background: #059669')) styleVal = 'btn-success';
            else if (s.includes('background:#dc2626') || s.includes('background: #dc2626')) styleVal = 'btn-danger';
            else if (s.includes('border:2px solid') || s.includes('border: 2px solid')) styleVal = 'btn-outline';
            else if (s.includes('background:#e2e8f0') || s.includes('background: #e2e8f0')) styleVal = 'btn-gray';
            document.getElementById('rtable-link-style').value = styleVal;

            // Change button text to indicate edit mode
            document.getElementById('rtable-link-insert').textContent = '✏️ อัปเดตลิงก์/ปุ่ม';
        } else {
            this._editingLink = null;
            document.getElementById('rtable-link-url').value = '';
            document.getElementById('rtable-link-text').value = '';
            document.getElementById('rtable-link-target').value = '_blank';
            document.getElementById('rtable-link-style').value = 'link';
            document.getElementById('rtable-link-insert').textContent = 'แทรกลิงก์/ปุ่ม';
        }

        modal.style.display = 'flex';
        setTimeout(() => { document.getElementById('rtable-link-text').focus(); }, 100);
    }

    _closeLinkModal() {
        const modal = document.getElementById('rtable-link-modal') || this.linkModal;
        if (modal) modal.style.display = 'none';
    }

    _insertLink() {
        const text = document.getElementById('rtable-link-text').value.trim();
        const url = document.getElementById('rtable-link-url').value.trim();
        const target = document.getElementById('rtable-link-target').value;
        const style = document.getElementById('rtable-link-style').value;

        if (!url) { alert('กรุณากรอก URL'); return; }
        const label = text || url;

        const styleMap = {
            'link': `style="color:#4f46e5;text-decoration:underline;"`,
            'btn-primary': `style="display:inline-block;padding:6px 16px;background:#4f46e5;color:#fff;border-radius:8px;text-decoration:none;font-weight:600;font-size:13px;"`,
            'btn-success': `style="display:inline-block;padding:6px 16px;background:#059669;color:#fff;border-radius:8px;text-decoration:none;font-weight:600;font-size:13px;"`,
            'btn-danger': `style="display:inline-block;padding:6px 16px;background:#dc2626;color:#fff;border-radius:8px;text-decoration:none;font-weight:600;font-size:13px;"`,
            'btn-outline': `style="display:inline-block;padding:6px 16px;background:transparent;color:#4f46e5;border:2px solid #4f46e5;border-radius:8px;text-decoration:none;font-weight:600;font-size:13px;"`,
            'btn-gray': `style="display:inline-block;padding:6px 16px;background:#e2e8f0;color:#374151;border-radius:8px;text-decoration:none;font-weight:600;font-size:13px;"`,
        };

        // Wrap link in a <div> so it acts as a block (prevents overlap)
        const isBtn = style !== 'link';
        const newHtml = isBtn
            ? `<div style="margin:3px 0;"><a href="${url}" target="${target}" rel="noopener noreferrer" ${styleMap[style] || ''}>${label}</a></div>`
            : `<a href="${url}" target="${target}" rel="noopener noreferrer" ${styleMap['link']}>${label}</a>`;

        if (this._editingLink) {
            // === EDIT MODE: Replace existing anchor ===
            const inner = this.activeCell?.inner;
            if (!inner) { alert('กรุณาคลิกที่ช่องตารางก่อนแก้ไขลิงก์'); return; }
            this._editingLink.outerHTML = newHtml;
            this._editingLink = null;
            this._data.rows[this.activeCell.ri][this.activeCell.ci].content = inner.innerHTML;

        } else {
            // === INSERT MODE: Append link/button to the cell ===
            const cellRef = this._savedRangeCell || this.activeCell;
            if (!cellRef) { alert('กรุณาคลิกที่ช่องตารางก่อนแทรกลิงก์'); return; }

            const { ri, ci, inner } = cellRef;

            // If it's a button (wrapped in div), just append — the div wrapper handles block separation
            // If it's a plain link, append inline at end of existing content
            const currentHTML = inner.innerHTML;
            if (isBtn) {
                // Button: append as new block div — naturally goes to next line
                inner.innerHTML = currentHTML + newHtml;
            } else {
                // Plain link: append at end of text (inline)
                inner.innerHTML = currentHTML + newHtml;
            }

            this._savedRange = null;
            this._savedRangeCell = null;
            this._data.rows[ri][ci].content = inner.innerHTML;
        }


        this._closeLinkModal();
    }


    save() {
        // Sync all cell contents from DOM before saving
        if (this._tableEl) {
            this._tableEl.querySelectorAll('[contenteditable]').forEach(inner => {
                const cell = inner.parentElement;
                const ri = parseInt(cell.dataset.row);
                const ci = parseInt(cell.dataset.col);
                if (!isNaN(ri) && !isNaN(ci) && this._data.rows[ri]?.[ci] !== undefined) {
                    this._data.rows[ri][ci].content = inner.innerHTML;
                }
            });
        }
        return {
            withHeadings: this._data.withHeadings,
            colWidths: this._data.colWidths || [],
            rows: this._data.rows
        };
    }

    renderSettings() {
        return [
            {
                label: 'มีแถวหัวตาราง',
                icon: `<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="18" height="5" rx="1" fill="currentColor" opacity="0.5"/><rect x="3" y="10" width="18" height="11" rx="1" stroke="currentColor" stroke-width="1.5" fill="none"/></svg>`,
                isActive: this._data.withHeadings,
                closeOnActivate: true,
                toggle: 'withHeadings',
                onActivate: () => {
                    this._data.withHeadings = !this._data.withHeadings;
                    this._buildUI();
                }
            }
        ];
    }
}

// Always expose to window for Editor.js to pick up
if (typeof window !== 'undefined') {
    window.RichTable = RichTable;
}
