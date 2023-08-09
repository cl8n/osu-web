// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

import Img2x from 'components/img2x';
import NewsAnnouncementJson from 'interfaces/news-announcement-json';
import { range } from 'lodash';
import { action, makeObservable, observable } from 'mobx';
import { observer } from 'mobx-react';
import * as React from 'react';
import { classWithModifiers } from 'utils/css';

function modulo(dividend: number, divisor: number): number {
  return ((dividend % divisor) + divisor) % divisor;
}

const autoRotateIntervalMs = 6000;
const bn = 'news-announcements';
const itemBn = 'news-announcement';

interface Props {
  announcements: NewsAnnouncementJson[];
}

@observer
export default class Main extends React.Component<Props> {
  private autoRotateTimerId?: number;
  @observable private index = 0;
  @observable private maxIndex = this.length - 1;
  @observable private minIndex = 0;
  @observable private transition = true;

  private get length() {
    return this.props.announcements.length;
  }

  constructor(props: Props) {
    super(props);

    makeObservable(this);
  }

  componentDidMount() {
    this.setAutoRotateTimer();
  }

  componentWillUnmount() {
    this.clearAutoRotateTimer();
  }

  render() {
    if (this.length === 0) {
      return null;
    }

    if (this.length === 1) {
      return (
        <div className={bn}>
          <div className={`${bn}__container`}>
            {this.renderAnnouncement(this.props.announcements[0])}
          </div>
        </div>
      );
    }

    return (
      <>
        <div
          className={bn}
          onMouseEnter={this.clearAutoRotateTimer}
          onMouseLeave={this.setAutoRotateTimer}
        >
          <div
            className={classWithModifiers(`${bn}__container`, { transition: this.transition })}
            onTransitionEnd={this.handleTransitionEnd}
            style={{ '--index': this.index } as React.CSSProperties}
          >
            {/*
              Render the announcements, including clones before and after to
              help create the illusion of an infinitely scrolling container
            */}
            {range(this.minIndex, this.maxIndex + 1).map(
              (index) => this.renderAnnouncement(this.props.announcements[modulo(index, this.length)], index),
            )}
          </div>
          {this.renderButtons()}
        </div>
        {this.renderIndicators()}
      </>
    );
  }

  private clearAutoRotateTimer = () => {
    window.clearInterval(this.autoRotateTimerId);
  };

  private handleButtonClick = (event: React.MouseEvent<HTMLButtonElement>) => {
    this.setIndex(this.index + parseInt(event.currentTarget.dataset.increment ?? '', 10));
  };

  @action
  private handleTransitionEnd = (event: React.TransitionEvent<HTMLDivElement>) => {
    if (event.propertyName !== 'transform' || event.currentTarget !== event.target) {
      return;
    }

    const index = modulo(this.index, this.length);

    // Reset the index to be within normal bounds, if it went outside. Don't
    // show the transition so that nothing changes visually
    if (this.index !== index) {
      this.setIndex(index, false);
    }

    // Reset the max and min indices to delete all of the cloned announcements
    this.maxIndex = this.length - 1;
    this.minIndex = 0;
  };

  private renderAnnouncement = (announcement: NewsAnnouncementJson, index = 0) => (
    <div
      key={`${announcement.id}:${index}`}
      className={itemBn}
      style={{ '--index': index } as React.CSSProperties}
    >
      <a className={`${itemBn}__link`} href={announcement.url}>
        <Img2x className={`${itemBn}__image`} src={announcement.image_url} />
      </a>
      {announcement.content != null && (
        <div
          className={`${itemBn}__content`}
          dangerouslySetInnerHTML={{ __html: announcement.content.html }}
        />
      )}
    </div>
  );

  private renderButtons() {
    return (
      <>
        <button
          className={`${bn}__button ${bn}__button--left`}
          data-increment={-1}
          onClick={this.handleButtonClick}
        />
        <button
          className={`${bn}__button ${bn}__button--right`}
          data-increment={1}
          onClick={this.handleButtonClick}
        />
      </>
    );
  }

  private renderIndicators() {
    return (
      <div className={`${bn}__indicators`}>
        {this.props.announcements.map((_, index) => (
          <div
            key={index}
            className={classWithModifiers(
              `${bn}__indicator`,
              { active: index === modulo(this.index, this.length) },
            )}
          />
        ))}
      </div>
    );
  }

  private setAutoRotateTimer = () => {
    this.clearAutoRotateTimer();

    this.autoRotateTimerId = window.setInterval(
      () => this.setIndex(this.index + 1),
      autoRotateIntervalMs,
    );
  };

  @action
  private setIndex(index: number, transition = true) {
    this.index = index;
    this.maxIndex = Math.max(this.maxIndex, index);
    this.minIndex = Math.min(this.minIndex, index);
    this.transition = transition;
  }
}