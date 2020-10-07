// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

import { UserGroupEvent } from 'groups-history/user-group-event';
import UserGroupEventJSON from 'interfaces/user-group-event-json';
import * as React from 'react';

interface Props {
  events: UserGroupEventJSON[];
}

export class UserGroupEvents extends React.PureComponent<Props> {
  render() {
    return (
      <div className='user-group-events'>
        {this.props.events.map((userGroupEvent) => (
          <UserGroupEvent
            event={userGroupEvent}
            key={userGroupEvent.id}
          />
        ))}
      </div>
    );
  }
}
