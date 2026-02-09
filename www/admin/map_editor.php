<?php
include 'middleware.php';
require_once '../config.php';

// Fetch existing map data (assuming 1 main map for now, ID=1)
$map_json = '{}';
$bg_image = '';
$result = $mysqli->query("SELECT * FROM sys_maps WHERE id = 1");
if ($result && $row = $result->fetch_assoc()) {
    $map_json = $row['map_json'] ?: '{}';
    $bg_image = $row['background_image'];
}
?>
<?php ob_start(); ?>

<div class="animate-fade-in-up h-[calc(100vh-140px)] flex flex-col">
    <div class="flex justify-between items-center mb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">เครื่องมือสร้างแผนผัง (Map Editor)</h1>
            <p class="text-gray-500 text-sm">วาดและกำหนดจุดต่างๆ บนแผนที่</p>
        </div>
        <div class="flex gap-2">
            <button onclick="saveMap()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg shadow-sm transition-colors flex items-center gap-2">
                <i class="fas fa-save"></i> บันทึกแผนผัง
            </button>
        </div>
    </div>

    <div class="flex flex-1 gap-4 overflow-hidden">
        <!-- Toolbar -->
        <div class="w-20 flex flex-col gap-2 bg-white rounded-xl shadow-sm border border-gray-100 p-2">
            <button onclick="addRect()" class="w-full aspect-square rounded-lg bg-gray-50 hover:bg-blue-50 text-gray-600 hover:text-blue-600 flex flex-col items-center justify-center gap-1 transition-colors" title="เพิ่มสี่เหลี่ยม">
                <i class="far fa-square text-xl"></i>
                <span class="text-[10px]">Rect</span>
            </button>
            <button onclick="addCircle()" class="w-full aspect-square rounded-lg bg-gray-50 hover:bg-blue-50 text-gray-600 hover:text-blue-600 flex flex-col items-center justify-center gap-1 transition-colors" title="เพิ่มวงกลม">
                <i class="far fa-circle text-xl"></i>
                <span class="text-[10px]">Circle</span>
            </button>
            <button onclick="addTriangle()" class="w-full aspect-square rounded-lg bg-gray-50 hover:bg-blue-50 text-gray-600 hover:text-blue-600 flex flex-col items-center justify-center gap-1 transition-colors" title="เพิ่มสามเหลี่ยม">
                <i class="fas fa-play text-xl -rotate-90"></i>
                <span class="text-[10px]">Tri</span>
            </button>
            <button onclick="startPolygon()" class="w-full aspect-square rounded-lg bg-gray-50 hover:bg-blue-50 text-gray-600 hover:text-blue-600 flex flex-col items-center justify-center gap-1 transition-colors" title="วาดรูปทรงอิสระ">
                <i class="fas fa-draw-polygon text-xl"></i>
                <span class="text-[10px]">Poly</span>
            </button>
            <button onclick="addText()" class="w-full aspect-square rounded-lg bg-gray-50 hover:bg-blue-50 text-gray-600 hover:text-blue-600 flex flex-col items-center justify-center gap-1 transition-colors" title="เพิ่มข้อความ">
                <i class="fas fa-font text-xl"></i>
                <span class="text-[10px]">Text</span>
            </button>
            <div class="h-px bg-gray-200 my-1"></div>
            <button onclick="deleteSelected()" class="w-full aspect-square rounded-lg bg-red-50 hover:bg-red-100 text-red-500 hover:text-red-600 flex flex-col items-center justify-center gap-1 transition-colors" title="ลบที่เลือก">
                <i class="fas fa-trash-alt text-xl"></i>
                <span class="text-[10px]">Del</span>
            </button>
        </div>

        <!-- Canvas Area -->
        <div class="flex-1 bg-gray-200 rounded-xl shadow-inner border border-gray-300 overflow-auto relative flex justify-center items-center p-4">
            <canvas id="c"></canvas>
        </div>

        <!-- Properties Panel -->
        <div class="w-72 bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex flex-col gap-4 overflow-y-auto">
            <h3 class="font-bold text-gray-700 border-b pb-2">คุณสมบัติ (Properties)</h3>
            
            <div id="no-selection" class="text-center py-8 text-gray-400">
                <i class="fas fa-mouse-pointer text-3xl mb-2"></i>
                <p>คลิกเลือกวัตถุเพื่อแก้ไข</p>
            </div>

            <div id="prop-form" class="hidden flex flex-col gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ชื่ออาคาร / ป้ายกำกับ</label>
                    <input type="text" id="prop-label" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">สีพื้นหลัง</label>
                    <input type="color" id="prop-color" class="w-full h-10 rounded-lg cursor-pointer border border-gray-300 p-1">
                </div>
                <div>
                     <label class="block text-sm font-medium text-gray-700 mb-1">สีเส้นขอบ (Stroke) & ขนาด</label>
                     <div class="flex gap-2">
                         <input type="color" id="prop-stroke" class="h-10 w-1/3 rounded-lg cursor-pointer border border-gray-300 p-1" title="Stroke Color">
                         <input type="number" id="prop-stroke-width" min="0" max="20" class="flex-1 px-3 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Width">
                     </div>
                </div>
                <div class="flex items-center gap-2">
                     <input type="checkbox" id="prop-show-search" class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                     <label for="prop-show-search" class="text-sm font-medium text-gray-700">แสดงในรายการค้นหา</label>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">รายละเอียด (Description)</label>
                    <textarea id="prop-desc" rows="4" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Opacity</label>
                    <input type="range" id="prop-opacity" min="0" max="1" step="0.1" class="w-full">
                </div>
            </div>

            <div class="mt-auto border-t pt-4">
                <h4 class="font-bold text-gray-700 text-sm mb-2">อัปโหลดพื้นหลัง</h4>
                <form id="bg-upload-form" enctype="multipart/form-data">
                    <input type="file" name="bg_image" id="bg_input" class="text-xs w-full mb-2" accept="image/*">
                    <div class="flex gap-2">
                        <button type="button" onclick="uploadBg()" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 rounded-lg text-sm transition-colors">อัปโหลด</button>
                        <button type="button" onclick="removeBg()" class="bg-red-50 hover:bg-red-100 text-red-600 px-3 py-2 rounded-lg text-sm transition-colors" title="ลบพื้นหลัง">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Fabric.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1/fabric.min.js"></script>
