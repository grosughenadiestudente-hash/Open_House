<?php
if (!isset($lang)) $lang = $_GET['lang'] ?? 'it';
if (!isset($user_type)) $user_type = $_SESSION['user_type'] ?? '';
?>
<style>
    .navbar {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        width: 100% !important;
        z-index: 9999 !important;
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var nav = document.querySelector('nav.navbar');
        if (nav) {
            document.body.style.paddingTop = nav.offsetHeight + 'px';
        }
    });
</script>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php"><i class="bi bi-mortarboard"></i> Open House</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 d-flex align-items-lg-center">
                <li class="nav-item"><a class="nav-link" href="index.php?lang=<?= $lang ?>">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="istituti_elenco.php?lang=<?= $lang ?>">Istituti</a></li>
                <li class="nav-item"><a class="nav-link" href="partner_istituti.php?lang=<?= $lang ?>">Partner</a></li>
                <li class="nav-item"><a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#chiSiamoModal">Chi siamo</a></li>
                <li class="nav-item"><a class="nav-link" href="dashboard.php?lang=<?= $lang ?>">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="attivita_elenco.php?lang=<?= $lang ?>">Attività</a></li>
            </ul>

            <div class="d-flex align-items-center gap-2">
                <?php if (!empty($user_type)): ?>
                    <a class="nav-link text-white" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
                <?php else: ?>
                    <a href="login.php?lang=<?= $lang ?>" class="btn btn-light btn-sm">Accedi</a>
                    <a href="register.php?lang=<?= $lang ?>" class="btn btn-light btn-sm">Registrati</a>
                <?php endif; ?>

                <a href="<?= basename($_SERVER['PHP_SELF']) ?>?lang=it" class="btn btn-outline-light btn-sm <?= $lang === 'it' ? 'active' : '' ?>">IT</a>
                <a href="<?= basename($_SERVER['PHP_SELF']) ?>?lang=en" class="btn btn-outline-light btn-sm <?= $lang === 'en' ? 'active' : '' ?>">EN</a>
            </div>
        </div>
    </div>
</nav>

<!-- Modal Chi Siamo (incluso qui per essere disponibile su tutte le pagine) -->
<div class="modal fade" id="chiSiamoModal" tabindex="-1" aria-labelledby="chiSiamoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="chiSiamoModalLabel">Chi Siamo - VR Open House</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 mb-3">
                        <img src="image/745d5f52-0e02-42ee-b3f5-1a39e2aa9f9a.webp" alt="VR Open House" class="img-fluid" style="max-height:300px; object-fit:cover; width:100%;">
                    </div>
                                        <div class="col-12 text-muted" style="line-height:1.5;">
                        <h6><strong>L'innovazione al servizio dell'orientamento scolastico e della formazione</strong></h6>
                        <p>La piattaforma offre un ecosistema digitale intuitivo che consente agli istituti di superare i limiti della presenza fisica, offrendo visite virtuali, attività interattive e strumenti per l'orientamento.</p>
                        <p>Inclusività, accessibilità e visibilità per gli istituti sono i pilastri del progetto.</p>
                                                <div class="mt-3">
                                                        <button id="openModelBtn" type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modelViewerModal">Visualizza il tim</button>
                                                </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
            </div>
        </div>
    </div>
</div>

<!-- Model Viewer Modal -->
<div class="modal fade" id="modelViewerModal" tabindex="-1" aria-labelledby="modelViewerLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modelViewerLabel">Visualizza modello 3D</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
            </div>
            <div class="modal-body p-0">
                <div id="modelViewerContainer" style="width:100%;height:70vh;background:#111;display:none"></div>
                <img id="modelViewerImage" src="image/avaturn_screenshot.jpg" alt="Preview" style="width:100%;height:70vh;object-fit:contain;display:none;background:#111">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                <button id="removeModelBtn" type="button" class="btn btn-danger">Rimuovi modello</button>
            </div>
        </div>
    </div>
