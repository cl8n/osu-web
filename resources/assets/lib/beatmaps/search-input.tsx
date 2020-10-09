// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

import core from 'osu-core-singleton';
import * as React from 'react';
import { classWithModifiers } from 'utils/css';

interface ContainerProps {
  extraClass?: string;
}

interface InputProps {
  onChange: (event: React.ChangeEvent<HTMLInputElement>) => void;
}

interface InputUserProps extends InputProps {
  forwardedRef: React.Ref<HTMLInputElement>;
}

interface InputUserState {
  inputText: string;
}

const bn = 'beatmapsets-search';

function SearchInputContainer(props: React.PropsWithChildren<ContainerProps>) {
  return (
    <div className={`${bn}__input-container ${props.extraClass ?? ''}`}>
      {props.children}
      <div className={`${bn}__icon`}>
        <i className='fas fa-search' />
      </div>
    </div>
  );
}

class SearchInputUser extends React.PureComponent<InputUserProps, InputUserState> {
  private fakeInputRef: React.RefObject<HTMLDivElement>;

  constructor(props: InputUserProps) {
    super(props);

    this.fakeInputRef = React.createRef();
    this.state = { inputText: core.beatmapsetSearchController.filters.query ?? '' };
  }

  render() {
    return (
      <>
        <input
          className={`${bn}__input js-beatmapsets-search-input`}
          defaultValue={core.beatmapsetSearchController.filters.query ?? undefined}
          name='search'
          onChange={this.onInputChange}
          onScroll={this.onInputScroll}
          placeholder={osu.trans('beatmaps.listing.search.prompt')}
          ref={this.props.forwardedRef}
          type='textbox'
        />
        <div
          className={`${bn}__input-fake`}
          ref={this.fakeInputRef}
        >
          {this.fakeInputContent()}
        </div>
      </>
    );
  }

  private fakeInputContent() {
    let inputText = this.state.inputText;

    if (inputText.length === 0) {
      return;
    }

    const operationBn = `${bn}__operation`;
    const operatorPattern = '(!?[=:]|[<>][=:]?|==)';
    const operationRegex = new RegExp(
      `(ar|bpm|cs|hp|od|stars)${operatorPattern}([0-9.]+)|` +
      `(drain|length)${operatorPattern}([0-9]+(?:m?s|m|h)?)|` +
      `(keys)${operatorPattern}([0-9]+)`,
      'i',
    );

    const elements: React.ReactNode[] = [];
    let match: RegExpExecArray | null;

    while (match = operationRegex.exec(inputText)) {
      const firstCaptureIndex = [1, 4, 7].find((index) => match![index] !== undefined) as number;

      const key = match[firstCaptureIndex];
      const operator = match[firstCaptureIndex + 1];
      const value = match[firstCaptureIndex + 2];

      elements.push(
        inputText.slice(0, match.index),
        <span className={classWithModifiers(operationBn, ['value'])}>{key}</span>,
        <span className={classWithModifiers(operationBn, ['operator'])}>{operator}</span>,
        <span className={classWithModifiers(operationBn, ['value'])}>{value}</span>,
      );

      inputText = inputText.slice(match.index + match[0].length);
    }

    if (inputText.length > 0) {
      elements.push(inputText);
    }

    return <>{...elements}</>;
  }

  private onInputChange: React.ChangeEventHandler<HTMLInputElement> = (event) => {
    this.setState({ inputText: event.target.value });

    this.props.onChange(event);
  }

  private onInputScroll: React.UIEventHandler<HTMLInputElement> = (event) => {
    if (this.fakeInputRef.current) {
      this.fakeInputRef.current.scrollLeft = (event.target as HTMLInputElement).scrollLeft;
    }
  }
}

export const SearchInput = React.forwardRef<HTMLInputElement, InputProps>((props, ref) => {
  return (
    <SearchInputContainer>
      <SearchInputUser
        forwardedRef={ref}
        onChange={props.onChange}
      />
    </SearchInputContainer>
  );
});

export function SearchInputGuest() {
  return (
    <SearchInputContainer extraClass='js-user-link'>
      <input
        className={`${bn}__input`}
        disabled
        placeholder={osu.trans('beatmaps.listing.search.login_required')}
        type='textbox'
      />
    </SearchInputContainer>
  );
}
