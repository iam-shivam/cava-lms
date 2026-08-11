<?php
/**
 * SecurityHelper Class
 * 
 * Centralized security helper for client-side protection, watermarking,
 * right-click/shortcut blocking, and media stream authorization.
 */
class SecurityHelper {

    /**
     * Render global anti-piracy script (disables context menu, F12, Print, Save, Inspect)
     */
    public static function renderAntiPiracyScript(): string {
        return <<<HTML
<script>
(function() {
    'use strict';

    // Prevent context menu (right click)
    document.addEventListener('contextmenu', function(e) {
        e.preventDefault();
        return false;
    });

    // Block keyboard shortcuts for inspect element, print, save, view-source
    window.addEventListener('keydown', function(e) {
        if (
            e.key === 'F12' || 
            (e.ctrlKey && e.shiftKey && ['I','J','C','i','j','c'].includes(e.key)) ||
            (e.ctrlKey && ['U','u','P','p','S','s'].includes(e.key)) ||
            (e.metaKey && ['P','p','S','s'].includes(e.key))
        ) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
    }, true);
})();
</script>
HTML;
    }

    /**
     * Render dynamic multi-layered watermark canvas and SVG generator script
     */
    public static function renderWatermarkScript(string $watermarkText): string {
        $encodedText = json_encode($watermarkText);
        return <<<HTML
<script>
(function() {
    'use strict';
    window.watermarkText = {$encodedText};

    window.getMultiLayerSvgWatermark = function(text) {
        const email = text || window.watermarkText || 'CAVA LMS';
        const svgStr = `
            <svg xmlns="http://www.w3.org/2000/svg" width="380" height="240">
                <text x="50%" y="28%" fill="rgba(100, 100, 100, 0.18)" font-size="15" font-family="sans-serif" font-weight="bold" text-anchor="middle" transform="rotate(-30, 190, 67)">\${email}</text>
                <text x="50%" y="78%" fill="rgba(70, 70, 70, 0.14)" font-size="22" font-family="sans-serif" font-weight="900" text-anchor="middle" transform="rotate(-42, 190, 187)">\${email}</text>
            </svg>
        `;
        return 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(svgStr);
    };

    window.drawWatermarkOnCanvas = function(canvas, ctx, customText) {
        if (!canvas || !ctx) return;
        ctx.save();
        const text = customText || window.watermarkText || 'CAVA LMS';
        const width = canvas.width;
        const height = canvas.height;

        ctx.font = "bold 22px sans-serif";
        ctx.fillStyle = "rgba(110, 110, 110, 0.20)";
        ctx.textAlign = "center";
        ctx.textBaseline = "middle";
        ctx.rotate(-Math.PI / 6);

        const stepX = 260;
        const stepY = 160;
        for (let x = -width; x < width * 2; x += stepX) {
            for (let y = -height; y < height * 2; y += stepY) {
                ctx.fillText(text, x, y);
            }
        }
        ctx.restore();
    };
})();
</script>
HTML;
    }
}
