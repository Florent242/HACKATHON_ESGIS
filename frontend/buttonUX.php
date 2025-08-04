<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boutons Hackathon - Styles Premium</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #0f0f0f 0%, #1a1a2e 50%, #16213e 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 3rem;
            padding: 2rem;
        }

        h1 {
            color: #ffffff;
            font-size: 2.5rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #00d4ff, #3b82f6, #8b5cf6);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .section {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2rem;
            width: 100%;
            max-width: 800px;
        }

        .section-title {
            color: #e5e7eb;
            font-size: 1.5rem;
            font-weight: 600;
            text-align: center;
        }

        .buttons-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            width: 100%;
        }

        /* === BOUTONS BLEUS STARTCHALLENGE === */
        .btn-primary {
            position: relative;
            padding: 16px 32px;
            font-size: 16px;
            font-weight: 600;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 56px;
        }

        .btn-startchallenge {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #06b6d4 100%);
            color: white;
            box-shadow: 0 8px 32px rgba(59, 130, 246, 0.3);
        }

        .btn-startchallenge::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.6s;
        }

        .btn-startchallenge:hover::before {
            left: 100%;
        }

        .btn-startchallenge:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 48px rgba(59, 130, 246, 0.4);
        }

        .btn-startchallenge:active {
            transform: translateY(0);
        }

        .btn-cyber {
            background: linear-gradient(135deg, #0ea5e9 0%, #3b82f6 50%, #6366f1 100%);
            color: white;
            border: 1px solid rgba(59, 130, 246, 0.3);
            position: relative;
            overflow: hidden;
        }

        .btn-cyber::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.1) 50%, transparent 70%);
            transform: translateX(-100%);
            transition: transform 0.6s;
        }

        .btn-cyber:hover::after {
            transform: translateX(100%);
        }

        .btn-cyber:hover {
            box-shadow: 0 0 30px rgba(59, 130, 246, 0.6), inset 0 0 20px rgba(255, 255, 255, 0.1);
            transform: translateY(-3px);
        }

        .btn-neon {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: #00d4ff;
            border: 2px solid #00d4ff;
            text-shadow: 0 0 10px #00d4ff;
            box-shadow: 
                0 0 20px rgba(0, 212, 255, 0.3),
                inset 0 0 20px rgba(0, 212, 255, 0.1);
        }

        .btn-neon:hover {
            background: linear-gradient(135deg, #3b82f6 0%, #00d4ff 100%);
            color: #1e3a8a;
            text-shadow: none;
            box-shadow: 
                0 0 40px rgba(0, 212, 255, 0.8),
                inset 0 0 30px rgba(255, 255, 255, 0.2);
            transform: scale(1.05);
        }

        /* === BOUTONS STANDARDS === */
        .btn-standard {
            background: linear-gradient(135deg, #374151 0%, #4b5563 100%);
            color: #f9fafb;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .btn-standard:hover {
            background: linear-gradient(135deg, #4b5563 0%, #6b7280 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }

        .btn-ghost {
            background: transparent;
            color: #e5e7eb;
            border: 2px solid rgba(229, 231, 235, 0.3);
            backdrop-filter: blur(10px);
        }

        .btn-ghost:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(229, 231, 235, 0.6);
            backdrop-filter: blur(20px);
            transform: translateY(-2px);
        }

        .btn-gradient {
            background: linear-gradient(135deg, #6b7280 0%, #9ca3af 50%, #d1d5db 100%);
            color: #1f2937;
            border: none;
        }

        .btn-gradient:hover {
            background: linear-gradient(135deg, #9ca3af 0%, #d1d5db 50%, #f3f4f6 100%);
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .btn-tech {
            background: linear-gradient(135deg, #111827 0%, #374151 100%);
            color: #10b981;
            border: 1px solid #10b981;
            position: relative;
        }

        .btn-tech::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, transparent, rgba(16, 185, 129, 0.1));
            opacity: 0;
            transition: opacity 0.3s;
        }

        .btn-tech:hover::before {
            opacity: 1;
        }

        .btn-tech:hover {
            box-shadow: 0 0 25px rgba(16, 185, 129, 0.4);
            transform: translateY(-2px);
        }

        /* === NOUVEAUX STYLES PARTICIPATION === */
        .btn-participate {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 50%, #1e40af 100%);
            color: white;
            box-shadow: 0 8px 32px rgba(59, 130, 246, 0.4);
            position: relative;
        }

        .btn-participate::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: radial-gradient(circle, rgba(255,255,255,0.3) 0%, transparent 70%);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: all 0.5s ease;
        }

        .btn-participate:hover::after {
            width: 300px;
            height: 300px;
        }

        .btn-participate:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 15px 40px rgba(59, 130, 246, 0.5);
        }

        .btn-register {
            background: linear-gradient(135deg, #059669 0%, #10b981 50%, #34d399 100%);
            color: white;
            border: 2px solid transparent;
            background-clip: padding-box;
        }

        .btn-register:hover {
            background: linear-gradient(135deg, #10b981 0%, #34d399 50%, #6ee7b7 100%);
            transform: translateY(-2px);
            box-shadow: 0 10px 35px rgba(16, 185, 129, 0.4);
        }

        .btn-compete {
            background: linear-gradient(135deg, #dc2626 0%, #ef4444 50%, #f87171 100%);
            color: white;
            position: relative;
            overflow: hidden;
        }

        .btn-compete::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 10px,
                rgba(255,255,255,0.1) 10px,
                rgba(255,255,255,0.1) 20px
            );
            animation: stripes 2s linear infinite;
        }

        @keyframes stripes {
            0% { transform: translateX(-20px); }
            100% { transform: translateX(20px); }
        }

        .btn-compete:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 40px rgba(220, 38, 38, 0.4);
        }

        .btn-hackathon {
            background: linear-gradient(135deg, #7c3aed 0%, #a855f7 50%, #c084fc 100%);
            color: white;
            box-shadow: 0 8px 32px rgba(124, 58, 237, 0.3);
        }

        .btn-hackathon:hover {
            background: linear-gradient(135deg, #a855f7 0%, #c084fc 50%, #ddd6fe 100%);
            transform: translateY(-2px) rotateZ(1deg);
            box-shadow: 0 15px 45px rgba(124, 58, 237, 0.5);
        }

        /* === STYLES ACTIONS SPÉCIALES === */
        .btn-stream {
            background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
            color: white;
            animation: pulse-red 2s infinite;
        }

        @keyframes pulse-red {
            0%, 100% { box-shadow: 0 0 20px rgba(220, 38, 38, 0.4); }
            50% { box-shadow: 0 0 40px rgba(220, 38, 38, 0.8); }
        }

        .btn-stream:hover {
            transform: scale(1.05);
            animation: none;
            box-shadow: 0 0 50px rgba(220, 38, 38, 0.9);
        }

        .btn-mentor {
            background: linear-gradient(135deg, #0891b2 0%, #06b6d4 50%, #22d3ee 100%);
            color: white;
            position: relative;
        }

        .btn-mentor::before {
            content: '💬';
            position: absolute;
            top: -10px;
            right: -10px;
            background: #10b981;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            animation: bounce 1s infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }

        .btn-mentor:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(8, 145, 178, 0.4);
        }

        .btn-workshop {
            background: linear-gradient(135deg, #ea580c 0%, #f97316 50%, #fb923c 100%);
            color: white;
        }

        .btn-workshop:hover {
            background: linear-gradient(135deg, #f97316 0%, #fb923c 50%, #fed7aa 100%);
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(234, 88, 12, 0.4);
        }

        .btn-github {
            background: linear-gradient(135deg, #374151 0%, #4b5563 50%, #6b7280 100%);
            color: white;
            border: 1px solid #d1d5db;
        }

        .btn-github:hover {
            background: linear-gradient(135deg, #1f2937 0%, #374151 50%, #4b5563 100%);
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .btn-leaderboard {
            background: linear-gradient(135deg, #facc15 0%, #eab308 50%, #ca8a04 100%);
            color: #1f2937;
            font-weight: 700;
        }

        .btn-leaderboard:hover {
            background: linear-gradient(135deg, #fde047 0%, #facc15 50%, #eab308 100%);
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 12px 35px rgba(250, 204, 21, 0.4);
        }

        .btn-team {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #a855f7 100%);
            color: white;
        }

        .btn-team:hover {
            background: linear-gradient(135deg, #8b5cf6 0%, #a855f7 50%, #c084fc 100%);
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(139, 92, 246, 0.4);
        }

        /* Effets spéciaux */
        .btn-pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 20px rgba(59, 130, 246, 0.3); }
            50% { box-shadow: 0 0 40px rgba(59, 130, 246, 0.6); }
        }

        .btn-3d {
            transform-style: preserve-3d;
            transition: transform 0.3s;
        }

        .btn-3d:hover {
            transform: translateY(-5px) rotateX(10deg);
        }

        /* === STYLES POUR ICÔNES LUCIDE === */
        .icon {
            width: 20px;
            height: 20px;
            fill: currentColor;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
            transition: all 0.3s ease;
        }

        /* Animations spécifiques pour les icônes */
        .btn-primary:hover .icon {
            transform: scale(1.1);
        }

        .btn-startchallenge:hover .icon,
        .btn-participate:hover .icon,
        .btn-hackathon:hover .icon {
            animation: iconSpin 0.6s ease-in-out;
        }

        .btn-stream:hover .icon {
            animation: iconPulse 0.8s infinite;
        }

        .btn-compete:hover .icon {
            animation: iconShake 0.5s ease-in-out;
        }

        .btn-neon:hover .icon {
            filter: drop-shadow(0 0 8px currentColor);
            animation: iconGlow 1s ease-in-out infinite alternate;
        }

        .btn-cyber:hover .icon {
            animation: iconFlicker 0.3s ease-in-out 3;
        }

        .btn-mentor .icon {
            animation: iconFloat 2s ease-in-out infinite;
        }

        .btn-github:hover .icon {
            transform: rotateY(180deg);
        }

        .btn-leaderboard:hover .icon {
            animation: iconBounce 0.6s ease;
        }

        .btn-workshop:hover .icon {
            animation: iconRotate 0.5s ease-in-out;
        }

        /* Animations keyframes pour les icônes */
        @keyframes iconSpin {
            0% { transform: scale(1) rotate(0deg); }
            50% { transform: scale(1.2) rotate(180deg); }
            100% { transform: scale(1.1) rotate(360deg); }
        }

        @keyframes iconPulse {
            0%, 100% { transform: scale(1.1); }
            50% { transform: scale(1.3); }
        }

        @keyframes iconShake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-2px) rotate(-2deg); }
            75% { transform: translateX(2px) rotate(2deg); }
        }

        @keyframes iconGlow {
            0% { filter: drop-shadow(0 0 5px currentColor); }
            100% { filter: drop-shadow(0 0 15px currentColor); }
        }

        @keyframes iconFlicker {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        @keyframes iconFloat {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-3px); }
        }

        @keyframes iconBounce {
            0%, 100% { transform: translateY(0); }
            25% { transform: translateY(-5px) scale(1.1); }
            50% { transform: translateY(-2px) scale(1.05); }
            75% { transform: translateY(-3px) scale(1.08); }
        }

        @keyframes iconRotate {
            0% { transform: rotate(0deg) scale(1.1); }
            50% { transform: rotate(180deg) scale(1.2); }
            100% { transform: rotate(360deg) scale(1.1); }
        }

        /* Styles spéciaux pour différents types d'icônes */
        .btn-primary .icon-play {
            margin-left: 2px; /* Centrage visuel pour les triangles */
        }

        .btn-primary .icon-arrow {
            transition: transform 0.3s ease;
        }

        .btn-primary:hover .icon-arrow {
            transform: translateX(3px) scale(1.1);
        }

        .btn-primary .icon-star {
            filter: drop-shadow(0 0 3px rgba(255, 215, 0, 0.3));
        }

        .btn-primary:hover .icon-star {
            filter: drop-shadow(0 0 8px rgba(255, 215, 0, 0.6));
            animation: iconTwinkle 1s ease-in-out;
        }

        @keyframes iconTwinkle {
            0%, 100% { transform: scale(1.1) rotate(0deg); }
            25% { transform: scale(1.2) rotate(-5deg); }
            75% { transform: scale(1.15) rotate(5deg); }
        }

        /* Effets de groupe pour les icônes avec texte */
        .btn-primary:hover .icon + span {
            transform: translateX(2px);
        }

        /* Icônes avec badges/notifications */
        .btn-primary.has-notification .icon {
            position: relative;
        }

        .btn-primary.has-notification .icon::after {
            content: '';
            position: absolute;
            top: -2px;
            right: -2px;
            width: 8px;
            height: 8px;
            background: #ef4444;
            border-radius: 50%;
            border: 2px solid currentColor;
            animation: iconNotification 2s infinite;
        }

        @keyframes iconNotification {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }

        /* Couleurs spécifiques pour certaines icônes */
        .btn-stream .icon {
            color: #ffffff;
            filter: drop-shadow(0 0 5px #ef4444);
        }

        .btn-github .icon {
            transition: all 0.3s ease;
        }

        .btn-github:hover .icon {
            color: #f0f0f0;
        }

        .btn-tech .icon {
            color: #10b981;
        }

        .btn-neon .icon {
            color: #00d4ff;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .buttons-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            
            h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <h1>🚀 Boutons Hackathon Premium</h1>

    <div class="section">
        <h2 class="section-title">Boutons Bleus "Start Challenge"</h2>
        <div class="buttons-grid">
            <button class="btn-primary btn-startchallenge">
                <svg class="icon" viewBox="0 0 24 24">
                    <path d="M8 5v14l11-7z"/>
                </svg>
                Start Challenge
            </button>

            <button class="btn-primary btn-cyber">
                <svg class="icon" viewBox="0 0 24 24">
                    <path d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Launch Hackathon
            </button>

            <button class="btn-primary btn-neon btn-pulse">
                <svg class="icon" viewBox="0 0 24 24">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
                Begin Contest
            </button>

            <button class="btn-primary btn-startchallenge btn-3d">
                <svg class="icon" viewBox="0 0 24 24">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                    <polyline points="3.27,6.96 12,12.01 20.73,6.96"/>
                    <line x1="12" y1="22.08" x2="12" y2="12"/>
                </svg>
                Code Challenge
            </button>
        </div>
    </div>

    <div class="section">
        <h2 class="section-title">Boutons "Participer au Challenge"</h2>
        <div class="buttons-grid">
            <button class="btn-primary btn-participate">
                <svg class="icon" viewBox="0 0 24 24">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="8.5" cy="7" r="4"/>
                    <path d="M20 8v6M23 11h-6"/>
                </svg>
                Rejoindre l'Équipe
            </button>

            <button class="btn-primary btn-register">
                <svg class="icon" viewBox="0 0 24 24">
                    <path d="M9 11H5a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h2"/>
                    <path d="M13 11h6a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2h-2"/>
                    <path d="M9 7V3a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v4"/>
                    <path d="M13 15h-4v-4h4v4z"/>
                </svg>
                S'inscrire Maintenant
            </button>

            <button class="btn-primary btn-compete">
                <svg class="icon" viewBox="0 0 24 24">
                    <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/>
                    <path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/>
                    <path d="M4 22h16"/>
                    <path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/>
                    <path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/>
                    <path d="M18 2H6v7a6 6 0 0 0 12 0V2z"/>
                </svg>
                Entrer en Compétition
            </button>

            <button class="btn-primary btn-hackathon">
                <svg class="icon" viewBox="0 0 24 24">
                    <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                    <line x1="8" y1="21" x2="16" y2="21"/>
                    <line x1="12" y1="17" x2="12" y2="21"/>
                    <path d="M7 11h10"/>
                    <path d="M7 7h4"/>
                </svg>
                Participer au Hack
            </button>
        </div>
    </div>

    <div class="section">
        <h2 class="section-title">Boutons Standards</h2>
        <div class="buttons-grid">
            <button class="btn-primary btn-standard">
                <svg class="icon" viewBox="0 0 24 24">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                Mon Profil
            </button>

            <button class="btn-primary btn-ghost">
                <svg class="icon" viewBox="0 0 24 24">
                    <path d="M14 9V5a3 3 0 0 0-6 0v4"/>
                    <rect x="2" y="9" width="20" height="12" rx="2" ry="2"/>
                    <path d="M9 12l2 2 4-4"/>
                </svg>
                Soumettre Projet
            </button>

            <button class="btn-primary btn-gradient">
                <svg class="icon" viewBox="0 0 24 24">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14,2 14,8 20,8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                    <polyline points="10,9 9,9 8,9"/>
                </svg>
                Voir Résultats
            </button>

            <button class="btn-primary btn-tech">
                <svg class="icon" viewBox="0 0 24 24">
                    <polyline points="16 18 22 12 16 6"/>
                    <polyline points="8 6 2 12 8 18"/>
                </svg>
                Documentation
            </button>

            <button class="btn-primary btn-leaderboard">
                <svg class="icon" viewBox="0 0 24 24">
                    <path d="M8 21l4-7 4 7"/>
                    <path d="M12 21v-7"/>
                    <path d="M3 7l6 4 3-3 6 3"/>
                    <circle cx="12" cy="10" r="3"/>
                </svg>
                Classement
            </button>

            <button class="btn-primary btn-team">
                <svg class="icon" viewBox="0 0 24 24">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                Gérer Équipe
            </button>
        </div>
    </div>

    <div class="section">
        <h2 class="section-title">Boutons Actions Spéciales</h2>
        <div class="buttons-grid">
            <button class="btn-primary btn-stream">
                <svg class="icon" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/>
                    <polygon points="10,8 16,12 10,16 10,8"/>
                </svg>
                Live Stream
            </button>

            <button class="btn-primary btn-mentor">
                <svg class="icon" viewBox="0 0 24 24">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                    <path d="M13 8H7"/>
                    <path d="M17 12H7"/>
                </svg>
                Contacter Mentor
            </button>

            <button class="btn-primary btn-workshop">
                <svg class="icon" viewBox="0 0 24 24">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                    <path d="M8 14h.01"/>
                    <path d="M12 14h.01"/>
                    <path d="M16 14h.01"/>
                </svg>
                Workshop
            </button>

            <button class="btn-primary btn-github">
                <svg class="icon" viewBox="0 0 24 24">
                    <path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"/>
                </svg>
                Fork Repo
            </button>
        </div>
    </div>

    <div class="section">
        <h2 class="section-title">Exemples avec Icônes Lucide Personnalisées</h2>
        <div class="buttons-grid">
            <button class="btn-primary btn-startchallenge">
                <svg class="icon icon-play" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <polygon points="5,3 19,12 5,21 5,3"></polygon>
                </svg>
                <span>Démarrer Challenge</span>
            </button>

            <button class="btn-primary btn-participate">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="8.5" cy="7" r="4"></circle>
                    <path d="m20 8 2 2-2 2"></path>
                    <path d="m14 12 2 2-2 2"></path>
                </svg>
                <span>Rejoindre Équipe</span>
            </button>

            <button class="btn-primary btn-compete">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"></path>
                    <path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"></path>
                    <path d="M4 22h16"></path>
                    <path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"></path>
                    <path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"></path>
                    <path d="M18 2H6v7a6 6 0 0 0 12 0V2z"></path>
                </svg>
                <span>Compétition</span>
            </button>

            <button class="btn-primary btn-neon">
                <svg class="icon icon-star" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26 12,2"></polygon>
                </svg>
                <span>Challenge Elite</span>
            </button>

            <button class="btn-primary btn-mentor has-notification">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="m3 21 1.9-5.7a8.5 8.5 0 1 1 3.8 3.8z"></path>
                    <path d="M12 8v4"></path>
                    <path d="m12 16 .01 0"></path>
                </svg>
                <span>Aide Mentor</span>
            </button>

            <button class="btn-primary btn-github">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M15 22v-4a4.8 4.8 0 0 0-1-3.5c3 0 6-2 6-5.5.08-1.25-.27-2.48-1-3.5.28-1.15.28-2.35 0-3.5 0 0-1 0-3 1.5-2.64-.5-5.36-.5-8 0C6 2 5 2 5 2c-.3 1.15-.3 2.35 0 3.5A5.403 5.403 0 0 0 4 9c0 3.5 3 5.5 6 5.5-.39.49-.68 1.05-.85 1.65-.17.6-.22 1.23-.15 1.85v4"></path>
                    <path d="M9 18c-4.51 2-5-2-7-2"></path>
                </svg>
                <span>Fork Repository</span>
            </button>
        </div>
    </div>
        // Ajouter des effets interactifs
        document.querySelectorAll('.btn-primary').forEach(btn => {
            btn.addEventListener('mouseenter', function() {
                this.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
            });

            btn.addEventListener('mouseleave', function() {
                this.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
            });

            btn.addEventListener('click', function(e) {
                // Effet de ripple
                const ripple = document.createElement('div');
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;
                
                ripple.style.cssText = `
                    position: absolute;
                    width: ${size}px;
                    height: ${size}px;
                    left: ${x}px;
                    top: ${y}px;
                    background: rgba(255, 255, 255, 0.3);
                    border-radius: 50%;
                    transform: scale(0);
                    animation: ripple 0.6s ease-out;
                    pointer-events: none;
                `;
                
                this.appendChild(ripple);
                
                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });
        });

        // Animation CSS pour l'effet ripple
        const style = document.createElement('style');
        style.textContent = `
            @keyframes ripple {
                to {
                    transform: scale(2);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>