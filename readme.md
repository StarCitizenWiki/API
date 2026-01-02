# Star Citizen API

A Laravel-based API providing access to Star Citizen game data with multi-language support (English, German, Chinese Simplified).

## Third-Party Projects & Licenses

This project integrates data and translations from the following external community projects:

### 1. StarCitizenDeutsch

**Repository:** https://github.com/rjcncpt/StarCitizen-Deutsch-INI

**License:** [CC BY-NC-SA 4.0](https://creativecommons.org/licenses/by-nc-sa/4.0/) (Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International)

**Purpose:** Provides German language translations for Star Citizen game content, including items, vehicles, missions, and UI elements.

**Integration:** Integrated as a git submodule at `storage/app/api/StarCitizenDeutsch/`. German translations are loaded from `global.ini` files during the item import process via the Labels service.

**Attribution:** German translations are provided by the Star Citizen community translation team led by [rjcncpt](https://github.com/rjcncpt).

---

### 2. ScToolBoxLocales

**Repository:** https://github.com/StarCitizenToolBox/LocalizationData

**License:** License not explicitly stated in the repository

**Purpose:** Provides Chinese Simplified language translations for Star Citizen game content maintained by the StarCitizenToolBox community.

**Integration:** Located at `storage/app/api/ScToolBoxLocales/`. Chinese translations are loaded from `chinese_(simplified)/global.ini` during the item import process.

**Attribution:** Chinese translations are maintained by the StarCitizenToolBox organization and community contributors.

---

### 3. scunpacked-data

**Repository:** https://github.com/StarCitizenWiki/scunpacked-data

**License:** License not explicitly stated in the repository

**Purpose:** Contains unpacked and extracted Star Citizen game data in JSON format, including:
- Item definitions (`items.json`)
- Vehicle/ship data (`ships.json`)
- Manufacturer information (`manufacturers.json`)
- Translation labels (`labels.json`)
- Tags and classifications (`tags.json`)

**Integration:** Integrated as a separate repository at `storage/app/api/scunpacked-data/`. This is the primary data source for all game content imports, providing the raw game data that populates the API database.

**Attribution:** Data extraction and maintenance by the Star Citizen Wiki community.

---

## License Compliance

### StarCitizenDeutsch (CC BY-NC-SA 4.0)

This project uses German translations from StarCitizenDeutsch under the Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International license. This means:

- **Attribution (BY):** Credit must be given to the original creators
- **NonCommercial (NC):** The material cannot be used for commercial purposes
- **ShareAlike (SA):** Adaptations must be shared under the same license

**Required Attribution:**
German translations © Star Citizen Community Translation Team (rjcncpt and contributors)
Licensed under CC BY-NC-SA 4.0: https://creativecommons.org/licenses/by-nc-sa/4.0/

### Other Projects

ScToolBoxLocales and scunpacked-data do not have explicit license files in their repositories. These are community-maintained projects for the Star Citizen ecosystem. Users of this API should verify licensing requirements independently if using this data for commercial purposes.

---

## Acknowledgments

Special thanks to:
- The **Star Citizen German Translation Team** for their comprehensive German localization work
- The **StarCitizenToolBox** team for maintaining Chinese translations
- All community contributors who make these resources available

---

## Legal Notice

This project is a fan-made tool for the Star Citizen community and is not affiliated with or endorsed by Cloud Imperium Games or Roberts Space Industries. Star Citizen®, Roberts Space Industries®, and Cloud Imperium® are registered trademarks of Cloud Imperium Rights LLC and Cloud Imperium Rights Ltd.

All game data and translations are used for informational and educational purposes only.
