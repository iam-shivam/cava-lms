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
    <!-- PDF.js CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #333;
            color: #fff;
            font-family: sans-serif;
            display: flex;
            flex-direction: column;
            height: 100vh;
        }
        #toolbar {
            background-color: #474747;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 5px rgba(0,0,0,0.5);
            z-index: 10;
        }
        .controls button {
            background: #555;
            color: white;
            border: 1px solid #777;
            padding: 6px 12px;
            margin-right: 5px;
            cursor: pointer;
            border-radius: 4px;
        }
        .controls button:hover {
            background: #666;
        }
        #pdf-container {
            flex: 1;
            overflow: auto;
            display: flex;
            justify-content: center;
            padding: 20px;
            user-select: none;
            -webkit-user-select: none;
        }
        canvas {
            border: 1px solid #000;
            box-shadow: 0 4px 8px rgba(0,0,0,0.5);
            background: white;
            pointer-events: none;
        }
    </style>
</head>
<body>

    <div id="toolbar">
        <div>
            <strong><?php echo $docTitle; ?></strong>
        </div>
        <div class="controls">
            <button id="prev-page">Previous</button>
            <span id="page-num">1</span> / <span id="page-count">--</span>
            <button id="next-page">Next</button>
            <button id="zoom-out">Zoom Out</button>
            <button id="zoom-in">Zoom In</button>
        </div>
    </div>

    <div id="pdf-container">
        <canvas id="pdf-render"></canvas>
    </div>

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
