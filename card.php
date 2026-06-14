<?php
require_once 'db.php';
require_once 'guest_helpers.php';

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitation Card - Saymen & Disha</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        body { background: #fdfaf7; height: 100vh; display: flex; align-items: center; justify-content: center; margin: 0; padding: 20px; }
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
        }
        .card-hero {
            height: 250px;
            background: url('images/hero.jpg?v=4') no-repeat center center / cover;
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
    </style>
</head>
<body>

    <div style="position: fixed; top: 20px; right: 20px; z-index: 100;">
        <button id="downloadBtn" class="btn-luxury" style="padding: 10px 20px; font-size: 0.9rem;">Download as PNG Image</button>
    </div>

    <!-- Removed fade-in class to prevent 'washed out' capture during animation -->
    <div id="capture" class="card-container" style="background: #fdfaf7 !important; border: 1px solid #ddd; opacity: 1 !important; transform: none !important; width: 450px !important;">
        <div class="card-hero" style="background: #fdfaf7 url('images/story.jpg?v=3') no-repeat center 25% / cover; height: 350px; border-bottom: 2px solid #decba4;"></div>
        
        <div class="religious-icon" style="margin-top: -40px; width: 80px; height: 80px; background: #ffffff !important; opacity: 1 !important;">
            <img src="images/guru_nanak.png?v=1" alt="Guru Nanak" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
        </div>

        <div class="card-content" style="background: #fdfaf7 !important; padding: 5px 30px 20px; opacity: 1 !important;">
            <p style="color: #555555 !important; font-size: 1.1rem; margin-bottom: 2px; font-weight: 600; margin-top: 5px;">Dear</p>
            <div class="guest-title" style="color: #000000 !important; font-size: 2.2rem; font-weight: 900; line-height: 1.1; letter-spacing: -0.5px;"><?php echo htmlspecialchars($guest_name); ?></div>
            
            <div style="width: 40px; height: 1px; background: #8a6d3b; margin: 10px auto; opacity: 0.6;"></div>

            <div class="card-title serif" style="color: #8a6d3b !important; margin-top: 0px; font-weight: 700; letter-spacing: 3px; font-size: 1rem;">You are Invited</div>
            
            <h1 class="card-main serif" style="color: #700000 !important; font-size: 2.3rem; margin: 5px 0; line-height: 1; font-weight: 800;">
                Saymen<br>
                <span style="font-size: 0.5em; font-family: 'Outfit'; display: block; margin: 2px 0; color: #333333 !important; font-weight: 400;">&amp;</span>
                Disha
            </h1>
            
            <div style="width: 100%; height: 1px; background: #decba4; margin: 10px 0; opacity: 0.5;"></div>
            
            <p style="margin-bottom: 10px; color: #222222 !important; font-size: 1rem; font-weight: 700; line-height: 1.3;">We look forward to celebrating our special day with you!</p>
            
            <div style="margin-top: 10px; background: #fcf4e8; padding: 10px; border-radius: 12px; border: 1px solid #eee;">
                <p style="color: #700000 !important; font-weight: 800; font-size: 1.1rem; margin-bottom: 2px;">Royal Stage</p>
                <p style="color: #444444 !important; font-size: 0.95rem; margin-bottom: 5px; font-weight: 600;">Im Hegen 16, 22113 Glinde</p>
                
                <a href="https://www.google.com/maps/search/?api=1&query=Royal+Stage+Im+Hegen+16+22113+Glinde" 
                   data-html2canvas-ignore
                   style="color: var(--secondary); font-weight: 700; text-decoration: underline; font-size: 0.95rem;">
                   Open in Maps / Navigation
                </a>
            </div>


            <!-- This link is ONLY for the web view, ignore for PNG -->
            <a href="<?php echo $site_url; ?>" class="card-link" data-html2canvas-ignore style="margin-top: 40px;">View Full Invitation & RSVP</a>
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
