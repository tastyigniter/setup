const colors = require('tailwindcss/colors')

module.exports = {
    content: [
        "../../setup.php",
        "../assets/src/**/*.js",
        "../partials/*.php"
    ],
    theme: {
        colors: ({colors}) => ({
            inherit: colors.inherit,
            current: colors.current,
            transparent: colors.transparent,
            black: colors.black,
            white: colors.white,
            gray: colors.gray,
            red: colors.red,
            emerald: colors.emerald,
            amber: colors.amber,
            orange: {
                50: '#FFF3EE',
                100: '#FFE4D9',
                200: '#FFD2BF',
                300: '#FFC2AA',
                400: '#FFAA88',
                500: '#FF7A44',
                600: '#FF4900',
                700: '#BB3600',
                800: '#772200',
                900: '#330F00',
            },
        }),
        borderColor: ({theme}) => ({
            ...theme('colors'),
            DEFAULT: theme('colors.gray.200', 'currentColor'),
        }),
        container: {
            center: true,
        },
        fontFamily: {
            sans: ['Inter', 'sans-serif'],
            serif: ['Droid Sans Mono', 'serif'],
        },
        ringColor: ({theme}) => ({
            DEFAULT: theme('colors.orange.600', '#FF4900'),
            ...theme('colors'),
        }),
        extend: {},
    },
    plugins: [
        require('postcss-import'),
        require('@tailwindcss/forms')
    ],
}
