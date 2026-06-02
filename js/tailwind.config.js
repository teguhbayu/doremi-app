tailwind.config = {
  prefix: "tw-",
  corePlugins: {
    preflight: false,
  },
  theme: {
    extend: {
      colors: {
        primary: "#146C94", // Darkest Blue
        secondary: "#19A7CE", // Medium Blue
        accent: "#AFD3E2", // Light Blue
        background: "#F6F1F1", // Off White/Light Gray
      },
      fontFamily: {
        sans: ["Inter", "sans-serif"],
      },
    },
  },
};
