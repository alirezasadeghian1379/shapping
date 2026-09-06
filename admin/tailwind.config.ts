import type { Config } from 'tailwindcss'

export default <Partial<Config>>{
  theme: {
    extend: {
      colors: { brand: { 50: '#f0fdfa', 100: '#ccfbf1', 500: '#14b8a6', 600: '#0d9488', 700: '#0f766e', 900: '#134e4a' } },
      fontFamily: { sans: ['Vazirmatn', 'Tahoma', 'Arial', 'sans-serif'] },
      boxShadow: { soft: '0 10px 35px -15px rgb(15 23 42 / 0.16)' },
    },
  },
}
