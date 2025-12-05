/** @type {import('tailwindcss').Config} */
module.exports = {
    // v4 can auto-scan, but we’ll be explicit so PHP files are definitely included.
    content: [
        "./*.php",
        "./**/*.php",
        "./src/**/*.{js,ts,jsx,tsx,css}",
    ],
    theme: {
    },
    plugins: [],
};
