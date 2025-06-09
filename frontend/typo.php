<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Typographies Hackathon</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@300;400;500;600;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --background: hsl(222, 85%, 7%);
            --card: hsl(222, 65%, 12%);
            --card-bg: linear-gradient(135deg, #030B20 0%, #030F2A 100%);
            --border: #1E293B;
            --text: #FFFFFF;
            --text-secondary: #94A3B8;
            --blue: #3B82F6;
            --green: #22C55E;
            --yellow: #EAB308;
            --red: #EF4444;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: var(--background);
            color: var(--text);
            line-height: 1.6;
            padding: 2rem;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .section {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 1rem;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .section-title {
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            color: var(--blue);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* OPTION 1: Inter - Le safe choice */
        .inter-demo {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        .inter-demo .hero-title {
            font-size: clamp(2rem, 5vw, 4rem);
            font-weight: 800;
            background: linear-gradient(135deg, var(--blue), var(--green));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1.2;
            margin-bottom: 1rem;
        }

        .inter-demo .subtitle {
            font-size: 1.25rem;
            font-weight: 500;
            color: var(--text-secondary);
            margin-bottom: 2rem;
        }

        /* OPTION 2: Space Grotesk - Le moderne */
        .space-demo {
            font-family: 'Space Grotesk', sans-serif;
        }

        .space-demo .hero-title {
            font-size: clamp(2rem, 5vw, 4rem);
            font-weight: 700;
            background: linear-gradient(135deg, var(--yellow), var(--red));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1.1;
            margin-bottom: 1rem;
            letter-spacing: -0.02em;
        }

        .space-demo .subtitle {
            font-size: 1.25rem;
            font-weight: 500;
            color: var(--text-secondary);
            margin-bottom: 2rem;
        }

        /* Code font */
        .code-demo {
            font-family: 'JetBrains Mono', 'Fira Code', Consolas, monospace;
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 0.5rem;
            padding: 1rem;
            font-size: 0.9rem;
            color: var(--blue);
            margin: 1rem 0;
            overflow-x: auto;
        }

        /* Hierarchy examples */
        .typography-scale h1 {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            line-height: 1.2;
        }

        .typography-scale h2 {
            font-size: 2.25rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            line-height: 1.3;
        }

        .typography-scale h3 {
            font-size: 1.875rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            line-height: 1.3;
        }

        .typography-scale h4 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            line-height: 1.4;
        }

        .typography-scale h5 {
            font-size: 1.25rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            line-height: 1.4;
        }

        .typography-scale p {
            font-size: 1rem;
            font-weight: 400;
            color: var(--text-secondary);
            margin-bottom: 1rem;
            line-height: 1.6;
        }

        .typography-scale small {
            font-size: 0.875rem;
            font-weight: 400;
            color: var(--text-secondary);
        }

        /* Buttons avec typo */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            margin: 0.5rem 0.5rem 0.5rem 0;
        }

        .btn-primary {
            background: var(--blue);
            color: white;
        }

        .btn-primary:hover {
            background: var(--blue);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
        }

        /* Cards avec typo */
        .card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text);
        }

        .card-text {
            font-size: 0.95rem;
            color: var(--text-secondary);
            line-height: 1.5;
        }

        /* Badge */
        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-radius: 9999px;
            background: var(--green);
            color: white;
        }

        /* Recommandation highlight */
        .recommended {
            position: relative;
            border: 2px solid var(--green);
        }

        .recommended::before {
            content: "RECOMMANDÉ";
            position: absolute;
            top: -12px;
            left: 1rem;
            background: var(--green);
            color: white;
            padding: 0.25rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 700;
            border-radius: 0.25rem;
        }

        .pros {
            color: var(--green);
            margin-top: 1rem;
        }

        .cons {
            color: var(--red);
            margin-top: 0.5rem;
        }

        .use-cases {
            background: rgba(59, 130, 246, 0.1);
            border-left: 4px solid var(--blue);
            padding: 1rem;
            margin-top: 1rem;
            border-radius: 0 0.5rem 0.5rem 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="section">
            <h1 style="font-family: 'Space Grotesk', sans-serif; font-size: 3rem; font-weight: 700; text-align: center; margin-bottom: 2rem; background: linear-gradient(135deg, #3B82F6, #22C55E); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;">
                Typographies pour Hackathon
            </h1>
        </div>

        <!-- OPTION 1: Inter -->
        <div class="section recommended">
            <div class="section-title">
                🎯 Inter - Le choix universel
            </div>
            <div class="inter-demo">
                <h1 class="hero-title">HackFlow 2024</h1>
                <p class="subtitle">La plateforme qui transforme vos idées en réalité</p>
                
                <div class="typography-scale">
                    <h1>Page Title (H1)</h1>
                    <h2>Section Title (H2)</h2>
                    <h3>Component Title (H3)</h3>
                    <h4>Card Title (H4)</h4>
                    <h5>Subtitle (H5)</h5>
                    <p>Body text - Lorem ipsum dolor sit amet consectetur. Cette police est optimisée pour la lisibilité à toutes les tailles.</p>
                    <small>Caption text / metadata</small>
                </div>

                <div class="pros">
                    ✅ Lisibilité parfaite sur tous supports<br>
                    ✅ Optimisée pour les interfaces<br>
                    ✅ Large gamme de graisses (300-900)<br>
                    ✅ Excellent rendu sur écrans HD et Retina
                </div>
            </div>
            
            <div class="use-cases">
                <strong>Parfaite pour :</strong> Dashboards, formulaires, tables de données, interfaces complexes
            </div>
        </div>

        <!-- OPTION 2: Space Grotesk -->
        <div class="section">
            <div class="section-title">
                🚀 Space Grotesk - Le moderne
            </div>
            <div class="space-demo">
                <h1 class="hero-title">HackFlow 2024</h1>
                <p class="subtitle">La plateforme qui transforme vos idées en réalité</p>
                
                <div class="typography-scale">
                    <h1>Page Title (H1)</h1>
                    <h2>Section Title (H2)</h2>
                    <h3>Component Title (H3)</h3>
                    <h4>Card Title (H4)</h4>
                    <h5>Subtitle (H5)</h5>
                    <p>Body text - Lorem ipsum dolor sit amet consectetur. Cette police apporte une personnalité tech moderne.</p>
                    <small>Caption text / metadata</small>
                </div>

                <div class="pros">
                    ✅ Look moderne et tech<br>
                    ✅ Parfaite pour les titres<br>
                    ✅ Personnalité unique<br>
                    ✅ Tendance 2024
                </div>
                <div class="cons">
                    ⚠️ Moins lisible sur de longs textes<br>
                    ⚠️ Peut fatiguer à la lecture
                </div>
            </div>
            
            <div class="use-cases">
                <strong>Parfaite pour :</strong> Titres, héros, landing pages, branding
            </div>
        </div>

        <!-- CODE FONT -->
        <div class="section">
            <div class="section-title">
                💻 JetBrains Mono - Pour le code
            </div>
            <div class="code-demo">
function submitProject() {<br>
&nbsp;&nbsp;const project = {<br>
&nbsp;&nbsp;&nbsp;&nbsp;name: "IA Revolution",<br>
&nbsp;&nbsp;&nbsp;&nbsp;team: ["Alice", "Bob", "Charlie"],<br>
&nbsp;&nbsp;&nbsp;&nbsp;tech: ["React", "Node.js", "MongoDB"]<br>
&nbsp;&nbsp;};<br>
&nbsp;&nbsp;return api.post('/hackathon/submit', project);<br>
}
            </div>
            <div class="pros">
                ✅ Ligatures programmeur<br>
                ✅ Excellente lisibilité du code<br>
                ✅ Caractères bien distincts (0 vs O)<br>
                ✅ Espacement optimal
            </div>
        </div>

        <!-- COMBINAISON RECOMMANDÉE -->
        <div class="section" style="border: 2px solid #22C55E;">
            <div class="section-title">
                🏆 Stack Typographique Recommandée
            </div>
            
            <div style="font-family: 'Inter', sans-serif;">
                <h2 style="font-family: 'Space Grotesk', sans-serif; color: #3B82F6; margin-bottom: 1rem;">
                    Combinaison Gagnante
                </h2>
                
                <div class="card">
                    <div class="card-title" style="font-family: 'Space Grotesk', sans-serif;">
                        🎨 Titres & Branding
                    </div>
                    <div class="card-text">
                        <strong>Space Grotesk</strong> pour tous les titres, hero sections, et éléments de branding
                    </div>
                </div>

                <div class="card">
                    <div class="card-title">📖 Corps de texte & UI</div>
                    <div class="card-text">
                        <strong>Inter</strong> pour le body text, formulaires, navigation, et tous les éléments d'interface
                    </div>
                </div>

                <div class="card">
                    <div class="card-title" style="font-family: 'JetBrains Mono', monospace;">💻 Code & Data</div>
                    <div class="card-text">
                        <strong>JetBrains Mono</strong> pour les blocs de code, APIs, et données techniques
                    </div>
                </div>
            </div>

            <div style="margin-top: 2rem; padding: 1rem; background: rgba(34, 197, 94, 0.1); border-radius: 0.5rem; border-left: 4px solid #22C55E;">
                <strong style="color: #22C55E;">CSS à utiliser :</strong><br><br>
                <code style="font-family: 'JetBrains Mono', monospace; font-size: 0.9rem;">
font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;<br>
/* Pour les titres: */<br>
font-family: 'Space Grotesk', sans-serif;<br>
/* Pour le code: */<br>
font-family: 'JetBrains Mono', Consolas, monospace;
                </code>
            </div>
        </div>

        <!-- EXAMPLES CONCRETS -->
        <div class="section">
            <div class="section-title">
                🎪 Exemples concrets
            </div>
            
            <!-- Navigation -->
            <nav style="display: flex; gap: 2rem; margin-bottom: 2rem; font-family: 'Inter', sans-serif; font-weight: 500;">
                <a href="#" style="color: #3B82F6; text-decoration: none;">Accueil</a>
                <a href="#" style="color: #94A3B8; text-decoration: none;">Projets</a>
                <a href="#" style="color: #94A3B8; text-decoration: none;">Équipes</a>
                <a href="#" style="color: #94A3B8; text-decoration: none;">Classement</a>
            </nav>

            <!-- Hero section -->
            <div style="text-align: center; margin: 3rem 0;">
                <h1 style="font-family: 'Space Grotesk', sans-serif; font-size: 3rem; font-weight: 700; background: linear-gradient(135deg, #3B82F6, #22C55E); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; margin-bottom: 1rem;">
                    Hackathon 2024
                </h1>
                <p style="font-family: 'Inter', sans-serif; font-size: 1.25rem; color: #94A3B8; margin-bottom: 2rem;">
                    48h pour transformer vos idées en réalité
                </p>
                <button class="btn btn-primary" style="font-family: 'Inter', sans-serif;">
                    Rejoindre l'aventure
                </button>
            </div>

            <!-- Cards -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem;">
                <div class="card">
                    <span class="badge">EN COURS</span>
                    <h3 class="card-title" style="font-family: 'Space Grotesk', sans-serif;">EcoTrack AI</h3>
                    <p class="card-text" style="font-family: 'Inter', sans-serif;">
                        Une application IA pour tracker l'empreinte carbone en temps réel
                    </p>
                </div>
                <div class="card">
                    <span class="badge" style="background: #EAB308;">ÉVALUATION</span>
                    <h3 class="card-title" style="font-family: 'Space Grotesk', sans-serif;">HealthBot Pro</h3>
                    <p class="card-text" style="font-family: 'Inter', sans-serif;">
                        Chatbot médical intelligent pour le diagnostic préliminaire
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>