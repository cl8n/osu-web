# Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
# See the LICENCE file in the repository root for full licence text.

parseDuration = (string) ->
  float = parseFiniteFloat string

  return null unless float?

  switch string.slice -1
    when 'ms' then float * 0.001
    when 'm' then float * 60
    when 'h' then float * 3600
    else float


parseFiniteFloat = (string) ->
  float = parseFloat string

  if _.isFinite(float) then float else null


parseInt10 = (string) ->
  int = parseInt string, 10

  if _.isFinite(int) then int else null


class @BeatmapsetFilter
  @castFromString:
    mode: parseInt10
    genre: parseInt10
    language: parseInt10
    ar: parseFiniteFloat
    bpm: parseFiniteFloat
    cs: parseFiniteFloat
    drain: parseDuration
    hp: parseFiniteFloat
    keys: parseInt10
    length: parseDuration
    od: parseFiniteFloat
    stars: parseFiniteFloat


  @defaults:
    general: ''
    extra: ''
    genre: null
    language: null
    mode: null
    played: 'any'
    query: ''
    rank: ''
    status: 'leaderboard'


  # filter => query
  @keyToChar:
    extra: 'e'
    general: 'c'
    genre: 'g'
    language: 'l'
    mode: 'm'
    played: 'played'
    query: 'q'
    rank: 'r'
    sort: 'sort'
    status: 's'


  @operationFields: [
    'ar'
    'bpm'
    'cs'
    'drain'
    'hp'
    'keys'
    'length'
    'od'
    'stars'
  ]


  # query => filter
  @operators:
    eq: ['=', '==', ':']
    ne: ['!=', '!:']
    lt: ['<']
    le: ['<=', '<:']
    gt: ['>']
    ge: ['>=', '>:']


  @filtersFromUrl: (url) ->
    filters = {}
    params = new URL(url).searchParams
    getFilter = (key, char) ->
      value = params.get char

      return null if !value? || value.length == 0

      @castFromString[key]?(value) ? value

    for own key, char of @keyToChar
      value = getFilter key, char

      filters[key] = value if value?

    for field in @operationFields
      value = getFilter field, field

      if value?
        filters[key] =
          operator: @operators[params.get "#{field}_op"]?[0] ? '='
          value: value


  @queryParamsFromFilters: (filters) ->
    queryParams = {}

    for own key, value of filters
      continue unless value?

      if _.isPlainObject value
        # assume it's an operator, it came from ts...
        { operator, value } = value

        queryParams[key] = value
        queryParams["#{key}_op"] =
          _.findKey @operators, (filterOperators) -> filterOperators.includes operator
      else if @keyToChar[key]? && @getDefault(filters, key) != value
        queryParams[@keyToChar[key]] = value

    queryParams


  @fillDefaults: (filters) =>
    ret = {}

    for own key of @keyToChar
      ret[key] = filters[key] ? @getDefault(filters, key)

    ret


  @getDefault: (filters, key) =>
    return @defaults[key] if @defaults.hasOwnProperty(key)

    if key == 'sort'
      if filters.query?.trim().length > 0
        'relevance_desc'
      else
        if filters.status in ['pending', 'graveyard', 'mine']
          'updated_desc'
        else
          'ranked_desc'


  @getDefaults: (filters) =>
    ret = {}

    for own key of @keyToChar
      ret[key] = @getDefault(filters, key)

    ret


  @expanded: (filters) ->
    !_.isEmpty _.intersection Object.keys(filters), ['genre', 'language', 'extra', 'rank', 'played']


  # For UI purposes; server-side has its own check.
  @supporterRequired: (filters) ->
    _.reject ['played', 'rank'], (name) =>
      _.isEmpty(filters[name]) || filters[name] == @getDefault(filters, name)
