/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        court: {
          50: '#f0f4f8',
          100: '#dbe3f0',
          200: '#bdcce2',
          300: '#92acd0',
          400: '#6487ba',
          500: '#45699e',
          600: '#345281',
          700: '#2c436b',
          800: '#273a5a',
          900: '#23324c',
          950: '#172033',
        },
      },
    },
  },
  plugins: [],
}
