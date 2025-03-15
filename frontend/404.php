<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page non trouvée</title>
    <link rel="stylesheet" href="/HACKATHON_ESGIS/public/css/dist/output.css">
    <style>
        .animate-float {
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }

        .glitch {
            position: relative;
            animation: glitch 1s linear infinite;
            text-shadow: 0 0 20px rgba(66, 220, 219, 0.8);
            color: #00fff2;
        }

        @keyframes glitch {
            2%, 64% { transform: translate(2px,0) skew(0deg); }
            4%, 60% { transform: translate(-2px,0) skew(0deg); }
            62% { transform: translate(0,0) skew(5deg); }
        }

        .glitch:before,
        .glitch:after {
            content: '404';
            position: absolute;
            left: 0;
        }

        .glitch:before {
            animation: glitchTop 1s linear infinite;
            clip-path: polygon(0 0, 100% 0, 100% 33%, 0 33%);
            -webkit-clip-path: polygon(0 0, 100% 0, 100% 33%, 0 33%);
        }

        @keyframes glitchTop {
            2%, 64% { transform: translate(2px,-2px); }
            4%, 60% { transform: translate(-2px,2px); }
            62% { transform: translate(13px,-1px) skew(-13deg); }
        }

        .glitch:after {
            animation: glitchBottom 1.5s linear infinite;
            clip-path: polygon(0 67%, 100% 67%, 100% 100%, 0 100%);
            -webkit-clip-path: polygon(0 67%, 100% 67%, 100% 100%, 0 100%);
        }

        @keyframes glitchBottom {
            2%, 64% { transform: translate(-2px,0); }
            4%, 60% { transform: translate(-2px,0); }
            62% { transform: translate(-22px,5px) skew(21deg); }
        }

        .bg-matrix {
            background: linear-gradient(rgba(0, 0, 0, 0.95), rgba(0, 0, 0, 0.97)),
                        linear-gradient(90deg, rgba(0, 255, 235, 0.1) 1px, transparent 1px),
                        linear-gradient(rgba(0, 255, 235, 0.1) 1px, transparent 1px);
            background-size: 100% 100%, 20px 20px, 20px 20px;
            animation: matrixBg 20s linear infinite;
            position: relative;
            overflow: hidden;
        }

        @keyframes matrixBg {
            from { background-position: 0 0, 0 0, 0 0; }
            to { background-position: 0 0, -400px 0, 0 -400px; }
        }

        .neon-border {
            box-shadow: 0 0 10px rgba(0, 255, 242, 0.5),
                        0 0 20px rgba(0, 255, 242, 0.3),
                        0 0 30px rgba(0, 255, 242, 0.2),
                        inset 0 0 30px rgba(0, 255, 242, 0.1);
            transition: box-shadow 0.3s ease;
        }
        
        .neon-border:hover {
            box-shadow: 0 0 15px rgba(0, 255, 242, 0.6),
                        0 0 30px rgba(0, 255, 242, 0.4),
                        0 0 45px rgba(0, 255, 242, 0.3),
                        inset 0 0 40px rgba(0, 255, 242, 0.2);
        }

        .circuit-lines {
            background-image: 
                radial-gradient(circle at 100% 100%, rgba(0, 255, 242, 0.1) 0, rgba(0, 255, 242, 0) 20px),
                radial-gradient(circle at 0% 100%, rgba(0, 255, 242, 0.1) 0, rgba(0, 255, 242, 0) 20px),
                radial-gradient(circle at 100% 0%, rgba(0, 255, 242, 0.1) 0, rgba(0, 255, 242, 0) 20px),
                radial-gradient(circle at 0% 0%, rgba(0, 255, 242, 0.1) 0, rgba(0, 255, 242, 0) 20px);
        }
        
        .circuit-lines::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: 
                linear-gradient(90deg, transparent 98%, rgba(0, 255, 242, 0.3) 2%),
                linear-gradient(transparent 98%, rgba(0, 255, 242, 0.3) 2%);
            background-size: 30px 30px;
            z-index: -1;
        }
        
        .digital-rain {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            overflow: hidden;
            z-index: -1;
        }
        
        .digital-rain span {
            color: #0ff;
            opacity: 0;
            font-size: 1.2rem;
            position: absolute;
            top: -100px;
            animation: fall linear infinite;
        }
        
        @keyframes fall {
            0% {
                opacity: 0;
                transform: translateY(0);
                color: #0ff;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 0.8;
                color: #0aa;
            }
            100% {
                opacity: 0;
                transform: translateY(calc(100vh + 20px));
                color: #088;
            }
        }
        
        .scan-line {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 10px;
            background: linear-gradient(to bottom, 
                transparent, 
                rgba(0, 255, 242, 0.2) 50%, 
                transparent);
            opacity: 0.4;
            z-index: 100;
            animation: scan 4s linear infinite;
        }
        
        @keyframes scan {
            0% { transform: translateY(-10px); }
            100% { transform: translateY(100vh); }
        }
        
        .pulse {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .btn-cyber {
            position: relative;
            background: rgba(0, 0, 0, 0.7);
            color: #0ff;
            border: 1px solid #0ff;
            padding: 0.75rem 1.5rem;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            overflow: hidden;
            z-index: 1;
        }
        
        .btn-cyber:before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0, 255, 242, 0.3), transparent);
            transition: all 0.3s ease;
            z-index: -1;
        }
        
        .btn-cyber:hover:before {
            left: 100%;
        }
        
        .btn-cyber:hover {
            box-shadow: 0 0 10px #0ff;
            text-shadow: 0 0 5px #0ff;
        }
        
        .flicker {
            animation: flicker 3s linear infinite;
        }
        
        @keyframes flicker {
            0%, 19.999%, 22%, 62.999%, 64%, 64.999%, 70%, 100% {
                opacity: 1;
                text-shadow: 0 0 10px #0ff, 0 0 20px #0ff, 0 0 40px #0ff;
            }
            20%, 21.999%, 63%, 63.999%, 65%, 69.999% {
                opacity: 0.4;
                text-shadow: none;
            }
        }
    </style>