<script>
    // Initialize Canvas
    const canvas = new fabric.Canvas('c', {
        width: 2000, // Default size
        height: 800,
        backgroundColor: '#fff'
    });

    // Load Data
    const savedJSON = <?php echo $map_json ?: '{}'; ?>;
    const savedBg = '<?php echo $bg_image; ?>';

    if (savedJSON && Object.keys(savedJSON).length > 0) {
        canvas.loadFromJSON(savedJSON, canvas.renderAll.bind(canvas));
    }
    
    // Background Image
    if (savedBg) {
        setBackground('/uploads/' + savedBg);
    }

    function setBackground(url) {
        fabric.Image.fromURL(url, function(img) {
            // Resize canvas to fit image optionally, or fit image to canvas
            // Let's set canvas size to image size for better mapping
            canvas.setWidth(img.width);
            canvas.setHeight(img.height);
            canvas.setBackgroundImage(img, canvas.renderAll.bind(canvas), {
                scaleX: 1,
                scaleY: 1
            });
        });
    }

    // --- Interaction Logic ---
    const propForm = document.getElementById('prop-form');
    const noSelection = document.getElementById('no-selection');
    const labelInput = document.getElementById('prop-label');
    const colorInput = document.getElementById('prop-color');
    const strokeInput = document.getElementById('prop-stroke');
    const strokeWidthInput = document.getElementById('prop-stroke-width');
    const showSearchInput = document.getElementById('prop-show-search');
    const descInput = document.getElementById('prop-desc');
    const opacityInput = document.getElementById('prop-opacity');

    let activeObj = null;

    canvas.on('selection:created', updateProps);
    canvas.on('selection:updated', updateProps);
    canvas.on('selection:cleared', () => {
        activeObj = null;
        propForm.classList.add('hidden');
        noSelection.classList.remove('hidden');
    });

    function updateProps(e) {
        activeObj = e.selected[0];
        if (!activeObj) return;

        propForm.classList.remove('hidden');
        noSelection.classList.add('hidden');

        // Load values from object custom properties
        labelInput.value = activeObj.label || '';
        descInput.value = activeObj.description || '';
        colorInput.value = activeObj.fill || '#cccccc';
        strokeInput.value = activeObj.stroke || '#000000';
        strokeWidthInput.value = activeObj.strokeWidth || 0;
        showSearchInput.checked = activeObj.showInSearch !== false; // Default true
        opacityInput.value = activeObj.opacity || 1;
    }

    // --- Property Change Listeners ---
    labelInput.addEventListener('input', () => {
        if (activeObj) {
            activeObj.set('label', labelInput.value);
            canvas.requestRenderAll();
        }
    });

    descInput.addEventListener('input', () => {
        if (activeObj) {
            activeObj.set('description', descInput.value);
        }
    });

    colorInput.addEventListener('input', () => {
        if (activeObj) {
            activeObj.set('fill', colorInput.value);
            canvas.requestRenderAll();
        }
    });

    strokeInput.addEventListener('input', () => {
        if (activeObj) {
            activeObj.set('stroke', strokeInput.value);
            canvas.requestRenderAll();
        }
    });

    strokeWidthInput.addEventListener('input', () => {
        if (activeObj) {
            activeObj.set('strokeWidth', parseInt(strokeWidthInput.value, 10));
            canvas.requestRenderAll();
        }
    });

    showSearchInput.addEventListener('change', () => {
        if (activeObj) {
            activeObj.set('showInSearch', showSearchInput.checked);
        }
    });
    
    opacityInput.addEventListener('input', () => {
        if (activeObj) {
            activeObj.set('opacity', parseFloat(opacityInput.value));
            canvas.requestRenderAll();
        }
    });

    // --- Toolbar Functions ---
    function addRect() {
        const rect = new fabric.Rect({
            left: 100,
            top: 100,
            fill: 'rgba(59, 130, 246, 0.5)',
            width: 100,
            height: 100,
            stroke: 'blue',
            strokeWidth: 2,
            label: 'อาคารใหม่',
            description: ''
        });
        canvas.add(rect);
        canvas.setActiveObject(rect);
    }

    function addCircle() {
        const circle = new fabric.Circle({
            left: 150,
            top: 150,
            radius: 50,
            fill: 'rgba(16, 185, 129, 0.5)', // Green
            stroke: 'green',
            strokeWidth: 2,
            label: 'วงกลม',
            description: ''
        });
        canvas.add(circle);
        canvas.setActiveObject(circle);
    }

    function addTriangle() {
        const triangle = new fabric.Triangle({
            left: 200,
            top: 200,
            width: 100,
            height: 100,
            fill: 'rgba(245, 158, 11, 0.5)', // Orange
            stroke: 'orange',
            strokeWidth: 2,
            label: 'สามเหลี่ยม',
            description: ''
        });
        canvas.add(triangle);
        canvas.setActiveObject(triangle);
    }

    // --- Polygon Drawing Mode ---
    let isDrawingPoly = false;
    let polyPoints = [];
    let activeLine = null;
    let activeShape = null;

    function startPolygon() {
        isDrawingPoly = true;
        polyPoints = [];
        canvas.discardActiveObject();
        canvas.requestRenderAll();
        canvas.defaultCursor = 'crosshair';
        canvas.selection = false; // Disable group selection while drawing
        
        // Notify user (Optional: Toast or Alert)
        // alert('Click to add points. Double Click to finish.');
    }

    function finishPolygon() {
        isDrawingPoly = false;
        canvas.defaultCursor = 'default';
        canvas.selection = true;

        // Clear temp lines
        canvas.getObjects('line').forEach(l => {
            if (l.isTemp) canvas.remove(l);
        });

        // Create Final Polygon
        if (polyPoints.length > 2) {
            const polygon = new fabric.Polygon(polyPoints, {
                fill: 'rgba(139, 92, 246, 0.5)', // Purple
                stroke: 'purple',
                strokeWidth: 2,
                label: 'พื้นที่',
                description: '',
                objectCaching: false
            });
            canvas.add(polygon);
            canvas.setActiveObject(polygon);
        }
        
        polyPoints = [];
    }

    // Canvas Events for Polygon
    canvas.on('mouse:down', function(options) {
        if (!isDrawingPoly) return;
        
        const pointer = canvas.getPointer(options.e);
        polyPoints.push({ x: pointer.x, y: pointer.y });
        
        // Draw little circle at point
        const point = new fabric.Circle({
            left: pointer.x,
            top: pointer.y,
            radius: 3,
            fill: 'red',
            originX: 'center',
            originY: 'center',
            selectable: false,
            evented: false,
            isTemp: true
        });
        canvas.add(point);

        // Draw Line
        if (polyPoints.length > 1) {
            const start = polyPoints[polyPoints.length - 2];
            const end = polyPoints[polyPoints.length - 1];
            const line = new fabric.Line([start.x, start.y, end.x, end.y], {
                stroke: 'red',
                strokeWidth: 1,
                selectable: false,
                evented: false,
                isTemp: true
            });
            canvas.add(line);
        }
    });

    canvas.on('mouse:dblclick', function(options) {
        if (isDrawingPoly) {
            finishPolygon();
        }
    });

    function addText() {
        const text = new fabric.IText('ข้อความ', {
            left: 100,
            top: 100,
            fontFamily: 'Sarabun',
            fill: '#333',
            fontSize: 20
        });
        canvas.add(text);
        canvas.setActiveObject(text);
    }

    function deleteSelected() {
        const active = canvas.getActiveObjects();
        if (active.length) {
            canvas.discardActiveObject();
            active.forEach(obj => canvas.remove(obj));
        }
    }

    // --- Save & Upload ---
    function saveMap() {
        const json = JSON.stringify(canvas.toJSON(['label', 'description', 'showInSearch'])); // Include custom properties
        
        fetch('map_save.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'map_json=' + encodeURIComponent(json)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) alert('บันทึกเรียบร้อย');
            else alert('เกิดข้อผิดพลาด: ' + data.message);
        });
    }

    function uploadBg() {
        const formData = new FormData(document.getElementById('bg-upload-form'));
        fetch('map_upload_bg.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                setBackground('/uploads/' + data.filename);
                // Trigger save to update background_image column via reload or just imply it?
                // The PHP upload script updates the DB.
            } else {
                alert('Upload failed');
            }
        });
    }

    function removeBg() {
        if (!confirm('ต้องการลบภาพพื้นหลังใช่หรือไม่?')) return;
        
        fetch('map_remove_bg.php', { method: 'POST' })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                canvas.setBackgroundImage(null, canvas.renderAll.bind(canvas));
                canvas.backgroundColor = '#fff';
                document.getElementById('bg_input').value = '';
                alert('ลบพื้นหลังเรียบร้อย');
            } else {
                alert('เกิดข้อผิดพลาด: ' + data.message);
            }
        });
    }

    // Keyboard Delete
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Delete' || e.key === 'Backspace') {
            // Only if not typing in input
            if (document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
                deleteSelected();
            }
        }
    });

</script>

<style>
    .animate-fade-in-up { animation: fadeInUp 0.5s ease-out; }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<?php
$content = ob_get_clean();
include 'dashboard.php';
?>
