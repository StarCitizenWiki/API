import "./bootstrap";
import { initTabulatorTables } from "./tables/baseTable";

import { createIcons, icons } from 'lucide';

const lightTheme = window.AppThemes?.light ?? 'nord';
const darkTheme = window.AppThemes?.dark ?? 'night';

document.addEventListener("DOMContentLoaded", () => {
    initTabulatorTables();
    createIcons({icons});

    const themeToggles = document.querySelectorAll("[data-theme-toggle]");

    if (themeToggles.length === 0) {
        return;
    }

    const storedTheme = localStorage.getItem("theme");
    const prefersDark = window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches;
    const currentTheme = storedTheme ?? (prefersDark ? darkTheme : lightTheme);

    document.documentElement.setAttribute("data-theme", currentTheme);
    themeToggles.forEach((toggle) => {
        toggle.checked = currentTheme === darkTheme;
    });

    themeToggles.forEach((toggle) => {
        toggle.addEventListener("change", () => {
            const nextTheme = toggle.checked ? darkTheme : lightTheme;

            document.documentElement.setAttribute("data-theme", nextTheme);
            localStorage.setItem("theme", nextTheme);

            themeToggles.forEach((syncToggle) => {
                syncToggle.checked = nextTheme === darkTheme;
            });
        });
    });
});