</head>
<body class="bg-matrix min-h-screen flex items-center justify-center p-4 select-none">
    <div class="digital-rain" id="digitalRain"></div>
    <div class="scan-line"></div>
    
    <div class="relative">
        <!-- Cercles décoratifs animés -->
        <div class="absolute -z-10 w-96 h-96 bg-cyan-900/30 rounded-full mix-blend-screen filter blur-xl opacity-70 animate-float" style="top: -150px; left: -150px;"></div>
        <div class="absolute -z-10 w-96 h-96 bg-blue-900/30 rounded-full mix-blend-screen filter blur-xl opacity-70 animate-float" style="animation-delay: -1s; top: -100px; right: -150px;"></div>
        <div class="absolute -z-10 w-96 h-96 bg-teal-900/30 rounded-full mix-blend-screen filter blur-xl opacity-70 animate-float" style="animation-delay: -2s; bottom: -150px; left: -100px;"></div>

        <div class="bg-black/80 backdrop-blur-xl p-16 rounded-2xl neon-border circuit-lines w-full max-w-2xl text-center border border-cyan-500/30 pulse">
            <h1 class="glitch text-9xl font-black mb-8">404</h1>
            <p class="text-3xl font-bold text-cyan-400 mb-6 flicker">ERREUR SYSTÈME DÉTECTÉE</p>
            <div class="w-32 h-2 bg-gradient-to-r from-cyan-500 to-transparent mx-auto rounded-full mb-8"></div>
            
            <div class="mb-8 backdrop-blur-sm bg-black/30 p-6 rounded border border-cyan-500/20">
                <div class="flex items-center text-left mb-4">
                    <div class="w-4 h-4 bg-cyan-400 rounded-full mr-4 animate-pulse"></div>
                    <p class="text-xl text-cyan-300/80">Diagnostic: <span class="font-mono text-cyan-400">La matrice a perdu cette page dans ses circuits.</span></p>
                </div>
                <div class="flex items-center text-left">
                    <div class="w-4 h-4 bg-cyan-400 rounded-full mr-4 animate-pulse" style="animation-delay: 0.5s"></div>
                    <p class="text-xl text-cyan-300/80">Solution recommandée: Retournez à la
                        <a href="/HACKATHON_ESGIS/public/" class="text-cyan-400 hover:text-cyan-300 font-medium transition-colors duration-300 hover:underline">source</a>
                    </p>
                </div>
            </div>
            
            <button id="scanBtn" class="btn-cyber mb-8 mx-auto block">SCANNER LE SYSTÈME</button>
            
            <div class="space-y-4">
                <div class="flex justify-center space-x-3">
                    <div class="w-3 h-3 bg-cyan-500 rounded-full animate-ping" style="animation-duration: 2s"></div>
                    <div class="w-3 h-3 bg-cyan-500 rounded-full animate-ping" style="animation-duration: 2.5s; animation-delay: 0.2s"></div>
                    <div class="w-3 h-3 bg-cyan-500 rounded-full animate-ping" style="animation-duration: 3s; animation-delay: 0.4s"></div>
                </div>
                <p class="text-md text-cyan-300/60">Détection d'anomalie? 
                    <a href="/HACKATHON_ESGIS/public/contact" class="text-cyan-400 hover:text-cyan-300 font-medium transition-colors duration-300 hover:underline">Contactez les administrateurs système</a>
                </p>
            </div>
        </div>
    </div>

    <svg class="absolute bottom-0 left-0 w-full h-20 opacity-40" xmlns="http://www.w3.org/2000/svg">
        <defs>
            <linearGradient id="wave" x1="0%" y1="0%" x2="100%" y2="0%">
                <stop offset="0%" stop-color="#0ff" stop-opacity="0.2" />
                <stop offset="50%" stop-color="#0ff" stop-opacity="0.5" />
                <stop offset="100%" stop-color="#0ff" stop-opacity="0.2" />
            </linearGradient>
        </defs>
        <path 
            fill="url(#wave)" 
            d="M0,32 C320,0,320,64,640,32 C960,0,960,64,1280,32 C1600,0,1600,64,1920,32 L1920,64 L0,64 Z"
            id="wavePath">
        </path>
    </svg>

    <script>
        // Animation des cercles au mouvement de la souris avec effet plus prononcé
        document.addEventListener('mousemove', (e) => {
            const circles = document.querySelectorAll('.animate-float');
            const mouseX = e.clientX;
            const mouseY = e.clientY;

            circles.forEach((circle, index) => {
                const speed = (index + 1) * 0.08;
                const x = (mouseX - window.innerWidth / 2) * speed;
                const y = (mouseY - window.innerHeight / 2) * speed;
                circle.style.transform = `translate(${x}px, ${y}px) scale(${1 + speed/2})`;
            });
        });
        
        // Effet de pluie de code de la matrice
        function createDigitalRain() {
            const container = document.getElementById('digitalRain');
            const characters = '01アイウエオカキクケコサシスセソタチツテトナニヌネノハヒフヘホマミムメモヤユヨラリルレロワヲン';
            const columns = Math.floor(window.innerWidth / 20);
            
            for (let i = 0; i < columns; i++) {
                const span = document.createElement('span');
                span.style.left = i * 20 + 'px';
                span.style.animationDuration = Math.random() * 5 + 3 + 's';
                span.style.animationDelay = Math.random() * 5 + 's';
                span.textContent = characters.charAt(Math.floor(Math.random() * characters.length));
                container.appendChild(span);
            }
            
            // Change characters periodically
            setInterval(() => {
                document.querySelectorAll('.digital-rain span').forEach(span => {
                    if (Math.random() > 0.98) {
                        span.textContent = characters.charAt(Math.floor(Math.random() * characters.length));
                    }
                });
            }, 100);
        }
        
        createDigitalRain();
        
        // Animation de l'onde en bas
        const wavePath = document.getElementById('wavePath');
        let waveOffset = 0;
        
        function animateWave() {
            waveOffset -= 1;
            wavePath.setAttribute('d', `M${waveOffset},32 C${320+waveOffset},0,${320+waveOffset},64,${640+waveOffset},32 C${960+waveOffset},0,${960+waveOffset},64,${1280+waveOffset},32 C${1600+waveOffset},0,${1600+waveOffset},64,${1920+waveOffset},32 L${1920+waveOffset},64 L${waveOffset},64 Z`);
            if (waveOffset <= -640) waveOffset = 0;
            requestAnimationFrame(animateWave);
        }
        
        animateWave();
        
        // Scanner le système button effect
        document.getElementById('scanBtn').addEventListener('click', function() {
            const btn = this;
            btn.textContent = "SCAN EN COURS...";
            btn.disabled = true;
            
            // Add scan effect
            const scanEffect = document.createElement('div');
            scanEffect.classList.add('absolute', 'inset-0', 'bg-cyan-500/10');
            scanEffect.style.animation = 'pulse 0.5s ease 5';
            document.body.appendChild(scanEffect);
            
            setTimeout(() => {
                btn.textContent = "SYSTÈME SCANNÉ";
                scanEffect.remove();
                
                // Show random cyberpunk diagnostic
                const diagnostics = [
                    "Fragment de code détecté: 42.53.75.63",
                    "Anomalie quantique identifiée",
                    "Entrée interdite: Accès niveau 5 requis",
                    "Corruptions des données systèmes",
                    "Erreur dans la matrice détectée"
                ];
                
                const diagnosticEl = document.createElement('div');
                diagnosticEl.classList.add('text-cyan-300', 'bg-black/50', 'p-3', 'mt-4', 'rounded', 'border', 'border-cyan-500/20', 'text-sm', 'font-mono');
                diagnosticEl.innerHTML = `>> ${diagnostics[Math.floor(Math.random() * diagnostics.length)]}`;
                btn.insertAdjacentElement('afterend', diagnosticEl);
                
                setTimeout(() => {
                    btn.disabled = false;
                    btn.textContent = "SCANNER LE SYSTÈME";
                }, 3000);
            }, 2500);
        });
        
        // Add more matrix characters when window is resized
        window.addEventListener('resize', () => {
            const container = document.getElementById('digitalRain');
            container.innerHTML = ''; // Clear existing spans
            createDigitalRain();
        });
    </script>
</body>
</html>