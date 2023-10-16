/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./assets/**/*.js",
        "./templates/**/*.html.twig",
        "./node_modules/flowbite/**/*.js",
        "./src/Form/*.php"
    ],
    theme: {
        extend: {},
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('flowbite/plugin'),
        require("daisyui")
    ],
};
