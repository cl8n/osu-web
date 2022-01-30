// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

import FlagCountry from 'flag-country';
import HeaderV4 from 'header-v4';
import GameMode from 'interfaces/game-mode';
import ScoreJson from 'interfaces/score-json';
import { route } from 'laroute';
import { snakeCase } from 'lodash';
import Mod from 'mod';
import { PlayDetailMenu } from 'play-detail-menu';
import PlaymodeTabs from 'playmode-tabs';
import * as React from 'react';
import PpValue from 'scores/pp-value';
import TimeWithTooltip from 'time-with-tooltip';
import { UserLink } from 'user-link';
import { getArtist, getTitle } from 'utils/beatmap-helper';

interface Props {
  container: HTMLElement;
  mode: GameMode;
  scores: ScoreJson[];
  type: 'scores-performance';
}

export default class Main extends React.Component<Props> {
  constructor(props: Props) {
    super(props);
  }

  gameModeHref = (mode: GameMode) => route('records', {
    mode,
    type: this.props.type,
  });

  render() {
    return (
      <>
        <HeaderV4
          links={[
            {
              active: true,
              title: osu.trans(`records.type.${snakeCase(this.props.type)}`),
              url: route('records', {
                mode: this.props.mode,
                type: this.props.type,
              }),
            },
          ]}
          theme='rankings'
          titleAppend={
            <PlaymodeTabs
              currentMode={this.props.mode}
              hrefFunc={this.gameModeHref}
              visit
            />
          }
        />

        <div className='osu-page osu-page--generic'>
          {this.props.scores.length === 0 ? (
            <div>
              {osu.trans('records.index.empty')}
            </div>
          ) : (
            <table className='ranking-page-table'>
              <thead>
                <tr>
                  <th className='ranking-page-table__heading' />
                  <th className='ranking-page-table__heading ranking-page-table__heading--main'>
                    {osu.trans('records.index.heading.score')}
                  </th>
                  <th className='ranking-page-table__heading'>
                    {osu.trans('records.index.heading.player')}
                  </th>
                  <th className='ranking-page-table__heading ranking-page-table__heading--focused'>
                    {osu.trans('records.index.heading.performance')}
                  </th>
                </tr>
              </thead>
              <tbody>
                {this.props.scores.map(this.renderScoreRow)}
              </tbody>
            </table>
          )}
        </div>
      </>
    );
  }

  renderScore(score: ScoreJson) {
    const bn = 'play-detail';
    const { beatmap, beatmapset } = score;

    if (beatmap == null || beatmapset == null) {
      throw new Error('score json is missing beatmap or beatmapset details');
    }

    return (
      <div className={bn}>
        <div className={`${bn}__group ${bn}__group--top`}>
          <div className={`${bn}__icon ${bn}__icon--main`}>
            <div className={`score-rank score-rank--full score-rank--${score.rank}`} />
          </div>

          <div className={`${bn}__detail`}>
            <a
              className={`${bn}__title u-ellipsis-overflow`}
              href={route('beatmaps.show', { beatmap: beatmap.id, mode: score.mode })}
            >
              {getTitle(beatmapset)}
              {' '}
              <small className={`${bn}__artist`}>
                {osu.trans('users.show.extra.beatmaps.by_artist', { artist: getArtist(beatmapset) })}
              </small>
            </a>
            <div className={`${bn}__beatmap-and-time`}>
              <span className={`${bn}__beatmap`}>
                {beatmap.version}
              </span>
              <span className={`${bn}__time`}>
                <TimeWithTooltip dateTime={score.created_at} relative />
              </span>
            </div>
          </div>
        </div>

        <div className={`${bn}__group ${bn}__group--bottom`}>
          <div className={`${bn}__score-detail ${bn}__score-detail--score`}>
            <div className={`${bn}__icon ${bn}__icon--extra`}>
              <div className={`score-rank score-rank--full score-rank--${score.rank}`} />
            </div>
            <div className={`${bn}__score-detail-top-right`}>
              <div className={`${bn}__accuracy-and-weighted-pp`}>
                <span className={`${bn}__accuracy`}>
                  {osu.formatNumber(score.accuracy * 100, 2)}%
                </span>
              </div>
            </div>
          </div>

          <div className={`${bn}__score-detail ${bn}__score-detail--mods`}>
            {score.mods.map((mod) => <Mod key={mod} mod={mod} />)}
          </div>

          <div className={`${bn}__pp`}>
            <PpValue
              score={score}
              suffix={<span className={`${bn}__pp-unit`}>pp</span>}
            />
          </div>

          <div className={`${bn}__more`}>
            <PlayDetailMenu score={score} />
          </div>
        </div>
      </div>
    );
  }

  renderScoreRow = (score: ScoreJson, index: number) => (
    <tr key={score.id} className='ranking-page-table__row'>
      <td className='ranking-page-table__column ranking-page-table__column--rank'>
          #{index + 1}
      </td>
      <td className='ranking-page-table__column'>
        {this.renderScore(score)}
      </td>
      <td className='ranking-page-table__column'>
        <div className='ranking-page-table__user-link'>
          <FlagCountry
            country={score.user.country}
            modifiers='medium'
          />
          <UserLink
            className='ranking-page-table__user-link-text'
            mode={score.mode}
            tooltipPosition='right center'
            user={score.user}
          />
        </div>
      </td>
      <td className='ranking-page-table__column ranking-page-table__column--focused'>
        {osu.formatNumber(score.pp!)}
      </td>
    </tr>
  );
}
