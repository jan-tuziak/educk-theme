/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./*.php",
    "./**/*.php",
    "./src/**/*.{js,css}",
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ["Archivo", "system-ui", "-apple-system", "Segoe UI", "sans-serif"],
      },
      colors: {
        primary: "#FFC818",
        secondary: "#F3CB4A",
        text: "#3A3E40",
        "text-soft": "#E5E4E2",
        headers: "#37352E",
        "bg-main": "#FFFFFF",
        "bg-secondary": "#F1F1F1",
        "bg-yellow": "#F3CB4A",
      },
      fontSize: {
        h1: ["4.75rem", { lineHeight: "1.05" }], // 76px
        h2: ["3.75rem", { lineHeight: "1.1" }],  // 60px
        h3: ["2.5rem", { lineHeight: "1.2" }],   // 40px
        h4: ["2rem", { lineHeight: "1.2" }],     // 32px
        h5: ["1.625rem", { lineHeight: "1.3" }], // 26px
        body: ["1.125rem", { lineHeight: "1.7" }], // 18px
        sm: ["0.875rem", { lineHeight: "1.6" }], // 14px
        button: ["1.125rem", { lineHeight: "1.2" }], // 18px
      }
    },
  },
  plugins: [],
};
