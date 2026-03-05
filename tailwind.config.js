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
            colors: {
                tix: {
                    orange: '#000000',
                },
                eventbrite: {
                    orange: '#d1410c',
                    purple: '#1e0a3c',
                    dark: '#1e0a3c',
                    gray: {
                        50: '#f8f7fa',
                        100: '#eeedf2',
                        400: '#6f7287',
                        600: '#39364f',
                    }
                }
            },
            fontFamily: {
                sans: ['Montserrat', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
