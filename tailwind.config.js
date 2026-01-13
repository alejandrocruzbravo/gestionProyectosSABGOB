import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Manrope', ...defaultTheme.fontFamily.sans], // Usaremos Manrope como fuente principal
                display: ['Manrope', 'sans-serif'],
            },
            colors: {
                // Tus colores personalizados del diseño
                "primary": "#00998c",
                "primary-dark": "#007a70",
                "background-light": "#f6f7f8",
                "background-dark": "#212936",
                "surface-light": "#ffffff",
                "surface-dark": "#2d3748",
            },
        
        },
    },

    plugins: [forms],
};
