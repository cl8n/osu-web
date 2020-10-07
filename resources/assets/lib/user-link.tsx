// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

import { route } from 'laroute';
import * as React from 'react';

interface UserProps {
  id?: number;
  username: string;
}

interface DeletedUserProps {
  id: undefined;
  username: undefined;
}

interface Props {
  children?: React.ReactNode;
  className?: string;
  tooltipPosition?: string;
  user: UserProps | DeletedUserProps;
}

export class UserLink extends React.PureComponent<Props> {
  render() {
    return this.props.user.id == null ? (
      <span className={this.props.className}>
        {this.props.children ?? this.props.user.username ?? osu.trans('users.deleted')}
      </span>
    ) : (
      <a
        className={`js-usercard ${this.props.className ?? ''}`}
        data-tooltip-position={this.props.tooltipPosition}
        data-user-id={this.props.user.id}
        href={route('users.show', { user: this.props.user.id })}
      >
        {this.props.children ?? this.props.user.username}
      </a>
    );
  }
}
