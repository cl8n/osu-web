// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

import { action, computed, intercept, observable } from 'mobx';

type filterValueType = string | null;
type filterOperationType = filterOperation | null;

interface filterOperation {
  operator: '=' | '==' | ':' | '!=' | '!:' | '<' | '<=' | '<:' | '>' | '>=' | '>:';
  value: number;
}

export interface BeatmapsetSearchParams {
  extra: filterValueType;
  general: filterValueType;
  genre: filterValueType;
  language: filterValueType;
  mode: filterValueType;
  played: filterValueType;
  query: filterValueType;
  rank: filterValueType;
  sort: filterValueType;
  status: filterValueType;

  ar: filterOperationType;
  bpm: filterOperationType;
  cs: filterOperationType;
  drain: filterOperationType;
  hp: filterOperationType;
  keys: filterOperationType;
  length: filterOperationType;
  od: filterOperationType;
  stars: filterOperationType;

  //[key: string]: any;
}

export class BeatmapsetSearchFilters implements BeatmapsetSearchParams {
  @observable extra: filterValueType = null;
  @observable general: filterValueType = null;
  @observable genre: filterValueType = null;
  @observable language: filterValueType = null;
  @observable mode: filterValueType = null;
  @observable played: filterValueType = null;
  @observable query: filterValueType = null;
  @observable rank: filterValueType = null;
  @observable sort: filterValueType = null;
  @observable status: filterValueType = null;

  @observable ar: filterOperationType = null;
  @observable bpm: filterOperationType = null;
  @observable cs: filterOperationType = null;
  @observable drain: filterOperationType = null;
  @observable hp: filterOperationType = null;
  @observable keys: filterOperationType = null;
  @observable length: filterOperationType = null;
  @observable od: filterOperationType = null;
  @observable stars: filterOperationType = null;

  //[key: string]: any;

  constructor(url: string) {
    const filters = BeatmapsetFilter.filtersFromUrl(url) as Partial<BeatmapsetSearchParams>;

    Object.assign(this, filters);

    intercept(this, 'query', (change) => {
      change.newValue = osu.presence((change.newValue as filterValueType)?.trim());

      return change;
    });
  }

  @computed
  get displaySort() {
    return this.selectedValue('sort');
  }

  @computed
  get queryParams() {
    const values = this.values;

    return BeatmapsetFilter.queryParamsFromFilters(values);
  }

  selectedValue(key: keyof BeatmapsetSearchFilters): filterValueType {
    const value = this[key];
    if (value == null) {
      return BeatmapsetFilter.getDefault(this.values, key) as filterValueType;
    }

    return value;
  }

  toKeyString() {
    const values = this.values;

    const normalized = BeatmapsetFilter.fillDefaults(values) as any;
    const parts = [];
    for (const key of Object.keys(normalized)) {
      parts.push(`${key}=${normalized[key]}`);
    }

    return parts.join('&');
  }

  @action
  update(newFilters: Partial<BeatmapsetSearchParams>) {
    if (newFilters.query !== undefined && newFilters.query !== this.query
      || newFilters.status !== undefined && newFilters.status !== this.status) {
      this.sort = null;
    }

    Object.assign(this, newFilters);
  }

  /**
   * Returns a copy of the values in the filter.
   */
  @computed
  private get values(): BeatmapsetSearchParams {
    return Object.assign({}, this);
  }
}
