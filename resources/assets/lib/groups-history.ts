// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

import { Main } from 'groups-history/main';

reactTurbolinks.registerPersistent('groups-history', Main, true, () => {
  return {
    events: osu.parseJson('json-events'),
    groups: osu.parseJson('json-groups'),
  };
});
