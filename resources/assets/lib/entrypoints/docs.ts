// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

document.addEventListener('DOMContentLoaded', () => {
  const headings = document.querySelectorAll('body .content h1[id], body .content h2[id]');

  if (headings.length === 0) {
    throw new Error('headings not found');
  }

  document.addEventListener('scroll', (event) => {
    if (event.eventPhase !== Event.AT_TARGET) {
      return;
    }

    let targetHeading: Element | null = null;

    for (const heading of headings) {
      if (heading.getBoundingClientRect().top > 1) {
        break;
      }

      targetHeading = heading;
    }

    if (targetHeading != null) {
      history.pushState(null, '', `#${targetHeading.id}`);
      window.dispatchEvent(new Event('hashchange'));
    }
  });
});
