<?php
// ============================================================
// BeachWatch — Resorts Page
// Uses XSLT transformation to render resort listings from XML
// ============================================================

require_once 'php/config.php';
require_once 'php/transform_xslt.php';
require_once 'php/parse_xml.php';

$resorts = parseResortsXML();
$xsltHTML = transformResortsToHTML(); // XSLT transform for display
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>BeachWatch — All Resorts</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Lato:wght@300;400;600;700&display=swap" rel="stylesheet"/>
    <style>
        :root {
            --ocean: #0077b6; --deep: #03045e; --coral: #e76f51;
            --ocean-light: #00b4d8; --gray: #6c757d;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Lato', sans-serif; background: #f0f8ff; }
        nav {
            background: var(--deep); padding: 16px 50px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .nav-logo {
            font-family: 'Playfair Display', serif; font-size: 1.4rem;
            color: white; text-decoration: none; letter-spacing: 2px;
        }
        .nav-logo span { color: var(--ocean-light); }
        .nav-links { display: flex; gap: 28px; list-style: none; }
        .nav-links a { color: rgba(255,255,255,0.8); text-decoration: none; font-size: 0.88rem; font-weight: 600; }
        .nav-links a:hover { color: var(--ocean-light); }
        .nav-links .active { color: var(--ocean-light); }

        .page-hero {
            background: linear-gradient(135deg, var(--deep), var(--ocean));
            padding: 60px 50px 40px;
            color: white;
        }
        .page-hero h1 {
            font-family: 'Playfair Display', serif; font-size: 2.4rem; margin-bottom: 8px;
        }
        .page-hero p { color: rgba(255,255,255,0.75); font-size: 0.95rem; }

        .tech-badge {
            display: inline-block; margin-top: 14px;
            background: rgba(0,180,216,0.2); border: 1px solid rgba(0,180,216,0.4);
            color: var(--ocean-light); font-size: 0.72rem;
            padding: 4px 14px; border-radius: 20px; letter-spacing: 1px;
        }

        .content { max-width: 1200px; margin: 0 auto; padding: 50px 30px; }

        /* Tabs for XSLT vs DOM view */
        .view-tabs {
            display: flex; gap: 0; margin-bottom: 36px;
            border: 2px solid var(--ocean); border-radius: 10px;
            overflow: hidden; width: fit-content;
        }
        .tab-btn {
            padding: 10px 28px; background: white; color: var(--ocean);
            border: none; cursor: pointer; font-weight: 600;
            font-size: 0.88rem; transition: all 0.2s; font-family: 'Lato', sans-serif;
        }
        .tab-btn.active { background: var(--ocean); color: white; }
        .tab-btn:hover:not(.active) { background: #e8f4fd; }

        /* XSLT output panel */
        .xslt-panel, .dom-panel { display: none; }
        .xslt-panel.visible, .dom-panel.visible { display: block; }

        /* DOM parsed resorts */
        .resort-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 28px;
        }
        .resort-card {
            background: white; border-radius: 16px; overflow: hidden;
            box-shadow: 0 2px 18px rgba(0,0,0,0.07);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .resort-card:hover { transform: translateY(-6px); box-shadow: 0 10px 32px rgba(0,119,182,0.15); }
        .card-head {
            background: linear-gradient(135deg, var(--ocean), var(--deep));
            padding: 24px 22px; color: white;
        }
        .card-head h3 { font-family: 'Playfair Display', serif; font-size: 1.15rem; margin-bottom: 6px; }
        .card-head .loc { font-size: 0.8rem; opacity: 0.8; }
        .card-body { padding: 20px 22px; }
        .desc { font-size: 0.85rem; color: #555; line-height: 1.6; margin-bottom: 14px; }
        .tags { display: flex; flex-wrap: wrap; gap: 5px; margin-bottom: 16px; }
        .tag {
            background: #e8f4fd; color: var(--ocean);
            font-size: 0.7rem; padding: 3px 9px; border-radius: 20px; font-weight: 600;
        }
        .card-foot {
            display: flex; justify-content: space-between; align-items: center;
            padding: 14px 22px; border-top: 1px solid #eee; background: #fafafa;
        }
        .price { font-family: 'Playfair Display', serif; font-size: 1.2rem; color: var(--coral); }
        .price small { font-family: 'Lato', sans-serif; font-size: 0.7rem; color: var(--gray); }
        .btn-book {
            background: var(--coral); color: white; padding: 8px 18px;
            border-radius: 20px; text-decoration: none; font-size: 0.8rem; font-weight: 700;
            transition: background 0.2s;
        }
        .btn-book:hover { background: #c1440e; }

        /* JSON output */
        .json-output {
            background: #1e1e2e; color: #cdd6f4; font-family: 'Courier New', monospace;
            font-size: 0.82rem; padding: 28px; border-radius: 12px;
            max-height: 500px; overflow-y: auto; line-height: 1.6;
        }
        .json-key { color: #89b4fa; }
        .json-str { color: #a6e3a1; }
        .json-num { color: #fab387; }

        .info-box {
            background: #e8f4fd; border-left: 4px solid var(--ocean);
            padding: 14px 18px; border-radius: 0 8px 8px 0;
            font-size: 0.85rem; color: #333; margin-bottom: 28px; line-height: 1.6;
        }
        .info-box strong { color: var(--ocean); }

        footer {
            background: var(--deep); color: rgba(255,255,255,0.5);
            text-align: center; padding: 28px; font-size: 0.82rem;
        }
        footer strong { color: var(--ocean-light); }
    </style>
</head>
<body>

<nav>
    <a href="index.html" class="nav-logo">Beach<span>Watch</span></a>
    <ul class="nav-links">
        <li><a href="index.html">Home</a></li>
        <li><a href="resorts.php" class="active">Resorts</a></li>
        <li><a href="reservation.php">Reserve</a></li>
        <li><a href="notifications.php">Notifications</a></li>
    </ul>
</nav>

<div class="page-hero">
    <h1>Beach Resorts</h1>
    <p>Explore all available resorts in Davao Oriental</p>
    <span class="tech-badge">⚙️ Powered by XML + XSLT Transformation + DOM Parsing</span>
</div>

<div class="content">
    <!-- Tab Switcher -->
    <div class="view-tabs">
        <button class="tab-btn active" onclick="showTab('xslt')">🔄 XSLT View (from XML)</button>
        <button class="tab-btn" onclick="showTab('dom')">🌳 DOM Parsed View</button>
        <button class="tab-btn" onclick="showTab('json')">📋 JSON Output</button>
    </div>

    <!-- XSLT Panel -->
    <div class="xslt-panel visible" id="tab-xslt">
        <div class="info-box">
            <strong>XSLT Transformation:</strong> The resort listings below are rendered by transforming
            <code>resorts.xml</code> using the <code>resorts.xsl</code> stylesheet via PHP's
            <code>XSLTProcessor</code>. This converts raw XML data directly into styled HTML.
        </div>
        <?php echo $xsltHTML; ?>
    </div>

    <!-- DOM Parsed Panel -->
    <div class="dom-panel" id="tab-dom">
        <div class="info-box">
            <strong>DOM Parsing:</strong> The resort data below is parsed from <code>resorts.xml</code>
            using PHP's <code>DOMDocument</code> class (tree-based XML parsing). Each resort element
            and its children are traversed using <code>getElementsByTagName()</code>.
        </div>
        <div class="resort-grid">
            <?php foreach ($resorts as $resort): ?>
            <div class="resort-card">
                <div class="card-head">
                    <h3><?= htmlspecialchars($resort['name']) ?></h3>
                    <div class="loc">📍 <?= htmlspecialchars($resort['location']) ?></div>
                </div>
                <div class="card-body">
                    <p class="desc"><?= htmlspecialchars($resort['description']) ?></p>
                    <div class="tags">
                        <?php foreach ($resort['amenities'] as $a): ?>
                            <span class="tag">✓ <?= htmlspecialchars($a) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="card-foot">
                    <div class="price">
                        ₱<?= number_format($resort['price_per_night'], 2) ?>
                        <small>/night</small>
                    </div>
                    <a href="reservation.php?resort_id=<?= $resort['id'] ?>" class="btn-book">Book Now</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- JSON Panel -->
    <div class="dom-panel" id="tab-json">
        <div class="info-box">
            <strong>XML → JSON Conversion:</strong> This demonstrates how the XML data is
            converted to JSON format using DOM parsing. This is useful for API responses or
            JavaScript consumption.
        </div>
        <div class="json-output">
            <pre><?php
                $json = json_encode(['resorts' => $resorts], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                // Syntax highlight
                $json = preg_replace('/"([^"]+)":/', '<span class="json-key">"$1"</span>:', $json);
                $json = preg_replace('/: "([^"]*)"/', ': <span class="json-str">"$1"</span>', $json);
                $json = preg_replace('/: (\d+\.?\d*)/', ': <span class="json-num">$1</span>', $json);
                echo $json;
            ?></pre>
        </div>
    </div>
</div>

<footer>
    <strong>BeachWatch</strong> · ITP 121 Final Project · Davao Oriental State University
</footer>

<script>
function showTab(tab) {
    // Hide all panels
    document.querySelectorAll('.xslt-panel, .dom-panel').forEach(p => p.classList.remove('visible'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));

    // Show target
    const panelId = tab === 'xslt' ? 'tab-xslt' : (tab === 'dom' ? 'tab-dom' : 'tab-json');
    document.getElementById(panelId).classList.add('visible');
    event.currentTarget.classList.add('active');
}
</script>
</body>
</html>
