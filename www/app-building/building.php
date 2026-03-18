<?php
global $mysqli;
if (!isset($mysqli)) {
    require_once '../config.php';
}

// Fetch Map JSON
$map_json = '{}';
$bg_image = '';
$result = $mysqli->query("SELECT * FROM sys_maps WHERE id = 1");
if ($result && $row = $result->fetch_assoc()) {
    $map_json = $row['map_json'] ?: '{}';
    $bg_image = $row['background_image'];
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ผังอาคารวิทยาลัยเทคนิคเลย</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            background-color: #f8f9fa;
            min-height: 100vh;
        }

        .header-title {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 1rem 0;
            margin-bottom: 0;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .map-wrapper {
            height: 70vh; /* Fixed height for map */
            min-height: 500px;
            position: relative;
            background-color: #eef2f5;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
            border-bottom: 1px solid #ddd;
        }

        #map-container {
            width: 100%;
            height: 100%;
            cursor: grab;
            display: flex; /* Center canvas */
            justify-content: center;
            align-items: center;
        }
        
        #map-container:active {
            cursor: grabbing;
        }

        /* Controls */
        .map-controls {
            position: absolute;
            bottom: 20px;
            right: 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            z-index: 100;
        }

        @media (max-width: 768px) {
            .map-controls {
                bottom: 120px; /* Move up to avoid Tooltip/Browser bar */
                right: 15px;
            }
        }

        .btn-control {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-size: 1.2rem;
            padding: 0;
        }

        /* Tooltip */
        #tooltip {
            position: absolute;
            background: rgba(33, 37, 41, 0.95);
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 14px;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.2s;
            z-index: 1050;
            transform: translate(-50%, -120%);
            white-space: nowrap;
        }

        /* Mobile Tooltip (Fixed Bottom) */
        @media (max-width: 768px) {
            #tooltip {
                position: fixed;
                bottom: 20px;
                left: 50% !important; /* Override JS left */
                top: auto !important; /* Override JS top */
                transform: translateX(-50%); /* Center horizontally */
                width: 90%;
                max-width: 400px;
                text-align: center;
                white-space: normal;
                box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            }
        }

        /* Search Container */
        .search-container {
            position: absolute;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            width: 90%;
            max-width: 400px;
            z-index: 1000;
            background: white;
            border-radius: 50px;
            padding: 5px 15px;
        }

        /* Mobile Table Card View */
        @media (max-width: 768px) {
            #building-table table, 
            #building-table thead, 
            #building-table tbody, 
            #building-table th, 
            #building-table td, 
            #building-table tr { 
                display: block; 
                width: 100% !important;
            }
            
            #building-table thead tr { 
                position: absolute;
                top: -9999px;
                left: -9999px;
            }
            
            #building-table tr { 
                margin-bottom: 15px; 
                background: #fff;
                border-radius: 12px;
                box-shadow: 0 4px 10px rgba(0,0,0,0.05);
                padding: 15px;
                border: 1px solid #f1f3f5;
            }
            
            #building-table td { 
                border: none;
                position: relative;
                padding: 5px 0 !important;
                text-align: left !important;
            }
            
            #building-table td:first-child {
                font-size: 1.1rem;
                border-bottom: 1px solid #f8f9fa;
                margin-bottom: 10px;
                padding-bottom: 10px !important;
            }

            #building-table td:last-child {
                border-top: 1px dashed #eee;
                margin-top: 10px;
                padding-top: 15px !important;
            }

            #building-table .btn {
                width: 100%;
                display: flex;
                justify-content: center;
                align-items: center;
                padding: 10px;
            }
        }
    </style>
