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

{div, i} = ReactDOMFactories
el = React.createElement

@BeatmapIcon = (props) ->
  beatmap = props.beatmap

  # debug
  showNew = true

  difficultyRating = props.overrideVersion ? (beatmap.difficulty_rating_custom unless beatmap.convert)
  difficultyRating ?= BeatmapHelper.getDiffRating(beatmap.difficulty_rating)
  showTooltip = (props.showTitle ? true) && !props.overrideVersion?
  mode = if beatmap.convert then 'osu' else beatmap.mode

  className = "beatmap-icon beatmap-icon--#{difficultyRating} beatmap-icon--#{props.modifier}"
  className += " beatmap-icon--with-hover js-beatmap-tooltip" if showTooltip
  className += " beatmap-icon--with-hover js-beatmap-tooltip-edit-rating" if showNew

  if showNew
    div
      className: className
      'data-id': beatmap.id
      'data-mode': mode
      'data-custom-rating': beatmap.difficulty_rating_custom
      div className: 'beatmap-icon__shadow'
      i className: "fal fa-extra-mode-#{mode}"
  else
    div
      className: className
      'data-beatmap-title': beatmap.version if showTooltip
      'data-stars': _.round beatmap.difficulty_rating, 2 if showTooltip
      'data-difficulty': difficultyRating
      div className: 'beatmap-icon__shadow'
      i className: "fal fa-extra-mode-#{mode}"
