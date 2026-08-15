export default {
  darkMode: 'class',
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      animation: {
        'spin-slow': 'spin 5s linear infinite',
      },
      fontFamily: {
        sans: ['Nunito Sans', 'Poppins', 'sans-serif'],
        khmer: ['Kantumruy Pro', 'sans-serif'],
        dmsans: ['DM Sans', 'sans-serif'],
        nunito: ['Nunito Sans', 'sans-serif'],
      },
    },
  },
  plugins: [],
}