</head>
<body>

    <header class="header-title text-center">
        <div class="container">
            <h1 class="m-0 fw-bold">ผังอาคารวิทยาลัยเทคนิคเลย</h1>
            <small class="opacity-75">คลิก/แตะ ที่อาคารเพื่อดูรายละเอียด • เลื่อนเพื่อนำทาง</small>
        </div>
    </header>

    <div class="map-wrapper">
        <div id="map-container">
            <canvas id="c"></canvas>
        </div>

        <div class="map-controls">
            <button class="btn btn-primary btn-control shadow-sm" id="zoom-in" title="Zoom In">
                <i class="bi bi-plus-lg">+</i>
            </button>
            <button class="btn btn-primary btn-control shadow-sm" id="zoom-out" title="Zoom Out">
                <i class="bi bi-dash-lg">-</i>
            </button>
            <button class="btn btn-secondary btn-control shadow-sm" id="reset" title="Reset View">
                <i class="bi bi-arrow-counterclockwise">R</i>
            </button>
        </div>
    </div>

    <?php
        $mapData = json_decode($map_json, true);
        $buildings = [];
        if (isset($mapData['objects'])) {
            foreach ($mapData['objects'] as $index => $obj) {
                if (!empty($obj['label'])) {
                    $obj['id'] = $index; 
                    $buildings[] = $obj;
                }
            }
        }
        // Sort by Label
        usort($buildings, function($a, $b) {
            return strcmp($a['label'], $b['label']);
        });
    ?>
    <div class="container py-5" id="building-table">
        <h2 class="mb-4 fw-bold text-secondary border-start border-4 border-primary ps-3">รายชื่ออาคารและสถานที่</h2>
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th scope="col" class="py-3 ps-4" style="width: 30%;">ชื่ออาคาร / สถานที่</th>
                                <th scope="col" class="py-3">รายละเอียด</th>
                                <th scope="col" class="py-3 text-end pe-4" style="width: 150px;">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($buildings as $b): 
                                if (isset($b['showInSearch']) && $b['showInSearch'] === false) continue;
                            ?>
                            <tr>
                                <td class="ps-4 fw-bold text-primary"><?= htmlspecialchars($b['label']) ?></td>
                                <td class="text-muted small"><?= nl2br(htmlspecialchars($b['description'] ?? '-')) ?></td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="locateBuilding(<?= $b['id'] ?>)">
                                        <i class="bi bi-geo-alt-fill me-1"></i> ดูแผนที่
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="tooltip"></div>

    <!-- Info Modal - REMOVED (Replaced by Tooltip) -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1/fabric.min.js"></script>
    <script src="https://unpkg.com/@panzoom/panzoom@4.5.1/dist/panzoom.min.js"></script>

    <script>
        // Init Fabric Canvas (Static/Interactive Hybrid)
        const canvas = new fabric.Canvas('c', {
            selection: false,
            hoverCursor: 'pointer',
            defaultCursor: 'grab' 
        });
        
        // Data
        const savedJSON = <?php echo $map_json ?: '{}'; ?>;
        const savedBg = '<?php echo $bg_image; ?>';

        // Load JSON
        if (savedJSON && Object.keys(savedJSON).length > 0) {
            canvas.loadFromJSON(savedJSON, () => {
                canvas.getObjects().forEach(obj => {
                    // Make objects "Interactive" but NOT "Selectable/Movable"
                    obj.selectable = false; 
                    obj.lockMovementX = true;
                    obj.lockMovementY = true;
                    
                    // Only enable interaction if showInSearch is NOT false (default true)
                    if ((obj.label || obj.description) && obj.showInSearch !== false) {
                         obj.evented = true;
                         obj.hoverCursor = 'pointer';
                    } else {
                         obj.evented = false; // Disable all interactions (hover/click)
                         obj.hoverCursor = 'default';
                    }
                });
                
                // Set Canvas Size from JSON if available
                if (savedJSON.width) canvas.setWidth(savedJSON.width);
                if (savedJSON.height) canvas.setHeight(savedJSON.height);

                canvas.renderAll();
                initInteractions();
            });
        }
        
        // Load Background if needed
        if (savedBg) {
             fabric.Image.fromURL('/uploads/' + savedBg, function(img) {
                 canvas.setWidth(img.width);
                 canvas.setHeight(img.height);
                 canvas.setBackgroundImage(img, canvas.renderAll.bind(canvas));
             });
        } else if (!savedJSON.width) {
             // Default only if not set by JSON
             canvas.setWidth(2000);
             canvas.setHeight(800);
        }

        // --- Search Control ---
        const searchContainer = document.createElement('div');
        searchContainer.className = 'search-container shadow-sm';
        searchContainer.innerHTML = `
            <div class="input-group">
                <span class="input-group-text bg-white border-0 text-primary"><i class="bi bi-search"></i></span>
                <select class="form-select border-0" id="building-search" style="box-shadow: none; cursor: pointer;">
                    <option value="">ค้นหาอาคาร / สถานที่...</option>
                </select>
            </div>
        `;
        document.querySelector('.map-wrapper').appendChild(searchContainer);
        const searchSelect = document.getElementById('building-search');

        // --- Interactions ---
        const tooltip = document.getElementById('tooltip');

        function initInteractions() {
            let activeObject = null;
            const originalStates = new Map();
            // Fabric wrapper is ready
        }

        // Global Panzoom var
        let panzoomInstance = null;

        // --- Init Function ---
        function initApp() {
             // 1. Setup Panzoom
             const fabricWrapper = document.querySelector('.canvas-container');
             if (fabricWrapper) {
                // Determine Start Scale dynamically
                const mapContainer = document.getElementById('map-container');
                const containerWidth = mapContainer.clientWidth;
                const containerHeight = mapContainer.clientHeight;
                
                // Get Canvas Dimensions (Fabric wrapper dimensions)
                const canvasWidth = canvas.getWidth();
                const canvasHeight = canvas.getHeight();
                
                // Calculate Scale to Fit (with some padding)
                const scaleX = (containerWidth * 0.95) / canvasWidth;
                const scaleY = (containerHeight * 0.95) / canvasHeight;
                let startScale = Math.min(scaleX, scaleY);
                
                // Limit max start scale
                if (startScale > 1) startScale = 1;
                
                // Allow very small scales for seamless zooming
                const minScaleAllowed = Math.min(0.01, startScale / 2);

                panzoomInstance = Panzoom(fabricWrapper, {
                    maxScale: 5,
                    minScale: minScaleAllowed, 
                    contain: null, // Allow free panning/zooming (solves edge & cover issues)
                    startScale: startScale,
                    cursor: 'grab'
                });
                
                // Apply Start Scale & Center
                panzoomInstance.zoom(startScale, { animate: false });

                // Center the map initially
                // Calculate centered position
                const initialX = (containerWidth - (canvasWidth * startScale)) / 2;
                const initialY = (containerHeight - (canvasHeight * startScale)) / 2;
                panzoomInstance.pan(initialX, initialY, { animate: false });

                document.getElementById('zoom-in').addEventListener('click', panzoomInstance.zoomIn);
                document.getElementById('zoom-out').addEventListener('click', panzoomInstance.zoomOut);
                document.getElementById('reset').addEventListener('click', () => {
                    panzoomInstance.reset();
                    panzoomInstance.zoom(startScale, { animate: true });
                    panzoomInstance.pan(initialX, initialY, { animate: true });
                });
                // Allow Wheel
                mapContainer.addEventListener('wheel', panzoomInstance.zoomWithWheel);
                
                panzoomInstance.bind();
             }

             // 2. Populate Search
             const objects = canvas.getObjects();
             objects.sort((a, b) => (a.label || '').localeCompare(b.label || ''));

             objects.forEach(obj => {
                 // Check label AND showInSearch (default true if undefined)
                 if (obj.label && obj.showInSearch !== false) {
                     const option = document.createElement('option');
                     option.value = objects.indexOf(obj);
                     option.text = obj.label;
                     searchSelect.appendChild(option);
                     
                     obj.selectable = false; 
                     obj.evented = true; 
                     obj.hoverCursor = 'pointer';
                 }
             });

             // Search Event
             searchSelect.addEventListener('change', (e) => {
                 const idx = e.target.value;
                 if (idx !== "") {
                     const target = objects[idx];
                     focusOnObject(target);
                 } else {
                     clearHighlight();
                     panzoomInstance.reset();
                 }
             });

             // Canvas Events
             canvas.on('mouse:over', (e) => {
                 if (window.innerWidth > 768 && e.target && e.target.label) {
                     highlightObject(e.target);
                     showTooltip(e.target);
                 }
             });
             
             canvas.on('mouse:out', (e) => {
                 if (window.innerWidth > 768) {
                     clearHighlight();
                     document.getElementById('tooltip').style.opacity = 0;
                 }
             });
             
             canvas.on('mouse:move', (e) => {
                 if (window.innerWidth > 768 && document.getElementById('tooltip').style.opacity == '1') {
                     const t = document.getElementById('tooltip');
                    t.style.left = (e.e.clientX + 15) + 'px';
                    t.style.top = (e.e.clientY + 15) + 'px';
                 }
             });
             
             canvas.on('mouse:down', (opt) => {
                 if (opt.target && opt.target.label) {
                     const idx = objects.indexOf(opt.target);
                     searchSelect.value = idx;
                     focusOnObject(opt.target);
                 }
             });
        }

        // --- Global Helpers ---
        let currentHighlight = null;
        const stateMap = new Map();

        function highlightObject(target) {
            if (currentHighlight === target) return;
            if (currentHighlight) clearHighlight();

            if (!stateMap.has(target)) {
                 stateMap.set(target, {
                     fill: target.fill,
                     stroke: target.stroke,
                     strokeWidth: target.strokeWidth,
                     opacity: target.opacity
                 });
            }

            target.set({
                opacity: 0.9,
                stroke: '#FF4500', 
                strokeWidth: 5,
                strokeUniform: true
            });
            currentHighlight = target;
            canvas.requestRenderAll();
        }

        function clearHighlight() {
            if (currentHighlight) {
                const state = stateMap.get(currentHighlight);
                if (state) currentHighlight.set(state);
                currentHighlight = null;
                canvas.requestRenderAll();
            }
        }

        function showTooltip(target) {
            const tooltip = document.getElementById('tooltip');
            let content = `<div class="fw-bold mb-1">${target.label}</div>`;
            if (target.description) {
                const descHtml = target.description.replace(/\n/g, '<br>');
                content += `<small class="d-block text-white opacity-75">${descHtml}</small>`;
            }
            tooltip.innerHTML = content;
            tooltip.style.opacity = 1;
            
            if (window.innerWidth <= 768) {
                 tooltip.style.bottom = '90px'; 
                 tooltip.style.left = '50%';
                 tooltip.style.transform = 'translateX(-50%)';
                 tooltip.style.top = 'auto';
            }
        }

        function focusOnObject(target) {
            highlightObject(target);
            showTooltip(target);
            
            if (panzoomInstance) {
                const center = target.getCenterPoint();
                const container = document.getElementById('map-container');
                const cw = container.clientWidth;
                const ch = container.clientHeight;
                
                const isMobile = window.innerWidth <= 768;
                const targetScale = isMobile ? 1.0 : 1.5; 
                
                const px = (cw / 2) - (center.x * targetScale);
                const offsetTop = isMobile ? (ch * 0.15) : 50; 
                const py = (ch / 2) - (center.y * targetScale) + offsetTop;
                
                panzoomInstance.zoom(targetScale, { animate: true });
                setTimeout(() => {
                    panzoomInstance.pan(px, py, { animate: true });
                }, 100);
            }
        }

        // Function called from PHP Table
        function locateBuilding(index) {
            // 1. Scroll Up
            document.querySelector('.header-title').scrollIntoView({ behavior: 'smooth' });
            
            // 2. Select Object on Canvas
            // JSON objects are loaded in order, so index matches canvas.item(index)
            // Note: Background image is typically handled separately in Fabric IF set via setBackgroundImage
            // But if it was added as object, indexes shift. 
            // Our logic uses setBackgroundImage, so it's not in getObjects().
            // So indexes should match.
            const target = canvas.item(index);
            if (target) {
                focusOnObject(target);
                
                // Sync Dropdown
                // Need to find this object's index in the "Search Sort Order"? No, value is raw index?
                // Wait.
                // In initApp: `objects.forEach(obj => { ... option.value = objects.indexOf(obj); })`
                // `objects` in initApp was `canvas.getObjects()`.
                // `objects.indexOf(obj)` IS the raw index.
                // So setting searchSelect.value = index works!
                const searchSelect = document.getElementById('building-search');
                if (searchSelect) searchSelect.value = index;
            }
        }

        setTimeout(initApp, 500);
    </script>
</body>
</html>