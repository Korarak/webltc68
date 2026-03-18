<?php
include 'middleware.php';
ob_start();
?>

<div class="h-[calc(100vh-80px)] bg-gray-50 flex flex-col" x-data="fileManager()">
    
    <!-- Toolbar -->
    <div class="bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-4">
            <h1 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-folder-open text-yellow-500"></i> จัดการไฟล์
            </h1>
            <div class="h-6 w-px bg-gray-300"></div>
            <!-- Breadcrumbs -->
            <div class="flex items-center text-sm text-gray-600">
                <button @click="loadPath('')" class="hover:text-blue-600 transition-colors flex items-center gap-1">
                    <i class="fas fa-home"></i> Root
                </button>
                <template x-for="(crumb, index) in breadcrumbs" :key="index">
                    <div class="flex items-center">
                        <span class="mx-2 text-gray-400">/</span>
                        <button @click="loadPath(crumb.path)" class="hover:text-blue-600 font-medium" x-text="crumb.name"></button>
                    </div>
                </template>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <input type="file" id="fileInput" class="hidden" multiple @change="uploadFiles($event)">
            <button @click="triggerUpload()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm shadow-emerald-200">
                <i class="fas fa-cloud-upload-alt mr-1"></i> อัปโหลด
            </button>
            <button @click="createFolder()" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm">
                <i class="fas fa-folder-plus mr-1"></i> โฟลเดอร์ใหม่
            </button>
            <button @click="downloadZip()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm shadow-blue-200">
                <i class="fas fa-file-archive mr-1"></i> Backup (Zip)
            </button>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 overflow-hidden flex">
        
        <!-- File Grid -->
        <div class="flex-1 overflow-y-auto p-6" id="drop-zone" 
             @dragover.prevent="dragover = true" 
             @dragleave.prevent="dragover = false"
             @drop.prevent="handleDrop($event)"
             :class="{'bg-blue-50 border-2 border-dashed border-blue-400': dragover}">
            
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 gap-4">
                <!-- Back Button (if not root) -->
                <template x-if="currentPath !== ''">
                    <div @dblclick="goUp()" 
                         class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 hover:bg-gray-50 cursor-pointer flex flex-col items-center justify-center h-40 transition-all select-none group"
                         :class="{'ring-2 ring-emerald-400 bg-emerald-50': dropTarget === 'parent'}"
                         @dragover.prevent="if(draggingItem) dropTarget = 'parent'"
                         @dragleave.prevent="if(dropTarget === 'parent') dropTarget = null"
                         @drop.prevent="dropOnParent()">
                        <i class="fas fa-level-up-alt text-3xl text-gray-300 group-hover:text-gray-500 mb-2"></i>
                        <span class="text-sm font-medium text-gray-500">...</span>
                    </div>
                </template>

                <template x-for="item in items" :key="item.path">
                    <div @dblclick="item.type === 'folder' ? loadPath(item.path) : previewFile(item)" 
                         class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 hover:shadow-md hover:border-blue-300 cursor-pointer flex flex-col items-center justify-between h-40 transition-all relative group"
                         :class="{
                             'ring-2 ring-blue-500 bg-blue-50': selectedItem === item,
                             'ring-2 ring-emerald-400 bg-emerald-50': dropTarget === item,
                             'opacity-50 border-dashed': draggingItem === item
                         }"
                         @click="selectedItem = item"
                         draggable="true"
                         @dragstart="dragStart($event, item)"
                         @dragend="dragEnd($event)"
                         @dragover.prevent="dragOverFolder($event, item)"
                         @dragleave.prevent="dragLeaveFolder($event, item)"
                         @drop.prevent="dropOnFolder($event, item)">
                        
                        <!-- Icon / Thumbnail -->
                        <div class="flex-1 flex items-center justify-center w-full overflow-hidden">
                             <template x-if="item.type === 'folder'">
                                 <i class="fas fa-folder text-5xl text-yellow-400 drop-shadow-sm"></i>
                             </template>
                             <template x-if="item.type === 'file'">
                                 <div>
                                     <template x-if="isImage(item.ext)">
                                         <img :src="item.url" class="max-h-24 max-w-full rounded shadow-sm object-cover">
                                     </template>
                                     <template x-if="!isImage(item.ext)">
                                         <i class="fas text-4xl text-gray-400 group-hover:text-blue-500 transition-colors" :class="getFileIcon(item.ext)"></i>
                                     </template>
                                 </div>
                             </template>
                        </div>

                        <!-- Info -->
                        <div class="w-full text-center mt-3">
                            <p class="text-xs font-medium text-gray-700 truncate w-full px-1" x-text="item.name" :title="item.name"></p>
                            <p class="text-[10px] text-gray-400 mt-1" x-text="item.type === 'folder' ? item.count + ' items' : item.size"></p>
                        </div>

                        <!-- Actions Hover -->
                        <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity flex gap-1 bg-white/80 backdrop-blur-sm rounded-lg p-1 shadow-sm z-10">
                             <button @click.stop="copyLink(item)" class="text-gray-500 hover:text-blue-600 p-1 rounded-md" title="Copy Link/Share">
                                 <i class="fas fa-link text-xs"></i>
                             </button>
                             <button @click.stop="deleteItem(item)" class="text-gray-500 hover:text-red-600 p-1 rounded-md" title="Delete">
                                 <i class="fas fa-trash-alt text-xs"></i>
                             </button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Empty State -->
            <div x-show="items.length === 0 && currentPath === ''" class="h-full flex flex-col items-center justify-center text-gray-300">
                <i class="fas fa-folder-open text-6xl mb-4"></i>
                <p>No files found</p>
                <button @click="triggerUpload()" class="mt-4 text-blue-500 hover:underline">Upload your first file</button>
            </div>
            
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('fileManager', () => ({
        items: [],
        currentPath: '',
        selectedItem: null,
        dragover: false,
        draggingItem: null,
        dropTarget: null,
        
        init() {
            this.loadPath('');
        },

        get breadcrumbs() {
            if(!this.currentPath) return [];
            let parts = this.currentPath.split('/');
            let crumbs = [];
            let acc = '';
            parts.forEach(p => {
                if(p) {
                    acc = acc ? acc + '/' + p : p;
                    crumbs.push({ name: p, path: acc });
                }
            });
            return crumbs;
        },

        loadPath(path) {
            this.currentPath = path;
            this.selectedItem = null;
            fetch('file_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'list', path: path })
            })
            .then(r => r.json())
            .then(d => {
                if(d.success) {
                    this.items = d.data;
                } else {
                    Swal.fire('Error', d.message, 'error');
                }
            });
        },

        goUp() {
            let parts = this.currentPath.split('/');
            parts.pop();
            this.loadPath(parts.join('/'));
        },

        isImage(ext) {
            return ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext.toLowerCase());
        },

        getFileIcon(ext) {
            const map = {
                'pdf': 'fa-file-pdf',
                'doc': 'fa-file-word', 'docx': 'fa-file-word',
                'xls': 'fa-file-excel', 'xlsx': 'fa-file-excel',
                'zip': 'fa-file-archive', 'rar': 'fa-file-archive',
                'mp4': 'fa-file-video', 'mov': 'fa-file-video'
            };
            return map[ext.toLowerCase()] || 'fa-file';
        },

        createFolder() {
            Swal.fire({
                title: 'New Folder',
                input: 'text',
                inputPlaceholder: 'Folder Name',
                showCancelButton: true
            }).then((res) => {
                if(res.isConfirmed && res.value) {
                    fetch('file_api.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'create_folder', path: this.currentPath, name: res.value })
                    }).then(r=>r.json()).then(d=>{
                        if(d.success) this.loadPath(this.currentPath);
                        else Swal.fire('Error', d.message, 'error');
                    });
                }
            });
        },

        deleteItem(item) {
            Swal.fire({
                title: 'Are you sure?',
                text: `Delete ${item.name}? This cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Delete'
            }).then((r) => {
                if(r.isConfirmed) {
                    fetch('file_api.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'delete', path: item.path }) 
                    }).then(res=>res.json()).then(d=>{
                        if(d.success) {
                            this.loadPath(this.currentPath);
                            const Toast = Swal.mixin({
                                toast: true, position: 'top-end', showConfirmButton: false, timer: 1500
                            });
                            Toast.fire({ icon: 'success', title: 'Deleted' });
                        } else {
                            Swal.fire('Error', d.message, 'error');
                        }
                    });
                }
            });
        },

        downloadZip() {
            const downloadUrl = `download_zip.php?path=${encodeURIComponent(this.currentPath)}`;
            window.location.href = downloadUrl;
        },

        previewFile(item) {
            window.open(item.url, '_blank');
        },

        /* --- Drag and Drop Move Logic --- */
        dragStart(event, item) {
            this.draggingItem = item;
            event.dataTransfer.effectAllowed = 'move';
        },
        dragEnd(event) {
            this.draggingItem = null;
            this.dropTarget = null;
        },
        dragOverFolder(event, item) {
            // Only highlight if it's a folder and not the same item being dragged
            if (this.draggingItem && this.draggingItem.path !== item.path && item.type === 'folder') {
                this.dropTarget = item;
            }
        },
        dragLeaveFolder(event, item) {
            if (this.dropTarget === item) {
                this.dropTarget = null;
            }
        },
        dropOnFolder(event, item) {
            if (this.draggingItem && this.draggingItem.path !== item.path && item.type === 'folder') {
                this.moveItem(this.draggingItem, item);
            }
            this.dropTarget = null;
            this.draggingItem = null;
        },
        dropOnParent() {
            if (this.draggingItem) {
                let parts = this.currentPath.split('/');
                parts.pop();
                let parentPath = parts.join('/');
                this.moveItem(this.draggingItem, { path: parentPath });
            }
            this.dropTarget = null;
            this.draggingItem = null;
        },
        moveItem(sourceItem, targetFolder) {
            fetch('file_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'move', source: sourceItem.path, destination: targetFolder.path })
            })
            .then(r => r.json())
            .then(d => {
                if(d.success) {
                    this.loadPath(this.currentPath);
                    const Toast = Swal.mixin({
                        toast: true, position: 'top-end', showConfirmButton: false, timer: 1500
                    });
                    Toast.fire({ icon: 'success', title: 'ย้ายการจัดเก็บสำเร็จ!' });
                } else {
                    Swal.fire('Error', d.message, 'error');
                }
            });
        },

        /* --- Upload Logic --- */
        triggerUpload() {
            document.getElementById('fileInput').click();
        },

        uploadFiles(event) {
            const files = Array.from(event.target.files);
            if(files.length === 0) return;
            this.processUploads(files);
            event.target.value = ''; // Reset input
        },

        handleDrop(event) {
            this.dragover = false;
            const files = event.dataTransfer.files;
            if(files.length > 0) {
                this.processUploads(files);
            }
        },

        async processUploads(files) {
            // Show loading
            Swal.fire({
                title: 'Uploading...',
                html: 'Please wait while we upload and optimize your files.',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            let uploadedCount = 0;
            let errors = [];

            for (let i = 0; i < files.length; i++) {
                const formData = new FormData();
                formData.append('action', 'upload');
                formData.append('path', this.currentPath);
                formData.append('file', files[i]);

                try {
                    const response = await fetch('file_api.php', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();
                    if(result.success) {
                        uploadedCount++;
                    } else {
                        errors.push(`${files[i].name}: ${result.message}`);
                    }
                } catch (e) {
                    errors.push(`${files[i].name}: Upload failed`);
                }
            }

            Swal.close();
            this.loadPath(this.currentPath); // Refresh list

            if(errors.length > 0) {
                Swal.fire('Upload Completed with Errors', errors.join('<br>'), 'warning');
            } else {
                const Toast = Swal.mixin({
                    toast: true, position: 'top-end', showConfirmButton: false, timer: 2000
                });
                Toast.fire({ icon: 'success', title: `Uploaded ${uploadedCount} files` });
            }
        },

        copyLink(item) {
            // Use full_url if available, otherwise construct it
            const link = item.full_url || (window.location.origin + item.url);
            navigator.clipboard.writeText(link).then(() => {
                const Toast = Swal.mixin({
                    toast: true, position: 'top-end', showConfirmButton: false, timer: 1500
                });
                Toast.fire({ icon: 'success', title: 'Link copied!' });
            }).catch(err => {
                console.error('Failed to copy: ', err);
            });
        }

    }));
});
</script>

<?php
$content = ob_get_clean();
include 'dashboard.php';
?>
