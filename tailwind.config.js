/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./assets/**/*.js", "./templates/**/*.html.twig"],
  theme: {
    extend: {
      colors: {
        primary: "rgba(var(--primary-color))",
        secondary: "rgba(var(--secondary-color))",
        tertiary: "rgba(var(--tertiary-color))",
        background: "rgba(var(--background-color))",
      },
      fontFamily: {
        title: ["var(--title-font)", "Arial", "sans-serif"],
        subtitle: ["var(--subtitle-font)", "Arial", "sans-serif"],
        content: ["var(--content-font)", "Arial", "sans-serif"],
      },
      fontSize: {
        title: "var(--title-font-size)",
        subtitle: "var(--subtitle-font-size)",
        content: "var(--content-font-size)",
      },
    },
  },
  plugins: [],
};
