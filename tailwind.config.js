/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],
    theme: {
        extend: {
            colors: {
                brand: {
                    DEFAULT: '#009CA6',
                    50: '#E6F5F6',
                    100: '#CCEBEB',
                    200: '#99D6D9',
                    300: '#66C2C7',
                    400: '#33ADB5',
                    500: '#009CA6',
                    600: '#008C95',
                    700: '#007D85',
                    800: '#006D74',
                    900: '#005E64',
                    teal: '#009CA6',
                    lime: '#D4E157',
                    dark: '#007A82',
                }
            },
            boxShadow: {
                'atoms-card': '0 25px 50px -12px rgba(0, 0, 0, 0.25)',
            },
            borderRadius: {
                'atoms': '8px',
            },
            fontFamily: {
                sans: ['Inter', 'sans-serif'],
            }
        },
    },
    plugins: [require('@tailwindcss/forms')],
};
