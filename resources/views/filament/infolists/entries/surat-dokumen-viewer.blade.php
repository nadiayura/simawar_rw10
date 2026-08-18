<?php
use Illuminate\Support\Facades\Storage;
$files = is_array($entry->getState()) ? $entry->getState() : (empty($entry->getState()) ? [] : [$entry->getState()]);
$items = array_map(function ($p) {
    $mime = null;
    try {
        $mime = Storage::mimeType($p);
    } catch (\Throwable $e) {
        $mime = null;
    }
    return [
        'url' => Storage::url($p),
        'mime' => $mime ?: 'application/octet-stream',
    ];
}, $files);
$uid = 'dok_'.uniqid();
?>
<div class="space-y-4">
    <div style="display:flex; gap:1rem; overflow-x:auto; padding:.25rem; scroll-snap-type:x mandatory;">
        <?php foreach ($items as $i => $it): ?>
            <?php $isImg = str_starts_with(strtolower($it['mime']), 'image/'); ?>
            <div onclick="window['open_<?php echo e($uid); ?>'](<?php echo e($i); ?>)" style="flex:0 0 auto; width:280px; aspect-ratio:16/9; background:#e5e7eb; border-radius:.5rem; overflow:hidden; cursor:pointer; scroll-snap-align:start; display:flex; align-items:center; justify-content:center; position:relative;">
                <?php if ($isImg): ?>
                    <img src="<?php echo e($it['url']); ?>" alt="Dokumen" style="width:100%; height:100%; object-fit:cover; transition:transform .3s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                <?php else: ?>
                    <div style="display:flex; align-items:center; justify-content:center; width:100%; height:100%; color:#111827;">
                        <span style="background:#fff; padding:.25rem .5rem; border-radius:.375rem; font-size:12px; border:1px solid #e5e7eb;">PDF</span>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    <div id="modal-<?php echo e($uid); ?>" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.8); align-items:center; justify-content:center;">
        <div style="position:relative; z-index:1; width:100%; max-width:95vw; max-height:95vh; margin:0 auto; padding:0.5rem;">
            <button type="button" onclick="window['close_<?php echo e($uid); ?>']()" style="position:absolute; top:1rem; right:1rem; color:#fff; z-index:10;">
                <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            <img id="image-<?php echo e($uid); ?>" src="" alt="" style="width:100%; height:auto; max-height:90vh; object-fit:contain; border-radius:.5rem; display:none; margin:0 auto;">
            <iframe id="pdf-<?php echo e($uid); ?>" src="" style="width:100%; height:90vh; border:none; border-radius:.5rem; display:none;" allowfullscreen></iframe>
            <?php if (count($items) > 1): ?>
            <div style="position:absolute; inset:0; display:flex; align-items:center; justify-content:space-between; padding:0 1rem;">
                <button type="button" onclick="window['nav_<?php echo e($uid); ?>'](-1)" style="background:rgba(0,0,0,.5); color:#fff; padding:.5rem; border-radius:9999px;">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
                <button type="button" onclick="window['nav_<?php echo e($uid); ?>'](1)" style="background:rgba(0,0,0,.5); color:#fff; padding:.5rem; border-radius:9999px;">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>
            <?php endif; ?>
        </div>
        <div style="position:absolute; inset:0; z-index:0;" onclick="window['close_<?php echo e($uid); ?>']()"></div>
    </div>
    <script>
        (function(){
            var items = <?php echo json_encode($items); ?>;
            var idx = 0;
            var modal = document.getElementById('modal-<?php echo e($uid); ?>');
            var imgEl = document.getElementById('image-<?php echo e($uid); ?>');
            var pdfEl = document.getElementById('pdf-<?php echo e($uid); ?>');
            function render(){
                var it = items[idx] || null;
                if (!it) return;
                var isImg = String(it.mime || '').toLowerCase().startsWith('image/');
                if (isImg) {
                    pdfEl.style.display = 'none';
                    imgEl.style.display = 'block';
                    imgEl.src = it.url || '';
                } else {
                    imgEl.style.display = 'none';
                    pdfEl.style.display = 'block';
                    pdfEl.src = (it.url || '') + '#toolbar=1&zoom=page-width';
                }
            }
            window['open_<?php echo e($uid); ?>'] = function(i){
                idx = i;
                render();
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            };
            window['close_<?php echo e($uid); ?>'] = function(){
                modal.style.display = 'none';
                document.body.style.overflow = '';
                imgEl.src = '';
                pdfEl.src = '';
            };
            window['nav_<?php echo e($uid); ?>'] = function(direction){
                idx = (idx + direction + items.length) % items.length;
                render();
            };
            document.addEventListener('keydown', function(event){
                if (modal.style.display !== 'none') {
                    if (event.key === 'Escape') window['close_<?php echo e($uid); ?>']();
                    else if (event.key === 'ArrowLeft') window['nav_<?php echo e($uid); ?>'](-1);
                    else if (event.key === 'ArrowRight') window['nav_<?php echo e($uid); ?>'](1);
                }
            });
        })();
    </script>
</div>
