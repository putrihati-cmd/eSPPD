import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    DEFAULT: '#02A0AC', // Primary
                    50: '#F0FAFA',
                    100: '#D1EFEF',
                    500: '#02A0AC',
                    600: '#4DA790', // Brand Dark / Shadow
                    800: '#015F66',
                },
                accent: {
                    DEFAULT: '#CBE155', // Accent
                    hover: '#b8cc48',
                },
                teal: {
                    light: '#A2DBDC', // Light Teal
                },
                paper: '#FDFEFE', // White/Background
            },
        },
    },

    plugins: [forms],
};
