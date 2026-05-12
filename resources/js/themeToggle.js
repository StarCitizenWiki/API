export function themeToggle() {
    return {
        isDark: false,

        get lightTheme() {
            return window.AppThemes?.light ?? 'nord';
        },

        get darkTheme() {
            return window.AppThemes?.dark ?? 'night';
        },

        init() {
            const stored = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            this.isDark = stored
                ? stored === this.darkTheme
                : prefersDark;
        },
    };
}
