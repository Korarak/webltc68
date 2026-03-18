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
        <div class="flex-1 bg-gray-200 rounded-xl shadow-inner border border-gray-300 overflow-hidden relative flex justify-center items-center p-4">
            <canvas id="c"></canvas>
            
            <!-- Zoom Controls -->
            <div class="absolute bottom-4 right-4 flex flex-col gap-2">
                <div class="bg-white/90 backdrop-blur px-3 py-1 rounded text-xs text-gray-500 mb-1 shadow-sm border border-gray-100">
                    Alt + Drag to Pan
                </div>
                <div class="flex flex-col bg-white rounded-lg shadow-md border border-gray-100 overflow-hidden">
                    <button onclick="zoomIn()" class="p-2 hover:bg-gray-50 text-gray-600 hover:text-blue-600 transition-colors border-b border-gray-100" title="Zoom In">
                        <i class="fas fa-plus"></i>
                    </button>
                    <button onclick="zoomOut()" class="p-2 hover:bg-gray-50 text-gray-600 hover:text-blue-600 transition-colors border-b border-gray-100" title="Zoom Out">
                        <i class="fas fa-minus"></i>
                    </button>
                    <button onclick="resetZoom()" class="p-2 hover:bg-gray-50 text-gray-600 hover:text-blue-600 transition-colors" title="Reset View">
                        <i class="fas fa-compress"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Properties Panel -->
        <div class="w-72 bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex flex-col gap-4 overflow-y-auto">
            <h3 class="font-bold text-gray-700 border-b pb-2">คุณสมบัติ (Properties)</h3>
            
            <div id="no-selection" class="flex flex-col gap-4">
                <div class="text-center py-4 text-gray-400 border-b">
                    <i class="fas fa-map text-3xl mb-2"></i>
                    <p class="text-sm">การตั้งค่าแผนผัง (Global)</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ความกว้าง (Width)</label>
                    <input type="number" id="map-width" class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" value="2000">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ความสูง (Height)</label>
                    <input type="number" id="map-height" class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" value="800">
                </div>
                <div class="text-xs text-gray-400 mt-2">
                    * คลิกที่วัตถุเพื่อแก้ไขคุณสมบัติของวัตถุนั้น
                </div>
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

    // Map Settings Inputs
    const mapWidthInput = document.getElementById('map-width');
    const mapHeightInput = document.getElementById('map-height');

    // Load Data
    const savedJSON = <?php echo $map_json ?: '{}'; ?>;
    const savedBg = '<?php echo $bg_image; ?>';

    if (savedJSON && Object.keys(savedJSON).length > 0) {
        canvas.loadFromJSON(savedJSON, () => {
             canvas.renderAll();
             // Restore Canvas Size if saved
             if (savedJSON.width) {
                 canvas.setWidth(savedJSON.width);
                 mapWidthInput.value = savedJSON.width;
             }
             if (savedJSON.height) {
                 canvas.setHeight(savedJSON.height);
                 mapHeightInput.value = savedJSON.height;
             }
        });
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
            
            // Update inputs
            mapWidthInput.value = img.width;
            mapHeightInput.value = img.height;

            canvas.setBackgroundImage(img, canvas.renderAll.bind(canvas), {
                scaleX: 1,
                scaleY: 1
            });
        });
    }

    // --- Drawing Functions ---
    function setupNewObject(obj) {
        canvas.add(obj);
        canvas.setActiveObject(obj);
        canvas.requestRenderAll();
    }

    function addRect() {
        setupNewObject(new fabric.Rect({
            left: 100, top: 100, width: 100, height: 100,
            fill: '#cccccc', stroke: '#000000', strokeWidth: 2,
            opacity: 0.8
        }));
    }

    function addCircle() {
        setupNewObject(new fabric.Circle({
            left: 100, top: 100, radius: 50,
            fill: '#cccccc', stroke: '#000000', strokeWidth: 2,
            opacity: 0.8
        }));
    }

    function addTriangle() {
        setupNewObject(new fabric.Triangle({
            left: 100, top: 100, width: 100, height: 100,
            fill: '#cccccc', stroke: '#000000', strokeWidth: 2,
            opacity: 0.8
        }));
    }

    function addText() {
        setupNewObject(new fabric.IText('ข้อความ', {
            left: 100, top: 100,
            fontFamily: 'sans-serif',
            fontSize: 24, fill: '#000000'
        }));
    }

    function deleteSelected() {
        const activeObjects = canvas.getActiveObjects();
        if (activeObjects.length) {
            canvas.discardActiveObject();
            activeObjects.forEach(function(object) {
                canvas.remove(object);
            });
        }
    }

    let isDrawingPoly = false;
    let polyPoints = [];
    let lineArray = [];
    let activeLine = null;

    function startPolygon() {
        if (isDrawingPoly) {
            finishPolygon();
            return;
        }
        isDrawingPoly = true;
        polyPoints = [];
        lineArray = [];
        activeLine = null;
        canvas.selection = false;
        canvas.defaultCursor = 'crosshair';
        canvas.on('mouse:down', onPolyMouseDown);
        canvas.on('mouse:move', onPolyMouseMove);
    }

    function onPolyMouseDown(o) {
        if (!isDrawingPoly) return;
        
        let pointer = canvas.getPointer(o.e);
        
        // If double click or click near start point, finish polygon
        if (polyPoints.length > 2) {
            let startPt = polyPoints[0];
            if (Math.abs(startPt.x - pointer.x) < 15 && Math.abs(startPt.y - pointer.y) < 15) {
                finishPolygon();
                return;
            }
        }
        
        polyPoints.push({x: pointer.x, y: pointer.y});
        
        let points = [pointer.x, pointer.y, pointer.x, pointer.y];
        activeLine = new fabric.Line(points, {
            strokeWidth: 2,
            fill: '#999999',
            stroke: '#999999',
            originX: 'center',
            originY: 'center',
            selectable: false,
            evented: false
        });
        lineArray.push(activeLine);
        
        let circle = new fabric.Circle({
            radius: 4,
            fill: '#ffffff',
            stroke: '#333333',
            strokeWidth: 1,
            left: pointer.x,
            top: pointer.y,
            originX: 'center',
            originY: 'center',
            selectable: false,
            evented: false
        });
        lineArray.push(circle);
        
        canvas.add(activeLine);
        canvas.add(circle);
    }

    function onPolyMouseMove(o) {
        if (!isDrawingPoly || !activeLine) return;
        let pointer = canvas.getPointer(o.e);
        activeLine.set({ x2: pointer.x, y2: pointer.y });
        canvas.requestRenderAll();
    }

    function finishPolygon() {
        isDrawingPoly = false;
        canvas.selection = true;
        canvas.defaultCursor = 'default';
        canvas.off('mouse:down', onPolyMouseDown);
        canvas.off('mouse:move', onPolyMouseMove);
        
        lineArray.forEach(function(l) {
            canvas.remove(l);
        });
        lineArray = [];
        
        if (polyPoints.length > 2) {
            // Need to remove the last line connecting to cursor
            let polygon = new fabric.Polygon(polyPoints, {
                fill: '#cccccc', stroke: '#000000', strokeWidth: 2,
                opacity: 0.8
            });
            setupNewObject(polygon);
        }
    }

    // --- Interaction Logic ---
    const propForm = document.getElementById('prop-form');
    const noSelection = document.getElementById('no-selection');
    
    // ... (Object Props refs) ...
    const labelInput = document.getElementById('prop-label');
    const colorInput = document.getElementById('prop-color');
    const strokeInput = document.getElementById('prop-stroke');
    const strokeWidthInput = document.getElementById('prop-stroke-width');
    const showSearchInput = document.getElementById('prop-show-search');
    const descInput = document.getElementById('prop-desc');
    const opacityInput = document.getElementById('prop-opacity');

    // --- Guide Border ---
    let guideBorder = null;

    function updateGuideBorder() {
        if (guideBorder) {
            canvas.remove(guideBorder);
        }
        
        guideBorder = new fabric.Rect({
            left: 0,
            top: 0,
            width: canvas.getWidth() - 2, // Inset slightly
            height: canvas.getHeight() - 2,
            fill: 'transparent',
            stroke: '#9ca3af', // Gray-400
            strokeWidth: 2,
            strokeDashArray: [10, 10],
            selectable: false,
            evented: false,
            isGuide: true,
            excludeFromExport: true 
        });
        
        canvas.add(guideBorder);
        canvas.sendToBack(guideBorder); // Or bring to front? Front is better to see cut-off.
        canvas.bringToFront(guideBorder);
    }
    
    // Init Guide
    updateGuideBorder();

    // Update listeners
    mapWidthInput.addEventListener('change', () => {
        const w = parseInt(mapWidthInput.value, 10);
        if(w > 0) {
            canvas.setWidth(w);
            updateGuideBorder();
        }
    });

    mapHeightInput.addEventListener('change', () => {
        const h = parseInt(mapHeightInput.value, 10);
        if(h > 0) {
            canvas.setHeight(h);
            updateGuideBorder();
        }
    });

    // ... (Active Object Logic) ...

    let activeObj = null;

    canvas.on('selection:created', updateProps);
    canvas.on('selection:updated', updateProps);
    canvas.on('selection:cleared', () => {
        activeObj = null;
        propForm.classList.add('hidden');
        noSelection.classList.remove('hidden');
    });

    function updateProps(e) {
        // Skip guide object
        if (e.selected[0] && e.selected[0].isGuide) {
            canvas.discardActiveObject();
            return;
        }

        activeObj = e.selected[0];
        if (!activeObj) return;

        propForm.classList.remove('hidden');
        noSelection.classList.add('hidden');

        // Load values from object custom properties
        if (activeObj.type === 'i-text' || activeObj.type === 'text') {
            labelInput.value = activeObj.text || '';
        } else {
            labelInput.value = activeObj.label || '';
        }
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
            if (activeObj.type === 'i-text' || activeObj.type === 'text') {
                activeObj.set('text', labelInput.value);
            } else {
                activeObj.set('label', labelInput.value);
            }
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

    // ...

    // --- Save & Upload ---
    function saveMap() {
        // Remove guide before saving or filter it out?
        // Filtering is safer
        const jsonObj = canvas.toJSON(['label', 'description', 'showInSearch', 'isGuide']);
        
        // Filter out the guide object
        if (jsonObj.objects) {
            jsonObj.objects = jsonObj.objects.filter(o => !o.isGuide);
        }

        // Append Canvas Size
        jsonObj.width = canvas.getWidth();
        jsonObj.height = canvas.getHeight();
        
        const json = JSON.stringify(jsonObj); 
        
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
                if (activeObj && activeObj.isEditing) return; // Prevent deleting object while typing
                deleteSelected();
            }
        }
    });

    // --- Zoom & Pan ---
    
    function zoomIn() {
        let zoom = canvas.getZoom();
        zoom *= 1.1;
        if (zoom > 5) zoom = 5;
        canvas.zoomToPoint({ x: canvas.width / 2, y: canvas.height / 2 }, zoom);
    }

    function zoomOut() {
        let zoom = canvas.getZoom();
        zoom /= 1.1;
        if (zoom < 0.1) zoom = 0.1;
        canvas.zoomToPoint({ x: canvas.width / 2, y: canvas.height / 2 }, zoom);
    }

    function resetZoom() {
        canvas.setZoom(1);
        canvas.viewportTransform = [1,0,0,1,0,0]; 
    }

    canvas.on('mouse:wheel', function(opt) {
        var delta = opt.e.deltaY;
        var zoom = canvas.getZoom();
        zoom *= 0.999 ** delta;
        if (zoom > 5) zoom = 5;
        if (zoom < 0.1) zoom = 0.1;
        canvas.zoomToPoint({ x: opt.e.offsetX, y: opt.e.offsetY }, zoom);
        opt.e.preventDefault();
        opt.e.stopPropagation();
    });

    // Panning (Alt + Drag)
    let isDragging = false;
    let lastPosX, lastPosY;

    canvas.on('mouse:down', function(opt) {
        var evt = opt.e;
        if (evt.altKey === true) {
            isDragging = true;
            canvas.selection = false;
            lastPosX = evt.clientX;
            lastPosY = evt.clientY;
        }
    });

    canvas.on('mouse:move', function(opt) {
        if (isDragging) {
            var e = opt.e;
            var vpt = canvas.viewportTransform;
            vpt[4] += e.clientX - lastPosX;
            vpt[5] += e.clientY - lastPosY;
            canvas.requestRenderAll();
            lastPosX = e.clientX;
            lastPosY = e.clientY;
        }
    });

    canvas.on('mouse:up', function(opt) {
        // on mouse up we want to recalculate new interaction
        // for all objects, so we call setViewportTransform
        if(isDragging) {
             canvas.setViewportTransform(canvas.viewportTransform);
             isDragging = false;
             canvas.selection = true;
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
