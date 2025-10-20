/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./frontend/**/*.php",
    "./public/**/*.js",
    "./public/css/styles/utilities/custom.css",
    "./includes/**/*.php",
    "./public/css/styles/global.css"
  ],
  theme: {
    extend: {
      colors: {
        'royal': {
          50: '#f0f9ff',
          100: '#e0f2fe',
          200: '#bae6fd',
          300: '#7dd3fc',
          400: '#38bdf8',
          500: '#0ea5e9',
          600: '#0284c7',
          700: '#0369a1',
          800: '#075985',
          900: '#0c4a6e',
          950: '#0f172a'
        },
        'cyber': {
          400: '#00d9ff',
          500: '#00bcd4',
          600: '#0097a7'
        }
      },
      animation: {
        'pulse-glow': 'pulse-glow 2s cubic-bezier(0.4, 0, 0.6, 1) infinite',
        'slide-up': 'slide-up 0.3s ease-out',
        'fade-in': 'fade-in 0.5s ease-out',
        'bounce-in': 'bounce-in 0.6s ease-out'
      },
      keyframes: {
        'pulse-glow': {
          '0%, 100%': {
            opacity: '1',
            boxShadow: '0 0 20px rgba(0, 217, 255, 0.3)'
          },
          '50%': {
            opacity: '0.8',
            boxShadow: '0 0 40px rgba(0, 217, 255, 0.6)'
          }
        },
        'slide-up': {
          '0%': { transform: 'translateY(10px)', opacity: '0' },
          '100%': { transform: 'translateY(0)', opacity: '1' }
        },
        'fade-in': {
          '0%': { opacity: '0' },
          '100%': { opacity: '1' }
        },
        'bounce-in': {
          '0%': { transform: 'scale(0.3)', opacity: '0' },
          '50%': { transform: 'scale(1.05)' },
          '70%': { transform: 'scale(0.9)' },
          '100%': { transform: 'scale(1)', opacity: '1' }
        }
      }
    }
  },
  plugins: {
    'postcss-import': {},
    'tailwindcss/nesting': {},
    'tailwindcss': {},
    'autoprefixer': {},
    'flowbite/plugin': {},
    'tailwindcss/forms': {},
    'tailwindcss/aspect-ratio': {},
    'tailwindcss/typography': {},
  },
}

tailwind.config = {
  theme: {
    extend: {
      fontFamily: {
        'inter': ['Inter', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'sans-serif']
      }
    }
  }
}

