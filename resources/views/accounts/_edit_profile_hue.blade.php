{{--
    Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
    See the LICENCE file in the repository root for full licence text.
--}}
@php
    $value = auth()->user()->profileCustomization()->hue;
@endphp
<div class="account-edit__input-group">
    <div class="account-edit-entry js-account-edit js-user-preferences-update" data-url="{{ route('account.options') }}" data-account-edit-auto-submit="1" data-skip-ajax-error-popup="1">
        <input
            class="account-edit-entry__input js-account-edit__input"
            name="user_profile_customization[hue]"
            data-last-value="{{ $value }}"
            value="{{ $value }}"
            @if (auth()->user()->isSilenced())
                disabled
            @endif
        >

        <div class="account-edit-entry__label">
            {{ osu_trans("accounts.edit.profile.user.hue") }}
        </div>

        @include('accounts._edit_entry_status')

        <span class="account-edit-entry__error js-form-error--error"></span>
    </div>
</div>