</div>

<!-- Three.js + GLTF loader for model viewer -->
<script src="https://unpkg.com/three@0.152.0/build/three.min.js"></script>
<script src="https://unpkg.com/three@0.152.0/examples/js/loaders/GLTFLoader.js"></script>
<script>
    (function(){
        let renderer, scene, camera, currentModel, rafId;
        const container = document.getElementById('modelViewerContainer');
        const modalEl = document.getElementById('modelViewerModal');
        const modelPath = 'image/avaturn_screenshot.jpg';

        function initViewer(){
            if(renderer) return;
            scene = new THREE.Scene();
            camera = new THREE.PerspectiveCamera(45, container.clientWidth/container.clientHeight, 0.1, 1000);
            renderer = new THREE.WebGLRenderer({antialias:true, alpha:true});
            renderer.setPixelRatio(window.devicePixelRatio || 1);
            renderer.setSize(container.clientWidth, container.clientHeight);
            container.appendChild(renderer.domElement);
            const dir = new THREE.DirectionalLight(0xffffff, 0.9); dir.position.set(5,5,10); scene.add(dir);
            scene.add(new THREE.AmbientLight(0x666666));
            camera.position.set(0, -4, 2);
            camera.up.set(0,0,1);
            window.addEventListener('resize', onResize);
        }

        function onResize(){
            if(!renderer) return;
            camera.aspect = container.clientWidth/container.clientHeight; camera.updateProjectionMatrix();
            renderer.setSize(container.clientWidth, container.clientHeight);
        }

        function loadModel(url){
            // if url is a GLB/GLTF, use GLTFLoader, otherwise treat as an image texture
            const lower = url.toLowerCase();
            if(lower.endsWith('.glb') || lower.endsWith('.gltf')){
                const loader = new THREE.GLTFLoader();
                loader.load(url, (gltf)=>{
                    if(currentModel){ scene.remove(currentModel); }
                    currentModel = gltf.scene || gltf.scenes?.[0];
                    if(!currentModel) return;
                    const box = new THREE.Box3().setFromObject(currentModel);
                    const size = new THREE.Vector3(); box.getSize(size);
                    const maxDim = Math.max(size.x, size.y, size.z) || 1;
                    const scale = 2.5 / maxDim;
                    currentModel.scale.setScalar(scale);
                    box.setFromObject(currentModel);
                    const center = new THREE.Vector3(); box.getCenter(center);
                    currentModel.position.sub(center);
                    scene.add(currentModel);
                    camera.position.set(0, -Math.max(3, 3*scale), Math.max(1.5, 1.5*scale));
                    camera.lookAt(0,0,0);
                }, undefined, (e)=>{ console.error('GLTF load error', e); });
            } else {
                // load as image and map onto a plane
                const texLoader = new THREE.TextureLoader();
                texLoader.load(url, (texture)=>{
                    if(currentModel){ scene.remove(currentModel); }
                    const aspect = (texture.image && texture.image.width) ? texture.image.width / texture.image.height : 1;
                    const height = 2.0;
                    const width = height * aspect;
                    const geom = new THREE.PlaneGeometry(width, height);
                    const mat = new THREE.MeshBasicMaterial({map:texture, side: THREE.DoubleSide});
                    const plane = new THREE.Mesh(geom, mat);
                    currentModel = plane;
                    scene.add(plane);
                    camera.position.set(0, Math.max(3, 3), 0.5);
                    camera.lookAt(0,0,0);
                }, undefined, (e)=>{ console.error('Texture load error', e); });
            }
        }

        function animate(){
            rafId = requestAnimationFrame(animate);
            if(currentModel) currentModel.rotation.z += 0.005;
            renderer && renderer.render(scene, camera);
        }

        function destroyViewer(){
            cancelAnimationFrame(rafId);
            if(currentModel){ scene.remove(currentModel); currentModel.traverse && currentModel.traverse((o)=>{ if(o.geometry) o.geometry.dispose(); if(o.material) { if(Array.isArray(o.material)){ o.material.forEach(m=>m.dispose && m.dispose()); } else o.material.dispose && o.material.dispose(); } }); currentModel = null; }
            if(renderer){ renderer.dispose(); if(renderer.domElement && renderer.domElement.parentNode) renderer.domElement.parentNode.removeChild(renderer.domElement); renderer = null; }
            scene = null; camera = null;
            window.removeEventListener('resize', onResize);
        }

        modalEl.addEventListener('shown.bs.modal', function(){
            // robust image/GLB display: use loader, onload/onerror and small delay to ensure layout
            const isImage = /\.(jpg|jpeg|png|webp|jfif)$/i.test(modelPath);
            const imgEl = document.getElementById('modelViewerImage');
            const canvasContainer = document.getElementById('modelViewerContainer');
            // ensure loader and error elements exist (create if needed)
            let loaderEl = document.getElementById('modelViewerLoader');
            if(!loaderEl){
                loaderEl = document.createElement('div');
                loaderEl.id = 'modelViewerLoader';
                loaderEl.style.position = 'absolute'; loaderEl.style.inset = '0'; loaderEl.style.display = 'none'; loaderEl.style.alignItems = 'center'; loaderEl.style.justifyContent = 'center'; loaderEl.style.background = 'rgba(0,0,0,0.6)'; loaderEl.style.color = '#fff';
                loaderEl.innerHTML = '<div style="text-align:center"><div class="spinner-border text-light" role="status"></div><div class="mt-2">Caricamento modello...</div></div>';
                modalEl.querySelector('.modal-body').appendChild(loaderEl);
            }
            let errorEl = document.getElementById('modelViewerError');
            if(!errorEl){
                errorEl = document.createElement('div'); errorEl.id = 'modelViewerError'; errorEl.style.display='none'; errorEl.style.padding='16px'; errorEl.style.color='#b00'; errorEl.style.background='#fee'; errorEl.textContent='Impossibile caricare l\'anteprima.';
                modalEl.querySelector('.modal-body').appendChild(errorEl);
            }
            errorEl.style.display = 'none';

            if (isImage) {
                if (renderer) { destroyViewer(); }
                canvasContainer.style.display = 'none';
                imgEl.style.display = 'none';
                loaderEl.style.display = 'flex';

                imgEl.onload = function(){ loaderEl.style.display = 'none'; imgEl.style.display = 'block'; imgEl.style.visibility = 'visible'; imgEl.style.opacity = '1'; };
                imgEl.onerror = function(){ loaderEl.style.display = 'none'; errorEl.style.display = 'block'; console.error('modelViewer: image failed to load');
                    // fallback to GLB if available
                    const glbPath = 'image/model (1).glb';
                    imgEl.style.display = 'none';
                    canvasContainer.style.display = 'block';
                    initViewer(); loadModel(glbPath); animate();
                };

                // small defer to ensure modal layout computed
                setTimeout(()=>{
                    try { imgEl.src = modelPath; } catch(e){ loaderEl.style.display='none'; errorEl.style.display='block'; }
                }, 50);
            } else {
                imgEl.style.display = 'none';
                loaderEl.style.display = 'none';
                canvasContainer.style.display = 'block';
                initViewer(); loadModel(modelPath); animate();
            }
        });

        modalEl.addEventListener('hidden.bs.modal', function(){
                // hide image fallback and destroy WebGL viewer
                const imgEl2 = document.getElementById('modelViewerImage');
                if(imgEl2){ imgEl2.style.display = 'none'; }
                destroyViewer();
        });

        document.getElementById('removeModelBtn').addEventListener('click', function(){
            if(currentModel){ scene.remove(currentModel); currentModel = null; }
        });
    })();
</script>

