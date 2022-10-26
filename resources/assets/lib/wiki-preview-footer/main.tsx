// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

import { action, computed, makeObservable } from 'mobx';
import { observer } from 'mobx-react';
import core from 'osu-core-singleton';
import * as React from 'react';
import { hideLoadingOverlay, showLoadingOverlay } from 'utils/loading-overlay';
import { getInt } from 'utils/math';
import { currentUrlParams } from 'utils/turbolinks';

interface InitialData {
  osu_web_version: string;
  pull_requests: PullRequest[];
  repository_url: string;
}

interface PullRequest {
  id: number;
  title: string;
}

interface Props {
  container: HTMLElement;
}

interface State {
  pullRequestId: number | undefined;
  pullRequestsAvailable: PullRequest[];
}

@observer
export default class Main extends React.Component<Props, State> {
  private readonly osuWebVersion: string;
  private readonly repositoryUrl: string;

  constructor(props: Props) {
    super(props);

    const initialData = JSON.parse(this.props.container.dataset.initialState ?? 'null') as InitialData;

    this.osuWebVersion = initialData.osu_web_version;
    this.repositoryUrl = initialData.repository_url;
    this.state = {
      pullRequestId: getInt(currentUrlParams().get('wiki-pr')),
      pullRequestsAvailable: initialData.pull_requests,
    };

    makeObservable(this);
  }

  render() {
    return (
      <div className='wiki-preview-footer'>
        <span>This is a previewer tool for wiki articles and news posts.</span>
        <div>
          <label htmlFor='wiki-pull-request'>PR: </label>
          <select
            name='wiki-pull-request'
            onChange={this.handlePullRequestChange}
            value={this.state.pullRequestId}
          >
            {this.state.pullRequestsAvailable.map((pullRequest) => (
              <option key={pullRequest.id} value={pullRequest.id}>
                {pullRequest.title} #{pullRequest.id}
              </option>
            ))}
          </select>
        </div>
        <div className='wiki-preview-footer__links'>
          <a className='wiki-preview-footer__link' href='https://github.com/cl8n/osu-web/tree/wiki-preview'>Source code</a>
          <a className='wiki-preview-footer__link' href='https://osu.ppy.sh'>Real osu! website</a>
          <span className='wiki-preview-footer__version'>
            Previewing osu-web v{this.osuWebVersion}
          </span>
        </div>
      </div>
    );
  }

  private handlePullRequestChange = (event: React.ChangeEvent<HTMLSelectElement>) => {
    const pullRequestId = getInt(event.currentTarget.value);

    if (pullRequestId === this.state.pullRequestId) {
      return;
    }

    // Turbolinks.controller.replaceHistory

    this.xhr.setExtraPageOrder?.abort();
    showLoadingOverlay();

    const xhr = $.ajax(route('account.options'), {
      data: {
        user_profile_customization: {
          extras_order: newOrder,
        },
      },
      dataType: 'json',
      method: 'PUT',
    })
      .done(() => {
        /* TODO reload page */
        this.setState({ pullRequestId });
      })
      .always(hideLoadingOverlay);

    this.xhr.setExtraPageOrder = xhr;
  };
}
