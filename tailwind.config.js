const colors = require('tailwindcss/colors')

module.exports = {
    content: [
        './resources/**/*.{vue,js}',
    ],

    theme: {
        colors: {
            black: { DEFAULT: colors.black[500], ...colors.black },
            blue: { DEFAULT: colors.blue[500], ...colors.blue },
            gray: { DEFAULT: colors.slate[500], ...colors.slate },
            green: { DEFAULT: colors.green[500], ...colors.green },
            indigo: { DEFAULT: colors.indigo[500], ...colors.indigo },
            orange: { DEFAULT: colors.orange[500], ...colors.orange },
            pink: { DEFAULT: colors.pink[500], ...colors.pink },
            red: { DEFAULT: colors.red[500], ...colors.red },
            rose: { DEFAULT: colors.rose[500], ...colors.rose },
            white: { DEFAULT: colors.white[500], ...colors.white },
        }
    },
};
