<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

    <xsl:output method="html" encoding="UTF-8" indent="yes"/>

    <!-- Root template -->
    <xsl:template match="/">
        <html lang="en">
        <head>
            <meta charset="UTF-8"/>
            <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
            <title>BeachWatch — Resort Listings</title>
            <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&amp;family=Lato:wght@300;400;600&amp;display=swap" rel="stylesheet"/>
            <style>
                :root {
                    --ocean: #0077b6;
                    --sand: #f4e1c0;
                    --coral: #e76f51;
                    --deep: #023e8a;
                    --white: #ffffff;
                    --gray: #6c757d;
                }
                * { box-sizing: border-box; margin: 0; padding: 0; }
                body {
                    font-family: 'Lato', sans-serif;
                    background: linear-gradient(135deg, #e0f7fa 0%, #f4e1c0 100%);
                    min-height: 100vh;
                    padding: 40px 20px;
                }
                header {
                    text-align: center;
                    margin-bottom: 50px;
                }
                header h1 {
                    font-family: 'Playfair Display', serif;
                    font-size: 2.8rem;
                    color: var(--deep);
                    letter-spacing: 2px;
                }
                header p {
                    color: var(--gray);
                    font-size: 1rem;
                    margin-top: 8px;
                }
                .badge {
                    display: inline-block;
                    background: var(--ocean);
                    color: white;
                    font-size: 0.7rem;
                    padding: 3px 10px;
                    border-radius: 20px;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                    margin-bottom: 16px;
                }
                .resort-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
                    gap: 30px;
                    max-width: 1200px;
                    margin: 0 auto;
                }
                .resort-card {
                    background: var(--white);
                    border-radius: 16px;
                    box-shadow: 0 4px 24px rgba(0,119,182,0.10);
                    overflow: hidden;
                    transition: transform 0.3s, box-shadow 0.3s;
                }
                .resort-card:hover {
                    transform: translateY(-6px);
                    box-shadow: 0 12px 36px rgba(0,119,182,0.18);
                }
                .card-header {
                    background: linear-gradient(135deg, var(--ocean), var(--deep));
                    padding: 28px 24px 20px;
                    color: white;
                }
                .card-header h2 {
                    font-family: 'Playfair Display', serif;
                    font-size: 1.3rem;
                    margin-bottom: 6px;
                }
                .card-header .location {
                    font-size: 0.82rem;
                    opacity: 0.85;
                    display: flex;
                    align-items: center;
                    gap: 4px;
                }
                .card-body { padding: 20px 24px; }
                .description {
                    color: #444;
                    font-size: 0.9rem;
                    line-height: 1.6;
                    margin-bottom: 16px;
                }
                .amenities {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 6px;
                    margin-bottom: 18px;
                }
                .amenity-tag {
                    background: #e8f4fd;
                    color: var(--ocean);
                    font-size: 0.72rem;
                    padding: 4px 10px;
                    border-radius: 20px;
                    font-weight: 600;
                }
                .card-footer {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 16px 24px;
                    background: #f8f9fa;
                    border-top: 1px solid #eee;
                }
                .price {
                    font-family: 'Playfair Display', serif;
                    font-size: 1.3rem;
                    color: var(--coral);
                    font-weight: 700;
                }
                .price span {
                    font-family: 'Lato', sans-serif;
                    font-size: 0.75rem;
                    color: var(--gray);
                    font-weight: 400;
                }
                .capacity {
                    font-size: 0.8rem;
                    color: var(--gray);
                }
                .btn-book {
                    display: block;
                    text-align: center;
                    background: var(--coral);
                    color: white;
                    text-decoration: none;
                    padding: 10px 0;
                    border-radius: 0 0 16px 16px;
                    font-weight: 600;
                    font-size: 0.9rem;
                    letter-spacing: 0.5px;
                    transition: background 0.2s;
                }
                .btn-book:hover { background: #c1440e; }
                footer {
                    text-align: center;
                    margin-top: 60px;
                    color: var(--gray);
                    font-size: 0.85rem;
                }
            </style>
        </head>
        <body>
            <header>
                <span class="badge">Davao Oriental Tourism</span>
                <h1>🌊 BeachWatch Resorts</h1>
                <p>Discover the finest beach resorts in Davao Oriental</p>
            </header>

            <div class="resort-grid">
                <xsl:apply-templates select="resorts/resort"/>
            </div>

            <footer>
                <p>&#169; 2025 BeachWatch — Beach Resort Reservation System | ITP 121 Final Project</p>
                <p style="margin-top:6px;">Generated from XML via XSLT Transformation</p>
            </footer>
        </body>
        </html>
    </xsl:template>

    <!-- Resort card template -->
    <xsl:template match="resort">
        <div class="resort-card">
            <div class="card-header">
                <h2><xsl:value-of select="name"/></h2>
                <div class="location">
                    📍 <xsl:value-of select="location"/>
                </div>
            </div>
            <div class="card-body">
                <p class="description"><xsl:value-of select="description"/></p>
                <div class="amenities">
                    <xsl:for-each select="amenities/amenity">
                        <span class="amenity-tag">✓ <xsl:value-of select="."/></span>
                    </xsl:for-each>
                </div>
            </div>
            <div class="card-footer">
                <div class="price">
                    ₱<xsl:value-of select="price_per_night"/>
                    <span>/night</span>
                </div>
                <div class="capacity">
                    👥 Up to <xsl:value-of select="capacity"/> guests
                </div>
            </div>
            <a href="reservation.php?resort_id={@id}" class="btn-book">
                Book Now →
            </a>
        </div>
    </xsl:template>

</xsl:stylesheet>
