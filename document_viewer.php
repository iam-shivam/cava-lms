<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/helpers/SecurityHelper.php';

$docId = trim($_GET['id'] ?? '');

if (empty($docId) || empty($_SESSION['user_id'])) {
    die('Unauthorized access.');
}

// Check if doc is pdf, if not, just redirect to serve_document.php so it downloads
try {
    $doc = DB::fetch("SELECT file_type, title FROM video_documents WHERE id = ?", [$docId]);
    if (!$doc) {
        die("Document not found.");
    }
    
    if ($doc['file_type'] !== 'pdf') {
        header("Location: serve_document.php?id=" . urlencode($docId));
        exit;
    }
} catch (Exception $e) {
    die("Error loading document.");
}

$pdfUrl = "serve_document.php?id=" . urlencode($docId);
$docTitle = htmlspecialchars($doc['title']);

// turn on if we need to tackle the screenshot loophole
// $user = DB::fetch("SELECT full_name, email FROM users WHERE id = ?", [$_SESSION['user_id']]);
// $watermarkText = htmlspecialchars($user['full_name'] . ' (' . $user['email'] . ')');
$watermarkText = 'CAVA LMS';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $docTitle; ?> - Document Viewer</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/cava-studio-tokens.css?v=2.0">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/cava-studio-document-viewer.css?v=2.0">
    <!-- PDF.js CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
</head>
<body>

    <header id="toolbar">
        <div class="viewer-identity">
            <span class="viewer-mark" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M7 3.75h7l3 3v13.5H7z"></path>
                    <path d="M14 3.75v3h3M9.75 11h4.5M9.75 14.25h4.5M9.75 17.5h3"></path>
                </svg>
            </span>
            <div class="viewer-heading">
                <span class="viewer-eyebrow">CAVA Studio &middot; Protected viewer</span>
                <strong class="viewer-title" title="<?php echo $docTitle; ?>"><?php echo $docTitle; ?></strong>
            </div>
        </div>

        <div class="controls" role="toolbar" aria-label="Document controls">
            <div class="control-cluster" aria-label="Page navigation">
                <button type="button" id="prev-page" aria-label="Previous page" aria-controls="pdf-container">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m15 18-6-6 6-6"></path>
                    </svg>
                    <span class="control-label">Previous</span>
                </button>
                <span class="page-indicator" role="status" aria-live="polite" aria-label="Current page">
                    <span id="page-num">1</span> <span aria-hidden="true">/</span> <span id="page-count">--</span>
                </span>
                <button type="button" id="next-page" aria-label="Next page" aria-controls="pdf-container">
                    <span class="control-label">Next</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m9 18 6-6-6-6"></path>
                    </svg>
                </button>
            </div>

            <span class="control-divider" aria-hidden="true"></span>

            <div class="control-cluster" aria-label="Zoom controls">
                <button type="button" id="zoom-out" aria-label="Zoom out" aria-controls="pdf-container">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="10.5" cy="10.5" r="6.5"></circle>
                        <path d="M15.2 15.2 20 20M7.5 10.5h6"></path>
                    </svg>
                    <span class="control-label">Zoom out</span>
                </button>
                <button type="button" id="zoom-in" aria-label="Zoom in" aria-controls="pdf-container">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="10.5" cy="10.5" r="6.5"></circle>
                        <path d="M15.2 15.2 20 20M7.5 10.5h6M10.5 7.5v6"></path>
                    </svg>
                    <span class="control-label">Zoom in</span>
                </button>
            </div>
        </div>
    </header>

    <main id="pdf-container" role="region" aria-label="<?php echo $docTitle; ?> document pages" tabindex="0">
        <div class="document-stage">
            <canvas id="pdf-render" role="img" aria-label="Rendered page from <?php echo $docTitle; ?>"></canvas>
        </div>
    </main>

    <?php echo SecurityHelper::renderAntiPiracyScript(); ?>

    <script>
        const url = '<?php echo $pdfUrl; ?>';
        
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';

        let pdfDoc = null,
            pageNum = 1,
            pageIsRendering = false,
            pageNumIsPending = null,
            scale = 1.2,
            canvas = document.getElementById('pdf-render'),
            ctx = canvas.getContext('2d');

        const renderPage = num => {
            pageIsRendering = true;

            pdfDoc.getPage(num).then(page => {
                const viewport = page.getViewport({ scale });
                canvas.height = viewport.height;
                canvas.width = viewport.width;

                const renderCtx = {
                    canvasContext: ctx,
                    viewport: viewport
                };

                page.render(renderCtx).promise.then(() => {
                    pageIsRendering = false;
                    
                    // Draw dynamic watermark
                    ctx.save();
                    ctx.font = "bold 60px Arial";
                    ctx.fillStyle = "rgba(100, 100, 100, 0.25)";
                    ctx.textAlign = "center";
                    ctx.textBaseline = "middle";
                    ctx.translate(canvas.width / 2, canvas.height / 2);
                    ctx.rotate(-Math.PI / 4);
                    // Draw multiple times for coverage
                    ctx.fillText("<?php echo addslashes($watermarkText); ?>", 0, -200);
                    ctx.fillText("<?php echo addslashes($watermarkText); ?>", 0, 0);
                    ctx.fillText("<?php echo addslashes($watermarkText); ?>", 0, 200);
                    ctx.restore();

                    if (pageNumIsPending !== null) {
                        renderPage(pageNumIsPending);
                        pageNumIsPending = null;
                    }
                });

                document.getElementById('page-num').textContent = num;
            });
        };

        const queueRenderPage = num => {
            if (pageIsRendering) {
                pageNumIsPending = num;
            } else {
                renderPage(num);
            }
        };

        const showPrevPage = () => {
            if (pageNum <= 1) return;
            pageNum--;
            queueRenderPage(pageNum);
        };

        const showNextPage = () => {
            if (pageNum >= pdfDoc.numPages) return;
            pageNum++;
            queueRenderPage(pageNum);
        };
        
        const zoomIn = () => {
            scale += 0.2;
            queueRenderPage(pageNum);
        };

        const zoomOut = () => {
            if (scale <= 0.6) return;
            scale -= 0.2;
            queueRenderPage(pageNum);
        };

        const loadingTask = pdfjsLib.getDocument({
            url: url,
            httpHeaders: {
                'X-Viewer-Auth': 'true'
            }
        });

        loadingTask.promise.then(pdfDoc_ => {
            pdfDoc = pdfDoc_;
            document.getElementById('page-count').textContent = pdfDoc.numPages;
            renderPage(pageNum);
        }).catch(err => {
            const div = document.createElement('div');
            div.className = 'error';
            div.appendChild(document.createTextNode(err.message));
            document.getElementById('pdf-container').innerHTML = `<p style="color:red; background: white; padding: 20px; border-radius: 8px;">Error loading document: Secure Session Expired or Invalid Permissions.</p>`;
        });

        document.getElementById('prev-page').addEventListener('click', showPrevPage);
        document.getElementById('next-page').addEventListener('click', showNextPage);
        document.getElementById('zoom-in').addEventListener('click', zoomIn);
        document.getElementById('zoom-out').addEventListener('click', zoomOut);
    </script>
</body>
</html>
