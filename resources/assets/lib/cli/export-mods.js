// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

'use strict';

const fs = require('fs');

const root = `${__dirname}/../../../..`;

function exportMods() {
  const modsByRuleset = JSON.parse(fs.readFileSync(`${root}/database/mods.json`));

  const exportedMods = {};
  for (const mods of modsByRuleset) {
    for (const mod of mods.Mods) {
      exportedMods[mod.Acronym] = {
        name: mod.Name,
        type: mod.Type,
      };

      if (mod.Icon != null) {
        exportedMods[mod.Acronym].icon = {
          family: mod.Icon.Family,
          icon: mod.Icon.Icon,
          weight: mod.Icon.Weight,
        };
      }
    }
  }

  exportedMods.NM = {
    icon: {
      family: 'osuModsFont',
      icon: '\ue817',
      weight: null,
    },
    name: 'No Mod',
    type: 'System',
  };
  exportedMods.V2 = {
    name: 'Score V2',
    type: 'Conversion',
  };

  const outDir = `${root}/resources/assets/build`;
  fs.mkdirSync(outDir, { recursive: true });
  fs.writeFileSync(`${outDir}/mods.json`, JSON.stringify(exportedMods));
}

module.exports = exportMods;
