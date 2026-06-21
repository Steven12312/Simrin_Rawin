<?php
require_once 'db.php';
require_once 'guest_helpers.php';
$eventsConfig = require 'config.events.php';

$guest_hash = $_GET['g'] ?? '';
$guest = null;

if ($guest_hash) {
    $stmt = $pdo->prepare("SELECT * FROM guests WHERE guest_hash = ?");
    $stmt->execute([$guest_hash]);
    $guest = $stmt->fetch();
}

if (!$guest) {
    die("Guest not found.");
}

$guest_name = build_guest_display_name($guest);

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$base = rtrim($protocol . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']), '/');
$site_url = $base . "/index.php?g=" . $guest_hash;

$invited_events_keys = $guest['invited_events'] ? json_decode($guest['invited_events'], true) : [];
$invited_events = [];
foreach ($invited_events_keys as $key) {
    if (isset($eventsConfig['events'][$key])) {
        $invited_events[] = $eventsConfig['events'][$key];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitation Card - Simrin & Rawin</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        body { background: #fdfaf7; min-height: 100vh; display: flex; align-items: center; justify-content: center; margin: 0; padding: 20px; flex-direction: column; }
        .card-container {
            width: 100%;
            max-width: 500px;
            background: white;
            border-radius: 40px;
            overflow: hidden;
            box-shadow: 0 30px 60px rgba(0,0,0,0.12);
            text-align: center;
            border: 1px solid rgba(0,0,0,0.05);
            position: relative;
            margin-bottom: 20px;
        }
        .card-hero {
            height: 250px;
            background: url('images/hero.jpg?v=4') no-repeat center 15% / cover;
            position: relative;
        }
        .card-hero::after {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(to bottom, transparent, white);
        }
        .religious-icon {
            width: 100px;
            height: 100px;
            margin: -50px auto 20px;
            position: relative;
            z-index: 10;
            background: white;
            border-radius: 50%;
            padding: 0;
            overflow: hidden;
            border: 3px solid var(--secondary);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .religious-icon img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }
        .card-content { padding: 0 30px 20px; }
        .card-title { font-size: 1rem; color: var(--secondary); letter-spacing: 3px; text-transform: uppercase; margin-bottom: 5px; }
        .guest-title { font-size: 1.6rem; color: var(--primary); margin: 10px 0; font-weight: 700; }
        .card-main { font-size: 2.2rem; margin: 10px 0; line-height: 1.1; }
        .card-ornament { width: 60px; height: 2px; background: var(--secondary); margin: 15px auto; opacity: 0.3; }
        .card-link { display: inline-block; padding: 15px 30px; background: var(--gold-gradient); color: white; border-radius: 50px; text-decoration: none; font-weight: 700; box-shadow: 0 10px 20px rgba(184, 134, 11, 0.2); }
        .event-block {
            margin-top: 15px; 
            background: #fcf4e8; 
            padding: 15px; 
            border-radius: 12px; 
            border: 1px solid #eee;
            text-align: center;
        }
        .event-title {
            color: #700000; font-weight: 800; font-size: 1.2rem; margin-bottom: 5px; font-family: 'Playfair Display', serif;
        }
        .event-date {
            color: #333; font-weight: 600; font-size: 0.95rem; margin-bottom: 10px;
        }
        .event-location {
            color: #444; font-size: 0.95rem; font-weight: 600; margin-bottom: 2px;
        }
        .event-address {
            color: #666; font-size: 0.85rem; margin-bottom: 10px;
        }
    </style>
</head>
<body>

    <div style="position: fixed; top: 20px; right: 20px; z-index: 100;">
        <button id="downloadBtn" class="btn-luxury" style="padding: 10px 20px; font-size: 0.9rem;">Download as PNG Image</button>
    </div>

    <!-- Removed fade-in class to prevent 'washed out' capture during animation -->
    <div id="capture" class="card-container" style="background: #fdfaf7 !important; border: 1px solid #ddd; opacity: 1 !important; transform: none !important; width: 450px !important;">
        <div class="card-hero" style="background: #fdfaf7 url('images/hero.jpg?v=4') no-repeat center 15% / cover; height: 350px; border-bottom: 2px solid #decba4;"></div>
        
        <div class="religious-icon" style="margin-top: -50px; width: 100px; height: 100px; background: #ffffff !important; opacity: 1 !important;">
            <img src="images/guru_nanak.png?v=1" alt="Guru Nanak" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
        </div>

        <div class="card-content" style="background: #fdfaf7 !important; padding: 5px 30px 20px; opacity: 1 !important;">
            <p style="color: #555555 !important; font-size: 1.1rem; margin-bottom: 2px; font-weight: 600; margin-top: 5px;">Dear</p>
            <div class="guest-title" style="color: #000000 !important; font-size: 2.2rem; font-weight: 900; line-height: 1.1; letter-spacing: -0.5px;"><?php echo htmlspecialchars($guest_name); ?></div>
            
            <div style="width: 40px; height: 1px; background: #8a6d3b; margin: 10px auto; opacity: 0.6;"></div>

            <div class="card-title serif" style="color: #8a6d3b !important; margin-top: 0px; font-weight: 700; letter-spacing: 1px; font-size: 0.95rem; text-transform: uppercase;">You are invited to the Wedding Celebration of</div>
            
            <h1 class="card-main serif" style="color: #700000 !important; font-size: 2.3rem; margin: 5px 0; line-height: 1; font-weight: 800;">
                Simrin<br>
                <span style="font-size: 0.5em; font-family: 'Outfit'; display: block; margin: 2px 0; color: #333333 !important; font-weight: 400;">&amp;</span>
                Rawin
            </h1>
            
            <div style="width: 100%; height: 1px; background: #decba4; margin: 10px 0; opacity: 0.5;"></div>
            
            <p style="margin-bottom: 15px; color: #222222 !important; font-size: 1rem; font-weight: 700; line-height: 1.3;">We look forward to celebrating our special days with you!</p>
            
            <?php if (empty($invited_events)): ?>
                <p style="color: #666; font-style: italic;">No specific events assigned yet.</p>
            <?php else: ?>
                <?php foreach ($invited_events as $event): ?>
                <div class="event-block">
                    <div class="event-title"><?php echo htmlspecialchars($event['title']); ?></div>
                    <div class="event-date"><?php echo htmlspecialchars($event['date']); ?></div>
                    <div class="event-location"><?php echo htmlspecialchars($event['location_name']); ?></div>
                    <div class="event-address"><?php echo htmlspecialchars($event['address']); ?></div>
                    <?php if (!empty($event['time'])): ?>
                    <div style="color: var(--secondary); font-size: 0.85rem; font-weight: 700; margin-top: 5px;"><?php echo htmlspecialchars($event['time']); ?></div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- This link is ONLY for the web view, ignore for PNG -->
            <a href="<?php echo $site_url; ?>" class="card-link" data-html2canvas-ignore style="margin-top: 40px;">View Full Details & RSVP</a>
        </div>
    </div>

    <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
    <script>
        document.getElementById('downloadBtn').onclick = function() {
            generateImage(canvas => {
                const link = document.createElement('a');
                link.download = 'Invitation_<?php echo str_replace(' ', '_', $guest_name); ?>.jpg';
                link.href = canvas.toDataURL("image/jpeg", 0.9);
                link.click();
                this.innerHTML = "Download as PNG Image";
            });
        };

        function generateImage(callback) {
            const captureArea = document.querySelector("#capture");
            html2canvas(captureArea, {
                useCORS: true,
                scale: 2,
                backgroundColor: "#fdfaf7",
                logging: false,
                removeContainer: true
            }).then(callback);
        }

        // Parent window communication (for Admin Dashboard)
        window.addEventListener('message', function(event) {
            if (event.data === 'capture_card') {
                generateImage(canvas => {
                    const dataUrl = canvas.toDataURL("image/jpeg", 0.9);
                    window.parent.postMessage({ type: 'card_captured', image: dataUrl }, '*');
                });
            }
        });
    </script>

</body>
</html>
