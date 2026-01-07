import "./bootstrap";
import { initTabulatorTables } from "./tables/baseTable";

import { createIcons, icons } from 'lucide';

document.addEventListener("DOMContentLoaded", () => {
    initTabulatorTables();
    createIcons({icons});

    const themeToggles = document.querySelectorAll("[data-theme-toggle]");

    if (themeToggles.length === 0) {
        return;
    }

    const storedTheme = localStorage.getItem("theme");
    const prefersDark = window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches;
    const currentTheme = storedTheme ?? (prefersDark ? "dark" : "light");

    document.documentElement.setAttribute("data-theme", currentTheme);
    themeToggles.forEach((toggle) => {
        toggle.checked = currentTheme === "dark";
    });

    themeToggles.forEach((toggle) => {
        toggle.addEventListener("change", () => {
            const nextTheme = toggle.checked ? "dark" : "light";

            document.documentElement.setAttribute("data-theme", nextTheme);
            localStorage.setItem("theme", nextTheme);

            themeToggles.forEach((syncToggle) => {
                syncToggle.checked = nextTheme === "dark";
            });
        });
    });
});
