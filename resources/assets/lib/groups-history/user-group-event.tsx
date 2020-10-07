// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

import UserGroupEventJSON from 'interfaces/user-group-event-json';
import { route } from 'laroute';
import * as React from 'react';
import { StringWithComponent } from 'string-with-component';
import TimeWithTooltip from 'time-with-tooltip';
import { UserLink } from 'user-link';
import { classWithModifiers } from 'utils/css';

interface Props {
  event: UserGroupEventJSON;
}

const cssEventTypes = {
  group_add: 'group-add',
  group_remove: 'group-remove',
  group_rename: 'group-rename',
  user_add: 'user-add',
  user_remove: 'user-remove',
};

export class UserGroupEvent extends React.PureComponent<Props> {
  render() {
    return (
      <div className={classWithModifiers('user-group-event', [cssEventTypes[this.props.event.type]])}>
        <i className='user-group-event__icon' />
        <span className='user-group-event__message'>
          <StringWithComponent
            pattern={osu.trans(`group_history.event.${this.props.event.type}`)}
            mappings={this.messageMappings(this.props.event)}
          />
        </span>
        <span className='user-group-event__time'>
          <TimeWithTooltip dateTime={this.props.event.created_at} />
        </span>
      </div>
    );
  }

  private messageMappings(event: UserGroupEventJSON) {
    const mappings: Record<string, JSX.Element> = {
      ':group': (
        <a href={route('groups.show', { group: event.group_id })}>
          {event.group_name}
        </a>
      ),
    };

    switch (event.type) {
      case 'group_rename':
        mappings[':old_group'] = (
          <a href={route('groups.show', { group: event.group_id })}>
            {event.group_name_old}
          </a>
        );
        break;
      case 'user_add':
      case 'user_remove':
        mappings[':user'] = <UserLink user={event.user} />;
        break;
    }

    return mappings;
  }
}
