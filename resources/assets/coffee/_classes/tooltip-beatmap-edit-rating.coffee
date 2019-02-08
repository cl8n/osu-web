###
#    Copyright (c) ppy Pty Ltd <contact@ppy.sh>.
#
#    This file is part of osu!web. osu!web is distributed with the hope of
#    attracting more community contributions to the core ecosystem of osu!.
#
#    osu!web is free software: you can redistribute it and/or modify
#    it under the terms of the Affero GNU General Public License version 3
#    as published by the Free Software Foundation.
#
#    osu!web is distributed WITHOUT ANY WARRANTY; without even the implied
#    warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
#    See the GNU Affero General Public License for more details.
#
#    You should have received a copy of the GNU Affero General Public License
#    along with osu!web.  If not, see <http://www.gnu.org/licenses/>.
###

{a} = ReactDOMFactories
el = React.createElement

class @TooltipBeatmapEditRating
  icon: (id, mode, rating, active) ->
    className = 'tooltip-beatmap-edit-rating__option'
    className += ' tooltip-beatmap-edit-rating__option--active' if active

    a
      className: className
      href: laroute.route 'beatmaps.custom-difficulty',
        beatmap: id
        rating: rating if active
      'data-method': 'PUT'
      'data-remote': true
      'nice'
#      el BeatmapIcon,
#        beatmap:
#          convert: false
#          mode: mode
#        overrideVersion: rating

  content: (beatmap) ->
    content = ''

    for rating in ['easy', 'normal', 'hard', 'insane', 'expert', 'expert-plus']
      content += @icon beatmap.id, beatmap.mode, rating, beatmap.customRating == rating

    content

  constructor: ->
    $(document).on 'mouseover touchstart', '.js-beatmap-tooltip-edit-rating', @onMouseOver

  onMouseOver: (event) =>
    el = event.currentTarget

    return if !el.dataset.id?

    content = @content el.dataset

    if el._tooltip
      $(el).qtip 'set', 'content.text': content
      return

    at = el.dataset.tooltipPosition ? 'top center'
    my = switch at
      when 'top center' then 'bottom center'
      when 'left center' then 'right center'
      when 'right center' then 'left center'

    options =
      overwrite: false
      content: content
      position:
        my: my
        at: at
        viewport: $(window)
      show:
        event: event.type
        ready: true
      hide:
        event: 'click mouseleave'
      style:
        classes: 'qtip tooltip-beatmap-edit-rating'
        tip:
          width: 10
          height: 9

    if event.type == 'touchstart'
      options.hide = inactive: 3000

    $(el).qtip options, event

    el._tooltip = true
