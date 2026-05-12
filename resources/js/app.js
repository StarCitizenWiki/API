import "./bootstrap";
import { initTabulatorTables } from "./tables/baseTable";
import { themeToggle } from "./themeToggle";
import { liveSearch } from "./liveSearch";
import { blueprintSearch } from "./blueprintSearch";
import { blueprintTuning } from "./blueprintTuning";

import { createIcons, icons } from 'lucide';

document.addEventListener("DOMContentLoaded", () => {
    initTabulatorTables();
    createIcons({icons});

    window.Alpine.data('themeToggle', themeToggle);
    window.Alpine.data('liveSearch', liveSearch);
    window.Alpine.data('blueprintSearch', blueprintSearch);
    window.Alpine.data('blueprintTuning', blueprintTuning);
    window.Alpine.start();
});
