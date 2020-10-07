// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

interface EventBase {
    created_at: string;
    group_id: number;
    group_name: string;
    id: number;
}

interface GenericEvent extends EventBase {
    type: 'group_add' | 'group_remove';
}

interface GroupRenameEvent extends EventBase {
    group_name_old: string;
    type: 'group_rename';
}

interface UserEvent extends EventBase {
    type: 'user_add' | 'user_remove';
    user: {
        id?: number;
        username: string;
    };
}

type UserGroupEventJSON = GenericEvent | GroupRenameEvent | UserEvent;

export default UserGroupEventJSON;
