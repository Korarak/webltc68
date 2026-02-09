<?php
include 'middleware.php';
include '../condb/condb.php'; // Include here to fetch initial data if needed, though we primarily use API

// Fetch initial data for SSR/fast load (optional, but good for SEO/Performance)
// We can also just fetch everything via API on mount, but let's pass initial data to Alpine for instant render
function getTableData($mysqli, $table, $id_field, $name_field, $order_field = null) {
    if ($order_field) {
        $query = "SELECT * FROM $table ORDER BY $order_field";
    } else {
        $query = "SELECT * FROM $table ORDER BY $name_field";
    }
    $result = $mysqli->query($query);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Special fetch for workbranch
$workbranches_query = "SELECT wb.*, d.department_name 
                       FROM workbranch wb 
                       LEFT JOIN department d ON wb.department_id = d.id 
                       ORDER BY d.department_name, wb.workbranch_name";
$workbranches = $mysqli3->query($workbranches_query)->fetch_all(MYSQLI_ASSOC);

$departments = getTableData($mysqli3, 'department', 'id', 'department_name');
$positions = getTableData($mysqli3, 'positions', 'id', 'position_name');
$position_levels = getTableData($mysqli3, 'position_level', 'id', 'level_name');
$genders = getTableData($mysqli3, 'gender', 'id', 'gender_name');
$education_levels = getTableData($mysqli3, 'education_level', 'id', 'education_name');
$worklevels = getTableData($mysqli3, 'worklevel', 'id', 'work_level_name');

// Pass PHP data to JS
$initialData = [
    'department' => $departments,
    'positions' => $positions,
    'position_level' => $position_levels,
    'gender' => $genders,
    'education_level' => $education_levels,
    'worklevel' => $worklevels,
    'workbranch' => $workbranches
];
?>

<?php ob_start(); ?>

<div x-data="personnelSettings()" class="max-w-7xl mx-auto space-y-6">
    
    <!-- Title Header -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-2xl p-6 shadow-xl text-white flex justify-between items-center transform transition hover:scale-[1.002]">
        <div>
            <h1 class="text-3xl font-bold flex items-center gap-3">
                <i class="fas fa-cogs opacity-80"></i>
                ตั้งค่าระบบบุคลากร
            </h1>
            <p class="text-indigo-100 mt-1 pl-11 text-sm font-medium opacity-90">จัดการข้อมูลพื้นฐาน: แผนก, ตำแหน่ง, วิทยฐานะ และอื่นๆ</p>
        </div>
        <div class="hidden sm:block opacity-20">
            <i class="fas fa-users-cog text-6xl"></i>
        </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-6">
        <!-- Sidebar Navigation -->
        <div class="w-full lg:w-64 flex-shrink-0 space-y-2">
            <template x-for="(tab, key) in tabs" :key="key">
                <button @click="activeTab = key"
                    :class="activeTab === key ? 'bg-white shadow-md text-blue-600 border-l-4 border-blue-600 scale-[1.02]' : 'hover:bg-white/60 text-gray-600 hover:text-gray-800 border-l-4 border-transparent'"
                    class="w-full text-left px-5 py-4 rounded-r-xl transition-all duration-200 flex items-center gap-3 font-medium">
                    <span :class="activeTab === key ? 'bg-blue-50 text-blue-600' : 'bg-gray-100 text-gray-500'" 
                          class="w-10 h-10 rounded-lg flex items-center justify-center transition-colors">
                        <i :class="tab.icon"></i>
                    </span>
                    <span x-text="tab.label"></span>
                </button>
            </template>
        </div>

        <!-- Main Content Area -->
        <div class="flex-1 min-w-0">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 min-h-[600px] relative overflow-hidden">
                
                <!-- Loading Overlay -->
                <div x-show="isLoading" class="absolute inset-0 bg-white/80 backdrop-blur-sm z-50 flex items-center justify-center" x-transition.opacity>
                    <div class="flex flex-col items-center gap-3">
                        <i class="fas fa-circle-notch fa-spin text-4xl text-blue-500"></i>
                        <span class="text-gray-500 font-medium animate-pulse">กำลังทำงาน...</span>
                    </div>
                </div>

                <!-- Content Header -->
                <div class="p-6 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-gray-50/50">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                            <i :class="currentTab.icon" class="text-blue-500"></i>
                            <span x-text="'จัดการ' + currentTab.label"></span>
                        </h2>
                        <p class="text-gray-500 text-sm mt-1" x-text="currentTab.description"></p>
                    </div>
                    <button @click="openModal()" 
                        class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl shadow-lg shadow-blue-200 transition-all active:scale-95 flex items-center gap-2 font-medium">
                        <i class="fas fa-plus"></i>
                        <span x-text="'เพิ่ม' + currentTab.itemLabel"></span>
                    </button>
                </div>

                <!-- Table Content -->
                <div class="p-0">
                     <!-- Filter Search -->
                    <div class="px-6 py-4 border-b border-gray-100 bg-white sticky top-0 z-10">
                        <div class="relative max-w-md">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input x-model="searchQuery" type="text" placeholder="ค้นหาข้อมูล..." 
                                class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all">
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 text-gray-600 text-sm font-semibold border-b border-gray-100">
                                    <th class="px-6 py-4 w-20 text-center">#</th>
                                    <template x-for="col in currentTab.columns">
                                        <th class="px-6 py-4" x-text="col.header"></th>
                                    </template>
                                    <th class="px-6 py-4 text-right w-40">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <template x-for="(item, index) in filteredItems" :key="item.id">
                                    <tr class="hover:bg-blue-50/30 transition-colors group">
                                        <td class="px-6 py-4 text-center text-gray-400 font-medium" x-text="index + 1"></td>
                                        
                                        <!-- Dynamic Columns -->
                                        <template x-for="col in currentTab.columns">
                                            <td class="px-6 py-4">
                                                <div x-text="item[col.field]" class="text-gray-700 font-medium"></div>
                                                <div x-if="col.subField" class="text-xs text-gray-400 mt-0.5" x-text="item[col.subField]"></div>
                                            </td>
                                        </template>

                                        <td class="px-6 py-4 text-right">
                                            <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <button @click="openModal(item)" class="w-8 h-8 rounded-lg bg-yellow-50 text-yellow-600 hover:bg-yellow-100 flex items-center justify-center transition">
                                                    <i class="fas fa-pen text-xs"></i>
                                                </button>
                                                <button @click="deleteItem(item)" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 flex items-center justify-center transition">
                                                    <i class="fas fa-trash text-xs"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="filteredItems.length === 0">
                                    <td colspan="100%" class="text-center py-12 text-gray-400">
                                        <i class="fas fa-inbox text-4xl mb-3 block opacity-30"></i>
                                        ไม่พบข้อมูล
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Form -->
    <div x-show="isModalOpen" style="display: none;"
        class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <!-- Overlay -->
        <div x-show="isModalOpen" x-transition.opacity
            class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="closeModal()"></div>

        <!-- Panel -->
        <div x-show="isModalOpen" x-transition
            class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
            
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-800" x-text="editMode ? 'แก้ไขข้อมูล' : 'เพิ่มข้อมูลใหม่'"></h3>
                    <button @click="closeModal()" class="text-gray-400 hover:text-gray-600 transition">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form @submit.prevent="saveItem" class="p-6 space-y-5">
                    
                    <!-- Dynamic Fields -->
                    <template x-for="field in currentTab.fields">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5" x-text="field.label"></label>
                            
                            <!-- Text Input -->
                            <template x-if="field.type === 'text'">
                                <input type="text" x-model="formData[field.key]" 
                                    class="w-full px-4 py-2.5 rounded-xl border-gray-200 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-all font-medium text-gray-700"
                                    required>
                            </template>

                            <!-- Select Input (e.g. for workbranch -> department) -->
                            <template x-if="field.type === 'select'">
                                <div class="relative">
                                    <select x-model="formData[field.key]" 
                                        class="w-full px-4 py-2.5 rounded-xl border-gray-200 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-all font-medium text-gray-700 appearance-none"
                                        required>
                                        <option value="">-- เลือกรายการ --</option>
                                        <template x-for="opt in getOptions(field.source)" :key="opt.id">
                                            <option :value="opt.id" x-text="opt.name"></option>
                                        </template>
                                    </select>
                                    <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                                </div>
                            </template>
                        </div>
                    </template>

                    <div class="pt-2 flex gap-3">
                        <button type="button" @click="closeModal()" class="flex-1 px-4 py-2.5 rounded-xl border border-gray-200 text-gray-600 font-semibold hover:bg-gray-50 transition active:scale-95">ยกเลิก</button>
                        <button type="submit" class="flex-1 px-4 py-2.5 rounded-xl bg-blue-600 text-white font-semibold hover:bg-blue-700 shadow-lg shadow-blue-200 transition active:scale-95">บันทึกข้อมูล</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('personnelSettings', () => ({
        activeTab: 'department',
        searchQuery: '',
        isModalOpen: false,
        isLoading: false,
        editMode: false,
        // Initial Data from PHP
        data: <?= json_encode($initialData) ?>,
        formData: {},

        tabs: {
            'department': {
                label: 'แผนก/ฝ่าย',
                itemLabel: 'แผนก',
                description: 'จัดการโครงสร้างแผนกและฝ่ายงานภายในองค์กร',
                icon: 'fas fa-building',
                table: 'department',
                columns: [{ header: 'ชื่อแผนก/ฝ่าย', field: 'department_name' }],
                fields: [{ label: 'ชื่อแผนก/ฝ่าย', key: 'department_name', type: 'text' }]
            },
            'positions': {
                label: 'ตำแหน่งงาน',
                itemLabel: 'ตำแหน่ง',
                description: 'กำหนดรายชื่อตำแหน่งงานทั้งหมด',
                icon: 'fas fa-briefcase',
                table: 'positions',
                columns: [{ header: 'ชื่อตำแหน่ง', field: 'position_name' }],
                fields: [{ label: 'ชื่อตำแหน่ง', key: 'position_name', type: 'text' }]
            },
            'position_level': {
                label: 'ระดับวิทยฐานะ',
                itemLabel: 'วิทยฐานะ',
                description: 'ระดับความเชี่ยวชาญหรือวิทยฐานะ',
                icon: 'fas fa-award',
                table: 'position_level',
                columns: [{ header: 'ชื่อระดับ', field: 'level_name' }],
                fields: [{ label: 'ชื่อระดับ', key: 'level_name', type: 'text' }]
            },
            'worklevel': {
                label: 'ระดับงาน',
                itemLabel: 'ระดับงาน',
                description: 'แบ่งระดับการปฏิบัติงาน',
                icon: 'fas fa-layer-group',
                table: 'worklevel',
                columns: [{ header: 'ชื่อระดับงาน', field: 'work_level_name' }],
                fields: [{ label: 'ชื่อระดับงาน', key: 'work_level_name', type: 'text' }]
            },
            'workbranch': { // Complex one with FK
                label: 'สาขางาน',
                itemLabel: 'สาขางาน',
                description: 'สาขาหรือกลุ่มงานย่อยภายใต้แผนก',
                icon: 'fas fa-code-branch',
                table: 'workbranch',
                columns: [
                    { header: 'ชื่อสาขางาน', field: 'workbranch_name' },
                    { header: 'สังกัดแผนก', field: 'department_name', subField: null }
                ],
                fields: [
                    { label: 'ชื่อสาขางาน', key: 'workbranch_name', type: 'text' },
                    { label: 'สังกัดแผนก', key: 'department_id', type: 'select', source: 'department' }
                ]
            },
            'education_level': {
                label: 'ระดับการศึกษา',
                itemLabel: 'ระดับ',
                description: 'วุฒิการศึกษาตามระดับ (ตรี, โท, เอก...)',
                icon: 'fas fa-graduation-cap',
                table: 'education_level',
                columns: [{ header: 'ระดับการศึกษา', field: 'education_name' }],
                fields: [{ label: 'ระดับการศึกษา', key: 'education_name', type: 'text' }]
            },
            'gender': {
                label: 'คำนำหน้า/เพศ',
                itemLabel: 'รายการ',
                description: 'ข้อมูลเพศและคำนำหน้าชื่อ',
                icon: 'fas fa-venus-mars',
                table: 'gender',
                columns: [{ header: 'ชื่อเพศ/คำนำหน้า', field: 'gender_name' }],
                fields: [{ label: 'ชื่อเพศ/คำนำหน้า', key: 'gender_name', type: 'text' }]
            }
        },

        get currentTab() {
            return this.tabs[this.activeTab];
        },

        get filteredItems() {
            const items = this.data[this.activeTab] || [];
            if (!this.searchQuery) return items;
            
            const lowerQuery = this.searchQuery.toLowerCase();
            return items.filter(item => {
                return Object.values(item).some(val => 
                    String(val).toLowerCase().includes(lowerQuery)
                );
            });
        },

        getOptions(source) {
            // Helper for select dropdowns to create standard id/name pair
            if (source === 'department') {
                return this.data.department.map(d => ({ id: d.id, name: d.department_name }));
            }
            return [];
        },

        openModal(item = null) {
            this.editMode = !!item;
            this.formData = item ? { ...item } : {};
            this.isModalOpen = true;
        },

        closeModal() {
            this.isModalOpen = false;
            this.formData = {};
        },

        async saveItem() {
            this.isLoading = true;
            try {
                const formData = new FormData();
                formData.append('action', this.editMode ? 'edit' : 'add');
                formData.append('table', this.currentTab.table);
                
                // Append all fields
                for (const key in this.formData) {
                    formData.append(key, this.formData[key]);
                }

                const response = await fetch('api/setting_api.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    await Swal.fire({
                        icon: 'success',
                        title: 'สำเร็จ!',
                        text: result.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    this.closeModal();
                    // Refetch data for this tab (Simple Approach: Reload Page or handle manually. 
                    // ideally we update local state for SPA feel, but reload is safer for sync)
                    location.reload(); 
                } else {
                    throw new Error(result.message);
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'ผิดพลาด',
                    text: error.message
                });
            } finally {
                this.isLoading = false;
            }
        },

        async deleteItem(item) {
            const result = await Swal.fire({
                title: 'ยืนยันการลบ?',
                text: "ข้อมูลจะถูกลบถาวรและไม่สามารถกู้คืนได้",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#d1d5db',
                confirmButtonText: 'ใช่, ลบเลย!',
                cancelButtonText: 'ยกเลิก',
                reverseButtons: true
            });

            if (result.isConfirmed) {
                this.isLoading = true;
                try {
                     const formData = new FormData();
                    formData.append('action', 'delete');
                    formData.append('table', this.currentTab.table);
                    formData.append('id', item.id);

                    const response = await fetch('api/setting_api.php', {
                        method: 'POST',
                        body: formData
                    });

                    const res = await response.json();

                    if (res.success) {
                        await Swal.fire({
                            icon: 'success',
                            title: 'ลบสำเร็จ',
                            timer: 1500,
                            showConfirmButton: false
                        });
                        location.reload();
                    } else {
                        throw new Error(res.message);
                    }
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'ผิดพลาด',
                        text: error.message
                    });
                } finally {
                    this.isLoading = false;
                }
            }
        }
    }));
});
</script>

<?php
$content = ob_get_clean();
include 'dashboard.php';
?>