// tailwind.config.js
module.exports = {
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
  ],
  theme: {
    extend: {
      colors: {
        chicken: 'var(--chicken)', // 🐔 Custom color added here
      },
    },
  },
  plugins: [],
}
