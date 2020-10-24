// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

import ScoreJson from 'interfaces/score-json';

interface ScoreBestJson extends ScoreJson {
  best_id: number;
}

export function canBeReported(score: ScoreJson): score is ScoreBestJson {
  return score.best_id != null
    && currentUser.id != null
    && score.user_id !== currentUser.id;
}

// TODO: move to application state repository thingy later
export function hasMenu(score: ScoreJson) {
  return canBeReported(score) || hasReplay(score) || hasShow(score);
}

export function hasReplay(score: ScoreJson) {
  return score.replay;
}

export function hasShow(score: ScoreJson): score is ScoreBestJson {
  return score.best_id != null;
}
