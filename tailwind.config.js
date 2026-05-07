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
                poppins: ['Poppins', ...defaultTheme.fontFamily.sans],
                sans: ['Poppins', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    50:  '#fdf8f0',
                    100: '#f9ecda',
                    200: '#f2d5ad',
                    300: '#e8b979',
                    400: '#de9840',
                    500: '#d4831f',
                    600: '#b86a16',
                    700: '#995215',
                    800: '#7d4218',
                    900: '#673816',
                    950: '#381b09',
                },
                krem: {
                    50:  '#fefcf9',
                    100: '#fdf6ec',
                    200: '#f9e8cf',
                    300: '#f3d5a8',
                    400: '#edbc79',
                },
            },
        },
    },

    plugins: [forms],
};
