<?php
// ============================================================
// BeachWatch — XSLT Transformation
// Converts resorts.xml → HTML using resorts.xsl
// ============================================================

require_once 'config.php';

/**
 * Transform resorts.xml to HTML using XSLT
 * Returns HTML string or outputs directly
 */
function transformResortsToHTML($outputToFile = false) {
    $xmlFile  = XML_PATH . 'resorts.xml';
    $xslFile  = XSLT_PATH . 'resorts.xsl';

    if (!file_exists($xmlFile)) {
        return '<p>Error: resorts.xml not found.</p>';
    }
    if (!file_exists($xslFile)) {
        return '<p>Error: resorts.xsl not found.</p>';
    }

    // Load XML
    $xml = new DOMDocument();
    $xml->load($xmlFile);

    // Load XSLT stylesheet
    $xsl = new DOMDocument();
    $xsl->load($xslFile);

    // Create XSLT processor
    $proc = new XSLTProcessor();
    $proc->importStylesheet($xsl);

    // Perform transformation
    $html = $proc->transformToXML($xml);

    if ($outputToFile) {
        // Save output to a static HTML file
        $outputPath = __DIR__ . '/../pages/resorts_generated.html';
        file_put_contents($outputPath, $html);
        return $outputPath;
    }

    return $html;
}

/**
 * Transform resorts.xml to JSON
 * Demonstrates XML → JSON conversion via XSLT
 */
function transformResortsToJSON() {
    require_once 'parse_xml.php';
    $resorts = parseResortsXML();
    return json_encode(['resorts' => $resorts], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}

// ============================================================
// Direct access: render transformed HTML
// ============================================================
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    $format = $_GET['format'] ?? 'html';

    if ($format === 'json') {
        header('Content-Type: application/json');
        echo transformResortsToJSON();
    } else {
        header('Content-Type: text/html; charset=UTF-8');
        echo transformResortsToHTML();
    }
}
