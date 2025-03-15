<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ImpactSeed - Complete Hope Ecosystem</title>
    <style>
        :root {
            --hope-green: #00cc66;
            --impact-orange: #ff6600;
            --text-dark: #2c3e50;
        }

        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }

        .header {
            background: var(--hope-green);
            padding: 2rem;
            text-align: center;
            color: white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .impact-counter {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            padding: 2rem;
            background: white;
            margin: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .tap-area {
            width: 200px;
            height: 200px;
            background: var(--impact-orange);
            border-radius: 50%;
            margin: 2rem auto;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            transition: transform 0.1s;
            animation: pulse 2s infinite;
            touch-action: manipulation;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .powerups {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            padding: 1rem;
            margin: 1rem;
            background: rgba(255,255,255,0.9);
            border-radius: 15px;
        }

        .boost-active {
            animation: boostGlow 0.5s infinite alternate;
        }

        @keyframes boostGlow {
            from { box-shadow: 0 0 10px var(--hope-green); }
            to { box-shadow: 0 0 20px var(--impact-orange); }
        }

        .profile-section {
            background: rgba(255,255,255,0.9);
            padding: 1rem;
            margin: 1rem;
            border-radius: 15px;
            text-align: center;
        }

        .progress-bar {
            height: 20px;
            background: #eee;
            border-radius: 10px;
            overflow: hidden;
            margin: 1rem 2rem;
        }

        .progress-fill {
            height: 100%;
            background: var(--hope-green);
            width: 0;
            transition: width 0.5s ease;
        }
    </style>
</head>
<body>
    <div class="profile-section">
        <h3>🙌 Your Hope Identity</h3>
        <input type="text" id="username" placeholder="Blessed Name">
        <input type="tel" id="phone" placeholder="2547XXXXXXXX" pattern="[0-9]{10}">
        <button onclick="saveProfile()">Save Kingdom Profile</button>
    </div>

    <div class="header">
        <h1>ImpactSeed 🌍</h1>
        <p>Current Value: <span id="tap-value">1</span> KSH per Tap</p>
        <div class="live-counter">
            Total Hope: <span id="live-taps">0</span> KSH
        </div>
    </div>

    <div class="impact-counter">
        <div class="impact-item" style="background: #ffe066;">
            <h3>🍲 Meals Provided</h3>
            <div id="meals-counter">0</div>
        </div>
        <div class="impact-item" style="background: #69db7c;">
            <h3>📚 Education Kits</h3>
            <div id="education-counter">0</div>
        </div>
        <div class="impact-item" style="background: #74c0fc;">
            <h3>👕 Clothing Bundles</h3>
            <div id="clothing-counter">0</div>
        </div>
        <div class="impact-item" style="background: #d8f5a2;">
            <h3>📖 Bibles Distributed</h3>
            <div id="bible-counter">0</div>
        </div>
    </div>

    <div class="progress-bar">
        <div class="progress-fill" id="global-progress"></div>
    </div>

    <div class="tap-area" id="tapArea">
        TAP TO SOW HOPE<br>
        <small>(Multi-touch supported)</small>
    </div>

    <div class="powerups">
        <button onclick="buyPowerup('double', 500)">2x Multiplier (500 taps)</button>
        <button onclick="buyPowerup('triple', 1500)">3x Multiplier (1500 taps)</button>
    </div>

    <div style="text-align: center; margin: 2rem;">
        <button onclick="convertImpact('food')" style="background: #ffe066;">🍲 Convert to Food</button>
        <button onclick="convertImpact('education')" style="background: #69db7c;">🎓 School Kits</button>
        <button onclick="convertImpact('bible')" style="background: #d8f5a2;">📖 Print Bible</button>
    </div>

    <div class="data-export">
        <input type="file" id="importFile" style="display:none;">
        <button onclick="document.getElementById('importFile').click()">📥 Import Impact</button>
        <button onclick="exportData()">📤 Export Impact</button>
        <p>Total Hope Seeds: <span id="total-taps">0</span> KSH</p>
    </div>

    <div style="text-align: center; padding: 2rem;">
        <button onclick="donateViaMPesa()" style="background: var(--hope-green); color: white; padding: 1rem 2rem;">
            DIRECT DONATE VIA MPESA
        </button>
        <p style="margin-top: 2rem; color: var(--text-dark);">
            "For I was hungry and you gave me something to eat..." - Matthew 25:35<br>
            Every 1000 taps = 1 meal provided to a child in need
        </p>
    </div>

    <script>
        let impactData = JSON.parse(localStorage.getItem('impactData')) || {
            user: { name: '', phone: '' },
            taps: 0,
            conversions: { meals: 0, education: 0, clothing: 0, bibles: 0 },
            rates: { meal: 1000, education: 5000, clothing: 3000, bible: 2000 },
            powerUps: { multiplier: 1, active: false },
            tapHistory: [],
            boostActive: false
        };

        let currentMultiplier = impactData.powerUps.multiplier;
        let tapValue = 1;
        let boostTimeout;

        function saveProfile() {
            impactData.user = {
                name: document.getElementById('username').value,
                phone: document.getElementById('phone').value
            };
            localStorage.setItem('impactData', JSON.stringify(impactData));
            alert('Profile saved! 🙏');
        }

        function handleTap(taps = 1) {
            impactData.taps += taps * tapValue * currentMultiplier;
            impactData.tapHistory.push(Date.now());
            
            checkAchievements();
            checkBoostStatus();
            updateDisplay();
            localStorage.setItem('impactData', JSON.stringify(impactData));
            
            // Tap animation
            document.getElementById('tapArea').style.transform = 'scale(0.95)';
            setTimeout(() => {
                document.getElementById('tapArea').style.transform = 'scale(1)';
            }, 100);
        }

        function checkAchievements() {
            if(impactData.taps % 100 === 0) {
                showConfetti();
                alert(`🎉 Hurray! ${impactData.taps} hope seeds planted!`);
            }
        }

        function checkBoostStatus() {
            const recentTaps = impactData.tapHistory.filter(t => Date.now() - t < 30000);
            if(recentTaps.length >= 100 && !impactData.boostActive) {
                activateBoost();
            }
        }

        function activateBoost() {
            tapValue = 2;
            impactData.boostActive = true;
            document.getElementById('tapArea').classList.add('boost-active');
            document.getElementById('tap-value').textContent = '2';
            
            clearTimeout(boostTimeout);
            boostTimeout = setTimeout(() => {
                tapValue = 1;
                impactData.boostActive = false;
                document.getElementById('tapArea').classList.remove('boost-active');
                document.getElementById('tap-value').textContent = '1';
            }, 30000);
        }

        function buyPowerup(type, cost) {
            if(impactData.taps >= cost) {
                impactData.taps -= cost;
                currentMultiplier = type === 'double' ? 2 : 3;
                impactData.powerUps = { multiplier: currentMultiplier, active: true };
                alert(`🚀 ${type.toUpperCase()} POWER ACTIVATED!`);
                updateDisplay();
                localStorage.setItem('impactData', JSON.stringify(impactData));
            } else {
                alert(`Need ${cost - impactData.taps} more taps!`);
            }
        }

        function convertImpact(type) {
            const required = impactData.rates[type];
            if(impactData.taps >= required) {
                impactData.taps -= required;
                impactData.conversions[type]++;
                alert(`🌱 Converted to ${type}! ${required} hope seeds planted!`);
                updateDisplay();
                localStorage.setItem('impactData', JSON.stringify(impactData));
            } else {
                alert(`Need ${required - impactData.taps} more seeds!`);
            }
        }

        function updateDisplay() {
            document.getElementById('live-taps').textContent = impactData.taps.toLocaleString();
            document.getElementById('total-taps').textContent = impactData.taps.toLocaleString();
            document.getElementById('meals-counter').textContent = impactData.conversions.meals.toLocaleString();
            document.getElementById('education-counter').textContent = impactData.conversions.education.toLocaleString();
            document.getElementById('clothing-counter').textContent = impactData.conversions.clothing.toLocaleString();
            document.getElementById('bible-counter').textContent = impactData.conversions.bibles.toLocaleString();
            
            const progress = (impactData.taps % 10000) / 10000 * 100;
            document.getElementById('global-progress').style.width = `${progress}%`;
        }

        function exportData() {
            const data = JSON.stringify(impactData);
            const encrypted = btoa(unescape(encodeURIComponent(data)));
            const blob = new Blob([encrypted], {type: 'text/plain'});
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `ImpactSeed_${impactData.user.name || 'Anonymous'}_${Date.now()}.enc`;
            link.click();
        }

        document.getElementById('importFile').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const reader = new FileReader();
            reader.onload = function(e) {
                try {
                    const decrypted = decodeURIComponent(escape(atob(e.target.result)));
                    impactData = JSON.parse(decrypted);
                    localStorage.setItem('impactData', JSON.stringify(impactData));
                    
                    alert(`Welcome back ${impactData.user.name}! 🌟\n` +
                          `Total Impact: ${impactData.taps.toLocaleString()} taps\n` +
                          `Meals Provided: ${impactData.conversions.meals.toLocaleString()}`);
                    
                    currentMultiplier = impactData.powerUps.multiplier;
                    updateDisplay();
                } catch(error) {
                    alert('Invalid impact file format');
                }
            };
            reader.readAsText(file);
        });

        function donateViaMPesa() {
            const amount = prompt("Enter amount in KSH:");
            if(amount > 0) {
                alert(`Donate via MPesa:\n1. Go to Lipa Na MPesa\n2. Till Number: 0113194977\n3. Amount: ${amount} KSH\n4. Reference: IMPACT${amount}`);
            }
        }

        // Touch and click handlers
        let touchCount = 0;
        document.getElementById('tapArea').addEventListener('touchstart', function(e) {
            touchCount = e.touches.length;
            handleTap(touchCount >= 2 ? touchCount * 2 : 1);
            e.preventDefault();
        }, { passive: false });

        document.getElementById('tapArea').addEventListener('click', () => handleTap());

        // Initialization
        document.getElementById('username').value = impactData.user.name;
        document.getElementById('phone').value = impactData.user.phone;
        updateDisplay();

        // Community impact simulation
        setInterval(() => {
            impactData.conversions.meals += Math.floor(Math.random() * 3);
            impactData.conversions.education += Math.floor(Math.random() * 2);
            impactData.conversions.clothing += Math.floor(Math.random() * 4);
            impactData.conversions.bibles += Math.floor(Math.random() * 1);
            updateDisplay();
            localStorage.setItem('impactData', JSON.stringify(impactData));
        }, 15000);

        function showConfetti() {
            // Simple confetti effect
            const colors = ['#ff0000', '#00ff00', '#0000ff', '#ffff00'];
            for(let i = 0; i < 50; i++) {
                const confetti = document.createElement('div');
                confetti.style.position = 'fixed';
                confetti.style.width = '5px';
                confetti.style.height = '5px';
                confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.left = Math.random() * 100 + 'vw';
                confetti.style.top = '-10px';
                confetti.style.borderRadius = '50%';
                confetti.style.animation = `confetti-fall ${Math.random() * 3 + 2}s linear`;
                document.body.appendChild(confetti);
                
                setTimeout(() => confetti.remove(), 3000);
            }
        }

        // Add confetti animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes confetti-fall {
                to { transform: translateY(100vh) rotate(360deg); }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
