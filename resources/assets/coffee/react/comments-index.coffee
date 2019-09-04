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

import { CommentsManager } from 'comments-manager'
import core from 'osu-core-singleton'
import { Main } from './comments-index/main'


reactTurbolinks.registerPersistent 'comments-index', CommentsManager, true, ->
  commentBundle = osu.parseJson('json-index')

  if commentBundle?
    core.dataStore.commentableMetaStore.initialize(commentBundle.commentable_meta)
    core.dataStore.commentStore.initialize(commentBundle.comments, commentBundle.user_votes)
    core.dataStore.userStore.updateWithJSON(commentBundle.users)

  component: Main
  commentBundle: commentBundle
