/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./assets/**/*.js", "./templates/**/*.html.twig"],
  theme: {
    container: {
      padding: {
        md: "1rem",
      },
    },
    extend: {
      colors: {
        primary: "rgba(var(--primary-color))",
        secondary: "rgba(var(--secondary-color))",
        tertiary: "rgba(var(--tertiary-color))",
        background: "rgba(var(--background-color))",
        green: {
          '100': '#C9E4CA',
          '800': '#43A047',
        },
        red: {
          '100': '#F0C9C9',
          '800': '#F44336',
        },
        yellow: {
          '100': '#F4DDBB',
          '800': '#FF9800',
        },
        backgroundIcon: '#FBF6F6'
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
      borderRadius: {
        small: "10px",
        half: "40px",
      },
      gap:{
        '13':"52px"
       }
    },
  },
  plugins: [],
};
