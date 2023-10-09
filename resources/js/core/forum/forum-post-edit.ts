// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

export default class ForumPostEdit {
  constructor() {
    $(document)
      .on('ajax:success', '.js-edit-post-start', this.handleEditStart)
      .on('click', '.js-edit-post-cancel', this.handleCancel)
      .on('ajax:success', '.js-forum-post-edit', this.handleEditSaved);
  }

  private readonly handleCancel = (e: JQuery.TriggeredEvent) => {
    e.preventDefault();

    // clear before target is removed
    $.publish('forum-post-input:clear', [e.target]);

    const $postBox = $(e.target).parents('.js-forum-post-edit--container');
    $postBox
      .html($postBox.attr('data-original-post') ?? '')
      .attr('data-original-post', null);
  };

  private readonly handleEditSaved = (e: JQuery.TriggeredEvent, data: string) => {
    const target: unknown = e.target;

    // allow another callbacks to finish before replacing form with new post.
    setTimeout(() => {
      this.saved(target, data);
    });
  };

  private readonly handleEditStart = (e: JQuery.TriggeredEvent, data: string) => {
    const target: unknown = e.target;

    // allow another callbacks to finish before replacing post with form.
    setTimeout(() => {
      this.start(target, data);
    });
  };

  private readonly saved = (target: unknown, data: string) => {
    if (!(target instanceof HTMLElement)) {
      throw new Error('target must be instance of HTMLElement');
    }

    $(target).parents('.js-forum-post').replaceWith(data);
  };

  private readonly start = (target: unknown, data: string) => {
    if (!(target instanceof HTMLElement)) {
      throw new Error('target must be instance of HTMLElement');
    }

    const $postBox = $(target).parents('.js-forum-post-edit--container');

    $postBox
      .attr('data-original-post', $postBox.html())
      .html(data)
      .find('[name=body]')
      .focus();

    $.publish('forum-post-input:restore', [$postBox[0]]);
  };
}
