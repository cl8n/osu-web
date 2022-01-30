// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

import core from 'osu-core-singleton';
import * as React from 'react';
import Main from 'records-index/main';
import { parseJson } from 'utils/json';

core.reactTurbolinks.register('records-index', () => (
  <Main {...parseJson('json-records-index')} />
));
