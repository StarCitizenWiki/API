import "./bootstrap";
import { themeToggle } from "./themeToggle";
import { liveSearch } from "./liveSearch";
import { createIcons } from "lucide";

// NOTE: When adding icons in Blade templates or PHP, add the import and entry here too.
// See: resources/views/components/icon.blade.php
import {
    Activity,
    AlertCircle,
    ArrowLeft,
    ArrowRight,
    ArrowUp,
    Atom,
    Banana,
    Bomb,
    BookOpen,
    Box,
    BrickWallShield,
    CheckCircle,
    ChevronRight,
    CirclePlus,
    Cog,
    ContactRound,
    Copy,
    Cpu,
    Crosshair,
    DraftingCompass,
    Drone,
    EggFried,
    ExternalLink,
    Fan,
    Flag,
    Flame,
    Gauge,
    Gem,
    GitCompare,
    CodeXml,
    Home,
    Image,
    Info,
    Joystick,
    Key,
    Languages,
    Layers,
    LineChart,
    List,
    Locate,
    Lock,
    Magnet,
    Map,
    MapPin,
    MapPinned,
    Menu,
    Moon,
    MoreVertical,
    MousePointer2Off,
    Move,
    OctagonAlert,
    Package as PackageIcon,
    PaintBucket,
    Pickaxe,
    Plug,
    Power,
    PowerOff,
    Puzzle,
    Radar,
    RefreshCw,
    Rocket,
    Satellite,
    Search,
    SearchX,
    Server,
    Shield,
    ShieldCheck,
    ShieldUser,
    Shirt,
    Skull,
    SquareStack,
    Star,
    Sun,
    Syringe,
    Target,
    Terminal,
    Trash2,
    Truck,
    User,
    Users,
    Utensils,
    Wifi,
    Wrench,
    X as XIcon,
    Zap,
} from "lucide";

const icons = {
    Activity,
    AlertCircle,
    ArrowLeft,
    ArrowRight,
    ArrowUp,
    Atom,
    Banana,
    Bomb,
    BookOpen,
    Box,
    BrickWallShield,
    CheckCircle,
    ChevronRight,
    CirclePlus,
    Cog,
    CodeXml,
    ContactRound,
    Copy,
    Cpu,
    Crosshair,
    DraftingCompass,
    Drone,
    EggFried,
    ExternalLink,
    Fan,
    Flag,
    Flame,
    Gauge,
    Gem,
    GitCompare,
    Home,
    Image,
    Info,
    Joystick,
    Key,
    Languages,
    Layers,
    LineChart,
    List,
    Locate,
    Lock,
    Magnet,
    Map,
    MapPin,
    MapPinned,
    Menu,
    Moon,
    MoreVertical,
    MousePointer2Off,
    Move,
    OctagonAlert,
    Package: PackageIcon,
    PaintBucket,
    Pickaxe,
    Plug,
    Power,
    PowerOff,
    Puzzle,
    Radar,
    RefreshCw,
    Rocket,
    Satellite,
    Search,
    SearchX,
    Server,
    Shield,
    ShieldCheck,
    ShieldUser,
    Shirt,
    Skull,
    SquareStack,
    Star,
    Sun,
    Syringe,
    Target,
    Terminal,
    Trash2,
    Truck,
    User,
    Users,
    Utensils,
    Wifi,
    Wrench,
    X: XIcon,
    Zap,
};

document.addEventListener("DOMContentLoaded", async () => {
    window.Alpine.data("themeToggle", themeToggle);
    window.Alpine.data("liveSearch", liveSearch);

    const lazyPromises = [];

    if (document.querySelector('[x-data="blueprintSearch"]')) {
        lazyPromises.push(
            import("./blueprintSearch").then(({ blueprintSearch }) => {
                window.Alpine.data("blueprintSearch", blueprintSearch);
            }),
        );
    }

    if (document.querySelector('[x-data="blueprintTuning"]')) {
        lazyPromises.push(
            import("./blueprintTuning").then(({ blueprintTuning }) => {
                window.Alpine.data("blueprintTuning", blueprintTuning);
            }),
        );
    }

    if (document.querySelector('[x-data^="portEquippable"]')) {
        lazyPromises.push(
            import("./portEquippable").then(({ portEquippable }) => {
                window.Alpine.data("portEquippable", portEquippable);
            }),
        );
    }

    if (document.querySelector('[x-data="developerQuickstart"], [x-data="developerShowDemo"], [x-data="developerFiltersDemo"]')) {
        lazyPromises.push(
            import("./developerQuickstart").then((mod) => {
                window.Alpine.data("developerQuickstart", mod.developerQuickstart);
                window.Alpine.data("developerShowDemo", mod.developerShowDemo);
                window.Alpine.data("developerFiltersDemo", mod.developerFiltersDemo);
            }),
        );
    }

    await Promise.all(lazyPromises);

    window.Alpine.start();

    createIcons({ icons });

    if (document.querySelector("[data-tabulator]")) {
        const { initTabulatorTables } = await import("./tables/baseTable");
        initTabulatorTables();
    }
});
