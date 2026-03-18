class GoogleDriveEmbed {
    static get toolbox() {
        return {
            title: 'Google Drive',
            icon: '<svg viewBox="0 0 512 512" width="20" height="20"><path fill="#FFC107" d="M170.6 341.3L85.3 192l85.3-149.3L256 192z"/><path fill="#1976D2" d="M341.4 341.3h-171L85.1 490.7h170.6z"/><path fill="#4CAF50" d="M256 42.7L341.3 192l-85.3 149.3h170.6L426.6 192z"/></svg>'
        };
    }

    constructor({ data, api }) {
        this.api = api;
        this.data = {
            url: data.url || '',
            embedCode: data.embedCode || '',
            height: data.height || 600,
            viewMode: data.viewMode || 'grid' // 'grid' or 'list'
        };
        this.wrapper = undefined;
    }

    render() {
        this.wrapper = document.createElement('div');
        this.wrapper.classList.add('cdx-block');
        this.wrapper.classList.add('google-drive-embed-block');

        if (this.data.embedCode) {
            this._createPreview();
        } else {
            this._showInput();
        }

        return this.wrapper;
    }

    _showInput() {
        this.wrapper.innerHTML = '';

        const container = document.createElement('div');
        container.classList.add('p-4', 'border', 'border-gray-300', 'rounded-lg', 'bg-gray-50', 'flex', 'flex-col', 'gap-3');

        const title = document.createElement('div');
        title.innerHTML = '<i class="fab fa-google-drive text-[#1fa463] mr-2"></i> <strong>Embed Google Drive</strong>';
        title.classList.add('text-gray-700', 'text-sm');

        const input = document.createElement('input');
        input.type = 'text';
        input.placeholder = 'Paste Google Drive file or folder URL here...';
        input.classList.add('w-full', 'px-3', 'py-2', 'border', 'border-gray-200', 'rounded', 'focus:outline-none', 'focus:border-blue-500', 'text-sm');

        const button = document.createElement('button');
        button.innerText = 'Embed';
        button.classList.add('px-4', 'py-2', 'bg-blue-600', 'text-white', 'rounded', 'hover:bg-blue-700', 'text-sm', 'font-medium', 'self-start', 'transition-colors');

        button.addEventListener('click', () => {
            this._processUrl(input.value);
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                this._processUrl(input.value);
            }
        });

        container.appendChild(title);
        container.appendChild(input);
        container.appendChild(button);
        this.wrapper.appendChild(container);
    }

    _processUrl(url) {
        if (!url) return;

        // Match generic drive folders: /folders/ID
        const folderMatch = url.match(/folders\/([a-zA-Z0-9-_]+)/);
        // Match generic drive files: /file/d/ID
        const fileMatch = url.match(/file\/d\/([a-zA-Z0-9-_]+)/);

        let id = null;
        let isFolder = false;

        if (folderMatch && folderMatch[1]) {
            id = folderMatch[1];
            isFolder = true;
        } else if (fileMatch && fileMatch[1]) {
            id = fileMatch[1];
        }

        if (id) {
            this.data.url = url;
            this.data.isFolder = isFolder;
            this.data.driveId = id;
            this._generateEmbedCode();
            this._createPreview();
        } else {
            this.api.notifier.show({
                message: 'Invalid Google Drive link. Please ensure it contains a file or folder ID.',
                style: 'error',
            });
        }
    }

    _generateEmbedCode() {
        if (!this.data.driveId) return;

        if (this.data.isFolder) {
            this.data.embedCode = `<iframe src="https://drive.google.com/embeddedfolderview?id=${this.data.driveId}#${this.data.viewMode}" width="100%" height="${this.data.height}" frameborder="0"></iframe>`;
        } else {
            this.data.embedCode = `<iframe src="https://drive.google.com/file/d/${this.data.driveId}/preview" width="100%" height="${this.data.height}" allow="autoplay" frameborder="0"></iframe>`;
        }
    }

    _createPreview() {
        this.wrapper.innerHTML = '';

        const previewContainer = document.createElement('div');
        previewContainer.classList.add('relative', 'border', 'border-gray-200', 'rounded-lg', 'overflow-hidden', 'bg-white', 'my-4');

        const toolbar = document.createElement('div');
        toolbar.classList.add('bg-gray-100', 'px-3', 'py-2', 'border-b', 'border-gray-200', 'flex', 'items-center', 'justify-between');
        toolbar.innerHTML = `
            <div class="text-xs text-gray-600 truncate"><i class="fab fa-google-drive mr-1"></i> Google Drive Embed</div>
            <div class="text-xs text-gray-400">Use block settings (tune menu) to change view or delete</div>
        `;

        const iframeWrapper = document.createElement('div');
        iframeWrapper.innerHTML = this.data.embedCode;
        // Disable pointer events in editor so user can still click around the block to drag/delete
        const iframe = iframeWrapper.querySelector('iframe');
        if (iframe) {
            iframe.style.pointerEvents = 'none';
            iframe.style.minHeight = '300px';
        }

        previewContainer.appendChild(toolbar);
        previewContainer.appendChild(iframeWrapper);
        this.wrapper.appendChild(previewContainer);
    }

    renderSettings() {
        const settings = [];

        if (this.data.isFolder) {
            settings.push({
                icon: '<svg viewBox="0 0 24 24" width="20" height="20"><path d="M3 3h8v8H3zm0 10h8v8H3zM13 3h8v8h-8zm0 10h8v8h-8z"/></svg>',
                label: 'Grid View',
                toggle: true,
                isActive: this.data.viewMode === 'grid',
                closeOnActivate: true,
                onActivate: () => {
                    this.data.viewMode = 'grid';
                    this._generateEmbedCode();
                    this._createPreview();
                }
            });

            settings.push({
                icon: '<svg viewBox="0 0 24 24" width="20" height="20"><path d="M3 13h2v-2H3v2zm0 4h2v-2H3v2zm0-8h2V7H3v2zm4 4h14v-2H7v2zm0 4h14v-2H7v2zM7 7v2h14V7H7z"/></svg>',
                label: 'List View',
                toggle: true,
                isActive: this.data.viewMode === 'list',
                closeOnActivate: true,
                onActivate: () => {
                    this.data.viewMode = 'list';
                    this._generateEmbedCode();
                    this._createPreview();
                }
            });
        }

        settings.push({
            icon: '<svg viewBox="0 0 24 24" width="20" height="20"><path d="M12 2L4.5 20.29l.71.71L12 18l6.79 3 .71-.71z"/></svg>',
            label: 'Set Custom Height',
            closeOnActivate: true,
            onActivate: () => {
                let newHeight = prompt("Enter new height in pixels (e.g., 400, 600, 800):", this.data.height);
                if (newHeight && !isNaN(newHeight)) {
                    this.data.height = parseInt(newHeight);
                    this._generateEmbedCode();
                    this._createPreview();
                }
            }
        });

        // Add a reset button replacing the old remove button
        settings.push({
            icon: '<svg viewBox="0 0 24 24" width="20" height="20"><path fill="#f44336" d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z"/></svg>',
            label: 'Clear Link (Change File)',
            closeOnActivate: true,
            onActivate: () => {
                this.data = { url: '', embedCode: '', height: 600, viewMode: 'grid' };
                this._showInput();
            }
        });

        return settings;
    }

    save(blockContent) {
        return this.data;
    }
}

// Ensure it's available globally for Editor.js to find via the config
window.GoogleDriveEmbed = GoogleDriveEmbed;
