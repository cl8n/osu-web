// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

import { kebabCase } from 'lodash';
import mods, { ModJson } from 'mods.json';
import * as React from 'react';
import { classWithModifiers } from 'utils/css';

function getIconModifiers(icon: ModJson['icon']) {
  switch (icon?.family) {
    case 'FontAwesome':
      return icon.weight === 'Solid' ? 'fa-solid' : 'fa-regular';
    case 'osuModsFont':
      return 'osu';
    default:
      return null;
  }
}

const unknownMod: ModJson = {
  name: 'Unknown Mod',
  type: 'System',
};

interface Props {
  mod: string;
}

export default function Mod({ mod: modAcronym }: Props) {
  const mod = mods[modAcronym] ?? unknownMod;
  const iconModifiers = getIconModifiers(mod.icon);

  return (
    <div className={classWithModifiers('mod', kebabCase(mod.type))} title={mod.name}>
      <span className={classWithModifiers('mod__background', kebabCase(mod.type))} />
      {iconModifiers != null ? (
        <span className={classWithModifiers('mod__icon', iconModifiers, { 'no-mod': modAcronym === 'NM' })}>
          {mod.icon?.icon}
        </span>
      ) : (
        <span className='mod__acronym'>{modAcronym}</span>
      )}
    </div>
  );
}
