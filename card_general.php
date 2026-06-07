<?php
require_once 'guest_helpers.php';

// Neutral card for groups - Showing full schedule and family details
$guest_name = "Family & Friends";

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$base = rtrim($protocol . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']), '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitation - Saymen & Disha</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        body { background: #fdfaf7; min-height: 100vh; display: flex; align-items: center; justify-content: center; margin: 0; padding: 40px 20px; font-family: 'Outfit', sans-serif; }
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
            background: #fdfaf7 url('images/story.jpg?v=3') no-repeat center 25% / cover;
            position: relative;
            border-bottom: 2px solid #decba4;
        }
        .religious-icon {
            width: 60px;
            height: 60px;
            margin: -30px auto 5px;
            position: relative;
            z-index: 10;
            background: white;
            border-radius: 50%;
            padding: 0;
            overflow: hidden;
            border: 2px solid #decba4;
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        }
        .religious-icon img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }
        .card-content { padding: 5px 30px 20px; background: #fdfaf7; }
        .serif { font-family: 'Playfair Display', serif; }
        
        .blessings {
            font-size: 0.7rem;
            font-style: italic;
            color: #8a6d3b;
            line-height: 1.3;
            margin-bottom: 10px;
        }
        
        .parent-info {
            font-size: 0.75rem;
            color: #666;
            margin-top: 5px;
            line-height: 1.2;
        }

        .schedule-section {
            text-align: left;
            margin-top: 20px;
        }
        .day-title {
            color: #700000;
            font-weight: 800;
            font-size: 1rem;
            border-bottom: 1px solid #decba4;
            padding-bottom: 3px;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .day-date { font-size: 0.75rem; color: #8a6d3b; font-weight: 700; letter-spacing: 1px; }
        
        .event-row {
            display: flex;
            margin-bottom: 4px;
            font-size: 0.85rem;
            line-height: 1.2;
        }
        .event-time {
            font-weight: 700;
            color: #8a6d3b;
            min-width: 70px;
        }
        .event-desc {
            color: #333;
            font-weight: 500;
        }
        
        .venue-box {
            margin-top: 20px;
            background: white;
            padding: 12px;
            border-radius: 15px;
            border: 1px solid #eee;
            box-shadow: 0 5px 15px rgba(0,0,0,0.02);
        }
    </style>
</head>
<body>

    <div style="position: fixed; top: 20px; right: 20px; z-index: 100;">
        <button id="downloadBtn" class="btn-luxury" style="padding: 10px 20px; font-size: 0.8rem; background: #000; color: #fff;">Download Invitation Image</button>
    </div>

    <div id="capture" class="card-container" style="background: #fdfaf7 !important; width: 450px !important;">
        <div class="card-hero"></div>
        
        <div class="religious-icon" style="width: 70px; height: 70px; margin-top: -35px;">
            <img src="images/guru_nanak.png?v=1" alt="Guru Nanak" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
        </div>

        <div class="card-content">
            <div class="blessings">
                With the blessings of our Grandparents<br>
                <strong>Late Sdr. Roshan Kapoor & Late Smt. Murti Kaur Kapoor</strong><br>
                <strong>Mr. Manor Singh Kapoor & Late Smt. Pushpa Kaur Kapoor</strong><br>
                <strong>Mr. Babulal Arenja & Mrs. Geeta Arenja</strong><br>
                <strong>Mr. Narayan Dass Jagga & Mrs. Shaku Jagga</strong>
            </div>

            <h1 class="serif" style="color: #700000; font-size: 2.2rem; margin: 0; line-height: 1;">
                Saymen
            </h1>
            <div class="parent-info">
                Son of <strong>Rajinder Singh & Dimple Kapoor</strong>
            </div>
            
            <div style="font-size: 0.9rem; font-family: 'Outfit'; margin: 5px 0; color: #333; font-weight: 400;">&amp;</div>
            
            <h1 class="serif" style="color: #700000; font-size: 2.2rem; margin: 0; line-height: 1;">
                Disha
            </h1>
            <div class="parent-info">
                Daughter of <strong>Mr. Hitesh & Mrs. Harsha Arenja</strong>
            </div>
            
            <div style="width: 40px; height: 1px; background: #decba4; margin: 15px auto;"></div>

            <div class="schedule-section">
                <div class="day-title">
                    <span>Engagement Party</span>
                    <span class="day-date">25. JUN 2026</span>
                </div>
                <div class="event-row"><span class="event-time">04:00 PM</span> <span class="event-desc">Welcome of Guests</span></div>
                <div class="event-row"><span class="event-time">05:00 PM</span> <span class="event-desc">Light Music & Cocktails</span></div>
                <div class="event-row"><span class="event-time">06:00 PM</span> <span class="event-desc">Nashta</span></div>
                <div class="event-row"><span class="event-time">09:00 PM</span> <span class="event-desc">Dinner</span></div>
            </div>

            <div class="venue-box">
                <p style="color: #700000; font-weight: 800; font-size: 1rem; margin: 0 0 2px;">Royal Stage</p>
                <p style="color: #444; font-size: 0.85rem; font-weight: 600; margin: 0;">Im Hegen 16, 22113 Glinde</p>
            </div>
        </div>
    </div>

    <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
    <script>
        document.getElementById('downloadBtn').onclick = function() {
            generateImage(canvas => {
                const link = document.createElement('a');
                link.download = 'Saymen_Disha_Full_Invitation.jpg';
                link.href = canvas.toDataURL("image/jpeg", 0.95);
                link.click();
                this.innerHTML = "Download Invitation Image";
            });
        };

        function generateImage(callback) {
            const captureArea = document.querySelector("#capture");
            html2canvas(captureArea, {
                useCORS: true,
                scale: 3,
                backgroundColor: "#fdfaf7",
                logging: false
            }).then(callback);
        }

        // Parent window communication (for Admin Dashboard)
        window.addEventListener('message', function(event) {
            if (event.data === 'capture_card') {
                generateImage(canvas => {
                    const dataUrl = canvas.toDataURL("image/jpeg", 0.95);
                    window.parent.postMessage({ type: 'card_captured', image: dataUrl }, '*');
                });
            }
        });
    </script>

</body>
</html>
